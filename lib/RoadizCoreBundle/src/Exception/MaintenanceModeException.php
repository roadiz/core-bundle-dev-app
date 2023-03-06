<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MaintenanceModeException extends \Exception
{
    protected AbstractController $controller;

    /**
     * @return AbstractController
     */
    public function getController(): AbstractController
    {
        return $this->controller;
    }

    /**
     * @var string
     */
    protected $message = 'Website is currently under maintenance. We will be back shortly.';

    /**
     * @param AbstractController|null $controller
     * @param string $message
     * @param int $code
     */
    public function __construct(AbstractController $controller = null, $message = null, $code = 0)
    {
        if (null !== $message) {
            parent::__construct($message, $code);
        } else {
            parent::__construct($this->message, $code);
        }

        $this->controller = $controller;
    }
}
