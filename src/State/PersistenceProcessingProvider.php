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

use function get_class;

/**
 * @template D of object
 * @template E of object
 *
 * @implements ProviderInterface<D>
 * @implements ProcessorInterface<D, D>
 */
abstract readonly class PersistenceProcessingProvider implements ProviderInterface, ProcessorInterface, ResourceDtoTransformer
{
    /**
     * @param E $entity
     *
     * @return D
     */
    abstract protected function entityToDto(object $entity, ResourceDtoTransformer $subTransformer): object;

    /**
     * @param D $dto
     *
     * @param E|null $entity (null = create new entity)
     */
    abstract protected function dtoToEntity(object $dto, ?object $entity, ResourceDtoTransformer $subTransformer): object;

    /**
     * @return class-string<E>
     */
    abstract protected function getEntityClass(): string;

    /**
     * @return class-string<D>
     */
    abstract protected function getDtoClass(): string;

    public function __construct(
        #[Autowire(service: ItemProvider::class)] private ProviderInterface $itemProvider,
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        #[Autowire(service: DelegatingResourceDtoTransformer::class)] private ResourceDtoTransformer $subTransformer,
        private EntityManagerInterface $entityManager,
    ) {
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

        $entity = $this->dtoToEntity($data, $entity, $this->subTransformer);

        if ($operation instanceof DeleteOperationInterface) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            return null;
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->entityToDto($entity, $this->subTransformer);
    }

    final public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $dtos = [];

            $entities = $this->collectionProvider->provide($operation, $uriVariables, $context);
            foreach ($entities as $entity) {
                $dtos[] = $this->entityToDto($entity, $this->subTransformer);
            }

            return $dtos;
        }

        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);
        if ($entity === null) {
            return null;
        }

        return $this->entityToDto($entity, $this->subTransformer);
    }

    final public function transformEntityToDto(object $entity): object
    {
        return $this->entityToDto($entity, $this->subTransformer);
    }

    final public function transformDtoToEntity(object $dto): object
    {
        // TODO: LOCATE IDENTIFIER USING ATTRIBUTE
        return $this->entityManager->getReference($this->getEntityClass(), $dto->id);
    }

    final public function supportsDto(object $dto): bool
    {
        return get_class($dto) === $this->getDtoClass();
    }

    final public function supportsEntity(object $entity): bool
    {
        return get_class($entity) === $this->getEntityClass();
    }
}
