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
require $phpbb_root_path . 'includes/functions_posting.' . $phpEx;

$template->assign_vars(array(
	// Traverse up one more directory as we are in ./style/
	'S_EDITOR'	=> $phpbb_root_path . '../styles/prosilver/template/posting_editor.html',
));

generate_smilies('inline', false);

// Return control to phpBB
np_unregister();

page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => 'post.html'
));

page_footer();
