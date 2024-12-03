<?php


declare(strict_types = 1);


namespace src\queryBuilder\src\enums;

enum JoinType: string
{
	case INNER = 'INNER JOIN';
	case LEFT = 'LEFT JOIN';
	case RIGHT = 'RIGHT JOIN';
}