
var map = {
    // карта
    'myMap': null,
    // сетка (x y)
    'grid':[0,0],
    // размеры изображения (соответсвуют координатной плоскости на карте)
    'coordinates': [0,0],
    // слой
    'layer': null,
    // класс карт
    'ymaps': null,
    // радиусы
    'radius': {},
    // название изображения
    'map_name': null,
    'startMap': function(ymaps) {
        // Задаем занчения по умолчанию
        this.coordinates = [
            SIZE.x,
            SIZE.y
        ]
        this.grid = [
            GRID.x,
            GRID.y
        ]
        this.map_name = MAP_NAME;
        this.ymaps = ymaps;
        this.radius = {
            'main': RADIUS,
            'zoom': RADIUS
        }
        var self = this;

        this.myMap = new ymaps.Map('myMap', {
            center: [this.coordinates[1] / 2, this.coordinates[0] / 2],
            zoom: 1,
            controls: [
                'zoomControl',
            ]
        }, {
            maxZoom: 5,
            minZoom: 1,
            projection: new ymaps.projection.Cartesian([
                [Math.max(SIZE.x, SIZE.y), 0],
                [0, Math.max(SIZE.x, SIZE.y)]
            ])
        });
        this.myMap.events.add('boundschange', function (event) {
            if (event.get('newZoom') != event.get('oldZoom')) {
                self.updateRadius();
            }
        });
        this.updateLayers();

        // присваиваем scope для взаимодействия с контроллером
        var scope = angular.element('[ng-controller="main"]').scope();
        this.myMap.events.add('click', function (e) {
            // получаем размер КП с учетом зума
            self.radius.zoom = self.radius.main * Math.pow(2, self.myMap.getZoom()-1);
            // так как карты лежат в другой плоскости (повернуты на 90 градусов для удобства расчета)
            // Левый верхний угол лежит в точке 0, 0 и находится она в плоскости I
            // (все ее координаты положительные) необходимо поменять местами x и y
            self.addPlacemark(e.get('coords').reverse());
            scope.setCoords(self.placeMarks);
        });
        // Если есть координаты (редактировнание существующей карты)
        // добавялем их
        for(var i in COORDS){
            this.addPlacemark(COORDS[i]);
        }
        scope.setCoords(self.placeMarks);
        setTimeout(function(){
            scope.$apply();
        }, 150);
    },
    // Добавляем КП на карту и в коллекцию
    addPlacemark: function(coords){
        var self = this;
        var obj = new this.ymaps.Placemark([coords[1], coords[0]], {}, {
            iconLayout: 'default#image',
            iconImageHref: '/static/img/ring.png',
            // размеры КП с учетом зума и указанных в форме пикселе
            iconImageSize: [self.radius.zoom, self.radius.zoom],
            iconImageOffset: [-(self.radius.zoom/2), -(self.radius.zoom/2)]
        });
        self.placeMarks.push({
            'object': obj,
            'coords': coords
        });
        self.myMap.geoObjects.add(obj);
    },
    // обновялем радиусы КП
    // необходимо при изменении радиуса, а также при масштабировании
    updateRadius: function(){
        this.radius.zoom = this.radius.main * Math.pow(2, this.myMap.getZoom()-1);
        for(var i in this.placeMarks) {
            this.placeMarks[i].object.options.set('iconImageSize', [this.radius.zoom, this.radius.zoom]);
            this.placeMarks[i].object.options.set('iconImageOffset', [-(this.radius.zoom / 2), -(this.radius.zoom / 2)]);
        }
    },
    // при наведении меняем картинку координаты на зеленый круг
    allocationPlaceMark: function(key, onoff){
        this.placeMarks[key].object.options.set('iconImageHref', '/static/img/ring' + (onoff ? '2' : '') + '.png')
    },
    // удаляем координату
    removePlaceMark: function(key){
        this.myMap.geoObjects.remove(this.placeMarks[key].object);
        this.placeMarks.splice(key, 1);
    },
    'placeMarks':[],
    // перерисовка слоя при изменении сетки
    // нормальной функции для обновления слоя я не нашел (нет примеров, а на QA советуют нерабочие функции)
    // поэтому для перерисовки удаляем слой и добавляем его заного.
    'updateLayers': function(){
        if(!this.myMap){
            return;
        }
        this.myMap.layers.removeAll();
        this.myMap.layers.add(new this.ymaps.Layer(
            '/app_dev.php/img/layout/' + this.map_name + '/%x/%y/%z/'+this.grid[0]+'/'+this.grid[1]+'.png'
            ,{
                notFoundTile: '/static/img/none.png'
            }
        ));
    }
}

// обертка для автозапуска
function startMap(ymaps){
    map.startMap(ymaps);
}