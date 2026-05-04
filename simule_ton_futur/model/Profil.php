<?php

class Profil
{
    private ?int    $idProfil      = null;
    private ?string $bio           = null;
    private ?string $photoProfil   = 'default.png';
    private ?string $ville         = null;
    private ?string $pays          = null;
    private ?string $langue        = null;
    private ?int    $idUtilisateur = null;

    public function __construct(
        $idProfil      = null,
        $bio           = null,  
        $photoProfil   = 'default.png',
        $ville         = null,
        $pays          = null,
        $langue        = null,
        $idUtilisateur = null
    ) {
        $this->idProfil      = $idProfil;
        $this->bio           = $bio;
        $this->photoProfil   = $photoProfil ?? 'default.png';
        $this->ville         = $ville;
        $this->pays          = $pays;
        $this->langue        = $langue;
        $this->idUtilisateur = $idUtilisateur;
    }

    
    public function getIdProfil(): ?int      { return $this->idProfil;      }
    public function getBio(): ?string        { return $this->bio;           }
    public function getPhotoProfil(): string { return $this->photoProfil ?? 'default.png'; }
    public function getVille(): ?string      { return $this->ville;         }
    public function getPays(): ?string       { return $this->pays;          }
    public function getLangue(): ?string     { return $this->langue;        }
    public function getIdUtilisateur(): ?int { return $this->idUtilisateur; }

    public function setIdProfil(?int $v): self       { $this->idProfil      = $v; return $this; }
    public function setBio(?string $v): self         { $this->bio           = $v; return $this; }
    public function setPhotoProfil(?string $v): self { $this->photoProfil   = $v; return $this; }
    public function setVille(?string $v): self       { $this->ville         = $v; return $this; }
    public function setPays(?string $v): self        { $this->pays          = $v; return $this; }
    public function setLangue(?string $v): self      { $this->langue        = $v; return $this; }
    public function setIdUtilisateur(?int $v): self  { $this->idUtilisateur = $v; return $this; }
}
