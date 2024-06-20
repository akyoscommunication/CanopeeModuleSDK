<?php

namespace App\Class;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class Text extends Filter
{
    public function __construct(string $name, ?string $label = null, ?string $placeholder = null)
    {
        parent::__construct($name, $label, $placeholder);
        $this->type = TextType::class;
    }
}
