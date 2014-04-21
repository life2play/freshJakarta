CREATE TABLE busway_halte (
  id serial,
  koridorno varchar(10),
  halteid varchar(10),
  seq integer,
  haltename varchar(100),
  lat real,
  long real,
  capacity integer,
  nextdistance real,
  prevdistance real,
  nexthalteid varchar(10),
  prevhalteid varchar(10),
  routeflag integer,
  visibilityflag integer,
  directionflag integer,
  aplicationflag integer,
  sortname varchar(50), -- i believe they meant "short name"
  geolocation text,
  haltetype varchar(5),
  PRIMARY KEY (id)
);

CREATE TABLE busway_eta (
  id serial,
  halteid varchar(10),
  haltename varchar(100),
  long real,
  lat real,
  koridorno varchar(10),
  capacity integer,
  haltetype varchar(5),
  eta integer,
  etatime varchar(50),
  bustime varchar(50),
  system_check_time timestamp,
  PRIMARY KEY (id)
);

