<?php

declare(strict_types=1);

namespace src\queryBuilder\src;

use src\queryBuilder\src\config\ConfigService;
use src\queryBuilder\src\connection\DatabaseConfig;
use src\queryBuilder\src\connection\PDOConnection;
use src\queryBuilder\src\interfaces\CacheInterface;


class DatabaseFactory
{

	/**
	 * @param  CacheInterface|null  $cache
	 *
	 * @return QueryBuilder
	 */
	public static function create(?CacheInterface $cache = null): QueryBuilder
	{
		$config = ConfigService::databaseConfig();
		return new QueryBuilder(
			new PDOConnection(
				new DatabaseConfig(
					driver: $config['driver'],
					host: $config['host'],
					database: $config['database'],
					port: $config['port'],
					username: $config['username'],
					password: $config['password'],
					charset: $config['charset'],
					collation: $config['collation'],
				)
			),
			new Paginator(),
			$cache
		);
	}
}
