<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kandungan;
use App\Models\Resiko;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportCosingToKandungan extends Command
{
    protected $signature = 'cosing:import';
    protected $description = 'Import kandungan dari file COSING ke database';

    public function handle()
    {
        $filePath = storage_path('app/public/COSING_Cleaned_Normalized_v7(1).csv');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: $filePath");
            return 1;
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // baca header

        $inserted = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            $name = trim($data['INCI name']);
            $function = $data['Function'] ?? null;
            $riskDesc = $data['Risk Description'] ?? null;
            $riskLevel = $data['Risk Level'] ?? null;
            $restriction = $data['Restriction'] ?? null;

            if (!$name) {
                $skipped++;
                continue;
            }

            try {
                DB::beginTransaction();

                $resiko = null;
                if ($riskDesc) {
                    $resiko = Resiko::firstOrCreate(
                        ['deskripsi' => $riskDesc],
                        [
                            'tingkat_resiko' => $riskLevel,
                            'code' => $restriction
                        ]
                    );
                }

                $existing = Kandungan::where('name', $name)->first();
                if ($existing) {
                    $skipped++;
                    DB::rollBack();
                    continue;
                }

                Kandungan::create([
                    'name' => $name,
                    'fungsi' => $function,
                    'resiko_id' => $resiko?->id
                ]);

                $inserted++;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Gagal import kandungan', [
                    'error' => $e->getMessage(),
                    'nama' => $name
                ]);
                $skipped++;
            }
        }

        fclose($file);

        $this->info("Import selesai. Berhasil: $inserted, Dilewati: $skipped");
        return 0;
    }
}
