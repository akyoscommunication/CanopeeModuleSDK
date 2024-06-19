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
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PostMount;

trait ComponentWithFilterTrait
{
	use ComponentToolsTrait;

	#[ExposeInTemplate(name: 'formFilters', getter: 'getFormFilters')]
	private ?string $formFilters = null;

	#[LiveProp(writable: true)]
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

	public function getFormFilters(): FormView
	{
		$form = $this->formBuilder->create(FormType::class, null, [
			'attr' => [
				'class' => 'l-grid l-grid--1',
			],
			'translation_domain' => $this->getDefaultTransDomain(),
		]);
		foreach ($this->getFilters() as $filter) {
			$form->add($filter->getName(), $filter->getType(), $filter->getOptions());
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
			if ($this->values[$filter->getName()] !== null && $this->values[$filter->getName()] !== '' && !empty($this->values[$filter->getName()])){
				foreach ($filter->getParams() as $param) {
					if ($filter->getSearchType() === 'like')
						$value = '%' . $this->values[$filter->getName()] . '%';
					else
						$value = $this->values[$filter->getName()];
					$queryParam[] = $builder->expr()->{$filter->getSearchType()}($param, ':'.$filter->getName());
					$builder->setParameter($filter->getName(), $value);
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
	public function getQueryFiltered(): QueryBuilder
	{
		$builder = $this->getDefaultQuery();
		return $this->addSearchQuery($builder);
	}

	protected function getDefaultTransDomain(): string
	{
		return $this->defaultTransDomain;
	}
}
