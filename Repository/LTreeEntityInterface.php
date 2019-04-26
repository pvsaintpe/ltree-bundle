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
     * @param object|null|LTreeEntityInterface $parent
     * @return object
     */
    public function setParent(LTreeEntityInterface $parent);

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
     * @param object|LTreeEntityInterface $children
     * @return object
     */
    public function addChildren(LTreeEntityInterface $children);

    /**
     * @param object|LTreeEntityInterface $children
     * @return object
     */
    public function removeChildren(LTreeEntityInterface $children);

    /**
     * @return ArrayCollection|object[]|LTreeEntityInterface[]
     */
    public function getChildren();
}
