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

$submit = isset($_POST['post']);
$preview = isset($_POST['preview']);
$edit = isset($_REQUEST['i']);

$params = array();

$id = request_var('i', 0);

$bbcode_status	= (bool) $config['allow_bbcode'];
$smilies_status	= (bool) $config['allow_smilies'];
$img_status		= $bbcode_status;
$url_status		= $bbcode_status && $config['allow_post_links'];
$flash_status	= $bbcode_status && $config['allow_post_flash'];
$quote_status	= true;

if ($edit)
{
	$idea = idea::get($id);

	$title			= $idea->title;
	$description	= $idea->description;

	$disable_bbcode		= !($idea->description_options & OPTION_FLAG_BBCODE);
	$disable_smilies	= !($idea->description_options & OPTION_FLAG_SMILIES);
	$disable_magic_url	= !($idea->description_options & OPTION_FLAG_LINKS);

	$params[] = 'mode=edit';
	$params[] = 'i=' . $id;
}
else
{
	$title			= '';
	$description	= '';

	$disable_bbcode		= false;
	$disable_smilies	= false;
	$disable_magic_url	= false;
}

if ($submit || $preview)
{
	$title				= utf8_normalize_nfc(request_var('title', (string) $title, true));
	$description		= utf8_normalize_nfc(request_var('description', (string) $description, true));

	$disable_bbcode		= request_var('disable_bbcode', false);
	$disable_smilies	= request_var('disable_smilies', false);
	$disable_magic_url	= request_var('disable_magic_url', false);

	$enable_bbcode		= $bbcode_status && !$disable_bbcode;
	$enable_smilies		= $smilies_status && !$disable_smilies;
	$enable_magic_url	= $url_status && !$disable_magic_url;

	// No bbcode/smiley/margic disable options
	if (!$edit)
	{
		$idea = idea::create($title, $description, voter::get_current());
	}
	else
	{
		$idea->title = $title;
		$idea->set_description($description, $enable_bbcode, $enable_magic_url, $enable_smilies);
	}

	if ($submit)
	{
		$idea->save();
		trigger_error('w00000t!');
	}
	else
	{
		$template->assign_var('PREVIEW', $idea->description_html);
		$idea->destroy();
	}
}

$template->assign_vars(array(
	// Traverse up one more directory as we are in ./style/
	//'S_EDITOR'	=> $phpbb_root_path . '../styles/prosilver/template/posting_editor.html',

	'TITLE'					=> $title,
	'DESCRIPTION'			=> $description,

	'BBCODE_STATUS'			=> sprintf($user->lang['BBCODE_IS_' . (($bbcode_status) ? 'ON' : 'OFF')], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
	'IMG_STATUS'			=> $user->lang['IMAGES_ARE_' . (($img_status) ? 'ON' : 'OFF')],
	'FLASH_STATUS'			=> $user->lang['FLASH_IS_' . (($flash_status) ? 'ON' : 'OFF')],
	'SMILIES_STATUS'		=> $user->lang['SMILIES_ARE_' . (($smilies_status) ? 'ON' : 'OFF')],
	'URL_STATUS'			=> $user->lang['URL_IS_' . (($url_status) ? 'ON' : 'OFF')],

	'S_POST_ACTION'			=> append_sid(NP_ROOT_PATH . '/post.' . $phpEx, (!empty($params) ? implode('&amp;', $params) : false)),
	'S_SMILIES_ALLOWED'		=> $smilies_status,
	'S_BBCODE_ALLOWED'		=> $bbcode_status,
	'S_LINKS_ALLOWED'		=> $url_status,
	'S_BBCODE_CHECKED'		=> $disable_bbcode ? ' checked="checked"' : '',
	'S_SMILIES_CHECKED'		=> $disable_smilies ? ' checked="checked"' : '',
	'S_MAGIC_URL_CHECKED'	=> $disable_magic_url ? ' checked="checked"' : '',
));

generate_smilies('inline', false);

// Return control to phpBB
np_unregister();

page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => 'post.html'
));

page_footer();
