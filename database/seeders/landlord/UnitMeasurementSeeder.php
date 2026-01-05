<?php

namespace Database\Seeders;

use App\Models\Landlord\GeneralTable\GeneralTable;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use Illuminate\Database\Seeder;

class UnitMeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tabla_general_unidades_medida                  =   new GeneralTable();
        $tabla_general_unidades_medida->name            =   'UNIDADES DE MEDIDA';
        $tabla_general_unidades_medida->description     =   'UNIDADES DE MEDIDA';
        $tabla_general_unidades_medida->symbol          =   'UDM';
        $tabla_general_unidades_medida->save();

        $unidades_medida = [
            ['descripcion' => 'BOBINAS', 'simbolo' => '4A'],
            ['descripcion' => 'BALDE', 'simbolo' => 'BJ'],
            ['descripcion' => 'BARRILES', 'simbolo' => 'BLL'],
            ['descripcion' => 'BOLSA', 'simbolo' => 'BG'],
            ['descripcion' => 'BOTELLAS', 'simbolo' => 'BO'],
            ['descripcion' => 'CAJA', 'simbolo' => 'BX'],
            ['descripcion' => 'CARTONES', 'simbolo' => 'CT'],
            ['descripcion' => 'CENTIMETRO CUADRADO', 'simbolo' => 'CMK'],
            ['descripcion' => 'CENTIMETRO CUBICO', 'simbolo' => 'CMQ'],
            ['descripcion' => 'CENTIMETRO LINEAL', 'simbolo' => 'CMT'],
            ['descripcion' => 'CIENTO DE UNIDADES', 'simbolo' => 'CEN'],
            ['descripcion' => 'CILINDRO', 'simbolo' => 'CY'],
            ['descripcion' => 'CONOS', 'simbolo' => 'CJ'],
            ['descripcion' => 'DOCENA', 'simbolo' => 'DZN'],
            ['descripcion' => 'DOCENA POR 10**6', 'simbolo' => 'DZP'],
            ['descripcion' => 'FARDO', 'simbolo' => 'BE'],
            ['descripcion' => 'GALON INGLES (4,545956L)', 'simbolo' => 'GLI'],
            ['descripcion' => 'GRAMO', 'simbolo' => 'GRM'],
            ['descripcion' => 'GRUESA', 'simbolo' => 'GRO'],
            ['descripcion' => 'HECTOLITRO', 'simbolo' => 'HLT'],
            ['descripcion' => 'HOJA', 'simbolo' => 'LEF'],
            ['descripcion' => 'JUEGO', 'simbolo' => 'SET'],
            ['descripcion' => 'KILOGRAMO', 'simbolo' => 'KGM'],
            ['descripcion' => 'KILOMETRO', 'simbolo' => 'KTM'],
            ['descripcion' => 'KILOVATIO HORA', 'simbolo' => 'KWH'],
            ['descripcion' => 'KIT', 'simbolo' => 'KIT'],
            ['descripcion' => 'LATAS', 'simbolo' => 'CA'],
            ['descripcion' => 'LIBRAS', 'simbolo' => 'LBR'],
            ['descripcion' => 'LITRO', 'simbolo' => 'LTR'],
            ['descripcion' => 'MEGAWATT HORA', 'simbolo' => 'MWH'],
            ['descripcion' => 'METRO', 'simbolo' => 'MTR'],
            ['descripcion' => 'METRO CUADRADO', 'simbolo' => 'MTK'],
            ['descripcion' => 'METRO CUBICO', 'simbolo' => 'MTQ'],
            ['descripcion' => 'MILIGRAMOS', 'simbolo' => 'MGM'],
            ['descripcion' => 'MILILITRO', 'simbolo' => 'MLT'],
            ['descripcion' => 'MILIMETRO', 'simbolo' => 'MMT'],
            ['descripcion' => 'MILIMETRO CUADRADO', 'simbolo' => 'MMK'],
            ['descripcion' => 'MILIMETRO CUBICO', 'simbolo' => 'MMQ'],
            ['descripcion' => 'MILLARES', 'simbolo' => 'MIL'],
            ['descripcion' => 'MILLON DE UNIDADES', 'simbolo' => 'UM'],
            ['descripcion' => 'ONZAS', 'simbolo' => 'ONZ'],
            ['descripcion' => 'PALETAS', 'simbolo' => 'PF'],
            ['descripcion' => 'PAQUETE', 'simbolo' => 'PK'],
            ['descripcion' => 'PAR', 'simbolo' => 'PR'],
            ['descripcion' => 'PORCION', 'simbolo' => 'PT'],
            ['descripcion' => 'RESMA', 'simbolo' => 'RM'],
            ['descripcion' => 'ROLLO', 'simbolo' => 'RO'],
            ['descripcion' => 'SACO', 'simbolo' => 'SA'],
            ['descripcion' => 'SET', 'simbolo' => 'ST'],
            ['descripcion' => 'TAMBOR', 'simbolo' => 'TU'],
            ['descripcion' => 'TANQUE', 'simbolo' => 'TK'],
            ['descripcion' => 'TONELADA', 'simbolo' => 'TNE'],
            ['descripcion' => 'TUBOS', 'simbolo' => 'TU'],
            ['descripcion' => 'UNIDAD', 'simbolo' => 'NIU'],
            ['descripcion' => 'YARDA', 'simbolo' => 'YRD'],
            ['descripcion' => 'GRAMO NETO', 'simbolo' => 'GRN'],
            ['descripcion' => 'KILOGRAMO NETO', 'simbolo' => 'KGN'],
            ['descripcion' => 'TONELADA LARGA', 'simbolo' => 'LTON'],
            ['descripcion' => 'TONELADA CORTA', 'simbolo' => 'STON'],
            ['descripcion' => 'PIES CUBICOS', 'simbolo' => 'FTQ'],
            ['descripcion' => 'PIES CUADRADOS', 'simbolo' => 'FTK'],
            ['descripcion' => 'PIE LINEAL', 'simbolo' => 'FT'],
            ['descripcion' => 'MIL', 'simbolo' => 'ML'],
            ['descripcion' => 'KILOMETRO POR HORA', 'simbolo' => 'KMH'],
            ['descripcion' => 'MILLAS POR HORA', 'simbolo' => 'MPH'],
            ['descripcion' => 'MILILITROS POR HORA', 'simbolo' => 'MLH'],
            ['descripcion' => 'MILIGRAMOS POR HORA', 'simbolo' => 'MGH'],
            ['descripcion' => 'MILIMETROS POR HORA', 'simbolo' => 'MMH'],
            ['descripcion' => 'GRAMOS POR HORA', 'simbolo' => 'GRH'],
            ['descripcion' => 'PIES POR HORA', 'simbolo' => 'FTH'],
            ['descripcion' => 'CENTIMETROS POR HORA', 'simbolo' => 'CMH'],
            ['descripcion' => 'METROS POR HORA', 'simbolo' => 'MTH'],
            ['descripcion' => 'YARDAS POR HORA', 'simbolo' => 'YDH'],
            ['descripcion' => 'MILILITROS POR MINUTO', 'simbolo' => 'MLM'],
            ['descripcion' => 'MILIGRAMOS POR MINUTO', 'simbolo' => 'MGM'],
            ['descripcion' => 'GRAMOS POR MINUTO', 'simbolo' => 'GRM'],
            ['descripcion' => 'PIES POR MINUTO', 'simbolo' => 'FTM'],
            ['descripcion' => 'CENTIMETROS POR MINUTO', 'simbolo' => 'CMM'],
            ['descripcion' => 'METROS POR MINUTO', 'simbolo' => 'MTM'],
            ['descripcion' => 'YARDAS POR MINUTO', 'simbolo' => 'YDM'],
            ['descripcion' => 'MILILITROS POR SEGUNDO', 'simbolo' => 'MLS'],
            ['descripcion' => 'MILIGRAMOS POR SEGUNDO', 'simbolo' => 'MGS'],
            ['descripcion' => 'GRAMOS POR SEGUNDO', 'simbolo' => 'GRS'],
            ['descripcion' => 'PIES POR SEGUNDO', 'simbolo' => 'FTS'],
            ['descripcion' => 'CENTIMETROS POR SEGUNDO', 'simbolo' => 'CMS'],
            ['descripcion' => 'METROS POR SEGUNDO', 'simbolo' => 'MTS'],
            ['descripcion' => 'YARDAS POR SEGUNDO', 'simbolo' => 'YDS'],
            ['descripcion' => 'TONELADA MÉTRICA', 'simbolo' => 'T'],
            ['descripcion' => 'UNIDADES POR METRO', 'simbolo' => 'UPM'],
            ['descripcion' => 'UNIDADES POR CENTÍMETRO', 'simbolo' => 'UPC'],
            ['descripcion' => 'UNIDADES POR MILÍMETRO', 'simbolo' => 'UPMM'],
            ['descripcion' => 'UNIDADES POR GRAMO', 'simbolo' => 'UPG'],
            ['descripcion' => 'UNIDADES POR KILOGRAMO', 'simbolo' => 'UPKG'],
            ['descripcion' => 'UNIDADES POR LITRO', 'simbolo' => 'UPL']
        ];

        foreach ($unidades_medida as $unidad_medida) {
            $item                   = new GeneralTableDetail();
            $item->name             = $unidad_medida['descripcion'];
            $item->description      = $unidad_medida['descripcion'];
            $item->symbol           = $unidad_medida['simbolo'];
            $item->parameter        = $unidad_medida['simbolo'];
            $item->general_table_id = $tabla_general_unidades_medida->id;
            $item->save();
        }
    }
}
