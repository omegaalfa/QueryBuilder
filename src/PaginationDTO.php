<?php

declare(strict_types=1);


namespace Omegaalfa\queryBuilder;

final class PaginationDTO
{
	/**
	 * @param  int  $currentPage
	 * @param  int  $perPage
	 * @param  int  $totalPages
	 * @param  int  $totalItems
	 */
	public function __construct(
		public readonly int $currentPage,
		public readonly int $perPage,
		public readonly int $totalPages,
		public readonly int $totalItems
	) {}
}
