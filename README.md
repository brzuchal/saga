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
use Brzuchal\Saga\Mapping\AttributeSagaMetadataFactory;
use Brzuchal\Saga\Mapping\SagaMetadataRepository;
use Brzuchal\Saga\SagaManager;
use Brzuchal\Saga\Store\InMemorySagaStore;

$metadataFactory = new AttributeSagaMetadataFactory();
$metadataRepository = new SagaMetadataRepository([
    $metadataFactory->create(OrderProcessing::class);
]);
$store = new InMemorySagaStore();
$manager = new SagaManager($store, $metadataRepository)

$event = new OrderCreated();
$manager($event);
```
