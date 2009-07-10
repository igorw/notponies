<?php

class voter extends np_record
{
	private $name;
	private $colour;
	private $rank;
	private $posts;
	private $avatar = array(
		'file'	=> '',
		'type'	=> 0,
		'width'	=> 0,
		'height'=> 0,
	);

	private $base_points;

	private $points;

	const DEFAULT_POINTS = 20;

	const TABLE = NP_VOTERS_TABLE;

	private function __construct(array $data)
	{
		np_registry::get_instance()->register($this);

		$this->id			= (int) $data['id'];
		$this->name			= $data['username'];
		$this->colour		= $data['user_colour'];
		$this->rank			= (int) $data['user_rank'];
		$this->posts		= (int) $data['user_posts'];
		$this->avatar		= array(
			'file'	=> $data['user_avatar'],
			'type'	=> (int) $data['user_avatar_type'],
			'width'	=> (int) $data['user_avatar_width'],
			'height'=> (int) $data['user_avatar_height'],
		);
		$this->base_points	= (int) $data['base_points'];

		// Calculate on request
		$this->points = null;
	}

	public function rank()
	{
		if (!function_exists('get_user_rank'))
		{
			global $phpbb_root_path, $phpEx;

			include $phpbb_root_path . 'includes/functions_display.' . $phpEx;
		}

		$return = array(
			'title'		=> '',
			'img'		=> '',
			'img_src'	=> '',
		);

		get_user_rank($this->rank, $this->posts, $return['title'], $return['img'], $return['img_src']);

		return $return;
	}

	public function avatar($alt = 'USER_AVATAR', $ignore_config = false)
	{
		if (!function_exists('get_user_avatar'))
		{
			global $phpbb_root_path, $phpEx;

			include $phpbb_root_path . 'includes/functions_display.' . $phpEx;
		}
		return get_user_avatar($this->avatar['file'], $this->avatar['type'], $this->avatar['width'], $this->avatar['height'], $alt, $ignore_config);
	}

	public static function get_current()
	{
		global $user;

		return self::get((int) $user->data['user_id']);
	}

	public static function get($id)
	{
		global $db;

		$sql = 'SELECT v.*, u.user_id, u.username, u.user_colour, u.user_rank, u.user_posts,
				u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
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

			$sql = 'INSERT INTO ' . self::TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);

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
