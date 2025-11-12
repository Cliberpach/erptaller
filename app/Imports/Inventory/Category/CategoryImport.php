<?php

namespace App\Imports\Inventory\Category;

use App\Models\Category;
use App\Models\Productos\Categoria;
use Exception;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;


class CategoryImport implements ToCollection
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
        $listadoCategorias  =   [];
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
            } elseif (Category::where('name', $nombre)->where('status','ACTIVE')->exists()) {
                $con_errores    =   true;
                $error          =   "La categoría '$nombre' ya existe.";
            }elseif (strlen($nombre) > 150) {
                $con_errores    =   true;
                $error          =   "La categoría '$nombre' tiene más de 150 caracteres.";
            }

            $nombresProcesados[] = $nombre;

            $listadoCategorias[] = [
                'fila'      => $key + 1,
                'nombre'    => $nombre,
                'error'     => $error,
            ];
        }

        if(count($listadoCategorias) === 0){
            throw new Exception("EL EXCEL ESTÁ VACÍO!!!");
        }

        $this->resultado    =   (object)['con_errores'=>$con_errores,'listadoCategorias'=>$listadoCategorias];
        return $this->resultado;
    }

    public function getResultados()
    {
        return $this->resultado;
    }
}
