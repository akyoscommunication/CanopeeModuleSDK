<?php

namespace Akyos\CanopeeModuleSDK\Trait;

use Doctrine\ORM\QueryBuilder;

Trait SimpleRepositoryTrait
{
    private string $alias = 'obj';

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

        $this->getEntityManager()->remove($object);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAll(): QueryBuilder
    {
        return $this->createQueryBuilder($this->alias);
    }
}