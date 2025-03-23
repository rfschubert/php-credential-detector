# Detector de Padrões de Credenciais para PHP

Cliente PHP para o Detector de Padrões de Credenciais com integração para Laravel. Esta biblioteca permite identificar e proteger informações sensíveis em strings de texto.

## Objetivo

Este cliente PHP foi desenvolvido para consumir o modelo de machine learning treinado pelo [Detector de Padrões de Credenciais](https://github.com/rfschubert/credential-pattern-detector), oferecendo uma solução fácil de usar para aplicações PHP, especialmente em projetos Laravel.

## Características

- Detecção de diversos tipos de credenciais em texto (senhas, tokens, chaves de API, etc.)
- **Dupla validação com IA (usando modelo ONNX) e Regex para maior precisão**
- Integração simples com Laravel através de Facade e Service Provider
- Baixa taxa de falsos positivos
- Alta performance e baixo consumo de recursos
- APIs simples e intuitivas

## Instalação

```bash
composer require rfschubert/php-credential-detector
```

### Configuração para ONNX Runtime (Opcional)

Para habilitar a detecção baseada em IA, é recomendado instalar o ONNX Runtime para PHP:

```bash
# Instalar a dependência da extensão FFI
sudo apt-get install php-ffi

# Instalar a biblioteca ONNX Runtime
# Veja instruções completas em ONNX_SETUP.md
```

Consulte o arquivo [ONNX_SETUP.md](ONNX_SETUP.md) para instruções detalhadas sobre como configurar o ONNX Runtime.

**Nota**: Mesmo sem o ONNX Runtime, a biblioteca funcionará usando o método de fallback baseado em expressões regulares.

## Uso Básico

```php
<?php

use RfSchubert\CredentialDetector\Detector;

$detector = new Detector();

$texto = "Minha senha é X#9pL@7!2ZqR e meu usuário é joao123";
$resultado = $detector->detect($texto);

if ($resultado->hasCredential()) {
    echo "Credencial detectada com confiança: " . $resultado->getConfidence() . "\n";
    echo "Credenciais encontradas: " . implode(", ", $resultado->getMatches()) . "\n";
} else {
    echo "Nenhuma credencial detectada";
}
```

## Integração com Laravel

### Configuração

Publique o arquivo de configuração:

```bash
php artisan vendor:publish --provider="RfSchubert\CredentialDetector\Laravel\Providers\CredentialDetectorServiceProvider"
```

Algumas das opções disponíveis no arquivo de configuração:

```php
// config/credential-detector.php

return [
    // Limiar de confiança para considerar uma string como credencial (0.0 a 1.0)
    'confidence_threshold' => env('CREDENTIAL_DETECTOR_THRESHOLD', 0.7),
    
    // Padrões personalizados de regex (null para usar os padrões default)
    'patterns' => null,
    
    // Pré-carregar o modelo de IA para melhor performance
    'preload_model' => env('CREDENTIAL_DETECTOR_PRELOAD', false),
];
```

### Uso com Facade

```php
<?php

use RfSchubert\CredentialDetector\Laravel\Facades\CredentialDetector;

$texto = "API_KEY=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6";
$resultado = CredentialDetector::detect($texto);

if ($resultado->hasCredential()) {
    // Trate a credencial encontrada
}
```

## Sistema de Dupla Validação

Esta biblioteca utiliza um inovador sistema de dupla validação:

1. **Expressões Regulares**: Detecta padrões conhecidos de credenciais usando regex otimizados
2. **Inteligência Artificial**: Utiliza um modelo ONNX pré-treinado para detectar credenciais através de aprendizado de máquina

O sistema sempre escolhe a validação com maior nível de confiança, garantindo assim melhor precisão e reduzindo falsos positivos.

### Como Funciona

Por padrão, a biblioteca tentará utilizar a detecção baseada em IA se o ONNX Runtime estiver disponível. Caso contrário, ou se ocorrer um erro durante a inferência do modelo, a biblioteca recorrerá ao método baseado em expressões regulares.

Para forçar a utilização do modelo de IA, certifique-se de instalar o ONNX Runtime e configurar o ambiente conforme descrito em [ONNX_SETUP.md](ONNX_SETUP.md).

## Contribuição

Contribuições são bem-vindas! Por favor, sinta-se à vontade para enviar um Pull Request.

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo LICENSE.md para mais detalhes.