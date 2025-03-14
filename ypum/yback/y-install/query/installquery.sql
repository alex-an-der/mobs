-- ---------------------------------------------------------------------------------
DROP TABLE IF EXISTS y_user_details;
-- ---------------------------------------------------------------------------------
DROP TABLE IF EXISTS y_user;
CREATE TABLE y_user (
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
mail VARCHAR(50) NOT NULL UNIQUE,
locked TINYINT NULL DEFAULT 0,
password VARCHAR(130) NOT NULL,
roles INT UNSIGNED NOT NULL,
lastlogin DATETIME NULL,
created DATETIME NULL,
validated DATETIME NULL,
token VARCHAR(50) NULL UNIQUE,
tokencreated DATETIME NULL,
PRIMARY KEY (ID)
) COLLATE='utf8mb4_general_ci';
-- ---------------------------------------------------------------------------------
DROP TABLE IF EXISTS y_sites;
CREATE TABLE y_sites (
ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
dir VARCHAR(300) NOT NULL UNIQUE,
roles BIGINT UNSIGNED NOT NULL DEFAULT 0,
PRIMARY KEY (ID)
) COLLATE='utf8mb4_general_ci';
-- ---------------------------------------------------------------------------------
DROP TABLE IF EXISTS y_roles;
CREATE TABLE y_roles (
bit INT NOT NULL,
name VARCHAR(80) DEFAULT '',
role_comment VARCHAR(300) DEFAULT '',
role_active BIT(1) NOT NULL DEFAULT 1,
PRIMARY KEY (bit)
) COLLATE='utf8mb4_general_ci';
-- ---------------------------------------------------------------------------------
DROP TABLE IF EXISTS y_user_fields;
CREATE TABLE y_user_fields (
	ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	uf_name VARCHAR(50) NOT NULL DEFAULT '',
	fieldname VARCHAR(50) NOT NULL DEFAULT '',
	PRIMARY KEY (ID) USING BTREE,
	UNIQUE INDEX fieldname (fieldname) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
-- ---------------------------------------------------------------------------------
-- DROP ganbz oben wegen Forein-Keys
CREATE TABLE y_user_details (
	-- ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	userID BIGINT(20) UNSIGNED NOT NULL,
	fieldID BIGINT(20) UNSIGNED NOT NULL,
	fieldvalue VARCHAR(260) NOT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (userID, fieldID) USING BTREE,
	INDEX FK_y_user_details_y_user (userID) USING BTREE,
	INDEX FK_y_user_details_y_user_fields (fieldID) USING BTREE,
	CONSTRAINT FK_y_user_details_y_user FOREIGN KEY (userID) REFERENCES y_user (ID) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT FK_y_user_details_y_user_fields FOREIGN KEY (fieldID) REFERENCES y_user_fields (ID) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8mb4_general_ci' ENGINE=InnoDB AUTO_INCREMENT=1;

-- ---------------------------------------------------------------------------------
DROP VIEW IF EXISTS y_v_userfields;
CREATE VIEW y_v_userfields AS 
SELECT f.ID AS fieldID, d.userID, f.uf_name, f.fieldname, d.fieldvalue 
FROM  y_user_details d LEFT JOIN y_user_fields f ON d.fieldID = f.ID;
-- ---------------------------------------------------------------------------------

-- ---------------------------------------------------------------------------------
INSERT INTO y_roles (bit, name, role_comment, role_active) 
VALUES (0, 'Administrator', 'Automatisch bei der Installation hinzugefügt', b'1');
-- ---------------------------------------------------------------------------------
INSERT INTO y_roles (bit) VALUES ('1');
INSERT INTO y_roles (bit) VALUES ('2');
INSERT INTO y_roles (bit) VALUES ('3');
INSERT INTO y_roles (bit) VALUES ('4');
INSERT INTO y_roles (bit) VALUES ('5');
INSERT INTO y_roles (bit) VALUES ('6');
INSERT INTO y_roles (bit) VALUES ('7');
INSERT INTO y_roles (bit) VALUES ('8');
INSERT INTO y_roles (bit) VALUES ('9');
INSERT INTO y_roles (bit) VALUES ('10');
INSERT INTO y_roles (bit) VALUES ('11');
INSERT INTO y_roles (bit) VALUES ('12');
INSERT INTO y_roles (bit) VALUES ('13');
INSERT INTO y_roles (bit) VALUES ('14');
INSERT INTO y_roles (bit) VALUES ('15');
INSERT INTO y_roles (bit) VALUES ('16');
INSERT INTO y_roles (bit) VALUES ('17');
INSERT INTO y_roles (bit) VALUES ('18');
INSERT INTO y_roles (bit) VALUES ('19');
INSERT INTO y_roles (bit) VALUES ('20');
INSERT INTO y_roles (bit) VALUES ('21');
INSERT INTO y_roles (bit) VALUES ('22');
INSERT INTO y_roles (bit) VALUES ('23');
INSERT INTO y_roles (bit) VALUES ('24');
INSERT INTO y_roles (bit) VALUES ('25');
INSERT INTO y_roles (bit) VALUES ('26');
INSERT INTO y_roles (bit) VALUES ('27');
INSERT INTO y_roles (bit) VALUES ('28');
INSERT INTO y_roles (bit) VALUES ('29');
INSERT INTO y_roles (bit) VALUES ('30');
INSERT INTO y_roles (bit) VALUES ('31');

-- ---------------------------------------------------------------------------------
/*

-- MIT datentyp und pflichtfeld --

DROP TABLE IF EXISTS y_user_fields;
CREATE TABLE y_user_fields (
	ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	uf_name VARCHAR(50) NOT NULL DEFAULT '',
	fieldname VARCHAR(50) NOT NULL DEFAULT '',
  datentyp VARCHAR(20) NOT NULL DEFAULT 'text',
  pflichtfeld BIT(1) NOT NULL DEFAULT b'0',
	PRIMARY KEY (ID) USING BTREE,
	UNIQUE INDEX fieldname (fieldname) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
*/

/*
sort INT UNSIGNED NOT NULL DEFAULT 0,
*/


/*
SET @sql = CONCAT('SELECT userID, ', @sql, ' 
                  FROM y_v_userfields ' ,@vwhere, 
                  ' GROUP BY userID');
*/