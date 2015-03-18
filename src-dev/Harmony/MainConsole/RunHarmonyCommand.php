<?php
namespace Harmony\MainConsole;

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $http_port = $input->getOption('http_port');

        if (!ctype_digit((string) $http_port)) {
            throw new \Exception('The http_port option must be a number. Passed value: '.$http_port);
        }

        putenv("SECURITY_KEY=".bin2hex(openssl_random_pseudo_bytes(20)));
        putenv("HARMONY_HTTP_PORT=".$http_port);

        // Let's start the internal web server.
        $output->writeln("Starting Harmony web-server on <info>http://localhost:".$http_port."</info>");

        // For performance, we disable xdebug
        // Also, we set opcache.revalidate_freq to 0 to avoid bugs when instances file is modified.
        passthru(PHP_BINARY.' -S localhost:'.$http_port.' -d xdebug.remote_autostart=0 -d xdebug.remote_enable=0 -d opcache.revalidate_freq=0 src/internal_web_server_router.php');
    }
}
