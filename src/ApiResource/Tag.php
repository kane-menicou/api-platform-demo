<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Tag as TagEntity;
use App\State\TagPersistenceProcessingProvider;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    provider: TagPersistenceProcessingProvider::class,
    processor: TagPersistenceProcessingProvider::class,
    stateOptions: new Options(entityClass: TagEntity::class),
)]
final class Tag
{
    #[ApiProperty(identifier: true, example: '01J5KT28ZCVZSHKS7W5DVQQXKC')]
    #[Assert\Ulid]
    public Ulid $id;

    #[ApiProperty(description: 'The name of the tag.', example: 'Symfony 7.0')]
    #[Assert\NotBlank]
    public string $name;
}
