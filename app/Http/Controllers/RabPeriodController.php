<?php

namespace App\Http\Controllers;

use App\Models\RabPeriod;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RabPeriodController extends Controller
{
    private function currentRegionId(): int
    {
        $id = Region::where('is_active', true)->orderBy('id')->value('id')
            ?? Region::orderBy('id')->value('id');
        return (int) $id;
    }

    public function index()
    {
        $regionId = $this->currentRegionId();
        $periods = RabPeriod::where('region_id', $regionId)
            ->withCount('days')
            ->orderByDesc('start_date')
            ->paginate(20);

        return view('rab-periods.index', compact('periods'));
    }

    public function create()
    {
        return view('rab-periods.create');
    }

    public function store(Request $request)
    {
        $regionId = $this->currentRegionId();

        $data = $request->validate([
            'name'       => 'required|string|max:191',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'pk_price'   => 'required|integer|min:0',
            'pb_price'   => 'required|integer|min:0',
            'status'     => 'required|in:draft,confirmed,locked',
            'notes'      => 'nullable|string',
        ]);

        $data['region_id'] = $regionId;
        $period = RabPeriod::create($data);

        return redirect()->route('rab-periods.show', $period)
            ->with('success', 'RAB Period created. Now fill in each day\'s student counts.');
    }

    public function show(RabPeriod $rabPeriod)
    {
        $rabPeriod->load(['days' => function ($q) {
            $q->with(['menus' => function ($q) {
                $q->with(['items', 'replacements']);
            }]);
        }]);

        $dayStats = $rabPeriod->days->map(function ($day) use ($rabPeriod) {
            $day->setRelation('period', $rabPeriod);
            foreach ($day->menus as $menu) {
                $menu->setRelation('day', $day);
                foreach ($menu->replacements as $rep) {
                    $rep->setRelation('day', $day);
                }
            }
            return [
                'day'      => $day,
                'budget'   => $day->budget(),
                'rfc'      => $day->rfc(),
                'surplus'  => $day->surplus(),
            ];
        });

        $totalBudget = $dayStats->sum('budget');
        $totalRfc    = $dayStats->sum('rfc');
        $netSurplus  = $dayStats->sum('surplus');

        return view('rab-periods.show', compact('rabPeriod', 'dayStats', 'totalBudget', 'totalRfc', 'netSurplus'));
    }

    public function edit(RabPeriod $rabPeriod)
    {
        return view('rab-periods.edit', compact('rabPeriod'));
    }

    public function update(Request $request, RabPeriod $rabPeriod)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:191',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'pk_price'   => 'required|integer|min:0',
            'pb_price'   => 'required|integer|min:0',
            'status'     => 'required|in:draft,confirmed,locked',
            'notes'      => 'nullable|string',
        ]);

        $rabPeriod->update($data);

        return redirect()->route('rab-periods.show', $rabPeriod)
            ->with('success', 'RAB Period updated.');
    }

    public function destroy(RabPeriod $rabPeriod)
    {
        $rabPeriod->delete();
        return redirect()->route('rab-periods.index')
            ->with('success', 'RAB Period deleted.');
    }

    public function report(RabPeriod $rabPeriod)
    {
        $rabPeriod->load([
            'days' => function ($q) {
                $q->orderBy('day_date')
                  ->with(['menus' => function ($q) {
                      $q->orderBy('sort_order')->orderBy('id')
                        ->with(['menu', 'items.product', 'items.unit', 'items.supplier', 'replacements']);
                  }]);
            },
        ]);

        foreach ($rabPeriod->days as $day) {
            $day->setRelation('period', $rabPeriod);
            foreach ($day->menus as $menu) {
                $menu->setRelation('day', $day);
                foreach ($menu->replacements as $rep) {
                    $rep->setRelation('day', $day);
                }
            }
        }

        $dayStats = $rabPeriod->days->map(fn($day) => [
            'day'     => $day,
            'budget'  => $day->budget(),
            'rfc'     => $day->rfc(),
            'surplus' => $day->surplus(),
        ]);

        $totalBudget = $dayStats->sum('budget');
        $totalRfc    = $dayStats->sum('rfc');
        $netSurplus  = $dayStats->sum('surplus');

        return view('rab-periods.report', compact('rabPeriod', 'dayStats', 'totalBudget', 'totalRfc', 'netSurplus'));
    }

    public function export(Request $request, RabPeriod $rabPeriod)
    {
        $rabPeriod->load([
            'days' => function ($q) {
                $q->orderBy('day_date')
                  ->with(['menus' => function ($q) {
                      $q->orderBy('sort_order')->orderBy('id')
                        ->with(['menu', 'items.product', 'items.unit', 'items.supplier', 'replacements']);
                  }]);
            },
        ]);

        foreach ($rabPeriod->days as $day) {
            $day->setRelation('period', $rabPeriod);
            foreach ($day->menus as $menu) {
                $menu->setRelation('day', $day);
                foreach ($menu->replacements as $rep) {
                    $rep->setRelation('day', $day);
                }
            }
        }

        if ($request->query('format') === 'csv') {
            return $this->exportCsv($rabPeriod);
        }

        if ($request->query('format') === 'xlsx') {
            return $this->exportXlsx($rabPeriod);
        }

        return $this->exportJson($rabPeriod);
    }

    private function exportJson(RabPeriod $rabPeriod): \Illuminate\Http\JsonResponse
    {
        $days = $rabPeriod->days->map(function ($day) {
            $rfc     = $day->rfc();
            $surplus = $day->surplus();

            $menus = $day->menus->map(function ($menu) {
                $effPk   = $menu->effectivePkCount();
                $effPb   = $menu->effectivePbCount();
                $menuRfc = $menu->totalCost();

                return [
                    'menu_name'      => $menu->menu->name,
                    'category'       => $menu->category,
                    'is_replacement' => (bool) $menu->is_replacement,
                    'effective_pk'   => $effPk,
                    'effective_pb'   => $effPb,
                    'rfc'            => round($menuRfc, 2),
                    'items'          => $menu->items->map(function ($item) use ($effPk, $effPb) {
                        return [
                            'product'        => $item->product->name,
                            'unit'           => $item->unit->name,
                            'supplier'       => $item->supplier?->name,
                            'pk_gramasi'     => (float) $item->pk_gramasi,
                            'pb_gramasi'     => (float) $item->pb_gramasi,
                            'purchase_price' => (float) $item->purchase_price,
                            'rfc'            => round($item->costFor($effPk, $effPb), 2),
                        ];
                    }),
                ];
            });

            return [
                'date'        => $day->day_date->format('Y-m-d'),
                'day_of_week' => $day->day_date->format('l'),
                'pk_count'    => $day->pk_count,
                'pb_count'    => $day->pb_count,
                'budget'      => $day->budget(),
                'realisasi'   => (float) $day->realisasi,
                'rfc'         => round($rfc, 2),
                'sisa'        => round($surplus, 2),
                'menus'       => $menus,
            ];
        });

        $totalBudget    = $days->sum('budget');
        $totalRfc       = $days->sum('rfc');
        $totalRealisasi = $days->sum('realisasi');
        $netSisa        = $days->sum('sisa');

        return response()->json([
            'period' => [
                'id'         => $rabPeriod->id,
                'name'       => $rabPeriod->name,
                'start_date' => $rabPeriod->start_date->format('Y-m-d'),
                'end_date'   => $rabPeriod->end_date->format('Y-m-d'),
                'pk_price'   => $rabPeriod->pk_price,
                'pb_price'   => $rabPeriod->pb_price,
                'status'     => $rabPeriod->status,
            ],
            'summary' => [
                'total_budget'    => $totalBudget,
                'total_rfc'       => round($totalRfc, 2),
                'total_realisasi' => round($totalRealisasi, 2),
                'net_sisa'        => round($netSisa, 2),
            ],
            'days' => $days,
        ]);
    }

    private function exportXlsx(RabPeriod $rabPeriod): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $dayNames = [
            0 => 'MINGGU', 1 => 'SENIN',  2 => 'SELASA', 3 => 'RABU',
            4 => 'KAMIS',  5 => 'JUMAT',  6 => 'SABTU',
        ];
        $monthNames = [
            1 => 'Januari',   2 => 'Februari', 3 => 'Maret',    4 => 'April',
            5 => 'Mei',       6 => 'Juni',      7 => 'Juli',     8 => 'Agustus',
            9 => 'September', 10 => 'Oktober',  11 => 'November', 12 => 'Desember',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('RAB');

        $headers = [
            'HARI', 'JENIS BAHAN', 'MENU', 'BAHAN', 'SATUAN',
            'GRAMASI PK (g)', 'GRAMASI PB (g)', 'HARGA SATUAN (Rp)',
            'JML PK', 'JML PB',
            'RFC PK (Rp)', 'RFC PB (Rp)',
            'KEBUTUHAN PK (g)', 'KEBUTUHAN PB (g)', 'TOTAL KEBUTUHAN (g)', 'ROUND (kg)',
            'TOTAL HARGA (Rp)', 'RAB (Rp)', 'NOTA',
        ];
        $colCount = count($headers); // 19
        $lastCol  = Coordinate::stringFromColumnIndex($colCount);

        // Row 1 — period title
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->setCellValue('A1',
            $rabPeriod->name
            . '   |   ' . $rabPeriod->start_date->format('d M Y') . ' s/d ' . $rabPeriod->end_date->format('d M Y')
            . '   |   PK: Rp ' . number_format($rabPeriod->pk_price)
            . '   |   PB: Rp ' . number_format($rabPeriod->pb_price)
        );
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Row 2 — column headers
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '2', $h);
        }
        $sheet->getStyle('A2:' . $lastCol . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E75B6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBDD7EE']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(36);

        $rpFmt = '"Rp "#,##0.00';
        $numFmt = '#,##0.00';
        $currentRow = 3;
        $periodTotalRfc    = 0;
        $periodTotalBudget = 0;

        foreach ($rabPeriod->days as $day) {
            $date        = $day->day_date;
            $idDay       = $dayNames[$date->dayOfWeek];
            $idDate      = $date->day . ' ' . $monthNames[$date->month] . ' ' . $date->year;
            $budget      = $day->budget();
            $dayRfc      = $day->rfc();
            $daySisa     = $day->surplus();
            $dayStartRow = $currentRow;
            $hasRows     = false;

            foreach ($day->menus as $menu) {
                $effPk    = $menu->effectivePkCount();
                $effPb    = $menu->effectivePbCount();
                $category = strtoupper($menu->is_replacement ? 'alergen' : $menu->category);

                if ($menu->items->isEmpty()) {
                    $sheet->setCellValue('B' . $currentRow, $category);
                    $sheet->setCellValue('C' . $currentRow, $menu->menu->name);
                    $sheet->setCellValue('I' . $currentRow, $effPk);
                    $sheet->setCellValue('J' . $currentRow, $effPb);
                    $sheet->setCellValue('R' . $currentRow, $budget);
                    $this->styleItemRow($sheet, $currentRow, $lastCol, $rpFmt);
                    $currentRow++;
                    $hasRows = true;
                    continue;
                }

                foreach ($menu->items as $item) {
                    $price          = (float) $item->purchase_price;
                    $pkG            = (float) $item->pk_gramasi;
                    $pbG            = (float) $item->pb_gramasi;
                    $rfcPk          = $price > 0 ? round($pkG / 1000 * $price, 4) : 0;
                    $rfcPb          = $price > 0 ? round($pbG / 1000 * $price, 4) : 0;
                    $kebutuhanPk    = round($pkG * $effPk, 2);
                    $kebutuhanPb    = round($pbG * $effPb, 2);
                    $totalKebutuhan = $kebutuhanPk + $kebutuhanPb;
                    $roundKg        = round($totalKebutuhan / 1000, 2);
                    $totalHarga     = round($item->costFor($effPk, $effPb), 2);

                    $sheet->setCellValue('B' . $currentRow, $category);
                    $sheet->setCellValue('C' . $currentRow, $menu->menu->name);
                    $sheet->setCellValue('D' . $currentRow, $item->product->name);
                    $sheet->setCellValue('E' . $currentRow, $item->unit->name);
                    $sheet->setCellValue('F' . $currentRow, $pkG);
                    $sheet->setCellValue('G' . $currentRow, $pbG);
                    $sheet->setCellValue('H' . $currentRow, $price);
                    $sheet->setCellValue('I' . $currentRow, $effPk);
                    $sheet->setCellValue('J' . $currentRow, $effPb);
                    $sheet->setCellValue('K' . $currentRow, $rfcPk);
                    $sheet->setCellValue('L' . $currentRow, $rfcPb);
                    $sheet->setCellValue('M' . $currentRow, $kebutuhanPk);
                    $sheet->setCellValue('N' . $currentRow, $kebutuhanPb);
                    $sheet->setCellValue('O' . $currentRow, $totalKebutuhan);
                    $sheet->setCellValue('P' . $currentRow, $roundKg);
                    $sheet->setCellValue('Q' . $currentRow, $totalHarga);
                    $sheet->setCellValue('R' . $currentRow, $budget);
                    $sheet->setCellValue('S' . $currentRow, $item->supplier?->name ?? '');

                    $sheet->getStyle('H' . $currentRow)->getNumberFormat()->setFormatCode($rpFmt);
                    $sheet->getStyle('K' . $currentRow)->getNumberFormat()->setFormatCode($rpFmt);
                    $sheet->getStyle('L' . $currentRow)->getNumberFormat()->setFormatCode($rpFmt);
                    $sheet->getStyle('Q' . $currentRow)->getNumberFormat()->setFormatCode($rpFmt);
                    $sheet->getStyle('R' . $currentRow)->getNumberFormat()->setFormatCode($rpFmt);
                    $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle('M' . $currentRow . ':O' . $currentRow)->getNumberFormat()->setFormatCode($numFmt);
                    $this->styleItemRow($sheet, $currentRow, $lastCol, $rpFmt);

                    $currentRow++;
                    $hasRows = true;
                }
            }

            // Ensure at least one row exists for days with no menus
            if (!$hasRows) {
                $sheet->setCellValue('I' . $currentRow, $day->pk_count);
                $sheet->setCellValue('J' . $currentRow, $day->pb_count);
                $sheet->setCellValue('R' . $currentRow, $budget);
                $this->styleItemRow($sheet, $currentRow, $lastCol, $rpFmt);
                $currentRow++;
            }

            // Merge HARI column vertically across all ingredient rows
            $dayEndRow = $currentRow - 1;
            if ($dayEndRow > $dayStartRow) {
                $sheet->mergeCells('A' . $dayStartRow . ':A' . $dayEndRow);
            }
            $sheet->setCellValue('A' . $dayStartRow, $idDay . "\n" . $idDate);
            $sheet->getStyle('A' . $dayStartRow . ':A' . $dayEndRow)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E75B6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);

            // Day total row
            $sheet->mergeCells('A' . $currentRow . ':P' . $currentRow);
            $sheet->setCellValue('A' . $currentRow, 'TOTAL ' . $idDay);
            $sheet->setCellValue('Q' . $currentRow, round($dayRfc, 2));
            $sheet->setCellValue('R' . $currentRow, $budget);
            $sheet->setCellValue('S' . $currentRow, 'SISA: Rp ' . number_format(round($daySisa, 0)));
            $sheet->getStyle('A' . $currentRow . ':S' . $currentRow)->applyFromArray([
                'font'      => ['bold' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBDD7EE']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
            $sheet->getStyle('Q' . $currentRow)->getNumberFormat()->setFormatCode($rpFmt);
            $sheet->getStyle('R' . $currentRow)->getNumberFormat()->setFormatCode($rpFmt);

            $currentRow += 2; // total row + blank separator
            $periodTotalRfc    += $dayRfc;
            $periodTotalBudget += $budget;
        }

        // Period summary row
        $netSisa = $periodTotalBudget - $periodTotalRfc;
        $sheet->mergeCells('A' . $currentRow . ':P' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, 'TOTAL PERIODE: ' . $rabPeriod->name);
        $sheet->setCellValue('Q' . $currentRow, round($periodTotalRfc, 2));
        $sheet->setCellValue('R' . $currentRow, $periodTotalBudget);
        $sheet->setCellValue('S' . $currentRow, 'Net SISA: Rp ' . number_format(round($netSisa, 0)));
        $sheet->getStyle('A' . $currentRow . ':S' . $currentRow)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF4472C4']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getStyle('Q' . $currentRow)->getNumberFormat()->setFormatCode($rpFmt);
        $sheet->getStyle('R' . $currentRow)->getNumberFormat()->setFormatCode($rpFmt);

        // Column widths
        foreach ([
            'A' => 18, 'B' => 12, 'C' => 20, 'D' => 22, 'E' => 12,
            'F' => 12, 'G' => 12, 'H' => 16, 'I' => 10, 'J' => 10,
            'K' => 16, 'L' => 16, 'M' => 18, 'N' => 18, 'O' => 18,
            'P' => 12, 'Q' => 18, 'R' => 18, 'S' => 22,
        ] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane('B3');

        $filename = 'rab-' . Str::slug($rabPeriod->name) . '-' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new XlsxWriter($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
            'Content-Disposition' => 'attachment',
        ]);
    }

    private function styleItemRow($sheet, int $row, string $lastCol, string $rpFmt): void
    {
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBDD7EE']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    private function exportCsv(RabPeriod $rabPeriod): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'rab-' . Str::slug($rabPeriod->name) . '-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rabPeriod) {
            $out = fopen('php://output', 'w');

            $dayNames = [
                0 => 'MINGGU', 1 => 'SENIN',  2 => 'SELASA', 3 => 'RABU',
                4 => 'KAMIS',  5 => 'JUMAT',  6 => 'SABTU',
            ];
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',    4 => 'April',
                5 => 'Mei',     6 => 'Juni',      7 => 'Juli',     8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $blank = fn() => array_fill(0, 18, '');

            fputcsv($out, [
                'Tanggal', 'Kategori', 'Menu', 'Bahan',
                'Gramasi PK (g)', 'Gramasi PB (g)',
                'Kalkulasi Harga PK (Rp)', 'Kalkulasi Harga PB (Rp)',
                'Harga Satuan (Rp)',
                'Jumlah PM PK', 'Jumlah PM PB',
                'Kebutuhan PK (g)', 'Kebutuhan PB (g)',
                'Total Kebutuhan (g)', 'Round (kg)',
                'Total Harga (Rp)', 'RAB (Rp)', 'Supplier',
            ]);

            $periodTotalRfc    = 0;
            $periodTotalBudget = 0;

            foreach ($rabPeriod->days as $day) {
                $date       = $day->day_date;
                $idDay      = $dayNames[$date->dayOfWeek];
                $idDate     = $date->day . ' ' . $monthNames[$date->month] . ' ' . $date->year;
                $budget     = $day->budget();
                $dayRfc     = $day->rfc();
                $daySisa    = $day->surplus();

                // Day header row
                $row = $blank();
                $row[0]  = $idDay . ', ' . $idDate;
                $row[9]  = 'PK: ' . number_format($day->pk_count);
                $row[10] = 'PB: ' . number_format($day->pb_count);
                $row[15] = '';
                $row[16] = $budget;
                $row[17] = 'Realisasi: ' . round((float) $day->realisasi, 2);
                fputcsv($out, $row);

                foreach ($day->menus as $menu) {
                    $effPk    = $menu->effectivePkCount();
                    $effPb    = $menu->effectivePbCount();
                    $category = $menu->is_replacement ? 'alergen' : $menu->category;

                    if ($menu->items->isEmpty()) {
                        $row    = $blank();
                        $row[1] = $category;
                        $row[2] = $menu->menu->name;
                        $row[9] = $effPk;
                        $row[10] = $effPb;
                        $row[16] = $budget;
                        fputcsv($out, $row);
                        continue;
                    }

                    foreach ($menu->items as $item) {
                        $price          = (float) $item->purchase_price;
                        $pkGramasi      = (float) $item->pk_gramasi;
                        $pbGramasi      = (float) $item->pb_gramasi;
                        $kalkulasiPk    = $price > 0 ? round($pkGramasi / 1000 * $price, 4) : 0;
                        $kalkulasiPb    = $price > 0 ? round($pbGramasi / 1000 * $price, 4) : 0;
                        $kebutuhanPk    = round($pkGramasi * $effPk, 2);
                        $kebutuhanPb    = round($pbGramasi * $effPb, 2);
                        $totalKebutuhan = $kebutuhanPk + $kebutuhanPb;
                        $roundKg        = round($totalKebutuhan / 1000, 2);
                        $totalHarga     = round($item->costFor($effPk, $effPb), 2);

                        fputcsv($out, [
                            '',
                            $category,
                            $menu->menu->name,
                            $item->product->name,
                            $pkGramasi,
                            $pbGramasi,
                            $kalkulasiPk,
                            $kalkulasiPb,
                            $price,
                            $effPk,
                            $effPb,
                            $kebutuhanPk,
                            $kebutuhanPb,
                            $totalKebutuhan,
                            $roundKg,
                            $totalHarga,
                            $budget,
                            $item->supplier?->name ?? '',
                        ]);
                    }
                }

                // Day total row
                $row     = $blank();
                $row[0]  = 'TOTAL ' . $idDay;
                $row[15] = round($dayRfc, 2);
                $row[16] = $budget;
                $row[17] = 'SISA: ' . round($daySisa, 2);
                fputcsv($out, $row);

                fputcsv($out, $blank()); // blank separator between days

                $periodTotalRfc    += $dayRfc;
                $periodTotalBudget += $budget;
            }

            // Period summary row
            $netSisa = $periodTotalBudget - $periodTotalRfc;
            $row     = $blank();
            $row[0]  = 'TOTAL PERIODE: ' . $rabPeriod->name;
            $row[15] = round($periodTotalRfc, 2);
            $row[16] = $periodTotalBudget;
            $row[17] = 'Net SISA: ' . round($netSisa, 2);
            fputcsv($out, $row);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
