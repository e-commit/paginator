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

namespace Ecommit\Paginator\Tests\Paginator;

use Ecommit\Paginator\AbstractPaginator;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type PaginatorOptions array{
 *      page?: mixed,
 *      max_per_page?: int,
 *      iterator: \ArrayIterator<int|string, mixed>
 * }
 * @phpstan-type PaginatorResolvedOptions array{
 *      page: int<0, max>,
 *      max_per_page: int<0, max>,
 *      iterator: \ArrayIterator<int|string, mixed>
 * }
 *
 * @template-extends AbstractPaginator<mixed, mixed, PaginatorOptions, PaginatorResolvedOptions>
 */
class TestPaginator extends AbstractPaginator
{
    protected function buildCount(): int
    {
        return \count($this->getOption('iterator')->getArrayCopy());
    }

    protected function buildIterator(): \Traversable
    {
        return $this->getOption('iterator');
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('iterator');
    }
}
