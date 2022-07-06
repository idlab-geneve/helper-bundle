<?php
declare(strict_types=1);

namespace Idlab\HelperBundle\Tests;

use Idlab\HelperBundle\IdlabHelperBundle;
use Idlab\HelperBundle\Service\Helper;
use Idlab\HelperBundle\StaticHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new IdlabHelperTestingKernel('test', false);
        $kernel->boot();
        $container = $kernel->getContainer();
        $service = $container->get('idlab_helper.helper');
        $this->assertInstanceOf(Helper::class, $service);
        $this->assertEquals('stuff', $service->getRandomParam());
    }
}

class IdlabHelperTestingKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new IdlabHelperBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {

    }


}