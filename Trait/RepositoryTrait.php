<?php

namespace Akyos\CanopeeModuleSDK\Trait;

use App\Entity\Customer;
use App\Entity\UserAccessRight;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\Service\Attribute\Required;

Trait RepositoryTrait
{
    private string $alias = 'obj';
    private string $customerAlias = 'customer';
    private string $customerParameter = 'customer';
    private bool $performSameCustomerCheck = true;
    private ?string $deletedAlias = null;
    private ?string $deletedStateProperty = 'deletedState';
    private ?string $deletedState = 'alive';

    private ?Customer $customer = null;

    private Security $security;
    private WorkflowInterface $deleteWorkflow;
    private RequestStack $requestStack;

    #[Required]
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    #[Required]
    public function deleteWorkflow(#[Target('delete')] WorkflowInterface $deleteWorkflow): void
    {
        $this->deleteWorkflow = $deleteWorkflow;
    }

    public function add(mixed $object, bool $flush = false): void
    {
        if (!$object) {
            return;
        }

        $this->getEntityManager()->persist($object);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(mixed $object, bool $flush = false): void
    {
        if (!$object) {
            return;
        }

        if ($this->deleteWorkflow->can($object, 'to_delete')) {
            $this->deleteWorkflow->apply($object, 'to_delete');
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAll(): QueryBuilder
    {
        return $this->defaultQuery();
    }

    private function defaultQuery(): QueryBuilder
    {
        $this->defineCustomer();
        $queryBuilder = $this->createQueryBuilder($this->alias);
        $queryBuilder = $this->andWhereNotDelete($queryBuilder);
        $queryBuilder = $this->defineCustomerAlias($queryBuilder);
        $queryBuilder = $this->andWhereCustomer($queryBuilder);
        $queryBuilder = $this->addOrderBy($queryBuilder);

        return $queryBuilder;
    }

    private function defineCustomer(): void
    {
        if(!$this->customer && $this->performSameCustomerCheck) {
            /** @var UserAccessRight|null $userAccessRight */
            $userAccessRight = $this->defineUserAccessRight();
            $this->customer = $userAccessRight?->getCustomer();
        }
    }

    private function defineUserAccessRight(): ?UserAccessRight
    {
        if(!$this->userAccessRight) {
            return $this->requestStack->getSession()->get('userAccessRights');
        }
        return null;
    }

    public function andWhereNotDelete(QueryBuilder $queryBuilder): Comparison|QueryBuilder
    {
        if ($this->deletedState !== null) {
            $queryBuilder
                ->andWhere(($this->deletedAlias ?? $this->alias).'.'.$this->deletedStateProperty.' = :deleted')
                ->setParameter('deleted', $this->deletedState)
            ;
        }

        return $queryBuilder;
    }

    private function defineCustomerAlias(QueryBuilder $queryBuilder): QueryBuilder
    {
        if($this->performSameCustomerCheck) {
            $queryBuilder->innerJoin($this->alias.'.'.$this->customerAlias, $this->customerAlias);
        }

        return $queryBuilder;
    }

    private function andWhereCustomer(QueryBuilder $queryBuilder): QueryBuilder
    {
        if($this->performSameCustomerCheck) {
            $queryBuilder
                ->andWhere($this->customerAlias.' = :'.$this->customerParameter)
                ->setParameter($this->customerParameter, $this->customer)
            ;
        }

        return $queryBuilder;
    }

    private function addOrderBy(QueryBuilder $queryBuilder): QueryBuilder
    {
        return $queryBuilder
            ->addOrderBy($this->alias.'.createdAt', 'DESC')
            ;
    }

    // GETTERS AND SETTERS

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
