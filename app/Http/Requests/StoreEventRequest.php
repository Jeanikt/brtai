<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after:now',
            'event_time' => 'required|date_format:H:i',
            'location' => 'required|string|max:500',
            'location_reveal_after_payment' => 'boolean',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'theme' => 'nullable|string|max:255',
            'rules' => 'nullable|string',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'header_image.image' => 'O arquivo deve ser uma imagem válida.',
            'header_image.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg ou gif.',
            'header_image.max' => 'A imagem não pode ser maior que 5MB.',
        ];
    }
}
