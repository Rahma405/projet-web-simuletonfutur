-- Ajouter une colonne created_at pour le tri par date de création
ALTER TABLE cv ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Mettre à jour les CV existants
UPDATE cv SET created_at = NOW() WHERE created_at IS NULL;
