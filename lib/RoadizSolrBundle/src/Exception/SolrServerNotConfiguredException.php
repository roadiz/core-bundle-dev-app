<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Exception;

/**
 * Exception raised when no Solr server is configured.
 */
class SolrServerNotConfiguredException extends \RuntimeException implements SolrServerException
{
    public function __construct(string $message = 'Solr server is not configured', int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
