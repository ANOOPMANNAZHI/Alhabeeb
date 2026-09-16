-- Resync every serial/identity sequence in schema "public" to its table's MAX(id).
-- Safe to re-run any time (after a dump restore or bulk insert with explicit ids).
-- Run: psql -h <host> -U <user> -d <db> -f reset_sequences.sql   (or paste into pgAdmin Query Tool)
DO $$
DECLARE r record; mx bigint;
BEGIN
  FOR r IN
    SELECT t.relname AS tbl, a.attname AS col, s.relname AS seq
    FROM pg_class s
    JOIN pg_depend d    ON d.objid = s.oid AND d.deptype = 'a'
    JOIN pg_class t     ON t.oid = d.refobjid
    JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = d.refobjsubid
    JOIN pg_namespace n ON n.oid = t.relnamespace
    WHERE s.relkind = 'S' AND n.nspname = 'public'
  LOOP
    EXECUTE format('SELECT COALESCE(MAX(%I), 0) FROM %I', r.col, r.tbl) INTO mx;
    IF mx > 0 THEN
      EXECUTE format('SELECT setval(%L, %s)', r.seq, mx);
      RAISE NOTICE 'reset % -> %', r.seq, mx;
    END IF;
  END LOOP;
END $$;
