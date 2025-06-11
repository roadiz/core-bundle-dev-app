<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Theme;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ThemeGenerator
{
    public const string METHOD_COPY = 'copy';
    public const string METHOD_ABSOLUTE_SYMLINK = 'absolute symlink';
    public const string METHOD_RELATIVE_SYMLINK = 'relative symlink';

    protected Filesystem $filesystem;

    public function __construct(
        protected readonly string $projectDir,
        protected readonly string $publicDir,
        protected readonly string $cacheDir,
        protected readonly LoggerInterface $logger,
    ) {
        $this->filesystem = new Filesystem();
    }

    public function installThemeAssets(ThemeInfo $themeInfo, string $expectedMethod): ?string
    {
        if ($themeInfo->exists()) {
            $publicThemeDir = $this->publicDir.'/themes/'.$themeInfo->getThemeName();
            if ($publicThemeDir !== $themeInfo->getThemePath()) {
                $targetDir = $publicThemeDir.'/static';
                $originDir = $themeInfo->getThemePath().'/static';

                $this->filesystem->remove($publicThemeDir);
                $this->filesystem->mkdir($publicThemeDir);

                if (static::METHOD_RELATIVE_SYMLINK === $expectedMethod) {
                    return $this->relativeSymlinkWithFallback($originDir, $targetDir);
                } elseif (static::METHOD_ABSOLUTE_SYMLINK === $expectedMethod) {
                    return $this->absoluteSymlinkWithFallback($originDir, $targetDir);
                } else {
                    return $this->hardCopy($originDir, $targetDir);
                }
            } else {
                $this->logger->info($themeInfo->getThemeName().' assets are already public.');
            }
        }

        return null;
    }

    /**
     * Try to create relative symlink.
     *
     * Falling back to absolute symlink and finally hard copy.
     */
    private function relativeSymlinkWithFallback(string $originDir, string $targetDir): string
    {
        try {
            $this->symlink($originDir, $targetDir, true);
            $method = self::METHOD_RELATIVE_SYMLINK;
        } catch (IOException) {
            $method = $this->absoluteSymlinkWithFallback($originDir, $targetDir);
        }

        return $method;
    }

    /**
     * Try to create absolute symlink.
     *
     * Falling back to hard copy.
     */
    private function absoluteSymlinkWithFallback(string $originDir, string $targetDir): string
    {
        try {
            $this->symlink($originDir, $targetDir);
            $method = self::METHOD_ABSOLUTE_SYMLINK;
        } catch (IOException) {
            // fall back to copy
            $method = $this->hardCopy($originDir, $targetDir);
        }

        return $method;
    }

    /**
     * Creates symbolic link.
     */
    private function symlink(string $originDir, string $targetDir, bool $relative = false): void
    {
        if ($relative) {
            $this->filesystem->mkdir(dirname($targetDir));
            $realTargetParentDir = realpath(dirname($targetDir));
            if (false === $realTargetParentDir) {
                throw new IOException(sprintf('Cannot resolve realpath for "%s" dirname.', $targetDir));
            }
            $originDir = $this->filesystem->makePathRelative($originDir, $realTargetParentDir);
        }
        $this->filesystem->symlink($originDir, $targetDir);
        if (!file_exists($targetDir)) {
            throw new IOException(sprintf('Symbolic link "%s" was created but appears to be broken.', $targetDir), 0, null, $targetDir);
        }
    }

    /**
     * Copies origin to target.
     */
    private function hardCopy(string $originDir, string $targetDir): string
    {
        try {
            $this->filesystem->mkdir($targetDir, 0777);
            // We use a custom iterator to ignore VCS files
            $this->filesystem->mirror(
                $originDir,
                $targetDir,
                Finder::create()->ignoreDotFiles(false)->in($originDir)
            );
        } catch (IOException) {
            // Do nothing
        }

        return static::METHOD_COPY;
    }
}
