<?php

namespace Akyos\CanopeeModuleSDK\Class;

use Akyos\CanopeeModuleSDK\Class\Fields\ChoiceField;
use Akyos\CanopeeModuleSDK\Class\Fields\EnumField;
use Symfony\Component\Translation\TranslatableMessage;

class Enum extends Filter
{
    public string $class;

    public bool $multiple = false;

    public function __construct(string $name, ?string $label = null, ?string $placeholder = null)
    {
        parent::__construct($name, $label, $placeholder);
        $this->type = EnumField::class;
    }

    public function setMultiple(bool $multiple): Enum
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function getOptions(): array
    {
        $options = parent::getOptions();
        $optionFields = [
            'class' => $this->getClass(),
            'placeholder' => $this->getPlaceholderTransDomain() ? new TranslatableMessage($this->getPlaceholder(), [], $this->getPlaceholderTransDomain()) : $this->getPlaceholder(),
            'multiple' => $this->isMultiple(),
        ];

        return array_merge($optionFields, $options);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }
}
