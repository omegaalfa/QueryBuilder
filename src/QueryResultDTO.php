<<<<<<< 5313169754216fa10edcce8877f7046e38695f59
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
=======
<?php

declare(strict_types = 1);


namespace src\queryBuilder\src;

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
	)
	{
	}

	/**
	 * @return Generator
	 */
	public function iterator(): Generator
	{
		yield from $this->data;
	}
>>>>>>> Descrição das alterações
}