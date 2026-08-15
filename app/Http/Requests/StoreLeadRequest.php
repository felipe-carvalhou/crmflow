<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    /**
     * Rota pública chamada pela landing page — qualquer um pode enviar.
     */
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
            'nome' => ['required', 'string', 'min:2', 'max:255'],
            'telefone' => [
                'required',
                'string',
                'max:20',
                // Aceita formatos BR comuns: (11) 91234-5678, 11 91234-5678,
                // 11912345678, +55 11 91234-5678, com ou sem o 9º dígito.
                'regex:/^\+?(55)?\s?\(?\d{2}\)?\s?9?\d{4}-?\d{4}$/',
            ],
            'segmento' => ['required', 'string', 'max:255'],
            'origem' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome.',
            'telefone.required' => 'Informe o telefone.',
            'telefone.regex' => 'Informe um telefone válido, com DDD (ex: (11) 91234-5678).',
            'segmento.required' => 'Informe o segmento.',
        ];
    }

    /**
     * Remove tudo que não for dígito ou "+" antes de validar, pra aceitar
     * telefone digitado com ou sem máscara.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('telefone')) {
            $this->merge([
                'telefone' => trim((string) $this->input('telefone')),
            ]);
        }
    }
}
