consoleApp.controller('ConsoleController', ['$scope', function($scope) {
    $scope.consoles = {
        'learn angular': {name:'learn angular', output:[{text:"toto"}], "status":"running"},
        'composer': {name:'composer', output:[{text:"toto", error:true}], "status":"running"}
    };
    $scope.selectedConsole = "composer";

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

    var conn = new ab.Session('ws://localhost:8001/console',
        function() {
            conn.subscribe('console_main', function(topic, data) {
                // This is where you would add the new article to the DOM (beyond the scope of this tutorial)
                //console.log('New article published to category "' + topic + '" : ' + data.title);
                //console.log('Topic '+topic);
                //console.log(data);

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
                        outputToConsole(msg.name, msg.output, msg.error);
                    }

                });

            });
        },
        function() {
            console.warn('WebSocket connection closed');
        },
        {'skipSubprotocolCheck': true}
    );

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
        $scope.selectedConsole = name;
    };

    $scope.minimize = function() {
        $scope.selectedConsole = null;
    };

    $scope.onkey = function($event) {
        // A key is pressed in the selected console.
        // Let's send it back!
        //console.log($event);

        conn.call("keyPressed", $scope.selectedConsole, $event.charCode, $event.which).then(function (result) {
            //console.log("Key pressed!");
        }, function(error) {
            console.error(error);
        });
    }

}]);
