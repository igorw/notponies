<?php

class vote
{
	private $id;

	private $idea_id;

	private $user_id;

	private $value;

	private $cost;

	private $ctime;

	private $mtime;

	const YES = 1;
	const NO = -1;
	const DELETED = 0;

	const DEFAULT_COST = 1;

	const TABLE = NP_VOTES_TABLE;

	public function __construct(array $data)
	{
		$this->id		= (int) $data['vote_id'];
		$this->idea_id	= (int) $data['idea_id'];
		$this->user_id	= (int) $data['user_id'];
		$this->value	= (int) $data['vote_value'];
		$this->cost		= (int) $data['vote_cost'];
		$this->ctime	= (int) $data['vote_ctime'];
		$this->mtime	= (int) $data['vote_mtime'];
	}

	public function changeable()
	{
		return true;
	}

	public function change($negate)
	{
		global $db;

		$this->value = ($negate) ? self::NO : self::YES;

		$sql = 'UPDATE ' . STABLES_VOTES_TABLE . '
			SET vote_mtime = ' . time() . "
				vote_value = {$this->value}
			WHERE vote_id = {$this->id}";
		$db->sql_query($sql);
	}

	public function removable()
	{
		return true;
	}

	public function remove()
	{
		global $db;

		$sql = 'DELETE
			FROM ' . STABLES_VOTES_TABLE . '
			WHERE vote_id = ' . $this->id;
		$db->sql_query($sql);

		$this->value = self::DELETED;
	}

	public static function add(idea $idea, $negate)
	{
		global $db, $user;

		if ($idea->user_id == $user->data['user_id'])
		{
			trigger_error('You are unable to vote for your own ideas.');
		}

		if ($idea->voted())
		{
			trigger_error('You have already voted.');
		}

		$time = time();

		$sql_ary = array(
			'idea_id'		=> (int) $this->id,
			'user_id'		=> (int) $user->data['user_id'],
			'vote_value'	=> (int) ($negate ? vote::NO : vote::YES),
			'vote_ctime'	=> (int) $time,
			'vote_mtime'	=> (int) $time,
		);

		$sql = 'INSERT INTO ' . STABLES_VOTES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);

		return $db->sql_query($sql);
	}

	public static function find_by_idea(idea $idea)
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . STABLES_VOTES_TABLE . '
			WHERE idea_id = ' . $idea->id;

		$result = $db->sql_query($sql);

		$votes = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$votes[(int) $row['user_id']] = new self($row);
		}
		$db->sql_freeresult($result);

		return $votes;
	}
}
