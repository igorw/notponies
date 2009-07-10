<?php

class idea
{
	private $id;

	private $title;

	private $description;

	private $cost;

	private $vote_cost = vote::DEFAULT_COST;

	private $votes;

	/**
	* @todo use private and getter
	*/
	public $user_id;

	const DEFAULT_COST = 5;

	const TABLE = NP_IDEAS_TABLE;

	public function __construct(array $data)
	{
		$this->id			= (int) $data['id'];
		$this->title		= $data['title'];
		$this->description	= $data['description'];
		$this->cost			= (int) $data['cost'];
		$this->vote_cost	= (int) $data['vote_cost'];
		$this->votes		= vote::find_by_idea($this);
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

		if ($row)
		{
			return new self($row);
		}
		else
		{
			return null;
		}
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
