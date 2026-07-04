<?php

namespace App\Http\Controllers\LandLord;

use App\Http\Controllers\Controller;
use App\Http\Services\Landlord\Maintenance\Backup\BackupManager;
use Illuminate\View\View;
use Throwable;

class BackupController extends Controller
{
    private BackupManager $s_manager;

    public function __construct()
    {
        $this->middleware('auth');
        $this->s_manager = new BackupManager();
    }

    public function index(): View
    {
        return view('configuracion.backups');
    }

    public function estado()
    {
        return response()->json($this->s_manager->estadoConBackups());
    }

    public function generar()
    {
        try {
            $estado = $this->s_manager->generar();
            return response()->json([
                'success' => true,
                'message' => 'BACKUP ENCOLADO. SE GENERARÁ EN SEGUNDO PLANO.',
                'estado'  => $estado,
            ]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function descargar(string $archivo)
    {
        $ruta = $this->s_manager->rutaSegura($archivo);

        if (!$ruta) {
            abort(404);
        }

        return response()->download($ruta, $archivo);
    }

    public function eliminar(string $archivo)
    {
        try {
            $this->s_manager->eliminar($archivo);
            return response()->json(['success' => true, 'message' => 'BACKUP ELIMINADO CON ÉXITO']);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
