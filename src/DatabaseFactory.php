<?php

namespace Omegaalfa\queryBuilder;

use Omegaalfa\queryBuilder\config\ConfigService;
use Omegaalfa\queryBuilder\connection\DatabaseConfig;
use Omegaalfa\queryBuilder\connection\PDOConnection;
use Omegaalfa\queryBuilder\interfaces\CacheInterface;


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
