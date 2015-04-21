validatorsApp.controller('ValidatorsController', ['$scope', '$http', '$sce', function($scope, $http, $sce) {
    $scope.validators = [
        /*{
            "code": "ok",
            "htmlMessage": "coucou ok"
        },
        {
            "code": "warn",
            "htmlMessage": "coucou warning"
        },
        {
            "code": "error",
            "htmlMessage": "coucou error"
        }*/
    ];

    $scope.pendingResponses = 0;

    $scope.displayErrors = true;
    $scope.displayWarnings = true;
    $scope.displaySuccesses = false;

    $scope.nbMax = function() {
        return $scope.validators.length + $scope.pendingResponses;
    }

    $scope.nbOk = function() {
        return $scope.validators.filter(function($item) { return $item.code == "ok"; }).length;
    }

    $scope.nbWarn = function() {
        return $scope.validators.filter(function($item) { return $item.code == "warn"; }).length;
    }

    $scope.nbError = function() {
        return $scope.validators.filter(function($item) { return $item.code == "error"; }).length;
    }

    $scope.percentOk = function() {
        return $scope.nbOk() / $scope.nbMax() * 100;
    }

    $scope.percentWarn = function() {
        return $scope.nbWarn() / $scope.nbMax() * 100;
    }

    $scope.percentError = function() {
        return $scope.nbError() / $scope.nbMax() * 100;
    }

    $scope.error = null;

    // Let's fetch initial data!

    $http.get(MoufInstanceManager.rootUrl+'validators/get_list').
        success(function(data, status, headers, config) {
            $scope.pendingResponses = data.length;

            data.classes.forEach(function(className) {
                $http.get(MoufInstanceManager.rootUrl+'validators/get_class?class='+className).
                    success(function(data2, status2, headers2, config2) {
                        data2.forEach(function(validator) {
                            validator.htmlMessage = $sce.trustAsHtml(validator.htmlMessage);
                            $scope.validators.push(validator);
                        });
                        $scope.pendingResponses --;
                    }).
                    error(function(data2, status2, headers2, config2) {
                        // Mark the output HTML as trusted
                        $scope.error = $sce.trustAsHtml(data2);
                        $scope.pendingResponses --;
                    });
            });

            data.instances.forEach(function(instanceName) {
                $http.get(MoufInstanceManager.rootUrl+'validators/get_instance?instance='+instanceName).
                    success(function(data2, status2, headers2, config2) {
                        data2.forEach(function(validator) {
                            validator.htmlMessage = $sce.trustAsHtml(validator.htmlMessage);
                            $scope.validators.push(validator);
                        });
                        $scope.pendingResponses --;
                    }).
                    error(function(data2, status2, headers2, config2) {
                        // Mark the output HTML as trusted
                        $scope.error = $sce.trustAsHtml(data2);
                        $scope.pendingResponses --;
                    });
            });
        }).
        error(function(data, status, headers, config) {
            // Mark the output HTML as trusted
            $scope.error = $sce.trustAsHtml(data);;
        });

}]);
