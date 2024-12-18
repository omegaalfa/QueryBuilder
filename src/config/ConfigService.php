<?php

namespace Omegaalfa\QueryBuilder\config;

use function Core\env;

class ConfigService
{
	/**
	 * @return array
	 */
	public static function databaseConfig(): array
	{
		return [
			'driver'    => env('DB_DRIVER'),
			'host'      => env('DB_HOST'),
			'database'  => env('DB_DATABASE'),
			'port'      => env('DB_PORT'),
			'username'  => env('DB_USERNAME'),
			'password'  => env('DB_PASSWORD'),
			'charset'   => env('DB_CHARSET'),
			'collation' => env('DB_COLLATION')
		];
	}
}
