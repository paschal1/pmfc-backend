<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'message' => $this->message,
            'areasize' => $this->areasize,
            'location' => $this->location,
            'squarefeet' => $this->squarefeet,
            'budget' => $this->budget,
            'service_ids' => json_decode($this->service_ids, true),
            'service_titles' => $this->service_titles,
            'service_prices' => $this->service_prices,
            'details' => json_decode($this->details, true),
            'quote' => json_decode($this->quote, true),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
