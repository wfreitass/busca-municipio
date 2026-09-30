<?php

return [
    /*
    | Falhar a carga do read model se os totais não baterem com o IBGE
    | (27 UFs, 5.570 municípios, 203.080.756 hab., 8.510.417 km²).
    | Desligado só nos testes, que usam fixtures pequenas.
    */
    'validar_totais' => (bool) env('CENSO_VALIDAR_TOTAIS', true),
];
