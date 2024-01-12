<?php 
namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Imports\CalificacionesImport; // Add this line

class CalificacionesImportMulti implements WithMultipleSheets 
{
    private $idCalificaciones;

    public function __construct($idCalificaciones)
    {
        $this->idCalificaciones = $idCalificaciones;
    }

    public function sheets(): array
    {
        return [
            new CalificacionesImport($this->idCalificaciones),
        ];
    }
}
?>
