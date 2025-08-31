<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'learning_area_id' => $this->learning_area_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'level' => $this->level,
            'source' => $this->source,
            'platform' => $this->platform,
            'external_url' => $this->external_url,
            'is_certified' => (bool) $this->is_certified,
            'is_published' => (bool) $this->is_published,
            'starts_at' => optional($this->starts_at)->toDateString(),
            'ends_at' => optional($this->ends_at)->toDateString(),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}

