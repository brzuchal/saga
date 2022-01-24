<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

final class SagaMetadataFactory
{
    /** @psalm-var array<class-string, SagaMetadata> */
    protected array $data = [];

    /** @psalm-param iterable<class-string, MappingDriver> $drivers */
    public function __construct(
        protected iterable $drivers = [],
    ) {
    }

    /**
     * @param class-string $type
     */
    public function create(string $type): SagaMetadata
    {
        if (\array_key_exists($type, $this->data)) {
            return $this->data[$type];
        }

        foreach ($this->drivers as $driver) {
            $metadata = $driver->loadMetadataForClass($type);
            if ($metadata === null) {
                continue;
            }

            return $this->data[$type] = $metadata;
        }

        return $this->data[$type];
    }

    /**
     * @psalm-return array<class-string, SagaMetadata>
     */
    public function findByMessage(object $message): array
    {
        $list = [];
        foreach ($this->data as $metadata) {
            if (!$metadata->hasHandlerMethod($message)) {
                continue;
            }

            $list[$metadata->type] = $metadata;
        }

        return $list;
    }
}
