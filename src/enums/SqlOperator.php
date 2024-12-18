<?php

declare(strict_types=1);


namespace Omegaalfa\QueryBuilder\enums;

enum SqlOperator: string
{
	case EQUALS = '=';
	case NOT_EQUALS = '!=';
	case GREATER_THAN = '>';
	case LESS_THAN = '<';
	case GREATER_THAN_OR_EQUALS = '>=';
	case LESS_THAN_OR_EQUALS = '<=';
	case LIKE = 'LIKE';
	case IN = 'IN';
}
