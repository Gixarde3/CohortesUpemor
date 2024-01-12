<?php

namespace App\Imports;

use App\Models\CalificacionProcesada;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Add this line
use Maatwebsite\Excel\Concerns\WithBatchInserts; // Add this line
use Maatwebsite\Excel\Concerns\WithChunkReading; // Add this line

class CalificacionesImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $idCalificaciones;

    public function __construct($idCalificaciones)
    {
        $this->idCalificaciones = $idCalificaciones;
    }
    public function model(array $row)
    {
        if(!isset($row['clave_grupo'])){
            return null;
        }
        return new CalificacionProcesada([
            //
            'idCalificacionCuatrimestral' => $this->idCalificaciones,
            'ClaveGrupo'     => $row['clave_grupo'],
            'NombreGrupo'    => $row['nombre_grupo'], 
            'LetraGrupo' => $row['letra'],
            'PaternoProfesor' => $row['paterno_profesor'] ? $row['paterno_profesor'] : " ",
            'MaternoProfesor' => $row['materno_profesor'] ? $row['materno_profesor'] : " ",
            'NombreProfesor' => $row['nombre_profesor'] ? $row['nombre_profesor'] : " ",
            'ClaveMateria' => $row['clave_materia'] ? $row['clave_materia'] : " ",
            'NombreMateria' => $row['nombre_materia'],
            'PlanEstudios' => $row['plan_estudios'],
            'Matricula' => $row['matricula'],
            'PaternoAlumno' => $row['paterno_alumno'] ? $row['paterno_alumno'] : " ",
            'MaternoAlumno' => $row['materno_alumno'] ? $row['materno_alumno'] : " ",
            'NombreAlumno' => $row['nombre_alumno'],
            'EstadoAlumno' => $row['estado_alumno'],
            'CalificacionAlumno' => $row['calificacion'],
            'TipoCursamiento' => $row['tipo_cursamiento']
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
}
