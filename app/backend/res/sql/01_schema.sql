CREATE TABLE busway_koridor (
  id serial,
  koridor varchar(10),
  starthalte varchar(10),
  finishhalte varchar(10),
  shname varchar(100),
  fhname varchar(100),
  PRIMARY KEY (id)
);

CREATE TABLE busway_halte (
  id serial,
  koridor varchar(10),
  halteid varchar(10),
  haltename varchar(100),
  lat decimal(11,6),
  long decimal(11,6),
  PRIMARY KEY (id)
);

CREATE TABLE busway_halte_real_distance (
  id serial,
  koridor varchar(10),
  ahalteid varchar(10),
  bhalteid varchar(10),
  alat decimal(11,6),
  along decimal(11,6),
  blat decimal(11,6),
  blong decimal(11,6),
  distance double precision,
  PRIMARY KEY (id)
);

CREATE TABLE busway_halte_its (
  id serial,
  koridorno varchar(10),
  halteid varchar(10),
  seq integer,
  haltename varchar(100),
  lat decimal(11,6),
  long decimal(11,6),
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
  lat decimal(11,6),
  long decimal(11,6),
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
  latitude decimal(11,6),
  longitude decimal(11,6),
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
  srclat decimal(11,6), 
  srclong decimal(11,6),
  dstlat decimal(11,6),
  dstlong decimal(11,6),
  distance decimal(12,3),
  PRIMARY KEY(id)
);

CREATE TABLE trayek_opendata (
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
  FOREIGN KEY (trayek_id) REFERENCES trayek_opendata(id)
);

CREATE TABLE rute_kembali (
  id serial,
  trayek_id integer,
  halte varchar(50),
  PRIMARY KEY (id),
  FOREIGN KEY (trayek_id) REFERENCES trayek_opendata(id)
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

CREATE TABLE trayek_umum (
  id serial,
  nama varchar(255),
  jenis varchar(30),
  PRIMARY KEY (id)
);

CREATE TABLE trayek_umum_rute (
  id serial,
  trayek_umum_id integer,
  type varchar(15),
  lat decimal(11,6),
  long decimal(11,6),
  PRIMARY KEY (id),
  FOREIGN KEY (trayek_umum_id) REFERENCES trayek_umum(id)
);

CREATE TABLE trayek_umum_rute_processed (
  id serial,
  trayek_umum_id integer,
  type varchar(15),
  lat decimal(11,6),
  long decimal(11,6),
  PRIMARY KEY (id),
  FOREIGN KEY (trayek_umum_id) REFERENCES trayek_umum(id)
);

CREATE TABLE intersect_angkot_busway (
  id serial,
  trayek_umum_id integer,
  trayek_umum_rute_id integer,
  angkotlat decimal(11,6),
  angkotlong decimal(11,6),
  busway_koridor_id integer,
  busway_halte_id integer,
  buswaylat decimal(11,6),
  buswaylong decimal(11,6),
  distance double precision,
  PRIMARY KEY (id),
  FOREIGN KEY (trayek_umum_id) REFERENCES trayek_umum (id),
  FOREIGN KEY (trayek_umum_rute_id) REFERENCES trayek_umum_rute (id),
  FOREIGN KEY (busway_koridor_id) REFERENCES busway_koridor (id),
  FOREIGN KEY (busway_halte_id) REFERENCES busway_halte (id)
);

CREATE TABLE intersect_busway_busway (
  id serial,
  src_koridor_id integer,
  src_halte_id integer,
  dst_koridor_id integer,
  dst_halte_id integer,
  alat decimal(11,6),
  along decimal(11,6),
  blat decimal(11,6),
  blong decimal(11,6),
  distance double precision,
  PRIMARY KEY (id),
  FOREIGN KEY (src_koridor_id) REFERENCES busway_koridor (id),
  FOREIGN KEY (src_halte_id) REFERENCES busway_halte (id),
  FOREIGN KEY (dst_koridor_id) REFERENCES busway_koridor (id),
  FOREIGN KEY (dst_halte_id) REFERENCES busway_halte (id)
);

CREATE TABLE intersect_angkot_angkot (
  id serial,
  src_trayek_umum_id integer,
  src_trayek_umum_rute_id integer,
  dst_trayek_umum_id integer,
  dst_trayek_umum_rute_id integer,
  alat decimal(11,6),
  along decimal(11,6),
  blat decimal(11,6),
  blong decimal(11,6),
  distance double precision,
  PRIMARY KEY (id),
  FOREIGN KEY (src_trayek_umum_id) REFERENCES trayek_umum (id),
  FOREIGN KEY (src_trayek_umum_rute_id) REFERENCES trayek_umum_rute (id),
  FOREIGN KEY (dst_trayek_umum_id) REFERENCES trayek_umum (id),
  FOREIGN KEY (dst_trayek_umum_rute_id) REFERENCES trayek_umum_rute (id)
);

CREATE TABLE routing_paths (
  id serial,
  path_id integer,
  step integer,
  point_a varchar(30),
  point_b varchar(30),
  path_a varchar(30),
  path_b varchar(30),
  PRIMARY KEY(id)
);

CREATE TABLE layer_theatre (
  id serial,
  nama varchar(100),
  telepon varchar(50),
  lat decimal(11,6),
  long decimal(11,6),
  PRIMARY KEY (id)
);

CREATE TABLE layer_cctv (
  id serial,
  cctv_name varchar(255),
  urladdress varchar(255),
  latitude decimal(11,6),
  longitude decimal(11,6),
  full_path text,
  flag integer,
  info_date timestamp,
  PRIMARY KEY (id)
);
