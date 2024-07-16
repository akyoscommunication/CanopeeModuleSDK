<?php

namespace Akyos\CanopeeModuleSDK\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PostMount;

trait ComponentWithFilterTrait
{
    use ComponentToolsTrait;

    #[ExposeInTemplate(name: 'formFilters', getter: 'getFormFilters')]
    private ?string $formFilters = null;

    public ?string $repository = null;

    #[LiveProp(writable: true)]
    public string $defaultTransDomain = 'form';

    public EntityManagerInterface $entityManager;
    public FormFactoryInterface $formBuilder;

    public RequestStack $requestFilter;

    #[LiveProp(writable: true)]
    public array $values = [];

    /**
     * @internal
     */
    #[Required]
    public function setRequestFilter(RequestStack $requestStack): void
    {
        $this->requestFilter = $requestStack;
    }


    /**
     * @internal
     */
    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @internal
     */
    #[Required]
    public function setFormBuilder(FormFactoryInterface $formBuilder): void
    {
        $this->formBuilder = $formBuilder;
    }

    #[PostMount]
    public function initValues(): void
    {
        foreach ($this->getFilters() as $filter) {
            $this->values[$filter->getName()] = $this->requestFilter->getCurrentRequest()->query->get($filter->getName());
        }
    }

    public function setRepository(string $repository): void
    {
        $this->repository = $repository;
    }

    #[LiveAction]
    public function updateFilters(): void
    {
        $this->emit('filtersUpdated');
    }

    public function getFormFilters(): FormView
    {
        $form = $this->formBuilder->create(FormType::class, null, [
            'attr' => [
                'class' => 'l-grid l-grid--1 search-form',
            ],
            'translation_domain' => $this->getDefaultTransDomain(),
        ]);
        foreach ($this->getFilters() as $filter) {
            $form->add($filter->getName(), $filter->getType(), array_merge($filter->getOptions(), ['attr' => array_merge($filter->getOptions()['attr'] ?? [], ['data-action' => 'live#action','data-live-action-param' => 'updateFilters'])]));
        }

        return $form->createView();
    }

    abstract protected function getFilters(): iterable;

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    /**
     * @throws Exception
     */
    protected function getDefaultQuery(): QueryBuilder
    {
        if ($this->repository === null)
            throw new Exception('You must set the repository before using the default query');

        return $this->entityManager->getRepository($this->repository)
            ->createQueryBuilder('entity');
    }

    /**
     * @throws Exception
     */
    public function addSearchQuery(QueryBuilder $builder): QueryBuilder
    {
        foreach ($this->getFilters() as $filter) {
            $queryParam = [];
            $value = $this->values[$filter->getName()];
            if($this->values[$filter->getName()] === null || $this->values[$filter->getName()] === '' || empty($this->values[$filter->getName()])){
                $value = $filter->getDefaultValue();
            }
            if ($value !== '' && !empty($value)){
                foreach ($filter->getParams() as $param) {
                    if($param instanceof \Closure) {
                        // Param should be a Closure that takes a QueryBuilder and a mixed value, returns and expression, ex:
                        // function(QueryBuilder $builder, mixed $value) {
                        //    if ($value) {
                        //       if ($value === Module::MODULE_CANOPEE_VALUE) {
                        //          $expr = $builder->expr()->andX('m IS NULL');
                        //       } else {
                        //          $expr = $builder->expr()->andX('m.slug = :site');
                        //          $builder->setParameter('site', $value);
                        //       }
                        //       return $expr;
                        //    }
                        // }
                        $queryParam[] = $param($builder, $value);
                    } else {
                        // Param should be a string, ex: 'n.type'
                        if ($filter->getSearchType() === 'like')
                            $value = '%' . $value . '%';
                        $queryParam[] = $builder->expr()->{$filter->getSearchType()}($param, ':'.$filter->getName());
                        $builder->setParameter($filter->getName(), $value);
                    }
                }
                $builder->andWhere(
                    $builder->expr()->orX(...$queryParam)
                );
            }
        }

        return $builder;
    }

    /**
     * @throws Exception
     */
    public function getQuery(): QueryBuilder
    {
        $builder = $this->getDefaultQuery();
        return $this->addSearchQuery($builder);
    }

    protected function getDefaultTransDomain(): string
    {
        return $this->defaultTransDomain;
    }
}
