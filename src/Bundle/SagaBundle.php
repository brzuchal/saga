<?php declare(strict_types=1);

namespace Brzuchal\Saga\Bundle;

use Brzuchal\Saga\Bundle\DependencyInjection\SagaExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SagaBundle extends Bundle
{
    public function getContainerExtension(): Extension
    {
        return new SagaExtension();
    }
}
