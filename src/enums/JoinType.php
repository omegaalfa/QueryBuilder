<?php


declare(strict_types=1);


namespace Omegaalfa\queryBuilder\enums;

enum JoinType: string
{
	case INNER = 'INNER JOIN';
	case LEFT = 'LEFT JOIN';
	case RIGHT = 'RIGHT JOIN';
}
