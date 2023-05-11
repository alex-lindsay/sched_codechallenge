-- Active: 1683827901463@@localhost@3306@sched
CREATE OR REPLACE VIEW active_session
AS
SELECT * FROM session WHERE active IN ('Y', 'A');
