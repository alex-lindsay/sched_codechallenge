-- Active: 1683827901463@@localhost@3306@sched
ALTER TABLE `user` 
    ADD COLUMN sort_last_name varchar(255) GENERATED ALWAYS AS (SUBSTRING_INDEX(TRIM(REGEXP_REPLACE(name, '[^a-zA-Z ]', '')), ' ', -1));