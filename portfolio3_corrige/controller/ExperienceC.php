<?php
require_once __DIR__ . '/../model/Experience.php';
require_once __DIR__ . '/../model/Cv.php';

class ExperienceC
{
    private Experience $model;
    private Cv         $cvModel;

    public function __construct()
    {
        $this->model   = new Experience();
        $this->cvModel = new Cv();
    }

    /** @return Experience[] */
    public function listExperiences(): array { return $this->model->findAll(); }

    /** @return Experience[] */
    public function searchExperiencesByTitre(string $search): array { return $this->model->findByTitre($search); }

    /** @return Experience[] */
    public function listExperiencesSortedByDate(string $order = 'DESC'): array { return $this->model->findAllSortedByDate($order); }

    public function getExperience(int $id): ?Experience { return $this->model->findById($id); }

    /** @return Cv[] */
    public function listCvs(): array { return $this->cvModel->findAll(); }

    public function countExperiences(): int { return $this->model->countAll(); }

    /**
     * Ajoute une expérience.
     * Retourne un tableau d'erreurs (vide si succès).
     */
    public function add(array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;

        $exp = new Experience();
        $exp->setCvId((int)$data['cv_id']);
        $exp->setTitre($data['titre']);
        $exp->setEntreprise($data['entreprise']  ?? '');
        $exp->setLieu($data['lieu']              ?? '');
        $exp->setDateDebut($data['date_debut']);
        $exp->setDateFin(!empty($data['date_fin']) ? $data['date_fin'] : null);
        $exp->setEnCours(isset($data['en_cours']));
        $exp->setDescription($data['description'] ?? '');
        $exp->setTypeContrat($data['type_contrat'] ?? 'Autre');
        $exp->create();

        return [];
    }

    /**
     * Modifie une expérience.
     * Retourne un tableau d'erreurs (vide si succès).
     */
    public function edit(int $id, array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;

        $exp = $this->model->findById($id);
        if (!$exp) return ['global' => 'Expérience introuvable.'];

        $exp->setCvId((int)$data['cv_id']);
        $exp->setTitre($data['titre']);
        $exp->setEntreprise($data['entreprise']  ?? '');
        $exp->setLieu($data['lieu']              ?? '');
        $exp->setDateDebut($data['date_debut']);
        $exp->setDateFin(!empty($data['date_fin']) ? $data['date_fin'] : null);
        $exp->setEnCours(isset($data['en_cours']));
        $exp->setDescription($data['description'] ?? '');
        $exp->setTypeContrat($data['type_contrat'] ?? 'Autre');
        $exp->update();

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

        // Titre obligatoire, min 2 caractères
        if (empty(trim($d['titre'] ?? ''))) {
            $e['titre'] = "Le titre du poste est obligatoire.";
        } elseif (strlen(trim($d['titre'])) < 2) {
            $e['titre'] = "Le titre doit contenir au moins 2 caractères.";
        } elseif (strlen(trim($d['titre'])) > 150) {
            $e['titre'] = "Le titre ne doit pas dépasser 150 caractères.";
        }

        // Type de contrat obligatoire
        $typesValides = ['CDI', 'CDD', 'Stage', 'Freelance', 'Alternance', 'Bénévolat', 'Autre'];
        if (empty($d['type_contrat']) || !in_array($d['type_contrat'], $typesValides)) {
            $e['type_contrat'] = "Veuillez sélectionner un type de contrat valide.";
        }

        // Date de début obligatoire et format valide
        if (empty(trim($d['date_debut'] ?? ''))) {
            $e['date_debut'] = "La date de début est obligatoire.";
        } elseif (!strtotime(trim($d['date_debut']))) {
            $e['date_debut'] = "Format de date invalide (attendu : YYYY-MM-DD).";
        }

        // Date de fin : obligatoire si pas "en cours"
        $enCours = isset($d['en_cours']);
        if (!$enCours) {
            if (empty(trim($d['date_fin'] ?? ''))) {
                $e['date_fin'] = "La date de fin est obligatoire si le poste n'est pas en cours.";
            } elseif (!strtotime(trim($d['date_fin']))) {
                $e['date_fin'] = "Format de date invalide (attendu : YYYY-MM-DD).";
            } elseif (
                !empty($d['date_debut']) && strtotime($d['date_debut']) &&
                strtotime($d['date_fin']) < strtotime($d['date_debut'])
            ) {
                $e['date_fin'] = "La date de fin doit être postérieure à la date de début.";
            }
        }

        return $e;
    }
}
