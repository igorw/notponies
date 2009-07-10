<?php

class voter extends np_record
{
	private $name;

	private $base_points;

	private $points;

	const DEFAULT_POINTS = 20;

	const TABLE = NP_VOTERS_TABLE;

	private function __construct(array $data)
	{
		np_registry::get_instance()->register($this);

		$this->id			= (int) $data['id'];
		$this->name			= $data['name'];
		$this->base_points	= (int) $data['base_points'];

		// Calculate on request
		$this->points = null;
	}

	public static function get_current()
	{
		global $user;

		return self::get((int) $user->data['user_id']);
	}

	public static function get($id)
	{
		$sql = 'SELECT v.*, u.user_id, u.username AS name
			FROM ' . USERS_TABLE . ' u
			LEFT JOIN ' . self::TABLE . ' v ON (v.id = u.user_id)
			WHERE u.user_id = ' . (int) $id . '
				AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row || empty($row['user_id']))
		{
			// Either of these mean the phpBB user does not exist

			return null;
		}

		if (empty($row['id']))
		{
			// Voter doesn't exist
			$sql_ary = array(
				'id'			=> (int) $row['user_id'],
				'base_points'	=> (int) self::DEFAULT_POINTS,
			);

			$sql = 'INSERT INTO ' . self::TABLE . ' ' . $db->sql_build_ary('INSERT', $sql_ary);

			$db->sql_query($sql);

			// Merge in
			$row = array_merge($row, $sql_ary);
		}

		return new self($row);
	}

	public function __get($var)
	{
		if (!isset($this->$var))
		{
			return null;
		}

		if ($var == 'points' && $this->$var === null)
		{
			$this->points = $this->base_points;

			$sql = 'SELECT SUM(vote_cost) AS cost
				FROM ' . vote::TABLE . "
				WHERE user_id = {$this->id}
				GROUP BY user_id";

			$result = $db->sql_query($sql);
			$this->points -= (int) $db->sql_fetchfield('cost', false, $result);
			$db->sql_freeresult($result);

			$sql = 'SELECT SUM(idea_cost) AS cost
				FROM ' . idea::TABLE . "
				WHERE user_id = {$this->id}
				GROUP BY user_id";

			$result = $db->sql_query($sql);
			$this->points -= (int) $db->sql_fetchfield('cost', false, $result);
			$db->sql_freeresult($result);
		}

		return $this->$var;
	}
}
