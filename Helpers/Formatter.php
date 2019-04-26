<?php

namespace LTree\Helpers;

/**
 * Class Formatter
 * @package LTree\Helpers
 */
class Formatter
{
    protected const PAD_STRING = '... ';
    protected const PAD_TYPE = STR_PAD_LEFT;

    /**
     * @param string $name
     * @param int $level
     * @param string $pad_string
     * @param int $pad_type
     * @return string
     */
    public function asLTree(string $name, int $level = 1, string $pad_string = self::PAD_STRING, int $pad_type = self::PAD_TYPE): string
    {
        $level = ($level < 1) ? 1 : $level;
        return str_pad($name, strlen($name) + strlen($pad_string) * ($level - 1), $pad_string, $pad_type);
    }
}
