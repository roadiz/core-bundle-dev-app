<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\NodeTypes;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Importer\NodeTypesImporter;
use RZ\Roadiz\CoreBundle\Message\UpdateDoctrineSchemaMessage;
use RZ\Roadiz\Documentation\Generators\DocumentationGenerator;
use RZ\Roadiz\Typescript\Declaration\DeclarationGeneratorFactory;
use RZ\Roadiz\Typescript\Declaration\Generators\DeclarationGenerator;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

final class NodeTypesUtilsController extends RozierApp
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly NodeTypes $nodeTypesBag,
        private readonly NodeTypesImporter $nodeTypesImporter,
        private readonly MessageBusInterface $messageBus,
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

    /**
     * @deprecated nodeTypes will be static in future Roadiz versions
     */
    public function exportAllAction(Request $request): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        $nodeTypes = $this->em()
            ->getRepository(NodeType::class)
            ->findAll();

        $zipArchive = new \ZipArchive();
        $tmpfname = tempnam(sys_get_temp_dir(), date('Y-m-d-H-i-s').'.zip');
        if (false === $tmpfname) {
            throw new RuntimeError('Unable to create temporary file.');
        }
        unlink($tmpfname); // Deprecated: ZipArchive::open(): Using empty file as ZipArchive is deprecated
        $zipArchive->open($tmpfname, \ZipArchive::CREATE);

        /** @var NodeType $nodeType */
        foreach ($nodeTypes as $nodeType) {
            $zipArchive->addFromString(
                $nodeType->getName().'.json',
                $this->serializer->serialize(
                    $nodeType,
                    'json',
                    SerializationContext::create()->setGroups(['node_type', 'position'])
                )
            );
        }

        $zipArchive->close();
        $response = new BinaryFileResponse($tmpfname);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'nodetypes-'.date('Y-m-d-H-i-s').'.zip'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * Import a Json file (.json) containing NodeType datas and fields.
     *
     * @deprecated nodeTypes will be static in future Roadiz versions
     *
     * @throws RuntimeError
     */
    public function importJsonFileAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        $form = $this->buildImportJsonFileForm();

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && !empty($form['node_type_file'])
        ) {
            $file = $form['node_type_file']->getData();

            if ($file->isValid()) {
                $serializedData = file_get_contents($file->getPathname());
                if (false === $serializedData) {
                    throw new RuntimeError('Unable to read uploaded file.');
                }

                if (null !== json_decode($serializedData)) {
                    $this->nodeTypesImporter->import($serializedData);
                    $this->em()->flush();

                    $this->messageBus->dispatch(new Envelope(new UpdateDoctrineSchemaMessage()));

                    /*
                     * Redirect to update schema page
                     */
                    return $this->redirectToRoute('nodeTypesHomePage');
                }
                $form->addError(new FormError($this->getTranslator()->trans('file.format.not_valid')));
            } else {
                $form->addError(new FormError($this->getTranslator()->trans('file.not_uploaded')));
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/node-types/import.html.twig', $this->assignation);
    }

    private function buildImportJsonFileForm(): FormInterface
    {
        $builder = $this->createFormBuilder()
                        ->add('node_type_file', FileType::class, [
                            'label' => 'nodeType.file',
                        ]);

        return $builder->getForm();
    }
}
