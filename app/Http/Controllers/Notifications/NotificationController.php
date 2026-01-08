<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Alerts\Alert;
use App\Models\Tenant\Alerts\AlertUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $userId = auth()->id();
        $today = Carbon::today();

        // Parámetros de paginación
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Consulta con paginación
        $notifications = DB::table('alerts')
            ->where('status', 'ACTIVO')
            //->where('creator_user_id', $userId)
            ->where('advance_date', '>=', $today)
            ->where('notice_date', '>=', $today)
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Total de notificaciones para saber si hay más
        $totalNotifications = DB::table('alerts')
            ->where('status', 'ACTIVO')
            ->where('creator_user_id', $userId)
            ->where('advance_date', '>=', $today)
            ->where('notice_date', '>=', $today)
            ->count();

        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'name' => $notification->name,
                'description' => $notification->description,
                'type_object' => $notification->type_object,
                'object_id' => $notification->object_id,
                'notice_date' => $notification->notice_date,
                'advance_date' => $notification->advance_date,
                'created_at' => $notification->created_at,
                'time_ago' => $this->getTimeAgo($notification->notice_date),
                'icon' => $this->getIconForType($notification->type_object),
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $totalNotifications,
            'notifications' => $formattedNotifications,
            'current_page' => $page,
            'per_page' => $perPage,
            'has_more' => ($offset + $notifications->count()) < $totalNotifications
        ]);
    }

    public function getNotificationsCount()
    {
        $userId = auth()->id();
        $today = Carbon::today();

        $count = DB::table('alerts')
            ->where('status', 'ACTIVO')
            ->where('creator_user_id', $userId)
            ->where('notice_date', '<=', $today)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    public function markAsRead($id)
    {
        DB::table('alerts')
            ->where('id', $id)
            ->where('creator_user_id', auth()->id())
            ->update([
                'status' => 'ANULADO',
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    public function markAllAsRead()
    {
        $userId = auth()->id();

        DB::table('alerts')
            ->where('creator_user_id', $userId)
            ->where('status', 'ACTIVO')
            ->update([
                'status' => 'ANULADO',
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    private function getIconForType($typeObject)
    {
        $icons = [
            'ORDEN_TRABAJO' => [
                'icon' => 'fi fi-rr-tool-box',
                'bgClass' => 'bg-primary'
            ],
            'COTIZACION' => [
                'icon' => 'fi fi-rr-calculator',
                'bgClass' => 'bg-info'
            ],
            'VENTA' => [
                'icon' => 'fi fi-rr-shopping-cart',
                'bgClass' => 'bg-success'
            ],
            'PRODUCCION' => [
                'icon' => 'fi fi-rr-settings',
                'bgClass' => 'bg-warning'
            ],
            'COMPRA' => [
                'icon' => 'fi fi-rr-shopping-bag',
                'bgClass' => 'bg-secondary'
            ],
        ];

        return $icons[$typeObject] ?? [
            'icon' => 'fi fi-rr-bell',
            'bgClass' => 'bg-dark'
        ];
    }

    private function getTimeAgo($date)
    {
        $noticeDate = Carbon::parse($date);
        $now = Carbon::now();

        if ($noticeDate->isToday()) {
            return 'Hoy';
        } elseif ($noticeDate->isYesterday()) {
            return 'Ayer';
        } else {
            $diff = $now->diffInDays($noticeDate);
            return "Hace {$diff} días";
        }
    }

    public function notified(Request $request)
    {
        DB::beginTransaction();
        try {
            $alert_id   =   $request->get('alert_id');
            $userId     =   auth()->id();

            $alert  =   Alert::findOrFail($alert_id);
            $alertUser = AlertUser::firstOrCreate(
                [
                    'alert_id' => $alert->id,
                    'user_id'  => $userId,
                ],
                [
                    'notification_channel' => 'WEB',
                ]
            );

            if (is_null($alertUser->notified_at)) {
                $alertUser->update([
                    'notified_at' => Carbon::now(),
                ]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Alerta marcada como notificada',
            ]);

        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }
}
