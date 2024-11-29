<?php

declare(strict_types = 1);


namespace src\queryBuilder;

use PDO;
use PDOStatement;


final class QueryBuilder implements QueryBuilderInterface
{
	/**
	 * @var array
	 */
	private array $fields = [];
	/**
	 * @var string|null
	 */
	private ?string $table = null;
	/**
	 * @var string|null
	 */
	private ?string $alias = null;
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
	 * @var array|null
	 */
	private ?array $limit = null;
	/**
	 * @var array
	 */
	private array $params = [];

	/**
	 * @param  PDOConnection       $connection
	 * @param  PaginatorInterface  $paginator
	 */
	public function __construct(
		private readonly PDOConnection $connection,
		private readonly PaginatorInterface $paginator
	) {
	}

	/**
	 * @param  array  $fields
	 *
	 * @return $this
	 */
	public function select(array $fields = ['*']): self
	{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * @param  string       $table
	 * @param  string|null  $alias
	 *
	 * @return $this
	 */
	public function from(string $table, ?string $alias = null): self
	{
		$this->table = $table;
		$this->alias = $alias;
		return $this;
	}

	/**
	 * @param  string              $column
	 * @param  ComparisonOperator  $operator
	 * @param  mixed               $value
	 *
	 * @return $this
	 */
	public function where(string $column, ComparisonOperator $operator, mixed $value): self
	{
		$param = ':param' . count($this->params);
		$this->where[] = sprintf('%s %s %s', $column, $operator->value, $param);
		$this->params[$param] = $value;
		return $this;
	}

	/**
	 * @param  string    $table
	 * @param  string    $first
	 * @param  string    $operator
	 * @param  string    $second
	 * @param  JoinType  $type
	 *
	 * @return $this
	 */
	public function join(
		string $table,
		string $first,
		string $operator,
		string $second,
		JoinType $type = JoinType::INNER
	): self {
		$this->joins[] = sprintf(
			'%s %s ON %s %s %s',
			$type->value,
			$table,
			$first,
			$operator,
			$second
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
		$sql = ['SELECT', implode(', ', $this->fields)];

		if($this->table) {
			$sql[] = 'FROM';
			$sql[] = $this->table;
			if($this->alias) {
				$sql[] = 'AS';
				$sql[] = $this->alias;
			}
		}

		if($this->joins) {
			$sql[] = implode(' ', $this->joins);
		}

		if($this->where) {
			$sql[] = 'WHERE';
			$sql[] = implode(' AND ', $this->where);
		}

		if($this->orderBy) {
			$sql[] = 'ORDER BY';
			$sql[] = implode(', ', $this->orderBy);
		}

		if($this->limit) {
			$sql[] = 'LIMIT';
			$sql[] = '?, ?';
		}

		return implode(' ', $sql);
	}

	/**
	 * @throws QueryException
	 */
	public function execute(): QueryResultDTO
	{
		try {
			$stmt = $this->prepareAndExecute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$count = $stmt->rowCount();

			$pagination = null;
			if($this->limit) {
				$total = $this->getTotalCount();
				$pagination = $this->paginator->paginate(
					$total,
					$this->limit[0],
					(int)($this->limit[1] / $this->limit[0]) + 1
				);
			}

			return new QueryResultDTO($data, $count, $pagination);
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
			$stmt->bindValue(count($this->params) + 1, $this->limit[1], PDO::PARAM_INT);
			$stmt->bindValue(count($this->params) + 2, $this->limit[0], PDO::PARAM_INT);
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
		$countQuery->fields = ['COUNT(*) as total'];
		$countQuery->orderBy = [];
		$countQuery->limit = null;

		$stmt = $countQuery->prepareAndExecute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		return (int)($result['total'] ?? 0);
	}
}