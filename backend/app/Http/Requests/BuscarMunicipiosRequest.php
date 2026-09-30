<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BuscarMunicipiosRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:60'],
            'limite' => ['sometimes', 'integer', 'between:1,20'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'q.required' => 'Informe o termo de busca (q).',
            'q.min' => 'O campo q deve ter pelo menos 2 caracteres.',
            'q.max' => 'O campo q deve ter no máximo 60 caracteres.',
            'limite.integer' => 'O campo limite deve ser um número inteiro.',
            'limite.between' => 'O campo limite deve estar entre 1 e 20.',
        ];
    }

    public function termo(): string
    {
        return $this->string('q')->toString();
    }

    public function limite(): int
    {
        return $this->integer('limite', 10);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('q'))) {
            $this->merge(['q' => trim($this->input('q'))]);
        }
    }
}
