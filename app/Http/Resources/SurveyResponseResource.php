<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class SurveyResponseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'survey_id'      => $this->survey_id,
            'reservation_id' => $this->reservation_id,
            'guest_id'       => $this->guest_id,
            'answers'        => $this->answers,
            'submitted_at'   => $this->submitted_at?->toIso8601String(),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
