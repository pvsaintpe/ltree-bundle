<?php

namespace LTree\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use LTree\Annotation\Driver\AnnotationDriverInterface;
use LTree\DqlFunction\LTreeConcatFunction;
use LTree\DqlFunction\LTreeNlevelFunction;
use LTree\DqlFunction\LTreeOperatorFunction;
use LTree\DqlFunction\LTreeSubpathFunction;
use LTree\TreeBuilder\TreeBuilderInterface;
use LTree\Types\LTreeType;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use LogicException;
use LTree\Annotation\Driver\PropertyNotFoundException;
use InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use ReflectionException;

/**
 * Class LTreeEntityRepository
 * @package LTree\Repository
 */
class LTreeEntityRepository extends EntityRepository implements LTreeEntityRepositoryInterface
{
    public const LTREE_ALIAS = 'ltree_entity';

    /**
     * @var AnnotationDriverInterface
     */
    private $annotationDriver;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var TreeBuilderInterface
     */
    private $treeBuilder;

    /**
     * @return TreeBuilderInterface
     */
    public function getTreeBuilder(): TreeBuilderInterface
    {
        if ($this->treeBuilder === null) {
            throw new LogicException('Repository must inject property accessor service itself');
        }

        return $this->treeBuilder;
    }

    /**
     * @param TreeBuilderInterface $treeBuilder
     * @return LTreeEntityRepository
     */
    public function setTreeBuilder(TreeBuilderInterface $treeBuilder): LTreeEntityRepositoryInterface
    {
        $this->treeBuilder = $treeBuilder;

        return $this;
    }

    /**
     * @return PropertyAccessorInterface
     */
    public function getPropertyAccessor(): PropertyAccessorInterface
    {
        if ($this->propertyAccessor === null) {
            throw new LogicException('Repository must inject property accessor service itself');
        }
        return $this->propertyAccessor;
    }

    /**
     * @param PropertyAccessorInterface $propertyAccessor
     * @return LTreeEntityRepository
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): LTreeEntityRepositoryInterface
    {
        $this->propertyAccessor = $propertyAccessor;

        return $this;
    }

    /**
     * @return AnnotationDriverInterface
     */
    public function getAnnotationDriver(): AnnotationDriverInterface
    {
        if ($this->annotationDriver === null) {
            throw new LogicException('Repository must inject annotation driver service itself');
        }
        return $this->annotationDriver;
    }

    /**
     * @param AnnotationDriverInterface $annotationDriver
     * @return $this
     */
    public function setAnnotationDriver(AnnotationDriverInterface $annotationDriver): LTreeEntityRepositoryInterface
    {
        $this->annotationDriver = $annotationDriver;

        return $this;
    }

    /**
     * @param $entity
     * @throws ReflectionException
     */
    protected function checkClass($entity): void
    {
        if (!is_a($entity, $this->getClassName())) {
            throw new InvalidArgumentException(sprintf('Entity must be instance of %s', $this->getClassName()));
        }

        if (!$this->getAnnotationDriver()->classIsLTree($this->getClassName())) {
            throw new InvalidArgumentException('Entity must have ltree entity annotation');
        }
    }

    /**
     * @param object $entity
     * @return QueryBuilder
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getAllParentQueryBuilder($entity): QueryBuilder
    {
        $this->checkClass($entity);
        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);

        $qb = $this->createQueryBuilder(static::LTREE_ALIAS);
        $qb->where(sprintf(LTreeOperatorFunction::FUNCTION_NAME . '(%s.%s, \'@>\', :self_path) = true', static::LTREE_ALIAS, $pathName));
        $qb->andWhere(sprintf('%s.%s <> :self_path', static::LTREE_ALIAS, $pathName));
        $qb->orderBy(sprintf('%s.%s', static::LTREE_ALIAS, $pathName), 'DESC');
        $qb->setParameter('self_path', $pathValue, LTreeType::TYPE_NAME);

        return $qb;
    }

    /**
     * @param object $entity
     * @return QueryBuilder
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getAllChildrenQueryBuilder($entity): QueryBuilder
    {
        $this->checkClass($entity);
        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
        $orderFieldName = 'parent_paths_for_order';

        $qb = $this->createQueryBuilder(static::LTREE_ALIAS);
        $qb->addSelect(sprintf(LTreeSubpathFunction::FUNCTION_NAME . '(%s.%s, 0, -1) as HIDDEN %s', static::LTREE_ALIAS, $pathName, $orderFieldName));
        $qb->where(sprintf(LTreeOperatorFunction::FUNCTION_NAME . '(%s.%s, \'<@\', :self_path) = true', static::LTREE_ALIAS, $pathName));
        $qb->andWhere(sprintf('%s.%s <> :self_path', static::LTREE_ALIAS, $pathName));
        $qb->orderBy($orderFieldName);
        $qb->setParameter('self_path', $pathValue, LTreeType::TYPE_NAME);

        return $qb;
    }

    /**
     * @param object $entity
     * @return QueryBuilder
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getInverseLTreeBuilder($entity): QueryBuilder
    {
        $this->checkClass($entity);

        $idName = $this->getAnnotationDriver()->getIdProperty($entity)->getName();
        $idValue = $this->getPropertyAccessor()->getValue($entity, $idName);
        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();

        if ($idValue) {
            $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
            $pathValue[] = '*';
        } else {
            $pathValue = [];
        }

        $qb = $this->createQueryBuilder(static::LTREE_ALIAS);

        if ($pathValue) {
            $qb->where(sprintf(LTreeOperatorFunction::FUNCTION_NAME . '(%s.%s, \'~\', :self_path) = false', static::LTREE_ALIAS, $pathName));
            $qb->setParameter('self_path', $pathValue, LTreeType::TYPE_NAME);
        }

        $qb->orderBy(sprintf('%s.%s', static::LTREE_ALIAS, $pathName), 'ASC');

        return $qb;
    }

    /**
     * @param object $entity
     * @param int $hydrate
     * @return array|mixed
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getAllParent($entity, $hydrate = Query::HYDRATE_OBJECT)
    {
        return $this->getAllParentQueryBuilder($entity)->getQuery()->getResult($hydrate);
    }

    /**
     * @param object $entity
     * @param bool $treeMode
     * @param int $hydrate
     * @return array|mixed|object
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getAllChildren($entity, $treeMode = false, $hydrate = Query::HYDRATE_OBJECT)
    {
        $this->checkClass($entity);
        $result = $this->getAllChildrenQueryBuilder($entity)->getQuery()->getResult($hydrate);

        if ($treeMode && !in_array($hydrate, [Query::HYDRATE_OBJECT, Query::HYDRATE_ARRAY], true)) {
            throw new LogicException('If treeMode is true, hydration mode must be object or array');
        }

        if (!$treeMode) {
            return $result;
        }

        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
        $parentName = $this->getAnnotationDriver()->getParentProperty($entity)->getName();
        $childName = $this->getAnnotationDriver()->getChildsProperty($entity)->getName();

        return $this->treeBuilder->buildTree($result, $pathName, $pathValue, $parentName, $childName);
    }

    /**
     * @param object $entity
     * @param array|object|null $to (if null move to root node)
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     * @return mixed
     */
    public function moveNode($entity, $to = null)
    {
        $this->checkClass($entity);
        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $oldPathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);

        if ($to !== null) {
            $this->checkClass($to);
            $newPathValue = $this->getPropertyAccessor()->getValue($to, $pathName);
        } else {
            $newPathValue = [];
        }

        $prepareString = static function ($str) use ($pathName) {
            $replacement = [
                '%alias%' => static::LTREE_ALIAS,
                '%path%' => $pathName
            ];
            return str_replace(array_keys($replacement), array_values($replacement), $str);
        };

        $qb = $this->createQueryBuilder(static::LTREE_ALIAS)
            ->update()
            ->set(
                $prepareString('%alias%.%path%'),
                $prepareString(implode('', [
                    LTreeConcatFunction::FUNCTION_NAME,
                    '(:new_path, ',
                    LTreeSubpathFunction::FUNCTION_NAME,
                    '(%alias%.%path%, (',
                    LTreeNlevelFunction::FUNCTION_NAME,
                    '(:self_path) - 1)))',
                ]))
            )
            ->where($prepareString(LTreeOperatorFunction::FUNCTION_NAME . '(%alias%.%path%, \'<@\', :self_path) = true'))
            ->setParameter(':self_path', $oldPathValue, LTreeType::TYPE_NAME)
            ->setParameter(':new_path', $newPathValue, LTreeType::TYPE_NAME)
        ;

        return $qb->getQuery()->execute();
    }
}
