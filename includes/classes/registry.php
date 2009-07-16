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

	public function register(np_record &$object)
	{
		$type = get_class($object);

		if (!isset($this->objects[$type]))
		{
			$this->objects[$type] = array();
		}
		$this->objects[$type][$object->get_id()] =& $object;
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

	public function remove(np_record $object)
	{
		$type = get_class($object);

		unset($this->objects[$type][$object->get_id()]);
	}

	public function shutdown()
	{
		foreach ($this->objects as $objects)
		{
			foreach ($objects as $object)
			{
				$object->save();
			}
		}
	}
}
