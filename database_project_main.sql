DROP DATABASE IF EXISTS planning_zoning;
CREATE DATABASE planning_zoning;

USE planning_zoning;

--Main Database Construction Here
SOURCE pandz_schema_mariadb_final.sql;

--Create procedures for inserts and views for observation
SOURCE find_duplicates.sql;
SOURCE form_insert_procedures.sql;
SOURCE form_update_procedures.sql;
SOURCE database_views.sql;

--Sample login data and sample data for insers
SOURCE auth_schema.sql;
SOURCE database_sample_insert.sql;