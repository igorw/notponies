# Temporary install schema
CREATE TABLE phpbb_np_ideas (
	id mediumint(8) UNSIGNED NOT NULL auto_increment,
	user_id mediumint(8) UNSIGNED NOT NULL,
	topic_id mediumint(8) UNSIGNED NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	description mediumtext NOT NULL,
	cost smallint(4) NOT NULL,
	vote_cost smallint(4) NOT NULL,
	vote_closed tinyint(1) DEFAULT 0 NOT NULL,
	rejected tinyint(1) DEFAULT 0 NOT NULL,
	ctime int(11) UNSIGNED NOT NULL,
	mtime int(11) UNSIGNED NOT NULL,
	PRIMARY KEY (id),
	KEY user_id (user_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;

CREATE TABLE phpbb_np_votes (
	id mediumint(8) UNSIGNED NOT NULL auto_increment,
	idea_id mediumint(8) UNSIGNED NOT NULL,
	user_id mediumint(8) UNSIGNED NOT NULL,
	`count` smallint(4) NOT NULL,
	`value` smallint(4) NOT NULL,
	cost smallint(4) NOT NULL,
	ctime int(11) UNSIGNED NOT NULL,
	mtime int(11) UNSIGNED NOT NULL,
	PRIMARY KEY (id),
	UNIQUE idea_user (idea_id, user_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;

CREATE TABLE phpbb_np_voters (
	id mediumint(8) UNSIGNED NOT NULL,
	base_points smallint(4) NOT NULL,
	PRIMARY KEY (id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;
