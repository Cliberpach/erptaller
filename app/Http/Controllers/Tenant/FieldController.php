<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Field\FieldStoreRequest;
use App\Http\Requests\Tenant\Field\FieldUpdateRequest;
use App\Models\Company;
use App\Models\Field;
use App\Models\Plan;
use App\Models\TypeField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FieldController extends Controller
{

    public function editFieldType(Request $request, $id)
    {
        $type_field = TypeField::findOrFail($id);
        
        // Convertir la descripción a mayúsculas antes de guardarla
        $type_field->description = strtoupper($request->input('description'));
        $type_field->save();

        return back()->with('success', 'Tipo de campo actualizado exitosamente.');
    }

    public function indexFieldType(Request $request)
    {
        $type_field = TypeField::all();

        return view('field.typeIndex', compact('type_field'));
    }

    public function deleteFieldType($id)
    {
        // Buscar el tipo de campo por su ID
        $type_field = TypeField::findOrFail($id);

        // Eliminar el tipo de campo
        $type_field->delete();

        // Redirigir de nuevo a la lista de tipos de campo con un mensaje de éxito
        return redirect()->route('tenant.campos.index_tipo_campos')->with('success', 'Tipo de campo eliminado exitosamente.');
    }

    public function fieldType(Request $request)
    {
        $type_field = new TypeField();
        
        // Convertir la descripción a mayúsculas antes de guardarla
        $type_field->description = strtoupper($request->input('description'));
        $type_field->save();

        return back()->with('datos', 'Tipo de campo registrado');
    }

    public function field()
    {
        $fields         = Field::where('isDeleted', false)->get();
        $countFields    = Field::where('isDeleted', false)->count();
        $plan           = Plan::first();

        if($countFields >= $plan->number_fields){
            $create = false;
        }else{
            $create = true;
        }

        return view('field.index', compact('fields', 'create'));
    }

    public function create()
    {
        $type_fields    = TypeField::all();
        $plan           = Plan::first();
        $countFields    = Field::where('isDeleted', false)->count();

        if($countFields >= $plan->number_fields){
            Session::flash('field_error','DEBES SUBIR DE PLAN PARA SEGUIR CREANDO MÁS CAMPOS!!!');
            return redirect()->route('tenant.campos.campo');
        }

        return view('field.create', compact('type_fields'));
    }


/*
array:6 [▼ // app\Http\Controllers\Tenant\FieldController.php:89
  "_token"          => "H0a7hVkTpej3wdmLYSfKzz7aFubL8HquUoauU5Gk"
  "type_field_id"   => "1"
  "field"           => "C1"
  "day_price"       => "12"
  "night_price"     => "14"
  "location"        => "Y DALE U"
]
*/ 
    public function store(FieldStoreRequest $request)
    {
        
        $field                  = new Field();
        $field->type_field_id   = $request->type_field_id;
        $field->field           = $request->field;
        $field->location        = $request->location;
        $field->day_price       = $request->day_price;
        $field->night_price     = $request->night_price;
        $field->save();

        return redirect()->route('tenant.campos.campo');

    }

    public function edit($id)
    {
        $type_fields    = TypeField::all();
        $field          = Field::findOrFail($id);
        return view('field.edit', compact('type_fields', 'field'));
    }

    public function update(FieldUpdateRequest $request, $id)
    {
        
        $field                  = Field::findOrFail($id);
        $field->type_field_id   = $request->type_field_id;
        $field->field           = $request->field;
        $field->location        = $request->location;
        $field->day_price       = $request->day_price;
        $field->night_price     = $request->night_price;
        $field->update();

        return redirect()->route('tenant.campos.campo');
    }

    public function destroy($id)
    {
        $field = Field::findOrFail($id);
        $field->isDeleted = true;
        $field->save();

        return redirect()->route('tenant.campos.campo');
    }
}
