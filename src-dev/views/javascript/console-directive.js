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
                    // Note: -16 is a workaround
                    if (consoleElem.scrollTop() >= consoleElem.prop("scrollHeight") - consoleElem.outerHeight(true) - 16) {
                        trackBottom = true;
                    } else {
                        trackBottom = false;
                    }
                });

                $scope.onKeyPress = function($event) {
                    //console.log($event);
                    $scope.sendchar({event: $event});
                }

                var maximized = false;

                $scope.maximize = function() {
                    if (!maximized) {
                        // Note: -16 is a workaround
                        consoleElem.height($(window).height() - jQuery('.console-footer').outerHeight() - element.find('.console-tab').outerHeight() - 16);
                        consoleElem.css("max-height", "none");
                        maximized = true;
                    } else {
                        consoleElem.css('height', '');
                        consoleElem.css("max-height", '');
                        maximized = false;
                    }
                }
            }
        };
    });
