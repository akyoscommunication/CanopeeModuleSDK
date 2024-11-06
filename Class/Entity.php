<?php

namespace Akyos\CanopeeModuleSDK\Class;

use Akyos\CanopeeModuleSDK\Class\Fields\EntityField;
use Closure;
use Symfony\Component\Translation\TranslatableMessage;

class Entity extends Filter
{

    public const USER_SEARCH_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M12 20a8 8 0 0 0 8-8a8 8 0 0 0-8-8a8 8 0 0 0-8 8a8 8 0 0 0 8 8m0-18a10 10 0 0 1 10 10a10 10 0 0 1-10 10C6.47 22 2 17.5 2 12A10 10 0 0 1 12 2m.5 5v5.25l4.5 2.67l-.75 1.23L11 13V7z"/></svg> ';

    public string $class;

    public ?Closure $repository = null;

    public string|Closure|null $choiceLabel = null;

    public bool $multiple = false;

    public ?array $groups = [];
    public ?array $choices = [];

    public function __construct(string $name, ?string $label = null, ?string $placeholder = null, $type = EntityField::class)
    {
        parent::__construct($name, $label, $placeholder);
        $this->type = $type;
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

    public function getQueryBuilder(): ?Closure
    {
        return $this->repository;
    }

    public function setQueryBuilder(?Closure $repository = null): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function setMultiple(bool $multiple): Entity
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
            'placeholder' => $this->getPlaceholderTransDomain() ? (new TranslatableMessage($this->getPlaceholder(), [], $this->getPlaceholderTransDomain())) : $this->getPlaceholder(),
            'multiple' => $this->isMultiple(),
        ];

        if ($this->getQueryBuilder()) {
            $optionFields['query_builder'] = $this->getQueryBuilder();
        }

        if ($this->choiceLabel) {
            $optionFields['choice_label'] = $this->choiceLabel;
        }

        return array_merge($optionFields, $options);
    }

    public function setChoiceLabel(string|Closure $choiceLabel): Entity
    {
        $this->choiceLabel = $choiceLabel;
        return $this;
    }
}
