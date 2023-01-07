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

namespace Ecommit\Paginator;

/**
 * @template TKey
 *
 * @template-covariant TValue
 *
 * @template TResolvedOptions of array<string, mixed>
 *
 * @template-extends \IteratorAggregate<TKey, TValue>
 */
interface PaginatorInterface extends \IteratorAggregate, \Countable
{
    /**
     * @return TResolvedOptions
     */
    public function getOptions(): array;

    /**
     * @template TResolvedOptionKey of key-of<TResolvedOptions>
     *
     * @param TResolvedOptionKey $option
     *
     * @return TResolvedOptions[TResolvedOptionKey]
     */
    public function getOption(string $option): mixed;

    public function haveToPaginate(): bool;

    public function getFirstIndice(): int;

    public function getLastIndice(): int;

    public function getFirstPage(): int;

    public function getPreviousPage(): ?int;

    public function getPage(): int;

    public function pageExists(): bool;

    public function getNextPage(): ?int;

    public function getLastPage(): int;

    public function isFirstPage(): bool;

    public function isLastPage(): bool;

    public function getMaxPerPage(): int;

    public function isInitialized(): bool;
}
