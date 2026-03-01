<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'korean' => $this->korean,
            'pronunciation' => $this->pronunciation,
            'english_description' => $this->english_description,
            'korean_description' => $this->korean_description,
            'level' => $this->level,
            'level_label' => $this->level_label,
            'usage_frequency' => $this->usage_frequency,
            'usage_context' => $this->usage_context,
            'audio_url' => $this->audio_url,
            'categories' => SlangCategoryResource::collection($this->whenLoaded('categories')),
            'examples' => SlangExampleResource::collection($this->whenLoaded('examples')),
        ];
    }
}
