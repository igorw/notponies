<?php
/**
* @package notponies
* @version 1.0.0-dev
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('NP_ROOT_PATH', dirname(__FILE__));

// Bootstrap
require NP_ROOT_PATH . '/includes/bootstrap.php';

$template->assign_vars(array(
	'S_EDITOR'	=> $phpbb_root_path . 'styles/prosilver/template/posting_editor.html',
));

// Return control to phpBB
np_unregister();

page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => 'post.html'
));

page_footer();
