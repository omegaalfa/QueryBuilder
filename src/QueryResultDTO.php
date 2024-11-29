<?php

declare(strict_types = 1);


namespace src\queryBuilder;

class QueryResultDTO
{
	/**
	 * @param  array               $data
	 * @param  int                 $count
	 * @param  PaginationDTO|null  $pagination
	 */
	public function __construct(
		public readonly array $data,
		public readonly int $count,
		public readonly ?PaginationDTO $pagination = null
	)
	{
	}
}