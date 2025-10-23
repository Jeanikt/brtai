<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtém as regras de validação que se aplicam à requisição.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after:now',
            'location' => 'required|string|max:500',
            'location_reveal_after_payment' => 'nullable|boolean',
            'max_participants' => 'nullable|integer|min:1',
            'theme' => 'nullable|string|max:255',
            'rules' => 'nullable|string',
            'price_tiers' => 'nullable|array',
            'price_tiers.*.name' => 'required_with:price_tiers|string|max:255',
            'price_tiers.*.price' => 'required_with:price_tiers|numeric|min:0',
            'price_tiers.*.max_quantity' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Obtém as mensagens de erro personalizadas para o validador.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do evento é obrigatório.',
            'name.max' => 'O nome do evento não pode ter mais de 255 caracteres.',
            'event_date.required' => 'A data do evento é obrigatória.',
            'event_date.date' => 'A data do evento deve ser uma data válida.',
            'event_date.after' => 'A data do evento deve ser futura.',
            'location.required' => 'A localização do evento é obrigatória.',
            'location.max' => 'A localização não pode ter mais de 500 caracteres.',
            'max_participants.min' => 'O número máximo de participantes deve ser pelo menos 1.',
            'price_tiers.*.name.required_with' => 'O nome do lote de preço é obrigatório.',
            'price_tiers.*.price.required_with' => 'O preço do lote é obrigatório.',
            'price_tiers.*.price.numeric' => 'O preço deve ser um valor numérico.',
            'price_tiers.*.price.min' => 'O preço deve ser maior ou igual a zero.',
            'price_tiers.*.max_quantity.min' => 'A quantidade máxima deve ser pelo menos 1.',
        ];
    }

    /**
     * Obtém os atributos personalizados para mensagens de erro.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome do evento',
            'event_date' => 'data do evento',
            'location' => 'localização',
            'max_participants' => 'máximo de participantes',
            'price_tiers.*.name' => 'nome do lote',
            'price_tiers.*.price' => 'preço do lote',
            'price_tiers.*.max_quantity' => 'quantidade máxima',
        ];
    }
}
