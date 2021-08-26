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

class ArrayPaginator extends AbstractPaginator
{
    protected function buildCountResults(): int
    {
        return (null === $this->getOption('count_results')) ? \count($this->getOption('data')) : $this->getOption('count_results');
    }

    protected function buildIterator(): \Traversable
    {
        if (null === $this->getOption('count_results')) {
            $offset = 0;
            $limit = 0;
            if ($this->count() > 0) {
                $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
                $limit = $this->getMaxPerPage();
            }
            $partialData = \array_slice($this->getOption('data'), $offset, $limit);

            return new \ArrayIterator($partialData);
        }

        return new \ArrayIterator($this->getOption('data'));
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('data');
        $resolver->setAllowedTypes('data', ['array', \ArrayIterator::class]);
        $resolver->setNormalizer('data', function (Options $options, $value) {
            if ($value instanceof \ArrayIterator) {
                return $value->getArrayCopy();
            }

            return $value;
        });

        $resolver->setDefault('count_results', null);
        $resolver->setAllowedTypes('count_results', ['int', 'null']);
        $resolver->setAllowedValues('count_results', function ($value) {
            return null === $value || $value >= 0;
        });
    }
}
