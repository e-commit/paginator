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

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractPaginator implements PaginatorInterface
{
    private $page;
    private $maxPerPage;

    private $iterator;
    private $countResults;
    private $lastPage;

    private $pageExists = true;
    private $paginationIsInitialized = false;
    private $iteratorIsInitialized = false;

    final public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'page' => 1,
            'max_per_page' => 100,
        ]);
        //Page option: Not use "setAllowedTypes" (because user input. Value checked by setPage method)
        $resolver->setAllowedTypes('max_per_page', 'int');
        $resolver->setAllowedValues('max_per_page', function ($value) {
            return $value > 0;
        });
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        $this->setPage($options['page']);
        $this->setMaxPerPage($options['max_per_page']);
        unset($options['page']);
        unset($options['max_per_page']);

        $this->buildPagination($options);
        $this->iterator = $this->buildIterator($options);
        $this->iteratorIsInitialized = true;

        return $this;
    }

    abstract protected function buildCountResults(array $options): int;

    abstract protected function buildIterator(array $options): \Traversable;

    private function buildPagination(array $options): void
    {
        $this->countResults = $this->buildCountResults($options);

        $lastPage = 1;
        if ($this->countResults > 0) {
            $lastPage = (int) ceil($this->countResults / $this->getMaxPerPage());
        }

        if ($this->getPage() > $lastPage) {
            $this->page = $lastPage;
            $this->pageExists = false;
        }

        $this->lastPage = $lastPage;
        $this->paginationIsInitialized = true;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function count(): int
    {
        $this->testIfPaginationIsInitialized();

        return $this->countResults;
    }

    public function haveToPaginate(): bool
    {
        $this->testIfPaginationIsInitialized();

        return $this->count() > $this->getMaxPerPage();
    }

    public function getFirstIndice(): int
    {
        $this->testIfPaginationIsInitialized();

        if (0 === $this->count()) {
            return 0;
        }

        return ($this->getPage() - 1) * $this->getMaxPerPage() + 1;
    }

    public function getLastIndice(): int
    {
        $this->testIfPaginationIsInitialized();

        if ($this->getPage() * $this->getMaxPerPage() >= $this->count()) {
            return $this->count();
        }

        return $this->getPage() * $this->getMaxPerPage();
    }

    public function getFirstPage(): int
    {
        $this->testIfPaginationIsInitialized();

        return 1;
    }

    public function getPreviousPage(): ?int
    {
        $this->testIfPaginationIsInitialized();

        if ($this->isFirstPage()) {
            return null;
        }

        return $this->getPage() - 1;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function pageExists(): bool
    {
        return $this->pageExists;
    }

    public function getNextPage(): ?int
    {
        $this->testIfPaginationIsInitialized();

        if ($this->getPage() >= $this->getLastPage()) {
            return null;
        }

        return $this->getPage() + 1;
    }

    public function getLastPage(): int
    {
        $this->testIfPaginationIsInitialized();

        return $this->lastPage;
    }

    public function isFirstPage(): bool
    {
        $this->testIfPaginationIsInitialized();

        return 1 === $this->getPage();
    }

    public function isLastPage(): bool
    {
        $this->testIfPaginationIsInitialized();

        return $this->getPage() === $this->getLastPage();
    }

    private function setPage($page): self
    {
        if (null === $page || !is_scalar($page) || !preg_match('/^\d+$/', (string) $page)) {
            $page = 1;
            $this->pageExists = false;
        }
        $page = (int) $page;

        if ($page <= 0) {
            $page = 1;
            $this->pageExists = false;
        }

        $this->page = $page;

        return $this;
    }

    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    private function setMaxPerPage(int $maxPerPage): self
    {
        $this->maxPerPage = $maxPerPage;

        if ($this->maxPerPage <= 0) {
            throw new \Exception('Max results value must be positive');
        }

        return $this;
    }

    public function getIterator(): \Traversable
    {
        if (!$this->iteratorIsInitialized) {
            throw new \Exception('The iterator must be initialized');
        }

        return $this->iterator;
    }

    public function isInitialized(): bool
    {
        return $this->paginationIsInitialized && $this->iteratorIsInitialized;
    }

    protected function testIfPaginationIsInitialized(): void
    {
        if (!$this->paginationIsInitialized) {
            throw new \Exception('The pagination must be initialized');
        }
    }
}
