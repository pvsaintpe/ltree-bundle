<?php

namespace LTree\Entity;

use App\Entity\Department;
use Doctrine\ORM\EntityManagerInterface;
use LTree\Repository\LTreeEntityRepository;

/**
 * Class TestRepository
 *
 * @method TestEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestEntity[]    findAll()
 * @method TestEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @package LTree\Entity
 */
class TestRepository extends LTreeEntityRepository
{
    /**
     * TestRepository constructor.
     * @param EntityManagerInterface $registry
     */
    public function __construct(EntityManagerInterface $registry)
    {
        parent::__construct($registry, $registry->getClassMetadata(TestEntity::class));
    }
}
