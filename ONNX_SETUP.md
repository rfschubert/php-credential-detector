# Configuração do ONNX Runtime para PHP Credential Detector

Este documento fornece instruções para configurar o ONNX Runtime no PHP Credential Detector.

## Sobre o ONNX Runtime

O ONNX (Open Neural Network Exchange) é um formato aberto para representar modelos de aprendizado de máquina. O ONNX Runtime é um motor de inferência de alto desempenho para modelos ONNX, permitindo executar modelos de IA diretamente no PHP.

## Requisitos

Para usar o ONNX Runtime com PHP Credential Detector, você precisa de:

- PHP 7.4 ou superior
- Extensão FFI do PHP
- Biblioteca ONNX Runtime (libonnxruntime)
- Extensão ONNX Runtime para PHP

## Instalação

### 1. Instalar a extensão FFI do PHP

A extensão FFI (Foreign Function Interface) permite que o PHP chame funções de bibliotecas externas.

```bash
# Para Ubuntu/Debian
sudo apt-get install php-ffi

# Para CentOS/RHEL
sudo yum install php-ffi

# Para macOS com Homebrew
brew install php

# Habilitar FFI no php.ini
echo "ffi.enable=true" >> /path/to/php.ini
```

### 2. Instalar a biblioteca ONNX Runtime

```bash
# Para Ubuntu/Debian (Linux x64)
mkdir -p /opt/onnxruntime
cd /opt/onnxruntime
wget https://github.com/microsoft/onnxruntime/releases/download/v1.16.0/onnxruntime-linux-x64-1.16.0.tgz
tar -xzf onnxruntime-linux-x64-1.16.0.tgz
rm onnxruntime-linux-x64-1.16.0.tgz
ln -s /opt/onnxruntime/onnxruntime-linux-x64-1.16.0/lib/libonnxruntime.so.1.16.0 /usr/lib/libonnxruntime.so
echo "/opt/onnxruntime/onnxruntime-linux-x64-1.16.0/lib" > /etc/ld.so.conf.d/onnxruntime.conf
ldconfig
```

### 3. Instalar a extensão ONNX Runtime para PHP

Instale a extensão [microsoft/onnxruntime-php](https://github.com/microsoft/onnxruntime-php):

```bash
git clone https://github.com/microsoft/onnxruntime-php.git
cd onnxruntime-php
phpize
./configure --enable-onnxruntime
make
make install
echo "extension=onnxruntime.so" > /path/to/php/conf.d/20-onnxruntime.ini
```

## Verificação da Instalação

Para verificar se a extensão ONNX Runtime está instalada corretamente:

```php
<?php
// Verificar se a extensão está carregada
var_dump(extension_loaded('onnxruntime'));

// Verificar se a classe ORT\Session está disponível
var_dump(class_exists('\\ORT\\Session'));
?>
```

## Configuração no PHP Credential Detector

O PHP Credential Detector está projetado para usar o ONNX Runtime automaticamente se estiver disponível. Caso contrário, ele recorrerá a um método baseado em expressões regulares.

Para usar o ONNX Runtime:

1. Verifique se o modelo ONNX e arquivos auxiliares estão baixados:
   - `credential_detector.onnx`
   - `credential_detector_config.json`
   - `credential_detector_vectorizer.json`
   - `credential_detector_patterns.json`

2. Inicialize o detector com a opção para pré-carregar o modelo:

```php
$detector = new \RfSchubert\CredentialDetector\Detector(0.7, null, true);
```

## Solução de Problemas

- **Erro de carregamento da extensão FFI**: Verifique se a extensão FFI está instalada e habilitada no php.ini.
- **Erro "Class ORT\Session not found"**: Verifique se a extensão ONNX Runtime está instalada corretamente.
- **Erro ao carregar o modelo**: Verifique se os arquivos do modelo estão no local correto e acessíveis.

## Desempenho

O uso do ONNX Runtime pode melhorar significativamente a precisão da detecção de credenciais, especialmente para casos complexos. No entanto, o método de fallback baseado em expressões regulares também oferece boa performance para a maioria dos casos comuns.

## Links Úteis

- [ONNX Runtime para PHP](https://github.com/microsoft/onnxruntime-php)
- [ONNX Runtime - GitHub](https://github.com/microsoft/onnxruntime)
- [ONNX - Site Oficial](https://onnx.ai/) 