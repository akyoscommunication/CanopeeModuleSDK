<?php

namespace Akyos\CanopeeModuleSDK\Messenger\Message;

final class ScheduleCommandMessage
{
    public function __construct(
        private string $command,
    ) {
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }
}
