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
use App\Models\Cohorte;
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
    private $idCreador;
    public function __construct($idBaja, $periodo, $idCreador)
    {
        $this->idBaja = $idBaja;
        $this->periodo = $periodo;
        $this->idCreador = $idCreador;
    }
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            $carrera = $row['carrera'];
            if($carrera == "INGENIERÍA EN TECNOLOGÍAS DE LA INFORMACIÓN"){
                $carrera = "ITI";
            }
            if($carrera == "INGENIERÍA EN BIOTECNOLOGÍA"){
                $carrera = "IBT";
            }
            if($carrera == "INGENIERÍA TECNOLOGÍA AMBIENTAL"){
                $carrera = "ITA";
            }
            if($carrera == "INGENIERÍA EN FINANZAS"){
                $carrera = "IFI";
            }
            if($carrera == "INGENIERÍA INDUSTRIAL"){
                $carrera = "IIN";
            }
            if($carrera == "LICENCIATURA EN ADMINISTRACIÓN Y GESTIÓN EMPRESARIAL"){
                $carrera = "LAE";
            }
            if($carrera == "INGENIERÍA EN ELECTRÓNICA Y TELECOMUNICACIONES"){
                $carrera = "IET";
            }
            $cohorte = Cohorte::firstOrCreate([
                'periodo' => 'O',
                'anio' => "20".substr($row['matricula'], 4,2),
                'plan' => $carrera." H".substr($row['matricula'], 4,2),
                'idCreador' => $this->idCreador
            ]);
            $alumno = Alumno::firstOrCreate([
                'matricula' => $row['matricula'],
                'idCohorte' => $cohorte->id
            ]);
            $alumno->activo = false;
            $alumno->save();
            $bajaProcesada = BajaProcesada::create([
                'idBaja' => $this->idBaja,
                'idAlumno' => $alumno->id,
                'bajaDefinitiva' => $row[strtolower('cierre_'.$this->periodo)] == "Baja Definitiva" ? true : false,
                'periodo' => $row['periodo']
            ]);
            
            $razones = $row['motivo_de_bajas'];
            $divisores = [">", " y ", " e "];
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
                if(strstr($nuevaRazon, "ACUMULé CUATRO ASIGNATURAS O MáS REPROBADAS EN CUATRIMESTRES ANTERIORES")){
                    $observacion = explode("(", $nuevaRazon);
                    $nuevaRazon = "ACUMULé CUATRO ASIGNATURAS O MáS REPROBADAS EN CUATRIMESTRES ANTERIORES";
                    $observaciones = "(".$observacion[count($observacion) - 1];
                }
                if(strstr($nuevaRazon, "FALTA DE PAGO")){
                    $nuevaRazon = "FALTA DE PAGO";
                }
                if(strstr($nuevaRazon, "PRóRROGA")){
                    $nuevaRazon = "INCUMPLIMIENTO DE PRóRROGA";
                }
                if(strstr($nuevaRazon, "CARRERA")){
                    $nuevaRazon = "CAMBIO DE CARRERA";
                }
                if(strstr($nuevaRazon, "DOMICILIO")){
                    $nuevaRazon = "CAMBIO DE DOMICILIO";
                }
                if(strstr($nuevaRazon, "ECONóMICOS") || strstr($nuevaRazon, "DINERO") || strstr($nuevaRazon, "GASTOS")){
                    $nuevaRazon = "MOTIVOS ECONóMICOS";
                }
                if(strstr($nuevaRazon, "FAMILIARES")){
                    $nuevaRazon = "MOTIVOS FAMILIARES";
                }
                if(strstr($nuevaRazon, "PERSONALES")){
                    $nuevaRazon = "MOTIVOS PERSONALES";
                }
                if(strstr($nuevaRazon, "BAJA TEMPORAL")){
                    $nuevaRazon = "NO REGRESÓ DE BAJA TEMPORAL";
                }
                if(strstr($nuevaRazon, "REACTIVACIóN")){
                    $nuevaRazon = "REACTIVACIóN POR OFICIO UPEMOR";
                }
                if(strstr($nuevaRazon, "HORARIOS")){
                    $nuevaRazon = "HORARIOS INCOMPATIBLES";
                }
                if(strstr($nuevaRazon, "MATERIAS") || str($nuevaRazon, "ASIGNATURAS")){
                    $nuevaRazon = "EXCESO DE MATERIAS REPROBADAS POR CUATRIMESTRE O ACUMULADAS";
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
