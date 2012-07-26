CREATE TABLE ubication (
	  id int(11) NOT NULL auto_increment,
	  name varchar(120) NOT NULL,
	  PRIMARY KEY  (id),
	  KEY id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE sucursal (
	  id int(11) NOT NULL auto_increment,
	  name varchar(120) NOT NULL,
	  ubication_id int(11) NOT NULL,
	  PRIMARY KEY  (id),
	  KEY id (id),
	  KEY ubication_id (ubication_id),
	  CONSTRAINT sucursal_ibfk_1 FOREIGN KEY (ubication_id) REFERENCES ubication (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE customer (
	  id int(11) NOT NULL auto_increment,
	  identification varchar(20) NOT NULL,
	  sucursal_id int(11) NOT NULL,
	  name varchar(120) NOT NULL,
	  email varchar(52) default NULL,
	  created_at datetime default NULL,
	  status char(1) default NULL,
	  PRIMARY KEY  (id),
	  KEY sucursal_id (sucursal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE account (
	  id int(11) NOT NULL auto_increment,
	  number int(22) NOT NULL,
	  password varchar(40) NOT NULL,
	  sucursal_id int(11) NOT NULL,
	  customer_id int(11) NOT NULL,
	  balance decimal(30,6) NOT NULL,
	  swap_balance decimal(30,6) NOT NULL,
	  type char(1) NOT NULL,
	  created_at datetime default NULL,
	  status char(1) default NULL,
	  PRIMARY KEY  (id),
	  KEY clientes_id (customer_id),
	  KEY sucursal_id (sucursal_id),
	  CONSTRAINT account_ibfk_1 FOREIGN KEY (customer_id) REFERENCES customer (id),
	  CONSTRAINT account_ibfk_2 FOREIGN KEY (sucursal_id) REFERENCES sucursal (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE movement (
	  id int(11) NOT NULL auto_increment,
	  account_id int(11) NOT NULL,
	  ubication_id int(11) NOT NULL,
	  cash decimal(30,6) NOT NULL,
	  created_at datetime default NULL,
	  PRIMARY KEY  (id),
	  KEY account_id (account_id),
	  KEY ubication_id (ubication_id),
	  CONSTRAINT movement_ibfk_2 FOREIGN KEY (ubication_id) REFERENCES ubication (id),
	  CONSTRAINT movement_ibfk_1 FOREIGN KEY (account_id) REFERENCES account (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

