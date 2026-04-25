FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx \
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

RUN docker-php-ext-install pdo

RUN pip3 install --break-system-packages \
    opencv-python-headless \
    numpy \
    qrcode[pil] \
    pillow

RUN ln -sf /usr/bin/python3 /usr/bin/python

COPY docker/nginx.conf /etc/nginx/sites-available/default

COPY . /var/www/html/

RUN mkdir -p /var/www/html/data \
             /var/www/html/uploads/scans \
             /var/www/html/uploads/qr \
             /var/www/html/temp \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/data \
                    /var/www/html/uploads \
                    /var/www/html/temp

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
