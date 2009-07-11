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

			foreach ($this->_modified as $var)
			{
				$col = $var;

				$sql_ary[$col] = $this->$var;
			}

			$sql = 'UPDATE ' . self::TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE id = ' . $this->id;

			$db->sql_query($sql);

			$this->_modified = array();
		}
	}
}
