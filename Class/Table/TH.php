<?php

namespace Akyos\CanopeeModuleSDK\Class\Table;

class TH
{
	public string $name;
	public ?string $sort = null;
	public ?int $colspan = null;
	public ?int $rowspan = null;

	public function __construct(string $name, string $sort = null, ?int $colspan = null, ?int $rowspan = null)
	{
		$this->name = $name;
		$this->sort = $sort;
		$this->colspan = $colspan;
		$this->rowspan = $rowspan;
	}
}
