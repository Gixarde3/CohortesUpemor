<?php

namespace App\Imports;

use App\Models\BajaProcesada;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Alumno;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading; // Add this line
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class BajaImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, SkipsOnFailure
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    private $idBaja;
    private $periodo;
    public function __construct($idBaja, $periodo)
    {
        $this->idBaja = $idBaja;
        $this->periodo = $periodo;
    }
    public function model(array $row)
    {
        $alumno = Alumno::firstOrCreate([
            'matricula' => $row['matricula']
        ]);
        $alumno->activo = false;
        $alumno->save();
        return new BajaProcesada([
            'idBaja' => $this->idBaja,
            'idAlumno' => $alumno->id,
            'bajaDefinitiva' => $row[strtolower('cierre_'.$this->periodo)] == "Baja Definitiva" ? true : false,
            'motivo' => $row['motivo_de_bajas']
        ]);
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
            'matricula' => ['required']
        ];
    }
    public function onFailure(Failure ...$failures) // Add this line
    {
        // Handle the failures how you'd like.
    }
}
