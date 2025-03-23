<?php

namespace RfSchubert\CredentialDetector\Services;

use PhpML\ONNX\Model;
use PhpML\ONNX\Exception\RuntimeException;
use RfSchubert\CredentialDetector\Exception\ModelNotFoundException;

/**
 * Serviço para interagir com o modelo ONNX para detecção de credenciais
 */
class OnnxModelService
{
    /**
     * Instância do modelo ONNX
     * 
     * @var Model|null
     */
    protected $model = null;

    /**
     * Caminho para o arquivo do modelo
     * 
     * @var string
     */
    protected $modelPath;

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

        try {
            $this->model = new Model($this->modelPath);
        } catch (RuntimeException $e) {
            throw new ModelNotFoundException(
                "Erro ao carregar modelo ONNX: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Verifica se o texto contém credenciais usando o modelo ONNX
     * 
     * @param string $text Texto a ser analisado
     * @return array [probabilidade, classe]
     * @throws ModelNotFoundException Se o modelo não estiver carregado
     */
    public function predict(string $text): array
    {
        if ($this->model === null) {
            $this->loadModel();
        }

        // Pré-processamento do texto para o formato esperado pelo modelo
        $processedText = $this->preprocessText($text);
        
        try {
            $prediction = $this->model->predict([
                'input' => [$processedText]
            ]);
            
            // A saída esperada é um array com [probabilidade, classe]
            // Onde classe 1 indica que é uma credencial e 0 que não é
            $probability = $prediction['output'][0][1] ?? 0;
            $isCredential = $probability >= 0.5;
            
            return [$probability, $isCredential];
        } catch (\Exception $e) {
            // Em caso de erro na predição, retornamos probabilidade 0
            return [0, false];
        }
    }

    /**
     * Pré-processa o texto para o formato esperado pelo modelo
     * 
     * @param string $text Texto a ser processado
     * @return array Texto processado no formato adequado para o modelo
     */
    protected function preprocessText(string $text): array
    {
        // Aqui deve ser implementado o mesmo pré-processamento usado no treinamento
        // Por exemplo, tokenização, normalização, etc.
        
        // Como exemplo simples, estamos apenas retornando o texto como array de caracteres
        return str_split($text);
    }
} 