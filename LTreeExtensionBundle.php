<?php

namespace LTree;

use LTree\DependencyInjection\LTreeExtensionExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class LTreeExtensionBundle
 * @package LTree
 */
class LTreeExtensionBundle extends Bundle
{
    protected function getContainerExtensionClass()
    {
        return LTreeExtensionExtension::class;
    }
}
