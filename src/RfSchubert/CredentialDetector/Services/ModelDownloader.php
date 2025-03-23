<?php

namespace RfSchubert\CredentialDetector\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use RfSchubert\CredentialDetector\Exception\ModelDownloadException;

/**
 * Serviço para baixar o modelo treinado.
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
     * Baixa o modelo do repositório remoto
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
            // Baixar o modelo
            $response = $this->client->request('GET', self::MODEL_URL);
            
            if ($response->getStatusCode() !== 200) {
                throw new ModelDownloadException(
                    "Falha ao baixar o modelo. Código de status: {$response->getStatusCode()}"
                );
            }

            // Salvar o modelo no arquivo
            file_put_contents($modelPath, $response->getBody()->getContents());
            
            return true;
        } catch (GuzzleException $e) {
            throw new ModelDownloadException(
                "Erro ao baixar o modelo: " . $e->getMessage(),
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