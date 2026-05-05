<?php
require_once __DIR__ . '/../model/Competence.php';
require_once __DIR__ . '/../model/Cv.php';

class CompetenceC
{
    private Competence $model;
    private Cv         $cvModel;

    public function __construct()
    {
        $this->model   = new Competence();
        $this->cvModel = new Cv();
    }

    /** @return Competence[] */
    public function listCompetences(): array { return $this->model->findAll(); }

    public function getCompetence(int $id): ?Competence { return $this->model->findById($id); }

    /** @return Cv[] */
    public function listCvs(): array { return $this->cvModel->findAll(); }

    public function countCompetences(): int { return $this->model->countAll(); }

    /**
     * Ajoute une compétence.
     * Retourne un tableau d'erreurs (vide si succès).
     */
    public function add(array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;

        $comp = new Competence();
        $comp->setCvId((int)$data['cv_id']);
        $comp->setNomCompetence($data['nom_competence']);
        $comp->setNiveau($data['niveau']);
        $comp->setCategorie($data['categorie']);
        $comp->create();

        return [];
    }

    /**
     * Modifie une compétence.
     * Retourne un tableau d'erreurs (vide si succès).
     */
    public function edit(int $id, array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;

        $comp = $this->model->findById($id);
        if (!$comp) return ['global' => 'Compétence introuvable.'];

        $comp->setCvId((int)$data['cv_id']);
        $comp->setNomCompetence($data['nom_competence']);
        $comp->setNiveau($data['niveau']);
        $comp->setCategorie($data['categorie']);
        $comp->update();

        return [];
    }

    public function delete(int $id): void
    {
        $this->model->delete($id);
    }

    // ── Validation PHP (pas HTML5) ─────────────────────────────────────────
    private function validate(array $d): array
    {
        $e = [];

        // CV obligatoire
        if (empty($d['cv_id']) || (int)$d['cv_id'] <= 0) {
            $e['cv_id'] = "Veuillez sélectionner un CV.";
        }

        // Nom de la compétence obligatoire, 2–100 chars
        if (empty(trim($d['nom_competence'] ?? ''))) {
            $e['nom_competence'] = "Le nom de la compétence est obligatoire.";
        } elseif (strlen(trim($d['nom_competence'])) < 2) {
            $e['nom_competence'] = "Le nom doit contenir au moins 2 caractères.";
        } elseif (strlen(trim($d['nom_competence'])) > 100) {
            $e['nom_competence'] = "Le nom ne doit pas dépasser 100 caractères.";
        }

        // Niveau obligatoire parmi les valeurs acceptées
        $niveauxValides = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert'];
        if (empty(trim($d['niveau'] ?? ''))) {
            $e['niveau'] = "Le niveau est obligatoire.";
        } elseif (!in_array(trim($d['niveau']), $niveauxValides)) {
            $e['niveau'] = "Niveau invalide. Valeurs acceptées : " . implode(', ', $niveauxValides) . ".";
        }

        // Catégorie obligatoire parmi les valeurs acceptées
        $categoriesValides = ['Programmation', 'Framework', 'Base de données', 'DevOps', 'Design', 'Soft skills', 'Autre'];
        if (empty(trim($d['categorie'] ?? ''))) {
            $e['categorie'] = "La catégorie est obligatoire.";
        } elseif (!in_array(trim($d['categorie']), $categoriesValides)) {
            $e['categorie'] = "Catégorie invalide.";
        }

        return $e;
    }
}
