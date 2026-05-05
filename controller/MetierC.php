<?php
require_once __DIR__ . '/../model/Metier.php';

class MetierC
{
    private Metier $model;

    public function __construct()
    {
        $this->model = new Metier();
    }

    /** @return Metier[] */
    public function listMetiers(): array
    {
        return $this->model->findAll();
    }

    public function getMetier(int $id): ?Metier
    {
        return $this->model->findById($id);
    }

    public function countMetiers(): int
    {
        return $this->model->countAll();
    }

    public function getAllSecteurs(): array
    {
        return $this->model->getAllSecteurs();
    }

    /**
     * Retourne les métiers compatibles avec le CV trié par score DESC.
     * Filtre optionnel par secteur.
     * @return Metier[]
     */
    public function getRecommandations(int $cvId, string $secteur = ''): array
    {
        $metiers = $this->model->findMetiersCompatibles($cvId);

        // Filtre par secteur si demandé
        if (!empty(trim($secteur))) {
            $metiers = array_filter(
                $metiers,
                fn($m) => strtolower($m->getSecteur()) === strtolower($secteur)
            );
        }

        return array_values($metiers);
    }

    /**
     * Retourne les compétences requises pour un métier donné.
     */
    public function getCompetencesRequises(int $metierId): array
    {
        return $this->model->getCompetencesRequises($metierId);
    }

    /**
     * Ajoute un métier depuis les données POST.
     * Retourne un tableau d'erreurs (vide si succès).
     */
    public function add(array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;

        $m = new Metier();
        $m->setTitre($data['titre']);
        $m->setDescription($data['description']  ?? '');
        $m->setSecteur($data['secteur']          ?? '');
        $m->setSalaireMin((int)($data['salaire_min'] ?? 0));
        $m->setSalaireMax((int)($data['salaire_max'] ?? 0));
        $m->create();

        return [];
    }

    /**
     * Met à jour un métier.
     */
    public function edit(int $id, array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;

        $m = $this->model->findById($id);
        if (!$m) return ['global' => 'Métier introuvable.'];

        $m->setTitre($data['titre']);
        $m->setDescription($data['description']  ?? '');
        $m->setSecteur($data['secteur']          ?? '');
        $m->setSalaireMin((int)($data['salaire_min'] ?? 0));
        $m->setSalaireMax((int)($data['salaire_max'] ?? 0));
        $m->update();

        return [];
    }

    public function delete(int $id): void
    {
        $this->model->delete($id);
    }

    // ── Validation ────────────────────────────────────────────────────────
    private function validate(array $d): array
    {
        $e = [];

        if (empty(trim($d['titre'] ?? ''))) {
            $e['titre'] = "Le titre du métier est obligatoire.";
        } elseif (strlen(trim($d['titre'])) < 3) {
            $e['titre'] = "Le titre doit contenir au moins 3 caractères.";
        }

        if (empty(trim($d['secteur'] ?? ''))) {
            $e['secteur'] = "Le secteur est obligatoire.";
        }

        if (!empty($d['salaire_min']) && !is_numeric($d['salaire_min'])) {
            $e['salaire_min'] = "Le salaire minimum doit être un nombre.";
        }

        if (!empty($d['salaire_max']) && !is_numeric($d['salaire_max'])) {
            $e['salaire_max'] = "Le salaire maximum doit être un nombre.";
        }

        if (!empty($d['salaire_min']) && !empty($d['salaire_max'])) {
            if ((int)$d['salaire_min'] > (int)$d['salaire_max']) {
                $e['salaire_max'] = "Le salaire max doit être supérieur au salaire min.";
            }
        }

        return $e;
    }
}
