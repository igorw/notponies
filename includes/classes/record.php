<?php

abstract class np_record
{
	protected $id;

	protected $_modified = array();

	// Must be declared
	public static $sql_columns = array();

	protected function get_class()
	{
		return new ReflectionClass(get_class($this));
	}

	public function get_id()
	{
		return $this->id;
	}

	public function save()
	{
		$insert = !$this->get_id();

		if (!empty($this->_modified) || $insert)
		{
			global $db;

			// Blergh ~_~
			$columns = $this->get_class()->getStaticPropertyValue('sql_columns');

			$vars = ($insert) ? array_keys($columns) : array_intersect(array_keys($columns), array_keys($this->_modified));

			$sql_ary = array();

			foreach ($vars as $var)
			{
				if (isset($columns[$var]) && $col = $columns[$var])
				{
					$sql_ary[$col] = ($col === "{$var}_id" && $this->$var instanceof self) ? $this->$var->id : $this->$var;
				}
			}

			if (!$insert)
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
