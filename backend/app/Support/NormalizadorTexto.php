<?php

namespace App\Support;

use Illuminate\Support\Str;

final class NormalizadorTexto
{
    public static function normalizar(string $texto): string
    {
        $texto = Str::ascii($texto);
        $texto = strtolower(str_replace(["'", '’', '-'], ' ', $texto));

        return trim((string) preg_replace('/\s+/', ' ', $texto));
    }
}
