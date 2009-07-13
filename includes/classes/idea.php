<?php

class idea extends np_record
{
	protected $user;

	protected $topic_id;

	protected $title;

	protected $description;

	protected $description_uid;
	protected $description_bitfield;
	protected $description_options;

	protected $cost;

	protected $vote_cost = vote::DEFAULT_COST;

	protected $votes;

	protected $ctime;

	protected $mtime;

	// Auto calculated
	protected $score;

	public static $sql_columns = array(
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
		'score'					=> 'score',
	);

	const DEFAULT_COST = 5;

	const TABLE = NP_IDEAS_TABLE;

	const POPULAR = 1;
	const NEWEST = 2;
	const SCORE = 3;

	public function __construct(array $data)
	{
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
		$this->score				= (int) $data['score'];

		$this->recalculate_score();

		np_registry::get_instance()->register($this);
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

	public function get_description($display = true, $max_paragraphs = false, $max_length = false, $replacement = '...')
	{
		if ($max_paragraphs || $max_length)
		{
			// Trim at full sentence if possible then space or newline
			$description = trim_text($this->description, $this->description_uid, (int) $max_length, (int) $max_paragraphs, array(array('. ' => true), array(' ', "\n")), (string) $replacement, $this->description_bitfield, true);
		}
		else
		{
			$description = $this->description;
		}

		if ($display)
		{
			return generate_text_for_display($description, $this->description_uid, $this->description_bitfield, $this->description_options);
		}
		else
		{
			decode_message($description, $this->description_uid);

			return $description;
		}
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

	public function recalculate_score()
	{
		$score = 0;

		foreach ($this->votes as $vote)
		{
			if (!$vote instanceof vote)
			{
				// Sanity check
				trigger_error('Unknown error occurred.', E_USER_ERROR);
			}
			$score += $vote->score;
		}

		if ($score !== $this->score)
		{
			$this->_modified = array_merge(array('score' => $this->score), $this->_modified);
			$this->score = $score;
		}
	}

	protected static function parse_description($value, $allow_bbcode = true, $allow_magic_urls = true, $allow_smilies = true)
	{
		$uid = $bitfield = '';
		$options = 0;

		generate_text_for_storage($value, $uid, $bitfield, $options, $allow_bbcode, $allow_magic_urls, $allow_smilies);

		return array(
			'description'			=> $value,
			'description_uid'		=> $uid,
			'description_bitfield'	=> $bitfield,
			'description_options'	=> (int) $options,
		);
	}

	public function set_description($value, $allow_bbcode = null, $allow_magic_urls = null, $allow_smilies = null)
	{
		if ($allow_bbcode === null || $allow_magic_urls === null || $allow_smilies === null)
		{
			$allow_bbcode		= ($allow_bbcode === null) ? (bool) ($this->description_options & OPTION_FLAG_BBCODE) : $allow_bbcode;
			$allow_magic_urls	= ($allow_magic_urls === null) ? (bool) ($this->description_options & OPTION_FLAG_LINKS) : $allow_magic_urls;
			$allow_smilies		= ($allow_smilies === null) ? (bool) ($this->description_options & OPTION_FLAG_SMILIES) : $allow_smilies;
		}

		// Merge the current array over the top to preserve the originally modified value
		$this->_modified = array_merge(array(
			'description'			=> $this->description,
			'description_uid'		=> $this->description_uid,
			'description_bitfield'	=> $this->description_bitfield,
			'description_options'	=> $this->description_options
		), $this->_modified);

		$result = self::parse_description($value, $allow_bbcode, $allow_magic_urls, $allow_smilies);

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

		$sql_where = $sql_order_by = '';

		switch ($criteria)
		{
			case self::POPULAR:

			// Temporary
			//break;

			case self::SCORE:
				$sql_order_by = 'ORDER BY score DESC';
			break;

			case self::NEWEST:
			default:
			break;
		}

		$sql_order_by .= (($sql_order_by) ? ',' : 'ORDER BY') . ' ctime DESC';

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

	public static function create($title, $description, voter $user)
	{
		if (!self::can_create($user))
		{
			trigger_error('Arg');
		}

		$data = array_merge(array(
			'title'				=> (string) $title,
			'cost'				=> (int) self::DEFAULT_COST,
			'vote_cost'			=> (int) vote::DEFAULT_COST,
			'user_id'			=> (int) $user->id,
			'topic_id'			=> 0, // @todo
		), self::parse_description($description));

		return new self($data);
	}

	public function voted(voter $voter = null)
	{
		$voter = ($voter === null) ? voter::get_current() : $voter;

		return isset($this->votes[$voter->id]) && $this->votes[$voter->id]->value != vote::DELETED;
	}

	public function get_vote(voter $voter = null)
	{
		$voter = ($voter === null) ? voter::get_current() : $voter;

		return $this->voted() ? $this->votes[$voter->id] : null;
	}

	public function vote(voter $voter, $count, $negate = false)
	{
		if ($vote = vote::add($this, $count, $negate, $voter))
		{
			$this->votes[] = $vote;

			$this->recalculate_score();

			return true;
		}
		return false;
	}

	/**
	 * Permissions related stuff
	 */
	public static function can_create(voter $user = null)
	{
		$user = ($user === null) ? voter::get_current() : $user;

		return $user->is_eligible();
	}

	public function can_edit(voter $user = null)
	{
		$user = ($user === null) ? voter::get_current() : $user;

		return ($user->get_id() === $this->user->get_id()) || $user->is_moderator() || $user->is_administrator();
	}

	public function can_delete(voter $user = null)
	{
		$user = ($user === null) ? voter::get_current() : $user;

		return $user->is_moderator() || $user->is_administrator();
	}

	public function can_vote(voter $user = null)
	{
		$user = ($user === null) ? voter::get_current() : $user;

		return $user->is_eligible() && ($user->get_id() !== $this->user->get_id()) && !$this->voted($user);
	}

	public function can_vote_change($direction = false, voter $user = null)
	{
		$user = ($user === null) ? voter::get_current() : $user;

		switch ($direction)
		{
			case vote::UP:
				$direction = $this->get_vote($user)->value == vote::DOWN;
			break;

			case vote::DOWN:
				$direction = $this->get_vote($user)->value == vote::UP;
			break;

			default:
				// Always assume
				$direction = true;
		}

		return $user->is_eligible() && ($user->get_id() !== $this->user->get_id()) && $this->voted($user) && $this->get_vote($user)->changeable() && $direction;
	}

	public function can_vote_remove(voter $user = null)
	{
		$user = ($user === null) ? voter::get_current() : $user;

		return $user->is_eligible() && ($user->get_id() !== $this->user->get_id()) && $this->voted($user) && $this->get_vote($user)->removable();
	}
}
