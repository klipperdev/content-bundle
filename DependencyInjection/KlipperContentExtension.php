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

use JMS\SerializerBundle\JMSSerializerBundle;
use Klipper\Bundle\RoutingBundle\KlipperRoutingBundle;
use Klipper\Component\Content\Uploader\UploaderConfiguration;
use Klipper\Component\System\Util\SystemUtil;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use TusPhp\Tus\Server;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class KlipperContentExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $ref = new \ReflectionClass($this);
        $configPath = \dirname($ref->getFileName(), 2).'/Resources/config';
        $loader = new Loader\XmlFileLoader($container, new FileLocator($configPath));

        $this->configureImageManipulator($loader, $container, $config['image_manipulator']);
        $this->configureSerializer($loader);
        $this->configureDownloader($loader);
        $this->configureUploader($loader, $container, $config['uploader']);
        $this->configureStreamWrapper($loader);
    }

    /**
     * Remove the keys with null values.
     *
     * @param array $options The options
     */
    private function cleanOptions(array $options): array
    {
        $options['animated.delay'] = $options['animated-delay'];
        $options['animated.loops'] = $options['animated-loops'];
        unset($options['animated-delay'], $options['animated-loops']);

        return array_filter($options, static function ($value) {
            return null !== $value;
        });
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    private function configureImageManipulator(LoaderInterface $loader, ContainerBuilder $container, array $config): void
    {
        $loader->load('image_manipulator.xml');

        $container->getDefinition('klipper_content.image_manipulator')
            ->replaceArgument(2, $config['temp_path'])
            ->replaceArgument(3, $this->cleanOptions($config['engine_options']))
            ->replaceArgument(4, $config['available_extensions'])
        ;

        $container->setAlias(
            'klipper_content.image_manipulator.imagine',
            'klipper_content.image_manipulator.'.$config['engine']
        );

        if (null !== $cacheId = $config['cache_id']) {
            $container->removeDefinition('klipper_content.image_manipulator.cache');
            $container->setAlias('klipper_content.image_manipulator.cache', $cacheId);
        } else {
            $container->getDefinition('klipper_content.image_manipulator.cache')
                ->replaceArgument(0, $config['cache_path'])
            ;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    private function configureSerializer(LoaderInterface $loader): void
    {
        if (class_exists(JMSSerializerBundle::class) && class_exists(KlipperRoutingBundle::class)) {
            $loader->load('serializer.xml');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    private function configureDownloader(LoaderInterface $loader): void
    {
        $loader->load('downloader.xml');
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    private function configureUploader(LoaderInterface $loader, ContainerBuilder $container, array $config): void
    {
        $loader->load('uploader.xml');
        $loader->load('uploader_namer.xml');

        if ($config['form_data_adapter']) {
            $loader->load('uploader_adapter_form_data.xml');
        }

        if (class_exists(Server::class)) {
            $loader->load('uploader_adapter_tus.xml');

            $container->getDefinition('klipper_content.tus_server')
                ->addMethodCall('setUploadDir', [rtrim($config['adapters']['tus']['temp_upload_path'], '/')])
            ;

            if (null !== $cacheId = $config['adapters']['tus']['cache_id']) {
                $container->removeDefinition('klipper_content.tus_server.cache');
                $container->getDefinition('klipper_content.tus_server')
                    ->replaceArgument(0, new Reference($cacheId))
                ;
            } else {
                $container->getDefinition('klipper_content.tus_server.cache')
                    ->replaceArgument(0, rtrim($config['adapters']['tus']['cache_path'], '/').'/')
                    ->replaceArgument(1, $config['adapters']['tus']['cache_file'])
                ;
            }
        }

        foreach ($config['configurations'] as $name => $uploaderConfig) {
            $maxSize = $uploaderConfig['max_size'];
            $maxSize = \is_string($maxSize) ? SystemUtil::convertToBytes($maxSize) : $maxSize;

            $def = new Definition(UploaderConfiguration::class, [
                $name,
                $uploaderConfig['path'],
                $maxSize,
                $uploaderConfig['allowed_type_mimes'],
                $uploaderConfig['namer'],
            ]);
            $def->addTag('klipper_content.uploader.config');

            $container->setDefinition('klipper_content.uploader.config.'.$name, $def);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    private function configureStreamWrapper(LoaderInterface $loader): void
    {
        $loader->load('stream_wrapper.xml');
    }
}