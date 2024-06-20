<?php

namespace App\Class;

use App\Class\Fields\DateField;

class Date extends Filter
{
	public function __construct(string $name, ?string $label = null, ?string $placeholder = null)
	{
		parent::__construct($name, $label, $placeholder);
		$this->type = DateField::class;
	}
}
