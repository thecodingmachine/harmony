consoleApp.directive('consoletab', function() {
        return {
            restrict: 'E',
            scope: {
                object: '=',
                kill: '&',
                remove: '&',
                select: '&',
                selected: '=',
                mode: '=',
                minimize: '&'
            },
            templateUrl: MoufInstanceManager.rootUrl+"src-dev/views/javascript/console-tab-directive.html",
            link: function($scope, element, attrs) {


            }
        };
    });
