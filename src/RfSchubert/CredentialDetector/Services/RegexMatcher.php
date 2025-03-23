<?php

namespace RfSchubert\CredentialDetector\Services;

/**
 * Serviço para encontrar correspondências de padrões regex em um texto.
 */
class RegexMatcher
{
    /**
     * Padrões regex padrão para detecção de credenciais
     *
     * @var array
     */
    protected const DEFAULT_PATTERNS = [
        'api_key' => '/(?i)(?:api[_-]?key|apikey)[\'"]?\s*(?::|=|=>)\s*[\'"]?([a-zA-Z0-9]{16,64})[\'"]?/',
        'aws_key' => '/(?:AKIA|A3T|AGPA|AIDA|AROA|AIPA|ANPA|ANVA|ASIA)[A-Z0-9]{16}/',
        'password' => '/(?i)(?:password|senha|pwd)[\'"]?\s*(?::|=|=>)\s*[\'"]?([^\s]{8,64})[\'"]?/',
        'private_key' => '/-----BEGIN (?:RSA|DSA|EC|OPENSSH) PRIVATE KEY-----/',
        'auth_token' => '/(?i)(?:authorization|authentication|auth[_-]?token|bearer)[\'"]?\s*(?::|=|=>)\s*[\'"]?([^\s]{8,})[\'"]?/',
        'jwt' => '/eyJ[a-zA-Z0-9_-]{5,}\.eyJ[a-zA-Z0-9_-]{5,}\.[a-zA-Z0-9_-]{5,}/',
        'secret' => '/(?i)(?:secret|secretkey|client[_-]secret)[\'"]?\s*(?::|=|=>)\s*[\'"]?([^\s]{8,})[\'"]?/',
    ];

    /**
     * Padrões regex compilados
     *
     * @var array
     */
    protected $patterns;

    /**
     * Construtor
     *
     * @param array|null $customPatterns Padrões personalizados (opcional)
     */
    public function __construct(?array $customPatterns = null)
    {
        $this->patterns = $customPatterns ?? self::DEFAULT_PATTERNS;
    }

    /**
     * Encontra correspondências de padrões no texto
     *
     * @param string $text Texto a ser analisado
     * @return array Array com [matches, positions]
     */
    public function findMatches(string $text): array
    {
        $matches = [];
        $positions = [];

        foreach ($this->patterns as $patternName => $pattern) {
            preg_match_all($pattern, $text, $patternMatches, PREG_OFFSET_CAPTURE);
            
            if (!empty($patternMatches[0])) {
                foreach ($patternMatches[0] as $index => $match) {
                    // Se houver grupos de captura, use o primeiro grupo se disponível
                    if (!empty($patternMatches[1][$index])) {
                        $matchValue = $patternMatches[1][$index][0];
                        $start = $patternMatches[1][$index][1];
                        $end = $start + strlen($matchValue);
                    } else {
                        $matchValue = $match[0];
                        $start = $match[1];
                        $end = $start + strlen($matchValue);
                    }
                    
                    $matches[] = $matchValue;
                    $positions[] = [$start, $end, $patternName];
                }
            }
        }

        return [$matches, $positions];
    }

    /**
     * Obtém os padrões atuais
     *
     * @return array
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }
} 