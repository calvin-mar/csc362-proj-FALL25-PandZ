DROP DATABASE IF EXISTS planning_zoning_test;
CREATE DATABASE planning_zoning_test;

USE planning_zoning_test;

FLUSH PRIVILEGES;


SOURCE pandz_schema_mariadb_final.sql;
SOURCE database_sample_insert.sql;
SOURCE form_insert_procedures.sql;