<?php

namespace Akyos\CanopeeModuleSDK\Class\Fields;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnumField extends AbstractType
{
    public function __construct(
    ){}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'autocomplete' => true,
            'empty_data'  => null,
        ]);
    }

    public function getParent(): string
    {
        return EnumType::class;
    }
}
