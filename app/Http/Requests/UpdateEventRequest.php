<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'sometimes|required|date|after:now',
            'location' => 'sometimes|required|string|max:500',
            'location_reveal_after_payment' => 'nullable|boolean',
            'max_participants' => 'nullable|integer|min:1',
            'theme' => 'nullable|string|max:255',
            'rules' => 'nullable|string',
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
        ];
    }
}
