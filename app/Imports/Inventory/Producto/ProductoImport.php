<?php

namespace App\Imports\Inventory\Producto;

use App\Models\Brand;
use App\Models\Product;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductoImport implements ToCollection, WithMultipleSheets
{

    protected $resultado    =   null;

     /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        $con_errores        =   false;
        $listadoProductos   =   [];
        $nombresProcesados  =   [];

        foreach ($rows as $key => $row) {


            // Validar encabezados
            if ($key === 0) {
                $headers = ["NOMBRE", "CÓDIGO BARRAS", "CÓDIGO INTERNO", "CATEGORÍA", "MARCA", "PRECIO VENTA","PRECIO COMPRA", "STOCK MÍNIMO"];

                foreach ($headers as $index => $header) {
                    if (strtoupper(trim($row[$index])) !== $header) {
                        throw new Exception("FORMATO INCORRECTO DEL ARCHIVO EXCEL");
                    }
                }
                continue;
            }

            // Asignación de variables
            $nombre                 = $row[0];
            $codigo_barras          = $row[1];
            $codigo_interno         = $row[2];
            $categoria              = $row[3];
            $marca                  = $row[4];
            //$unidad_medida        = $row[5];
            $precio_venta           = $row[5];
            $precio_compra          = $row[6];
            $stock_minimo           = $row[7];
            $error                  = '';


            // Validaciones
            if (empty($nombre) || strlen(str_replace(' ', '', $nombre)) === 0) {
                continue;
            } elseif (strlen($nombre) > 200) {
                $con_errores = true;
                $error = "El producto '$nombre' tiene más de 200 caracteres.";
            } elseif (in_array($nombre, $nombresProcesados)) {
                $con_errores = true;
                $error = "El producto '$nombre' está repetido en el archivo Excel.";
            } elseif (Product::where('name', $nombre)->where('status', '<>', 'INACTIVE')->exists()) {
                $con_errores = true;
                $error = "El producto '$nombre' ya existe en productos activos.";
            }

            if (!empty($codigo_barras) && strlen($codigo_barras) > 20) {
                $con_errores    =   true;
                $error          .=  " El código de barras tiene más de 20 caracteres.";
            }

            if (!empty($codigo_interno) && strlen($codigo_interno) > 20) {
                $con_errores    =   true;
                $error          .=  " El código interno tiene más de 20 caracteres.";
            }

            if (empty($categoria) || !DB::table('categories as c')
                    ->where('c.name', $categoria)
                    ->where('c.status', 'ACTIVE')
                    ->exists()) {
                $con_errores = true;
                $error .= " La categoría '$categoria' no es válida o no existe.";
            }

            if (empty($marca) || !Brand::where('name', $marca)->where('status','ACTIVE')->exists()) {
                $con_errores    =   true;
                $error          .=  " La marca '$marca' no es válida o no existe.";
            }



            if (!is_numeric($precio_venta)) {
                $con_errores = true;
                $error .= " El precio venta debe ser un valor numérico.";
            }

            if (!is_numeric($precio_compra)) {
                $con_errores = true;
                $error .= " El precio compra debe ser un valor numérico.";
            }

            if (!is_numeric($stock_minimo)) {
                $con_errores = true;
                $error .= " El stock mínimo debe ser un valor numérico.";
            }

            $nombresProcesados[] = $nombre;

            $listadoProductos[] = [
                'fila'                  => $key + 1,
                'nombre'                => $nombre,
                'codigo_barras'         => $codigo_barras,
                'codigo_interno'        => $codigo_interno,
                'categoria'             => $categoria,
                'marca'                 => $marca,
                //'unidad_medida'         => $unidad_medida,
                'precio_venta'          => $precio_venta,
                'precio_compra'         => $precio_compra,
                'stock_minimo'          => $stock_minimo,
                'error'                 => $error,
            ];


        }

        if (count($listadoProductos) === 0) {
            throw new Exception("EL EXCEL ESTÁ VACÍO!!!");
        }


        $this->resultado = (object)['con_errores' => $con_errores, 'listadoProductos' => $listadoProductos];
        return $this->resultado;
    }

    public function sheets(): array
    {
        return [
            'PRODUCTOS' => $this
        ];
    }

    public function getResultados()
    {
        return $this->resultado;
    }
}
