<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Captcha;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class TurnstileCaptchaService implements CaptchaServiceInterface
{
    public function __construct(
        private HttpClientInterface $client,
        protected ?string $publicKey,
        #[\SensitiveParameter]
        protected ?string $privateKey,
        protected ?string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
    ) {
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return !empty($this->publicKey) && !empty($this->privateKey) && !empty($this->verifyUrl);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[\Override]
    public function check(
        string $responseValue,
    ): true|string|array {
        if (!$this->isEnabled()) {
            return true;
        }

        $response = $this->client->request('POST', $this->verifyUrl, [
            'body' => [
                'sitekey' => $this->publicKey,
                'secret' => $this->privateKey,
                'response' => $responseValue,
            ],
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $jsonResponse = json_decode($response->getContent(false), true);

        return (isset($jsonResponse['success']) && true === $jsonResponse['success']) ?
            (true) :
            ($jsonResponse['error-codes']);
    }

    #[\Override]
    public function getFieldName(): string
    {
        return 'cf-turnstile-response';
    }

    #[\Override]
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * {% block cf_turnstile_widget -%}
     * <div
     * class="cf-turnstile"
     * data-sitekey="{{ configs.publicKey }}"
     * data-callback="javascriptCallback"
     * ></div>
     * {%- endblock cf_turnstile_widget %}.
     */
    #[\Override]
    public function getFormWidgetName(): string
    {
        return 'cf_turnstile';
    }
}
