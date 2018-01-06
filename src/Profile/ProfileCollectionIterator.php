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

final class ProfileCollectionIterator implements \Iterator
{
    /**
     * @var Profile[]
     */
    private $profiles;

    /**
     * @var int
     */
    private $position;

    public function __construct(ProfileCollection $collection)
    {
        $this->profiles = $collection->asArray();
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < \count($this->profiles);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): Profile
    {
        return $this->profiles[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
