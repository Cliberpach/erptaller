<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [

            // 🇯🇵 JAPÓN
            'Toyota',
            'Lexus',
            'Daihatsu',
            'Hino',
            'Honda',
            'Acura',
            'Nissan',
            'Infiniti',
            'Mazda',
            'Subaru',
            'Mitsubishi Motors',
            'Suzuki',
            'Isuzu',

            // 🇺🇸 ESTADOS UNIDOS
            'Ford',
            'Lincoln',
            'Chevrolet',
            'GMC',
            'Cadillac',
            'Buick',
            'Dodge',
            'Chrysler',
            'Jeep',
            'Ram',
            'Tesla',
            'Rivian',
            'Lucid Motors',
            'Hummer (GM)',
            'Karma Automotive',

            // 🇩🇪 ALEMANIA
            'Volkswagen',
            'Audi',
            'Porsche',
            'Bentley',
            'Lamborghini',
            'Bugatti', // VAG
            'Mercedes-Benz',
            'Maybach',
            'Smart',
            'BMW',
            'Mini',
            'Rolls-Royce',
            'Opel',

            // 🇰🇷 COREA DEL SUR
            'Hyundai',
            'Kia',
            'Genesis',
            'Samsung Motors (Renault Korea)',

            // 🇫🇷 FRANCIA
            'Renault',
            'Dacia',
            'Alpine',
            'Peugeot',
            'Citroën',
            'DS Automobiles',

            // 🇮🇹 ITALIA
            'Fiat',
            'Abarth',
            'Alfa Romeo',
            'Ferrari',
            'Maserati',
            'Lamborghini',
            'Iveco',
            'Pagani',
            'DR Automobiles',

            // 🇬🇧 REINO UNIDO
            'Aston Martin',
            'Bentley',
            'Rolls-Royce',
            'Jaguar',
            'Land Rover',
            'McLaren',
            'Lotus',
            'Vauxhall',

            // 🇸🇪 SUECIA
            'Volvo',
            'Polestar',
            'Koenigsegg',
            'Scania',

            // 🇨🇳 CHINA (marcas activas globalmente)
            'Geely',
            'BYD',
            'MG (SAIC)',
            'Great Wall Motors',
            'Haval',
            'Changan',
            'Chery',
            'Jetour',
            'Omoda',
            'Exeed',
            'Lynk & Co',
            'NIO',
            'XPeng',
            'Li Auto',
            'Wuling',
            'Hongqi',
            'Zeekr',
            'Leapmotor',
            'Borgward (Foton)',
            'Yutong',
            'Foton',

            // 🇮🇳 INDIA
            'Tata Motors',
            'Mahindra',
            'Ashok Leyland',
            'Force Motors',
            'Maruti Suzuki',

            // 🇪🇸 ESPAÑA
            'SEAT',
            'Cupra',

            // 🇷🇺 RUSIA
            'Lada',
            'GAZ',
            'UAZ',

            // 🇹🇷 TURQUÍA
            'TOGG',

            // 🇨🇿 REPÚBLICA CHECA
            'Škoda',

            // 🇺🇦 UCRANIA
            'ZAZ',

            // 🇧🇷 BRASIL
            'Agrale',

            // 🇦🇷 ARGENTINA
            'Ika Torino (solo si quieres actuales, esta se omite)',
            // (NO incluida por ser histórica)

            // 🇲🇽 MÉXICO
            'Mastretta',

            // 🇮🇩 INDONESIA
            'Wuling Indonesia',
            'Esemka',

            // 🇹🇭 TAILANDIA
            'Thai Rung',

            // MARCAS DE MOTOS (si también manejas vehículos en general)
            'Yamaha',
            'Kawasaki',
            'KTM',
            'Harley-Davidson',
            'Ducati',
            'Triumph',
            'Royal Enfield',

            // CAMIONES Y COMERCIALES
            'Freightliner',
            'Kenworth',
            'Peterbilt',
            'Mack',
            'International',
            'Volvo Trucks',
            'DAF',
            'MAN',
            'Iveco',
            'Hino',
            'Fuso',

        ];

        foreach ($brands as $brand) {
            Brand::create([
                'description' => mb_strtoupper(trim($brand), 'UTF-8'),
                'status' => 'ACTIVE',
            ]);
        }
    }
}
