<?php

class idea
{
	private $id;

	private $title;

	private $description;

	private $vote_cost = vote::DEFAULT_COST;

	private $votes;

	/**
	* @todo use private and getter
	*/
	public $user_id;

	const TABLE = NP_IDEAS_TABLE;

	public function __construct(array $data)
	{
		$this->id			= (int) $data['id'];
		$this->title		= $data['title'];
		$this->description	= $data['description'];
		$this->vote_cost	= (int) $data['vote_cost'];
		$this->votes		= vote::find_by_idea($this);
	}

	public static function get($id)
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . self::TABLE . '
			WHERE idea_id = ' . (int) $id;

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

	public static function create($title, $description, $version)
	{
		global $db, $user;

		$sql_ary = array(
			'idea_title'		=> (string) $title,
			'idea_description'	=> (string) $description,
			'idea_version'		=> (string) $version,
			'idea_vote_cost'	=> vote::DEFAULT_COST,
			'user_id'			=> (int) $user->data['user_id'],
		);
		$sql = 'INSERT INTO ' . self::TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);

		$db->sql_query($sql);

//		return new self(array(
	}

	public function voted()
	{
		global $user;

		return isset($this->votes[$user->data['user_id']]) && $this->votes[$user->data['user_id']]->value != vote::DELETED;
	}

	public function get_vote()
	{
		global $user;

		return $this->voted() ? $this->votes[$user->data['user_id']] : null;
	}

	public function vote($negate = false)
	{
		return vote::add($this, $negate);
	}
}
