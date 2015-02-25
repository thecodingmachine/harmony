<?php
namespace Harmony\MainConsole;


use React\ChildProcess\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunHarmonyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Start the Harmony server. Harmony runs on 2 ports that you can configure using optional parameter')
            ->addOption(
                'http_port',
                'p',
                InputOption::VALUE_REQUIRED,
                'The HTTP port of Harmony',
                8000
            )
            ->addOption(
                'ws_port',
                'wp',
                InputOption::VALUE_REQUIRED,
                'The Websocket port of Harmony',
                8001
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $http_port = $input->getOption('http_port');
        $ws_port = $input->getOption('ws_port');

        $pimple = new \Pimple();
        $pimple['port'] = $ws_port;

        $pimple['loop'] = $pimple->share(function() {
            return \React\EventLoop\Factory::create();
        });

        /*$pimple['reactor'] = $pimple->share(function($pimple) {
            $socket = new Reactor($pimple['loop']);
            $socket->listen($pimple['port'], '127.0.0.1');
            return $socket;
        });*/


        /*$pimple['ioserver'] = $pimple->share(function($pimple) {
            return new \Ratchet\Server\IoServer(new DisplayActivityApp(), $pimple['loop']);
        });*/

        $pimple['consoleRepository'] = $pimple->share(function($pimple) {
            return new \Harmony\React\ConsoleRepository($pimple['loop']);
        });

        $pimple['ratchetConsoleHttp'] = $pimple->share(function($pimple) {
            return new \Harmony\React\RatchetConsoleLauncher($pimple['consoleRepository']);
        });

        $pimple['ratchetConsole'] = $pimple->share(function($pimple) {
            return new \Harmony\React\RatchetConsole($pimple['consoleRepository']);
        });

        $pimple['ratchetApp'] = $pimple->share(function($pimple) {
            // TODO: put 'hostname' instead of "localhost"
            $app =  new \Ratchet\App('localhost', $pimple['port'], '127.0.0.1', $pimple['loop']);
            $app->route('/console', $pimple['ratchetConsole']);
            // FIXME: * is a security issue! Add security to the /run command
            $app->route('/run', $pimple['ratchetConsoleHttp'], ['*']);
            return $app;
        });



        // Let's start the internal web server.
        $process = new Process(PHP_BINARY.' -S localhost:'.$http_port.' src/internal_web_server_router.php');
        $process->start($pimple['loop']);

        $process->on('exit', function($exitCode, $terminationSignal) use ($finalName) {
            echo "Internal web server terminated. Exiting.\n";
            exit;
        });

        $process->stdout->on('data', function($output) {
            echo $output;
        });

        $process->stderr->on('data', function($output) {
            file_put_contents('php://stderr', $output);
        });



        $pimple['ratchetApp']->run();
    }
}