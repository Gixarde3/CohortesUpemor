<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdminisionesMultiImport implements WithMultipleSheets
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }
    public function sheets(): array
    {
        return [
            'SOLICITUDES_RECIBIDAS' => new SolicitudesImport(),
            'EXANI_II' => new ExaniImport(),
            'CURSO' => new CursoImport(),
            'INSCRITOS' => new InscritosImport()
        ];
    }
}
