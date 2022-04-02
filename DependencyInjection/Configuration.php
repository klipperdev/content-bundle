<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ContentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your config files.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('klipper_content');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->append($this->getImageManipulatorNode())
            ->append($this->getUploaderNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function getImageManipulatorNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('image_manipulator');

        /** @var ArrayNodeDefinition $rootNode */
        $node = $treeBuilder->getRootNode();
        $node
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('cache_id')->defaultNull()->info('The service id of the image manipulator cache')->end()
            ->scalarNode('cache_path')->defaultValue('%kernel.project_dir%/var/image-manipulator-cache')->end()
            ->scalarNode('temp_path')->defaultValue('%kernel.project_dir%/var/image-manipulator')->end()
            ->arrayNode('available_extensions')
            ->scalarPrototype()->end()
            ->end()
            ->enumNode('engine')
            ->values(['gd', 'imagick', 'gmagick'])
            ->defaultValue('gd')
            ->end()
            ->arrayNode('engine_options')
            ->addDefaultsIfNotSet()
            ->normalizeKeys(false)
            ->children()
            ->scalarNode('format')->defaultNull()->end()
            ->scalarNode('foreground')->defaultNull()->end()
            ->booleanNode('flatten')->defaultNull()->end()
            ->scalarNode('resolution-units')->defaultNull()->end()
            ->integerNode('resolution-x')->defaultNull()->end()
            ->integerNode('resolution-y')->defaultNull()->end()
            ->scalarNode('resampling-filter')->defaultNull()->end()
            ->integerNode('jpeg_quality')->defaultNull()->end()
            ->integerNode('png_compression_level')->defaultNull()->end()
            ->integerNode('png_compression_filter')->defaultNull()->end()
            ->integerNode('webp_quality')->defaultNull()->end()
            ->scalarNode('webp_lossless')->defaultNull()->end()
            ->booleanNode('animated')->defaultNull()->end()
            ->integerNode('animated-delay')->defaultNull()->end()
            ->integerNode('animated-loops')->defaultNull()->end()
            ->scalarNode('interlace')->defaultNull()->end()
            ->end()
            ->end()
            ->end()
        ;

        return $node;
    }

    private function getUploaderNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('uploader');

        /** @var ArrayNodeDefinition $rootNode */
        $node = $treeBuilder->getRootNode();
        $node
            ->addDefaultsIfNotSet()
            ->children()
            ->append($this->getUploaderAdapterNode())
            ->append($this->getUploaderConfigurationNode())
            ->booleanNode('form_data_adapter')->defaultTrue()->end()
            ->end()
        ;

        return $node;
    }

    private function getUploaderAdapterNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('adapters');

        /** @var ArrayNodeDefinition $rootNode */
        $node = $treeBuilder->getRootNode();
        $node
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('tus')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('cache_id')->defaultNull()->info('The service id of the TUS server cache')->end()
            ->scalarNode('cache_path')->defaultValue('%kernel.cache_dir%/tus_cache')->end()
            ->scalarNode('cache_file')->defaultValue('tus_php.server.cache')->end()
            ->scalarNode('temp_upload_path')->defaultValue('%kernel.cache_dir%/tus_upload_temp')->end()
            ->end()
            ->end()
            ->end()
        ;

        return $node;
    }

    private function getUploaderConfigurationNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('configurations');

        /** @var ArrayNodeDefinition $rootNode */
        $node = $treeBuilder->getRootNode();
        $node
            ->useAttributeAsKey('name')
            ->normalizeKeys(false)
            ->arrayPrototype()
            ->children()
            ->scalarNode('name')->end()
            ->scalarNode('path')->isRequired()->end()
            ->scalarNode('max_size')->defaultValue(0)->end()
            ->arrayNode('allowed_type_mimes')
            ->scalarPrototype()->end()
            ->end()
            ->scalarNode('namer')->defaultNull()->end()
            ->scalarNode('attachment_class')->defaultNull()->end()
            ->end()
            ->end()
        ;

        return $node;
    }
}
