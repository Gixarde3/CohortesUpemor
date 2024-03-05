<?php

namespace App\Imports;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Imports\SolicitudesImport;
class AdminisionesMultiImport implements WithMultipleSheets
{
    private $idAdmision;
    private $idCreador;
    public function __construct($idAdmision, $idCreador)
    {
        $this->idAdmision = $idAdmision;
        $this->idCreador = $idCreador;
    }
    public function sheets(): array
    {
        return [
            'CURSO' => new CursoImport($this->idAdmision),
            'INSCRITOS' => new InscritosImport($this->idCreador)
        ];
    }
}
