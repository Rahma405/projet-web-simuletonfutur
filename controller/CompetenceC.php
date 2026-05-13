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

    public function listCompetences(): array { return $this->model->findAll(); }
    public function getCompetence(int $id)  { return $this->model->findById($id); }
    public function listCvs(): array        { return $this->cvModel->findAll(); }
    public function countCompetences(): int { return $this->model->countAll(); }

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
        if (empty($d['cv_id']))
            $e['cv_id'] = "Veuillez selectionner un CV.";
        if (empty($d['nom_competence']))
            $e['nom_competence'] = "Le nom est obligatoire.";
        elseif (strlen($d['nom_competence']) < 2)
            $e['nom_competence'] = "Minimum 2 caracteres.";
        if (empty($d['niveau']))
            $e['niveau'] = "Le niveau est obligatoire.";
        if (empty($d['categorie']))
            $e['categorie'] = "La categorie est obligatoire.";
        return $e;
    }
}
