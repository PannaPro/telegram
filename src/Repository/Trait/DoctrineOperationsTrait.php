<?php

namespace App\Repository\Trait;

use Doctrine\ORM\Exception\ORMException;

trait DoctrineOperationsTrait
{
    /**
     * @return void
     */
    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param object $entity
     * @return void
     */
    public function persist(object $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * @param object $entity
     * @return void
     */
    public function removeAndFlush(object $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->flush();
    }

    public function remove(object $entity): void
    {
        $this->getEntityManager()->remove($entity);
    }

    /**
     * @param object $entity
     * @return void
     */
    public function save(object $entity): void
    {
        $this->persist($entity);
        $this->flush();
    }

    public function getReference(string $entity, int $id): object
    {
        try {
            return $this->getEntityManager()->getReference($entity, $id);
        } catch (ORMException $e) {
            // TODO make custom exception
            throw new \Exception('error');
//            throw DomainExternalORMException::invalidReference($entity, $id);
        }
    }
}
