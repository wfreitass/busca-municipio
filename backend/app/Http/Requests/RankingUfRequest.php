<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RankingUfRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'pagina' => ['sometimes', 'integer', 'min:1'],
            'por_pagina' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'pagina.integer' => 'O campo pagina deve ser um número inteiro.',
            'pagina.min' => 'O campo pagina deve ser maior ou igual a 1.',
            'por_pagina.integer' => 'O campo por_pagina deve ser um número inteiro.',
            'por_pagina.between' => 'O campo por_pagina deve estar entre 1 e 100.',
        ];
    }

    public function pagina(): int
    {
        return $this->integer('pagina', 1);
    }

    public function porPagina(): int
    {
        return $this->integer('por_pagina', 50);
    }
}
