<?php

declare(strict_types=1);


namespace Omegaalfa\queryBuilder\connection;

use PDO;
use Omegaalfa\queryBuilder\exceptions\DatabaseException;
use Omegaalfa\queryBuilder\interfaces\ConnectionInterface;


final class PDOConnection implements ConnectionInterface
{
	/**
	 * @var PDO|null
	 */
	private ?PDO $connection = null;

	/**
	 * @param  DatabaseConfig  $config
	 */
	public function __construct(protected DatabaseConfig $config) {}


	/**
	 * @return PDO
	 */
	public function connect(): PDO
	{
		if ($this->connection === null) {
			$dsn = sprintf(
				'%s:host=%s;dbname=%s;port=%d;charset=%s',
				$this->config->driver,
				$this->config->host,
				$this->config->database,
				$this->config->port,
				$this->config->charset,
			);

			$this->connection = new PDO(
				$dsn,
				$this->config->username,
				$this->config->password,
				$this->config->options
			);

			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			if (!empty($this->config->collation)) {
				$this->connection->exec("SET NAMES '{$this->config->charset}' COLLATE '{$this->config->collation}'");
			}
		}

		return $this->connection;
	}

	/**
	 * @return void
	 */
	public function disconnect(): void
	{
		$this->connection = null;
	}

	/**
	 * @throws DatabaseException
	 */
	public function transaction(callable $callback): mixed
	{
		try {
			$this->connection?->beginTransaction();
			$result = $callback($this->connection);
			$this->connection?->commit();
			return $result;
		} catch (\Throwable $e) {
			$this->connection?->rollBack();
			throw new DatabaseException(
				message: "Transaction failed: {$e->getMessage()}",
				code: (int)$e->getCode(),
				previousException: $e
			);
		}
	}
}
