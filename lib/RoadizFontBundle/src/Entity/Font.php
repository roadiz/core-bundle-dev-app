<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\DateTimedInterface;
use RZ\Roadiz\Core\AbstractEntities\DateTimedTrait;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\FontBundle\Repository\FontRepository;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Fonts are entities which store each webfont file for a
 * font-family and a font-variant.
 */
#[
    ORM\Entity(repositoryClass: FontRepository::class),
    ORM\Table(name: 'fonts'),
    ORM\HasLifecycleCallbacks,
    ORM\UniqueConstraint(columns: ['name', 'variant']),
    ORM\Index(columns: ['created_at'], name: 'font_created_at'),
    ORM\Index(columns: ['updated_at'], name: 'font_updated_at'),
    UniqueEntity(fields: ['name', 'variant'])
]
class Font implements DateTimedInterface, PersistableInterface
{
    use SequentialIdTrait;
    use DateTimedTrait;

    public const int REGULAR = 0;
    public const int ITALIC = 1;
    public const int BOLD = 2;
    public const int BOLD_ITALIC = 3;
    public const int LIGHT = 4;
    public const int LIGHT_ITALIC = 5;
    public const int MEDIUM = 6;
    public const int MEDIUM_ITALIC = 7;
    public const int BLACK = 8;
    public const int BLACK_ITALIC = 9;
    public const int THIN = 10;
    public const int THIN_ITALIC = 11;
    public const int EXTRA_LIGHT = 12;
    public const int EXTRA_LIGHT_ITALIC = 13;
    public const int SEMI_BOLD = 14;
    public const int SEMI_BOLD_ITALIC = 15;
    public const int EXTRA_BOLD = 16;
    public const int EXTRA_BOLD_ITALIC = 17;

    public const string MIME_DEFAULT = 'application/octet-stream';
    public const string MIME_SVG = 'image/svg+xml';
    public const string MIME_TTF = 'application/x-font-truetype';
    public const string MIME_OTF = 'application/x-font-opentype';
    public const string MIME_WOFF = 'application/font-woff';
    public const string MIME_WOFF2 = 'application/font-woff2';
    public const string MIME_EOT = 'application/vnd.ms-fontobject';

    /**
     * Get readable variant association.
     */
    public static array $variantToHuman = [
        Font::THIN => 'font_variant.thin',                      // 100
        Font::THIN_ITALIC => 'font_variant.thin.italic',        // 100
        Font::EXTRA_LIGHT => 'font_variant.extra_light',               // 200
        Font::EXTRA_LIGHT_ITALIC => 'font_variant.extra_light.italic', // 200
        Font::LIGHT => 'font_variant.light',                    // 300
        Font::LIGHT_ITALIC => 'font_variant.light.italic',      // 300
        Font::REGULAR => 'font_variant.regular',                    // 400
        Font::ITALIC => 'font_variant.italic',                      // 400
        Font::MEDIUM => 'font_variant.medium',                  // 500
        Font::MEDIUM_ITALIC => 'font_variant.medium.italic',    // 500
        Font::SEMI_BOLD => 'font_variant.semi_bold',                 // 600
        Font::SEMI_BOLD_ITALIC => 'font_variant.semi_bold.italic',   // 600
        Font::BOLD => 'font_variant.bold',                      // 700
        Font::BOLD_ITALIC => 'font_variant.bold.italic',        // 700
        Font::EXTRA_BOLD => 'font_variant.extra_bold',                // 800
        Font::EXTRA_BOLD_ITALIC => 'font_variant.extra_bold.italic',  // 800
        Font::BLACK => 'font_variant.black',                    // 900
        Font::BLACK_ITALIC => 'font_variant.black.italic',      // 900
    ];

    #[ORM\Column(name: 'variant', type: 'integer', unique: false, nullable: false)]
    protected int $variant = Font::REGULAR;

    protected ?UploadedFile $eotFile = null;
    protected ?UploadedFile $woffFile = null;
    protected ?UploadedFile $woff2File = null;
    protected ?UploadedFile $otfFile = null;
    protected ?UploadedFile $svgFile = null;

    #[ORM\Column(name: 'eot_filename', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $eotFilename = null;

    #[ORM\Column(name: 'woff_filename', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $woffFilename = null;

    #[ORM\Column(name: 'woff2_filename', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $woff2Filename = null;

    #[ORM\Column(name: 'otf_filename', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $otfFilename = null;

    #[ORM\Column(name: 'svg_filename', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $svgFilename = null;

    #[ORM\Column(type: 'string', length: 100, unique: false, nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 128, unique: false, nullable: false)]
    #[Assert\Length(max: 128)]
    private string $hash = '';

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    #[Assert\Length(max: 100)]
    private string $folder = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Create a new Font and generate a random folder name.
     */
    public function __construct()
    {
        $this->folder = \mb_substr(hash('crc32b', date('YmdHi')), 0, 12);
        $this->initDateTimedTrait();
    }

    /**
     * Get a readable string to describe current font variant.
     */
    public function getReadableVariant(): string
    {
        return static::$variantToHuman[$this->getVariant()];
    }

    public function getVariant(): int
    {
        return $this->variant;
    }

    /**
     * @return $this
     */
    public function setVariant(int $variant): Font
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Return font variant information for CSS font-face
     * into a simple array.
     *
     * * style
     * * weight
     *
     * @see https://developer.mozilla.org/fr/docs/Web/CSS/font-weight
     */
    public function getFontVariantInfos(): array
    {
        return match ($this->getVariant()) {
            static::SEMI_BOLD_ITALIC => [
                'style' => 'italic',
                'weight' => 600,
            ],
            static::SEMI_BOLD => [
                'style' => 'normal',
                'weight' => 600,
            ],
            static::EXTRA_BOLD_ITALIC => [
                'style' => 'italic',
                'weight' => 800,
            ],
            static::EXTRA_BOLD => [
                'style' => 'normal',
                'weight' => 800,
            ],
            static::EXTRA_LIGHT_ITALIC => [
                'style' => 'italic',
                'weight' => 200,
            ],
            static::EXTRA_LIGHT => [
                'style' => 'normal',
                'weight' => 200,
            ],
            static::THIN_ITALIC => [
                'style' => 'italic',
                'weight' => 100,
            ],
            static::THIN => [
                'style' => 'normal',
                'weight' => 100,
            ],
            static::BLACK_ITALIC => [
                'style' => 'italic',
                'weight' => 900,
            ],
            static::BLACK => [
                'style' => 'normal',
                'weight' => 900,
            ],
            static::MEDIUM_ITALIC => [
                'style' => 'italic',
                'weight' => 500,
            ],
            static::MEDIUM => [
                'style' => 'normal',
                'weight' => 500,
            ],
            static::LIGHT_ITALIC => [
                'style' => 'italic',
                'weight' => 300,
            ],
            static::LIGHT => [
                'style' => 'normal',
                'weight' => 300,
            ],
            static::BOLD_ITALIC => [
                'style' => 'italic',
                'weight' => 'bold',
            ],
            static::BOLD => [
                'style' => 'normal',
                'weight' => 'bold',
            ],
            static::ITALIC => [
                'style' => 'italic',
                'weight' => 'normal',
            ],
            default => [
                'style' => 'normal',
                'weight' => 'normal',
            ],
        };
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): Font
    {
        $this->name = $name;

        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return $this
     */
    public function setHash(string $hash): Font
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return $this
     */
    public function generateHashWithSecret(string $secret): Font
    {
        $this->hash = \mb_substr(hash('crc32b', $this->name.$secret), 0, 12);

        return $this;
    }

    public function getEOTRelativeUrl(): ?string
    {
        return $this->getFolder().'/'.$this->getEOTFilename();
    }

    public function getFolder(): string
    {
        return $this->folder;
    }

    public function getEOTFilename(): ?string
    {
        return $this->eotFilename;
    }

    /**
     * @return $this
     */
    public function setEOTFilename(?string $eotFilename): Font
    {
        $this->eotFilename = StringHandler::cleanForFilename($eotFilename);

        return $this;
    }

    public function getWOFFRelativeUrl(): ?string
    {
        return $this->getFolder().'/'.$this->getWOFFFilename();
    }

    public function getWOFFFilename(): ?string
    {
        return $this->woffFilename;
    }

    /**
     * @return $this
     */
    public function setWOFFFilename(?string $woffFilename): Font
    {
        $this->woffFilename = StringHandler::cleanForFilename($woffFilename);

        return $this;
    }

    public function getWOFF2RelativeUrl(): ?string
    {
        return $this->getFolder().'/'.$this->getWOFF2Filename();
    }

    public function getWOFF2Filename(): ?string
    {
        return $this->woff2Filename;
    }

    /**
     * @return $this
     */
    public function setWOFF2Filename(?string $woff2Filename): Font
    {
        $this->woff2Filename = StringHandler::cleanForFilename($woff2Filename);

        return $this;
    }

    public function getOTFRelativeUrl(): ?string
    {
        return $this->getFolder().'/'.$this->getOTFFilename();
    }

    public function getOTFFilename(): ?string
    {
        return $this->otfFilename;
    }

    /**
     * @return $this
     */
    public function setOTFFilename(?string $otfFilename): Font
    {
        $this->otfFilename = StringHandler::cleanForFilename($otfFilename);

        return $this;
    }

    public function getSVGRelativeUrl(): ?string
    {
        return $this->getFolder().'/'.$this->getSVGFilename();
    }

    public function getSVGFilename(): ?string
    {
        return $this->svgFilename;
    }

    /**
     * @return $this
     */
    public function setSVGFilename(?string $svgFilename): Font
    {
        $this->svgFilename = StringHandler::cleanForFilename($svgFilename);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(?string $description): Font
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of eotFile.
     */
    public function getEotFile(): ?UploadedFile
    {
        return $this->eotFile;
    }

    /**
     * Sets the value of eotFile.
     *
     * @param UploadedFile|null $eotFile the eot file
     */
    public function setEotFile(?UploadedFile $eotFile): Font
    {
        $this->eotFile = $eotFile;

        return $this;
    }

    /**
     * Gets the value of woffFile.
     */
    public function getWoffFile(): ?UploadedFile
    {
        return $this->woffFile;
    }

    /**
     * Sets the value of woffFile.
     *
     * @param UploadedFile|null $woffFile the woff file
     */
    public function setWoffFile(?UploadedFile $woffFile): Font
    {
        $this->woffFile = $woffFile;

        return $this;
    }

    /**
     * Gets the value of woff2File.
     */
    public function getWoff2File(): ?UploadedFile
    {
        return $this->woff2File;
    }

    /**
     * Sets the value of woff2File.
     *
     * @param UploadedFile|null $woff2File the woff2 file
     */
    public function setWoff2File(?UploadedFile $woff2File): Font
    {
        $this->woff2File = $woff2File;

        return $this;
    }

    /**
     * Gets the value of otfFile.
     */
    public function getOtfFile(): ?UploadedFile
    {
        return $this->otfFile;
    }

    /**
     * Sets the value of otfFile.
     *
     * @param UploadedFile|null $otfFile the otf file
     */
    public function setOtfFile(?UploadedFile $otfFile): Font
    {
        $this->otfFile = $otfFile;

        return $this;
    }

    /**
     * Gets the value of svgFile.
     */
    public function getSvgFile(): ?UploadedFile
    {
        return $this->svgFile;
    }

    /**
     * Sets the value of svgFile.
     *
     * @param UploadedFile|null $svgFile the svg file
     */
    public function setSvgFile(?UploadedFile $svgFile): Font
    {
        $this->svgFile = $svgFile;

        return $this;
    }
}
