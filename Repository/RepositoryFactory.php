<?php

namespace LTree\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LTree\Annotation\Driver\AnnotationDriverInterface;
use LTree\TreeBuilder\TreeBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryBase;

/**
 * Class RepositoryFactory
 * @package LTree\Repository
 */
class RepositoryFactory implements RepositoryFactoryBase
{
    /**
     * The list of EntityRepository instances.
     *
     * @var ObjectRepository[]
     */
    protected $repositoryList = array();

    /**
     * @var AnnotationDriverInterface
     */
    protected $annotationDriver;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var TreeBuilderInterface
     */
    protected $treeBuilder;

    /**
     * RepositoryFactory constructor.
     * @param AnnotationDriverInterface $annotationDriver
     * @param PropertyAccessorInterface $propertyAccessor
     * @param TreeBuilderInterface $treeBuilder
     */
    public function __construct(
        AnnotationDriverInterface $annotationDriver,
        PropertyAccessorInterface $propertyAccessor,
        TreeBuilderInterface $treeBuilder
    ) {
        $this->annotationDriver = $annotationDriver;
        $this->propertyAccessor = $propertyAccessor;
        $this->treeBuilder = $treeBuilder;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param string $entityName
     * @return ObjectRepository
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName): ObjectRepository
    {
        $entityName = ltrim($entityName, '\\');

        if (isset($this->repositoryList[$entityName])) {
            return $this->repositoryList[$entityName];
        }

        $repository = $this->createRepository($entityManager, $entityName);

        $this->repositoryList[$entityName] = $repository;

        return $repository;
    }

    /**
     * Create a new repository instance for an entity class.
     *
     * @param EntityManagerInterface $entityManager The EntityManager instance.
     * @param string                               $entityName    The name of the entity.
     *
     * @return ObjectRepository
     */
    protected function createRepository(EntityManagerInterface $entityManager, $entityName): ObjectRepository
    {
        $metadata = $entityManager->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName;

        if ($repositoryClassName === null) {
            $configuration = $entityManager->getConfiguration();
            $repositoryClassName = $configuration->getDefaultRepositoryClassName();
        }

        $repo = new $repositoryClassName($entityManager, $metadata);
        if ($repo instanceof LTreeEntityRepositoryInterface) {
            $repo->setAnnotationDriver($this->annotationDriver);
            $repo->setPropertyAccessor($this->propertyAccessor);
            $repo->setTreeBuilder($this->treeBuilder);
        }
        return $repo;
    }
}
