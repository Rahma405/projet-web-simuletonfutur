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

    public function listExperiences(): array  { return $this->model->findAll(); }
    public function getExperience(int $id)    { return $this->model->findById($id); }
    public function listCvs(): array          { return $this->cvModel->findAll(); }
    public function countExperiences(): int   { return $this->model->countAll(); }

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
            $e['cv_id'] = "Veuillez sélectionner un CV.";
        if (empty($d['titre']))
            $e['titre'] = "Le titre est obligatoire.";
        elseif (strlen($d['titre']) < 2)
            $e['titre'] = "Minimum 2 caractères.";
        if (empty($d['date_debut']))
            $e['date_debut'] = "La date de début est obligatoire.";
        if (empty($d['type_contrat']))
            $e['type_contrat'] = "Le type de contrat est obligatoire.";
        if (!isset($d['en_cours']) && empty($d['date_fin']))
            $e['date_fin'] = "La date de fin est obligatoire si le poste n'est pas en cours.";
        return $e;
    }
}
