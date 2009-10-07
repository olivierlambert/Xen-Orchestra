CREATE TYPE PERMISSION AS ENUM ('NONE', 'READ', 'WRITE', 'ADMIN');


-- Maybe it should be better to use CHARACTER VARYING (VARCHAR) instead of
-- CHARACTER because in PostgreSQL there is no performance difference.
-- http://www.postgresql.org/docs/8.4/interactive/datatype-character.html

CREATE TABLE "users"
(
	"id" SERIAL NOT NULL, -- SERIAL = Integer + auto-increment.
	"name" CHARACTER(50) NOT NULL, -- Name = login.
	"password" CHARACTER(32) NOT NULL, -- All passwords are hashed with md5,  thus, the length is 32 characters.
	"email" CHARACTER(320) NOT NULL, -- The maximum size of an email address is 320 characters.
	"permission" PERMISSION NOT NULL,

	PRIMARY KEY ("id"),
	UNIQUE ("name"), -- It seems an index is implied by UNIQUE.
	UNIQUE ("email")
);

CREATE TABLE "acls"
(
	"user_id" INTEGER NOT NULL,
	"dom0_id" CHARACTER(261) NOT NULL, -- DNS: 255 chars max + 1 ':' + 5 chars for the port.
	"domU_name" CHARACTER(50), -- No idea if there is a limit, use 50 until someone complains.
	"permission" PERMISSION NOT NULL,

	PRIMARY KEY ("user_id", "dom0_id", "domU_name"),
	FOREIGN KEY ("user_id") REFERENCES "users" ("id")
);

CREATE INDEX "acls_user_id_key" ON "acls" ("user_id");
