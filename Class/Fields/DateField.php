<?php

namespace App\Class\Fields;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateField extends AbstractType
{
	public function __construct(
	){}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'widget' => 'single_text',
		]);
	}

	public function getParent(): string
	{
		return DateType::class;
	}
}
