<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Captcha;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Cap (https://trycap.dev) standalone/self-hosted captcha.
 *
 * Unlike the vendor providers, Cap is self-hosted so its domain cannot be hardcoded:
 * both the widget endpoint and the siteverify endpoint live on the configured instance.
 * `verify_url` is `https://<instance>/<site-key>/siteverify`; the widget endpoint is the
 * same URL without the `siteverify` suffix.
 */
final readonly class CapCaptchaService implements CaptchaServiceInterface
{
    public function __construct(
        private HttpClientInterface $client,
        #[\SensitiveParameter]
        private ?string $privateKey,
        private string $verifyUrl = '',
    ) {
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return !empty($this->privateKey) && !empty($this->verifyUrl);
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
    ): true|string {
        if (!$this->isEnabled()) {
            return true;
        }

        $response = $this->client->request('POST', $this->verifyUrl, [
            'json' => [
                'secret' => $this->privateKey,
                'response' => $responseValue,
            ],
            'timeout' => 10,
        ]);
        $jsonResponse = json_decode($response->getContent(false), true);

        return (isset($jsonResponse['success']) && true === $jsonResponse['success']) ?
            (true) :
            ('captcha_is_invalid');
    }

    #[\Override]
    public function getFieldName(): string
    {
        return 'cap-token';
    }

    /**
     * Cap has no separate site key: the widget only needs the API endpoint, which is the
     * verify URL without the trailing `siteverify`, e.g. `https://<instance>/<site-key>/`.
     *
     * ponytail: reuses the getPublicKey() view slot to expose the endpoint instead of a raw
     * site key, so no interface/CaptchaType change is needed. Cap-only semantics.
     */
    #[\Override]
    public function getPublicKey(): ?string
    {
        if (empty($this->verifyUrl)) {
            return null;
        }

        return preg_replace('#siteverify/?$#', '', $this->verifyUrl);
    }

    /**
     * {% block cap_widget -%}
     * <script type="module" src="https://cdn.jsdelivr.net/npm/cap-widget@0.1.57" async defer></script>
     * <cap-widget data-cap-api-endpoint="{{ configs.publicKey }}"></cap-widget>
     * {%- endblock cap_widget %}.
     */
    #[\Override]
    public function getFormWidgetName(): string
    {
        return 'cap';
    }
}
