# Saga Management

## Install

```shell
composer require brzuchal/saga
```

## Usage

```php
namespace App;

use App\Events\OrderCreated;
use Brzuchal\Saga\Mapping\Saga;
use Brzuchal\Saga\Mapping\SagaMessageHandler;
use Brzuchal\Saga\Mapping\SagaStart;

#[Saga]
class OrderProcessing
{
    #[SagaStart,SagaMessageHandler(associationKey: 'orderId', property: 'id')]
    public function whenCreated(OrderCreated $event): void
    {
        // ...
    }
}
```

## Configuration

```php
use App\OrderProcessing;
use App\Events\OrderCreated;
use Brzuchal\Saga\Mapping\AttributeMappingDriver;
use Brzuchal\Saga\Mapping\SagaMetadataFactory;
use Brzuchal\Saga\SagaRepositoryFactory;
use Brzuchal\Saga\SagaManager;
use Brzuchal\Saga\Store\InMemorySagaStore;

$repositoryFactory = new SagaRepositoryFactory(
    new InMemorySagaStore(), 
    new SagaMetadataFactory([new AttributeMappingDriver()]),
);

$manager = new SagaManager($repositoryFactory->create(OrderProcessing::class));
$manager(new OrderCreated());
```
