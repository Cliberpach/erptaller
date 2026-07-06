<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\GlobalSetting;
use Illuminate\Database\Seeder;

class GlobalSettingSeeder extends Seeder
{
    public function run(): void
    {
        GlobalSetting::setValor('api_placa_url', 'https://multijc.com/api/queryplaca/');
        GlobalSetting::setValor('api_placa_token', 'c36358c49922c564f035d4dc2ff3492fbcfd31ee561866960f75b79f7d645d7d');
        GlobalSetting::setValor('api_placa_bearer', 'c36358c49922c564f035d4dc2ff3492fbcfd31ee561866960f75b79f7d645d7d');
    }
}
