<?php declare(strict_types=1);

namespace Brzuchal\Saga\Mapping;

use UnexpectedValueException;

final class SagaMetadataRepository
{
    /** @psalm-var array<class-string, SagaMetadata> */
    protected array $data = [];

    /** @psalm-param array<class-string, SagaMetadata> $data */
    public function __construct(
        array $data = [],
    ) {
        foreach ($data as $metadata) {
            $this->add($metadata);
        }
    }

    public function add(SagaMetadata $metadata): void
    {
        $this->data[$metadata->getName()] = $metadata;
    }

    /**
     * @param class-string $type
     */
    public function has(string $type): bool
    {
        return \array_key_exists($type, $this->data);
    }

    /**
     * @param class-string $type
     */
    public function find(string $type): SagaMetadata
    {
        if (!$this->has($type)) {
            throw new UnexpectedValueException(\sprintf(
                '%s for type %s not found',
                SagaMetadata::class,
                $type,
            ));
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

            $list[$metadata->getName()] = $metadata;
        }

        return $list;
    }
}
