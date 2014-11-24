var app = angular.module('app', []).config(function($interpolateProvider){
    // с twig работает плохо, поэтмоу заменяем ангуляровские {{ на {[
    $interpolateProvider.startSymbol('{[').endSymbol(']}');
});

app.controller('main', function ($scope) {

    $scope.rad = RADIUS;
    $scope.sx = GRID.x;
    $scope.sy = GRID.y;

    $scope.coordinates = [];
    $scope.setCoords = function(coords){
        $scope.coordinates = coords.map(function(el){
            el.coords[0] = Math.round(el.coords[0]);
            el.coords[1] = Math.round(el.coords[1]);
            return el;
        });
        $scope.coordGetName();
    };

    $scope.coordGetName = function(){
        var pix = {
            x: SIZE.x / $scope.sx,
            y: SIZE.y / $scope.sy
        };
        $scope.coordinates = $scope.coordinates.map(function (el) {
            el.name =
                String.fromCharCode(Math.floor(el.coords[0] / pix.x) + 65) +
                Math.floor(el.coords[1] / pix.y);
            return el;
        })
    }

    // выделение цветом КП
    $scope.alocate = function(index, onoff){
        map.allocationPlaceMark(index, onoff);
    }

    $scope.delete = function(key){
        $scope.coordinates.splice(key, 1);
        map.removePlaceMark(key);
    };

    // меняем радиус и обновляем радиусы всех КП
    $scope.changeRadius = function(){
        if($scope.rad) {
            map.radius.main = $scope.rad;
            map.updateRadius();
        }
    };

    // изменнеие размеров сетки и пересчет квадаратов координат
    $scope.change = function(){
        map.grid = [
            $scope.sx,
            $scope.sy
        ];
        map.updateLayers();
        if($scope.coordinates) {
            $scope.coordGetName();
        }
    };
    $scope.change();









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


    // Наблюдение и проверки
    $scope.$watch('rad', function(oldv, newv) {
        if(newv < 10){
            $scope.rad = 10;
            return;
        }
        if(newv > 100){
            $scope.rad = 100;
            return;
        }
        if(oldv == newv){
            return;
        }
        $scope.changeRadius();
    });
    $scope.$watch('sx', function(oldv, newv) {
        if(newv < 2){
            $scope.sx = 2;
            return;
        }
        if(newv > 20){
            $scope.sx = 20;
            return;
        }
        if(oldv == newv){
            return;
        }
        $scope.change();
    });
    $scope.$watch('sy', function(oldv, newv) {
        if(newv < 2){
            $scope.sy = 2;
            return;
        }
        if(newv > 20){
            $scope.sy = 20;
            return;
        }
        if(oldv == newv){
            return;
        }
        $scope.change();
    });
});

