<?php
require_once __DIR__ . '/../model/Cv.php';

class CvC
{
    private Cv $model;

    public function __construct()
    {
        $this->model = new Cv();
    }

    public function listCvs(): array        { return $this->model->findAll(); }
    public function getCv(int $id)          { return $this->model->findById($id); }
    public function countCvs(): int         { return $this->model->countAll(); }

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

    public function add(array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;
        $this->model->create($data);
        return [];
    }

    public function edit(int $id, array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return $errors;
        $this->model->update($id, $data);
        return [];
    }

    public function delete(int $id): void { $this->model->delete($id); }

    private function validate(array $d): array
    {
        $e = [];
        if (empty($d['email']))
            $e['email'] = "L'email est obligatoire.";
        elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL))
            $e['email'] = "Email invalide.";
        if (empty($d['titre_poste']))
            $e['titre_poste'] = "Le titre du poste est obligatoire.";
        if (!empty($d['telephone']) && !preg_match('/^[0-9\s\+\-]{6,20}$/', $d['telephone']))
            $e['telephone'] = "Telephone invalide.";
        if (!empty($d['date_naissance']) && !strtotime($d['date_naissance']))
            $e['date_naissance'] = "Date invalide (format : YYYY-MM-DD).";
        return $e;
    }
}
