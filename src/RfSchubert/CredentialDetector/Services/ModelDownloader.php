<?php

namespace RfSchubert\CredentialDetector\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use RfSchubert\CredentialDetector\Exception\ModelDownloadException;

/**
 * Serviço para baixar o modelo treinado.
 * (Versão temporária para testes - cria um arquivo dummy)
 */
class ModelDownloader
{
    /**
     * URL do modelo ONNX
     *
     * @var string
     */
    protected const MODEL_URL = 'https://raw.githubusercontent.com/rfschubert/credential-pattern-detector/main/models/credential_detector_model.onnx';

    /**
     * Nome do arquivo do modelo
     *
     * @var string
     */
    protected const MODEL_FILENAME = 'credential_detector_model.onnx';

    /**
     * Cliente HTTP
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Construtor
     *
     * @param ClientInterface $client Cliente HTTP
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Cria um modelo simulado para testes
     *
     * @return bool
     * @throws ModelDownloadException
     */
    public function download(): bool
    {
        $modelsDir = $this->getModelsDirectory();
        $modelPath = $modelsDir . '/' . self::MODEL_FILENAME;

        // Criar diretório de modelos se não existir
        if (!is_dir($modelsDir)) {
            if (!mkdir($modelsDir, 0755, true)) {
                throw new ModelDownloadException("Não foi possível criar o diretório de modelos: {$modelsDir}");
            }
        }

        try {
            // Para testes, em vez de baixar o modelo, criamos um arquivo dummy
            file_put_contents($modelPath, "MODELO SIMULADO PARA TESTES");
            
            return true;
        } catch (\Exception $e) {
            throw new ModelDownloadException(
                "Erro ao criar modelo simulado: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Obtém o diretório de modelos
     *
     * @return string
     */
    protected function getModelsDirectory(): string
    {
        return dirname(__DIR__, 4) . '/models';
    }

    /**
     * Obtém o caminho completo para o arquivo do modelo
     *
     * @return string
     */
    public function getModelPath(): string
    {
        return $this->getModelsDirectory() . '/' . self::MODEL_FILENAME;
    }
} 