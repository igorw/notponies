<?php

class np_registry
{
	private $objects = array();

	private static $instance;

	private function __construct() {}
	private function __clone() {}

	public static function &get_instance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function register(&$object)
	{
		$type = get_class($object);

		if (!isset($this->objects[$type]))
		{
			$this->objects[$type] = array();
		}
		$this->objects[$type][$object->id] =& $object;
	}

	public function &get($type, $id)
	{
		if (!isset($this->objects[$type][$id]))
		{
			if (!isset($this->objects[$type]))
			{
				$this->objects[$type] = array();
			}
			$this->objects[$type][$id] = null;
		}
		return $this->objects[$type][$id];
	}

	public function remove($object)
	{
		unset($this->objects[$type][$object->id]);
	}
}
