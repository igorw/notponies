<?php

// For lack of a better name currently -_-

class ideas
{
	const AUTOLOADER = 'ideas::autoload';

	public static function autoload($class)
	{
		if (!preg_match('#^[a-z][a-z0-9_]+$#', $class))
		{
			return false;
		}

		if (strpos($class, 'np_') === 0)
		{
			$class = substr($class, 3);
		}

		$file = NP_ROOT_PATH . '/includes/classes/' . $class . '.php';

		if (!file_exists($file) || !is_file($file))
		{
			return false;
		}

		require $file;
	}
}
