<?php

require_once __DIR__ . '/../config.php';

class BlocageC
{
    private array $erreurs = [];

    public function bloquer(int $bloqueurId, int $bloqueId): bool
    {
        $this->erreurs = [];
        if ($bloqueurId === $bloqueId) { $this->erreurs[] = "Vous ne pouvez pas vous bloquer vous-même."; return false; }
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare("INSERT IGNORE INTO blocages (bloqueur_id, bloque_id) VALUES (:b1, :b2)");
            $stmt->execute(['b1' => $bloqueurId, 'b2' => $bloqueId]);
            return true;
        } catch (PDOException $e) { $this->erreurs[] = "Erreur BD : " . $e->getMessage(); return false; }
    }

    public function debloquer(int $bloqueurId, int $bloqueId): bool
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare("DELETE FROM blocages WHERE bloqueur_id = :b1 AND bloque_id = :b2");
            $stmt->execute(['b1' => $bloqueurId, 'b2' => $bloqueId]);
            return true;
        } catch (PDOException $e) { $this->erreurs[] = "Erreur BD : " . $e->getMessage(); return false; }
    }

    public function estBloque(int $bloqueurId, int $bloqueId): bool
    {
        $pdo  = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT id FROM blocages WHERE bloqueur_id = :b1 AND bloque_id = :b2");
        $stmt->execute(['b1' => $bloqueurId, 'b2' => $bloqueId]);
        return (bool) $stmt->fetch();
    }

    public function estBloqueEntreDeux(int $u1, int $u2): bool
    {
        $pdo  = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT id FROM blocages WHERE (bloqueur_id = :a AND bloque_id = :b) OR (bloqueur_id = :b2 AND bloque_id = :a2)");
        $stmt->execute(['a' => $u1, 'b' => $u2, 'b2' => $u2, 'a2' => $u1]);
        return (bool) $stmt->fetch();
    }

    public function listerBloques(int $userId): array
    {
        $pdo  = Config::getConnexion();
        $stmt = $pdo->prepare(
            "SELECT u.idUtilisateur AS id, CONCAT(u.prenom, ' ', u.nom) AS pseudo, u.email, u.role AS categorie, b.date_blocage
             FROM blocages b
             INNER JOIN utilisateur u ON b.bloque_id = u.idUtilisateur
             WHERE b.bloqueur_id = :id
             ORDER BY b.date_blocage DESC"
        );
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getErreurs(): array { return $this->erreurs; }
}
