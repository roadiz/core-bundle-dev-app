<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\User;

/**
 * A chrooted user must not be able to move one of its nodes under a parent
 * outside its chroot by forging newParentId on POST /rz-admin/ajax/nodes/position.
 */
final class NodePositionChrootTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testChrootedUserCannotMoveNodeOutOfChroot(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $chroot = $this->createNode($em, 'chroot');
        $inside = $this->createNode($em, 'inside', $chroot);
        $outside = $this->createNode($em, 'outside');

        $user = new User();
        $user->setUsername('chroot-test-'.uniqid().'@test.test');
        $user->setEmail($user->getUsername());
        $user->setPassword('not-used');
        $user->setUserRoles(['ROLE_BACKEND_USER', 'ROLE_ACCESS_NODES', 'ROLE_ACCESS_NODES_SETTING']);
        $user->setEnabled(true);
        $user->setChroot($chroot);
        $em->persist($user);
        $em->flush();
        $client->loginUser($user, 'main');

        $response = $client->request('POST', '/rz-admin/ajax/nodes/position', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'csrfToken' => $this->extractAjaxToken($client),
                'id' => $inside->getId(),
                'newParentId' => $outside->getId(),
                'firstPosition' => true,
            ],
        ]);

        self::assertSame(403, $response->getStatusCode());

        $em->clear();
        $reloaded = $em->find(Node::class, $inside->getId());
        self::assertSame($chroot->getId(), $reloaded?->getParent()?->getId());
    }

    private function createNode(EntityManagerInterface $em, string $name, ?Node $parent = null): Node
    {
        $node = new Node();
        $node->setNodeName($name.'-'.uniqid());
        $node->setNodeTypeName('ChrootTestType');
        $node->setParent($parent);
        $em->persist($node);
        $em->flush();

        return $node;
    }

    private function extractAjaxToken(mixed $client): string
    {
        $dashboard = $client->request('GET', '/rz-admin');
        preg_match("/ajaxToken['\"]?\s*:\s*'([^']+)'/", $dashboard->getContent(false), $matches);
        self::assertArrayHasKey(1, $matches, 'ajaxToken must be present in the rendered dashboard');

        return $matches[1];
    }
}
