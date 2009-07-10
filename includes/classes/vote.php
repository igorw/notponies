<?php

class vote
{
	private $id;

	private $idea_id;

	private $user_id;

	private $count;

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
		$this->id		= (int) $data['id'];
		$this->idea_id	= (int) $data['idea_id'];
		$this->user_id	= (int) $data['user_id'];
		$this->count	= (int) $data['count'];
		$this->value	= (int) $data['value'];
		$this->cost		= (int) $data['cost'];
		$this->ctime	= (int) $data['ctime'];
		$this->mtime	= (int) $data['mtime'];
	}

	public function __get($var)
	{
		if (isset($this->$var))
		{
			return $this->$var;
		}
		else if ($var == 'idea')
		{
			return idea::get($this->idea_id);
		}

		return null;
	}

	public function changeable()
	{
		return true;
	}

	public function change($count, $negate)
	{
		if (!$this->changeable())
		{
			return false;
		}

		global $db;

		$this->value = ($negate) ? self::NO : self::YES;

		$sql_ary = array(
			'count'			=> (int) $count,
			'value'			=> (int) ($negate ? vote::NO : vote::YES),
			'mtime'			=> (int) $time,
		);
		$sql_ary['cost'] = self::calculate_cost($sql_ary['count'], $sql_ary['value'], $this->idea->vote_cost);

		$sql = 'UPDATE ' . self::TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE id = ' . $this->id;
		$db->sql_query($sql);

		$this->count	= $sql_ary['count'];
		$this->value	= $sql_ary['value'];
		$this->cost		= $sql_ary['cost'];
		$this->mtime	= $sql_ary['mtime'];

		return true;
	}

	public function removable()
	{
		return true;
	}

	public function remove()
	{
		if (!$this->remoable())
		{
			return false;
		}

		global $db;

		$sql = 'DELETE
			FROM ' . self::TABLE . '
			WHERE id = ' . $this->id;
		$db->sql_query($sql);

		$this->value	= self::DELETED;

		return true;
	}

	public static function add(idea $idea, $count, $negate, voter $voter)
	{
		global $db;

		if ($idea->user_id == $voter->id)
		{
			trigger_error('You are unable to vote for your own ideas.');
		}

		if ($idea->voted($voter->id))
		{
			trigger_error('You have already voted.');
		}

		$time = time();

		$sql_ary = array(
			'idea_id'		=> (int) $this->id,
			'user_id'		=> (int) $voter->id,
			'count'			=> (int) $count,
			'value'			=> (int) ($negate ? vote::NO : vote::YES),
			'ctime'			=> (int) $time,
			'mtime'			=> (int) $time,
		);
		$sql_ary['cost'] = self::calculate_cost($sql_ary['count'], $sql_ary['value'], $idea->vote_cost);

		$sql = 'INSERT INTO ' . self::TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);

		return $db->sql_query($sql);
	}

	public static function calculate_cost($count, $value, $vote_cost)
	{
		return $count * $value * $vote_cost;
	}

	public static function find_by_idea(idea $idea)
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . self::TABLE . '
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
