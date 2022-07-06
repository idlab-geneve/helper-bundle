<?php

/*
 * This file is part of the Idlab Helper.
 *
 * (c) Idlab - Michael Vetterli (michael@idlab.ch)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Idlab\HelperBundle;

use Idlab\HelperBundle\DependencyInjection\IdlabHelperExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IdlabHelperBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new IdlabHelperExtension();
        }

        return $this->extension;
    }
}
