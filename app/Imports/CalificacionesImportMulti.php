<?php 
namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Imports\CalificacionesImport; // Add this line

class CalificacionesImportMulti implements WithMultipleSheets 
{
    private $idCalificaciones;
    private $idCreador;
    public function __construct($idCreador, $idCalificaciones)
    {
        $this->idCreador = $idCreador;
        $this->idCalificaciones = $idCalificaciones;
    }

    public function sheets(): array
    {
        return [
            new CalificacionesImport($this->idCreador, $this->idCalificaciones),
        ];
    }
}
?>
