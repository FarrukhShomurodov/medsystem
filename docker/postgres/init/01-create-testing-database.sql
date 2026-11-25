-- A separate database for the automated test suite, so `make test`
-- never touches development data.
SELECT 'CREATE DATABASE medsystem_testing OWNER ' || current_user
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'medsystem_testing')\gexec
