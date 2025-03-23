<?php

/**
 * Script de teste para verificar a integração ONNX
 */

// Incluir autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

use RfSchubert\CredentialDetector\Detector;

// Exibir informações sobre as extensões PHP disponíveis
echo "Extensões PHP disponíveis:\n";
echo "- FFI: " . (extension_loaded('ffi') ? 'SIM' : 'NÃO') . "\n";
echo "- JSON: " . (extension_loaded('json') ? 'SIM' : 'NÃO') . "\n";
echo "- ORT (ONNX Runtime): " . (extension_loaded('onnxruntime') ? 'SIM' : 'NÃO') . "\n";
echo "\n";

// Verificar se a classe ONNX Runtime está disponível
echo "ORT\\Session disponível: " . (class_exists('\\ORT\\Session') ? 'SIM' : 'NÃO') . "\n\n";

try {
    echo "Inicializando detector...\n";
    // Criar detector com pré-carregamento de modelo
    $detector = new Detector(0.7, null, true);
    echo "Detector inicializado com sucesso!\n\n";
    
    // Testar com exemplos de credenciais
    $examples = [
        "Esta é uma frase normal sem credenciais",
        "Minha senha é abc123!@#",
        "API_KEY=1234567890abcdef",
        "JWT_SECRET=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9",
        "DB_PASSWORD='super_secreto'"
    ];
    
    foreach ($examples as $example) {
        $result = $detector->detect($example);
        echo "Texto: '{$example}'\n";
        echo "É credencial: " . ($result->hasCredential() ? 'SIM' : 'NÃO') . "\n";
        echo "Confiança: " . number_format($result->getConfidence() * 100, 2) . "%\n";
        echo "Matches: " . implode(", ", $result->getMatches()) . "\n";
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 