Инструкция по установке
========================

    # клонируем из репозитория и обновляем composer
    git clone https://github.com/FaustVlll/map_symfony.git .
    composer update

    # устанавливаем права на папки
    HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
    sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/uploads/maps
    sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/uploads/maps

    # очистка кэша
    php app/console cache:clear --env=dev && app/console cache:clear --env=prod

    # Создаем базу
    php app/console doctrine:database:create
    php app/console doctrine:schema:update --force

