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
                'w',
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

        if (!ctype_digit((string)$http_port)) {
            throw new \Exception('The http_port option must be a number. Passed value: '.$http_port);
        }
        if (!ctype_digit((string)$ws_port)) {
            throw new \Exception('The ws_port option must be a number. Passed value: '.$ws_port);
        }

        $pimple = new \Pimple();
        $pimple['port'] = $ws_port;

        $pimple['loop'] = $pimple->share(function() {
            return \React\EventLoop\Factory::create();
        });

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

            // Very temporarily disabling E_USER_WARNING to remove the message about xdebug being loaded (because in
            // a dev env, this is normal!)
            $oldErrorReporting = error_reporting();
            error_reporting($oldErrorReporting & ~E_USER_WARNING);
            $app =  new \Ratchet\App('localhost', $pimple['port'], '127.0.0.1', $pimple['loop']);
            error_reporting($oldErrorReporting);

            $app->route('/console', $pimple['ratchetConsole']);
            // * is a security issue, but we add security using the SECURITY_KEY that is changed on each restart
            $app->route('/run', $pimple['ratchetConsoleHttp'], ['*']);
            return $app;
        });


        putenv("SECURITY_KEY=".bin2hex(openssl_random_pseudo_bytes(20)));
        putenv("HARMONY_HTTP_PORT=".$http_port);
        putenv("HARMONY_WS_PORT=".$ws_port);

        // Let's start the internal web server.
        $process = new Process(PHP_BINARY.' -S localhost:'.$http_port.' src/internal_web_server_router.php');
        $process->start($pimple['loop']);

        $process->on('exit', function($exitCode, $terminationSignal) {
            echo "Internal web server terminated with exit code $exitCode. Exiting.\n";
            exit;
        });

        $process->stdout->on('data', function($output) {
            echo $output;
        });

        $process->stderr->on('data', function($output) {
            file_put_contents('php://stderr', $output);
        });

        $output->writeln("Starting Harmony web-server on <info>http://localhost:".$http_port."</info>");
        $pimple['ratchetApp']->run();
    }
}