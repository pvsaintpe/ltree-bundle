<?php

namespace LTree\Repository;

/**
 * Interface LTreeParentInterface
 * @package LTree\Repository
 */
interface LTreeParentInterface
{
    /**
     * @param object|null|LTreeEntityInterface|LTreeParentInterface $parent
     * @return object
     */
    public function setParent($parent);

    /**
     * @return object|null|LTreeEntityInterface|LTreeParentInterface
     */
    public function getParent();
}
