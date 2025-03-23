<?php

namespace RfSchubert\CredentialDetector;

use RfSchubert\CredentialDetector\Exception\ModelDownloadException;
use RfSchubert\CredentialDetector\Exception\ModelNotFoundException;
use RfSchubert\CredentialDetector\Models\DetectionResult;
use RfSchubert\CredentialDetector\Services\ModelDownloader;
use RfSchubert\CredentialDetector\Services\OnnxModelService;
use RfSchubert\CredentialDetector\Services\RegexMatcher;
use GuzzleHttp\Client;

/**
 * Detector de padrões de credenciais em texto.
 * 
 * Esta classe utiliza tanto modelo de IA quanto regras baseadas em expressões regulares para identificar credenciais em texto.
 */
class Detector
{
    /**
     * Serviço para correspondência de regex
     *
     * @var RegexMatcher
     */
    protected $regexMatcher;

    /**
     * Serviço para o modelo ONNX
     *
     * @var OnnxModelService|null
     */
    protected $onnxService;

    /**
     * Limiar de confiança para detecção
     *
     * @var float
     */
    protected $confidenceThreshold;

    /**
     * Downloader do modelo
     *
     * @var ModelDownloader|null
     */
    protected $modelDownloader;

    /**
     * Indica se o modelo deve ser pré-carregado
     *
     * @var bool
     */
    protected $preloadModel;

    /**
     * Construtor
     *
     * @param float $confidenceThreshold Limiar de confiança (0.0 a 1.0)
     * @param array|null $patterns Padrões personalizados (opcional)
     * @param bool $preloadModel Se o modelo deve ser pré-carregado
     */
    public function __construct(float $confidenceThreshold = 0.7, ?array $patterns = null, bool $preloadModel = false)
    {
        $this->confidenceThreshold = $confidenceThreshold;
        $this->regexMatcher = new RegexMatcher($patterns);
        $this->preloadModel = $preloadModel;
        $this->modelDownloader = new ModelDownloader(new Client());
        
        // Garantir que o modelo está disponível
        $this->ensureModelAvailable();
        
        // Pré-carregar o modelo se necessário
        if ($preloadModel) {
            $this->loadOnnxModel();
        }
    }

    /**
     * Garante que o modelo necessário está disponível
     *
     * @return void
     * @throws ModelNotFoundException
     */
    protected function ensureModelAvailable(): void
    {
        $modelPath = $this->getModelPath();
        
        if (!file_exists($modelPath)) {
            try {
                $this->modelDownloader->download();
            } catch (ModelDownloadException $e) {
                throw new ModelNotFoundException(
                    "Não foi possível baixar o modelo. Erro: " . $e->getMessage()
                );
            }
        }
    }

    /**
     * Obtém o caminho para o arquivo do modelo
     *
     * @return string
     */
    public function getModelPath(): string
    {
        return $this->modelDownloader->getModelPath();
    }

    /**
     * Carrega o modelo ONNX
     *
     * @return void
     */
    protected function loadOnnxModel(): void
    {
        if ($this->onnxService === null) {
            $modelDownloader = $this->modelDownloader ?: new ModelDownloader(new Client());
            $this->onnxService = new OnnxModelService(
                $this->getModelPath(),
                $modelDownloader->getConfigPath(),
                $modelDownloader->getVectorizerPath(),
                $modelDownloader->getPatternsPath()
            );
        }
    }

    /**
     * Detecta credenciais no texto fornecido usando tanto regex quanto IA
     *
     * @param string $text Texto a ser analisado
     * @return DetectionResult
     */
    public function detect(string $text): DetectionResult
    {
        if (empty($text)) {
            return new DetectionResult(false, 0.0);
        }

        // 1. Procurar por matches usando regex
        list($regexMatches, $regexPositions) = $this->regexMatcher->findMatches($text);
        $regexConfidence = !empty($regexMatches) ? 0.9 : 0.0;
        
        // 2. Procurar usando o modelo de IA
        $aiConfidence = 0.0;
        $aiResult = false;
        
        try {
            // Carregar o modelo se ainda não estiver carregado
            if ($this->onnxService === null) {
                $this->loadOnnxModel();
            }
            
            // Fazer a predição usando o modelo
            list($aiConfidence, $aiResult) = $this->onnxService->predict($text);
        } catch (\Exception $e) {
            // Se houver erro na predição com IA, confiamos apenas no regex
            $aiConfidence = 0.0;
            $aiResult = false;
        }
        
        // 3. Combinar os resultados - escolher o método com maior confiança
        $bestConfidence = max($regexConfidence, $aiConfidence);
        $hasCredential = $bestConfidence >= $this->confidenceThreshold;
        
        // Usar os matches do regex se disponíveis
        $finalMatches = $regexMatches;
        $finalPositions = $regexPositions;
        
        // Se não temos matches do regex, mas a IA detectou algo,
        // consideramos o texto completo como match
        if (empty($finalMatches) && $aiResult && $aiConfidence >= $this->confidenceThreshold) {
            $finalMatches = [$text];
            $finalPositions = [[0, strlen($text), 'ai_detected']];
        }
        
        return new DetectionResult($hasCredential, $bestConfidence, $finalMatches, $finalPositions);
    }
} 