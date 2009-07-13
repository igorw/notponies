<?php

class vote extends np_record
{
	private $idea;

	private $user_id;

	private $count;

	private $value;

	private $cost;

	private $ctime;

	private $mtime;

	const UP = 1;
	const DOWN = -1;
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

		np_registry::get_instance()->register($this);
	}

	public function __get($var)
	{
		switch ($var)
		{
			case 'idea':
				return idea::get($this->idea_id);

			case 'score':
				return $this->value * $this->count;

			default:
				return (isset($this->$var)) ? $this->$var : null;
		}
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
			'mtime'			=> (int) time(),
		);
		$sql_ary['cost'] = self::calculate_cost($sql_ary['count'], $sql_ary['value'], idea::get($this->idea_id)->vote_cost);

		$sql = 'UPDATE ' . self::TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE id = ' . $this->id;
		$db->sql_query($sql);

		$this->count	= $sql_ary['count'];
		$this->value	= $sql_ary['value'];
		$this->cost		= $sql_ary['cost'];
		$this->mtime	= $sql_ary['mtime'];

		idea::get($this->idea_id)->recalculate_score();

		// @todo Adjust points
		// voter::get($this->voter_id)->points 

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

		idea::get($this->idea_id)->recalculate_score();

		return true;
	}

	public static function add(idea $idea, $count, $negate, voter $voter)
	{
		global $db;

		if ($idea->user->id === $voter->id)
		{
			trigger_error('You are unable to vote for your own ideas.');
		}

		if ($idea->voted($voter))
		{
			trigger_error('You have already voted.');
		}

		// Fail safe condition
		if (!$idea->can_vote($voter))
		{
			trigger_error('You cannot vote.');
		}

		$time = time();

		$sql_ary = array(
			'idea_id'		=> (int) $idea->get_id(),
			'user_id'		=> (int) $voter->get_id(),
			'count'			=> (int) $count,
			'value'			=> (int) ($negate ? vote::NO : vote::YES),
			'ctime'			=> (int) $time,
			'mtime'			=> (int) $time,
		);
		$sql_ary['cost'] = self::calculate_cost($sql_ary['count'], $sql_ary['value'], $idea->vote_cost);

		$sql = 'INSERT INTO ' . self::TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);

		$db->sql_query($sql);

		return self::get($db->sql_nextid());
	}

	public static function calculate_cost($count, $value, $vote_cost)
	{
		return $count * $value * $vote_cost;
	}

	public static function get($id)
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . self::TABLE . '
			WHERE id = ' . (int) $id;

		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return ($row) ? new self($row) : null;
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
			if (($object = np_registry::get_instance()->get(__CLASS__, $row['id'])) === null)
			{
				$votes[(int) $row['user_id']] = new self($row);
			}
			else
			{
				$votes[(int) $row['user_id']] = $object;
			}			
		}
		$db->sql_freeresult($result);

		return $votes;
	}
}
