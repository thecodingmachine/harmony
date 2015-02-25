<?php
namespace Harmony\React;


use Ratchet\Wamp\Topic;
use React\ChildProcess\Process;

class Console implements \JsonSerializable {

    private $name;
    private $process;
    private $output;
    private $sizeLimit;

    /**
     * @var Topic
     */
    private $topic;

    public function __construct($name, Process $process, $sizeLimit = 262144)
    {
        $this->name = $name;
        $this->process = $process;
        $this->sizeLimit = $sizeLimit;

        $process->stdout->on('data', function($output) {
            $this->broadcastOutput($output, false);
        });

        $process->stderr->on('data', function($output) {
            $this->broadcastOutput($output, true);
        });
    }

    /**
     * Sends some output to the browser.
     *
     * @param string $output
     * @param bool $error
     */
    private function broadcastOutput($output, $error = false) {
        $this->output .= $output;
        $this->output = substr($this->output, 0-$this->sizeLimit);

        if ($this->topic !== null) {
            $this->topic->broadcast(json_encode([
                'event' => 'output',
                'name' => $this->name,
                'output' => $output,
                'error' => $error
            ]));
        }
    }

    public function registerTopic(Topic $topic) {
        $this->topic = $topic;
    }

    /**
     * Terminates the process.
     */
    public function terminate() {
        $this->process->terminate();
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'output' => $this->output
        ];
    }

    /**
     * Sends a key pressed signal to the process.
     *
     * @param string $charCode
     * @param string $which
     */
    public function sendKeyPress($charCode, $which, $ctrlKey, $altKey, $shiftKey) {
        //$process->stdin->resume() ???
        $key = $charCode?:$which;
        $char = chr($key);
        echo("SENDING CHAR ".chr($key)." - code ".$key.". Ctrl: $ctrlKey\n");

        //$this->process->stdout->write(chr($key));

        // If CTRL-C
        if ($ctrlKey == true && strtolower($char) == 'c') {
            // Send SIGINT to the process:
            echo("Sending ctrl-c (SIGTERM) signal\n");
            //$this->process->terminate(SIGINT);
            $this->process->terminate(SIGTERM);
            return;
        }

        // Translate 13 to 10
        if ($key == 13) {
            $this->process->stdin->write(chr(10));
            $this->broadcastOutput(chr(10));
        } else {
            $this->process->stdin->write($char);
            $this->broadcastOutput($char);
        }
    }
}
