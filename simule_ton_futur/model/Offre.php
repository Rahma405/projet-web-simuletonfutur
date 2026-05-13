<?php
/**
 * Offre.php — Modèle (Model)
 * Encapsulation complète : attributs private + getters/setters
 */
class Offre
{
    private ?int $idOffre = null;
    private ?string $titre = null;
    private ?string $competences = null;
    private ?string $localisation = null;

    public function __construct(
        $idOffre = null,
        $titre = null,
        $competences = null,
        $localisation = null
    ) {
        $this->idOffre = $idOffre;
        $this->titre = $titre;
        $this->competences = $competences;
        $this->localisation = $localisation;
    }

    // ── Getters ──────────────────────────────────────────────
    public function getIdOffre(): ?int
    {
        return $this->idOffre;
    }
    public function getTitre(): ?string
    {
        return $this->titre;
    }
    public function getCompetences(): ?string
    {
        return $this->competences;
    }
    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    // ── Setters ──────────────────────────────────────────────
    public function setIdOffre(?int $v): self
    {
        $this->idOffre = $v;
        return $this;
    }
    public function setTitre(?string $v): self
    {
        $this->titre = $v;
        return $this;
    }
    public function setCompetences(?string $v): self
    {
        $this->competences = $v;
        return $this;
    }
    public function setLocalisation(?string $v): self
    {
        $this->localisation = $v;
        return $this;
    }
}
