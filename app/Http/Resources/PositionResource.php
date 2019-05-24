<?php

namespace App\Http\Resources;

use App\Http\Resources\VerdictResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'fen' => $this->fen,
            'trainable' => $this->training_enabled,
            'verdicts' => VerdictResource::collection($this->whenLoaded('verdicts')),
            'attemps' => []
        ];
    }
}
