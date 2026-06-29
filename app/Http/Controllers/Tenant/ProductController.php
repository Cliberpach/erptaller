<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Tenant\Inventory\Producto\ProductoExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Tenant\Inventory\Product\ProductoImportExcelRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Tenant\Inventory\Product\ProductStoreRequest;
use App\Http\Requests\Tenant\Inventory\Product\ProductUpdateRequest;
use App\Http\Services\Tenant\Inventory\Product\ProductManager;
use App\Imports\Inventory\Producto\ProductoImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Product;
use App\Models\Tenant\WarehouseProduct;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderProduct;
use Exception;
use Illuminate\Support\Facades\File;
use Throwable;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;


class ProductController extends Controller
{
    protected ProductManager $s_product;

    public function __construct()
    {
        $this->s_product    =   new ProductManager();
    }

    public function index()
    {
        $urlImagen = asset('assets/img/products/img_default.png');

        $categories =   DB::select('SELECT * FROM categories as c where c.status = "ACTIVE"');
        $brands     =   DB::select('SELECT * FROM brands b WHERE b.status = "ACTIVE"');
        $units      =   UtilController::getUnitsMeasurement();

        return view('product.index', compact(
            'urlImagen',
            'categories',
            'brands',
            'units'
        ));
    }

    public function getAll(Request $request)
    {
        $products   =   DB::table('products as p')
            ->join('categories as c', 'c.id', 'p.category_id')
            ->join('brands as b', 'b.id', 'p.brand_id')
            ->select(
                'p.id',
                'p.name',
                'p.description',
                'p.brand_id',
                'p.category_id',
                'c.name as category_name',
                'b.name as brand_name',
                'p.sale_price',
                'p.purchase_price',
                'p.stock',
                'p.stock_min',
                'p.code_factory',
                'p.code_bar',
                'p.img_route',
                'p.unit_id',
                'p.unit_symbol'
            )->where('p.status', 'ACTIVO');

        return DataTables::of($products)->make(true);
    }


    /*
array:12 [ // app\Http\Controllers\Tenant\ProductController.php:74
  "_token" => "toQgu5tmflxhBWA5u0kr4ZpszFEo4UdPaFmcqoRO"
  "name" => "ASDASASDZXC"
  "description" => "ASDZXC"
  "sale_price" => "1"
  "purchase_price" => "1"
  "stock" => "0"
  "stock_min" => "0"
  "code_factory" => null
  "code_bar" => null
  "category_id" => "1"
  "brand_id" => "1"
   "unit_id" => "121"
  "image" =>Illuminate\Http\UploadedFile
*/
    public function store(ProductStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $data       =   $request->validated();
            $product    =   $this->s_product->store($data);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'PRODUCTO REGISTRADO CON ÉXITO', 'product' => $product]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine()]);
        }
    }

    public function deleteImagePublic($name)
    {
        $rutaImagenAnterior = public_path('assets/img/products/' . $name);
        if (File::exists($rutaImagenAnterior) && $name != "img_default.png") {
            File::delete($rutaImagenAnterior);
        }
    }

    /*
array:11 [ // app\Http\Controllers\Tenant\ProductController.php:127
  "_token" => "toQgu5tmflxhBWA5u0kr4ZpszFEo4UdPaFmcqoRO"
  "name_edit" => "ASDASD-EDITADO"
  "description_edit" => null
  "sale_price_edit" => "1.00"
  "purchase_price_edit" => "1.00"
  "stock_min_edit" => "0"
  "code_factory_edit" => null
  "code_bar_edit" => null
  "category_id_edit" => "2"
  "brand_id_edit" => "2"
  "deleteImg"   =>  1
   "unit_id" => "97"
   "image_edit" => Illuminate\Http\UploadedFile
]
*/
    public function update($id, ProductUpdateRequest $request)
    {
        DB::beginTransaction();
        try {

            $data       =   $request->validated();
            $unit       =   GeneralTableDetail::findOrfail($request->get('unit_id'));

            $data['unit_symbol']    =   $unit->symbol;
            $data['unit_name']      =   $unit->name;

            $product    =   Product::findOrFail($id);

            //====== ELIMINAR IMAGEN PREVIA ========
            if ($request->deleteImg == 1) {
                $this->deleteImg($product);
                $data['img_route']  =   null;
                $data['img_name']   =   null;
            }

            $product->update($data);

            DB::commit();
            return response()->json(['success' => true, 'data' => $product, 'message' => 'PRODUCTO ACTUALIZADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $product            =   Product::findOrFail($id);
            $product->status    =   'INACTIVE';
            $product->update();

            $this->deleteImg($product);

            DB::commit();
            return response()->json(['success' => true, 'message' => "PRODUCTO ELIMINADO CON ÉXITO"]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function deleteImg(Product $product)
    {
        if ($product->img_route && file_exists(public_path($product->img_route))) {
            unlink(public_path($product->img_route));
            $product->img_route =   null;
            $product->img_name  =   null;
            $product->update();
        }
    }

    public function getFormatExcel(Request $request)
    {
        return Excel::download(new ProductoExport(), 'formato_import_productos.xlsx');
    }

    /*
array:1 [ // app\Http\Controllers\Tenant\ProductController.php:190
  "productos_import_excel" =>Illuminate\Http\UploadedFile
*/
    /**
     * Normaliza un nombre de catálogo (categoría/marca) para deduplicar antes de
     * firstOrCreate: trim + mayúsculas + colapsar espacios + quitar tildes.
     * La collation utf8mb4_unicode_ci es case-insensitive pero accent-SENSITIVE,
     * por eso se quitan tildes en PHP ("BUJÍAS"/"bujias"/" Bujias " -> "BUJIAS").
     */
    private function normalizarCatalogo(string $nombre): string
    {
        $n = mb_strtoupper(trim($nombre), 'UTF-8');
        $n = preg_replace('/\s+/u', ' ', $n);
        $n = strtr($n, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'Ü' => 'U', 'Ñ' => 'N',
        ]);

        return $n;
    }

    public function importExcel(ProductoImportExcelRequest $request)
    {
        DB::beginTransaction();
        try {

            $import = new ProductoImport();

            Excel::import($import, $request->file('productos_import_excel'));

            $resultado = $import->getResultados();

            if ($resultado->con_errores) {
                return response()->json(['success' => false, 'message' => 'ERRORES EN EL EXCEL', 'resultado' => $resultado]);
            } else {
                $lstProductos  =   $resultado->listadoProductos;

                // Unidad de medida por defecto = UNIDAD (NIU, código SUNAT). Se resuelve por
                // symbol (estable), no por id hardcodeado. Guard claro si no existe.
                $unidadDefault = GeneralTableDetail::where('general_table_id', 5)
                    ->where('symbol', 'NIU')
                    ->first();
                if (! $unidadDefault) {
                    throw new Exception("No existe la unidad de medida UNIDAD (NIU) en el catálogo de unidades.");
                }

                // Catálogos creados al vuelo (firstOrCreate). Se reportan para control.
                $categoriasNuevas = [];
                $marcasNuevas     = [];

                foreach ($lstProductos as $producto_excel) {

                    // firstOrCreate por nombre NORMALIZADO (trim+upper+sin tildes) -> no duplica
                    // por case/espacios/tildes. Si no existe, se crea (status ACTIVE).
                    $categoria = Category::firstOrCreate(
                        ['name' => $this->normalizarCatalogo($producto_excel['categoria'])],
                        ['status' => 'ACTIVE']
                    );
                    if ($categoria->wasRecentlyCreated) {
                        $categoriasNuevas[] = $categoria->name;
                    }

                    $marca = Brand::firstOrCreate(
                        ['name' => $this->normalizarCatalogo($producto_excel['marca'])],
                        ['status' => 'ACTIVE']
                    );
                    if ($marca->wasRecentlyCreated) {
                        $marcasNuevas[] = $marca->name;
                    }

                    $data   =   [
                        'name'              =>  mb_strtoupper($producto_excel['nombre'], 'UTF-8'),
                        'description'       =>  null,
                        'sale_price'        =>  $producto_excel['precio_venta'],
                        'purchase_price'    =>  $producto_excel['precio_compra'],
                        // STOCK INICIAL: ProductService::store rutea stock>0 -> Nota de Ingreso
                        // (almacén 1 + kardex). Vacío/0 -> warehouse en 0, sin kardex.
                        'stock'             =>  $producto_excel['stock_inicial'] ?? 0,
                        'stock_min'         =>  $producto_excel['stock_minimo'],
                        'code_factory'      =>  $producto_excel['codigo_interno'],
                        'code_bar'          =>  $producto_excel['codigo_barras'],
                        'category_id'       =>  $categoria->id,
                        'brand_id'          =>  $marca->id,
                        'unit_id'           =>  $unidadDefault->id, // UNIDAD (NIU) por defecto
                        'image'             =>  null
                    ];

                    $this->s_product->store($data);
                }
                DB::commit();

                // Reporte de catálogos creados al vuelo (sin duplicados).
                $categoriasNuevas = array_values(array_unique($categoriasNuevas));
                $marcasNuevas     = array_values(array_unique($marcasNuevas));

                $msg = 'EXCEL IMPORTADO CON ÉXITO';
                if ($categoriasNuevas || $marcasNuevas) {
                    $msg .= sprintf(
                        ' (%d categoría(s) nueva(s): %s; %d marca(s) nueva(s): %s)',
                        count($categoriasNuevas),
                        $categoriasNuevas ? implode(', ', $categoriasNuevas) : '-',
                        count($marcasNuevas),
                        $marcasNuevas ? implode(', ', $marcasNuevas) : '-'
                    );
                }

                return response()->json([
                    'success'           => true,
                    'message'           => $msg,
                    'resultado'         => $resultado,
                    'categorias_nuevas' => $categoriasNuevas,
                    'marcas_nuevas'     => $marcasNuevas,
                ]);
            }
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine()]);
        }
    }

    /**
     * Buscar productos ignorar stock (para TomSelect server-side)
     */
    public function searchProduct(Request $request)
    {
        $query          =   trim($request->get('q', ''));
        $warehouse_id   =   $request->get('warehouse_id');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $products = Product::from('products as p')
            ->leftjoin('warehouse_products as wp', 'wp.product_id', 'p.id')
            ->join('categories as c', 'c.id', 'p.category_id')
            ->join('brands as b', 'b.id', 'p.brand_id')
            ->where(function ($q) use ($query) {
                $q->where('p.name', 'LIKE', "%{$query}%")
                    ->orWhere('c.name', 'LIKE', "%{$query}%")
                    ->orWhere('b.name', 'LIKE', "%{$query}%");
            })
            ->where(function ($q) use ($warehouse_id) {
                $q->where('wp.warehouse_id', $warehouse_id)
                    ->orWhereNull('wp.warehouse_id');
            })
            ->limit(20)
            ->select(
                'wp.warehouse_id',
                'p.id',
                'b.name as brand_name',
                'p.name',
                'c.name as category_name',
                'p.sale_price',
                DB::raw('COALESCE(wp.stock, 0) as stock')
            )->get();

        $data = $products->map(fn($p) => [
            'id' => $p->id,
            'text' => "{$p->name} - ($p->stock)",
            'subtext' => "{$p->category_name}-{$p->brand_name}",
            'sale_price' =>  $p->sale_price,
            'name'  =>  $p->name,
            'category_name' =>  $p->category_name,
            'brand_name'    =>  $p->brand_name,
            'stock'         =>  $p->stock
        ]);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function searchProductStock(Request $request)
    {
        try {
            $query          =   trim($request->get('q', ''));
            $warehouse_id   =   $request->get('warehouse_id');

            if (empty($query)) {
                return response()->json(['data' => []]);
            }

            $products = Product::from('products as p')
                ->join('warehouse_products as wp', 'wp.product_id', 'p.id')
                ->join('categories as c', 'c.id', 'p.category_id')
                ->join('brands as b', 'b.id', 'p.brand_id')
                ->where('wp.warehouse_id', $warehouse_id)
                ->where('wp.stock', '>', 0)
                ->where(function ($q) use ($query) {
                    $q->where('p.name', 'LIKE', "%{$query}%")
                        ->orWhere('c.name', 'LIKE', "%{$query}%")
                        ->orWhere('b.name', 'LIKE', "%{$query}%");
                })->limit(20)
                ->get(
                    [
                        'wp.warehouse_id',
                        'p.id',
                        'b.name as brand_name',
                        'p.name',
                        'c.name as category_name',
                        'p.sale_price',
                        'wp.stock'
                    ]
                );

            $data = $products->map(fn($p) => [
                'warehouse_id'  =>  $p->warehouse_id,
                'id' => $p->id,
                'text' => "{$p->name}",
                'subtext' => "{$p->category_name}-{$p->brand_name}",
                'sale_price' =>  $p->sale_price,
                'name'  =>  $p->name,
                'category_name' =>  $p->category_name,
                'brand_name'    =>  $p->brand_name,
                'stock'         =>  $p->stock
            ]);

            return response()->json(['success' => true, 'message' => 'PRODUCTOS OBTENIDOS', 'data' => $data]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function validatedProductStock(Request $request)
    {
        try {

            $warehouse_id   = $request->get('warehouse_id');
            $product_id     = $request->get('product_id');
            $quantity       = (float) $request->get('quantity');
            $work_order_id  = $request->get('work_order_id');

            $item = WarehouseProduct::where('warehouse_id', $warehouse_id)
                ->where('product_id', $product_id)
                ->select('stock')
                ->first();

            if (!$item) {
                throw new Exception("Producto no encontrado en almacén.");
            }

            $stock_actual = (float) $item->stock;

            if (!$work_order_id) {

                if ($quantity > $stock_actual) {
                    throw new Exception("STOCK INSUFICIENTE (Stock: $stock_actual, Requiere: $quantity)");
                }

                return response()->json([
                    'success' => true,
                    'message' => 'VALIDACIÓN COMPLETADA'
                ]);
            }

            $item_bd = WorkOrderProduct::where('work_order_id', $work_order_id)
                ->where('product_id', $product_id)
                ->first();

            if (!$item_bd) {

                if ($quantity > $stock_actual) {
                    throw new Exception("STOCK INSUFICIENTE (Stock: $stock_actual, Requiere: $quantity)");
                }

                return response()->json([
                    'success' => true,
                    'message' => 'VALIDACIÓN COMPLETADA (Nuevo item)'
                ]);
            }

            $cantidad_anterior = (float) $item_bd->quantity;
            $diferencia = $quantity - $cantidad_anterior;

            if ($diferencia <= 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'VALIDACIÓN COMPLETADA (Cantidad reducida o igual)'
                ]);
            }

            if ($diferencia > $stock_actual) {
                throw new Exception("STOCK INSUFICIENTE (Stock: $stock_actual, Requiere adicional: $diferencia)");
            }

            return response()->json([
                'success' => true,
                'message' => 'VALIDACIÓN COMPLETADA (Actualización con diferencia)'
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
