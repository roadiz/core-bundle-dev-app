<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait ContextualizedDocumentTrait
{
    /**
     * @var string|null Image crop alignment.
     *
     * The possible values are:
     *
     * top-left
     * top
     * top-right
     * left
     * center (default)
     * right
     * bottom-left
     * bottom
     * bottom-right
     */
    #[ORM\Column(name: 'image_crop_alignment', type: 'string', length: 12, nullable: true)]
    #[Assert\Length(max: 12)]
    #[Assert\Choice(choices: [
        'top-left',
        'top',
        'top-right',
        'left',
        'center',
        'right',
        'bottom-left',
        'bottom',
        'bottom-right',
    ])]
    protected ?string $imageCropAlignment = null;

    #[ORM\Column(name: 'hotspot', type: 'json', nullable: true)]
    protected ?array $hotspot = null;

    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }

    public function setDocument(DocumentInterface $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getImageCropAlignment(): ?string
    {
        return $this->imageCropAlignment;
    }

    public function setImageCropAlignment(?string $imageCropAlignment): self
    {
        $this->imageCropAlignment = $imageCropAlignment;

        return $this;
    }

    public function getHotspot(): ?array
    {
        return $this->hotspot;
    }

    public function setHotspot(?array $hotspot): self
    {
        $this->hotspot = $hotspot;

        return $this;
    }
}
