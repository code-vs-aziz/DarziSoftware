-- Expand password column for bcrypt hashes (60+ chars).
ALTER TABLE users MODIFY UPASS VARCHAR(255) NOT NULL;
