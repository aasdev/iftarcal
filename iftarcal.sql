create table iftarschedule (
`date` date NOT NULL PRIMARY KEY,
numhosts SMALLINT(1) NOT NULL default 0,
hosts TEXT,
numcoord SMALLINT(1) NOT NULL default 0,
coordinators TEXT,
numvolun SMALLINT(1) NOT NULL default 0,
volunteers TEXT);
