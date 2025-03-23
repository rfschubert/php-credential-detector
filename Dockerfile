FROM php:8.1-cli

# Instalar dependências
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /app

# Copiar apenas arquivos necessários para atualizar o Composer
COPY composer.json .

# Atualizar composer.lock
RUN composer update --no-scripts --no-autoloader -v

# Copiar o resto dos arquivos do projeto
COPY . .

# Instalar dependências do Composer
RUN composer install -v

# Verificar se a biblioteca ONNX Runtime está disponível (será 'NÃO')
RUN echo "Verificando disponibilidade da biblioteca ONNX Runtime" && \
    php -r "echo 'Classe ORT\Session existe: ' . (class_exists('\\ORT\\Session') ? 'SIM' : 'NÃO') . PHP_EOL;" && \
    echo "O serviço usará o fallback baseado em expressões regulares"

# Comando de execução padrão
CMD ["php", "examples/test_onnx.php"] 