<?php

namespace RfSchubert\CredentialDetector\Services;

use RfSchubert\CredentialDetector\Exception\ModelNotFoundException;

/**
 * Serviço para interagir com o modelo ONNX para detecção de credenciais
 * (Versão temporária para testes - sem dependência externa)
 */
class OnnxModelService
{
    /**
     * Caminho para o arquivo do modelo
     * 
     * @var string
     */
    protected $modelPath;
    
    /**
     * Palavras-chave que indicam possíveis credenciais
     * 
     * @var array
     */
    protected $keywords = [
        'api_key', 'apikey', 'secret', 'password', 'senha', 'token', 'auth', 
        'key', 'credential', 'private', 'cert', 'jwt', 'bearer', 'access'
    ];

    /**
     * Construtor
     * 
     * @param string $modelPath Caminho para o arquivo do modelo ONNX
     */
    public function __construct(string $modelPath)
    {
        $this->modelPath = $modelPath;
    }

    /**
     * Carrega o modelo ONNX
     * 
     * @return void
     * @throws ModelNotFoundException Se o modelo não puder ser carregado
     */
    public function loadModel(): void
    {
        if (!file_exists($this->modelPath)) {
            throw new ModelNotFoundException(
                "Modelo ONNX não encontrado em: {$this->modelPath}"
            );
        }
        
        // Na versão de teste, o carregamento do modelo é simulado
        // Em um ambiente real, aqui carregaríamos o modelo ONNX
    }

    /**
     * Verifica se o texto contém credenciais usando uma simulação de IA
     * 
     * @param string $text Texto a ser analisado
     * @return array [probabilidade, classe]
     */
    public function predict(string $text): array
    {
        // Esta é uma implementação simplificada para testes
        // que simula a detecção de padrões sem usar o modelo ONNX
        
        $text = strtolower($text);
        $confidenceScore = 0.0;
        
        // Verificar se o texto contém palavras-chave indicativas de credenciais
        foreach ($this->keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $confidenceScore += 0.3;
            }
        }
        
        // Verificar padrões de caracteres que podem indicar credenciais
        // (por exemplo, combinações de letras e números)
        if (preg_match('/[a-z0-9]{16,}/', $text)) {
            $confidenceScore += 0.3;
        }
        
        // Verificar se há caracteres especiais típicos de senhas
        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $text)) {
            $confidenceScore += 0.2;
        }
        
        // Limitar o valor máximo a 0.95
        $confidenceScore = min($confidenceScore, 0.95);
        
        return [$confidenceScore, $confidenceScore >= 0.5];
    }

    /**
     * Pré-processa o texto para o formato esperado pelo modelo
     * 
     * @param string $text Texto a ser processado
     * @return array Texto processado no formato adequado para o modelo
     */
    protected function preprocessText(string $text): array
    {
        // Na versão de teste, o pré-processamento é simplificado
        return str_split($text);
    }
} 