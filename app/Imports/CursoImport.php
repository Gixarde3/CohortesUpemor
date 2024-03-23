<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Carbon;
use App\Models\Aspirante;
use App\Models\Ceneval;
use App\Models\Curso;
use Illuminate\Support\Facades\Date;
class CursoImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, SkipsOnFailure
{
    /**
    * @param Collection $collection
    */
    private $idAdmision;
    public function __construct($idAdmision)
    { 
        $this->idAdmision = $idAdmision;
    }
    /**
     * Importa una colección de datos y crea registros de cursos y aspirantes.
     *
     * @param Collection $collection La colección de datos a importar.
     * @return void
     */
    public function collection(Collection $collection)
    {
        //
        $carreras = [
            "INFORMÁTICA" => "ITI",
            "ADMINISTRACIÓN" => "LAE",
            "FINANCIERA" => "IFI",
            "BIOTECNOLOGÍA" => "IBT",
            "ELECTRÓNICA" => "IET",
            "INDUSTRIAL" => "IIN",
            "TECNOLOGÍAS" => "ITI",
            "TECNOLOGÍA" => "ITA"
        ];

        foreach ($collection as $row) {
            $partes = explode(" ", $row['grupo_seleccion']);
            $curso = Curso::firstOrCreate([
                'grupo' => $partes[sizeof($partes) - 1],
                'carrera' => $carreras[$partes[0]]
            ]);
            $aspirante = Aspirante::create(
            [
                'idAdmision' => $this->idAdmision,
                'cedula' => $row['cedula'],
                'apP' => $row['apellido_paterno'],
                'apM' => $row['apellido_materno'],
                'nombre' => $row['nombre'],
                'email' => $row['correo_electronico'],
                'fecha_registro' => $myDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_registro']),
                'telefono1' => $row['telefono_1'],
                'telefono2' => $row['telefono_2'],
                'telefono3' => $row['telefono_3'],
                'carrera' => $row['carrera'] === "IIF-H" ? "ITI-H" : $row['carrera'],
                'municipio' => $row['municipio'],
                'foraneo' => $row['foraneo'] != "NO",
                'tipo' => $row['tipo_aspirante'],
                'promedio' => $row['promedio_bachiller'],
                'escuela_procedencia' => $row['escuela_proc'],
                'curp' => $row['curp'],
                'idCurso' => $curso->id,
                'pago_curso' => $row['pago_curso_seleccion'] != "NO HA PAGADO",
                'aprobo_curso' => $row['estado_curso_seleccion'] == "APROBADO",
                'sexo' => $row['genero'] == 'MASCULINO' ? 'M' : 'F'
            ]
        );
        Ceneval::create(
            [
                'idAspirante' => $aspirante->id,
                'pagado' => $row['pagado_ceneval'] == "SI",
                'folio' => $row['folio_ceneval'],
                'calificacion' => $row['calificacion_ceneval'],
                'fecha' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_examen']),
                'estado' => $row['estado_ceneval'] == "APROBADO"
            ]
        );
        }
    }
    public function headingRow(): int
    {
        return 1;
    }
    public function batchSize(): int
    {
        return 100;
    }
    public function chunkSize(): int
    {
        return 100;
    }
    public function rules(): array
    {
        return [
            
        ];
    }
    public function onFailure(Failure ...$failures) // Add this line
    {
        // Handle the failures how you'd like.
    }
}
