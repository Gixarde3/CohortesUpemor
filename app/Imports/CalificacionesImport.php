<?php

namespace App\Imports;

use App\Models\CalificacionProcesada;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Add this line
use Maatwebsite\Excel\Concerns\WithBatchInserts; // Add this line
use Maatwebsite\Excel\Concerns\WithChunkReading; // Add this line
use Maatwebsite\Excel\Concerns\WithValidation; // Add this line
use Maatwebsite\Excel\Concerns\SkipsOnFailure; // Add this line
use Maatwebsite\Excel\Validators\Failure;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\Profesor;
use App\Models\Materia;
use App\Models\Cohorte;
class CalificacionesImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, SkipsOnFailure
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $idCreador;
    private $idCalificacion;
    public function __construct($idCreador, $idCalificacion){
        $this->idCreador = $idCreador;
        $this->idCalificacion = $idCalificacion;
    }
    public function model(array $row)
    {
        $grado = null;
        if(!isset($row['nombre_grupo'])){
            return null;
        }
        if(strstr($row['nombre_grupo'], 'Grupo General')){
            $grado = substr($row['nombre_grupo'], 14, 1);
            $caracter = substr($row['nombre_grupo'], 15, 1);
            if(ctype_digit($caracter)){
                $grado = $grado . $caracter;
            }
        }else{
            $grado = substr($row['nombre_grupo'], 0, 1);
            if(ctype_digit($grado)){
                $caracter = substr($row['nombre_grupo'], 1, 1);
                if($caracter != $row['letra']){
                    $grado = $grado . $caracter;
                }
            }else{
                $grado = null;
            }
        }
        $grupo = Grupo::firstOrCreate([
            'clave' => $row['clave_grupo'],
            'nombre' => $row['nombre_grupo'],
            'letra' => $row['letra'],
            'grado' => $grado,
            'periodo' => explode("-", $row['clave_grupo'])[1]
        ]);
        $cohorte = Cohorte::firstOrCreate([
            'periodo' => 'O',
            'anio' => "20".substr($row['matricula'], 4,2),
            'plan' => substr($row['plan_estudios'],0,3)." H".substr($row['matricula'], 4,2),
            'idCreador' => $this->idCreador
        ]);
        $alumno = Alumno::firstOrNew([
            'matricula' => $row['matricula'],
            'idCohorte' => $cohorte->id,
        ]);
        $alumno->apP = $row['paterno_alumno'];
        $alumno->apM = $row['materno_alumno'];
        $alumno->nombre = $row['nombre_alumno'];
        $alumno->activo = $row['estado_alumno'] == "ACTIVO" ? true : false;
        $alumno->save();
        $profesor = Profesor::firstOrCreate([
            'apP' => $row['paterno_profesor'],
            'apM' => $row['materno_profesor'],
            'nombre' => $row['nombre_profesor']
        ]);
        $materia = Materia::firstOrCreate([
            'clave' => $row['clave_materia'],
            'nombre' => $row['nombre_materia'],
            'plan' => $row['plan_estudios']
        ]); 

        return new CalificacionProcesada([
            'idAlumno' => $alumno->id,
            'idMateria' => $materia->id,
            'idProfesor' => $profesor->id,
            'idGrupo' => $grupo->id,
            'idCalificacion' => $this->idCalificacion,
            'calificacion' => $row['calificacion'],
            'tipoCursamiento' => $row['tipo_cursamiento']
        ]);
    }
    public function headingRow(): int
    {
        return 7;
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
            'calificacion' => ['required','integer']
        ];
    }
    public function onFailure(Failure ...$failures) // Add this line
    {
        // Handle the failures how you'd like.
    }
}
