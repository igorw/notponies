<?php

if (!defined('NP_ROOT_PATH'))
{
	exit;
}

// Bootstrap phpBB
define('IN_PHPBB', true);

$phpbb_root_path = (!isset($phpbb_root_path)) ? './../' : $phpbb_root_path;
$phpEx = (!isset($phpEx)) ? 'php' : $phpEx;

require $phpbb_root_path . 'common.' . $phpEx;

// Bootstrap !ponies
require NP_ROOT_PATH . '/config.php';
require NP_ROOT_PATH . '/includes/constants.php';
require NP_ROOT_PATH . '/includes/classes/ideas.php';

$user->session_begin();
$auth->acl($user->data);
$user->setup();

$template->set_custom_template(NP_ROOT_PATH . '/style', 'np');
$template->assign_var('T_TEMPLATE_PATH', NP_ROOT_PATH . '/style');
$user->theme['template_storedb'] = false;

spl_autoload_register(ideas::AUTOLOADER);

function np_unregister()
{
	spl_autoload_unregister(ideas::AUTOLOADER);
}
