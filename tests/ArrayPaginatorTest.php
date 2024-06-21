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

use Ecommit\Paginator\ArrayPaginator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * @phpstan-import-type PaginatorOptions from ArrayPaginator
 */
class ArrayPaginatorTest extends TestCase
{
    use BuildArrayIteratorTrait;

    protected ?array $defaultArray = null;

    public function testMissingDataOption(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('"data"');

        $options = $this->getDefaultOptions();
        unset($options['data']);
        $this->createPaginator($options); // @phpstan-ignore-line
    }

    public function testBadTypeDataOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('"data"');

        $options = $this->getDefaultOptions();
        $options['data'] = 'string';
        $this->createPaginator($options);  // @phpstan-ignore-line
    }

    public function testBadTypeCountOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('"count"');

        $options = $this->getDefaultOptions();
        $options['count'] = 'string';
        $this->createPaginator($options);  // @phpstan-ignore-line
    }

    public function testBadNumberCountOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('"count"');

        $options = $this->getDefaultOptions();
        $options['count'] = -5;
        $this->createPaginator($options); // @phpstan-ignore-line
    }

    /**
     * @dataProvider getTestCountProvider
     *
     * @param int<1, max>                             $maxPerPage
     * @param \ArrayIterator<int|string, mixed>|array $data
     */
    public function testCount(mixed $page, int $maxPerPage, \ArrayIterator|array $data, int $expectedValue): void
    {
        $options = $this->getDefaultOptions($page, $maxPerPage, $data);
        $paginator = $this->createPaginator($options);

        $this->assertCount($expectedValue, $paginator);
    }

    public function getTestCountProvider(): array
    {
        return [
            [1, 5, $this->getDefaultArray(), 52],
            [3, 5, $this->getDefaultArray(), 52],
            [11, 5, $this->getDefaultArray(), 52],
            [1, 5, [], 0], // No data
            ['page', 5, $this->getDefaultArray(), 52], // Bad page
            [1000, 5, $this->getDefaultArray(), 52], // Page too high

            [1, 5, $this->getDefaultIterator(), 52],
            [3, 5, $this->getDefaultIterator(), 52],
            [11, 5, $this->getDefaultIterator(), 52],
            [1, 5, $this->createIterator([]), 0], // No data
            ['page', 5, $this->getDefaultIterator(), 52], // Bad page
            [1000, 5, $this->getDefaultIterator(), 52], // Page too high
        ];
    }

    /**
     * @dataProvider getTestGetIteratorProvider
     *
     * @param int<1, max>                             $maxPerPage
     * @param \ArrayIterator<int|string, mixed>|array $data
     * @param \ArrayIterator<int|string, mixed>       $expectedValue
     */
    public function testGetIterator(mixed $page, int $maxPerPage, \ArrayIterator|array $data, \ArrayIterator $expectedValue): void
    {
        $options = $this->getDefaultOptions($page, $maxPerPage, $data);
        $paginator = $this->createPaginator($options);

        $this->assertEquals($expectedValue, $paginator->getIterator());
    }

    public function getTestGetIteratorProvider(): array
    {
        return [
            [1, 5, $this->getDefaultArray(), new \ArrayIterator([0, 1, 2, 3, 4])],
            [3, 5, $this->getDefaultArray(), new \ArrayIterator([10, 11, 12, 13, 14])],
            [11, 5, $this->getDefaultArray(), new \ArrayIterator([50, 51])],
            [1, 5, [], new \ArrayIterator()], // No data
            ['page', 5, $this->getDefaultArray(), new \ArrayIterator([0, 1, 2, 3, 4])], // Bad page
            [1000, 5, $this->getDefaultArray(), new \ArrayIterator([50, 51])], // Page too high

            [1, 5, $this->getDefaultIterator(), new \ArrayIterator([0, 1, 2, 3, 4])],
            [3, 5, $this->getDefaultIterator(), new \ArrayIterator([10, 11, 12, 13, 14])],
            [11, 5, $this->getDefaultIterator(), new \ArrayIterator([50, 51])],
            [1, 5, $this->createIterator([]), new \ArrayIterator()], // No data
            ['page', 5, $this->getDefaultIterator(), new \ArrayIterator([0, 1, 2, 3, 4])], // Bad page
            [1000, 5, $this->getDefaultIterator(), new \ArrayIterator([50, 51])], // Page too high
        ];
    }

    /**
     * @dataProvider getTestCountWithCountProvider
     *
     * @param int<1, max>                             $maxPerPage
     * @param \ArrayIterator<int|string, mixed>|array $data
     * @param \ArrayIterator<int|string, mixed>       $expectedIterator
     * @param int<0, max>                             $count
     */
    public function testWithCount(mixed $page, int $maxPerPage, \ArrayIterator|array $data, int $count, int $expectedCountPages, \ArrayIterator $expectedIterator): void
    {
        $options = $this->getDefaultOptions($page, $maxPerPage, $data);
        $options['count'] = $count;
        $paginator = $this->createPaginator($options);

        $this->assertCount($count, $paginator);
        $this->assertSame($expectedCountPages, $paginator->getLastPage());
        $this->assertEquals($expectedIterator, $paginator->getIterator());
    }

    public function getTestCountWithCountProvider(): array
    {
        return [
            [1, 5, $this->getDefaultArray(), 202, 41, new \ArrayIterator(range(0, 51))],
            [3, 5, $this->getDefaultArray(), 202, 41, new \ArrayIterator(range(0, 51))],
            [11, 5, $this->getDefaultArray(), 202, 41, new \ArrayIterator(range(0, 51))],
            [1, 5, [], 0, 1, new \ArrayIterator()], // No data
            ['page', 5, $this->getDefaultArray(), 202, 41, new \ArrayIterator(range(0, 51))], // Bad page
            [1000, 5, $this->getDefaultArray(), 202, 41, new \ArrayIterator(range(0, 51))], // Page too high

            [1, 5, $this->getDefaultIterator(), 202, 41, new \ArrayIterator(range(0, 51))],
            [3, 5, $this->getDefaultIterator(), 202, 41, new \ArrayIterator(range(0, 51))],
            [11, 5, $this->getDefaultIterator(), 202, 41, new \ArrayIterator(range(0, 51))],
            [1, 5, $this->createIterator([]), 0, 1, new \ArrayIterator()], // No data
            ['page', 5, $this->getDefaultIterator(), 202, 41, new \ArrayIterator(range(0, 51))], // Bad page
            [1000, 5, $this->getDefaultIterator(), 202, 41, new \ArrayIterator(range(0, 51))], // Page too high
        ];
    }

    /**
     * @param int<1, max>                             $perPage
     * @param \ArrayIterator<int|string, mixed>|array $data
     *
     * @return PaginatorOptions
     */
    protected function getDefaultOptions(mixed $page = 1, int $perPage = 5, \ArrayIterator|array|null $data = null): array
    {
        if (null === $data) {
            $data = $this->getDefaultArray();
        }

        return [
            'page' => $page,
            'max_per_page' => $perPage,
            'data' => $data,
        ];
    }

    /**
     * @param PaginatorOptions $options
     *
     * @return ArrayPaginator<mixed, mixed>
     */
    protected function createPaginator(array $options): ArrayPaginator
    {
        return new ArrayPaginator($options);
    }

    protected function getDefaultArray(): array
    {
        if (null === $this->defaultArray) {
            $this->defaultArray = range(0, 51);
        }

        return $this->defaultArray;
    }
}
