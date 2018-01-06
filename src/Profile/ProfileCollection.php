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

final class ProfileCollection implements \IteratorAggregate
{
    /**
     * @var Profile[]
     */
    private $profiles = [];

    public function add(Profile $profile): void
    {
        $this->profiles[] = $profile;
    }

    /**
     * @return Profile[]
     */
    public function asArray(): array
    {
        return $this->profiles;
    }

    public function getIterator(): ProfileCollectionIterator
    {
        return new ProfileCollectionIterator($this);
    }
}
