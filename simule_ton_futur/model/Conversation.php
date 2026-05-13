<?php

class Conversation
{
    private int    $id;
    private int    $utilisateur1Id;
    private int    $utilisateur2Id;
    private string $sujet;
    private string $dateCreation;

    public function __construct(int $id = 0, int $utilisateur1Id = 0, int $utilisateur2Id = 0, string $sujet = '', string $dateCreation = '')
    {
        $this->id = $id; $this->utilisateur1Id = $utilisateur1Id; $this->utilisateur2Id = $utilisateur2Id;
        $this->sujet = $sujet; $this->dateCreation = $dateCreation;
    }

    public function getId(): int { return $this->id; }
    public function getUtilisateur1Id(): int { return $this->utilisateur1Id; }
    public function getUtilisateur2Id(): int { return $this->utilisateur2Id; }
    public function getSujet(): string { return $this->sujet; }
    public function getDateCreation(): string { return $this->dateCreation; }

    public function setId(int $id): void { $this->id = $id; }
    public function setUtilisateur1Id(int $id): void { $this->utilisateur1Id = $id; }
    public function setUtilisateur2Id(int $id): void { $this->utilisateur2Id = $id; }
    public function setSujet(string $sujet): void { $this->sujet = $sujet; }
    public function setDateCreation(string $date): void { $this->dateCreation = $date; }
}
