FROM php:7.2-fpm
MAINTAINER Przemyslaw Lis <przemek@concertoplatform.com>

ARG CRAN_MIRROR=https://cloud.r-project.org/

RUN apt-get update -y \
 && apt-get -y install gnupg \
 && echo "deb http://cran.rstudio.com/bin/linux/debian stretch-cran35/" | tee -a /etc/apt/sources.list \
 && apt-key adv --no-tty --keyserver keys.gnupg.net --recv-key 'E19F5F87128899B192B1A2C2AD5F960A256A04AF' \
 && apt-get update -y \
 && apt-get -y install \
    cron \
    curl \
    gettext \
    git \
    libcurl4-openssl-dev \
    libmariadbclient-dev \
    libxml2-dev \
    libssl-dev \
    locales \
    mysql-client \
    nginx \
    procps \
    r-base \
    r-base-dev \
    unzip \
    wget \
    zip \
 && sed -i 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen \
 && locale-gen "en_US.UTF-8" \
 && docker-php-ext-install \
    curl \
    json \
    pdo \
    pdo_mysql \
    posix \
    sockets \
    xml \
    zip

COPY . /usr/src/concerto/
COPY build/php/php.ini /usr/local/etc/php/php.ini
COPY build/nginx/nginx.conf /etc/nginx/nginx.conf
COPY build/nginx/concerto.conf /etc/nginx/sites-available/concerto.conf
COPY build/php-fpm/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY build/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf
ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh /

RUN Rscript -e "install.packages(c('session','RMySQL','jsonlite','catR','digest','ggplot2','base64enc','rjson','httr'), repos='$CRAN_MIRROR')" \
 && R CMD INSTALL /usr/src/concerto/src/Concerto/TestBundle/Resources/R/concerto5 \
 && chmod +x /wait-for-it.sh \
 && php /usr/src/concerto/bin/console concerto:r:cache \
 && crontab -l | { cat; echo "* * * * * /usr/local/bin/php /usr/src/concerto/bin/console concerto:schedule:tick --env=dev >> /var/log/cron.log 2>&1"; } | crontab - \
 && crontab -l | { cat; echo "0 0 * * * /usr/local/bin/php /usr/src/concerto/bin/console concerto:sessions:clear --env=dev >> /var/log/cron.log 2>&1"; } | crontab - \
 && crontab -l | { cat; echo "*/5 * * * * /usr/local/bin/php /usr/src/concerto/bin/console concerto:sessions:log --env=dev >> /var/log/cron.log 2>&1"; } | crontab - \
 && rm -f /etc/nginx/sites-available/default \
 && rm -f /etc/nginx/sites-enabled/default \
 && ln -fs /etc/nginx/sites-available/concerto.conf /etc/nginx/sites-enabled/concerto.conf

RUN mkdir -p /usr/src/dmtcp \
 && cd /usr/src/dmtcp \
 && git clone -b master https://github.com/dmtcp/dmtcp.git /usr/src/dmtcp \
 && ./configure --prefix=/usr && make -j 2 && make install

RUN echo 'deb http://ftp.debian.org/debian stretch-backports main' | tee /etc/apt/sources.list.d/backports.list \
 && apt-get update -y \
 && apt-get install -y python-certbot-nginx -t stretch-backports \
 && mkdir -p /var/www/html \
 && chown -R www-data:www-data /var/www/html \
 && crontab -l | { cat; echo "0 0 10 * * /usr/bin/certbot renew"; } | crontab -
 
EXPOSE 80 9000
WORKDIR /usr/src/concerto