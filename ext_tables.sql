#
# Table structure for table 'tx_kequestionnaire_questions'
#
CREATE TABLE tx_kequestionnaire_questions (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	type varchar(11) DEFAULT '' NOT NULL,
	title tinytext NOT NULL,
	show_title tinyint(3) DEFAULT '0' NOT NULL,
	text text NOT NULL,
	helptext text NOT NULL,
	image blob NOT NULL,
	image_position varchar(11) DEFAULT '' NOT NULL,
	mandatory tinyint(3) DEFAULT '0' NOT NULL,
	mandatory_correct tinyint(3) DEFAULT '0' NOT NULL,
	time int(11) DEFAULT '0' NOT NULL,
	dependant_show tinyint(3) DEFAULT '0' NOT NULL,
	open_type int(11) DEFAULT '0' NOT NULL,
	open_pre_text tinytext NOT NULL,
	open_in_text tinytext NOT NULL,
	open_post_text tinytext NOT NULL,
	open_validation varchar(7) DEFAULT '' NOT NULL,
	open_validation_text tinytext NOT NULL,
	open_validation_keywords text NOT NULL,
	open_validation_keywords_all tinyint(3) DEFAULT '0' NOT NULL,
	open_compare_text text NOT NULL,
	closed_type varchar(13) DEFAULT '' NOT NULL,
	closed_selectsize int(11) DEFAULT '0' NOT NULL,
	closed_maxanswers int(11) DEFAULT '0' NOT NULL,
	closed_randomanswers tinyint(3) DEFAULT '0' NOT NULL,
	closed_inputfield int(11) DEFAULT '0' NOT NULL,
	matrix_type varchar(5) DEFAULT '' NOT NULL,
	matrix_validation varchar(7) DEFAULT '' NOT NULL,
	matrix_maxanswers int(11) DEFAULT '0' NOT NULL,
	matrix_inputfield int(11) DEFAULT '0' NOT NULL,
	demographic_type varchar(7) DEFAULT '' NOT NULL,
	demographic_fields text NOT NULL,
	demographic_addressfields text NOT NULL,
	privacy_post tinytext NOT NULL,
	privacy_link tinytext NOT NULL,
	privacy_file blob NOT NULL,
	answers blob NOT NULL,
	columns blob NOT NULL,
	dependancy blob NOT NULL,
	dependancy_simple tinyint(3) DEFAULT '0' NOT NULL,
	subquestions blob NOT NULL,
	sublines blob NOT NULL,
	submatrix blob NOT NULL,
	coords text NOT NULL,
	ddarea_drop_once tinyint(4) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);



#
# Table structure for table 'tx_kequestionnaire_answers'
#
CREATE TABLE tx_kequestionnaire_answers (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	value int(11) DEFAULT '0' NOT NULL,
	correct_answer tinyint(3) DEFAULT '0' NOT NULL,
	text text NOT NULL,
	helptext text NOT NULL,
	image blob NOT NULL,
	image_position varchar(11) DEFAULT '' NOT NULL,
	question_uid int(11) DEFAULT '0' NOT NULL,
	finish_page_uid int(11) DEFAULT '0' NOT NULL,
	show_input tinyint(3) DEFAULT '0' NOT NULL,
	validate_input tinyint(3) DEFAULT '0' NOT NULL,
	answerarea int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_kequestionnaire_columns'
#
CREATE TABLE tx_kequestionnaire_columns (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	different_type varchar(11) DEFAULT '' NOT NULL,
	maxanswers int(11) DEFAULT '0' NOT NULL,
	image blob NOT NULL,
	image_position varchar(11) DEFAULT '' NOT NULL,
	question_uid int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);



#
# Table structure for table 'tx_kequestionnaire_subquestions'
#
CREATE TABLE tx_kequestionnaire_subquestions (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title_line tinyint(4) DEFAULT '0' NOT NULL,
	render_as_slider tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	text text NOT NULL,
	image blob NOT NULL,
	image_position varchar(11) DEFAULT '' NOT NULL,
	question_uid int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);



#
# Table structure for table 'tx_kequestionnaire_dependancies'
#
CREATE TABLE tx_kequestionnaire_dependancies (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	dependant_question int(11) DEFAULT '0' NOT NULL,
	dependant_outcome int(11) DEFAULT '0' NOT NULL,
	activating_question int(11) DEFAULT '0' NOT NULL,
	activating_value int(11) DEFAULT '0' NOT NULL,
	activating_formula tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_kequestionnaire_sublines'
#
CREATE TABLE tx_kequestionnaire_sublines (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	start tinytext NOT NULL,
	end tinytext NOT NULL,
	question_uid int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_kequestionnaire_outcomes'
#
CREATE TABLE tx_kequestionnaire_outcomes (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	type varchar(11) DEFAULT '' NOT NULL,
	title tinytext NOT NULL,
	value_start int(11) DEFAULT '0' NOT NULL,
	value_end int(11) DEFAULT '0' NOT NULL,
	text text NOT NULL,
	image blob NOT NULL,
	image_position varchar(11) DEFAULT '' NOT NULL,
	dependancy blob NOT NULL,
	dependancy_simple tinyint(3) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_kequestionnaire_authcodes'
#
CREATE TABLE tx_kequestionnaire_authcodes (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	qpid tinytext NOT NULL,
	authcode tinytext NOT NULL,
	email tinytext NOT NULL,
	feuser int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_kequestionnaire_results'
#
CREATE TABLE tx_kequestionnaire_results (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	auth int(11) DEFAULT '0' NOT NULL,
	start_tstamp int(11) DEFAULT '0' NOT NULL,
	last_tstamp int(11) DEFAULT '0' NOT NULL,
	finished_tstamp int(11) DEFAULT '0' NOT NULL,
	xmldata longtext NOT NULL,
	ip tinytext NOT NULL,
	mailsent_tstamp int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_kequestionnaire_history'
#
CREATE TABLE tx_kequestionnaire_history (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	xmldata longtext NOT NULL,
	history_time int(11) DEFAULT '0' NOT NULL,
	result_id int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);
