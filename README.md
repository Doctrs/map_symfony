
Создание карт с координатам
========================
<ul>
    <li>
        <a href="#descr">Краткое описание</a>
    </li>
    <li>
        <a href="#install">Инструкция по установке</a>
    </li>
    <li>
        <a href="#create">Реализация</a>
    </li>
</ul>

<h1 id="descr">Краткое описание</h1>

Приложение для добавления координат на карту (собственную или Я.Карту)
С возможностью скачинваия карты (с сеткой и координатами) и координат.

Вначале создаем задание.

После добавляем к нему карту - при добавлении если есть своя карта то ее можно загрузить,
если поле оставить пустым, то будет предоставлен интерфейс Я.Карт

поля "Квадратов по Х" и "по Y" в последствии можно будет поменять.

<h3>При загрузке карты</h3>
сразуже можно добавлять координаты. Они появляются слева от карты (или снизу если карта большая)
Также можно менять количество квадратов по X и Y (изменяются динамически)
После добавления точек на карту будет сгенерировано изображение карты с точками и нанесенной сеткой
которое можно скачать.

<h3>При использовании интерфейса Я.Карт</h3>
Вначале нужно найти необходимую местность. После (кнопкой под картой) перейти к добавлению координат

Если местность неправильная то всегда можно перейти к редактированию местности.

При переходе к добавлению координат получаем ссылку на изображение карты. без него возможности сохранить и посмотреть результат отсутсвует (нет кнопки и проверка в symfony)

После - действия такиеже как и при загрузке карты.
<br><i>Иногда красные точки на карте при прорисовке gd меняют цвет на черный<br>
такео происходит судя по всему из-за картинки (чтото там с количеством цветов не так)<br>
Начал копать, но разбираться оказалось слишком долго, да и работа с графикой не требовалась, поэтому баг остался</i>


Все карты в будущем можно посмотреть, и удалить.
При удалении удаляются также и изображения карт чтобы не занимать место на диске.



<h1 id="install">Инструкция по установке</h1>

Папка для проекта в примере

    /var/www/maps/

клонируем из репозитория и обновляем composer

    cd /var/www/maps/
    git clone https://github.com/FaustVlll/map_symfony.git .
    composer update

устанавливаем права на папки

    mkdir -p web/uploads/maps/cache
    mkdir -p web/uploads/maps/coords
    HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
    sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/uploads/maps web/uploads/maps/cache web/uploads/maps/coords
    sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/uploads/maps web/uploads/maps/cache web/uploads/maps/coords

Создаем базу

    php app/console doctrine:database:create && php app/console doctrine:schema:update --force

чистка кэша

    php app/console cache:clear --env=prod

Добавляем в hosts строчку

    127.0.0.1       maps.local

Все готово - по адресу <a href="http://maps.local" target="_blank">maps.local</a> у нас есть рабочее приложение<br>
debug находится здесь - <a href="http://maps.local/app_dev.php" target="_blank">maps.local/app_dev.php</a>

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

<h1 id="create">Реализация</h1>
<ul>
    <li>
        <a href="http://symfony.com/" target="_blank">Symfony 2.5</a> - backend
    </li>
    <li>
        <a href="http://www.mysql.com/" target="_blank">MySQL 5.5.40</a> - database
    </li>
    <li>
        <a href="https://angularjs.org/" target="_blank">AngularJS 1.3.3</a> - frontend
    </li>
    <li>
        <a href="http://getbootstrap.com/" target="_blank">Bootstrap 3.3.1</a> - styles
    </li>
    <li>
        <a href="http://libgd.bitbucket.org/" target="_blank">PHP Gd</a> - draw images
    </li>
</ul>
