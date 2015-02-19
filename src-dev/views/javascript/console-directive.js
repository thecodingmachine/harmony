consoleApp.directive('console', function() {
        return {
            restrict: 'E',
            scope: {
                object: '=',
                kill: '&',
                remove: '&'
            },
            templateUrl: MoufInstanceManager.rootUrl+"src-dev/views/javascript/console-directive.html",
            link: function($scope, element, attrs) {


            }
        };
    })