<?php

namespace Akyos\CanopeeModuleSDK\Trait;

use Doctrine\ORM\QueryBuilder;
use Exception;
use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

trait ComponentWithPaginationTrait
{

    public CONST SHOW_MORE_TYPE = 'showMore';
    public CONST PAGINATE_TYPE = 'paginate';

    public CONST TYPES_LIST = [
        'showMore',
        'paginate'
    ];

    public bool $paginate = true;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public ?string $defaultSortField = null;

    #[LiveProp(writable: true)]
    public ?string $defaultSortDirection = null;

    public ?string $usedRoute = null;

    #[LiveProp(writable: true)]
    public ?array $routeParams = [];

    public string $type = 'paginate';

    #[LiveProp(writable: true)]
    public int $limit = 24;

    public PaginatorInterface $paginator;

    /**
     * @internal
     */
    #[Required]
    public function setPaginator(PaginatorInterface $paginator): void
    {
        $this->paginator = $paginator;
    }

    abstract protected function getQuery(): QueryBuilder;

    /**
     * @throws Exception
     */
    private function constructPaginator(): PaginationInterface
    {
        if(!in_array($this->type, self::TYPES_LIST)) {
            throw new Exception('Type not supported');
        }

        $page = match($this->type) {
            self::PAGINATE_TYPE => $this->page,
            default => 1
        };

        $limit = match($this->type) {
            self::SHOW_MORE_TYPE => $this->limit * $this->page,
            default => $this->limit
        };

        $paginator = $this->paginator->paginate(
            $this->getQuery(),
            $page,
            $limit,
            array_merge($this->getDefaultPaginatorOptions(), $this->getPaginatorOptions())
        );

        if($this->getUsedRoute() !== null) {
            $paginator->setUsedRoute($this->getUsedRoute());
            foreach($this->getRouteParams() as $key => $param) {
                $paginator->setParam($key, $param);
            }
        }
        return $paginator;
    }

    #[ExposeInTemplate]
    public function getElements(): PaginationInterface
    {
        return $this->constructPaginator();
    }

    #[LiveAction]
    public function toPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    #[LiveListener('filtersUpdated')]
    public function resetPage(): void
    {
        $this->toPage(1);
    }

    public function getPaginatorOptions(): array
    {
        return [];
    }

    public function getDefaultPaginatorOptions(): array
    {
        if($this->sort === null && $this->getDefaultSortField() === null) {
            return [];
        }
        return [
            PaginatorInterface::DEFAULT_SORT_FIELD_NAME => $this->sort ?? $this->getDefaultSortField(),
            PaginatorInterface::DEFAULT_SORT_DIRECTION => $this->sortDirection ?? ($this->getDefaultSortDirection() ?? 'desc'),
        ];
    }

    #[ExposeInTemplate]
    public function getPagination(): string
    {
        return $this->render('@CanopeeModuleSDK/paginator.html.twig', [
            'elements' => $this->getElements(),
        ])->getContent();
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    #[ExposeInTemplate]
    public function getMoreAttributes(): array
    {
        return [
            'data-action' => "live#action",
            'data-live-page-param' => $this->page + 1,
            'data-live-action-param' => "toPage"
        ];
    }
    #[ExposeInTemplate]
    public function canMore(): bool
    {
        return $this->getElements()->getTotalItemCount() > $this->limit * $this->page;
    }

    public function getUsedRoute(): ?string
    {
        return $this->usedRoute;
    }

    public function setUsedRoute(?string $usedRoute): void
    {
        $this->usedRoute = $usedRoute;
    }

    public function getRouteParams(): ?array
    {
        return $this->routeParams;
    }

    public function setRouteParams(?array $routeParams): void
    {
        $this->routeParams = $routeParams;
    }

    public function getDefaultSortField(): ?string
    {
        return $this->defaultSortField;
    }

    public function setDefaultSortField(?string $defaultSortField): void
    {
        $this->defaultSortField = $defaultSortField;
    }

    public function getDefaultSortDirection(): ?string
    {
        return $this->defaultSortDirection;
    }

    public function setDefaultSortDirection(?string $defaultSortDirection): void
    {
        $this->defaultSortDirection = $defaultSortDirection;
    }
}
