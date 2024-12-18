<?php

declare(strict_types=1);

namespace Omegaalfa\QueryBuilder\interfaces;


use Omegaalfa\QueryBuilder\enums\JoinType;
use Omegaalfa\QueryBuilder\enums\OrderDirection;
use Omegaalfa\QueryBuilder\enums\SqlOperator;

interface QueryBuilderInterface
{
	/**
	 * @param  string  $alias
	 *
	 * @return $this
	 */
	public function alias(string $alias): self;

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

	/**
	 * @return string
	 */
	public function getQuerySql(): string;
}
