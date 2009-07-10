<?php

// For lack of a better name currently -_-

class ideas
{
	public static function autoload($class)
	{
		if (!preg_match('#^[a-z][a-z0-9_]+$#', $class))
		{
			return;
		}

		require NP_ROOT_PATH . 'includes/classes/' . $class . '.php';
	}
}
