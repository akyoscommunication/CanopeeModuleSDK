<?php

namespace Akyos\CanopeeModuleSDK\Translation;

use Symfony\Component\Translation\TranslatableMessage;

class CanopeeTranslatableMessage // extends TranslatableMessage
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
}
