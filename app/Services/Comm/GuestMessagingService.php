<?php

namespace App\Services\Comm;

use App\Models\MessageThread;
use App\Models\Message;
use App\Models\QuickReply;
use Carbon\Carbon;

class GuestMessagingService
{
    public function getOrCreateThread(int $propertyId, int $guestId, ?int $reservationId = null): MessageThread
    {
        $thread = MessageThread::where('property_id', $propertyId)
            ->where('guest_id', $guestId)
            ->where('status', 'open')
            ->first();

        if (!$thread) {
            $thread = MessageThread::create([
                'property_id' => $propertyId,
                'guest_id' => $guestId,
                'reservation_id' => $reservationId,
                'channel' => 'web_chat',
                'status' => 'open',
                'last_message_at' => now(),
                'unread_count' => 0,
            ]);
        }

        return $thread;
    }

    public function sendMessage(int $threadId, string $direction, string $from, string $to, string $body, string $type = 'text', ?array $attachments = null): Message
    {
        $msg = Message::create([
            'thread_id' => $threadId,
            'direction' => $direction,
            'message_type' => $type,
            'from' => $from,
            'to' => $to,
            'body' => $body,
            'attachments' => $attachments,
            'status' => 'delivered',
            'is_read' => $direction === 'outbound', // staff messages auto-read
            'read_at' => $direction === 'outbound' ? now() : null,
        ]);

        $thread = MessageThread::find($threadId);
        if ($thread) {
            $thread->update([
                'last_message_at' => now(),
                'unread_count' => $direction === 'inbound'
                    ? $thread->unread_count + 1
                    : $thread->unread_count,
            ]);
        }

        return $msg;
    }

    public function markThreadRead(int $threadId): void
    {
        $thread = MessageThread::findOrFail($threadId);
        $thread->messages()
            ->where('direction', 'inbound')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        $thread->update(['unread_count' => 0]);
    }

    public function getQuickReplies(int $propertyId): array
    {
        return QuickReply::where('property_id', $propertyId)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->toArray();
    }

    public function getThreadMessages(int $threadId, int $limit = 50, ?int $afterId = null): array
    {
        $query = Message::where('thread_id', $threadId)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($afterId) {
            $query->where('id', '>', $afterId);
        }

        return $query->get()->reverse()->values()->toArray();
    }

    public function getUnreadCount(int $propertyId): int
    {
        return MessageThread::where('property_id', $propertyId)
            ->where('status', 'open')
            ->sum('unread_count');
    }

    public function getActiveThreads(int $propertyId, ?int $assigneeId = null): array
    {
        $query = MessageThread::where('property_id', $propertyId)
            ->where('status', 'open')
            ->with(['guest:id,first_name,last_name,phone,email', 'lastMessage'])
            ->orderBy('last_message_at', 'desc');

        if ($assigneeId) {
            $query->where('assignee_id', $assigneeId);
        }

        return $query->get()->toArray();
    }

    public function closeThread(int $threadId): void
    {
        MessageThread::where('id', $threadId)->update([
            'status' => 'closed',
            'unread_count' => 0,
        ]);
    }
}
