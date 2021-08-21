<?php

declare(strict_types=1);

/*
 * This file is part of the ecommit/paginator package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\Paginator\Tests;

trait BuildArrayIteratorTrait
{
    protected $defaultIterator;

    protected function createIterator(array $data): \ArrayIterator
    {
        return new \ArrayIterator($data);
    }

    protected function getDefaultIterator(): \ArrayIterator
    {
        if (null === $this->defaultIterator) {
            $this->defaultIterator = $this->createIterator(range(0, 51));
        }

        return $this->defaultIterator;
    }
}
