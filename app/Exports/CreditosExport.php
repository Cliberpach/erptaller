<?php

namespace App\Exports;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CreditosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $filters;
    protected $credits;
    protected $company;

    public function __construct($filters)
    {
        $this->filters = $filters;
        $this->company = Company::first();
    }

    public function collection()
    {
        $estado = $this->filters['search_estado'] ?? 'PENDIENTE';

        $query = DB::table('credits')
            ->select(
                'customer_name',
                'customer_document_number',
                'customer_phone',
                'field_name',
                'start_time',
                'end_time',
                'date',
                'total_hours',
                'ball',
                'vest',
                'dni',
                'ruc_number',
                'razon_social',
                'amount'
            )
            ->where('estado', $estado);

        if ($this->filters['search_type'] === 'dni') {
            $query->where('customer_document_number', $this->filters['search_input']);
        } elseif ($this->filters['search_type'] === 'ruc') {
            $query->where('ruc_number', $this->filters['search_input']);
        } elseif ($this->filters['search_type'] === 'nombre') {
            $query->where(function ($q) {
                $q->where('customer_name', 'like', '%' . $this->filters['search_input'] . '%')
                    ->orWhere('razon_social', 'like', '%' . $this->filters['search_input'] . '%');
            });
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('date', [$this->filters['start_date'], $this->filters['end_date']]);
        }

        $this->credits = $query->get();

        return $this->credits;
    }

    public function map($credit): array
    {
        $cliente = $this->filters['search_type'] === 'ruc' ? $credit->razon_social : $credit->customer_name;
        $documento = $this->filters['search_type'] === 'ruc' ? $credit->ruc_number : $credit->customer_document_number;

        return [
            $cliente,
            $documento,
            $credit->customer_phone,
            $credit->field_name,
            "{$credit->start_time} - {$credit->end_time}",
            \Carbon\Carbon::parse($credit->date)->format('d/m/Y'),
            $credit->total_hours,
            $credit->ball ? 'S\u00ed' : 'No',
            $credit->vest ? 'S\u00ed' : 'No',
            $credit->dni ? 'S\u00ed' : 'No',
            number_format($credit->amount, 2),
        ];
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'DNI / RUC',
            'Tel\u00e9fono',
            'Campo',
            'Horario',
            'Fecha',
            'Horas',
            'Pelota',
            'Chaleco',
            'DNI',
            'Monto (S/)',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 7);
                $sheet->mergeCells('A1:K1');
                $sheet->mergeCells('A2:K2');
                $sheet->mergeCells('A3:K3');
                $sheet->mergeCells('A4:K4');
                $sheet->mergeCells('A5:K5');
                $sheet->mergeCells('A6:K6');
                $sheet->mergeCells('A7:K7');

                $sheet->setCellValue('A1', $this->company->business_name ?? 'Empresa');
                $sheet->setCellValue('A2', 'RUC: ' . ($this->company->ruc ?? ''));
                $sheet->setCellValue('A3', 'Dirección: ' . ($this->company->fiscal_address ?? ''));
                $sheet->setCellValue('A4', 'Teléfono: ' . ($this->company->phone ?? ''));
                $sheet->setCellValue('A5', 'Email: ' . ($this->company->email ?? ''));
                $sheet->setCellValue('A6', 'Fecha de Reporte: ' . now()->format('d/m/Y'));

                $estado = strtoupper($this->filters['search_estado'] ?? 'PENDIENTE');
                $titulo = $estado === 'PAGADO' ? 'CRÉDITOS FACTURADOS' : 'CRÉDITOS PENDIENTES';
                $sheet->setCellValue('A7', $titulo);

                $styleHeader = [
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '3a6ea5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];

                for ($i = 1; $i <= 7; $i++) {
                    $sheet->getStyle("A{$i}")->applyFromArray($styleHeader);
                }

                $headerStyle = [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'f2f2f2'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ];

                $sheet->getStyle('A8:K8')->applyFromArray($headerStyle);
            },
        ];
    }
}
