<?php

/*
 * This file is part of the Idlab Helper.
 *
 * (c) Idlab - Michael Vetterli (michael@idlab.ch)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Idlab\HelperBundle\Service;

class Helper
{
    // Implement future methods requiering services in this class
    public function __construct(private ?string $random_param = null)
    {
    }
}
