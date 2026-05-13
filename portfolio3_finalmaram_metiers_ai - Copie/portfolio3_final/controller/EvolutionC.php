<?php
require_once __DIR__ . '/../model/EvolutionCarriere.php';
require_once __DIR__ . '/../model/Metier.php';
require_once __DIR__ . '/../model/Cv.php';

class EvolutionC
{
    private EvolutionCarriere $model;

    public function __construct()
    {
        $this->model = new EvolutionCarriere();
    }

    public function getEvolutionPourCv(int $cvId): array
    {
        $cvModel = new Cv();
        $cv = $cvModel->findById($cvId);
        if (!$cv) {
            return ['found' => false, 'titre_cv' => '', 'metier_depart' => null, 'chain' => [], 'score' => 0];
        }

        $metierModel = new Metier();
        $recommandations = $metierModel->findMetiersCompatibles($cvId);
        $metierDepart = $recommandations[0] ?? null;

        if (!$metierDepart || $metierDepart->getScore() <= 0) {
            return [
                'found' => false,
                'titre_cv' => $cv['titre_poste'] ?? '',
                'metier_depart' => null,
                'chain' => [],
                'score' => 0,
            ];
        }

        return [
            'found' => true,
            'titre_cv' => $cv['titre_poste'] ?? '',
            'metier_depart' => $metierDepart,
            'chain' => $this->model->getSmartChainEvolution((int)$metierDepart->getId(), $cvId),
            'score' => $metierDepart->getScore(),
        ];
    }
}
