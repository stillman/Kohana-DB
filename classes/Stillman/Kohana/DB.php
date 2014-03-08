<?php

namespace Stillman\Kohana;

use Database;

class DB extends \Kohana_DB
{
	public static function insert_row($table, array $data)
	{
		return \DB::insert($table, array_keys($data))->values($data)->execute();
	}

	public static function find(array $criteria)
	{
		return static::makeSql(Database::SELECT, $criteria);
	}

	public static function delete_rows(array $criteria)
	{
		return static::makeSql(Database::DELETE, $criteria);
	}

	public static function count(array $criteria, $connection = NULL)
	{
		unset($criteria['LIMIT'], $criteria['OFFSET'], $criteria['ORDER_BY']);
		$criteria['SELECT'] = ['COUNT(*) cnt'];
		return (int) \DB::find($criteria)->execute($connection)->get('cnt');
	}

	public static function makeSql($statement, array $criteria)
	{
		$statements = [
			Database::SELECT => 'SELECT',
			Database::DELETE => 'DELETE',
		];

		$action = $statements[$statement];

		isset($criteria['OFFSET']) or $criteria['OFFSET'] = 0;
		isset($criteria['params']) or $criteria['params'] = [];

		if ( ! isset($criteria[$action]))
		{
			$criteria[$action] = ($statement === Database::SELECT) ? ['*'] : [];
		}

		$select = is_array($criteria[$action])
			? implode(",\n\t", $criteria[$action])
			: $criteria[$action];

		$from = is_array($criteria['FROM'])
			? implode(', ', $criteria['FROM'])
			: $criteria['FROM'];

		$join = isset($criteria['JOIN'])
			? is_array($criteria['JOIN']) ? "\n".implode("\n", $criteria['JOIN']) : "\n".$criteria['JOIN']
			: '';

		$group_by = isset($criteria['GROUP_BY'])
			? "\nGROUP BY \n\t".(
				is_array($criteria['GROUP_BY'])
					? implode(",\n\t", $criteria['GROUP_BY'])
					: $criteria['GROUP_BY']
				)
			: '';

		$having = isset($criteria['HAVING'])
			? "\nHAVING \n\t(".implode(") \n\tAND (", $criteria['HAVING']).')'
			: '';

		$where = ! empty($criteria['WHERE'])
			? is_array($criteria['WHERE']) ? "\nWHERE \n\t(".implode(") \n\tAND (", $criteria['WHERE']).')' : "\nWHERE ".$criteria['WHERE']
			: '';

		$order_by = isset($criteria['ORDER_BY'])
			? "\nORDER BY ".(
				is_array($criteria['ORDER_BY'])
					? implode(', ', $criteria['ORDER_BY'])
					: $criteria['ORDER_BY']
				)
			: '';

		$limit = isset($criteria['LIMIT'])
			? "\nLIMIT {$criteria['LIMIT']} OFFSET {$criteria['OFFSET']}"
			: '';

		$sql = "{$statements[$statement]} \n\t$select \nFROM $from $join $where $group_by $having $order_by $limit";

		return \DB::query($statement, $sql)->parameters($criteria['params']);
	}
}