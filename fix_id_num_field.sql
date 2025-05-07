-- Update id_num field in property_owner table to VARCHAR to support various ID formats
ALTER TABLE property_owner
MODIFY COLUMN id_num VARCHAR(50) NOT NULL;
