Инструкция по установке
========================
Папка для проекта в примере

    /var/www/maps/

клонируем из репозитория и обновляем composer

    cd /var/www/maps/
    git clone https://github.com/FaustVlll/map_symfony.git .
    composer update
    
устанавливаем права на папки

    mkdir -p web/uploads/maps/
    HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
    sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/uploads/maps
    sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/uploads/maps
    
Создаем базу

    php app/console doctrine:database:create
    php app/console doctrine:schema:update --force
    
чистка кэша

    php app/console cache:clear --env=dev && app/console cache:clear --env=prod
    
Все готово.
И напоследок - конфиг апача

    <VirtualHost *:80>
    ServerName maps.local
    DocumentRoot /var/www/maps/web
    DirectoryIndex app.php
    ErrorLog /var/log/apache2/joboard-error.log
    CustomLog /var/log/apache2/joborad-access.log combined
        <Directory "/var/www/maps/web">
            AllowOverride All
            Order Deny,Allow
            Allow from all
            Require all granted
        </Directory>
    </VirtualHost>


