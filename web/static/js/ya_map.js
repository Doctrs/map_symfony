
var map = {
    'myMap': null,
    // создаем карту
    'startMap': function(ymaps) {
        this.myMap = new ymaps.Map('myMap', {
            center: [55.76, 37.64],
            zoom: 7,
            controls: ['default', 'routeEditor'],
            behaviors: ['drag', 'scrollZoom']
        });
    },
    // добавляем элементы управление (отклчюение уставноки точек)
    'addControls': function(){
        this.myMap.controls
            .add('default')
            .add('routeEditor');
        this.myMap.behaviors
            .enable('drag')
            .enable('scrollZoom');
    },
    // удалем элементы управления (включение возможности нанесения координат)
    'removeControls': function(){
        this.myMap.controls
            .remove('default')
            .remove('routeEditor')
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