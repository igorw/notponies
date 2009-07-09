<?php
/**
* @package notponies
* @version 1.0.0-dev
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('IDEA_ROOT_PATH', dirname(__FILE__));

if (!file_exists(IDEA_ROOT_PATH . '/config.php'))
{
	echo 'Missing config.php.';
	exit;
}

// Bootstrap
require IDEA_ROOT_PATH . '/config.php';
require IDEA_ROOT_PATH . '/includes/constants.php';
require IDEA_ROOT_PATH . '/includes/classes/ideas.php';

spl_autoload_register('ideas::autoload');
