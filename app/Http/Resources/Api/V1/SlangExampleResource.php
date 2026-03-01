<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlangExampleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'korean_example' => $this->korean_example,
            'english_example' => $this->english_example,
        ];
    }
}
