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
class StreamWrapperPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('klipper_content.subscriber.stream_wrapper')) {
            return;
        }

        $def = $container->getDefinition('klipper_content.subscriber.stream_wrapper');
        $refs = $this->findAndSortTaggedServices('klipper_content.stream_wrapper', $container);

        $def->setArgument(0, $refs);

        if (0 === \count($refs)) {
            $container->removeDefinition('klipper_content.subscriber.stream_wrapper');
        }
    }
}
