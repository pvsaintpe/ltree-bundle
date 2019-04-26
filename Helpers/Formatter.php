<?php

namespace LTree\Helpers;

/**
 * Class Formatter
 * @package LTree\Helpers
 */
class Formatter
{
    /**
     * @param string $name
     * @param int $level
     * @param string $pad_string
     * @return string
     */
    public function asLtree($name, $level = 0, $pad_string = '... '): string
    {
        return str_pad($name, strlen($name) + strlen($pad_string) * ($level - 1), $pad_string, STR_PAD_LEFT);
    }
}
