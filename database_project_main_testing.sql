DROP DATABASE IF EXISTS planning_zoning_test;
CREATE DATABASE planning_zoning_test;

USE planning_zoning_test;

SOURCE pandz_schema_mariadb_final.sql;
SOURCE database_sample_insert.sql;