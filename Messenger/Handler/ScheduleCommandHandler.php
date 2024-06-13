<?php

namespace Akyos\CanopeeModuleSDK\Messenger\Handler;

use Akyos\CanopeeModuleSDK\Messenger\Message\ScheduleCommandMessage;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ScheduleCommandHandler
{
    public function __construct(
        private readonly KernelInterface $kernel,
    ){}

    public function __invoke(ScheduleCommandMessage $command)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => $command->getCommand(),
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();

        $date = date('Y-m-d');
        $title = "Scheduler Command Test $date";

        $cacheFilePath = $this->kernel->getProjectDir().'/var/cache/scheduler_command.txt';

        if (file_put_contents($cacheFilePath, $content, FILE_APPEND) !== false) {
            $output->writeln($title);
            $output->writeln($content);
        } else {
            $output->writeln('Une erreur s\'est produite lors de la création / de la mise à jour du fichier.');
        }

        return $content;
    }
}
