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

// We only process data here, if we get none nothing to do
if (empty($_REQUEST))
{
	trigger_error('INVALID_MODE');
}

$id			= request_var('i', 0);
$negate		= isset($_REQUEST['up']) ? false : (isset($_REQUEST['down']) ? true : null);
$place		= isset($_REQUEST['place']);
$count		= request_var('count', 1);
$voter		= voter::get_current();
$direction	= $negate ? vote::DOWN : vote::UP;

if (!$id || $negate === null)
{
	trigger_error('INVALID_MODE');
}

$idea = idea::get($id);

if (!$place)
{
	// Calculate the cost of voting
	//$idea->vote_cost
}
else
{
	if ($voter->points < ($count * $idea->vote_cost))
	{
		trigger_error('You cannot afford to place this vote.');
	}

	if ($idea->voted($voter))
	{
		$vote = $idea->get_vote($voter);
		$diff = ($direction == $vote->value ? 1 : -1) * $count;

		if ($idea->can_vote_change($direction, $voter) && $vote->change($vote->count + $diff, $negate))
		{
			trigger_error('Yeeehaw!');
		}
		trigger_error('You have already voted.');
	}
	else if ($idea->vote($voter, $count, $negate))
	{
		trigger_error('Yay!');
	}
	else
	{
		trigger_error('There was a problem submitting your vote.');
	}
}
