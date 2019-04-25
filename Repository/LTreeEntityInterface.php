<?php

namespace LTree\Repository;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface LTreeEntityInterface
 * @package LTree\Repository
 */
interface LTreeEntityInterface
{
    /**
     * @param object|null $parent
     * @return object
     */
    public function setParent($parent);

    /**
     * @return object|null
     */
    public function getParent();

    /**
     * @return array
     */
    public function getPath(): array;

    /**
     * @param array|null $path
     * @return LTreeEntityInterface
     */
    public function setPath(?array $path);

    /**
     * @return int
     */
    public function getLevel(): int;

    /**
     * @param object $children
     * @return object
     */
    public function addChildren($children);

    /**
     * @param object $children
     * @return object
     */
    public function removeChildren($children);

    /**
     * @return ArrayCollection|object[]
     */
    public function getChildren();
}
