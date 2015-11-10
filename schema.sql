CREATE TABLE november_roles (
	id INT auto_increment,
	title VARCHAR(32),
	description TEXT,
	
	PRIMARY KEY (id),
	UNIQUE KEY (title)
);

CREATE TABLE november_users (
	id INT auto_increment,
	username VARCHAR(20),
	password VARCHAR(64),
	created TIMESTAMP default current_timestamp,
	
	PRIMARY KEY (id),
	UNIQUE KEY (username)
);

CREATE TABLE november_posts (
	id INT auto_increment,
	title VARCHAR(255),
	body TEXT,
	created TIMESTAMP default current_timestamp,
	user_id INT,
	
	PRIMARY KEY (id)
);

CREATE TABLE november_user_roles (
	id INT auto_increment,
	user_id INT,
	role_id INT,
	
	PRIMARY KEY (id),
	UNIQUE KEY (user_id, role_id)
);
