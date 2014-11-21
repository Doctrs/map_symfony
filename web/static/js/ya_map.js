
var map = {
    'myMap': null,
    'startMap': function(ymaps) {
        this.myMap = new ymaps.Map('myMap', {
            center: [55.76, 37.64],
            zoom: 7,
            controls: [],
            behaviors: ['drag', 'scrollZoom']
        });
        this.addControls();
    },
    'addControls': function(){
        this.myMap.controls
            .add('default')
            .add('routeEditor');
        this.myMap.behaviors
            .enable('drag')
            .enable('scrollZoom');
    },
    'removeControls': function(){
        this.myMap.controls
            .remove('default')
            .remove('routeEditor')
        this.myMap.behaviors
            .disable('drag')
            .disable('scrollZoom');
    },
    cartTypes: {
        'yandex#map': 'map',
        'yandex#satellite': 'sat',
        'yandex#hybrid': 'sat,skl',
        'yandex#publicMap': 'pmap',
        'yandex#publicMapHybrid': 'pskl'
    },
    getParams: function(){
        return {
            'll': this.myMap.getCenter().reverse().join(','),
            'z': this.myMap.getZoom(),
            'l': this.cartTypes[this.myMap.getType()]
        };
    }
}

function startMap(ymaps){
    map.startMap(ymaps);
}