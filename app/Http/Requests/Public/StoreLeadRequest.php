<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'website' => ['nullable', 'max:0'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'min:8', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'has_trade_in' => ['nullable', 'boolean'],
            'wants_financing' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'website.max' => 'Falha na verificação de segurança.',
            'name.required' => 'Informe seu nome.',
            'phone.required' => 'Informe seu telefone ou WhatsApp.',
        ];
    }
}
