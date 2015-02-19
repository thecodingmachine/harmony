<?php
namespace Harmony\React;


use Ratchet\Wamp\Topic;
use React\ChildProcess\Process;

class Console implements \JsonSerializable {

    private $name;
    private $process;
    private $output;

    /**
     * @var Topic
     */
    private $topic;

    public function __construct($name, Process $process, $sizeLimit = 262144)
    {
        $this->name = $name;
        $this->process = $process;

        $process->stdout->on('data', function($output) use ($sizeLimit) {

            $this->output .= $output;
            $this->output = substr($this->output, 0-$sizeLimit);

            if ($this->topic !== null) {
                $this->topic->broadcast(json_encode([
                    'event' => 'output',
                    'name' => $this->name,
                    'output' => $output
                ]));
            }
        });

        $process->stderr->on('data', function($output) use ($sizeLimit) {

            $this->output .= $output;
            $this->output = substr($this->output, 0-$sizeLimit);

            if ($this->topic !== null) {
                $this->topic->broadcast(json_encode([
                    'event' => 'output',
                    'name' => $this->name,
                    'output' => $output,
                    'error' => true
                ]));
            }
        });
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
}
