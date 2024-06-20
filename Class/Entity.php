<?php

namespace Akyos\CanopeeModuleSDK\Class;

use App\Class\Fields\EntityField;
use Symfony\Component\Translation\TranslatableMessage;

class Entity extends Filter
{
    public string $class;

    public \Closure $repository;

    public string|\Closure|null $choiceLabel = null;

    public bool $multiple = false;

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

    public function getQueryBuilder(): \Closure
    {
        return $this->repository;
    }

    public function setQueryBuilder(\Closure $repository): self
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
            'query_builder' => $this->getQueryBuilder(),
        ];

        if ($this->choiceLabel) {
            $optionFields['choice_label'] = $this->choiceLabel;
        }

        return array_merge($optionFields, $options);
    }

    public function setChoiceLabel(string|\Closure $choiceLabel): Entity
    {
        $this->choiceLabel = $choiceLabel;
        return $this;
    }
}
