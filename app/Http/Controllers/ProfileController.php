<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\CanteenSale;
use App\Models\Classroom;
use App\Models\DutyAssignment;
use App\Models\Semester;
use App\Models\SortedWaste;
use App\Models\User;
use App\Models\WasteDelivery;
use App\Models\WednesdayCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    private const DATA_TYPES = [
        'activity_logs' => 'Catatan kegiatan',
        'waste_deliveries' => 'Pengantaran tempat sampah',
        'sorted_wastes' => 'Pemilahan sampah',
        'wednesday_collections' => 'Pengumpulan limbah',
        'canteen_sales' => 'Penjualan kantin',
        'attendances' => 'Absensi petugas',
        'assignments' => 'Penugasan',
        'toilet_duty' => 'Piket toilet',
    ];

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $user = $request->user();
        $this->authorizeArchive($user);

        $type = $filters['type'] ?? 'activity_logs';
        $semester = $this->resolveSemester($filters['semester_id'] ?? null);
        $classroomId = $user->isTeacher()
            ? ($filters['classroom_id'] ?? null)
            : $user->classroom_id;

        return view('profile', [
            'profileUser' => $user->loadMissing('classroom'),
            'dataTypes' => self::DATA_TYPES,
            'type' => $type,
            'semesters' => Semester::orderByDesc('start_date')->get(),
            'selectedSemester' => $semester,
            'classrooms' => $user->isTeacher() ? Classroom::orderBy('name')->get() : collect(),
            'selectedClassroomId' => $classroomId,
            'columns' => $this->columns($type),
            'records' => $this->recordsQuery(
                $type,
                $user,
                $semester,
                $classroomId,
                $filters['date'] ?? null,
            )->paginate(25)->withQueryString()->through(function (Model $record) use ($type): array {
                $photoUrl = $type === 'activity_logs' && $record->photo
                    ? Storage::disk('public')->url($record->photo)
                    : null;
                $values = $this->formatRow($type, $record);
                if ($photoUrl !== null) {
                    $values[5] = $photoUrl;
                }

                return [
                    'values' => $values,
                    'photo_url' => $photoUrl,
                ];
            }),
        ]);
    }

    public function edit(Request $request): View
    {
        $user = $request->user();
        $this->authorizeArchive($user);

        return view('profile.edit', [
            'profileUser' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeArchive($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $oldPhotoPath = $user->profile_photo_path;
        $photoPath = isset($validated['profile_photo'])
            ? $validated['profile_photo']->store('profiles', 'public')
            : $oldPhotoPath;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'profile_photo_path' => $photoPath,
        ]);

        if ($photoPath !== $oldPhotoPath && $oldPhotoPath !== null
            && Storage::disk('public')->exists($oldPhotoPath)
            && ! Storage::disk('public')->delete($oldPhotoPath)) {
            throw new RuntimeException('Foto profil lama tidak dapat dihapus dari penyimpanan.');
        }

        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $user = $request->user();
        $this->authorizeArchive($user);

        $type = $filters['type'] ?? 'activity_logs';
        $semester = $this->resolveSemester($filters['semester_id'] ?? null);
        $classroomId = $user->isTeacher()
            ? ($filters['classroom_id'] ?? null)
            : $user->classroom_id;
        $query = $this->recordsQuery(
            $type,
            $user,
            $semester,
            $classroomId,
            $filters['date'] ?? null,
        );

        return response()->streamDownload(function () use ($query, $type): void {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                throw new RuntimeException('Tidak dapat membuka aliran ekspor arsip.');
            }

            try {
                if (fwrite($output, "\xEF\xBB\xBF") === false
                    || fputcsv($output, $this->columns($type), ';', '"', '\\') === false) {
                    throw new RuntimeException('Tidak dapat menulis judul kolom ekspor arsip.');
                }

                $query->chunk(500, function ($records) use ($output, $type): void {
                    foreach ($records as $record) {
                        $row = array_map(
                            fn (string $cell): string => $this->escapeSpreadsheetFormula($cell),
                            $this->formatRow($type, $record),
                        );

                        if (fputcsv($output, $row, ';', '"', '\\') === false) {
                            throw new RuntimeException('Tidak dapat menulis baris ekspor arsip.');
                        }
                    }
                });
            } finally {
                fclose($output);
            }
        }, 'arsip-'.$type.'-'.today()->format('Ymd').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{type?: string, semester_id?: int, classroom_id?: int, date?: string}
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'type' => ['nullable', 'string', Rule::in(array_keys(self::DATA_TYPES))],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
    }

    private function authorizeArchive(User $user): void
    {
        abort_unless($user->isTeacher() || $user->isStudent(), 403);
    }

    private function resolveSemester(?int $semesterId): ?Semester
    {
        if ($semesterId !== null) {
            return Semester::findOrFail($semesterId);
        }

        return Semester::current() ?? Semester::orderByDesc('start_date')->first();
    }

    private function recordsQuery(
        string $type,
        User $user,
        ?Semester $semester,
        ?int $classroomId,
        ?string $date,
    ): Builder {
        $query = match ($type) {
            'activity_logs' => ActivityLog::query()->with(['user.classroom', 'floor']),
            'waste_deliveries' => WasteDelivery::query()->with(['room.floor', 'user.classroom']),
            'sorted_wastes' => SortedWaste::query()->with(['classroom', 'user']),
            'wednesday_collections' => WednesdayCollection::query()->with(['contributor.classroom', 'user']),
            'canteen_sales' => CanteenSale::query()->with(['user.classroom', 'canteen']),
            'toilet_duty' => DutyAssignment::query()->with(['user.classroom', 'floor', 'creator'])
                ->where('task_type', 'toilet_duty'),
            'attendances' => Attendance::query()->with([
                'dutyAssignment.user.classroom',
                'dutyAssignment.floor',
                'dutyAssignment.canteen',
                'recorder',
            ]),
            'assignments' => Assignment::query()->with(['student.classroom', 'semester', 'creator']),
        };

        if ($semester !== null) {
            if ($type === 'assignments') {
                $query->where('semester_id', $semester->id);
            } elseif ($type === 'attendances') {
                $query->whereHas('dutyAssignment', fn (Builder $assignmentQuery) => $assignmentQuery->whereBetween(
                    'assignment_date',
                    [$semester->start_date->toDateString(), $semester->end_date->toDateString()],
                ));
            } else {
                $query->whereBetween($this->dateColumn($type), [
                    $semester->start_date->toDateString(),
                    $semester->end_date->toDateString(),
                ]);
            }
        }

        $dateColumn = in_array($type, ['assignments', 'attendances'], true)
            ? 'created_at'
            : $this->dateColumn($type);
        if ($date !== null) {
            if ($type === 'attendances') {
                $query->whereHas('dutyAssignment', fn (Builder $assignmentQuery) => $assignmentQuery->whereDate('assignment_date', $date));
            } else {
                $query->whereDate($dateColumn, $date);
            }
        }

        if ($user->isTeacher()) {
            if ($classroomId !== null) {
                $this->applyClassroomFilter($query, $type, $classroomId);
            }
        } else {
            $this->applyStudentFilter($query, $type, $user->id);
        }

        return $query->orderByDesc($dateColumn)->orderByDesc('id');
    }

    private function applyStudentFilter(Builder $query, string $type, int $studentId): void
    {
        match ($type) {
            'activity_logs', 'waste_deliveries', 'sorted_wastes', 'canteen_sales', 'toilet_duty' => $query
                ->where('user_id', $studentId),
            'wednesday_collections' => $query->where('contributor_id', $studentId),
            'attendances' => $query->whereHas(
                'dutyAssignment',
                fn (Builder $assignmentQuery) => $assignmentQuery->where('user_id', $studentId),
            ),
            'assignments' => $query->where('student_id', $studentId),
        };
    }

    private function dateColumn(string $type): string
    {
        return match ($type) {
            'activity_logs' => 'activity_date',
            'waste_deliveries' => 'delivery_date',
            'sorted_wastes' => 'sorted_date',
            'wednesday_collections' => 'collection_date',
            'canteen_sales' => 'sale_date',
            'toilet_duty' => 'assignment_date',
        };
    }

    private function applyClassroomFilter(Builder $query, string $type, int $classroomId): void
    {
        match ($type) {
            'activity_logs', 'waste_deliveries', 'canteen_sales', 'toilet_duty' => $query->whereHas(
                'user',
                fn (Builder $userQuery) => $userQuery->where('classroom_id', $classroomId),
            ),
            'sorted_wastes' => $query->where('classroom_id', $classroomId),
            'wednesday_collections' => $query->whereHas(
                'contributor',
                fn (Builder $userQuery) => $userQuery->where('classroom_id', $classroomId),
            ),
            'attendances' => $query->whereHas(
                'dutyAssignment.user',
                fn (Builder $userQuery) => $userQuery->where('classroom_id', $classroomId),
            ),
            'assignments' => $query->whereHas(
                'student',
                fn (Builder $userQuery) => $userQuery->where('classroom_id', $classroomId),
            ),
        };
    }

    /**
     * @return list<string>
     */
    private function columns(string $type): array
    {
        return match ($type) {
            'activity_logs' => ['Tanggal', 'Kegiatan', 'Rombel', 'Lantai', 'Keterangan', 'Foto', 'Pencatat'],
            'waste_deliveries' => ['Tanggal', 'Ruangan', 'Lantai', 'Rombel', 'Petugas'],
            'sorted_wastes' => ['Tanggal', 'Rombel', 'Organik (kg)', 'Non-organik (kg)', 'Residu (kg)', 'Pencatat'],
            'wednesday_collections' => ['Tanggal', 'Penyetor', 'NIS', 'Rombel', 'Jenis limbah', 'Jumlah', 'Pencatat'],
            'canteen_sales' => ['Tanggal', 'Kantin', 'Barang', 'Jumlah', 'Harga satuan', 'Total', 'Petugas', 'Rombel'],
            'attendances' => ['Tanggal', 'Nama', 'NIS', 'Rombel', 'Rayon', 'Tugas', 'Lokasi', 'Status', 'Keterangan', 'Pencatat'],
            'assignments' => ['Tanggal dicatat', 'Semester', 'Nama', 'NIS', 'Rombel', 'Tugas', 'Dibuat oleh'],
            'toilet_duty' => ['Tanggal dicatat', 'Nama', 'NIS', 'Rayon', 'Rombel', 'Lantai', 'Dicatat oleh'],
        };
    }

    /**
     * @return list<string>
     */
    private function formatRow(string $type, Model $record): array
    {
        return match ($type) {
            'activity_logs' => [
                $record->activity_date->format('d/m/Y'),
                $record->type_label,
                $record->user->classroom?->name ?? '-',
                $record->floor?->name ?? '-',
                $record->description ?? '-',
                $record->photo ?? '-',
                $record->user->name,
            ],
            'waste_deliveries' => [
                $record->delivery_date->format('d/m/Y'),
                $record->room->name,
                $record->room->floor?->name ?? '-',
                $record->user->classroom?->name ?? '-',
                $record->user->name,
            ],
            'sorted_wastes' => [
                $record->sorted_date->format('d/m/Y'),
                $record->classroom->name,
                $this->formatDecimal($record->organic_weight),
                $this->formatDecimal($record->inorganic_weight),
                $this->formatDecimal($record->residue_weight),
                $record->user->name,
            ],
            'wednesday_collections' => [
                $record->collection_date->format('d/m/Y'),
                $record->contributor->name,
                $record->contributor->nis ?? '-',
                $record->contributor->classroom?->name ?? '-',
                $record->waste_label,
                $record->amount === null
                    ? 'Disetor'
                    : $this->formatDecimal($record->amount).' '.$record->unit,
                $record->user->name,
            ],
            'canteen_sales' => [
                $record->sale_date->format('d/m/Y'),
                $record->canteen->name,
                $record->item_name,
                (string) $record->quantity,
                'Rp'.number_format($record->price, 0, ',', '.'),
                'Rp'.number_format($record->total, 0, ',', '.'),
                $record->user->name,
                $record->user->classroom?->name ?? '-',
            ],
            'attendances' => [
                $record->dutyAssignment->assignment_date->format('d/m/Y'),
                $record->dutyAssignment->user->name,
                $record->dutyAssignment->user->nis ?? '-',
                $record->dutyAssignment->user->classroom?->name ?? '-',
                $record->dutyAssignment->user->rayon ?? '-',
                $record->dutyAssignment->task_label,
                $record->dutyAssignment->location_name,
                Attendance::STATUS_LABELS[$record->status] ?? $record->status,
                $record->note ?? '-',
                $record->recorder->name,
            ],
            'assignments' => [
                $record->created_at->format('d/m/Y'),
                $record->semester?->label ?? '-',
                $record->student->name,
                $record->student->nis ?? '-',
                $record->student->classroom?->name ?? '-',
                $record->type_label,
                $record->creator->name,
            ],
            'toilet_duty' => [
                $record->assignment_date->format('d/m/Y'),
                $record->user->name,
                $record->user->nis ?? '-',
                $record->user->rayon ?? '-',
                $record->user->classroom?->name ?? '-',
                $record->floor?->name ?? '-',
                $record->creator->name,
            ],
        };
    }

    private function formatDecimal(float|string $value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }

    private function escapeSpreadsheetFormula(string $value): string
    {
        if ($value !== '-' && preg_match('/^[\t\r ]*[=+\-@]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
