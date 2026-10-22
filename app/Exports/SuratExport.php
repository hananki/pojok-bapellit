<?php

namespace App\Exports;

use App\Models\Surat;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SuratExport implements FromQuery, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public function startCell(): string
    {
        return 'A4';
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event): void {
                $sheet = $event->getSheet()->getDelegate();
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A1', 'BUKU AGENDA SURAT KELUAR/MASUK');
                $sheet->setCellValue('A2', 'Tanggal Export: '.now()->format('d-m-Y H:i'));
            },
        ];
    }

    public function query(): EloquentBuilder|QueryBuilder|Relation
    {
        return Surat::query()->orderBy('no_indek')->orderBy('tanggal')->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'No Surat', // Ganti dari 'No. Urut' menjadi 'No Surat'
            'Tanggal',
            'No. / Indeks',
            'Alamat Yang Dituju',
            'Isi Surat',
            'Banyak Lampiran',
            'Sifat Surat',
            'Keterangan',
        ];
    }

    public function map($surat): array
    {
        return [
            $surat->no_surat ?? '-', // Mengambil langsung dari kolom no_surat
            $surat->tanggal?->format('d-m-Y') ?? $surat->created_at?->format('d-m-Y') ?? '-',
            $surat->no_indek ?? '-', 
            $surat->alamat_tujuan ?? '-',
            $surat->isi_surat ?? '-',
            $surat->banyak_lampiran ?? 0,
            $surat->sifat_surat ?? '-',
            $surat->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        $lastRow = max($sheet->getHighestRow(), 4);

        $sheet->getStyle("A1:H{$lastRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true)
            ->setShrinkToFit(false);
        $sheet->getStyle("A4:H{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F2937']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '6B7280']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '0d9488']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(32);

        for ($row = 5; $row <= $lastRow; $row++) {
            $contentLength = max(
                mb_strlen((string) $sheet->getCell("D{$row}")->getValue()),
                mb_strlen((string) $sheet->getCell("E{$row}")->getValue()),
                mb_strlen((string) $sheet->getCell("H{$row}")->getValue())
            );
            $estimatedLines = max(1, (int) ceil($contentLength / 42));
            // Hapus batas min(90) agar teks panjang tidak terpotong
            $sheet->getRowDimension($row)->setRowHeight(max(24, $estimatedLines * 15));
        }

        $widths = [
            'A' => 15, // Sedikit dilebarkan untuk No Surat
            'B' => 15, 
            'C' => 24, 
            'D' => 28,
            'E' => 42, 
            'F' => 18, 
            'G' => 16, 
            'H' => 28,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getPageSetup()->setOrientation('landscape');
        $sheet->getPageSetup()->setPaperSize(9);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.3);
        $sheet->getPageMargins()->setRight(0.3);
        $sheet->getPageMargins()->setBottom(0.3);
        $sheet->getPageMargins()->setLeft(0.3);

        return [];
    }
}