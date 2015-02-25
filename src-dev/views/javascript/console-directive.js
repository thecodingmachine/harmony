consoleApp.directive('console', function() {
        return {
            restrict: 'E',
            scope: {
                object: '=',
                kill: '&',
                remove: '&',
                selected: '=',
                minimize: '&',
                sendchar: '&'
            },
            templateUrl: MoufInstanceManager.rootUrl+"src-dev/views/javascript/console-directive.html",
            link: function($scope, element, attrs) {
                var trackBottom = true;
                var consoleElem = element.find('.console');
                $scope.$watchCollection('object.output', function (newVal, oldVal) {
                    //console.log(newVal, oldVal);
                    if (trackBottom === true) {
                        consoleElem.scrollTop(consoleElem.prop("scrollHeight"));
                    }
                });

                // Let's keep the console at the bottom if the user puts the scroll at the bottom.
                consoleElem.scroll(function() {
                    if (consoleElem.scrollTop() == consoleElem.prop("scrollHeight") - consoleElem.outerHeight(true)) {
                        trackBottom = true;
                    } else {
                        trackBottom = false;
                    }
                });

                $scope.onKeyPress = function($event) {
                    //console.log($event);
                    $scope.sendchar({event: $event});
                }

            }
        };
    });
