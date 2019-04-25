<?php

namespace LTree\DependencyInjection;

use LTree\DqlFunction\LTreeConcatFunction;
use LTree\DqlFunction\LTreeNlevelFunction;
use LTree\DqlFunction\LTreeOperatorFunction;
use LTree\DqlFunction\LTreeSubpathFunction;
use LTree\Types\LTreeType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Exception;

/**
 * Class LTreeExtensionExtension
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 * @package LTree\DependencyInjection
 */
class LTreeExtensionExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $dbalConfig = [
            'dbal' => [
                'types' => [
                    LTreeType::TYPE_NAME => LTreeType::class,
                ],
                'mapping_types' => [
                    LTreeType::TYPE_NAME => LTreeType::TYPE_NAME
                ],
            ],
            'orm' => [
                'repository_factory' => 'ltree_bundle.repository_factory',
                'dql' => [
                    'string_functions' => [
                        LTreeConcatFunction::FUNCTION_NAME => LTreeConcatFunction::class,
                        LTreeSubpathFunction::FUNCTION_NAME => LTreeSubpathFunction::class
                    ],
                    'numeric_functions' => [
                        LTreeNlevelFunction::FUNCTION_NAME => LTreeNlevelFunction::class,
                        LTreeOperatorFunction::FUNCTION_NAME => LTreeOperatorFunction::class
                    ]
                ]
            ]
        ];

        $container->prependExtensionConfig('doctrine', $dbalConfig);
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
