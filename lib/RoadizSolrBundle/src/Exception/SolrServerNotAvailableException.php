<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Exception;

/**
 * Exception raised when no Solr server respond.
 */
class SolrServerNotAvailableException extends \RuntimeException implements SolrServerException
{
    public function __construct(string $message = 'Solr server is not available', int $code = 503, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
