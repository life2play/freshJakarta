CREATE TABLE userdata (
  id serial,
  userid varchar(25),
  name varchar(100),
  password text,
  status varchar(10),
  is_admin boolean,
  parent_id integer,
  first_login boolean,
  PRIMARY KEY (id),
  FOREIGN KEY (parent_id) REFERENCES userdata (id)
);

CREATE TABLE event (
  id serial,
  user_id integer,
  application varchar(100),
  module varchar(100),
  action varchar(100),
  event text,
  created_at timestamp,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES userdata (id)
);

CREATE TABLE table_fusion (
  id serial,
  type varchar(100),
  parent integer,
  name varchar(100),
  tablename varchar(100),  
  cols varchar(100),
  PRIMARY KEY (id)
  --FOREIGN KEY (parent) REFERENCES table_fusion (id)
);

insert into table_fusion (type, parent, name, tablename, cols) values ('krl',0,'Rute KRL Jabodetabek','1FVon3sWp6JKXrIqKBiHlaxPAoAO-EKJsef7Rv9c','col2');
insert into table_fusion (type, parent, name, tablename, cols) values ('transjakarta', 0,'Rute Transjakarta','1HWPRIE9fqiNyZZ8SIA31XE7nKKRjxWjooAUC-6','col3');