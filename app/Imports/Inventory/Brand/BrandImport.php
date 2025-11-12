<?php

namespace App\Imports\Inventory\Brand;

use App\Models\Brand;
use App\Models\Category;
use Exception;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;


class BrandImport implements ToCollection
{

    protected $resultado = null;


    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        $con_errores        =   false;
        $listadoMarcas      =   [];
        $nombresProcesados  =   [];

        foreach ($rows as $key => $row) {

            //====== VALIDAR ENCABEZADOS ======
            if ($key === 0) {
                $encabezado =   $row[0];
                if($encabezado !== 'NOMBRE'){
                    throw new Exception("FORMATO INCORRECTO DEL ARCHIVO EXCEL");
                }
                continue;
            }

            $nombre     =   $row[0];
            $error      =   '';

            if (empty($nombre) || strlen(str_replace(' ', '', $nombre)) === 0 ) {
                // $con_errores    =   true;
                // $error          =   "La fila " . ($key + 1) . " está vacía.";
                continue;
            } elseif (in_array($nombre, $nombresProcesados)) {
                $con_errores = true;
                $error = "El nombre '$nombre' está repetido en el archivo Excel.";
            } elseif (Brand::where('name', $nombre)->where('status','ACTIVE')->exists()) {
                $con_errores    =   true;
                $error          =   "La categoría '$nombre' ya existe.";
            }elseif (strlen($nombre) > 150) {
                $con_errores    =   true;
                $error          =   "La categoría '$nombre' tiene más de 150 caracteres.";
            }

            $nombresProcesados[] = $nombre;

            $listadoMarcas[] = [
                'fila'      => $key + 1,
                'name'      => $nombre,
                'error'     => $error,
            ];
        }

        if(count($listadoMarcas) === 0){
            throw new Exception("EL EXCEL ESTÁ VACÍO!!!");
        }

        $this->resultado    =   (object)['con_errores'=>$con_errores,'listadoMarcas'=>$listadoMarcas];
        return $this->resultado;
    }

    public function getResultados()
    {
        return $this->resultado;
    }
}
