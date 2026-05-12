<?php
/**
 * Utilisateur.php — Modèle (Model)
 * Encapsulation complète : attributs private + getters/setters
 */
class Utilisateur
{
    private ?int    $idUtilisateur = null;
    private ?string $nom           = null;
    private ?string $prenom        = null;
    private ?string $email         = null;
    private ?string $motDePasse    = null;
    private string  $role          = 'user';

    public function __construct(
        $id          = null,
        $nom         = null,
        $prenom      = null,
        $email       = null,
        $motDePasse  = null,
        $role        = 'user'
    ) {
        $this->idUtilisateur = $id;
        $this->nom           = $nom;
        $this->prenom        = $prenom;
        $this->email         = $email;
        $this->motDePasse    = $motDePasse;
        $this->role          = $role;
    }

    // ── Getters ──────────────────────────────────────────────
    public function getIdUtilisateur(): ?int    { return $this->idUtilisateur; }
    public function getNom(): ?string           { return $this->nom;           }
    public function getPrenom(): ?string        { return $this->prenom;        }
    public function getEmail(): ?string         { return $this->email;         }
    public function getMotDePasse(): ?string    { return $this->motDePasse;    }
    public function getRole(): string           { return $this->role;          }

    // ── Setters ──────────────────────────────────────────────
    public function setIdUtilisateur(?int $v): self    { $this->idUtilisateur = $v; return $this; }
    public function setNom(?string $v): self           { $this->nom = $v;           return $this; }
    public function setPrenom(?string $v): self        { $this->prenom = $v;        return $this; }
    public function setEmail(?string $v): self         { $this->email = $v;         return $this; }
    public function setMotDePasse(?string $v): self    { $this->motDePasse = $v;    return $this; }
    public function setRole(string $v): self           { $this->role = $v;          return $this; }
}
