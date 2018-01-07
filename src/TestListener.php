<?php declare(strict_types=1);
/*
 * This file is part of the phpunit-tideways-listener.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Tideways;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener as TestListenerInterface;
use PHPUnit\Framework\TestListenerDefaultImplementation;

final class TestListener implements TestListenerInterface
{
    use TestListenerDefaultImplementation;

    /**
     * @var string
     */
    private $targetDirectory;

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

    public function startTest(Test $test): void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        \tideways_xhprof_enable(\TIDEWAYS_XHPROF_FLAGS_MEMORY | \TIDEWAYS_XHPROF_FLAGS_CPU);
    }

    public function endTest(Test $test, $time): void
    {
        if (!$test instanceof TestCase) {
            return;
        }

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

    private function fileName(TestCase $test): string
    {
        $id = \str_replace('\\', '_', \get_class($test)) . '::' . $test->getName(false);

        if (!empty($test->dataDescription())) {
            $id .= '#' . \str_replace(' ', '_', $test->dataDescription());
        }

        return $this->targetDirectory . DIRECTORY_SEPARATOR . $id . '.json';
    }
}
