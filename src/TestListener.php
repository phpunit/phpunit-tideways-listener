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
     * @var string
     */
    private $targetDirectory;

    /**
     * @var int
     */
    private $testSuites = 0;

    /**
     * @var int
     */
    private $tests = 0;

    /**
     * @throws InvalidTargetDirectoryException
     * @throws TidewaysExtensionNotLoadedException
     */
    public function __construct(string $targetDirectory = '/tmp')
    {
        $this->ensureTargetDirectoryIsWritable($targetDirectory);
        $this->ensureProfilerIsAvailable();

        $this->targetDirectory = \realpath($targetDirectory) . \DIRECTORY_SEPARATOR;
        $this->filenamePrefix  = \uniqid('phpunit_tideways_');
    }

    public function startTestSuite(TestSuite $suite): void
    {
        $this->testSuites++;
    }

    public function endTestSuite(TestSuite $suite): void
    {
        $this->testSuites--;

        if ($this->testSuites === 0) {
            $profiles = new ProfileCollection;

            foreach (\glob($this->targetDirectory . $this->filenamePrefix . '*') as $fileName) {
                $profiles->add(
                    \unserialize(
                        \file_get_contents($fileName),
                        [ProfileCollection::class, Profile::class]
                    )
                );

                \unlink($fileName);
            }

            \file_put_contents(
                $this->targetDirectory . $this->filenamePrefix,
                \serialize($profiles)
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

        \file_put_contents(
            $this->profileFileName(),
            \serialize(
                new Profile(
                    \get_class($test),
                    $test->getName(false),
                    $test->dataDescription(),
                    \tideways_xhprof_disable()
                )
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

    private function profileFileName(): string
    {
        return \sprintf(
            '%s%s_%010d',
            $this->targetDirectory,
            $this->filenamePrefix,
            ++$this->tests
        );
    }
}
