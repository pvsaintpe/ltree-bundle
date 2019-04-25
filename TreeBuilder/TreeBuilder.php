<?php

namespace LTree\TreeBuilder;

use LogicException;
use Traversable;
use Countable;

/**
 * Class TreeBuilder
 * @package LTree\TreeBuilder
 */
class TreeBuilder implements TreeBuilderInterface
{
    /**
     * @var TreeBuilderInterface
     */
    protected $arrayBuilder;

    /**
     * @var TreeBuilderInterface
     */
    protected $objectBuilder;

    /**
     * TreeBuilder constructor.
     * @param TreeBuilderInterface $arrayBuilder
     * @param TreeBuilderInterface $objectBuilder
     */
    public function __construct(TreeBuilderInterface $arrayBuilder, TreeBuilderInterface $objectBuilder)
    {
        $this->arrayBuilder = $arrayBuilder;
        $this->objectBuilder = $objectBuilder;
    }

    /**
     * @param array|Countable|Traversable $list
     * @param string $pathName
     * @param null $parentPath
     * @param null $parentName
     * @param null $childrenName
     * @return array|object
     */
    public function buildTree($list, $pathName, $parentPath = null, $parentName = null, $childrenName = null)
    {
        $element = null;

        foreach ($list as $item) {
            $element = $item;
            break;
        }

        if (is_array($element)) {
            return $this->arrayBuilder->buildTree($list, $pathName, $parentPath, $parentName, $childrenName);
        }

        if (is_object($element)) {
            return $this->objectBuilder->buildTree($list, $pathName, $parentPath, $parentName, $childrenName);
        }

        throw new LogicException('Unable to find builder');
    }
}
