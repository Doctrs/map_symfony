// директива для расчета размера картинки
app.directive('styleImage', function(){
    return {
        restrict: 'A',
        link: function(scope, elem) {
            elem.on('load', function() {
                scope.imw = $(this).width();
                scope.imh = $(this).height();
                scope.change();
                setTimeout(function(){
                    scope.$apply();
                }, 100);
            });
        }
    };
});