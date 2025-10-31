<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'payment_status' => $this->payment_status,
            'payment_amount' => $this->payment_amount,
            'confirmed_at' => $this->confirmed_at,
            'checked_in_at' => $this->checked_in_at,
            'price_tier' => $this->priceTier ? [
                'id' => $this->priceTier->id,
                'name' => $this->priceTier->name,
                'price' => $this->priceTier->price,
            ] : null,
            'event' => $this->event ? [
                'id' => $this->event->id,
                'name' => $this->event->name,
            ] : null,
        ];
    }
}
