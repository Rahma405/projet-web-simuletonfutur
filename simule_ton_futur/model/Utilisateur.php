<?php

class Utilisateur
{
    private ?int    $idUtilisateur = null;
    private ?string $nom           = null;
    private ?string $prenom        = null;
    private ?string $email         = null;
    private ?string $motDePasse    = null;
    private string  $role          = 'user';
    private string  $statut        = 'actif';

    public function __construct(
        $id          = null,
        $nom         = null,
        $prenom      = null,
        $email       = null,
        $motDePasse  = null,
        $role        = 'user',
        $statut      = 'actif'
    ) {
        $this->idUtilisateur = $id;
        $this->nom           = $nom;
        $this->prenom        = $prenom;
        $this->email         = $email;
        $this->motDePasse    = $motDePasse;
        $this->role          = $role;
        $this->statut        = $statut;
    }

    public function getIdUtilisateur(): ?int    { return $this->idUtilisateur; }
    public function getNom(): ?string           { return $this->nom;           }
    public function getPrenom(): ?string        { return $this->prenom;        }
    public function getEmail(): ?string         { return $this->email;         }
    public function getMotDePasse(): ?string    { return $this->motDePasse;    }
    public function getRole(): string           { return $this->role;          }
    public function getStatut(): string         { return $this->statut;        }

    
    public function setIdUtilisateur(?int $v): self    { $this->idUtilisateur = $v; return $this; }
    public function setNom(?string $v): self           { $this->nom = $v;           return $this; }
    public function setPrenom(?string $v): self        { $this->prenom = $v;        return $this; }
    public function setEmail(?string $v): self         { $this->email = $v;         return $this; }
    public function setMotDePasse(?string $v): self    { $this->motDePasse = $v;    return $this; }
    public function setRole(string $v): self           { $this->role = $v;          return $this; }
    public function setStatut(string $v): self         { $this->statut = $v;        return $this; }
}
