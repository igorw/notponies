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

// Business logic
$template->assign_vars(array(
	'U_POST_IDEA'	=> append_sid(NP_ROOT_PATH . '/post.' . $phpEx),
));

foreach (idea::find(idea::POPULAR, 25) as $idea)
{
	$template->assign_block_vars('ideas', array_merge(array(
		'ID'			=> $idea->id,
		'TITLE'			=> $idea->title,
		'DESCRIPTION'	=> $idea->description_html,
		'USERNAME'		=> $idea->user->username('full'),
		'SCORE'			=> $idea->score,

		'S_CAN_DELETE'	=> $idea->can_delete(),
		'S_CAN_EDIT'	=> $idea->can_edit(),
		'S_CAN_VOTE'	=> $idea->can_vote(),

		'U_DELETE'		=> ($this->can_delete()) ? append_sid(NP_ROOT_PATH . '/post.' . $phpEx, "i={$idea->id}&delete") : false,
		'U_EDIT'		=> ($idea->can_edit()) ? append_sid(NP_ROOT_PATH . '/post.' . $phpEx, "i={$idea->id}") : false,
		'U_VOTE_UP'		=> ($idea->can_vote()) ? append_sid(NP_ROOT_PATH . '/vote.' . $phpEx, "i={$idea->id}&amp;up&amp;place") : false,
		'U_VOTE_DOWN'	=> ($idea->can_vote()) ? append_sid(NP_ROOT_PATH . '/vote.' . $phpEx, "i={$idea->id}&amp;down&amp;place") : false,
	), array_combine(array('RANK_TITLE', 'RANK_IMG', 'RANK_IMG_SRC'), $idea->user->rank())));
}

// Return control to phpBB
np_unregister();

page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => 'index.html'
));

page_footer();
