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

// Bootstrap
require NP_ROOT_PATH . '/includes/bootstrap.php';

// Business logic

// Ensure the user viewing the page is recorded in the DB
voter::get_current();

foreach (idea::find(idea::POPULAR, 25) as $idea)
{
	$template->assign_block_vars('ideas', array(
		'ID'		=> $idea->id,
	));
}

// Return control to phpBB
np_unregister();

page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => 'index.html'
));

page_footer();
