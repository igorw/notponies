<?php
/**
* @package notponies
* @version 1.0.0-dev
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('NP_ROOT_PATH', dirname(__FILE__));

if (!file_exists(NP_ROOT_PATH . '/config.php'))
{
	echo 'Missing config.php.';
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

spl_autoload_register('ideas::autoload');

// Business logic

spl_autoload_unregister('ideas::autoload');

page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => 'index.html'
));

page_footer();
