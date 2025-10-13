<?php

declare(strict_types=1);

namespace App\Model;

use ApiPlatform\Metadata\ApiProperty;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CreateArticleInput
{
    /**
     * @param array<Tag>|null $tags
     */
    public function __construct(
        #[NotBlank]
        #[Length(max: 200)]
        #[Groups(['article'])]
        private ?string $title = null,
        #[Groups(['article'])]
        #[ApiProperty(example: [
            '/api/tags/1',
            '/api/tags/2',
            '/api/tags/3',
        ])]
        private ?array $tags = null,
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTitle(string $title): CreateArticleInput
    {
        $this->title = $title;

        return $this;
    }

    public function setTags(?array $tags): CreateArticleInput
    {
        $this->tags = $tags;

        return $this;
    }
}
