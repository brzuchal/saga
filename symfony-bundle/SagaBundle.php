<?php declare(strict_types=1);

namespace Brzuchal\SagaBundle;

use Brzuchal\SagaBundle\DependencyInjection\SagaExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SagaBundle extends Bundle
{
    public function getContainerExtension(): Extension
    {
        return new SagaExtension();
    }
}
