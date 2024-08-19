<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Question;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @extends PersistenceProcessingProvider<\App\ApiResource\Tag, Tag>
 */
readonly class TagPersistenceProcessingProvider extends PersistenceProcessingProvider
{
    protected function entityToDto(object $entity): object
    {
        $dto = new \App\ApiResource\Tag();
        $dto->id = $entity->getId();
        $dto->name = $entity->getName();

        return $dto;
    }

    protected function dtoToEntity(object $dto, ?object $entity): object
    {
        if ($entity === null) {
            $entity = new Tag($dto->id, $dto->name);
        } else {
            $entity->setName($dto->name);
        }

        return $entity;
    }

    protected function getEntityClass(): string
    {
        return Tag::class;
    }
}
