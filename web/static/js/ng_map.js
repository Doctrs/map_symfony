var app = angular.module('app', []).config(function($interpolateProvider){
    $interpolateProvider.startSymbol('{[').endSymbol(']}');
});

app.controller('main', function ($scope) {

    $scope.coordinates = [];

    $scope.click = function(event){
        if(editMap){
            return;
        }
        var coord = getCoords(event);
        coord.name = Math.floor(coord.x / pix.x) + '/' + Math.floor(coord.y / pix.y)
        $scope.coordinates.push(coord)
    }

    $scope.delete = function(id){
        $scope.coordinates.splice(id,1);
    }

    var pix = {
        x: 0,
        y: 0
    };

    var editMap = false;
    $scope.urlYaIm = '';
    $scope.mapEditText = 'Нажмите для начала ввода координат';
    $scope.toogleMap = function(){
        editMap = !editMap;
        if(editMap){
            $scope.mapEditText = 'Нажмите для начала ввода координат';
            map.addControls();
        } else {
            var url = [];
            var params = map.getParams();
            for(var i in params){
                url.push(i + '=' + params[i]);
            }
            $scope.urlYaIm = 'http://static-maps.yandex.ru/1.x/?size=600,400&' + url.join('&');
            $scope.mapEditText = 'Нажмите для выбора места на карте';
            map.removeControls();
        }
    }

    $scope.change = function(){
        var array_x = [];
        pix.x = $scope.imw / $scope.sx;
        for(var i = 0 ; i < $scope.sx ; i++){
            array_x.push(pix.x * i);
        }
        $scope.array_x = array_x;
        var array_y = [];
        pix.y = $scope.imh / $scope.sy;
        for(var i = 0 ; i < $scope.sy ; i++){
            array_y.push(pix.y * i);
        }
        $scope.array_y = array_y;
        if($scope.coordinates) {
            $scope.coordinates = $scope.coordinates.map(function (el) {
                el.name = Math.floor(el.x / pix.x) + '/' + Math.floor(el.y / pix.y);
                return el;
            })
        }
    }









    function getCoords (mouseEvent) {
        var result = {
            x: 0,
            y: 0
        };

        if (!mouseEvent) {
            mouseEvent = window.event;
        }
        if (mouseEvent.pageX || mouseEvent.pageY) {
            result.x = mouseEvent.pageX;
            result.y = mouseEvent.pageY;
        } else if (mouseEvent.clientX || mouseEvent.clientY) {
            result.x = mouseEvent.clientX + document.body.scrollLeft +
            document.documentElement.scrollLeft;
            result.y = mouseEvent.clientY + document.body.scrollTop +
            document.documentElement.scrollTop;
        }
        if (mouseEvent.target) {
            var offEl = mouseEvent.target;
            var offX = 0;
            var offY = 0;

            var van_up_vars = false;
            if (typeof(offEl.offsetParent) != "undefined") {
                while (offEl) {
                    if(!van_up_vars && offEl.id == 'main_el'){
                        van_up_vars = true;
                    }

                    if(van_up_vars) {
                        offX += offEl.offsetLeft;
                        offY += offEl.offsetTop;
                    }
                    offEl = offEl.offsetParent;
                }
            }
            else {
                offX = offEl.x;
                offY = offEl.y;
            }
            result.x -= offX;
            result.y -= offY;
        }
        return result;
    }


    $scope.$watch('sx', function(oldv, newv) {
        if(oldv == newv){
            return;
        }
        $scope.change();
    });
    $scope.$watch('sy', function(oldv, newv) {
        if(oldv == newv){
            return;
        }
        $scope.change();
    });
});

