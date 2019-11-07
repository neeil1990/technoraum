create table b_catalog_vsfr_export
(
	ID int not null auto_increment,
	FILE_NAME varchar(100) not null,
	NAME varchar(250) not null,
	DEFAULT_PROFILE char(1) not null default 'N',
	IN_MENU char(1) not null default 'N',
	IN_AGENT char(1) not null default 'N',
	IN_CRON char(1) not null default 'N',
	SETUP_VARS text null,
	LAST_USE datetime null,
	IS_EXPORT char(1) not null default 'Y',
	NEED_EDIT char(1) not null default 'N',
	TIMESTAMP_X datetime null,
	MODIFIED_BY int(18) null,
	DATE_CREATE datetime null,
	CREATED_BY int(18) null,
	primary key (ID),
	index BCAT_EX_FILE_NAME(FILE_NAME),
	index IX_CAT_IS_EXPORT(IS_EXPORT)
);