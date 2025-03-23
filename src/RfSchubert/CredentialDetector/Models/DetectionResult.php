<?php

namespace RfSchubert\CredentialDetector\Models;

/**
 * Resultado da detecção de credenciais.
 */
class DetectionResult
{
    /**
     * Indica se uma credencial foi detectada
     *
     * @var bool
     */
    protected $hasCredential;

    /**
     * Nível de confiança da detecção (0.0 a 1.0)
     *
     * @var float
     */
    protected $confidence;

    /**
     * Lista de credenciais encontradas
     *
     * @var array
     */
    protected $matches;

    /**
     * Posições das correspondências encontradas
     *
     * @var array
     */
    protected $matchPositions;

    /**
     * Construtor
     *
     * @param bool $hasCredential Indica se uma credencial foi detectada
     * @param float $confidence Nível de confiança da detecção
     * @param array|null $matches Lista de credenciais encontradas
     * @param array|null $matchPositions Posições das correspondências encontradas
     */
    public function __construct(bool $hasCredential, float $confidence, ?array $matches = null, ?array $matchPositions = null)
    {
        $this->hasCredential = $hasCredential;
        $this->confidence = $confidence;
        $this->matches = $matches ?? [];
        $this->matchPositions = $matchPositions ?? [];
    }

    /**
     * Verifica se uma credencial foi detectada
     *
     * @return bool
     */
    public function hasCredential(): bool
    {
        return $this->hasCredential;
    }

    /**
     * Obtém o nível de confiança da detecção
     *
     * @return float
     */
    public function getConfidence(): float
    {
        return $this->confidence;
    }

    /**
     * Obtém a lista de credenciais encontradas
     *
     * @return array
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * Obtém as posições das correspondências encontradas
     *
     * @return array
     */
    public function getMatchPositions(): array
    {
        return $this->matchPositions;
    }

    /**
     * Converte o objeto para um array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'has_credential' => $this->hasCredential,
            'confidence' => $this->confidence,
            'matches' => $this->matches,
            'match_positions' => $this->matchPositions
        ];
    }
} 