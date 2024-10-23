<?php

namespace Akyos\CanopeeModuleSDK\Class;

use Closure;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class Filter
{
    public string $name;
    public string $label;
    public string $placeholder;

    public ?string $transDomain = null;
    public ?string $placeholderTransDomain = null;

    public array $options = [];
    public string $searchType = 'eq';

    public mixed $defaultValue = null;

    public ?Closure $callback = null;

    public array $params = [];
    public ?string $type = null;

    public string $classCSS = 'l-grid-col--3';

    public function __construct(string $name, ?string $label = null, ?string $placeholder = null)
    {
        $this->name = $name;
        $this->label = $label ?: $name;
        $this->placeholder = $placeholder ?: $name;
    }


    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function setCallback(Closure $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    public function addParam(string|array $params): self
    {
        if (is_array($params))
            $this->params = array_merge(...$this->params, ...$params);
        else if (is_string($params))
            $this->params[] = $params;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setPlaceholder(string $placeholder): Filter
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function getSearchType(): string
    {
        return $this->searchType;
    }

    public function setSearchType(string $searchType): self
    {
        $this->searchType = $searchType;

        return $this;
    }

    public function getOptions(): array
    {
        return empty($this->options) ? [
            'label' => $this->getTransDomain() ? (new TranslatableMessage($this->getLabel(), [], $this->getTransDomain())) : $this->getLabel(),
            'required' => false,
            'attr' => [
                'placeholder' => $this->getPlaceholderTransDomain() ? (new TranslatableMessage($this->getPlaceholder(), [], $this->getPlaceholderTransDomain())) : $this->getPlaceholder(),
                'data-model' => 'values.' . $this->getName(),

            ],
            'row_attr' => [
                'class' => $this->getClassCSS(),
            ]
        ] : $this->options;
    }

    public function addOption(string $name, array|string|\Closure $value): self
    {
        $options = $this->getOptions();
        $options[$name] = $value;

        $this->options = $options;

        return $this;
    }

    public function addOptions(array $options): self
    {
        if (array_key_exists('tom_select_options', $options) && array_key_exists('tom_select_options', $this->getOptions())) {
            $tomSelectOptions['tom_select_options'] = array_merge($this->getOptions()['tom_select_options'], $options['tom_select_options']);
        }
        $this->options = [
            ...$this->getOptions(),
            ...$options,
            ...$tomSelectOptions ?? []
        ];
        return $this;
    }

    public function setOptions(array $options): Filter
    {
        $this->options = $options;
        return $this;
    }

    public function setClassCSS(string $classCss): Filter
    {
        $this->classCSS = $classCss;
        return $this;
    }

    public function getClassCSS(): string
    {
        return $this->classCSS;
    }

    public function setTransDomain(?string $transDomain): Filter
    {
        $this->transDomain = $transDomain;
        return $this;
    }

    public function getTransDomain(): ?string
    {
        return $this->transDomain;
    }

    public function getCallback(): ?Closure
    {
        return $this->callback;
    }

    public function setPlaceholderTransDomain(?string $placeholderTransDomain): Filter
    {
        $this->placeholderTransDomain = $placeholderTransDomain;
        return $this;
    }

    public function getPlaceholderTransDomain(): ?string
    {
        return $this->placeholderTransDomain;
    }

    public function setDefaultValue(mixed $defaultValue): Filter
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

}
