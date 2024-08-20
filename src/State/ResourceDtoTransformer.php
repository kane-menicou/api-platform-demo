<?php

declare(strict_types=1);

namespace App\State;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template E of object
 * @template D of object
 */
#[AutoconfigureTag('app.resource_dto_transformer')]
interface ResourceDtoTransformer
{
    /**
     * @param D $dto
     *
     * @return E
     */
    public function transformDtoToEntity(object $dto): object;

    /**
     * @param E $entity
     *
     * @return D
     */
    public function transformEntityToDto(object $entity): object;

    /**
     * @psalm-assert-if-true E $entity
     */
    public function supportsEntity(object $entity): bool;

    /**
     * @psalm-assert-if-true D $dto
     */
    public function supportsDto(object $dto): bool;
}
