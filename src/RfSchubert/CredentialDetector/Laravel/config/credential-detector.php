<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Limiar de Confiança
    |--------------------------------------------------------------------------
    |
    | Este valor determina o limiar mínimo de confiança para considerar 
    | uma string como credencial. Valores mais altos reduzem falsos positivos,
    | mas podem aumentar falsos negativos.
    |
    */
    'confidence_threshold' => env('CREDENTIAL_DETECTOR_THRESHOLD', 0.7),

    /*
    |--------------------------------------------------------------------------
    | Padrões de Expressões Regulares Personalizados
    |--------------------------------------------------------------------------
    |
    | Aqui você pode definir seus próprios padrões de expressões regulares para
    | detecção de credenciais. Se definido como null, serão utilizados os 
    | padrões padrão da biblioteca.
    |
    */
    'patterns' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache de Modelo
    |--------------------------------------------------------------------------
    |
    | Se você deseja que o modelo seja carregado automaticamente em 
    | inicialização do aplicativo.
    |
    */
    'preload_model' => env('CREDENTIAL_DETECTOR_PRELOAD', false),
]; 