<?php

namespace Akyos\CanopeeModuleSDK\Translation;

use Symfony\Component\Translation\TranslatableMessage;

class CanopeeTranslatableMessage extends TranslatableMessage
{
    private string $translatedMessage;

    public function setTranslatedMessage(string $translatedMessage): static
    {
        $this->translatedMessage = $translatedMessage;

        return $this;
    }

    public function getTranslatedMessage(): string
    {
        return $this->translatedMessage;
    }

    public function __toString(): string
    {
        return $this->getTranslatedMessage();
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'domain' => $this->getDomain(),
            'parameters' => $this->getParameters(),
            'translatedMessage' => $this->getTranslatedMessage(),
        ];
    }
}
