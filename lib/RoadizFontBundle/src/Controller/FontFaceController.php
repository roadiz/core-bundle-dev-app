<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\FontBundle\Entity\Font;
use RZ\Roadiz\FontBundle\Repository\FontRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class FontFaceController
{
    public function __construct(
        private readonly FilesystemOperator $fontStorage,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Environment $templating,
    ) {
    }

    private function getFontData(Font $font, string $extension): ?array
    {
        try {
            return match ($extension) {
                'eot' => [
                    $this->fontStorage->read($font->getEOTRelativeUrl()),
                    Font::MIME_EOT
                ],
                'woff' => [
                    $this->fontStorage->read($font->getWOFFRelativeUrl()),
                    Font::MIME_WOFF
                ],
                'woff2' => [
                    $this->fontStorage->read($font->getWOFF2RelativeUrl()),
                    Font::MIME_WOFF2
                ],
                'svg' => [
                    $this->fontStorage->read($font->getSVGRelativeUrl()),
                    Font::MIME_SVG
                ],
                'otf' => [
                    $this->fontStorage->read($font->getOTFRelativeUrl()),
                    Font::MIME_OTF
                ],
                'ttf' => [
                    $this->fontStorage->read($font->getOTFRelativeUrl()),
                    Font::MIME_TTF
                ],
                default => null,
            };
        } catch (FilesystemException $exception) {
            return null;
        }
    }

    /**
     * Request a single protected font file from Roadiz.
     *
     * @param Request $request
     * @param string  $filename
     * @param int     $variant
     * @param string  $extension
     *
     * @return Response
     * @throws \Exception
     */
    public function fontFileAction(Request $request, string $filename, int $variant, string $extension): Response
    {
        /** @var FontRepository $repository */
        $repository = $this->managerRegistry->getRepository(Font::class);
        $lastMod = $repository->getLatestUpdateDate();
        /** @var Font $font */
        $font = $repository->findOneBy(['hash' => $filename, 'variant' => $variant]);

        if (null !== $font) {
            [$fontData, $mime] = $this->getFontData($font, $extension);

            if (\is_string($fontData)) {
                $response = new Response(
                    '',
                    Response::HTTP_NOT_MODIFIED,
                    [
                        'content-type' => $mime,
                    ]
                );
                if (null !== $lastMod) {
                    $response->setCache([
                        'last_modified' => $lastMod,
                        'max_age' => 60 * 60 * 48, // expires for 2 days
                        'public' => true,
                    ]);
                }
                if (!$response->isNotModified($request)) {
                    $response->setContent($fontData);
                    $response->setStatusCode(Response::HTTP_OK);
                    $response->setEtag(md5($fontData));
                }

                return $response;
            }
        }
        $msg = "Font doesn't exist " . $filename;

        return new Response(
            $msg,
            Response::HTTP_NOT_FOUND,
            ['content-type' => 'text/html']
        );
    }

    /**
     * Request the font-face CSS file listing available fonts.
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function fontFacesAction(Request $request): Response
    {
        /** @var FontRepository $repository */
        $repository = $this->managerRegistry->getRepository(Font::class);
        $lastMod = $repository->getLatestUpdateDate();

        $response = new Response(
            '',
            Response::HTTP_NOT_MODIFIED,
            ['content-type' => 'text/css']
        );
        $cacheConfig = [
            'max_age' => 60 * 60 * 48, // expires for 2 days
            'public' => true,
        ];
        if (null !== $lastMod) {
            $cacheConfig['last_modified'] = $lastMod;
        }
        $response->setCache($cacheConfig);

        if ($response->isNotModified($request)) {
            return $response;
        }

        $fonts = $repository->findAll();

        $assignation = [
            'fonts' => [],
        ];
        /** @var Font $font */
        foreach ($fonts as $font) {
            $variantHash = $font->getHash() . $font->getVariant();
            $assignation['fonts'][] = [
                'font' => $font,
                'variantHash' => $variantHash,
            ];
        }
        $content = $this->templating->render(
            '@RoadizFont/fonts/fontfamily.css.twig',
            $assignation
        );
        $response->setContent($content);
        $response->setEtag(md5($content));
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}
