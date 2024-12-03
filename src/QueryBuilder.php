<?php

declare(strict_types = 1);


namespace src\queryBuilder\src;

use PDO;
use PDOStatement;
use src\queryBuilder\src\connection\PDOConnection;
use src\queryBuilder\src\enums\JoinType;
use src\queryBuilder\src\enums\OrderDirection;
use src\queryBuilder\src\enums\SqlOperator;
use src\queryBuilder\src\exceptions\QueryException;
use src\queryBuilder\src\interfaces\CacheInterface;
use src\queryBuilder\src\interfaces\PaginatorInterface;
use src\queryBuilder\src\interfaces\QueryBuilderInterface;


final class QueryBuilder implements QueryBuilderInterface
{

	/**
	 * @var array
	 */
	private array $joins = [];

	/**
	 * @var array
	 */
	private array $where = [];

	/**
	 * @var array
	 */
	private array $orderBy = [];

	/**
	 * @var array
	 */
	private array $groupBy = [];

	/**
	 * @var array
	 */
	private array $having = [];

	/**
	 * @var array|null
	 */
	private ?array $limit = null;

	/**
	 * @var array
	 */
	private array $params = [];

	/**
	 * @var array
	 */
	private array $sql = [];

	/**
	 * @var string|null
	 */
	protected string|null $table = null;

	/**
	 * @var PDO|null
	 */
	private ?PDO $transaction = null;

	/**
	 * @var int
	 */
	private int $cacheTtl;

	/**
	 * @var string
	 */
	private string $cacheKey;

	/**
	 * @param  PDOConnection        $connection
	 * @param  PaginatorInterface   $paginator
	 * @param  CacheInterface|null  $cache
	 */
	public function __construct(
		private readonly PDOConnection $connection,
		private readonly PaginatorInterface $paginator,
		private readonly ?CacheInterface $cache = null
	) {
	}


	/**
	 * @return $this
	 */
	public function beginTransaction(): self
	{
		$this->transaction = $this->connection->connect();
		$this->transaction->beginTransaction();
		return $this;
	}


	/**
	 * @return void
	 */
	public function commit(): void
	{
		if($this->transaction !== null) {
			$this->transaction->commit();
			$this->transaction = null;
		}
	}


	/**
	 * @return void
	 */
	public function rollback(): void
	{
		if($this->transaction !== null) {
			$this->transaction->rollBack();
			$this->transaction = null;
		}
	}

	/**
	 * @return void
	 */
	private function resetState(): void
	{
		$this->joins = [];
		$this->where = [];
		$this->orderBy = [];
		$this->groupBy = [];
		$this->limit = null;
		$this->params = [];
		$this->sql = [];
	}


	/**
	 * @param  string  $alias
	 *
	 * @return $this
	 */
	public function alias(string $alias): self
	{
		$this->sql[] = 'AS';
		$this->sql[] = $alias;
		return $this;
	}


	/**
	 * @param  string  $table
	 * @param  array   $fields
	 *
	 * @return $this
	 */
	public function select(string $table, array $fields = ['*']): self
	{
		$this->resetState();
		$this->table = $table;
		$this->sql = ['SELECT', implode(', ', $fields)];
		$this->sql[] = 'FROM';
		$this->sql[] = $table;
		return $this;
	}


	/**
	 * @param  string  $table
	 * @param  array   $data
	 *
	 * @return $this
	 */
	public function insert(string $table, array $data): self
	{
		$this->resetState();
		$this->table = $table;
		$fields = array_keys($data);
		$this->sql = [
			'INSERT INTO',
			$table,
			'(' . implode(', ', $fields) . ')',
			'VALUES',
			'(' . implode(', ', array_map(static fn($field) => ':' . $field, $fields)) . ')'
		];

		foreach($data as $key => $value) {
			$param = ':' . $key;
			$this->params[$param] = $value;
		}

		return $this;
	}


	/**
	 * @param  string  $table
	 * @param  array   $data
	 *
	 * @return $this
	 */
	public function update(string $table, array $data): self
	{
		$this->resetState();
		$this->table = $table;
		$fields = [];
		foreach($data as $key => $value) {
			$param = ':' . $key;
			$fields[] = sprintf('%s = %s', $key, $param);
			$this->params[$param] = $value;
		}

		$this->sql = [
			'UPDATE',
			$table,
			'SET',
			implode(', ', $fields)
		];

		return $this;
	}


	/**
	 * @param  string  $table
	 *
	 * @return $this
	 */
	public function delete(string $table): self
	{
		$this->table = $table;
		$this->resetState();
		$this->sql = [
			'DELETE FROM',
			$table
		];

		return $this;
	}


	/**
	 * @param  string       $column
	 * @param  SqlOperator  $operator
	 * @param  mixed        $value
	 *
	 * @return $this
	 */
	public function where(string $column, SqlOperator $operator, mixed $value): self
	{
		$param = ':param' . count($this->params);
		$this->where[] = sprintf('%s %s %s', $column, $operator->value, $param);
		$this->params[$param] = $value;
		return $this;
	}


	/**
	 * @param  string    $table
	 * @param  string    $key
	 * @param  string    $operator
	 * @param  string    $refer
	 * @param  JoinType  $type
	 *
	 * @return $this
	 */
	public function join(string $table, string $key, string $operator, string $refer, JoinType $type = JoinType::INNER): self
	{
		$this->joins[] = sprintf(
			'%s %s ON %s %s %s',
			$type->value,
			$table,
			$key,
			$operator,
			$refer
		);
		return $this;
	}

	/**
	 * @param  string          $column
	 * @param  OrderDirection  $direction
	 *
	 * @return $this
	 */
	public function orderBy(string $column, OrderDirection $direction = OrderDirection::ASC): self
	{
		$this->orderBy[] = sprintf('%s %s', $column, $direction->value);
		return $this;
	}


	/**
	 * @param  string  $column
	 *
	 * @return $this
	 */
	public function groupBy(string $column): self
	{
		$this->groupBy[] = $column;
		return $this;
	}

	/**
	 * @param  string       $column
	 * @param  SqlOperator  $operator
	 * @param  mixed        $value
	 *
	 * @return $this
	 * @throws QueryException
	 */
	public function having(string $column, SqlOperator $operator, mixed $value): self
	{
		if(empty($this->groupBy)) {
			throw new QueryException('HAVING clause requires GROUP BY');
		}

		$param = ':having' . count($this->params);
		$this->having[] = sprintf('%s %s %s', $column, $operator->value, $param);
		$this->params[$param] = $value;
		return $this;
	}

	/**
	 * @param  int  $limit
	 * @param  int  $offset
	 *
	 * @return $this
	 */
	public function limit(int $limit, int $offset = 0): self
	{
		$this->limit = [$limit, $offset];
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSQL(): string
	{
		if($this->joins) {
			$this->sql[] = implode(' ', $this->joins);
		}

		if($this->where) {
			$this->sql[] = 'WHERE';
			$this->sql[] = implode(' AND ', $this->where);
		}

		if($this->orderBy) {
			$this->sql[] = 'ORDER BY';
			$this->sql[] = implode(', ', $this->orderBy);
		}

		if($this->groupBy) {
			$this->sql[] = 'GROUP BY';
			$this->sql[] = implode(', ', $this->groupBy);
		}

		if($this->having) {
			$this->sql[] = 'HAVING';
			$this->sql[] = implode(' AND ', $this->having);
		}

		if($this->limit) {
			$this->sql[] = 'LIMIT';
			$this->sql[] = ':offset, :limit';
		}

		return implode(' ', $this->sql);
	}

	/**
	 * @param  string  $query
	 * @param  array   $params
	 *
	 * @return $this
	 */
	public function raw(string $query, array $params = []): self
	{
		$this->resetState();
		$this->sql = [$query];
		$this->params = $params;

		return $this;
	}

	/**
	 * @return string
	 */
	private function generateCacheKey(): string
	{
		$sql = implode(' ', $this->sql);
		return md5($sql . serialize($this->params));
	}


	/**
	 * @param  int  $ttl
	 *
	 * @return $this
	 */
	public function cache(int $ttl = 3600): self
	{
		$this->cacheTtl = $ttl;
		return $this;
	}

	/**
	 * @return QueryResultDTO|null
	 */
	private function getFromCache(): ?QueryResultDTO
	{
		if(!isset($this->cacheTtl)) {
			return null;
		}

		$this->cacheKey = $this->generateCacheKey();
		if($this->cache->has($this->cacheKey)) {
			$cachedResult = $this->cache->get($this->cacheKey);
			return new QueryResultDTO(
				data: $cachedResult['data'],
				count: $cachedResult['count'],
				pagination: $cachedResult['pagination']
			);
		}

		return null;
	}

	/**
	 * @param  QueryResultDTO  $result
	 *
	 * @return void
	 */
	private function saveToCache(QueryResultDTO $result): void
	{
		if(!isset($this->cacheTtl) || !$this->cacheKey) {
			return;
		}

		$this->cache->set($this->cacheKey, $result, $this->cacheTtl);
	}

	/**
	 * @return QueryResultDTO
	 * @throws QueryException
	 */
	public function execute(): QueryResultDTO
	{
		try {
			if($cached = $this->getFromCache()) {
				$this->resetState();
				return $cached;
			}
			$stmt = $this->prepareAndExecute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$count = $stmt->rowCount();
			$pagination = null;
			if($this->limit) {
				$total = $this->getTotalCount();
				$pagination = $this->paginator->paginate(
					total: $total,
					perPage: $this->limit[0],
					currentPage: (int)($this->limit[1] / $this->limit[0]) + 1
				);
			}
			$result = new QueryResultDTO($data, $count, $pagination);
			$this->saveToCache($result);
			$this->resetState();
			return $result;
		} catch(\PDOException $e) {
			throw new QueryException(
				message: "Query execution failed: {$e->getMessage()}",
				code: (int)$e->getCode(),
				previousException: $e
			);
		}
	}

	/**
	 * @return PDOStatement
	 */
	private function prepareAndExecute(): PDOStatement
	{
		$stmt = $this->connection->connect()->prepare($this->getSQL());

		foreach($this->params as $param => $value) {
			$stmt->bindValue($param, $value);
		}

		if($this->limit) {
			$stmt->bindValue(':offset', $this->limit[1], PDO::PARAM_INT);
			$stmt->bindValue(':limit', $this->limit[0], PDO::PARAM_INT);
		}

		$stmt->execute();
		return $stmt;
	}


	/**
	 * @return int
	 */
	private function getTotalCount(): int
	{
		$countQuery = clone $this;
		$countQuery->resetState();
		$countQuery->sql = ['SELECT', 'COUNT(*) as total'];
		$countQuery->sql[] = 'FROM';
		$countQuery->sql[] = $this->table;
		$countQuery->orderBy = [];
		$countQuery->limit = null;


		$stmt = $countQuery->prepareAndExecute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		return (int)($result['total'] ?? 0);
	}
}