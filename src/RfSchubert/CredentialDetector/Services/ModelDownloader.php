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
    protected const MODEL_URL = 'https://raw.githubusercontent.com/rfschubert/credential-pattern-detector/main/models/onnx/credential_detector.onnx';

    /**
     * URL do arquivo de configuração do modelo
     *
     * @var string
     */
    protected const CONFIG_URL = 'https://raw.githubusercontent.com/rfschubert/credential-pattern-detector/main/models/onnx/credential_detector_config.json';

    /**
     * URL do arquivo de vetorização
     *
     * @var string
     */
    protected const VECTORIZER_URL = 'https://raw.githubusercontent.com/rfschubert/credential-pattern-detector/main/models/onnx/credential_detector_vectorizer.json';

    /**
     * URL do arquivo de padrões
     *
     * @var string
     */
    protected const PATTERNS_URL = 'https://raw.githubusercontent.com/rfschubert/credential-pattern-detector/main/models/onnx/credential_detector_patterns.json';

    /**
     * Nome do arquivo do modelo
     *
     * @var string
     */
    protected const MODEL_FILENAME = 'credential_detector.onnx';

    /**
     * Nome do arquivo de configuração
     *
     * @var string
     */
    protected const CONFIG_FILENAME = 'credential_detector_config.json';

    /**
     * Nome do arquivo de vetorização
     *
     * @var string
     */
    protected const VECTORIZER_FILENAME = 'credential_detector_vectorizer.json';

    /**
     * Nome do arquivo de padrões
     *
     * @var string
     */
    protected const PATTERNS_FILENAME = 'credential_detector_patterns.json';

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
     * Baixa o modelo ONNX e arquivos auxiliares
     *
     * @return bool
     * @throws ModelDownloadException
     */
    public function download(): bool
    {
        $modelsDir = $this->getModelsDirectory();
        
        // Criar diretório de modelos se não existir
        if (!is_dir($modelsDir)) {
            if (!mkdir($modelsDir, 0755, true)) {
                throw new ModelDownloadException("Não foi possível criar o diretório de modelos: {$modelsDir}");
            }
        }

        // Lista dos arquivos para baixar
        $files = [
            ['url' => self::MODEL_URL, 'filename' => self::MODEL_FILENAME],
            ['url' => self::CONFIG_URL, 'filename' => self::CONFIG_FILENAME],
            ['url' => self::VECTORIZER_URL, 'filename' => self::VECTORIZER_FILENAME],
            ['url' => self::PATTERNS_URL, 'filename' => self::PATTERNS_FILENAME]
        ];

        // Baixar cada arquivo
        foreach ($files as $file) {
            $filePath = $modelsDir . '/' . $file['filename'];
            try {
                $response = $this->client->request('GET', $file['url']);
                
                if ($response->getStatusCode() === 200) {
                    file_put_contents($filePath, $response->getBody()->getContents());
                } else {
                    throw new ModelDownloadException(
                        "Erro ao baixar o arquivo {$file['filename']}. Código de status: " . $response->getStatusCode()
                    );
                }
            } catch (GuzzleException $e) {
                throw new ModelDownloadException(
                    "Erro ao baixar o arquivo {$file['filename']}: " . $e->getMessage(),
                    0,
                    $e
                );
            } catch (\Exception $e) {
                throw new ModelDownloadException(
                    "Erro inesperado ao baixar o arquivo {$file['filename']}: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        return true;
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

    /**
     * Obtém o caminho completo para o arquivo de configuração
     *
     * @return string
     */
    public function getConfigPath(): string
    {
        return $this->getModelsDirectory() . '/' . self::CONFIG_FILENAME;
    }

    /**
     * Obtém o caminho completo para o arquivo de vetorização
     *
     * @return string
     */
    public function getVectorizerPath(): string
    {
        return $this->getModelsDirectory() . '/' . self::VECTORIZER_FILENAME;
    }

    /**
     * Obtém o caminho completo para o arquivo de padrões
     *
     * @return string
     */
    public function getPatternsPath(): string
    {
        return $this->getModelsDirectory() . '/' . self::PATTERNS_FILENAME;
    }
} 