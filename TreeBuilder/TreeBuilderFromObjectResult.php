<?php

namespace LTree\TreeBuilder;

use LTree\TreeBuilder\Exceptions\NotImplementException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Countable;
use Traversable;

/**
 * Class TreeBuilderFromObjectResult
 * @package LTree\TreeBuilder
 */
class TreeBuilderFromObjectResult implements TreeBuilderInterface
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * TreeBuilderFromObjectResult constructor.
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param array|Countable|Traversable $list
     * @param string $pathName
     * @param null $parentPath
     * @param null $parentName
     * @param null $childrenName
     * @return array|object|void
     * @throws NotImplementException
     */
    public function buildTree($list, $pathName, $parentPath = null, $parentName = null, $childrenName = null)
    {
        throw new NotImplementException('Build tree from object not implement yet');
    }
}
