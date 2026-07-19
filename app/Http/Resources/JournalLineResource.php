<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class JournalLineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'journal_entry_id' => $this->journal_entry_id,
            'account_id'       => $this->account_id,
            'account_code'     => $this->account?->code,
            'account_name'     => $this->account?->name,
            'debit'            => isset($this->debit) ? number_format((float) $this->debit, 2, '.', '') : null,
            'credit'           => isset($this->credit) ? number_format((float) $this->credit, 2, '.', '') : null,
            'description'      => $this->description,
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
