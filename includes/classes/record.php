<?php

abstract class np_record
{
	protected $id;

	protected $_modified = array();

	public function get_id()
	{
		return $this->id;
	}
}
