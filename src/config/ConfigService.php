<?php

namespace src\queryBuilder\src\config;

class ConfigService
{
	/**
	 * @return array
	 */
	public static function databaseConfig(): array
	{
		return [
			'driver'    => \Core\env('DB_DRIVER'),
			'host'      => \Core\env('DB_HOST'),
			'database'  => \Core\env('DB_DATABASE'),
			'port'      => \Core\env('DB_PORT'),
			'username'  => \Core\env('DB_USERNAME'),
			'password'  => \Core\env('DB_PASSWORD'),
			'charset'   => \Core\env('DB_CHARSET'),
			'collation' => \Core\env('DB_COLLATION')
		];
	}
}