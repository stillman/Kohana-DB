<?php

namespace Stillman\Kohana;

class DB extends \Kohana_DB
{
	public static function insert_row($table, array $data)
	{
		return \DB::insert($table, array_keys($data))->values($data)->execute();
	}

	public static function find(array $criteria)
	{
		isset($criteria['SELECT']) or $criteria['SELECT'] = ['*'];
		isset($criteria['OFFSET']) or $criteria['OFFSET'] = 0;
		isset($criteria['params']) or $criteria['params'] = [];

		$select = is_array($criteria['SELECT'])
			? implode(",\n\t", $criteria['SELECT'])
			: $criteria['SELECT'];

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

		$sql = "SELECT \n\t$select \nFROM $from $join $where $group_by $having $order_by $limit";

		return \DB::query(\Database::SELECT, $sql)->parameters($criteria['params']);
	}

	public static function count(array $criteria, $connection = NULL)
	{
		unset($criteria['LIMIT'], $criteria['OFFSET'], $criteria['ORDER_BY']);
		$criteria['SELECT'] = ['COUNT(*) cnt'];
		return (int) \DB::find($criteria)->execute($connection)->get('cnt');
	}
}