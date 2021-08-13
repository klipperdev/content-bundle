<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ContentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DoctrineDeleteContentConfigPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('klipper_content.orm.subscriber.delete_content')) {
            return;
        }

        $def = $container->getDefinition('klipper_content.orm.subscriber.delete_content');

        foreach ($this->findAndSortTaggedServices('klipper_content.doctrine_delete_config', $container) as $config) {
            $def->addMethodCall('addConfig', [$config]);
        }
    }
}
