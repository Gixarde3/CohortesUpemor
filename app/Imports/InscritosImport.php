<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use App\Models\Cohorte;
use App\Models\Alumno;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Add this line
use Maatwebsite\Excel\Concerns\WithBatchInserts; // Add this line
use Maatwebsite\Excel\Concerns\WithChunkReading; // Add this line
use Maatwebsite\Excel\Concerns\WithValidation; // Add this line
use Maatwebsite\Excel\Concerns\SkipsOnFailure; // Add this line
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Validators\Failure;


class InscritosImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, SkipsOnFailure
{
    /**
    * @param Collection $collection
    */
    private $idCreador;
    public function __construct($idCreador)
    {
        $this->idCreador = $idCreador;
    }
    public function collection(Collection $rows)
    {
        foreach($rows as $row){
            $carrera = $row['carrera_2'];
            if($carrera== "INGENIERÍA EN TECNOLOGÍAS DE LA INFORMACIÓN"){
                $carrera = "ITI";
            }
            if($carrera == "INGENIERÍA EN BIOTECNOLOGÍA"){
                $carrera = "IBT";
            }
            if($carrera == "INGENIERÍA EN TECNOLOGÍA AMBIENTAL"){
                $carrera = "ITA";
            }
            if($carrera == "INGENIERÍA FINANCIERA"){
                $carrera = "IFI";
            }
            if($carrera == "INGENIERÍA INDUSTRIAL"){
                $carrera = "IIN";
            }
            if($carrera == "LICENCIATURA EN ADMINISTRACIÓN Y GESTIÓN EMPRESARIAL "){
                $carrera = "LAE";
            }
            if($carrera == "INGENIERÍA EN ELECTRÓNICA Y TELECOMUNICACIONES"){
                $carrera = "IET";
            }
            $cohorte = Cohorte::firstOrCreate([
                'idCreador' => $this->idCreador,
                'periodo' => substr($row['matricula_1'], 3,1),
                'anio' =>"20".substr($row['matricula_1'], 4,2),
                'plan' => $carrera." H".substr($row['matricula_1'], 4,2)
            ]);
            $alumno = Alumno::firstOrNew([
                'matricula' => $row['matricula_1'],
            ]);
            $partesNombre = explode(" ", $row['nombre_4']);
            $nombre = "";
            $apP = "";
            $apM = "";
            for($i = 0; $i < sizeof($partesNombre); $i++){
                if($i == sizeof($partesNombre) - 1){
                    $apP = $partesNombre[$i];
                }
                else if($i == sizeof($partesNombre) - 2){
                    $apM = $partesNombre[$i];
                }
                else{
                    $nombre = $nombre . " " . $partesNombre[$i];
                }
            }
            $alumno->idCohorte = $cohorte->id;
            $alumno->nombre = $nombre;
            $alumno->apP = $apP;
            $alumno->apM = $apM;
            $alumno->activo = true;
            $alumno->save();
        }
    }
    public function rules(): array
    {
        return [
            
        ];
    }
    public function batchSize(): int
    {
        return 100;
    }
    public function chunkSize(): int
    {
        return 100;
    }
    public function onFailure(Failure ...$failures)
    {
        // Handle the failures how you'd like.
    }
    public function headingRow(): int
    {
        return 1;
    }
}
