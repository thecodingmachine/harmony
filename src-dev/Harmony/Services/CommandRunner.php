<?php
namespace Harmony\Services;

/**
 * Runs a command.
 * The command output will be displayed in the Harmony console.
 */
class CommandRunner {

    /**
     * Runs the command passed in parameter.
     *
     * @param string $command
     * @param string $name
     */
    public static function run($command, $name = null) {
        if ($name == null) {
            $name = substr($command, 0, 30);
        }

        $client = new \GuzzleHttp\Client();
        $response = $client->get('http://localhost:'.getenv('HARMONY_WS_PORT').'/run?name='.urlencode($name).'&command='.urlencode($command).'&key='.getenv('SECURITY_KEY'));
    }
}
