<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchaServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $verifyUrl = $container->resolveEnvPlaceholders(
            $container->getParameter('roadiz_core.captcha.verify_url'),
            true
        );
        if (str_starts_with((string) $verifyUrl, 'https://www.google.com')) {
            $container->setDefinition(
                CaptchaServiceInterface::class,
                (new Definition())
                    ->setClass(\RZ\Roadiz\CoreBundle\Captcha\GoogleRecaptchaService::class)
                    ->setPublic(true)
                    ->setArguments([
                        new Reference(HttpClientInterface::class),
                        '%roadiz_core.captcha.public_key%',
                        '%roadiz_core.captcha.private_key%',
                        '%roadiz_core.captcha.verify_url%',
                    ])
            );
        } elseif (str_starts_with((string) $verifyUrl, 'https://api.hcaptcha.com')) {
            $container->setDefinition(
                CaptchaServiceInterface::class,
                (new Definition())
                    ->setClass(\RZ\Roadiz\CoreBundle\Captcha\HCaptchaService::class)
                    ->setPublic(true)
                    ->setArguments([
                        new Reference(HttpClientInterface::class),
                        '%roadiz_core.captcha.public_key%',
                        '%roadiz_core.captcha.private_key%',
                        '%roadiz_core.captcha.verify_url%',
                    ])
            );
        } elseif (str_starts_with((string) $verifyUrl, 'https://global.frcapi.com')) {
            $container->setDefinition(
                CaptchaServiceInterface::class,
                (new Definition())
                    ->setClass(\RZ\Roadiz\CoreBundle\Captcha\FriendlyCaptchaService::class)
                    ->setPublic(true)
                    ->setArguments([
                        new Reference(HttpClientInterface::class),
                        '%roadiz_core.captcha.public_key%',
                        '%roadiz_core.captcha.private_key%',
                        '%roadiz_core.captcha.verify_url%',
                    ])
            );
        } elseif (str_starts_with((string) $verifyUrl, 'https://challenges.cloudflare.com')) {
            $container->setDefinition(
                CaptchaServiceInterface::class,
                (new Definition())
                    ->setClass(\RZ\Roadiz\CoreBundle\Captcha\TurnstileCaptchaService::class)
                    ->setPublic(true)
                    ->setArguments([
                        new Reference(HttpClientInterface::class),
                        '%roadiz_core.captcha.public_key%',
                        '%roadiz_core.captcha.private_key%',
                        '%roadiz_core.captcha.verify_url%',
                    ])
            );
        } else {
            $container->setDefinition(
                CaptchaServiceInterface::class,
                (new Definition())
                    ->setClass(\RZ\Roadiz\CoreBundle\Captcha\NullCaptchaService::class)
                    ->setPublic(true)
            );
        }
    }
}
