<?php

namespace App\Exports\Tenant;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportFieldExport implements FromCollection,ShouldAutoSize,WithStyles
{
    protected $data;
    protected $filters;

    public function __construct($data,$filters)
    {
        $this->data     =   $data;
        $this->filters  =   $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $company    =   Company::find(1);

        $info   =   ['EMPRESA:',$company->business_name];
        $info2  =   ['RUC:',$company->ruc];
        $info3  =   ['FECHA IMPRESIÓN:',Carbon::now()];
        $info4  =   ['USUARIO IMPRESIÓN:',Auth::user()->name];
        $info5  =   ['FECHA INICIO:',$this->filters->get('date_start')];
        $info6  =   ['FECHA FIN:',$this->filters->get('date_end')];
        $space  =   [''];

        $headers = [
            'FECHA REGISTRO', 
            'FECHA RESERVA', 
            'CAMPO', 
            'CLIENTE',
            'HORA',
            'IMPORTE',
            'ESTADO'
        ];

        $this->data =   collect([$headers])->merge($this->data);
        $this->data =   collect([$space])->merge($this->data);
        $this->data =   collect([$info6])->merge($this->data);
        $this->data =   collect([$info5])->merge($this->data);
        $this->data =   collect([$info4])->merge($this->data);
        $this->data =   collect([$info3])->merge($this->data);
        $this->data =   collect([$info2])->merge($this->data);
        $this->data =   collect([$info])->merge($this->data);

        return $this->data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A1' => ['font' => ['bold' => true]], 
            'A2' => ['font' => ['bold' => true]], 
            'A3' => ['font' => ['bold' => true]], 
            'A4' => ['font' => ['bold' => true]], 
            'A5' => ['font' => ['bold' => true]], 
            'A6' => ['font' => ['bold' => true]], 
    
            'A8:G8' => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID, 
                    'startColor' => ['argb' => 'D3D3D3'] 
                ]
            ],
        ];
    }
}
