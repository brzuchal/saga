<?php

declare(strict_types=1);

namespace Brzuchal\Saga;

/**
 * Enumeration containing the possible Creation Policies for Sagas.
 */
enum SagaCreationPolicy
{
    /**
     * Never loadMetadataForClass a new Saga instance, even if none exists.
     */
case NONE;

    /**
     * Only loadMetadataForClass a new Saga instance if none can be found.
     */
case IF_NONE_FOUND;

    /**
     * Always loadMetadataForClass a new Saga, even if one already exists.
     */
case ALWAYS;
    }
