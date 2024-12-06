<?php

declare(strict_types = 1);

namespace src\queryBuilder\src\interfaces;


use src\queryBuilder\src\enums\JoinType;
use src\queryBuilder\src\enums\OrderDirection;
use src\queryBuilder\src\enums\SqlOperator;
use src\queryBuilder\src\QueryResultDTO;

interface QueryBuilderInterface
{
	/**
	 * @param  string  $table
	 * @param  array   $fields
	 *
	 * @return $this
	 */
	public function select(string $table, array $fields = ['*']): self;

	/**
	 * @param  string  $table
	 * @param  array   $data
	 *
	 * @return $this
	 */
	public function insert(string $table, array $data): self;

	/**
	 * @param  string  $table
	 * @param  array   $data
	 *
	 * @return $this
	 */
	public function update(string $table, array $data): self;

	/**
	 * @param  string  $table
	 *
	 * @return $this
	 */
	public function delete(string $table): self;

	/**
	 * @param  string       $column
	 * @param  SqlOperator  $operator
	 * @param  mixed        $value
	 *
	 * @return $this
	 */
	public function where(string $column, SqlOperator $operator, mixed $value): self;

	/**
	 * @param  string    $table
	 * @param  string    $key
	 * @param  string    $operator
	 * @param  string    $refer
	 * @param  JoinType  $type
	 *
	 * @return $this
	 */
	public function join(string $table, string $key, string $operator, string $refer, JoinType $type = JoinType::INNER): self;

	/**
	 * @param  string          $column
	 * @param  OrderDirection  $direction
	 *
	 * @return $this
	 */
	public function orderBy(string $column, OrderDirection $direction = OrderDirection::ASC): self;

	/**
	 * @param  string       $column
	 * @param  SqlOperator  $operator
	 * @param  mixed        $value
	 *
	 * @return $this
	 */
	public function having(string $column, SqlOperator $operator, mixed $value): self;

	/**
	 * @param  int  $limit
	 * @param  int  $offset
	 *
	 * @return $this
	 */
	public function limit(int $limit, int $offset = 0): self;
}