CREATE TABLE busway_halte (
  id serial,
  halteid varchar(10),
  haltename varchar(100),
  koridor varchar(10),
  lat decimal(20,15),
  long decimal(20,15),
  PRIMARY KEY (id)
);

CREATE TABLE busway_halte_its (
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
  haltename varchar(100),  -- destination halte for the bus
  busno varchar(50),
  eta integer,
  latitude decimal(20,15),
  longitude decimal(20,15),
  etatime varchar(50),
  bustime varchar(50),
  PRIMARY KEY (id)
);

CREATE TABLE busway_eta_interim_speed_distance (
  id serial,
  busway_eta_bus_id integer,
  koridorno varchar(10),
  halteid varchar(10),
  busdistance decimal(12,4),
  eta integer,
  speed decimal(12,4),
  PRIMARY KEY(id),
  FOREIGN KEY (busway_eta_bus_id) REFERENCES busway_eta_bus(id)
);

CREATE TABLE cache_distance_query (
  id serial,
  srclat decimal(9,4), 
  srclong decimal(9,4),
  dstlat decimal(9,4),
  dstlong decimal(9,4),
  distance decimal(12,3),
  PRIMARY KEY(id)
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


CREATE TABLE apbd_collections (
  id serial,
  year int,
  unit varchar(200),
  SKPDNama varchar(200),
  urusan varchar(200), 
  namaUrusan varchar(200),
  program varchar(200),
  namaProgram varchar(200),
  noKegiatan varchar(200),
  namaKegiatan varchar(200),
  nilai varchar(200),
  kegiatanId varchar(200),
  SKPDKode2013 varchar(200),
  programKode varchar(200),
  realisasi varchar(200),
  persenRealisasi varchar(200),
  fisik varchar(100),
  idproj varchar(200),
  PRIMARY KEY (id)
);
