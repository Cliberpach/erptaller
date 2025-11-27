<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\Brand;
use App\Models\Landlord\ModelV;
use Illuminate\Database\Seeder;

class ModelSeeder extends Seeder
{
    public function run(): void
    {
        $data = [

            // 🇯🇵 Toyota
            'Toyota' => [
                'Corolla',
                'Hilux',
                'RAV4',
                'Yaris',
                'Camry',
                'Prado',
                'Land Cruiser',
                'Avanza',
                'Fortuner',
                'Tacoma',
            ],

            // 🇰🇷 Hyundai
            'Hyundai' => [
                'Elantra',
                'Tucson',
                'Santa Fe',
                'Accent',
                'Kona',
                'Creta',
                'Palisade',
                'i20',
                'i30',
                'Venue',
            ],

            // 🇰🇷 Kia
            'Kia' => [
                'Sportage',
                'Sorento',
                'Rio',
                'Cerato (Forte)',
                'Seltos',
                'Soul',
                'Picanto',
                'Carens',
                'Telluride',
                'Stonic',
            ],

            // 🇺🇸 Chevrolet
            'Chevrolet' => [
                'Silverado',
                'Onix',
                'Tracker',
                'Traverse',
                'Tahoe',
                'Camaro',
                'Malibu',
                'Equinox',
                'Colorado',
                'Spark',
            ],

            // 🇯🇵 Nissan
            'Nissan' => [
                'Sentra',
                'Versa',
                'Frontier',
                'Kicks',
                'X-Trail',
                'Pathfinder',
                'Qashqai',
                'Altima',
                'Murano',
                'Titan',
            ],

            // 🇯🇵 Honda
            'Honda' => [
                'Civic',
                'CR-V',
                'Pilot',
                'Accord',
                'HR-V',
                'Fit',
                'Ridgeline',
                'Odyssey',
                'City',
                'Passport',
            ],

            // 🇯🇵 Mazda
            'Mazda' => [
                'Mazda 3',
                'Mazda 6',
                'CX-3',
                'CX-5',
                'CX-30',
                'CX-9',
                'BT-50',
                'MX-5 Miata',
                'Mazda2',
                'CX-50',
            ],

            // 🇯🇵 Suzuki
            'Suzuki' => [
                'Swift',
                'Vitara',
                'Jimny',
                'Baleno',
                'S-Cross',
                'Ertiga',
                'Celerio',
                'Alto',
                'Ignis',
                'XL7',
            ],

            // 🇯🇵 Mitsubishi
            'Mitsubishi Motors' => [
                'Lancer',
                'Outlander',
                'Montero Sport',
                'ASX',
                'Mirage',
                'Eclipse Cross',
                'XPander',
                'Triton',
                'Pajero',
                'Galant',
            ],

            // 🇩🇪 Volkswagen
            'Volkswagen' => [
                'Golf',
                'Tiguan',
                'Polo',
                'Jetta',
                'Passat',
                'T-Cross',
                'T-Roc',
                'Amarok',
                'Atlas',
                'ID.4',
            ],

            // 🇫🇷 Renault
            'Renault' => [
                'Koleos',
                'Duster',
                'Stepway',
                'Logan',
                'Megane',
                'Clio',
                'Captur',
                'Arkana',
                'Talisman',
                'Kwid',
            ],

            // 🇯🇵 Subaru
            'Subaru' => [
                'Forester',
                'Outback',
                'Impreza',
                'XV',
                'Ascent',
                'Legacy',
                'BRZ',
                'Levorg',
                'Crosstrek',
                'WRX',
            ],

            // 🇩🇪 BMW
            'BMW' => [
                'Serie 3',
                'Serie 5',
                'Serie 1',
                'X1',
                'X3',
                'X5',
                'X6',
                'M3',
                'i3',
                'iX',
            ],

            // 🇩🇪 Mercedes-Benz
            'Mercedes-Benz' => [
                'Clase C',
                'Clase E',
                'Clase A',
                'Clase GLA',
                'Clase GLC',
                'Clase GLE',
                'Clase S',
                'GLB',
                'GLS',
                'CLA',
            ],

            // 🇩🇪 Audi
            'Audi' => [
                'A3',
                'A4',
                'A6',
                'Q3',
                'Q5',
                'Q7',
                'TT',
                'A1',
                'Q2',
                'e-tron',
            ],

            // 🇮🇳 Tata Motors
            'Tata Motors' => [
                'Nexon',
                'Harrier',
                'Safari',
                'Altroz',
                'Punch',
                'Tiago',
                'Tigor',
                'Hexa',
                'Zest',
                'Bolt',
            ],

            // 🇸🇪 Volvo
            'Volvo' => [
                'XC40',
                'XC60',
                'XC90',
                'S60',
                'S90',
                'V40',
                'V60',
                'C40 Recharge',
                'EX30',
                'EX90',
            ],

            // 🇨🇳 BYD
            'BYD' => [
                'Song Plus',
                'Tang',
                'Han',
                'Dolphin',
                'Atto 3',
                'Seagull',
                'Yuan Plus',
                'e2',
                'Destroyer 05',
                'Seal',
            ],

            // 🇨🇳 Changan
            'Changan' => [
                'CS35 Plus',
                'CS55 Plus',
                'CS75 Plus',
                'UNI-T',
                'UNI-K',
                'UNI-V',
                'Alsvin',
                'Eado',
                'Lamore',
                'Benni',
            ],
        ];

        foreach ($data as $brandName => $models) {

            $brand = Brand::where('description', $brandName)->first();

            if (!$brand) {
                continue; // por si hay marcas que no existen en tu tabla
            }

            foreach ($models as $model) {
                ModelV::create([
                    'brand_id'   => $brand->id,
                    'description' => mb_strtoupper(trim($model), 'UTF-8'),
                ]);
            }
        }
    }
}
