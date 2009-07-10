<?php

if (!defined('NP_TABLE_PREFIX'))
{
	define('NP_TABLE_PREFIX', $table_prefix . 'ideas_');
}

define('NP_IDEAS_TABLE', NP_TABLE_PREFIX . 'ideas');
define('NP_VOTES_TABLE', NP_TABLE_PREFIX . 'votes');
define('NP_VOTERS_TABLE', NP_TABLE_PREFIX . 'voters');
