-- Active: 1683827901463@@localhost@3306@sched
ALTER TABLE session ALTER session_start SET DEFAULT '1900-01-01 00:00:00';
ALTER TABLE session ADD INDEX session_active(active);