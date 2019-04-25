<?php

namespace LTree\Annotation\Driver;

use LTree\Annotation\LTreeChilds;
use LTree\Annotation\LTreeEntity;
use LTree\Annotation\LTreeParent;
use LTree\Annotation\LTreePath;
use ReflectionException;
use ReflectionProperty;

/**
 * Interface AnnotationDriverInterface
 * @package LTree\Annotation\Driver
 */
interface AnnotationDriverInterface
{
    public const ENTITY_ANNOTATION = LTreeEntity::class;

    public const CHILDS_ANNOTATION = LTreeChilds::class;

    public const PARENT_ANNOTATION = LTreeParent::class;

    public const PATH_ANNOTATION = LTreePath::class;

    /**
     * Check that ltree entity annotation is in the $object
     *
     * @throws ReflectionException
     * @param $object
     * @return bool
     */
    public function entityIsLTree($object): bool;

    /**
     * Check that ltree entity annotation is in the $className
     *
     * @throws ReflectionException
     * @param string $className
     * @return bool
     */
    public function classIsLTree(string $className): bool;

    /**
     * Return childs property reflection object
     *
     * @throws PropertyNotFoundException
     * @param $object
     * @return ReflectionProperty
     */
    public function getChildsProperty($object): ReflectionProperty;

    /**
     * Return parent property reflection object
     *
     * @throws PropertyNotFoundException
     * @param $object
     * @return ReflectionProperty
     */
    public function getParentProperty($object): ReflectionProperty;

    /**
     * Return path property reflection object
     *
     * @throws PropertyNotFoundException
     * @param $object
     * @return ReflectionProperty
     */
    public function getPathProperty($object): ReflectionProperty;
}
