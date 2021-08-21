# Paginator

Create a paginator.

![Tests](https://github.com/e-commit/paginator/workflows/Tests/badge.svg)

## Installation ##

To install paginator with Composer just run :

```bash
$ composer require ecommit/paginator
```


## Usage ##

```php
use Ecommit\Paginator\ArrayPaginator;

//Create a paginator
$paginator = new ArrayPaginator([
    //Options
    'page' => 1,
    'max_per_page' => 100,
    'data' => ['val1', 'val2', 'val3'],
    //Or with an ArrayIterator
    //'data' => new \ArrayIterator(['val1', 'val2', 'val3']),
]);

$totalPages = $paginator->getLastPage();
$totalResults = \count($paginator);
foreach ($paginator as $result) {
    //...
}
```

### Available options

| Option | Type | Required | Default value | Description |
| --- | --- | --- | --- | --- |
| **page** | Integer | No | 1 | Current page |
| **max_per_page** | Integer | No | 100 | Max elements per page |
| **data** | Array or ArrayIterator | Yes | | <ul><li>If `count_results` option is null : All data (of all pages)</li><li>If `count_results` option is not null : Only the data to display on the current page</li></ul> |
| **count_results** | Integer or null | No | Null | *You can use this option when the data volume is too large.* If the value is not null :<ul><li>It must equal the total number of results</li><li>The `data` option must contain only the data to display on the current page</li></ul>  |

### Available methods

See [API documentation](src/PaginatorInterface.php)

## License ##

This librairy is under the MIT license. See the complete license in *LICENSE* file.
