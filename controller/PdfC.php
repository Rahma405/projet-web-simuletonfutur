<?php
require_once __DIR__ . '/../model/Cv.php';
require_once __DIR__ . '/../model/Competence.php';
require_once __DIR__ . '/../model/Experience.php';

class PdfC
{
    private Cv          $cvModel;
    private Competence  $compModel;
    private Experience  $expModel;

    public function __construct()
    {
        $this->cvModel   = new Cv();
        $this->compModel = new Competence();
        $this->expModel  = new Experience();
    }

    /**
     * Génère le HTML complet d'un CV (template visuel professionnel).
     * Ce HTML sera converti en PDF côté vue via le navigateur (window.print).
     * Retourne null si le CV n'existe pas.
     */
    public function buildCvData(int $cvId): ?array
    {
        $cv = $this->cvModel->findById($cvId);
        if (!$cv) return null;

        $competences = $this->compModel->findByCvId($cvId);
        $experiences = $this->expModel->findByCvId($cvId);

        return [
            'cv'          => $cv,
            'competences' => $competences,
            'experiences' => $experiences,
        ];
    }
}
