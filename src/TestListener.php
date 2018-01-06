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

use PHPUnit\Framework\TestListener as TestListenerInterface;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

final class TestListener implements TestListenerInterface
{
    use TestListenerDefaultImplementation;

    /**
     * @var ProfileCollection
     */
    private $profiles;

    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * @var int
     */
    private $testSuites = 0;

    /**
     * @throws InvalidTargetDirectoryException
     * @throws TidewaysExtensionNotLoadedException
     */
    public function __construct(string $targetDirectory = '/tmp')
    {
        $this->ensureTargetDirectoryIsWritable($targetDirectory);
        $this->ensureProfilerIsAvailable();

        $this->targetDirectory = $targetDirectory;
        $this->profiles        = new ProfileCollection;
    }

    public function startTestSuite(TestSuite $suite): void
    {
        $this->testSuites++;
    }

    public function endTestSuite(TestSuite $suite): void
    {
        $this->testSuites--;

        if ($this->testSuites === 0) {
            \file_put_contents(
                $this->targetDirectory . \DIRECTORY_SEPARATOR . \uniqid('phpunit_tideways_'),
                \serialize($this->profiles)
            );
        }
    }

    public function startTest(Test $test)
    {
        if (!$test instanceof TestCase) {
            return;
        }

        \tideways_xhprof_enable(\TIDEWAYS_XHPROF_FLAGS_MEMORY | \TIDEWAYS_XHPROF_FLAGS_CPU);
    }

    public function endTest(Test $test, $time)
    {
        if (!$test instanceof TestCase) {
            return;
        }

        $this->profiles->add(
            new Profile(
                \get_class($test),
                $test->getName(false),
                $test->dataDescription(),
                \tideways_xhprof_disable()
            )
        );
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
}
