# Detector de Padrões de Credenciais para PHP

Cliente PHP para o Detector de Padrões de Credenciais com integração para Laravel. Esta biblioteca permite identificar e proteger informações sensíveis em strings de texto.

## Objetivo

Este cliente PHP foi desenvolvido para consumir o modelo de machine learning treinado pelo [Detector de Padrões de Credenciais](https://github.com/rfschubert/credential-pattern-detector), oferecendo uma solução fácil de usar para aplicações PHP, especialmente em projetos Laravel.

## Características

- Detecção de diversos tipos de credenciais em texto (senhas, tokens, chaves de API, etc.)
- Integração simples com Laravel através de Facade e Service Provider
- Baixa taxa de falsos positivos
- Alta performance e baixo consumo de recursos
- APIs simples e intuitivas

## Instalação (Em Breve)

```bash
composer require rfschubert/php-credential-detector
```

## Uso Básico (Em Breve)

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

## Integração com Laravel (Em Breve)

### Configuração

Publique o arquivo de configuração:

```bash
php artisan vendor:publish --provider="RfSchubert\CredentialDetector\Laravel\CredentialDetectorServiceProvider"
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

## Em Desenvolvimento

Esta biblioteca está em desenvolvimento ativo. Em breve estará disponível para uso via Composer.

## Contribuição

Contribuições são bem-vindas! Por favor, sinta-se à vontade para enviar um Pull Request.

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo LICENSE.md para mais detalhes.