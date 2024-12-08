<?php

declare(strict_types=1);


namespace Omegaalfa\queryBuilder\connection;

class DatabaseConfig
{
	/**
	 * @param  string       $driver
	 * @param  string       $host
	 * @param  string       $database
	 * @param  int          $port
	 * @param  string       $username
	 * @param  string       $password
	 * @param  array        $options
	 * @param  string       $charset
	 * @param  string       $collation
	 * @param  string|null  $prefix
	 */
	public function __construct(
		public readonly string $driver,
		public readonly string $host,
		public readonly string $database,
		public readonly int $port,
		public readonly string $username,
		public readonly string $password,
		public readonly array $options = [],
		public readonly string $charset = 'utf8',
		public readonly string $collation = 'utf8_unicode_ci',
		public readonly ?string $prefix = null,
	) {}
}
