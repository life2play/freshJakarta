CREATE TABLE busway_halte (
  id serial,
  koridorno varchar(10),
  halteid varchar(10),
  seq integer,
  haltename varchar(100),
  lat decimal(20,15),
  long decimal(20,15),
  capacity integer,
  nextdistance real,
  prevdistance real,
  nexthalteid varchar(10),
  prevhalteid varchar(10),
  routeflag integer,
  visibilityflag integer,
  directionflag integer,
  aplicationflag integer,
  sortname varchar(50), -- i believe they meant short name
  geolocation text,
  haltetype varchar(10),
  PRIMARY KEY (id)
);

CREATE TABLE busway_eta_halte (
  id serial,
  checktime timestamp,
  srchalte varchar(10),
  koridorno varchar(10),
  halteid varchar(10),
  haltename varchar(100),
  lat decimal(20,15),
  long decimal(20,15),
  capacity integer,
  haltetype varchar(10),
  PRIMARY KEY (id)
);

CREATE TABLE busway_eta_bus (
  id serial,
  checktime timestamp,
  srchalte varchar(10),
  koridorno varchar(10),
  halteid varchar(10),
  haltename varchar(100),
  busno varchar(50),
  eta integer,
  latitude decimal(20,15),
  longitude decimal(20,15),
  etatime varchar(50),
  bustime varchar(50),
  PRIMARY KEY (id)
);

CREATE TABLE trayek (
  id serial,
  jenisangkutan varchar(100),
  jenistrayek varchar(50),
  notrayek varchar(10),
  namatrayek varchar(50),
  terminal varchar(50),
  kodewilayah varchar(30),
  wilayah varchar(30),
  sukudinas varchar(30),
  PRIMARY KEY (id)
);

CREATE TABLE rute_berangkat (
  id serial,
  trayek_id integer,
  halte varchar(50),
  PRIMARY KEY (id),
  FOREIGN KEY (trayek_id) REFERENCES trayek(id)
);

CREATE TABLE rute_kembali (
  id serial,
  trayek_id integer,
  halte varchar(50),
  PRIMARY KEY (id),
  FOREIGN KEY (trayek_id) REFERENCES trayek(id)
);
