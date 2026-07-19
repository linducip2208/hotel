<?php

namespace App\Services\Communication;

use App\Models\Guest;
use App\Models\Message;
use App\Models\MessageThread;
use App\Services\Integrations\ProviderRegistry;

class MessagingService
{
    public function __construct(protected ProviderRegistry $registry) {}

    public function recordInbound(string $channel, string $from, string $body, array $context = []): Message
    {
        $thread = $this->resolveThread($channel, $from, $context);
        $msg = Message::create([
            'thread_id' => $thread->id,
            'direction' => 'inbound',
            'from' => $from,
            'body' => $body,
            'status' => 'delivered',
            'raw_payload' => $context,
        ]);
        $thread->update([
            'last_message_at' => now(),
            'unread_count' => $thread->unread_count + 1,
            'status' => 'open',
        ]);
        return $msg;
    }

    public function reply(MessageThread $thread, string $body, ?int $userId = null): Message
    {
        $msg = Message::create([
            'thread_id' => $thread->id,
            'direction' => 'outbound',
            'to' => $thread->guest?->email ?? $thread->guest?->phone,
            'body' => $body,
            'status' => 'queued',
        ]);

        $this->dispatchOutbound($thread, $msg);

        $thread->update(['last_message_at' => now()]);
        return $msg;
    }

    protected function resolveThread(string $channel, string $from, array $context): MessageThread
    {
        $guest = Guest::where('email', $from)->orWhere('phone', $from)->first();

        return MessageThread::firstOrCreate(
            [
                'property_id' => $context['property_id'] ?? 1,
                'channel' => $channel,
                'external_thread_id' => $context['external_thread_id'] ?? null,
            ],
            [
                'guest_id' => $guest?->id,
                'subject' => $context['subject'] ?? 'Conversation',
                'status' => 'open',
            ]
        );
    }

    protected function dispatchOutbound(MessageThread $thread, Message $msg): void
    {
        try {
            $adapter = match ($thread->channel) {
                'email' => $this->registry->forFeature($thread->property_id, 'mail_transactional'),
                'whatsapp' => $this->registry->forFeature($thread->property_id, 'wa_transactional'),
                'sms' => $this->registry->forFeature($thread->property_id, 'sms_transactional'),
                default => null,
            };
            if ($adapter && method_exists($adapter, 'send')) {
                $adapter->send($msg->to, $msg->body, []);
                $msg->update(['status' => 'sent']);
            }
        } catch (\Throwable $e) {
            $msg->update(['status' => 'failed']);
        }
    }
}
