<?php
require_once __DIR__ . '/../model/Cv.php';

class CvC
{
    private Cv $model;

    public function __construct()
    {
        $this->model = new Cv();
    }

    /** @return Cv[] */
    public function listCvs(): array { return $this->model->findAll(); }

    /** @return Cv[] */
    public function searchCvsByTitre(string $search): array { return $this->model->findByTitre($search); }

    /** @return Cv[] */
    public function listCvsSortedByDate(string $order = 'DESC'): array { return $this->model->findAllSortedByDate($order); }

    public function getCv(int $id): ?Cv { return $this->model->findById($id); }

    public function countCvs(): int { return $this->model->countAll(); }

    public function getCvWithCompetences(int $id): array
    {
        $rows = $this->model->findWithCompetences($id);
        if (!$rows) return [];
        $cv          = $rows[0];
        $competences = [];
        foreach ($rows as $row) {
            if ($row['comp_id']) {
                $competences[] = [
                    'id'             => $row['comp_id'],
                    'nom_competence' => $row['nom_competence'],
                    'niveau'         => $row['niveau'],
                    'categorie'      => $row['categorie'],
                ];
            }
        }
        return ['cv' => $cv, 'competences' => $competences];
    }

    /**
     * Crée un CV à partir des données POST.
     * Retourne un tableau d'erreurs (vide si succès).
     */
    public function add(array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;

        $cv = new Cv();
        $cv->setEmail($data['email']);
        $cv->setTelephone($data['telephone']      ?? '');
        $cv->setAdresse($data['adresse']          ?? '');
        $cv->setTitrePoste($data['titre_poste']);
        $cv->setDescription($data['description']  ?? '');
        $cv->setGithub($data['github']            ?? '');
        $cv->setLinkedin($data['linkedin']        ?? '');
        $cv->setSiteWeb($data['site_web']         ?? '');
        $cv->setDateNaissance($data['date_naissance'] ?? '');
        $cv->setPhoto($data['photo']              ?? '');
        $cv->create();

        return [];
    }

    /**
     * Met à jour un CV.
     * Retourne un tableau d'erreurs (vide si succès).
     */
    public function edit(int $id, array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;

        $cv = $this->model->findById($id);
        if (!$cv) return ['global' => 'CV introuvable.'];

        $cv->setEmail($data['email']);
        $cv->setTelephone($data['telephone']      ?? '');
        $cv->setAdresse($data['adresse']          ?? '');
        $cv->setTitrePoste($data['titre_poste']);
        $cv->setDescription($data['description']  ?? '');
        $cv->setGithub($data['github']            ?? '');
        $cv->setLinkedin($data['linkedin']        ?? '');
        $cv->setSiteWeb($data['site_web']         ?? '');
        $cv->setDateNaissance($data['date_naissance'] ?? '');
        $cv->setPhoto($data['photo']              ?? '');
        $cv->update();

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

        // Email obligatoire + format
        if (empty(trim($d['email'] ?? ''))) {
            $e['email'] = "L'email est obligatoire.";
        } elseif (!filter_var(trim($d['email']), FILTER_VALIDATE_EMAIL)) {
            $e['email'] = "Format d'email invalide (ex : nom@domaine.com).";
        }

        // Titre du poste obligatoire
        if (empty(trim($d['titre_poste'] ?? ''))) {
            $e['titre_poste'] = "Le titre du poste est obligatoire.";
        } elseif (strlen(trim($d['titre_poste'])) < 2) {
            $e['titre_poste'] = "Le titre doit contenir au moins 2 caractères.";
        }

        // Téléphone : format optionnel mais validé si renseigné
        if (!empty(trim($d['telephone'] ?? ''))) {
            if (!preg_match('/^\+?[0-9\s\-]{6,20}$/', trim($d['telephone']))) {
                $e['telephone'] = "Numéro de téléphone invalide (chiffres, espaces, +, -)";
            }
        }

        // Date de naissance : format YYYY-MM-DD et date passée
        if (!empty(trim($d['date_naissance'] ?? ''))) {
            $ts = strtotime(trim($d['date_naissance']));
            if (!$ts) {
                $e['date_naissance'] = "Date invalide. Format attendu : YYYY-MM-DD.";
            } elseif ($ts > time()) {
                $e['date_naissance'] = "La date de naissance ne peut pas être dans le futur.";
            }
        }

        // URL GitHub (optionnel)
        if (!empty(trim($d['github'] ?? ''))) {
            if (!filter_var(trim($d['github']), FILTER_VALIDATE_URL)) {
                $e['github'] = "L'URL GitHub est invalide.";
            }
        }

        // URL LinkedIn (optionnel)
        if (!empty(trim($d['linkedin'] ?? ''))) {
            if (!filter_var(trim($d['linkedin']), FILTER_VALIDATE_URL)) {
                $e['linkedin'] = "L'URL LinkedIn est invalide.";
            }
        }

        // URL site web (optionnel)
        if (!empty(trim($d['site_web'] ?? ''))) {
            if (!filter_var(trim($d['site_web']), FILTER_VALIDATE_URL)) {
                $e['site_web'] = "L'URL du site web est invalide.";
            }
        }

        return $e;
    }
}
