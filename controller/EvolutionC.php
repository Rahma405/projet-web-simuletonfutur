<?php
require_once __DIR__ . '/../model/EvolutionCarriere.php';
require_once __DIR__ . '/../model/Cv.php';

class EvolutionC
{
    private EvolutionCarriere $model;

    public function __construct()
    {
        $this->model = new EvolutionCarriere();
    }

    /**
     * Retourne la chaîne d'évolution complète pour un CV donné.
     * Étapes :
     *  1. Récupérer le titre_poste du CV
     *  2. Trouver le métier correspondant dans la BD
     *  3. Construire la chaîne d'évolution
     *
     * Retourne un tableau :
     * [
     *   'metier_depart'  => array (infos du métier de départ),
     *   'chain'          => EvolutionCarriere[] (étapes suivantes),
     *   'titre_cv'       => string,
     *   'found'          => bool
     * ]
     */
    public function getEvolutionPourCv(int $cvId): array
    {
        $cvModel = new Cv();
        $cv      = $cvModel->findById($cvId);

        if (!$cv) {
            return ['found' => false, 'titre_cv' => '', 'metier_depart' => null, 'chain' => []];
        }

        $titrePoste   = $cv->getTitrePoste();
        $metierDepart = $this->model->findMetierByTitre($titrePoste);

        if (!$metierDepart) {
            return [
                'found'         => false,
                'titre_cv'      => $titrePoste,
                'metier_depart' => null,
                'chain'         => [],
            ];
        }

        $chain = $this->model->getChainEvolution((int)$metierDepart['id']);

        return [
            'found'         => true,
            'titre_cv'      => $titrePoste,
            'metier_depart' => $metierDepart,
            'chain'         => $chain,
        ];
    }

    /**
     * Retourne les infos d'un métier par son id (pour la vue).
     */
    public function getMetierById(int $id): ?array
    {
        return $this->model->getMetierById($id);
    }
}
