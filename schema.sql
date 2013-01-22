DROP TABLE IF EXISTS tagmap;
DROP TABLE IF EXISTS image;
DROP TABLE IF EXISTS tag;


CREATE TABLE image(
   id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
   title VARCHAR(255),
   img_type VARCHAR(30),
   description VARCHAR(255),
   token VARCHAR(30),
   PRIMARY KEY(id)) ENGINE=InnoDB CHARSET=UTF8;

INSERT INTO image VALUES 
(1, "spongebob squarepants", "jpg","spongebob squarepants and his friend big starfish","tok1"),
(2, "painting stars", "jpg","painting stars","tok2"),
(3, "star by light", "jpg","stars drew by light","tok3"),
(4, "starfish", "gif","a colorful starfish","tok4");


CREATE TABLE tag(
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(255),
	PRIMARY KEY(id)) ENGINE=InnoDB CHARSET=UTF8;
INSERT INTO tag VALUES
(1,"spongebob"),
(2,"squarepants"),
(3,"starfish"),
(4,"painting"),
(5,"star"),
(6,"star"),
(7,"light"),
(8,"colorful"),
(9,"starfish"); 

CREATE TABLE tagmap(
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	img_id INT(10) UNSIGNED NOT NULL,
	tag_id INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY(img_id) REFERENCES image(id),
	FOREIGN KEY(tag_id) REFERENCES tag(id)) ENGINE=InnoDB CHARSET=UTF8;
INSERT INTO tagmap VALUES
(1,1,1),
(2,1,2),
(3,1,3),
(4,2,4),
(5,2,5),
(6,3,6),
(7,3,7),
(8,4,8),
(9,4,9);