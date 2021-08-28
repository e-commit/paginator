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

use Ecommit\Paginator\AbstractPaginator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbtractPaginatorTest extends TestCase
{
    use BuildArrayIteratorTrait;

    /**
     * @dataProvider getTestBadPageOptionProvider
     */
    public function testBadPageOption($page, $expectedPage): void
    {
        $options = $this->getDefaultOptions();
        $options['page'] = $page;
        $paginator = $this->createPaginator($options);

        $this->assertSame($expectedPage, $paginator->getPage());
        $this->assertFalse($paginator->pageExists());
    }

    public function getTestBadPageOptionProvider(): array
    {
        return [
            ['string', 1],
            [-1, 1],
            [100000, 11],
        ];
    }

    public function testDefaultPageOption(): void
    {
        $options = $this->getDefaultOptions();
        unset($options['page']);
        $paginator = $this->createPaginator($options);

        $this->assertSame(1, $paginator->getPage());
    }

    public function testBadFormatMaxPerPageOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('"max_per_page"');

        $options = $this->getDefaultOptions();
        $options['max_per_page'] = 'string';
        $this->createPaginator($options);
    }

    public function testBadNumberMaxPerPageOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('"max_per_page"');

        $options = $this->getDefaultOptions(1, -5, $this->getDefaultIterator());
        $this->createPaginator($options);
    }

    public function testDefaultMaxPerPageOption(): void
    {
        $options = $this->getDefaultOptions();
        unset($options['max_per_page']);
        $paginator = $this->createPaginator($options);

        $this->assertSame(100, $paginator->getMaxPerPage());
    }

    public function testGetOptions(): void
    {
        $options = $this->getDefaultOptions();
        $paginator = $this->createPaginator($options);

        $this->assertSame($options, $paginator->getOptions());
    }

    public function testGetOptionsReadOnly(): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions());
        $options = $paginator->getOptions();
        $options['page'] = 8;

        $this->assertSame(1, $paginator->getOptions()['page']);
    }

    public function testGetOption(): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions());

        $this->assertSame(1, $paginator->getOption('page'));
    }

    public function testGetOptionNotFound(): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Option "bad" not found');

        $paginator->getOption('bad');
    }

    public function testCount(): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions());
        $this->assertSame(52, $paginator->count());
        $this->assertCount(52, $paginator);
    }

    public function testCountWithoutData(): void
    {
        $options = $this->getDefaultOptions(1, 5, $this->createIterator([]));
        $paginator = $this->createPaginator($options);
        $this->assertSame(0, $paginator->count());
        $this->assertCount(0, $paginator);
    }

    public function testCountIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->count();
    }

    public function testHaveToPaginate(): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions());
        $this->assertTrue($paginator->haveToPaginate());
    }

    public function testHaveToPaginateNotEnoughData(): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions(1, 500));

        $this->assertFalse($paginator->haveToPaginate());
    }

    public function testHaveToPaginateWithoutData(): void
    {
        $options = $this->getDefaultOptions(1, 5, $this->createIterator([]));
        $paginator = $this->createPaginator($options);

        $this->assertFalse($paginator->haveToPaginate());
    }

    public function testHaveToPaginatefIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->haveToPaginate();
    }

    /**
     * @dataProvider getTestGetFirstIndiceProvider
     */
    public function testGetFirstIndice($page, int $maxPerPage, \ArrayIterator $iterator, int $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->getFirstIndice());
    }

    public function getTestGetFirstIndiceProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), 1],
            [3, 5, $this->getDefaultIterator(), 11],
            [11, 5, $this->getDefaultIterator(), 51],
            [1, 5, $this->createIterator([]), 0], //No data
            ['page', 5, $this->getDefaultIterator(), 1], //Bad page
            [1000, 5, $this->getDefaultIterator(), 51], //Page too high
        ];
    }

    public function testGetFirstIndiceIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->getFirstIndice();
    }

    /**
     * @dataProvider getTestGetLastIndiceProvider
     */
    public function testGetLastIndice($page, int $maxPerPage, \ArrayIterator $iterator, int $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->getLastIndice());
    }

    public function getTestGetLastIndiceProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), 5],
            [3, 5, $this->getDefaultIterator(), 15],
            [11, 5, $this->getDefaultIterator(), 52],
            [1, 5, $this->createIterator([]), 0], //No data
            ['page', 5, $this->getDefaultIterator(), 5], //Bad page
            [1000, 5, $this->getDefaultIterator(), 52], //Page too high
        ];
    }

    public function testGetLastIndiceIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->getLastIndice();
    }

    /**
     * @dataProvider getTestGetFirstPageProvider
     */
    public function testGetFirstPage($page, int $maxPerPage, \ArrayIterator $iterator, int $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->getFirstPage());
    }

    public function getTestGetFirstPageProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), 1],
            [3, 5, $this->getDefaultIterator(), 1],
            [11, 5, $this->getDefaultIterator(), 1],
            [1, 5, $this->createIterator([]), 1], //No data
            ['page', 5, $this->getDefaultIterator(), 1], //Bad page
            [1000, 5, $this->getDefaultIterator(), 1], //Page too high
        ];
    }

    public function testGetFirstPageIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->getFirstPage();
    }

    /**
     * @dataProvider getTestGetPreviousPageProvider
     */
    public function testGetPreviousPage($page, int $maxPerPage, \ArrayIterator $iterator, ?int $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->getPreviousPage());
    }

    public function getTestGetPreviousPageProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), null],
            [3, 5, $this->getDefaultIterator(), 2],
            [11, 5, $this->getDefaultIterator(), 10],
            [1, 5, $this->createIterator([]), null], //No data
            ['page', 5, $this->getDefaultIterator(), null], //Bad page
            [1000, 5, $this->getDefaultIterator(), 10], //Page too high
        ];
    }

    public function testGetPreviousPageIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->getPreviousPage();
    }

    /**
     * @dataProvider getTestGetPageProvider
     */
    public function testGetPage($page, int $maxPerPage, \ArrayIterator $iterator, int $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->getPage());
    }

    public function getTestGetPageProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), 1],
            [3, 5, $this->getDefaultIterator(), 3],
            [11, 5, $this->getDefaultIterator(), 11],
            [1, 5, $this->createIterator([]), 1], //No data
            ['page', 5, $this->getDefaultIterator(), 1], //Bad page
            [1000, 5, $this->getDefaultIterator(), 11], //Page too high
        ];
    }

    /**
     * @dataProvider getTestPageExistsProdiver
     */
    public function testPageExists($page, int $maxPerPage, \ArrayIterator $iterator, bool $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->pageExists());
    }

    public function getTestPageExistsProdiver(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), true],
            [3, 5, $this->getDefaultIterator(), true],
            [11, 5, $this->getDefaultIterator(), true],
            [1, 5, $this->createIterator([]), true], //No data
            ['page', 5, $this->getDefaultIterator(), false], //Bad page
            [1000, 5, $this->getDefaultIterator(), false], //Page too high
        ];
    }

    /**
     * @dataProvider getTestGetNextPageProvider
     */
    public function testGetNextPage($page, int $maxPerPage, \ArrayIterator $iterator, ?int $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->getNextPage());
    }

    public function getTestGetNextPageProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), 2],
            [3, 5, $this->getDefaultIterator(), 4],
            [11, 5, $this->getDefaultIterator(), null],
            [1, 5, $this->createIterator([]), null], //No data
            ['page', 5, $this->getDefaultIterator(), 2], //Bad page
            [1000, 5, $this->getDefaultIterator(), null], //Page too high
        ];
    }

    public function testGetNextPageIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->getNextPage();
    }

    /**
     * @dataProvider getTestGetLastPageProvider
     */
    public function testGetLastPage($page, int $maxPerPage, \ArrayIterator $iterator, int $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->getLastPage());
    }

    public function getTestGetLastPageProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), 11],
            [3, 5, $this->getDefaultIterator(), 11],
            [11, 5, $this->getDefaultIterator(), 11],
            [1, 5, $this->createIterator([]), 1], //No data
            ['page', 5, $this->getDefaultIterator(), 11], //Bad page
            [1000, 5, $this->getDefaultIterator(), 11], //Page too high
        ];
    }

    public function testGetLastPageIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->getLastPage();
    }

    /**
     * @dataProvider getTestIsFirstPageProvider
     */
    public function testIsFirstPage($page, int $maxPerPage, \ArrayIterator $iterator, bool $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->isFirstPage());
    }

    public function getTestIsFirstPageProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), true],
            [3, 5, $this->getDefaultIterator(), false],
            [11, 5, $this->getDefaultIterator(), false],
            [1, 5, $this->createIterator([]), true], //No data
            ['page', 5, $this->getDefaultIterator(), true], //Bad page
            [1000, 5, $this->getDefaultIterator(), false], //Page too high
        ];
    }

    public function testIsFirstPageIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->isFirstPage();
    }

    /**
     * @dataProvider getTestIsLastPageProvider
     */
    public function testIsLastPage($page, int $maxPerPage, \ArrayIterator $iterator, bool $expectedResult): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($expectedResult, $paginator->isLastPage());
    }

    public function getTestIsLastPageProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator(), false],
            [3, 5, $this->getDefaultIterator(), false],
            [11, 5, $this->getDefaultIterator(), true],
            [1, 5, $this->createIterator([]), true], //No data
            ['page', 5, $this->getDefaultIterator(), false], //Bad page
            [1000, 5, $this->getDefaultIterator(), true], //Page too high
        ];
    }

    public function testIsLastPageIfPaginationNotInitialized(): void
    {
        $paginator = $this->createPaginatorMockWithoutConstructor();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The pagination must be initialized');

        $paginator->isLastPage();
    }

    public function testGetMaxPerPage(): void
    {
        $options = $this->getDefaultOptions(1, 88, $this->getDefaultIterator());
        $paginator = $this->createPaginator($options);

        $this->assertSame(88, $paginator->getMaxPerPage());
    }

    /**
     * @dataProvider getTestGetIteratorProvider
     */
    public function testGetIterator($page, int $maxPerPage, \ArrayIterator $iterator): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));

        $this->assertSame($iterator, $paginator->getIterator());
    }

    public function getTestGetIteratorProvider(): array
    {
        return [
            [1, 5, $this->getDefaultIterator()],
            [3, 5, $this->getDefaultIterator()],
            [11, 5, $this->getDefaultIterator()],
            [1, 5, $this->createIterator([])], //No data
            ['page', 5, $this->getDefaultIterator()], //Bad page
            [1000, 5, $this->getDefaultIterator()], //Page too high
        ];
    }

    /**
     * @dataProvider getTestGetIteratorProvider
     */
    public function testIterate($page, int $maxPerPage, \ArrayIterator $iterator): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions($page, $maxPerPage, $iterator));
        $expectedCount = \count($iterator);

        $count = 0;
        foreach ($paginator as $result) {
            ++$count;
        }

        $this->assertSame($expectedCount, $count);
    }

    public function testIsInitializedTrue(): void
    {
        $paginator = $this->createPaginator($this->getDefaultOptions());

        $this->assertTrue($paginator->isInitialized());
    }

    protected function getDefaultOptions($page = 1, $perPage = 5, $iterator = null): array
    {
        if (null === $iterator) {
            $iterator = $this->getDefaultIterator();
        }

        return [
            'page' => $page,
            'max_per_page' => $perPage,
            'iterator' => $iterator,
        ];
    }

    protected function createPaginator(array $options): AbstractPaginator
    {
        return new class($options) extends AbstractPaginator {
            protected function buildCount(): int
            {
                return \count($this->getOption('iterator'));
            }

            protected function buildIterator(): \Traversable
            {
                return $this->getOption('iterator');
            }

            protected function configureOptions(OptionsResolver $resolver): void
            {
                $resolver->setRequired('iterator');
            }
        };
    }

    protected function createPaginatorMockWithoutConstructor(): AbstractPaginator
    {
        return $this->getMockBuilder(AbstractPaginator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildCount', 'buildIterator'])
            ->getMock();
    }
}
