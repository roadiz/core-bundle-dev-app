<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\NodeTypes;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\Documentation\Generators\DocumentationGenerator;
use RZ\Roadiz\Typescript\Declaration\DeclarationGeneratorFactory;
use RZ\Roadiz\Typescript\Declaration\Generators\DeclarationGenerator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

final class NodeTypesUtilsController extends RozierApp
{
    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
    ) {
    }

    /**
     * @throws RuntimeError
     */
    public function exportDocumentationAction(Request $request): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        $documentationGenerator = new DocumentationGenerator($this->nodeTypesBag, $this->getTranslator());

        $tmpfname = tempnam(sys_get_temp_dir(), date('Y-m-d-H-i-s').'.zip');
        if (false === $tmpfname) {
            throw new RuntimeError('Unable to create temporary file.');
        }

        unlink($tmpfname); // Deprecated: ZipArchive::open(): Using empty file as ZipArchive is deprecated
        $zipArchive = new \ZipArchive();
        $zipArchive->open($tmpfname, \ZipArchive::CREATE);

        $zipArchive->addFromString(
            '_sidebar.md',
            $documentationGenerator->getNavBar()
        );

        foreach ($documentationGenerator->getReachableTypeGenerators() as $reachableTypeGenerator) {
            $zipArchive->addFromString(
                $reachableTypeGenerator->getPath(),
                $reachableTypeGenerator->getContents()
            );
        }

        foreach ($documentationGenerator->getNonReachableTypeGenerators() as $nonReachableTypeGenerator) {
            $zipArchive->addFromString(
                $nonReachableTypeGenerator->getPath(),
                $nonReachableTypeGenerator->getContents()
            );
        }

        $zipArchive->close();
        $response = new BinaryFileResponse($tmpfname);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'documentation-'.date('Y-m-d-H-i-s').'.zip'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    public function exportTypeScriptDeclarationAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        $documentationGenerator = new DeclarationGenerator(
            new DeclarationGeneratorFactory($this->nodeTypesBag)
        );

        $fileName = 'roadiz-app-'.date('Ymd-His').'.d.ts';
        $response = new Response($documentationGenerator->getContents(), Response::HTTP_OK, [
            'Content-type' => 'application/x-typescript',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
        $response->prepare($request);

        return $response;
    }
}
