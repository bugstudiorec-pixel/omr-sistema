FROM php:8.2-apache

# Instala dependências do sistema para OpenCV e Python
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-dev \
    libgl1 \
    libglib2.0-0 \
    libsm6 \
    libxrender1 \
    libxext6 \
    libgomp1 \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP necessárias
RUN docker-php-ext-install pdo

# Instala bibliotecas Python
RUN pip3 install --break-system-packages \
    opencv-python-headless \
    numpy \
    qrcode[pil] \
    pillow

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# Configura o Apache para servir a pasta public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permissão para .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Copia o projeto para o container
COPY . /var/www/html/

# Cria pastas necessárias e ajusta permissões
RUN mkdir -p /var/www/html/data \
             /var/www/html/uploads/scans \
             /var/www/html/uploads/qr \
             /var/www/html/temp \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/data \
                    /var/www/html/uploads \
                    /var/www/html/temp

# Garante que python3 seja encontrado como 'python'
RUN ln -sf /usr/bin/python3 /usr/bin/python

EXPOSE 80
