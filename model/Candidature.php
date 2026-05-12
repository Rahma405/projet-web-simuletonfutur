<?php
/**
 * Candidature.php — Modèle (Model)
 * Encapsulation complète : attributs private + getters/setters
 */
class Candidature
{
    private ?int $idCandidature = null;
    private ?int $idUtilisateur = null;
    private ?string $skills = null;
    private ?string $cv = null;
    private ?string $localisation = null;

    public function __construct(
        $idCandidature = null,
        $idUtilisateur = null,
        $skills = null,
        $cv = null,
        $localisation = null
    ) {
        $this->idCandidature = $idCandidature;
        $this->idUtilisateur = $idUtilisateur;
        $this->skills = $skills;
        $this->cv = $cv;
        $this->localisation = $localisation;
    }

    // ── Getters ──────────────────────────────────────────────
    public function getIdCandidature(): ?int
    {
        return $this->idCandidature;
    }
    public function getIdUtilisateur(): ?int
    {
        return $this->idUtilisateur;
    }
    public function getSkills(): ?string
    {
        return $this->skills;
    }
    public function getCv(): ?string
    {
        return $this->cv;
    }
    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    // ── Setters ──────────────────────────────────────────────
    public function setIdCandidature(?int $v): self
    {
        $this->idCandidature = $v;
        return $this;
    }
    public function setIdUtilisateur(?int $v): self
    {
        $this->idUtilisateur = $v;
        return $this;
    }
    public function setSkills(?string $v): self
    {
        $this->skills = $v;
        return $this;
    }
    public function setCv(?string $v): self
    {
        $this->cv = $v;
        return $this;
    }
    public function setLocalisation(?string $v): self
    {
        $this->localisation = $v;
        return $this;
    }
}
