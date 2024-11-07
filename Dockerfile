# Usa a imagem PHP 8.2 com suporte a Apache
FROM php:8.2-cli

# Instala as extensões PHP necessárias para Symfony e MySQL
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install intl pdo_mysql zip

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos da aplicação para o contêiner
COPY . .

# Instala as dependências da aplicação
RUN composer install --no-dev --optimize-autoloader

# Define as permissões da pasta var para permitir gravação
RUN chown -R www-data:www-data var

# Define a variável de ambiente para produção
ENV APP_ENV=prod

# Limpa o cache
RUN php bin/console cache:clear

# Expõe a porta 80, que é a porta padrão do servidor do PHP
EXPOSE 80

# Comando para rodar o servidor embutido do PHP
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
