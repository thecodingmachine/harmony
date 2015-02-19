<?php
namespace Harmony\React;

use Ratchet\Wamp\Topic;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

/**
 * This class contains the list of all open commands along the output that was passed to them.
 */
class ConsoleRepository {

    private $loop;

    /**
     * @var array<string, Console>
     */
    private $consoles = array();

    function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @var Topic
     */
    private $topic;

    public function registerMainTopic(Topic $topic) {
        if ($topic->getId() !=  'console_main') {
            throw new \Exception('Error, the topic registered with ConsoleRepository must be "console_main"');
        }

        foreach ($this->consoles as $console) {
            $console->registerTopic($topic);
        }

        $this->topic = $topic;
    }

    /**
     * @param string $name
     * @param string $command
     */
    public function launchConsole($name, $command) {
        $finalName = $name;
        if (isset($this->consoles[$name])) {
            $i = 1;
            while (isset($this->consoles[$name." ($i)"])) {
                $i++;
            }
            $finalName = $name." ($i)";
        }

        $process = new Process($command);
        $process->start($this->loop);

        $process->on('exit', function($exitCode, $terminationSignal) use ($finalName) {
            unset($this->consoles[$finalName]);

            if ($this->topic) {
                $this->topic->broadcast(json_encode([
                    'event' => 'endconsole',
                    'name' => $finalName,
                    'exitCode' => $exitCode,
                    'terminationSignal' => $terminationSignal
                ]));
            }

        });

        $this->consoles[$finalName] = new Console($finalName, $process);

        if ($this->topic) {
            $this->consoles[$finalName]->registerTopic($this->topic);
            $this->topic->broadcast(json_encode([
                'event' => 'newconsole',
                'name' => $finalName
            ]));
        }

    }

    public function getConsoles() {
        return $this->consoles;
    }

    /**
     * @param string $processName Kills console $processName
     */
    public function killProcess($processName) {
        if (isset($this->consoles[$processName])) {
            $this->consoles[$processName]->terminate();
            unset($this->consoles[$processName]);
        } else {
            error_log("Unable to kill process $processName. Maybe it is already killed?");
        }
    }
}