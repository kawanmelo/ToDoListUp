# Usa uma imagem do PHP com Apache
FROM php:8.2-apache

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

# Conceder permissões corretas para o Apache acessar o diretório público e os arquivos gerados
RUN chown -R www-data:www-data /var/www/html/public /var/www/html/var

# Define a variável de ambiente para produção
ENV APP_ENV=prod

# Configura Apache para rodar na pasta public/
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Habilita o módulo de reescrita do Apache para Symfony
RUN a2enmod rewrite && service apache2 restart

# Limpa o cache
RUN php bin/console cache:clear

# Exposição da porta padrão do Apache
EXPOSE 80

# Comando para iniciar o servidor Apache
CMD ["apache2-foreground"]
