
var map = {
    'myMap': null,
    // создаем карту
    'startMap': function(ymaps) {
        var myCoordSystem = new ymaps.projection.Cartesian([[-1, -1], [1, 1]]);
        var PhotoLayer = function () {
                return new ymaps.Layer(
                    function (tile, zoom) {
                        console.log(tile, zoom);
                        console.log('');
                        console.log('/app_dev.php/img/layout/1416772612/'+tile[0]+'/'+tile[1]+'/' + zoom + '/5/5.png');
                        return '/app_dev.php/img/layout/1416772612/'+tile[0]+'/'+tile[1]+'/' + zoom + '/5/5.png';
                    }
                )
            };
        ymaps.layer.storage.add('my#photo', PhotoLayer);
        ymaps.mapType.storage.add('my#photo', new ymaps.MapType(
            'Фото',
            ['my#photo']
        ));

        this.myMap = new ymaps.Map('myMap', {
            center: [0, 0],
            zoom: 1,
            type: 'my#photo',
            controls: [
                'zoomControl',
            ]
        }, {
            maxZoom: 5,
            minZoom: 1,
            projection: myCoordSystem
        });
    },
    // добавляем элементы управление (отклчюение уставноки точек)
    'addControls': function(){
        this.myMap.controls
            .add('zoomControl');
        this.myMap.behaviors
            .enable('drag')
            .enable('scrollZoom');
    },
    // удалем элементы управления (включение возможности нанесения координат)
    'removeControls': function(){
        this.myMap.controls
            .remove('zoomControl');
        this.myMap.behaviors
            .disable('drag')
            .disable('scrollZoom');
    },
    // параметр тип карты
    cartTypes: {
        'yandex#map': 'map',
        'yandex#satellite': 'sat',
        'yandex#hybrid': 'sat,skl',
        'yandex#publicMap': 'pmap',
        'yandex#publicMapHybrid': 'pskl'
    },
    // получение параметров для получения картинки
    getParams: function(){
        return {
            'll': this.myMap.getCenter().reverse().join(','),
            'z': this.myMap.getZoom(),
            'l': this.cartTypes[this.myMap.getType()]
        };
    }
}

// обертка для автозапуска
function startMap(ymaps){
    map.startMap(ymaps);
}