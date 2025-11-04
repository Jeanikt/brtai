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
            'price' => 'required_if:is_free,false|numeric|min:0',
            'is_free' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do evento é obrigatório.',
            'event_date.required' => 'A data do evento é obrigatória.',
            'event_date.date' => 'A data do evento deve ser uma data válida.',
            'event_date.after' => 'A data do evento deve ser futura.',
            'event_time.required' => 'A hora do evento é obrigatória.',
            'event_time.date_format' => 'A hora do evento deve estar no formato HH:MM.',
            'location.required' => 'O local do evento é obrigatório.',
            'price.required_if' => 'O valor do ingresso é obrigatório para eventos pagos.',
            'price.numeric' => 'O valor do ingresso deve ser um número.',
            'price.min' => 'O valor do ingresso deve ser maior ou igual a zero.',
            'header_image.image' => 'O arquivo deve ser uma imagem válida.',
            'header_image.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg ou gif.',
            'header_image.max' => 'A imagem não pode ser maior que 5MB.',
        ];
    }
}
