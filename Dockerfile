FROM wyveo/nginx-php-fpm:php80
RUN rm /bin/sh && ln -s /bin/bash /bin/sh
RUN apt-key adv --fetch-keys 'https://packages.sury.org/php/apt.gpg' > /dev/null 2>&1
RUN apt-get update -y && apt-get install -y iputils-ping npm curl ffmpeg

ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 14.17.4

WORKDIR $NVM_DIR

RUN curl https://raw.githubusercontent.com/creationix/nvm/master/install.sh | bash \
    && . $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

ENV NODE_PATH $NVM_DIR/versions/node/v$NODE_VERSION/lib/node_modules
ENV PATH      $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

RUN npm install -g npm@6.14.14
#RUN curl -fsSL https://deb.nodesource.com/setup_current.x | bash -
#RUN apt-get install -y nodejs 

#COPY php.ini /etc/php/8.0/cli/php.ini
#COPY nginx.conf /etc/nginx/nginx.conf
COPY php/www.conf /etc/php/8.0/fpm/pool.d/www.conf
COPY nginx.conf /etc/nginx/nginx.conf
COPY vhost.conf /etc/nginx/conf.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

WORKDIR /var/www
COPY . /var/www
RUN chmod -R a+x node_modules

ENV MIX_APP_ENV=production
#ENV MIX_DIGITALOCEAN_SPACES_URL_PUBLIC=https://morin.nyc3.digitaloceanspaces.com/
ENV MIX_PUSHER_APP_KEY=myappkey
ENV MIX_PUSHER_APP_CLUSTER=mt1

#ENV NODE_OPTIONS=--max_old_space_size=2048
#RUN npm install


#RUN composer update
RUN composer install
#RUN npm run dev
RUN npm run production

RUN php artisan config:clear
#RUN php artisan config:cache
RUN php artisan view:clear
RUN php artisan route:clear

EXPOSE 8358
EXPOSE 6001
EXPOSE 80

COPY laravel.log /var/www/storage/logs
RUN chmod -R 777 /var/www/storage