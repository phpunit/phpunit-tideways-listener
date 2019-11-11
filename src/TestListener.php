<?php declare(strict_types=1);
/*
 * This file is part of phpunit/phpunit-tideways-listener.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Tideways;

use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;

final class TestListener implements AfterTestHook, BeforeTestHook
{
    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * @var array<string,int>
     */
    private $index = [];

    /**
     * @throws InvalidTargetDirectoryException
     * @throws TidewaysExtensionNotLoadedException
     */
    public function __construct(string $targetDirectory = '/tmp')
    {
        $this->ensureTargetDirectoryIsWritable($targetDirectory);
        $this->ensureProfilerIsAvailable();

        $this->targetDirectory = \realpath($targetDirectory);
    }

    public function executeBeforeTest(string $test): void
    {
        \tideways_xhprof_enable(\TIDEWAYS_XHPROF_FLAGS_MEMORY | \TIDEWAYS_XHPROF_FLAGS_CPU);
    }

    public function executeAfterTest(string $test, float $time): void
    {
        $data = \tideways_xhprof_disable();

        \file_put_contents($this->fileName($test), \json_encode($data));
    }

    /**
     * @throws TidewaysExtensionNotLoadedException
     */
    private function ensureProfilerIsAvailable(): void
    {
        if (!\extension_loaded('tideways_xhprof')) {
            throw new TidewaysExtensionNotLoadedException;
        }
    }

    /**
     * @throws InvalidTargetDirectoryException
     */
    private function ensureTargetDirectoryIsWritable(string $directory): void
    {
        if (!@\mkdir($directory) && !\is_dir($directory)) {
            throw new InvalidTargetDirectoryException;
        }
    }

    private function fileName(string $test): string
    {
        if (\strpos($test, 'with data set') !== false) {
            $test = \substr($test, 0, \strpos($test, 'with data set'));

            if (!isset($this->index[$test])) {
                $this->index[$test] = 1;
            } else {
                $this->index[$test]++;
            }

            $test .= '_' . $this->index[$test];
        }

        return $this->targetDirectory . \DIRECTORY_SEPARATOR . \str_replace(['\\', '::', ' '], '_', $test) . '.json';
    }
}
