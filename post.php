<?php
/**
* @package notponies
* @version 1.0.0-dev
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('NP_ROOT_PATH', '.');

// Bootstrap
require NP_ROOT_PATH . '/includes/bootstrap.php';
require $phpbb_root_path . 'includes/functions_posting.' . $phpEx;

$user->add_lang('posting');

$bbcode_status	= (bool) $config['allow_bbcode'];
$smilies_status	= (bool) $config['allow_smilies'];
$img_status		= $bbcode_status;
$url_status		= $bbcode_status && $config['allow_post_links'];
$flash_status	= $bbcode_status && $config['allow_post_flash'];
$quote_status	= true;

$template->assign_vars(array(
	// Traverse up one more directory as we are in ./style/
	//'S_EDITOR'	=> $phpbb_root_path . '../styles/prosilver/template/posting_editor.html',

	'S_SMILIES_ALLOWED'		=> $smilies_status,
	'S_BBCODE_ALLOWED'		=> $bbcode_status,
	'S_LINKS_ALLOWED'		=> $url_status,

	'BBCODE_STATUS'			=> sprintf($user->lang['BBCODE_IS_' . (($bbcode_status) ? 'ON' : 'OFF')], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
	'IMG_STATUS'			=> $user->lang['IMAGES_ARE_' . (($img_status) ? 'ON' : 'OFF')],
	'FLASH_STATUS'			=> $user->lang['FLASH_IS_' . (($flash_status) ? 'ON' : 'OFF')],
	'SMILIES_STATUS'		=> $user->lang['SMILIES_ARE_' . (($smilies_status) ? 'ON' : 'OFF')],
	'URL_STATUS'			=> $user->lang['URL_IS_' . (($url_status) ? 'ON' : 'OFF')],

	'S_POST_ACTION'			=> append_sid(NP_ROOT_PATH . '/post.' . $phpEx),
));

generate_smilies('inline', false);

// Return control to phpBB
np_unregister();

page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => 'post.html'
));

page_footer();
