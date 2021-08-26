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

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractPaginator implements PaginatorInterface
{
    private $options;

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
        $resolver->setNormalizer('page', function (Options $options, $page): int {
            if (null === $page || !is_scalar($page) || !preg_match('/^\d+$/', (string) $page)) {
                $page = 1;
                $this->pageExists = false;
            }
            $page = (int) $page;

            if ($page <= 0) {
                $page = 1;
                $this->pageExists = false;
            }

            return $page;
        });
        $resolver->setAllowedTypes('max_per_page', 'int');
        $resolver->setAllowedValues('max_per_page', function ($value) {
            return $value > 0;
        });
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        $this->buildPagination($this->options);
        $this->iterator = $this->buildIterator($this->options);
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
            $this->options['page'] = $lastPage;
            $this->pageExists = false;
        }

        $this->lastPage = $lastPage;
        $this->paginationIsInitialized = true;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
    }

    final public function getOptions(): array
    {
        return $this->options;
    }

    final public function getOption(string $option)
    {
        if (\array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        throw new \Exception(sprintf('Option "%s" not found', $option));
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
        return $this->getOption('page');
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

    public function getMaxPerPage(): int
    {
        return $this->getOption('max_per_page');
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
