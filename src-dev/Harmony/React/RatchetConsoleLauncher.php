<?php
namespace Harmony\React;


use Guzzle\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use React\EventLoop\LoopInterface;

/**
 * This class is used to receive HTTP methods asking to RUN some commands.
 * Commands run can be followed using the RatchetConsole application to publish/subscribe to output.
 *
 * Answers the /run URL.
 */
class RatchetConsoleLauncher implements HttpServerInterface {

    /**
     * @var RatchetConsole
     */
    private $consoleRepository;

    function __construct(ConsoleRepository $consoleRepository)
    {
        $this->consoleRepository = $consoleRepository;
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        // TODO: Implement onClose() method.
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    /**
     * @param \Ratchet\ConnectionInterface $conn
     * @param \Guzzle\Http\Message\RequestInterface $request null is default because PHP won't let me overload; don't pass null!!!
     * @throws \UnexpectedValueException if a RequestInterface is not passed
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
    {

        //var_dump($request->getUrl(true));
        //var_dump($request->getUrl(true)->getQuery()->get('command'));

        $name = $request->getUrl(true)->getQuery()->get('name');
        $command = $request->getUrl(true)->getQuery()->get('command');

        $conn->send("Running process '".$command."'");
        error_log("Running process '".$command."'");

        $this->consoleRepository->launchConsole($name, $command);

        $conn->close();

    }

    /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $from, $msg)
    {
        // TODO: Implement onMessage() method.
    }
}
