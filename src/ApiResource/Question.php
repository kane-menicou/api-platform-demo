<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as Api;
use App\Entity\Question as QuestionEntity;
use App\State\QuestionPersistenceProcessingProvider;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[Api\ApiResource(
    provider: QuestionPersistenceProcessingProvider::class,
    processor: QuestionPersistenceProcessingProvider::class,
    stateOptions: new Options(entityClass: QuestionEntity::class),
)]
final class Question
{
    #[Api\ApiProperty(identifier: true, example: '01J5KT28ZCVZSHKS7W5DVQQXKC')]
    #[Assert\Ulid]
    public Ulid $id;

    #[Assert\Length(min: 1, max: 65535)]
    #[Api\ApiProperty(example: 'When was Symfony released?')]
    #[Assert\NotBlank]
    public string $content;

    /**
     * @var Tag[]
     */
    #[Api\ApiProperty(example: ['/api/tags/01J5KT28ZCVZSHKS7W5DVQQXKC', '/api/tags/01J5KT28ZCVZSHKS7W5DVQQXKD'])]
    #[Assert\Count(min: 0, max: 254)]
    public array $tags = [];
}
