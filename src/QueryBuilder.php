<?php

declare(strict_types=1);


namespace Omegaalfa\queryBuilder;

use PDO;
use PDOStatement;
use Omegaalfa\queryBuilder\connection\PDOConnection;
use Omegaalfa\queryBuilder\exceptions\QueryException;
use Omegaalfa\queryBuilder\interfaces\CacheInterface;
use Omegaalfa\queryBuilder\interfaces\PaginatorInterface;
use Omegaalfa\queryBuilder\traits\QueryBuilderCacheTrait;

final class QueryBuilder extends QueryBuilderOperations
{
	use QueryBuilderCacheTrait;

	/**
	 * @var PDO|null
	 */
	private ?PDO $transaction = null;


	/**
	 * @param  PDOConnection        $connection
	 * @param  PaginatorInterface   $paginator
	 * @param  CacheInterface|null  $cache
	 */
	public function __construct(
		private readonly PDOConnection $connection,
		private readonly PaginatorInterface $paginator,
		private readonly ?CacheInterface $cache = null
	) {}

	/**
	 * @return $this
	 */
	public function beginTransaction(): self
	{
		$this->transaction = $this->connection->connect();
		$this->transaction->beginTransaction();
		return $this;
	}

	/**
	 * @return void
	 */
	public function commit(): void
	{
		if ($this->transaction !== null) {
			$this->transaction->commit();
			$this->transaction = null;
		}
	}

	/**
	 * @return void
	 */
	public function rollback(): void
	{
		if ($this->transaction !== null) {
			$this->transaction->rollBack();
			$this->transaction = null;
		}
	}


	/**
	 * @return PDOStatement
	 * @throws QueryException
	 */
	private function prepareAndExecute(): PDOStatement
	{
		$stmt = $this->connection->connect()->prepare($this->getQuerySql());

		foreach ($this->params as $param => $value) {
			if (empty($value)) {
				throw new QueryException("Field {$param} cannot be empty.");
			}
			$stmt->bindValue($param, $value);
		}

		if ($this->limit) {
			$stmt->bindValue(':offset', $this->limit[1], PDO::PARAM_INT);
			$stmt->bindValue(':limit', $this->limit[0], PDO::PARAM_INT);
		}

		$stmt->execute();
		return $stmt;
	}

	/**
	 * @return int
	 * @throws QueryException
	 */
	private function getTotalCount(): int
	{
		$countQuery = clone $this;
		$countQuery->sql = ['SELECT', 'COUNT(*) as total', "FROM {$this->table}"];
		$countQuery->orderBy = [];
		$countQuery->limit = null;
		$stmt = $countQuery->prepareAndExecute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		return (int)($result['total'] ?? 0);
	}

	/**
	 * @return QueryResultDTO
	 * @throws QueryException
	 */
	public function execute(): QueryResultDTO
	{
		try {
			if ($cached = $this->getFromCache()) {
				$this->resetOperationsState();
				return $cached;
			}
			$stmt = $this->prepareAndExecute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$count = $stmt->rowCount();
			$pagination = null;
			if ($this->limit) {
				$total = $this->getTotalCount();
				$pagination = $this->paginator->paginate(
					total: $total,
					perPage: $this->limit[0],
					currentPage: (int)($this->limit[1] / $this->limit[0]) + 1
				);
			}
			$result = new QueryResultDTO($data, $count, $pagination);
			$this->saveToCache($result);
			$this->resetOperationsState();
			return $result;
		} catch (\PDOException $e) {
			throw new QueryException(
				message: "Query execution failed: {$e->getMessage()}",
				code: (int)$e->getCode(),
				previousException: $e
			);
		}
	}
}
