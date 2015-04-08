<?php
namespace Harmony\MainConsole;

use Harmony\Services\ValidatorService;
use Harmony\Validator\ValidatorResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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

        $tags = [
            ValidatorResult::SUCCESS => "info",
            ValidatorResult::WARN => "comment",
            ValidatorResult::ERROR => "error",
        ];

        $nbByType = [
            "info" => 0,
            "comment" => 0,
            "error" => 0,
        ];

        foreach ($results as $result) {
            $tag = $tags[$result->getCode()];
            $nbByType[$tag] += 1;
            if (!$output->isVerbose() && $result->getCode() == ValidatorResult::SUCCESS) {
                continue;
            }
            $output->writeln(sprintf("<%s>%s</%s>", $tag, $result->getTextMessage(), $tag));
        }

        $output->writeln("");
        $output->writeln(sprintf("Validation finished. <info>%s</info> succeeded, <comment>%s</comment>"
            ." returned a warning and <error>%s</error> failed.",
            $this->getTestString($nbByType["info"]),
            $this->getTestString($nbByType["comment"]),
            $this->getTestString($nbByType["error"])));
    }

    private function getTestString($nbTest)
    {
        if ($nbTest == 0) {
            return "no test";
        } elseif ($nbTest == 1) {
            return "1 test";
        } else {
            return $nbTest." tests";
        }
    }
}
