<?php

namespace Akyos\CanopeeModuleSDK\Trait;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Akyos\CanopeeModuleSDK\Trait\ComponentWithPaginationTrait;

trait ComponentWithTableTrait
{
    use ComponentWithPaginationTrait;

    #[ExposeInTemplate(name: 'table', getter: 'getTable')]
    private ?string $table = null;

    public ?string $trTemplate = null;

    public ?string $tableTemplate = '@CanopeeModuleSDK/table/table.html.twig';

    #[LiveProp(writable: true)]
    public ?string $sort = null;

    #[LiveProp(writable: true)]
    public ?string $sortDirection = null;

    public function getTable(): false|string
    {
        return $this->render($this->tableTemplate, array_merge([
            'elements' => $this->getElements(),
            'trTemplate' => $this->trTemplate,
            'tHeader' => $this->getTHeader(),
            'paginate' => $this->paginate,
            'sort' => $this->sort,
            'sortDirection' => $this->sortDirection,
        ], $this->getTrTemplateVars()))->getContent();
    }

    protected function getTrTemplateVars(): array
    {
        return [];
    }

    #[LiveAction]
    public function sortColumn(#[LiveArg] string $sort, #[LiveArg] string $direction): void
    {
        $this->sort = $sort;
        $this->sortDirection = $direction;
    }
}
