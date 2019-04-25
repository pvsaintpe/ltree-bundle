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
    public function setPath(?array $path): self;

    /**
     * @return int
     */
    public function getLevel(): int;

    /**
     * @param object $child
     * @return object
     */
    public function addChildren($child);

    /**
     * @param object $child
     * @return object
     */
    public function removeChildren($child);

    /**
     * @return ArrayCollection|object[]
     */
    public function getChildren();
}
