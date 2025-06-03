<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class MaintenanceModeException extends ServiceUnavailableHttpException
{
    public function getController(): ?AbstractController
    {
        return $this->controller;
    }

    /**
     * @var string
     */
    protected $message = 'Website is currently under maintenance. We will be back shortly.';

    /**
     * @param string $message
     * @param int    $code
     */
    public function __construct(protected ?AbstractController $controller = null, $message = null, $code = 0)
    {
        if (null !== $message) {
            parent::__construct(null, $message, null, $code);
        } else {
            parent::__construct(null, $this->message, null, $code);
        }
    }
}
