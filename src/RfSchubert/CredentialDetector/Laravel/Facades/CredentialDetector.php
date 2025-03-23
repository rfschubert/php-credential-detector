<?php

namespace RfSchubert\CredentialDetector\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \RfSchubert\CredentialDetector\Models\DetectionResult detect(string $text)
 */
class CredentialDetector extends Facade
{
    /**
     * Obtém o nome do componente registrado.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'credential-detector';
    }
} 