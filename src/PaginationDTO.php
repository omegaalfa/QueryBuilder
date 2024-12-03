<<<<<<< 5313169754216fa10edcce8877f7046e38695f59
<?php

declare(strict_types=1);


namespace src\queryBuilder;

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
=======
<?php

declare(strict_types=1);


namespace src\queryBuilder\src;

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
>>>>>>> Descrição das alterações
}