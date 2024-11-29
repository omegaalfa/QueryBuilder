<?php

declare(strict_types = 1);


namespace src\queryBuilder;

class DatabaseConfig
{
	/**
	 * @param  string  $driver
	 * @param  string  $host
	 * @param  string  $database
	 * @param  int     $port
	 * @param  string  $username
	 * @param  string  $password
	 * @param  array   $options
	 * @param  string  $charset
	 */
	public function __construct(
		public readonly string $driver,
		public readonly string $host,
		public readonly string $database,
		public readonly int $port,
		public readonly string $username,
		public readonly string $password,
		public readonly array $options = [],
		public readonly string $charset = 'utf8mb4'
	) {
	}
}