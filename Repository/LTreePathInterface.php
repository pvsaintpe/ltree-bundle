<?php

namespace LTree\Repository;

/**
 * Interface LTreePathInterface
 * @package LTree\Repository
 */
interface LTreePathInterface
{
    /**
     * @return array
     */
    public function getPath(): array;

    /**
     * @param array|null $path
     * @return LTreeEntityInterface|LTreePathInterface
     */
    public function setPath(?array $path);

    /**
     * @return int
     */
    public function getLevel(): int;
}
