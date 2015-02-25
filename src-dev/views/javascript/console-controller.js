consoleApp.controller('ConsoleController', ['$scope', function($scope) {
    $scope.consoles = {};
    $scope.selectedConsole = null;
    /*    'learn angular': {name:'learn angular', output:[{text:"toto"}], "status":"running"},
        'composer': {name:'composer', output:[{text:"toto", error:true}], "status":"running"}
    };
    $scope.selectedConsole = "composer";*/
    $scope.status = 'connecting'; // 3 statuses: "connecting", "connected", "disconnected"

    $scope.safeApply = function(fn) {
        var phase = this.$root.$$phase;
        if(phase == '$apply' || phase == '$digest') {
            if(fn && (typeof(fn) === 'function')) {
                fn();
            }
        } else {
            this.$apply(fn);
        }
    };

    /**
     * Adds a new console to the list of consoles (in running state)
     *
     * @param name
     */
    var newconsole = function(name) {
        $scope.consoles[name] = {
            "name": name,
            "output": [{text:"", error:false}],
            "status": "running"
        };
        $scope.selectedConsole = name;
    };

    /**
     * Adds some output to the console.
     *
     * @param name
     * @param output
     */
    var outputToConsole = function(name, output, error) {
        var outputLines = $scope.consoles[name].output;
        var outputStr = output;
        while (outputStr.indexOf("\n") != -1) {
            var index = outputStr.indexOf("\n");
            var toAddStr = outputStr.substr(0, index+1);
            outputLines[outputLines.length-1].text += toAddStr;
            outputLines[outputLines.length-1].error = error;
            outputLines.push({text:"", error:error});
            outputStr = outputStr.substr(index+1);
        }
        outputLines[outputLines.length-1].text += outputStr;
    };

    var endconsole = function(name, exitCode, terminationSignal) {
        $scope.consoles[name].status = (exitCode==0)?"stopped":"error";
        $scope.consoles[name].exitCode = exitCode;
        $scope.consoles[name].terminationSignal = terminationSignal;
    };

    // Whether the user is changing page or not.
    var exiting = false;

    var conn = new ab.Session('ws://localhost:'+harmonyWsPort+'/console',
        function() {
            $scope.status = 'connected';
            conn.subscribe('console_main', function(topic, data) {

                $scope.safeApply(function() {

                    var msg = angular.fromJson(data);
                    //console.log(msg);
                    if (msg.event == "init") {
                        angular.forEach(msg.consoles, function(console) {
                            newconsole(console.name);
                            outputToConsole(console.name, console.output);
                        });
                    } else if (msg.event == "newconsole") {
                        newconsole(msg.name);
                    } else if (msg.event == "endconsole") {
                        endconsole(msg.name, msg.exitCode, msg.terminationSignal);
                    } else if (msg.event == "output") {
                        console.log(msg);
                        outputToConsole(msg.name, msg.output, msg.error);
                    }

                });

            });
        },
        function() {
            if (!exiting) {
                console.warn('WebSocket connection closed');
                $scope.safeApply(function() {
                    $scope.status = 'disconnected';
                });
            }
        },
        {'skipSubprotocolCheck': true}
    );

    jQuery(window).on('beforeunload', function(){
        conn.close();
        exiting = true;
    });

    $scope.kill = function(name) {

        conn.call("kill", name).then(function (result) {
            //console.log("YOUHOU!");
        }, function(error) {
            console.error(error);
        });

        $scope.consoles[name].status = "stopped";
    };

    $scope.remove = function(name) {
        $scope.kill(name);
        delete $scope.consoles[name];
    };

    $scope.select = function(name) {
        if ($scope.selectedConsole == name) {
            $scope.selectedConsole = null;
        } else {
            $scope.selectedConsole = name;
        }
    };

    $scope.minimize = function() {
        $scope.selectedConsole = null;
    };

    $scope.onkey = function($event) {
        // A key is pressed in the selected console.
        // Let's send it back!
        //console.log($event);

        conn.call("keyPressed", $scope.selectedConsole, $event.charCode, $event.which, $event.ctrlKey, $event.altKey, $event.shiftKey).then(function (result) {
            //console.log("Key pressed!");
        }, function(error) {
            console.error(error);
        });
    }

}]);
