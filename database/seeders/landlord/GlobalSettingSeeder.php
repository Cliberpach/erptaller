<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\GlobalSetting;
use Illuminate\Database\Seeder;

class GlobalSettingSeeder extends Seeder
{
    public function run(): void
    {
        GlobalSetting::setValor('api_placa_url', 'https://multijc.com/api/queryplaca/');
        GlobalSetting::setValor('api_placa_token', 'nsHeEpNSOBr8ucEFnL7OtKmVkZhefUuvoM8O1Lz7uFEOi4KtFZ54==');
        GlobalSetting::setValor('api_placa_bearer', 'nsHeEpNSOBr8ucEFnL7OtKmVkZhefUuvoM8O1Lz7uFEOi4KtFZ54==');
    }
}
