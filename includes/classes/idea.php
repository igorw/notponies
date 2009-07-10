<?php

class idea extends np_record
{
	private $title;

	private $description;

	private $cost;

	private $vote_cost = vote::DEFAULT_COST;

	private $votes;

	private $ctime;

	private $mtime;

	/**
	* @todo use private and getter
	*/
	private $user;

	const DEFAULT_COST = 5;

	const TABLE = NP_IDEAS_TABLE;

	const POPULAR = 1;
	const NEWEST = 2;

	public function __construct(array $data)
	{
		np_registry::get_instance()->register($this);

		$this->id			= (int) $data['id'];
		$this->title		= $data['title'];
		$this->description	= $data['description'];
		$this->cost			= (int) $data['cost'];
		$this->vote_cost	= (int) $data['vote_cost'];
		$this->votes		= vote::find_by_idea($this);
		$this->ctime		= (int) $data['ctime'];
		$this->mtime		= (int) $data['mtime'];
		$this->user			= voter::get($data['user_id']);
	}

	public function __get($var)
	{
		return isset($this->$var) ? $this->$var : null;
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

	public static function find($criteria, $limit)
	{
		global $db;

		$sql_where = '';

		switch ($criteria)
		{
			case self::POPULAR:

			break;

			case self::NEWEST:
			default:
				
			break;
		}

		$sql_order_by = 'ORDER BY ctime DESC';

		$sql = 'SELECT *
			FROM ' . self::TABLE . "
			$sql_where
			$sql_order_by";

		$result = ($limit > 0) ? $db->sql_query_limit($sql, $limit) : $db->sql_query($sql);

		$results = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$results[] = new self($row);
		}
		$db->sql_freeresult($result);

		return $results;
	}

	public static function create($title, $description, voter $voter)
	{
		global $db;

		$sql_ary = array(
			'title'				=> (string) $title,
			'description'		=> (string) $description,
			'cost'				=> (int) self::DEFAULT_COST,
			'vote_cost'			=> (int) vote::DEFAULT_COST,
			'user_id'			=> (int) $voter->id,
		);
		$sql = 'INSERT INTO ' . self::TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);

		$db->sql_query($sql);

		// Prepend the id
		$sql_ary = array_merge(array(
			'id'	=> $db->sql_nextid(),
		), $sql_ary);

		return new self($sql_ary);
	}

	public function voted(voter $voter)
	{
		return isset($this->votes[$voter->id]) && $this->votes[$voter->id]->value != vote::DELETED;
	}

	public function get_vote(voter $voter)
	{
		return $this->voted() ? $this->votes[$voter->id] : null;
	}

	public function vote(voter $voter, $count, $negate = false)
	{
		return vote::add($this, $count, $negate, $voter);
	}
}
