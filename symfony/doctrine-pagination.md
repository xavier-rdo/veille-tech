# Pagination avec Twig et Doctrine - DDD-friendly

## Les inputs


### Pagination query DTO

```php
<?php

declare(strict_types=1);

namespace App\Application\Common\Pagination;

/**
 * Pagination components extracted from a request.
 */
final class PaginationQuery
{
    public const DEFAULT_PER_PAGE = 20;

    private int $page;
    private int $perPage;

    /**
     * @param int|null $perPage Null to use default
     */
    public function __construct(int $page = 1, ?int $perPage = null)
    {
        $this->page = $page;
        $this->perPage = $perPage ?? self::DEFAULT_PER_PAGE;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getOffset(): int
    {
        return max($this->page - 1, 0) * $this->perPage;
    }
}
```

### Pagination DTO argument value resolver

```php
<?php

declare(strict_types=1);

namespace App\Infra\Bridge\Symfony\HttpKernel\Controller;

use App\Application\Common\Pagination\PaginationQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Resolves a {@link Pagination} object by extracting its components from request query params.
 * It also resolve the perPage value from the request attributes if not present in the query.
 */
class PaginationValueResolver implements ArgumentValueResolverInterface
{
    public const PER_PAGE_PARAM = 'perPage';
    public const PAGE_PARAM = 'page';

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === PaginationQuery::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield new Pagination(
            $request->query->getInt(self::PAGE_PARAM, 1),
            $request->query->getInt(self::PER_PAGE_PARAM, $request->attributes->getInt(self::PER_PAGE_PARAM, 20))
        );
    }
}
```

### Sort query argument value resolver

```php
<?php

declare(strict_types=1);

namespace App\Infra\Bridge\Symfony\HttpKernel\Controller;

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SortingValueResolver implements ArgumentValueResolverInterface
{
    public const PARAM = 'sort';

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === 'array' && $argument->getName() === 'sorters';
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        /** @var array<string, bool> $sorters */
        $sorters = $request->query->get(self::PARAM, null) ?? [];

        yield array_map(fn (string $value) => boolval($value) ? Criteria::ASC : Criteria::DESC, $sorters);
    }
}

```

### Usage dans les actions de contrôleur

```php
	public function list(Pagination $pagination, array $sorters, Request $request): Response
```

## Repository et types de retour

### Repository

```php
<?php

declare(strict_types=1);

namespace App\Infra\Repository;

use App\Domain\Common\Pagination\PaginatorInterface;
use App\Domain\Entity\User\DownloadPermission;
use App\Domain\Entity\User\Organization;
use App\Domain\Repository\OrganizationRepositoryInterface;
use App\Infra\Bridge\Doctrine\ORM\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Organization>
 */
class OrganizationRepository extends ServiceEntityRepository implements OrganizationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function listPaginated(int $page = 1, int $perPage = 20, array $filters = [], array $orderBy = []): PaginatorInterface
    {
        $queryBuilder = $this->createQueryBuilder('organization');

        $this->applyFilters($queryBuilder, $filters);
        $this->applyOrderBy($queryBuilder, $orderBy);

        return Paginator::createFromQueryBuilder($queryBuilder, $page, $perPage);
    }

    private function applyFilters(QueryBuilder $queryBuilder, array $filters, string $alias = 'organization'): void
    {
        if ($name = $filters['name'] ?? false) {
            $queryBuilder
                ->andWhere("$alias.name LIKE :name")
                ->setParameter('name', "%$name%")
            ;
        }
    }

    private function applyOrderBy(QueryBuilder $queryBuilder, array $orderBy, string $alias = 'organization'): void
    {
        foreach ($orderBy as $field => $direction) {
            $direction = null !== $direction ? strtoupper($direction) : null;

            if (!\in_array($direction, [Criteria::ASC, Criteria::DESC], true)) {
                $direction = Criteria::ASC;
            }

            $queryBuilder->addOrderBy("$alias.$field", $direction);
        }

        if (\count($orderBy) === 0) {
            $queryBuilder->addOrderBy("$alias.createdAt", Criteria::DESC);
        }

        $queryBuilder->addOrderBy("$alias.id", Criteria::DESC);
    }
}

```

### L'interface `PaginatorInterface`

```php

<?php

declare(strict_types=1);

namespace App\Domain\Common\Pagination;

/**
 * @template T
 * @template-extends \Traversable<int, T>
 */
interface PaginatorInterface extends \Traversable, \Countable
{
    /**
     * @return array<int, T>
     */
    public function getItems(): array;

    public function getCurrentPage(): int;

    public function getLastPage(): int;

    public function getItemsPerPage(): int;

    /**
     * Gets the number of items in the whole collection.
     */
    public function getTotalCount(): int;

    public function hasNextPage(): bool;

    /**
     * @return bool True if the asked page doesn't exist (no items)
     */
    public function isPageOutOfBounds(): bool;
}


```

### Paginator concret (Doctrine)

```php
<?php

declare(strict_types=1);

namespace App\Infra\Bridge\Doctrine\ORM\Pagination;

use App\Domain\Common\Pagination\PaginatorInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination as ORM;

/**
 * @template T
 *
 * @template-implements \IteratorAggregate<int, T>
 * @template-implements PaginatorInterface<T>
 */
class Paginator implements \IteratorAggregate, PaginatorInterface
{
    private ORM\Paginator $paginator;
    private Query $query;
    private int $firstResult;
    private int $maxResults;
    private int $totalItems;
    private ?\Traversable $iterator = null;

    final public function __construct(ORM\Paginator $paginator)
    {
        $this->paginator = $paginator;
        $this->query = $paginator->getQuery();
        $this->firstResult = (int) $this->query->getFirstResult();
        $this->maxResults = (int) $this->query->getMaxResults();

        if ($this->maxResults <= 0) {
            throw new \InvalidArgumentException('maxResult must be greater than 0.');
        }
    }

    /**
     * @return Paginator<T>
     */
    public static function createFromQueryBuilder(
        QueryBuilder $qb,
        int $page,
        int $perPage,
        bool $useOutputWalkers = true
    ): self {
        return new static((new ORM\Paginator($qb
            ->setFirstResult(max($page - 1, 0) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery(), false)
        )->setUseOutputWalkers($useOutputWalkers));
    }

    public function getItems(): array
    {
        return iterator_to_array($this->getIterator());
    }

    public function getCurrentPage(): int
    {
        return (int) floor($this->firstResult / $this->maxResults) + 1;
    }

    public function getLastPage(): int
    {
        return (int) max(ceil($this->getTotalCount() / $this->maxResults), 1);
    }

    public function getItemsPerPage(): int
    {
        return $this->maxResults;
    }

    public function getTotalCount(): int
    {
        return $this->totalItems ??= \count($this->paginator);
    }

    public function getIterator(): \Iterator
    {
        if (null === $this->iterator) {
            $this->iterator = $this->paginator->getIterator();
        }

        \assert($this->iterator instanceof \Iterator);

        return $this->iterator;
    }

    public function count(): int
    {
        $iterator = $this->getIterator();

        \assert($iterator instanceof \Countable);

        return \count($iterator);
    }

    public function hasNextPage(): bool
    {
        return $this->getCurrentPage() < $this->getLastPage();
    }

    public function isPageOutOfBounds(): bool
    {
        return $this->getCurrentPage() !== 1 && 0 === $this->count();
    }
}

```

## Query & Handler

### Query

```php

<?php

declare(strict_types=1);

namespace App\Application\Admin\Organization\Query;

use App\Application\Common\Pagination\Pagination;

class ListQuery
{
    public Pagination $pagination;

    /** @var array<string, mixed> */
    public array $filters = [];

    /** @var array<string, mixed> */
    public array $sorters = [];

    public function __construct(Pagination $pagination, array $filters, array $sorters)
    {
        $this->pagination = $pagination;
        $this->filters = $filters;
        $this->sorters = $sorters;
    }
}

```

### Handler

```php
<?php

declare(strict_types=1);

namespace App\Application\Admin\Organization\Handler;

use App\Application\Admin\Organization\Query\ListQuery;
use App\Domain\Common\Pagination\PaginatedData;
use App\Domain\Repository\OrganizationRepositoryInterface;

class ListQueryHandler
{
    private OrganizationRepositoryInterface $repository;

    public function __construct(OrganizationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ListQuery $query): PaginatedData
    {
        $paginator = $this->repository->listPaginated(
            $query->pagination->getPage(),
            $query->pagination->getPerPage(),
            $query->filters,
            $query->sorters
        );

        return PaginatedData::createFromPaginator($paginator);
    }
}
```

## Les vues

### Le DTO de vue `PaginatedData`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Common\Pagination;

/**
 * @phpstan-template T
 * @template-extends \ArrayObject<int, T>
 */
class PaginatedData extends \ArrayObject
{
    private int $currentPage;
    private int $lastPage;
    private int $itemsPerPage;
    private int $numberOfItems;
    private int $totalNumberOfItems;

    /**
     * @param array<int, T> $items
     */
    public function __construct(
        array $items,
        int $currentPage,
        int $lastPage,
        int $itemsPerPage,
        int $totalNumberOfItems
    ) {
        parent::__construct($items);

        $this->currentPage = $currentPage;
        $this->lastPage = $lastPage;
        $this->itemsPerPage = $itemsPerPage;
        $this->totalNumberOfItems = $totalNumberOfItems;
        $this->numberOfItems = \count($items);
    }

    /**
     * @param PaginatorInterface<T> $paginator
     *
     * @return PaginatedData<T>
     */
    public static function createFromPaginator(PaginatorInterface $paginator, callable $callback = null): self
    {
        return new self(
            $callback !== null ? array_map($callback, $paginator->getItems()) : $paginator->getItems(),
            $paginator->getCurrentPage(),
            $paginator->getLastPage(),
            $paginator->getItemsPerPage(),
            $paginator->getTotalCount()
        );
    }

    /**
     * @return array<int, T>
     */
    public function getItems(): array
    {
        return iterator_to_array($this->getIterator());
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function getNumberOfItems(): int
    {
        return $this->numberOfItems;
    }

    public function getTotalNumberOfItems(): int
    {
        return $this->totalNumberOfItems;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getFirstPage(): int
    {
        return 1;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function getPreviousPage(): ?int
    {
        return $this->isFirstPage() ? null : $this->currentPage - 1;
    }

    public function hasPreviousPage(): bool
    {
        return !$this->isFirstPage();
    }

    public function getNextPage(): ?int
    {
        return $this->isLastPage() ? null : $this->currentPage + 1;
    }

    public function hasNextPage(): bool
    {
        return !$this->isLastPage();
    }

    public function isFirstPage(): bool
    {
        return $this->currentPage === $this->getFirstPage();
    }

    public function isLastPage(): bool
    {
        return $this->currentPage === $this->getLastPage();
    }

    public function hasToPaginate(): bool
    {
        return $this->getLastPage() > $this->getFirstPage();
    }
}

```

### Les vues Twig

```twig
{% block body}

<h1>Organisations ({{ paginatedData.totalNumberOfItems }})</h1>

{% if paginatedData.totalNumberOfItems > 0 %}
	<ul>
	{% for organization in paginatedData %}
		<li>{{ organization.name }}</li>
	{%endfor}
	</ul>
{% else %}

	Aucun résultat
{% endif }

{% endblock %}

{% if paginatedData.hasToPaginate %}
    {{ include('common/pagination.html.twig', { page: paginatedData }, false) }}
{% endif %}
```

```twig

{# common/pagination.html.twig #}

{% block pagination %}
<ul class="pagination">
    <li class="pagination__item pagination__item--previous {{ page.hasPreviousPage ? '' : 'pagination__item--disabled' }}">
        <a href="{{ url_add({ page: page.previousPage }) }}">
            <i class="icon icon--chevron-left" aria-hidden="true"></i>
            <span>Previous</span>
        </a>
    </li>
    {% set pages = range(max(page.firstPage, page.currentPage - 3), min(page.lastPage, page.currentPage + 3)) %}

    {% if pages|first > page.firstPage %}
        {% block elipsis %}
            <li class="pagination__ellipsis">
                <i class="icon icon--bullets" aria-hiddent="true"></i>
                <span class="sr-only">...</span>
            </li>
        {% endblock %}
    {% endif %}

    {% for value in pages %}
        <li class="pagination__item {{ page.currentPage == value ? 'pagination__item--active' : '' }}">
            <a href="{{ url_add({ page: value }) }}">{{ value }}</a>
        </li>
    {% endfor %}

    {% if pages|last < page.lastPage %}
        {{ block('elipsis') }}
    {% endif %}

    <li class="pagination__item pagination__item--next {{ page.hasNextPage ? '' : 'pagination__item--disabled' }}">
        <a href="{{ url_add({ page: page.nextPage }) }}">
            <span>Next</span>
            <i class="icon icon--chevron-right" aria-hidden="true"></i>
        </a>
    </li>
</ul>
{% endblock %}
```