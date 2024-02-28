<?php

namespace App\Imports;

use App\Models\BajaProcesada;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Alumno;
use App\Models\RazonBaja;
use App\Models\Razon;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading; // Add this line
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;

class BajaImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, SkipsOnFailure
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
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            $alumno = Alumno::firstOrCreate([
                'matricula' => $row['matricula']
            ]);
            $alumno->activo = false;
            $alumno->save();
            $bajaProcesada = BajaProcesada::create([
                'idBaja' => $this->idBaja,
                'idAlumno' => $alumno->id,
                'bajaDefinitiva' => $row[strtolower('cierre_'.$this->periodo)] == "Baja Definitiva" ? true : false,
            ]);
            
            $razones = $row['motivo_de_bajas'];
            $divisores = [">", " y "];
            $razones = str_replace($divisores, $divisores[0], $razones);
            $razones = explode($divisores[0], $razones);
            foreach ($razones as $razon) {
                $nuevaRazon = strtoupper($razon);
                $observaciones = null;
                if(strstr($nuevaRazon, "DESEO CAMBIARME A OTRA INSTITUCIóN")){
                    $observacion = explode(",", $nuevaRazon);
                    $nuevaRazon = "DESEO CAMBIARME A OTRA INSTITUCION";
                    $observaciones = $observacion[count($observacion) - 1];
                }
                $bajaProcesada->observaciones = $observaciones;
                $bajaProcesada->save();
                $razon = Razon::firstOrCreate([
                    'nombre' => $nuevaRazon
                ]);
                $idBajaProc = $bajaProcesada->id;
                RazonBaja::create([
                    'idRazon' => $razon->id,
                    'idBajaProcesada' => $idBajaProc
                ]);
            }
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
            'matricula' => ['required']
        ];
    }
    public function onFailure(Failure ...$failures) // Add this line
    {
        // Handle the failures how you'd like.
    }
}
