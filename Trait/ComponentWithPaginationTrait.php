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

    public ?string $usedRoute = null;

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
    #[ExposeInTemplate]
    public function getElements(): PaginationInterface
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
            $this->getPaginatorOptions()
        );

        if($this->usedRoute !== null) {
            $paginator->setUsedRoute($this->usedRoute);
        }
        return $paginator;
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
}
