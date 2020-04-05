<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ContentBundle;

use Klipper\Bundle\ContentBundle\DependencyInjection\Compiler\StreamWrapperPass;
use Klipper\Bundle\ContentBundle\DependencyInjection\Compiler\UploaderAdapterPass;
use Klipper\Bundle\ContentBundle\DependencyInjection\Compiler\UploaderConfigurationPass;
use Klipper\Bundle\ContentBundle\DependencyInjection\Compiler\UploaderNamerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class KlipperContentBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new StreamWrapperPass());
        $container->addCompilerPass(new UploaderConfigurationPass());
        $container->addCompilerPass(new UploaderAdapterPass());
        $container->addCompilerPass(new UploaderNamerPass());
    }
}
