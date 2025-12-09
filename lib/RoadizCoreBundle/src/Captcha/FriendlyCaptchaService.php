<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Captcha;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class FriendlyCaptchaService implements CaptchaServiceInterface
{
    public function __construct(
        private HttpClientInterface $client,
        protected ?string $publicKey,
        #[\SensitiveParameter]
        protected ?string $privateKey,
        protected string $verifyUrl = 'https://global.frcapi.com/api/v2/captcha/siteverify',
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
                'response' => $responseValue,
            ],
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
                'X-API-Key' => $this->privateKey,
            ],
        ]);
        $jsonResponse = json_decode($response->getContent(false), true);

        return (isset($jsonResponse['success']) && true === $jsonResponse['success']) ?
            (true) :
            ($jsonResponse['error']['error_code'] ?? 'Unknown error');
    }

    #[\Override]
    public function getFieldName(): string
    {
        return 'frc-captcha-response';
    }

    #[\Override]
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * {% block friendlycaptcha_widget -%}
     * <script type="module" src="https://cdn.jsdelivr.net/npm/@friendlycaptcha/sdk@0.1.26/site.min.js" async defer></script>
     * <script nomodule src="https://cdn.jsdelivr.net/npm/@friendlycaptcha/sdk@0.1.26/site.compat.min.js" async defer></script>
     * <div class="frc-captcha" data-sitekey="{{ configs.publicKey }}"></div>
     * {%- endblock friendlycaptcha_widget %}.
     */
    #[\Override]
    public function getFormWidgetName(): string
    {
        return 'friendlycaptcha';
    }
}
