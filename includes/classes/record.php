<?php

abstract class np_record
{
	protected $id;

	protected $_modified = array();

	public function get_id()
	{
		return $this->id;
	}

	public function save()
	{
		if (!empty($this->_modified))
		{
			global $db;

			$sql_ary = array();

			foreach (array_keys($this->_modified) as $var)
			{
				$col = $var;

				$sql_ary[$col] = $this->$var;
			}

			if ($this->get_id())
			{
				$sql = 'UPDATE ' . constant(get_class($this) .  '::TABLE') . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE id = ' . $this->get_id();
			}
			else
			{
				$sql = 'INSERT INTO ' . constant(get_class($this) .  '::TABLE') . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			}

			$db->sql_query($sql);

			$this->_modified = array();
		}
	}

	public function revert(array $vars = array())
	{
		if (!$this->get_id())
		{
			// Cannot revert an un-inserted record
			return;
		}

		if (empty($vars))
		{
			$vars = array_keys($this->_modified);
		}

		foreach ($vars as $var)
		{
			$this->$var = $this->_modified[$var];
			unset($this->_modified[$var]);
		}
	}
}
