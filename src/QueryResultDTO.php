<?php

declare(strict_types=1);


namespace Omegaalfa\queryBuilder;

use Generator;

class QueryResultDTO
{
	/**
	 * @param  iterable            $data
	 * @param  int                 $count
	 * @param  PaginationDTO|null  $pagination
	 */
	public function __construct(
		public readonly iterable $data,
		public readonly int $count,
		public readonly ?PaginationDTO $pagination = null
	) {}

	/**
	 * @return Generator
	 */
	public function iterator(): Generator
	{
		yield from $this->data;
	}
}
