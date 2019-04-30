<?php

namespace LTree\Traits;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use LogicException;
use LTree\Annotation\Driver\AnnotationDriverInterface;
use LTree\DqlFunction\LTreeConcatFunction;
use LTree\DqlFunction\LTreeNlevelFunction;
use LTree\DqlFunction\LTreeOperatorFunction;
use LTree\DqlFunction\LTreeSubpathFunction;
use LTree\TreeBuilder\TreeBuilderInterface;
use LTree\Types\LTreeType;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

trait LTreeTrait
{
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
     * @param $alias
     * @param null $indexBy
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);

    abstract public function getClassName();

    public function getTreeBuilder(): TreeBuilderInterface
    {
        if ($this->treeBuilder === null) {
            throw new LogicException('Repository must inject property accessor service itself');
        }
        return $this->treeBuilder;
    }

    public function setTreeBuilder(TreeBuilderInterface $treeBuilder)
    {
        $this->treeBuilder = $treeBuilder;
        return $this;
    }

    public function getPropertyAccessor(): PropertyAccessorInterface
    {
        if ($this->propertyAccessor === null) {
            throw new LogicException('Repository must inject property accessor service itself');
        }
        return $this->propertyAccessor;
    }

    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
        return $this;
    }

    public function getAnnotationDriver(): AnnotationDriverInterface
    {
        if ($this->annotationDriver === null) {
            throw new LogicException('Repository must inject annotation driver service itself');
        }
        return $this->annotationDriver;
    }

    public function setAnnotationDriver(AnnotationDriverInterface $annotationDriver)
    {
        $this->annotationDriver = $annotationDriver;
        return $this;
    }

    protected function checkClass($entity): void
    {
        if (!is_a($entity, $this->getClassName())) {
            throw new InvalidArgumentException(sprintf('Entity must be instance of %s', $this->getClassName()));
        }

        if (!$this->getAnnotationDriver()->classIsLTree($this->getClassName())) {
            throw new InvalidArgumentException('Entity must have ltree entity annotation');
        }
    }

    public function getAllParentQueryBuilder($entity): QueryBuilder
    {
        $this->checkClass($entity);
        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);

        $alias = $this->getLTreeAlias();
        $qb = $this->createQueryBuilder($alias);
        $qb->where(sprintf(LTreeOperatorFunction::FUNCTION_NAME . '(%s.%s, \'@>\', :self_path) = true', $alias, $pathName));
        $qb->andWhere(sprintf('%s.%s <> :self_path', $alias, $pathName));
        $qb->orderBy(sprintf('%s.%s', static::$alias, $pathName), 'DESC');
        $qb->setParameter('self_path', $pathValue, LTreeType::TYPE_NAME);

        return $qb;
    }

    public function getAllChildrenQueryBuilder($entity): QueryBuilder
    {
        $this->checkClass($entity);
        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
        $orderFieldName = 'parent_paths_for_order';

        $alias = $this->getLTreeAlias();
        $qb = $this->createQueryBuilder($alias);
        $qb->addSelect(sprintf(LTreeSubpathFunction::FUNCTION_NAME . '(%s.%s, 0, -1) as HIDDEN %s', $alias, $pathName, $orderFieldName));
        $qb->where(sprintf(LTreeOperatorFunction::FUNCTION_NAME . '(%s.%s, \'<@\', :self_path) = true', $alias, $pathName));
        $qb->andWhere(sprintf('%s.%s <> :self_path', $alias, $pathName));
        $qb->orderBy($orderFieldName);
        $qb->setParameter('self_path', $pathValue, LTreeType::TYPE_NAME);

        return $qb;
    }

    public function getInverseLTreeBuilder($entity = null): QueryBuilder
    {
        if (empty($entity)) {
            $entityClassName = $this->getClassName();
            $entity = new $entityClassName;
        }

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

        $alias = $this->getLTreeAlias();
        $qb = $this->createQueryBuilder($alias);

        if ($pathValue) {
            $qb->where(sprintf(LTreeOperatorFunction::FUNCTION_NAME . '(%s.%s, \'~\', :self_path) = false', $alias, $pathName));
            $qb->setParameter('self_path', $pathValue, LTreeType::TYPE_NAME);
        }

        $qb->orderBy(sprintf('%s.%s', $alias, $pathName), 'ASC');

        return $qb;
    }

    public function getAllParent($entity, $hydrate = Query::HYDRATE_OBJECT)
    {
        return $this->getAllParentQueryBuilder($entity)->getQuery()->getResult($hydrate);
    }

    public function getAllLTree($hydrate = Query::HYDRATE_OBJECT)
    {
        return $this->getInverseLTreeBuilder()->getQuery()->getResult($hydrate);
    }

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

        $alias = $this->getLTreeAlias();
        $prepareString = static function ($str) use ($pathName, $alias) {
            $replacement = [
                '%alias%' => $alias,
                '%path%' => $pathName
            ];
            return str_replace(array_keys($replacement), array_values($replacement), $str);
        };

        $qb = $this->createQueryBuilder($alias)
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

    protected function getLTreeAlias(): string
    {
        return 'ltree_entity';
    }
}
