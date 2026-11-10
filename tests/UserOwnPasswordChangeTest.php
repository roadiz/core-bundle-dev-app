<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\RozierBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Changing your own password must require your current one, and any password
 * change must notify the account owner by email.
 */
final class UserOwnPasswordChangeTest extends KernelTestCase
{
    use MailerAssertionsTrait;

    private const string NEW_PASSWORD = 'NewPassword123!';

    public function testOwnPasswordChangeRequiresCurrentPassword(): void
    {
        $user = $this->createUser();
        $this->login($user);

        self::assertTrue($this->createForm($user)->has('currentPassword'));
        self::assertTrue($this->submit($user, [])->isValid(), 'Other fields can be edited without current password');
        self::assertFalse($this->submit($user, ['plainPassword' => $this->newPassword()])->isValid());
        self::assertFalse($this->submit($user, ['plainPassword' => $this->newPassword(), 'currentPassword' => 'wrong'])->isValid());
        $form = $this->submit($user, ['plainPassword' => $this->newPassword(), 'currentPassword' => 'OldPassword123!']);
        self::assertTrue($form->isValid(), (string) $form->getErrors(true));
    }

    public function testOtherUserPasswordChangeDoesNotAskCurrentPassword(): void
    {
        $this->login($this->createUser());

        self::assertFalse($this->createForm($this->createUser())->has('currentPassword'));
    }

    public function testPasswordChangeNotifiesUser(): void
    {
        $user = $this->createUser();
        $user->setPlainPassword(self::NEW_PASSWORD);
        $this->em()->flush();

        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertNotNull($email);
        self::assertEmailAddressContains($email, 'To', (string) $user->getEmail());
    }

    public function testSamePasswordIsNotChangedNorNotified(): void
    {
        $user = $this->createUser();
        $hash = $user->getPassword();
        $user->setPlainPassword('OldPassword123!');
        $this->em()->flush();
        $this->em()->refresh($user);

        self::assertSame($hash, $user->getPassword());
        self::assertEmailCount(0);
    }

    private function createUser(): User
    {
        $suffix = uniqid();
        $user = new User();
        $user->setUsername('ownpassword_'.$suffix);
        $user->setEmail('ownpassword_'.$suffix.'@example.test');
        $user->setPlainPassword('OldPassword123!');
        $user->setEnabled(true);

        $this->em()->persist($user);
        $this->em()->flush();

        return $user;
    }

    private function em(): EntityManagerInterface
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }

    private function login(User $user): void
    {
        $tokenStorage = self::getContainer()->get(TokenStorageInterface::class);
        self::assertInstanceOf(TokenStorageInterface::class, $tokenStorage);
        $tokenStorage->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));
    }

    private function createForm(User $user): FormInterface
    {
        $formFactory = self::getContainer()->get(FormFactoryInterface::class);
        self::assertInstanceOf(FormFactoryInterface::class, $formFactory);

        return $formFactory->create(UserType::class, $user, ['csrf_protection' => false]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function submit(User $user, array $data): FormInterface
    {
        // Fresh entity state, as in a real request
        $this->em()->refresh($user);
        $form = $this->createForm($user);
        $form->submit(array_merge([
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
        ], $data));

        return $form;
    }

    /**
     * @return array{first: string, second: string}
     */
    private function newPassword(): array
    {
        return ['first' => self::NEW_PASSWORD, 'second' => self::NEW_PASSWORD];
    }
}
