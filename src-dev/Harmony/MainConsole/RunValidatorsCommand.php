<?php
namespace Harmony\MainConsole;

use Harmony\Services\ValidatorService;
use Harmony\Validator\ValidatorResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A CLI command to run the status validators.
 */
class RunValidatorsCommand extends Command
{
    /**
     * @var ValidatorService
     */
    private $validatorService;

    public function __construct($validatorService)
    {
        parent::__construct();
        $this->validatorService = $validatorService;
    }


    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Run Harmony validators and return the status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $results = $this->validatorService->validate();

        $output->writeln("Status:");
        $output->writeln("=======");

        $nbOk = $nbWarn = $nbError = 0;

        foreach ($results as $result) {
            switch ($result->getCode()) {
                case ValidatorResult::SUCCESS:
                    if ($output->isVerbose()) {
                        $output->writeln("<info>".$result->getTextMessage()."</info>");
                    }
                    $nbOk++;
                    break;
                case ValidatorResult::WARN:
                    $output->writeln("<comment>".$result->getTextMessage()."</comment>");
                    $nbWarn++;
                    break;
                case ValidatorResult::ERROR:
                    $output->writeln("<error>".$result->getTextMessage()."</error>");
                    $nbError++;
                    break;
            }
        }

        $output->writeln("");
        $output->writeln(sprintf("Validation finished. <info>%d %s</info> succeeded, <comment>%d %s</comment>"
            ." returned a warning and <error>%d %s</error> failed.",
            $nbOk,
            $nbOk>1?"tests":"test",
            $nbWarn,
            $nbWarn>1?"tests":"test",
            $nbError,
            $nbError>1?"tests":"test"));
    }
}
