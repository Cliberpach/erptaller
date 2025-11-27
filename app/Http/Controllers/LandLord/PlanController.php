<?php

namespace App\Http\Controllers\LandLord;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlanStoreRequest;
use App\Http\Requests\PlanUpdateRequest;
use App\Models\Plan;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{

    private $plans;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $plans = Plan::select(
            'id',
            'description',
            'price',
            DB::raw('CASE WHEN number_fields > 6 THEN "SIN LÍMITE" ELSE number_fields END AS number_fields')
        )->get();

        return view('plan.index', compact('plans'));
    }

    public function create()
    {
        //
    }

    public function store(PlanStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            Plan::create([
                'description' => $request->get('description'),
                'number_fields' => $request->get('number_fields'),
                'price' => $request->get('price'),
            ]);

            DB::commit();

            $plans = Plan::select(
                'id',
                'description',
                'price',
                DB::raw('CASE WHEN number_fields > 6 THEN "SIN LÍMITE" ELSE number_fields END AS number_fields')
            )->get();

            return response()->json([
                'message' => 'Registro guardado exitosamente',
                'plans' => $plans,
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json($ex->getMessage());
        }
    }

    public function show(Plan $plan)
    {
        //
    }

    public function edit($id)
    {
        $plan = Plan::findOrFail($id);

        return response()->json([
            'data' => $plan
        ]);
    }

    public function update(PlanUpdateRequest $request, $id)
    {
        Plan::findOrFail($id)->update([
            'description' => $request->description,
            'number_fields' => $request->number_fields,
            'price' => $request->price,
        ]);

        $plans = Plan::select(
            'id',
            'description',
            'price',
            DB::raw('CASE WHEN number_fields > 6 THEN "SIN LÍMITE" ELSE number_fields END AS number_fields')
        )->get();

        return response()->json([
            'message' => 'Registro actualizado exitosamente',
            'plans' => $plans
        ]);
    }

    public function delete($id)
    {
        $plan = Plan::findOrFail($id);

        return response()->json([
            'data' => $plan
        ]);
    }

    public function destroy($id)
    {
        Plan::findOrFail($id)->delete();

        $plans = Plan::select(
            'id',
            'description',
            'price',
            DB::raw('CASE WHEN number_fields > 6 THEN "SIN LÍMITE" ELSE number_fields END AS number_fields')
        )->get();

        return response()->json([
            'message' => 'Registro eliminado exitosamente',
            'plans' => $plans
        ]);
    }
}
