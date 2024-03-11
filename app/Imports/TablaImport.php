<?php

namespace App\Imports;

use App\Models\AdmisionData;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TablaImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    private $idAdmision;
    public function __construct($idAdmision)
    {
        $this->idAdmision = $idAdmision;
    }
    public function collection(Collection $collection)
    {
        $rowCounter = 0;
        $carreras = [
            "Ingeniería en Tecnologias de la Información " => "ITI",
            "Licenciatura en Administración y Gestión Empresarial" => "LAE",
            "Ingeniería en Electrónica y Telecomunicaciones" => "IET",
            "Ingeniería en Tecnología Ambiental" => "ITA",
            "Ingeniería Industrial" => "IIN",
            "Ingeniería Financiera" => "IFI",
            "Ingeniería en Biotecnología" => "IBT",
        ];
        foreach($collection as $row){
            if($rowCounter == 0){
                $rowCounter++;
                continue;
            }
            if($row['carrera'] == "TOTAL"){
                break;
            }   
            $carrera = $carreras[$row['carrera']];
            $solicitudes = $row['numero_de_solicitudes_recibidas_para_ingresar_a_la_carrera_total_incluye_foraneos_unlugarparati_no_presentados_ingresosespeciales'] + $row[6];
            $examenes_presentados = $row['numero_de_aplicaciones_numero_de_alumnos_que_aplicaron_examen_upemor'] + $row[12];
            AdmisionData::create([
                'idAdmision' => $this->idAdmision,
                'carrera' => $carrera,
                'solicitudes' => $solicitudes,
                'examenes_presentados' => $examenes_presentados
            ]);
            $rowCounter++;
        }
    }
    public function headingRow(): int
    {
        return 1;
    }
}
