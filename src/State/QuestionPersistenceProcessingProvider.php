<?php

declare(strict_types=1);

namespace App\State;

use App\ApiResource\Question as QuestionResource;
use App\Entity\Question as QuestionEntity;

/**
 * @extends PersistenceProcessingProvider<QuestionResource, QuestionEntity>
 */
final readonly class QuestionPersistenceProcessingProvider extends PersistenceProcessingProvider
{
    protected function entityToDto(object $entity, ResourceDtoTransformer $subTransformer): object
    {
        $dto = new QuestionResource();
        $dto->id = $entity->getId();
        $dto->content = $entity->getContent();
        $dto->tags = $entity->getTags()->map($subTransformer->transformEntityToDto(...))->toArray();

        return $dto;
    }

    protected function dtoToEntity(object $dto, ?object $entity, ResourceDtoTransformer $subTransformer): object
    {
        $entity ??= new QuestionEntity($dto->id, $dto->content);
        $entity->setContent($dto->content);

        foreach ($dto->tags as $tag) {
            $entity->addTag($subTransformer->transformDtoToEntity($tag));
        }

        return $entity;
    }

    protected function getEntityClass(): string
    {
        return QuestionEntity::class;
    }

    protected function getDtoClass(): string
    {
        return QuestionResource::class;
    }
}
