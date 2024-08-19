<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Question;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 w
 */
readonly class QuestionPersistenceProcessingProvider extends PersistenceProcessingProvider
{
    public function __construct(
        #[Autowire(service: ItemProvider::class)] ProviderInterface $itemProvider,
        #[Autowire(service: CollectionProvider::class)] ProviderInterface $collectionProvider,
        EntityManagerInterface $entityManager,
        private TagPersistenceProcessingProvider $tagProvider,
    ) {
        parent::__construct($itemProvider, $collectionProvider, $entityManager);
    }

    protected function entityToDto(object $entity): object
    {
        $dto = new \App\ApiResource\Question();
        $dto->id = $entity->getId();
        $dto->content = $entity->getContent();
        $dto->tags = $entity->getTags()->map($this->tagProvider->getDtoFromEntity(...))->toArray();

        return $dto;
    }

    protected function dtoToEntity(object $dto, ?object $entity): object
    {
        if ($entity === null) {
            $entity = new Question($dto->id, $dto->content);
        } else {
            $entity->setContent($dto->content);
        }

        foreach ($dto->tags as $tag) {
            $entity->addTag($this->entityManager->getReference(Tag::class, $tag->id));
        }

        return $entity;
    }

    protected function getEntityClass(): string
    {
        return Question::class;
    }
}
