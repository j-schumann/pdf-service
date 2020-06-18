FROM vrokdd/php:symfony

RUN pecl install xdebug-2.8.0
RUN docker-php-ext-enable xdebug

RUN apt-get install -y librabbitmq-dev
RUN pecl install amqp
RUN docker-php-ext-enable amqp

# latex-base for lualatex, latex-extra for makecmd, extra-utils for texfot, lang-german for ngerman, fonts-lmodern for font-warning
# lualatex needs the .texlive2018 as cache dir, the supervisorconfig sets /var/www as home for the queue process
RUN apt-get update && apt-get install -y --no-install-recommends --fix-missing texlive-latex-base texlive-latex-extra texlive-extra-utils texlive-lang-german texlive-luatex texlive-xetex lmodern && \
    mkdir -p /var/www/.texlive2018 && chown -R www-data:www-data /var/www/.texlive2018 && \
    mkdir -p /var/lib/texmf/luatex-cache && chown -R www-data:www-data /var/lib/texmf/luatex-cache && \
    mkdir -p /var/www/.texmf-var && chown www-data:www-data /var/www/.texmf-var

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
