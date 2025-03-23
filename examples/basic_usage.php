<?php

// Carregar o autoloader do Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

use RfSchubert\CredentialDetector\Detector;

// Criar detector com configurações padrão
$detector = new Detector();

// Exemplos de textos para testar
$textos = [
    "Este é um texto normal sem credenciais",
    "Minha senha é X#9pL@7!2ZqR e meu usuário é joao123",
    "API_KEY=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ"
];

echo "=== DETECTOR DE CREDENCIAIS ===\n\n";

foreach ($textos as $index => $texto) {
    echo "Texto #" . ($index + 1) . ":\n";
    echo "\"" . $texto . "\"\n";
    
    // Detectar credenciais
    $resultado = $detector->detect($texto);
    
    echo "Resultado: ";
    if ($resultado->hasCredential()) {
        echo "CREDENCIAL DETECTADA\n";
        echo "Confiança: " . number_format($resultado->getConfidence() * 100, 1) . "%\n";
        
        if (!empty($resultado->getMatches())) {
            echo "Credenciais encontradas: " . implode(", ", $resultado->getMatches()) . "\n";
        }
    } else {
        echo "NENHUMA CREDENCIAL DETECTADA\n";
        echo "Confiança: " . number_format($resultado->getConfidence() * 100, 1) . "%\n";
    }
    
    echo "\n----------------------------\n\n";
}

// Exemplo com pré-carregamento do modelo para maior performance
echo "Exemplo com pré-carregamento do modelo:\n";
$detector = new Detector(0.7, null, true);
$resultado = $detector->detect("secret_key = '12345abcdef'");

echo "Resultado com modelo pré-carregado: ";
if ($resultado->hasCredential()) {
    echo "CREDENCIAL DETECTADA com confiança de " . number_format($resultado->getConfidence() * 100, 1) . "%\n";
} else {
    echo "NENHUMA CREDENCIAL DETECTADA\n";
} 