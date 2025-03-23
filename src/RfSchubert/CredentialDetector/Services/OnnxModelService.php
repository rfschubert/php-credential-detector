<?php

namespace RfSchubert\CredentialDetector\Services;

use RfSchubert\CredentialDetector\Exception\ModelNotFoundException;

/**
 * Serviço para interagir com o modelo ONNX para detecção de credenciais
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
     * Caminho para o arquivo de configuração
     * 
     * @var string
     */
    protected $configPath;

    /**
     * Caminho para o arquivo de vetorização
     * 
     * @var string
     */
    protected $vectorizerPath;

    /**
     * Caminho para o arquivo de padrões
     * 
     * @var string
     */
    protected $patternsPath;
    
    /**
     * Instância do modelo ONNX
     * 
     * @var \ORT\Session|null
     */
    protected $model = null;

    /**
     * Dados do vetorizador
     * 
     * @var array|null
     */
    protected $vectorizer = null;

    /**
     * Configuração do modelo
     * 
     * @var array|null
     */
    protected $config = null;

    /**
     * Palavras-chave que indicam possíveis credenciais (fallback)
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
     * @param string|null $configPath Caminho para o arquivo de configuração
     * @param string|null $vectorizerPath Caminho para o arquivo de vetorização
     * @param string|null $patternsPath Caminho para o arquivo de padrões
     */
    public function __construct(string $modelPath, string $configPath = null, string $vectorizerPath = null, string $patternsPath = null)
    {
        $this->modelPath = $modelPath;
        $this->configPath = $configPath ?? dirname($modelPath) . '/credential_detector_config.json';
        $this->vectorizerPath = $vectorizerPath ?? dirname($modelPath) . '/credential_detector_vectorizer.json';
        $this->patternsPath = $patternsPath ?? dirname($modelPath) . '/credential_detector_patterns.json';
    }

    /**
     * Carrega o modelo ONNX e arquivos auxiliares
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
        
        if (!file_exists($this->configPath)) {
            throw new ModelNotFoundException(
                "Arquivo de configuração não encontrado em: {$this->configPath}"
            );
        }
        
        if (!file_exists($this->vectorizerPath)) {
            throw new ModelNotFoundException(
                "Arquivo de vetorização não encontrado em: {$this->vectorizerPath}"
            );
        }
        
        // Verifica se a biblioteca ONNX Runtime está disponível
        if (!class_exists('\\ORT\\Session')) {
            throw new \RuntimeException(
                "A biblioteca ONNX Runtime não está instalada. Para instalar, siga as instruções em https://github.com/microsoft/onnxruntime-php"
            );
        }
        
        try {
            // Carrega o modelo ONNX
            $this->model = new \ORT\Session($this->modelPath);
            
            // Carrega o vetorizador
            $this->vectorizer = json_decode(file_get_contents($this->vectorizerPath), true);
            
            // Carrega a configuração
            $this->config = json_decode(file_get_contents($this->configPath), true);
            
        } catch (\Exception $e) {
            throw new ModelNotFoundException(
                "Erro ao carregar o modelo ONNX ou arquivos auxiliares: " . $e->getMessage()
            );
        }
    }

    /**
     * Verifica se o texto contém credenciais usando o modelo ONNX
     * 
     * @param string $text Texto a ser analisado
     * @return array [probabilidade, classe]
     */
    public function predict(string $text): array
    {
        // Tenta usar o modelo ONNX
        try {
            if ($this->model === null) {
                $this->loadModel();
            }
            
            // Extrair features do texto
            $features = $this->extractFeatures($text);
            
            // Vectorizar o texto
            $vector = $this->vectorizeText($features);
            
            // Fazer a previsão com o modelo ONNX
            $input = ['input' => $vector];
            $output = $this->model->run($input);
            
            // O output contém as probabilidades para cada classe [não_credencial, credencial]
            $probabilities = $output[0][0];
            $confidence = $probabilities[1]; // Probabilidade da classe "credencial"
            
            return [$confidence, $confidence >= 0.5];
            
        } catch (\Exception $e) {
            // Fallback para método alternativo se o modelo falhar
            return $this->fallbackPredict($text);
        }
    }

    /**
     * Método de fallback para predição quando o modelo ONNX não funciona
     * 
     * @param string $text Texto a ser analisado
     * @return array [probabilidade, classe]
     */
    protected function fallbackPredict(string $text): array
    {
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
     * Extrai features do texto
     * 
     * @param string $text Texto a ser processado
     * @return array Features extraídas
     */
    protected function extractFeatures(string $text): array
    {
        // Implementação simples de extração de features
        return [
            'text' => $text,
            'length' => strlen($text),
            'has_numbers' => preg_match('/[0-9]/', $text) ? 1 : 0,
            'has_special_chars' => preg_match('/[!@#$%^&*(),.?":{}|<>]/', $text) ? 1 : 0,
            'has_uppercase' => preg_match('/[A-Z]/', $text) ? 1 : 0,
            'has_lowercase' => preg_match('/[a-z]/', $text) ? 1 : 0
        ];
    }

    /**
     * Vectoriza o texto para o formato esperado pelo modelo
     * 
     * @param array $features Features extraídas do texto
     * @return array Vetor de entrada para o modelo
     */
    protected function vectorizeText(array $features): array
    {
        // Se temos um vetorizador carregado, usamos ele
        if (is_array($this->vectorizer) && !empty($this->vectorizer)) {
            // TODO: Implementar a vectorização baseada no arquivo de vectorização
            // Esta é uma implementação simplificada
            return $this->simpleVectorize($features['text']);
        }
        
        // Fallback para vectorização simples
        return $this->simpleVectorize($features['text']);
    }
    
    /**
     * Vectorização simples para textos
     * 
     * @param string $text Texto a ser vectorizado
     * @return array Vetor de features
     */
    protected function simpleVectorize(string $text): array
    {
        // Implementação simplificada que cria um vetor one-hot
        $vector = array_fill(0, 128, 0);
        $chars = str_split(strtolower($text));
        
        foreach ($chars as $char) {
            $code = ord($char);
            if ($code >= 32 && $code <= 126) { // ASCII printable chars
                $vector[$code - 32] = 1;
            }
        }
        
        return [$vector]; // O modelo espera uma matriz 2D
    }
} 