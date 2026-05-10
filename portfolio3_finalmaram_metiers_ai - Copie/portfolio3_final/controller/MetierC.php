<?php
require_once __DIR__ . '/../model/Metier.php';

class MetierC
{
    private Metier $model;

    public function __construct()
    {
        $this->model = new Metier();
    }

    public function listMetiers(): array { return $this->model->findAll(); }
    public function countMetiers(): int { return $this->model->countAll(); }
    public function getAllSecteurs(): array { return $this->model->getAllSecteurs(); }
    public function getCompetencesRequises(int $metierId): array { return $this->model->getCompetencesRequises($metierId); }

    public function getRecommandations(int $cvId, string $secteur = ''): array
    {
        $metiers = $this->model->findMetiersCompatibles($cvId);
        if (trim($secteur) !== '') {
            $metiers = array_filter($metiers, fn(Metier $m) => strtolower($m->getSecteur()) === strtolower($secteur));
        }
        return array_values($metiers);
    }

    public function getAlerte(int $cvId, int $seuil = 70): ?Metier
    {
        return $this->model->findMeilleurMetierCompatible($cvId, $seuil);
    }

    public function statsNiveaux(): array { return $this->model->statsNiveauxCompetences(); }
    public function statsSecteurs(): array { return $this->model->statsCvParSecteur(); }
    public function statsDashboard(): array { return $this->model->statsDashboard(); }
}
