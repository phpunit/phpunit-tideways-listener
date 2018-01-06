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

final class Profile
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $testClassName;

    /**
     * @var string
     */
    private $testMethodName;

    /**
     * @var string
     */
    private $testDataDescription;

    public function __construct(string $testClassName, $testMethodName, $testDataDescription, array $data)
    {
        $this->data                = $data;
        $this->testClassName       = $testClassName;
        $this->testMethodName      = $testMethodName;
        $this->testDataDescription = $testDataDescription;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function testClassName(): string
    {
        return $this->testClassName;
    }

    public function testMethodName(): string
    {
        return $this->testMethodName;
    }

    public function testDataDescription(): string
    {
        return $this->testDataDescription;
    }
}
