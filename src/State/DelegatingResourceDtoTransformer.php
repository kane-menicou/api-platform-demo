<?php

declare(strict_types=1);

namespace App\State;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * @implements ResourceDtoTransformer<object, object>
 */
final readonly class DelegatingResourceDtoTransformer implements ResourceDtoTransformer
{
    public function __construct(
        /**
         * @var iterable<ResourceDtoTransformer>
         */
        #[AutowireIterator('app.resource_dto_transformer')] private iterable $transformers,
    ) {
    }

    public function transformDtoToEntity(object $dto): object
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supportsDto($dto)) {
                return $transformer->transformEntityToDto($dto);
            }
        }

        throw new InvalidArgumentException();
    }

    public function transformEntityToDto(object $entity): object
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supportsEntity($entity)) {
                return $transformer->transformEntityToDto($entity);
            }
        }

        throw new InvalidArgumentException();
    }

    public function supportsEntity(object $entity): bool
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supportsEntity($entity)) {
                return true;
            }
        }

        return false;
    }

    public function supportsDto(object $dto): bool
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supportsDto($dto)) {
                return true;
            }
        }

        return false;
    }
}
