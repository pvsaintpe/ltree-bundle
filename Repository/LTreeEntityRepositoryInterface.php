<?php

namespace LTree\Repository;

use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use LTree\Annotation\Driver\AnnotationDriverInterface;
use LTree\TreeBuilder\TreeBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Interface LTreeEntityRepositoryInterface
 * @package LTree\Repository
 */
interface LTreeEntityRepositoryInterface extends ObjectRepository
{
    /**
     * @param TreeBuilderInterface $treeBuilder
     * @return LTreeEntityRepositoryInterface
     */
    public function setTreeBuilder(TreeBuilderInterface $treeBuilder);

    /**
     * @param PropertyAccessorInterface $propertyAccessor
     * @return LTreeEntityRepositoryInterface
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor);

    /**
     * @param AnnotationDriverInterface $annotationDriver
     * @return LTreeEntityRepositoryInterface
     */
    public function setAnnotationDriver(AnnotationDriverInterface $annotationDriver);

    /**
     * @param object $entity object entity
     * @return QueryBuilder
     */
    public function getAllParentQueryBuilder($entity): QueryBuilder;

    /**
     * @param object $entity object entity
     * @param int $hydrate Doctrine processing mode to be used during hydration process.
     *                               One of the Query::HYDRATE_* constants.
     * @return array|mixed with parents for $entity. The root node is last
     */
    public function getAllParent($entity, $hydrate = Query::HYDRATE_OBJECT);

    /**
     * @param int $hydrate Doctrine processing mode to be used during hydration process.
     *                               One of the Query::HYDRATE_* constants.
     * @return array|mixed with parents for $entity. The root node is last
     */
    public function getAllLTree($hydrate = Query::HYDRATE_OBJECT);

    /**
     * @param object $entity object entity
     * @return QueryBuilder
     */
    public function getAllChildrenQueryBuilder($entity): QueryBuilder;

    /**
     * @param object|null $entity object entity
     * @return QueryBuilder
     */
    public function getInverseLTreeBuilder($entity = null): QueryBuilder;

    /**
     * @param object $entity object entity
     * @param bool $treeMode This flag set how result will be presented
     * @param int $hydrate Doctrine processing mode to be used during hydration process.
     *                               One of the Query::HYDRATE_* constants.
     * @return array|mixed If $treeMode is true, result will be grouped to tree.
     *                  If hydrate is object, children placed in childs property.
     *                  If hydrate is array, children placed in __childs key.
     *               If $treeMode is false, result will be in one level array
     */
    public function getAllChildren($entity, $treeMode = false, $hydrate = Query::HYDRATE_OBJECT);

    /**
     * @param object $entity object entity
     * @param object|array $to object or path array
     */
    public function moveNode($entity, $to = null);
}
