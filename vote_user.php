<?php

class vote_user
{
	private $id;
	private $name;

	private $base_points;

	private $points;

	const DEFAULT_POINTS = 20;

	const TABLE = STABLES_USERS_TABLE;

	private function __construct($id, $name)
	{
		$this->id = (int) $id;
		$this->name = $name;

		$sql = 'SELECT *
			FROM ' . self::TABLE . '
			WHERE user_id = ' . $this->id;

		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			$row = array(
				'user_base_points'	=> self::DEFAULT_POINTS,
			);

			$sql = 'INSERT INTO ' . STABLES_USERS_TABLE . ' ' . $db->sql_build_ary('INSERT', $row);

			$db->sql_query($sql);
		}

		$this->base_points = (int) $row['user_base_points'];

		// Calculate on request
		$this->points = null;
	}

	public static function create_from_user(user $user)
	{
		return new self((int) $user->data['user_id'], $user->data['username']);
	}

	public static function get($id)
	{
		$sql = 'SELECT username
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $id;

		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			return null;
		}

		return new self((int) $id, $row['username']);
	}

	public function __get($var)
	{
		if (!isset($this->$var))
		{
			return null;
		}

		if ($var == 'points' && $var === null)
		{
			$this->points = $this->base_points;

			$sql = 'SELECT SUM(vote_cost) AS cost
				FROM ' . vote::TABLE . "
				WHERE user_id = {$this->id}
				GROUP BY user_id";

			$result = $db->sql_query($sql);
			$this->points -= (int) $db->sql_fetchfield($result, 'cost');
			$db->sql_freeresult($result);

			$sql = 'SELECT SUM(idea_cost) AS cost
				FROM ' . idea::TABLE . "
				WHERE user_id = {$this->id}
				GROUP BY user_id";

			$result = $db->sql_query($sql);
			$this->points -= (int) $db->sql_fetchfield($result, 'cost');
			$db->sql_freeresult($result);
		}

		return $this->$var;
	}
}
