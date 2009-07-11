<?php

class idea extends np_record
{
	private $user;

	private $topic_id;

	private $title;

	private $description;

	private $description_uid;
	private $description_bitfield;
	private $description_options;

	private $cost;

	private $vote_cost = vote::DEFAULT_COST;

	private $votes;

	private $ctime;

	private $mtime;

	protected static $sql_columns = array(
		'id'					=> 'id',
		'user'					=> 'user_id',
		'topic_id'				=> 'topic_id',
		'title'					=> 'title',
		'description'			=> 'description',
		'description_uid'		=> 'description_uid',
		'description_bitfield'	=> 'description_bitfield',
		'description_options'	=> 'description_options',
		'cost'					=> 'cost',
		'vote_cost'				=> 'vote_cost',
		'ctime'					=> 'ctime',
		'mtime'					=> 'mtime',
	);

	const DEFAULT_COST = 5;

	const TABLE = NP_IDEAS_TABLE;

	const POPULAR = 1;
	const NEWEST = 2;

	public function __construct(array $data)
	{
		np_registry::get_instance()->register($this);

		$this->id					= isset($data['id']) ? (int) $data['id'] : 0;
		$this->user					= voter::get($data['user_id']);
		$this->topic_id				= $data['topic_id'];
		$this->title				= $data['title'];
		$this->description			= $data['description'];
		$this->description_uid		= $data['description_uid'];
		$this->description_bitfield	= $data['description_bitfield'];
		$this->description_options	= (int) $data['description_options'];
		$this->cost					= (int) $data['cost'];
		$this->vote_cost			= (int) $data['vote_cost'];
		$this->votes				= vote::find_by_idea($this);
		$this->ctime				= isset($data['ctime']) ? (int) $data['ctime'] : time();
		$this->mtime				= isset($data['ctime']) ? (int) $data['mtime'] : time();
	}

	public function __get($var)
	{
		switch ($var)
		{
			case 'description':
				$message = $this->description;

				decode_message($message, $this->description_uid);

				return $message;
			break;

			case 'description_html':
				// @todo cache this
				return generate_text_for_display($this->description, $this->description_uid, $this->description_bitfield, $this->description_options);
			break;
		}

		return isset($this->$var) ? $this->$var : null;
	}

	public function __set($var, $value)
	{
		switch ($var)
		{
			case 'description':
				$this->set_description($value);
			break;

			case 'title':
				$this->_modified[$var] = $this->$var;
				$this->$var = $value;
			break;
		}
	}

	protected static function parse_description($value, $allow_bbcode = true, $allow_urls = true, $allow_smilies = true)
	{
		$uid = $bitfield = '';
		$options = 0;

		generate_text_for_storage($value, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

		return array(
			'description'			=> $value,
			'description_uid'		=> $uid,
			'description_bitfield'	=> $bitfield,
			'description_options'	=> (int) $options,
		);
	}

	public function set_description($value, $allow_bbcode = null, $allow_urls = null, $allow_smilies = null)
	{
		if ($allow_bbcode === null || $allow_urls === null || $allow_smilies === null)
		{
			if ($this->description_bitfield)
			{
				$bitfield = new bitfield($this->description_bitfield);

				$allow_bbcode	= ($allow_bbcode === null) ? $bitfield->get(OPTION_FLAG_BBCODE) : $allow_bbcode;
				$allow_urls		= ($allow_urls === null) ? $bitfield->get(OPTION_FLAG_LINKS) : $allow_urls;
				$allow_smilies	= ($allow_smilies === null) ? $bitfield->get(OPTION_FLAG_SMILIES) : $allow_smilies;

				unset($bitfield);
			}
			else
			{
				$allow_bbcode	= ($allow_bbcode === null) ? true : $allow_bbcode;
				$allow_urls		= ($allow_urls === null) ? true : $allow_urls;
				$allow_smilies	= ($allow_smilies === null) ? true : $allow_smilies;
			}
		}

		// Merge the current array over the top to preserve the originally modified value
		$this->_modified = array_merge(array(
			'description'			=> $this->description,
			'description_uid'		=> $this->description_uid,
			'description_bitfield'	=> $this->description_bitfield,
			'description_options'	=> $this->description_options
		), $this->_modified);

		$result = self::parse_description($value, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->description			= $result['description'];
		$this->description_uid		= $result['description_uid'];
		$this->description_bitfield	= $result['description_bitfield'];
		$this->description_options	= $result['description_options'];
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
		$data = array_merge(array(
			'title'				=> (string) $title,
			'cost'				=> (int) self::DEFAULT_COST,
			'vote_cost'			=> (int) vote::DEFAULT_COST,
			'user_id'			=> (int) $voter->id,
			'topic_id'			=> 0, // @todo
		), self::parse_description($description));

		return new self($data);
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
