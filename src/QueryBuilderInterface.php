<?php

declare(strict_types = 1);

namespace src\queryBuilder;


interface QueryBuilderInterface
{
	/**
	 * @param  array  $fields
	 *
	 * @return $this
	 */
	public function select(array $fields = ['*']): self;

	/**
	 * @param  string       $table
	 * @param  string|null  $alias
	 *
	 * @return $this
	 */
	public function from(string $table, ?string $alias = null): self;

	/**
	 * @param  string              $column
	 * @param  ComparisonOperator  $operator
	 * @param  mixed               $value
	 *
	 * @return $this
	 */
	public function where(string $column, ComparisonOperator $operator, mixed $value): self;

	/**
	 * @param  string    $table
	 * @param  string    $first
	 * @param  string    $operator
	 * @param  string    $second
	 * @param  JoinType  $type
	 *
	 * @return $this
	 */
	public function join(string $table, string $first, string $operator, string $second, JoinType $type = JoinType::INNER): self;

	/**
	 * @param  string          $column
	 * @param  OrderDirection  $direction
	 *
	 * @return $this
	 */
	public function orderBy(string $column, OrderDirection $direction = OrderDirection::ASC): self;

	/**
	 * @param  int  $limit
	 * @param  int  $offset
	 *
	 * @return $this
	 */
	public function limit(int $limit, int $offset = 0): self;

	/**
	 * @return string
	 */
	public function getSQL(): string;

	/**
	 * @return QueryResultDTO
	 */
	public function execute(): QueryResultDTO;
}