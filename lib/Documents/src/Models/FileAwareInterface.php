<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

/**
 * Provide paths for file management.
 */
interface FileAwareInterface
{
    /**
     * @return string return absolute path to public files folder
     */
    public function getPublicFilesPath(): string;

    /**
     * @return string return relative path to public files folder
     */
    public function getPublicFilesBasePath(): string;

    /**
     * @return string Return absolute path to private files folder. Path must be protected.
     */
    public function getPrivateFilesPath(): string;

    /**
     * @return string return relative path to private files folder
     */
    public function getPrivateFilesBasePath(): string;

    /**
     * @return string Return absolute path to private font files folder. Path must be protected.
     */
    public function getFontsFilesPath(): string;

    /**
     * @return string return relative path to private font files folder
     */
    public function getFontsFilesBasePath(): string;

    /**
     * @return string return absolute path to public images cache
     */
    public function getPublicCachePath(): string;

    /**
     * @return string return relative path to public images cache
     */
    public function getPublicCacheBasePath(): string;
}
