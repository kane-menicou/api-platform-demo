<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract readonly class PersistenceProcessingProvider implements ProviderInterface, ProcessorInterface
{
    /**
     * @param E $entity
     *
     * @return D
     */
    abstract protected function entityToDto(object $entity): object;

    /**
     * @param D $dto
     *
     * @param E|null $entity (null = create new entity)
     */
    abstract protected function dtoToEntity(object $dto, ?object $entity): object;

    /**
     * @return class-string<E>
     */
    abstract protected function getEntityClass(): string;

    public function __construct(
        #[Autowire(service: ItemProvider::class)] private ProviderInterface       $itemProvider,
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        protected EntityManagerInterface                                          $entityManager,
    )
    {
    }

    final public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // TODO: LOCATE IDENTIFIER USING ATTRIBUTE
        $id = $context['previous_data']?->id;

        if ($id !== null) {
            $entity = $this->entityManager->find($this->getEntityClass(), $id);
        } else {
            $entity = null;
        }

        $entity = $this->dtoToEntity($data, $entity);

        if ($operation instanceof DeleteOperationInterface) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            return null;
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->entityToDto($entity);
    }

    final public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $dtos = [];

            $entities = $this->collectionProvider->provide($operation, $uriVariables, $context);
            foreach ($entities as $entity) {
                $dtos[] = $this->entityToDto($entity);
            }

            return $dtos;
        }

        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);
        if ($entity === null) {
            throw new NotFoundHttpException();
        }

        return $this->entityToDto($entity);
    }

    final public function getDtoFromEntity(object $entity): object
    {
        return $this->entityToDto($entity);
    }
}
