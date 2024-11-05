<?php

namespace Akyos\CanopeeModuleSDK\Trait;

use App\Entity\Customer;
use App\Entity\UserAccessRight;
use Doctrine\ORM\QueryBuilder;
use Exception;
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
    private ?UserAccessRight $userAccessRight = null;

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

        if(property_exists($object, 'deletedState')) {
            if ($this->deleteWorkflow->can($object, 'to_delete')) {
                $this->deleteWorkflow->apply($object, 'to_delete');
            }
        } else {
            $this->getEntityManager()->remove($object);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    final public function find($id, $lockMode = null, $lockVersion = null)
    {
        if(is_string($id)) {
            $id = (int) $id;
            return $this->findById($id)->getQuery()->getOneOrNullResult();
        }
        if(is_int($id)) {
            return $this->findById($id)->getQuery()->getOneOrNullResult();
        }
        if(is_array($id) && isset($id['id']) && is_int($id['id'])) {
            return $this->findById($id['id'])->getQuery()->getOneOrNullResult();
        }

        throw new Exception('Do not use find method, create your own method based on findAll to benefit from the default query with customer and deletedState checks. See exemples in other repostories or look at Akyos\CanopeeModuleSDK\Trait\RepositoryTrait to understand how it works.');
    }

    final public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        foreach ($criteria as $key => $value) {
            if ($key === 'id') {
                return $this->findById($value)->getQuery()->getOneOrNullResult();
            }

            if($key === 'email') {
                return $this->findAll()->andWhere($this->alias.'.email = :email')->setParameter('email', $value)->getQuery()->getResult();
            }
        }

        throw new Exception('Do not use findBy method, create your own method based on findAll to benefit from the default query with customer and deletedState checks. See exemples in other repostories or look at Akyos\CanopeeModuleSDK\Trait\RepositoryTrait to understand how it works.');
    }

    final public function findOneBy(array $criteria, ?array $orderBy = null)
    {
        foreach ($criteria as $key => $value) {

            if ($key === 'id') {
                return $this->findById($value)->getQuery()->getOneOrNullResult();
            }

            if($key === 'email') {
                return $this->findAll()->andWhere($this->alias.'.email = :email')->setParameter('email', $value)->getQuery()->getOneOrNullResult();
            }
        }

        throw new Exception('Do not usefindOneBy method, create your own method based on findAll to benefit from the default query with customer and deletedState checks. See exemples in other repostories or look at Akyos\CanopeeModuleSDK\Trait\RepositoryTrait to understand how it works.');
    }

    final public function findById(int $id): QueryBuilder
    {
        return $this->findAll()
            ->andWhere($this->alias.'.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ;
    }

    /// FIND ALL

    final public function findAll(): QueryBuilder
    {
        return $this->defaultQuery();
    }

    final protected function defaultQuery(): QueryBuilder
    {
        $this->defineCustomer();
        $queryBuilder = $this->createQueryBuilder($this->alias);
        $queryBuilder = $this->commonJoins($queryBuilder);
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
            $this->userAccessRight = $this->requestStack->getSession()->get('userAccessRights');
            return $this->userAccessRight;
        }
        return null;
    }

    private function commonJoins(QueryBuilder $queryBuilder): QueryBuilder
    {
        return $queryBuilder;
    }

    private function andWhereNotDelete(QueryBuilder $queryBuilder): QueryBuilder
    {
        if (property_exists($this->_entityName, $this->deletedStateProperty)) {
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
        if($this->performSameCustomerCheck && $this->customer) {
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

    public function getPerformSameCustomerCheck(): bool
    {
        return $this->performSameCustomerCheck;
    }

    public function setPerformSameCustomerCheck(bool $performSameCustomerCheck): static
    {
        $this->performSameCustomerCheck = $performSameCustomerCheck;

        return $this;
    }
}