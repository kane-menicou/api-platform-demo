<?php

declare(strict_types=1);

namespace App\State;

use App\ApiResource\Tag as TagResource;
use App\Entity\Tag as TagEntity;

/**
 * @extends PersistenceProcessingProvider<TagResource, TagEntity>
 */
final readonly class TagPersistenceProcessingProvider extends PersistenceProcessingProvider
{
    protected function entityToDto(object $entity, ResourceDtoTransformer $subTransformer): object
    {
        $dto = new TagResource();
        $dto->id = $entity->getId();
        $dto->name = $entity->getName();

        return $dto;
    }

    protected function dtoToEntity(object $dto, ?object $entity, ResourceDtoTransformer $subTransformer): object
    {
        $entity ??= new TagEntity($dto->id, $dto->name);
        $entity->setName($dto->name);

        return $entity;
    }

    protected function getEntityClass(): string
    {
        return TagEntity::class;
    }

    protected function getDtoClass(): string
    {
        return TagResource::class;
    }
}
