<?php

namespace App\Class\Fields;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityField extends AbstractType
{
    public function __construct(
    ){}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'autocomplete' => true,
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
