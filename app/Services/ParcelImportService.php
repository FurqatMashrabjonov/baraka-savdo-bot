<?php

namespace App\Services;

use App\Enums\ParcelStatus;
use App\Models\Parcel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ParcelImportService
{
    protected array $importResults = [];
    protected int $chunkSize = 100; // Process in batches of 100

    public function importChinaExcel(string $filePath): array
    {
        $results = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => [],
        ];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            $dataRows = array_slice($rows, 1);

            // Process in chunks to avoid memory issues and conflicts
            $chunks = array_chunk($dataRows, $this->chunkSize);

            foreach ($chunks as $chunkIndex => $chunk) {
                DB::transaction(function () use ($chunk, &$results, $chunkIndex) {
                    $batch = [];

                    foreach ($chunk as $row) {
                        $results['processed']++;

                        if (empty($row[0])) {
                            continue; // Skip empty track numbers
                        }

                        $trackNumber = trim($row[0]);
                        $weight = !empty($row[1]) ? (float) $row[1] : null;
                        $isBanned = !empty($row[2]) ? (bool) $row[2] : false;

                        try {
                            $parcel = Parcel::where('track_number', $trackNumber)->lockForUpdate()->first();

                            if ($parcel) {
                                $parcel->weight = $weight;
                                $parcel->is_banned = $isBanned;
                                $parcel->china_uploaded_at = now();

                                if ($parcel->status === ParcelStatus::CREATED) {
                                    $parcel->status = ParcelStatus::ARRIVED_CHINA;
                                }

                                $parcel->save();
                                $results['updated']++;
                            } else {
                                $batch[] = [
                                    'track_number' => $trackNumber,
                                    'weight' => $weight,
                                    'is_banned' => $isBanned,
                                    'status' => ParcelStatus::ARRIVED_CHINA->value,
                                    'china_uploaded_at' => now(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                                $results['created']++;
                            }
                        } catch (\Exception $e) {
                            $results['errors'][] = "Trek raqami {$trackNumber}: " . $e->getMessage();
                        }
                    }

                    // Bulk insert new parcels
                    if (!empty($batch)) {
                        Parcel::insert($batch);
                    }
                }, 5); // Retry transaction up to 5 times
            }
        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        } finally {
            // Always delete the uploaded file after processing
            $this->deleteUploadedFile($filePath);
        }

        return $results;
    }

    public function importUzbekistanExcel(string $filePath): array
    {
        $results = [
            'processed' => 0,
            'updated' => 0,
            'not_found' => 0,
            'errors' => [],
        ];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            $dataRows = array_slice($rows, 1);

            // Process in chunks to avoid memory issues and conflicts
            $chunks = array_chunk($dataRows, $this->chunkSize);

            foreach ($chunks as $chunkIndex => $chunk) {
                DB::transaction(function () use ($chunk, &$results) {
                    foreach ($chunk as $row) {
                        $results['processed']++;

                        if (empty($row[0])) {
                            continue; // Skip empty track numbers
                        }

                        $trackNumber = trim($row[0]);

                        try {
                            $parcel = Parcel::where('track_number', $trackNumber)->lockForUpdate()->first();

                            if ($parcel) {
                                $parcel->uzb_uploaded_at = now();

                                if ($parcel->status === ParcelStatus::ARRIVED_CHINA) {
                                    $parcel->status = ParcelStatus::ARRIVED_UZB;
                                }

                                $parcel->save();
                                $results['updated']++;
                            } else {
                                $results['not_found']++;
                                $results['errors'][] = "Trek raqami topilmadi: {$trackNumber}";
                            }
                        } catch (\Exception $e) {
                            $results['errors'][] = "Trek raqami {$trackNumber}: " . $e->getMessage();
                        }
                    }
                }, 5); // Retry transaction up to 5 times
            }
        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        } finally {
            // Always delete the uploaded file after processing
            $this->deleteUploadedFile($filePath);
        }

        return $results;
    }

    public function getImportSummary(array $results, string $type): string
    {
        if ($type === 'china') {
            $summary = "Jami qayta ishlangan: {$results['processed']}\n";
            $summary .= "Yangi yaratilgan: {$results['created']}\n";
            $summary .= "Yangilangan: {$results['updated']}";

            if (!empty($results['errors'])) {
                $summary .= "\n\nXatolar:\n" . implode("\n", array_slice($results['errors'], 0, 5));
                if (count($results['errors']) > 5) {
                    $summary .= "\n... va yana " . (count($results['errors']) - 5) . " ta xato";
                }
            }
        } else {
            $summary = "Jami qayta ishlangan: {$results['processed']}\n";
            $summary .= "Yangilangan: {$results['updated']}\n";
            $summary .= "Topilmagan: {$results['not_found']}";

            if (!empty($results['errors'])) {
                $summary .= "\n\nXatolar:\n" . implode("\n", array_slice($results['errors'], 0, 5));
                if (count($results['errors']) > 5) {
                    $summary .= "\n... va yana " . (count($results['errors']) - 5) . " ta xato";
                }
            }
        }

        return $summary;
    }

    /**
     * Delete the uploaded file after processing
     */
    protected function deleteUploadedFile(string $filePath): void
    {
        try {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        } catch (\Exception $e) {
            // Log the error but don't throw - file deletion failure shouldn't break the import
            \Log::warning('Failed to delete uploaded file: ' . $filePath, ['error' => $e->getMessage()]);
        }
    }
}
