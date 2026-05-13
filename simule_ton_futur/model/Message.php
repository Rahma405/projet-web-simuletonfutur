<?php

class Message
{
    private int    $id;
    private int    $expediteurId;
    private int    $destinataireId;
    private string $contenu;
    private string $dateEnvoi;
    private int    $estLu;

    public function __construct(int $id = 0, int $expediteurId = 0, int $destinataireId = 0, string $contenu = '', string $dateEnvoi = '', int $estLu = 0)
    {
        $this->id = $id; $this->expediteurId = $expediteurId; $this->destinataireId = $destinataireId;
        $this->contenu = $contenu; $this->dateEnvoi = $dateEnvoi; $this->estLu = $estLu;
    }

    public function getId(): int { return $this->id; }
    public function getExpediteurId(): int { return $this->expediteurId; }
    public function getDestinataireId(): int { return $this->destinataireId; }
    public function getContenu(): string { return $this->contenu; }
    public function getDateEnvoi(): string { return $this->dateEnvoi; }
    public function getEstLu(): int { return $this->estLu; }

    public function setId(int $id): void { $this->id = $id; }
    public function setExpediteurId(int $id): void { $this->expediteurId = $id; }
    public function setDestinataireId(int $id): void { $this->destinataireId = $id; }
    public function setContenu(string $contenu): void { $this->contenu = $contenu; }
    public function setDateEnvoi(string $date): void { $this->dateEnvoi = $date; }
    public function setEstLu(int $estLu): void { $this->estLu = $estLu; }
}
