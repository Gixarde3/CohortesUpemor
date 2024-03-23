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
    /**
     * Devuelve un array con las hojas de cálculo a importar.
     *
     * Esta función devuelve un array que contiene las hojas de cálculo a importar en el archivo AdminisionesMultiImport.
     * Cada elemento del array representa una hoja de cálculo y se asocia con una instancia de una clase de importación específica.
     * Las hojas de cálculo se identifican por su nombre y se asocian con una instancia de una clase de importación correspondiente.
     * 
     * @return array Un array que contiene las hojas de cálculo a importar.
     */
    public function sheets(): array
    {
        return [
            'Tabla' => new TablaImport($this->idAdmision),
            'CURSO' => new CursoImport($this->idAdmision),
            'INSCRITOS' => new InscritosImport($this->idCreador, $this->idAdmision),
        ];
    }
}
