<?php

declare(strict_types=1);

namespace Omegaalfa\QueryBuilder;

use Omegaalfa\QueryBuilder\enums\JoinType;
use Omegaalfa\QueryBuilder\enums\OrderDirection;
use Omegaalfa\QueryBuilder\enums\SqlOperator;
use Omegaalfa\QueryBuilder\exceptions\QueryException;
use Omegaalfa\QueryBuilder\interfaces\QueryBuilderInterface;

class QueryBuilderOperations implements QueryBuilderInterface
{

	/**
	 * @var array
	 */
	protected array $joins = [];

	/**
	 * @var array
	 */
	protected array $where = [];

	/**
	 * @var array
	 */
	protected array $orderBy = [];

	/**
	 * @var array
	 */
	protected array $groupBy = [];

	/**
	 * @var array
	 */
	protected array $having = [];

	/**
	 * @var array|null
	 */
	protected ?array $limit = null;

	/**
	 * @var array
	 */
	protected array $params = [];

	/**
	 * @var array
	 */
	protected array $sql = [];

	/**
	 * @var string|null
	 */
	protected string|null $table = null;


	/**
	 * @param  string  $alias
	 *
	 * @return $this
	 */
	public function alias(string $alias): self
	{
		$this->sql[] = "AS {$alias}";
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
		$this->resetOperationsState();
		$this->table = $table;
		$this->sql = ['SELECT', implode(', ', $fields), "FROM {$table}"];
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
		$this->resetOperationsState();
		$this->table = $table;
		$fields = array_keys($data);
		$this->sql = [
			'INSERT INTO',
			$table,
			'(' . implode(', ', $fields) . ')',
			'VALUES',
			'(' . implode(', ', array_map(static fn($field) => ':' . $field, $fields)) . ')'
		];

		foreach ($data as $key => $value) {
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
		$this->resetOperationsState();
		$this->table = $table;
		$fields = [];
		foreach ($data as $key => $value) {
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
		$this->resetOperationsState();
		$this->table = $table;
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
		if (empty($this->groupBy)) {
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
	 * @param  string  $query
	 * @param  array   $params
	 *
	 * @return $this
	 */
	public function raw(string $query, array $params = []): self
	{
		$this->resetOperationsState();
		$this->sql = [$query];
		$this->params = $params;

		return $this;
	}

	/**
	 * @return void
	 */
	protected function resetOperationsState(): void
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
	 * @return string
	 */
	public function getQuerySql(): string
	{
		$query = $this->sql;
		if ($this->joins) {
			$query[] = implode(' ', $this->joins);
		}

		if ($this->where) {
			$query[] = 'WHERE ' . implode(' AND ', $this->where);
		}

		if ($this->groupBy) {
			$query[] = 'GROUP BY ' . implode(', ', $this->groupBy);
		}

		if ($this->having) {
			$query[] = 'HAVING ' . implode(' AND ', $this->having);
		}

		if ($this->orderBy) {
			$query[] = 'ORDER BY ' . implode(', ', $this->orderBy);
		}

		if ($this->limit) {
			$query[] = "LIMIT {$this->limit[1]} , {$this->limit[0]}";
		}

		return implode(' ', $query);
	}
}
