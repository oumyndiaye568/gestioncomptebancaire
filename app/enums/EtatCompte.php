<?php

namespace App\Enums;

enum EtatCompte: string {
    case ACTIF = 'actif';
    case INACTIF = 'inactif';
    case BLOQUE = 'bloque';
}
