<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AssignmentImportService
{
    public function parseStoredFile(
        string $path,
        int $classroomId,
        int $semesterId,
        string $assignmentType,
    ): array
    {
        $absolutePath = Storage::disk('local')->path($path);
        $handle = fopen($absolutePath, 'rb');

        if (! is_resource($handle)) {
            return [[
                'nis' => '',
                'tugas' => '',
                'status' => 'error',
                'message' => 'Berkas tidak dapat dibaca',
            ]];
        }

        $headerLine = fgets($handle);
        if ($headerLine === false) {
            fclose($handle);

            return [];
        }

        $headerLine = str_replace("\xEF\xBB\xBF", '', $headerLine);
        $delimiter = str_contains($headerLine, ';') ? ';' : ',';
        $header = array_map(
            fn (string $cell): string => mb_strtolower(trim($cell)),
            str_getcsv(trim($headerLine), $delimiter, '"', '\\'),
        );

        $rows = [];
        $rowNumber = 0;
        while (($line = fgets($handle)) !== false) {
            $line = trim(str_replace("\xEF\xBB\xBF", '', $line));
            if ($line === '') {
                continue;
            }

            $rowNumber++;
            if ($rowNumber > 2000) {
                break;
            }

            $cells = str_getcsv($line, $delimiter, '"', '\\');
            $row = array_combine($header, array_pad($cells, count($header), ''));
            $rows[] = [
                'nis' => trim((string) ($row['nis'] ?? '')),
                'tugas' => trim((string) ($row['tugas'] ?? ($row['jenis tugas'] ?? ''))),
            ];
        }
        fclose($handle);

        return $this->validateRows($rows, $classroomId, $semesterId, $assignmentType);
    }

    public function validateRows(
        array $rows,
        int $classroomId,
        int $semesterId,
        string $assignmentType,
    ): array
    {
        $semester = Semester::find($semesterId);
        $seenNis = [];
        $parsedRows = [];

        foreach ($rows as $row) {
            $nis = trim((string) ($row['nis'] ?? ''));
            $student = $nis === ''
                ? null
                : User::where('nis', $nis)
                    ->where('role', 'student')
                    ->where('classroom_id', $classroomId)
                    ->first();

            $message = null;
            if ($nis === '') {
                $message = 'NIS wajib diisi';
            } elseif (isset($seenNis[$nis])) {
                $message = 'NIS dobel dalam file';
            } elseif (! $student) {
                $message = 'NIS tidak terdaftar di rombel ini';
            } elseif (! $semester) {
                $message = 'Semester tidak ditemukan';
            } elseif (! array_key_exists($assignmentType, Assignment::TYPES)) {
                $message = 'Jenis penugasan tidak valid';
            }

            $seenNis[$nis] = true;

            $parsedRows[] = [
                'nis' => $nis,
                'nama' => $student?->name ?? '-',
                'tugas' => Assignment::TYPES[$assignmentType] ?? '',
                'status' => $message === null ? 'new' : 'error',
                'message' => $message,
                'user_id' => $student?->id,
                'semester_id' => $semester?->id,
                'assignment_type' => $assignmentType,
            ];
        }

        if ($semester) {
            foreach ($parsedRows as $index => $row) {
                if ($row['status'] === 'error') {
                    continue;
                }

                $existing = Assignment::where('student_id', $row['user_id'])
                    ->where('semester_id', $semester->id)
                    ->first();

                $parsedRows[$index]['status'] = $existing
                    ? ($existing->assignment_type === $row['assignment_type'] ? 'unchanged' : 'updated')
                    : 'new';
            }
        }

        return $parsedRows;
    }
}
