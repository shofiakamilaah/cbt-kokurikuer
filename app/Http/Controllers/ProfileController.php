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
use Illuminate\Pagination\LengthAwarePaginator;
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

        $isSeniorStudent = $user->isSeniorStudent();

        // Data khusus siswa kelas 11 & 12 (hanya melihat jadwal piket toilet)
        $todayToiletDuty = $isSeniorStudent ? $user->todayToiletDuty() : null;
        $toiletDuties = $isSeniorStudent
            ? $user->dutyAssignments()
                ->where('task_type', 'toilet_duty')
                ->orderByDesc('assignment_date')
                ->with(['floor', 'creator', 'attendance'])
                ->get()
            : collect();

        $type = $filters['type'] ?? 'activity_logs';
        $semester = $this->resolveSemester($filters['semester_id'] ?? null);
        $classroomId = $user->isTeacher()
            ? ($filters['classroom_id'] ?? null)
            : $user->classroom_id;

        // Siswa kelas 11 & 12 tidak perlu query arsip kokurikuler harian
        $records = $isSeniorStudent
            ? new LengthAwarePaginator([], 0, 25)
            : $this->recordsQuery(
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
            });

        return view('profile', [
            'profileUser' => $user->loadMissing('classroom'),
            'isSeniorStudent' => $isSeniorStudent,
            'todayToiletDuty' => $todayToiletDuty,
            'toiletDuties' => $toiletDuties,
            'dataTypes' => self::DATA_TYPES,
            'type' => $type,
            'semesters' => Semester::orderByDesc('start_date')->get(),
            'selectedSemester' => $semester,
            'classrooms' => $user->isTeacher() ? Classroom::orderBy('name')->get() : collect(),
            'selectedClassroomId' => $classroomId,
            'columns' => $this->columns($type),
            'records' => $records,
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
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $oldPhotoPath = $user->profile_photo_path;
        $photoPath = isset($validated['profile_photo'])
            ? $validated['profile_photo']->store('profiles', 'public')
            : $oldPhotoPath;

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'profile_photo_path' => $photoPath,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->update($updateData);

        if ($photoPath !== $oldPhotoPath && $oldPhotoPath !== null
            && Storage::disk('public')->exists($oldPhotoPath)
            && ! Storage::disk('public')->delete($oldPhotoPath)) {
            throw new RuntimeException('Foto profil lama tidak dapat dihapus dari penyimpanan.');
        }

        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui.');
    }

    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        $user = $request->user();
        $this->authorizeArchive($user);

        // Siswa kelas 11 dan 12 tidak memiliki arsip kokurikuler
        if ($user->isSeniorStudent()) {
            return redirect()->route('profile.index')->with('error', 'Siswa kelas 11 dan 12 tidak memiliki arsip harian kokurikuler.');
        }

        $filters = $this->validatedFilters($request);
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

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'type' => ['nullable', 'string', Rule::in(array_keys(self::DATA_TYPES))],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'date' => ['nullable', 'date'],
        ]);
    }

    private function resolveSemester(?int $semesterId): ?Semester
    {
        if ($semesterId !== null) {
            return Semester::find($semesterId);
        }

        return Semester::current() ?? Semester::orderByDesc('start_date')->first();
    }

    private function authorizeArchive(User $user): void
    {
        abort_unless($user->isTeacher() || $user->isStudent(), 403);
    }

    private function recordsQuery(
        string $type,
        User $user,
        ?Semester $semester,
        ?int $classroomId,
        ?string $date,
    ): Builder {
        $query = $this->baseQuery($type);

        if ($semester !== null) {
            $this->applySemesterFilter($query, $type, $semester);
        }

        if ($user->isStudent()) {
            $this->applyStudentFilter($query, $type, $user->id);
        } elseif ($classroomId !== null) {
            $this->applyClassroomFilter($query, $type, $classroomId);
        }

        if ($date !== null) {
            $this->applyDateFilter($query, $type, $date);
        }

        return $query->orderByDesc($this->dateColumn($type))->orderByDesc('id');
    }

    private function baseQuery(string $type): Builder
    {
        return match ($type) {
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
    }

    private function applySemesterFilter(Builder $query, string $type, Semester $semester): void
    {
        if ($type === 'assignments') {
            $query->where('semester_id', $semester->id);

            return;
        }

        $startDate = $semester->start_date->toDateString();
        $endDate = $semester->end_date->toDateString();

        if ($type === 'attendances') {
            $query->whereHas('dutyAssignment', fn (Builder $subQuery) => $subQuery->whereBetween('assignment_date', [$startDate, $endDate]));

            return;
        }

        $query->whereBetween($this->dateColumn($type), [$startDate, $endDate]);
    }

    private function applyDateFilter(Builder $query, string $type, string $date): void
    {
        if ($type === 'attendances') {
            $query->whereHas('dutyAssignment', fn (Builder $subQuery) => $subQuery->whereDate('assignment_date', $date));

            return;
        }

        if ($type === 'assignments') {
            $query->whereDate('created_at', $date);

            return;
        }

        $query->whereDate($this->dateColumn($type), $date);
    }

    private function applyStudentFilter(Builder $query, string $type, int $studentId): void
    {
        match ($type) {
            'activity_logs', 'waste_deliveries', 'sorted_wastes', 'canteen_sales', 'toilet_duty' => $query
                ->where('user_id', $studentId),
            'wednesday_collections' => $query->where('contributor_id', $studentId),
            'attendances' => $query->whereHas(
                'dutyAssignment',
                fn (Builder $subQuery) => $subQuery->where('user_id', $studentId),
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
            'attendances' => 'created_at',
            'assignments' => 'created_at',
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
                fn (Builder $contributorQuery) => $contributorQuery->where('classroom_id', $classroomId),
            ),
            'attendances' => $query->whereHas(
                'dutyAssignment.user',
                fn (Builder $userQuery) => $userQuery->where('classroom_id', $classroomId),
            ),
            'assignments' => $query->whereHas(
                'student',
                fn (Builder $studentQuery) => $studentQuery->where('classroom_id', $classroomId),
            ),
        };
    }

    private function columns(string $type): array
    {
        return match ($type) {
            'activity_logs' => ['Tanggal', 'Rombel', 'Lantai', 'Kegiatan', 'Keterangan', 'Foto', 'Pencatat'],
            'waste_deliveries' => ['Tanggal', 'Ruangan', 'Lantai', 'Rombel', 'Petugas'],
            'sorted_wastes' => ['Tanggal', 'Rombel', 'Organik (kg)', 'Non-organik (kg)', 'Residu (kg)', 'Pencatat'],
            'wednesday_collections' => ['Tanggal', 'Penyetor', 'NIS', 'Rombel', 'Jenis limbah', 'Jumlah', 'Pencatat'],
            'canteen_sales' => ['Tanggal', 'Kantin', 'Nama Makanan', 'Harga satuan', 'Stok awal', 'Terjual', 'Total', 'Petugas', 'Rombel'],
            'attendances' => ['Tanggal', 'Nama', 'NIS', 'Rombel', 'Rayon', 'Tugas', 'Lokasi', 'Status', 'Keterangan', 'Pencatat'],
            'assignments' => ['Tanggal dicatat', 'Semester', 'Nama', 'NIS', 'Rombel', 'Tugas', 'Dibuat oleh'],
            'toilet_duty' => ['Tanggal dicatat', 'Nama', 'NIS', 'Rayon', 'Rombel', 'Lantai', 'Dicatat oleh'],
        };
    }

    private function formatRow(string $type, Model $record): array
    {
        return match ($type) {
            'activity_logs' => [
                $record->activity_date->format('d/m/Y'),
                $record->user->classroom?->name ?? '-',
                $record->floor?->name ?? 'Semua area',
                $record->type_label,
                $record->description ?? '-',
                $record->photo ? 'Foto tersedia' : '-',
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
                $record->classroom?->name ?? '-',
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
                $record->canteen?->name ?? 'Kantin',
                $record->item_name,
                'Rp'.number_format($record->price, 0, ',', '.'),
                (string) ($record->initial_stock ?? 0),
                (string) $record->quantity,
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
                $record->recorder?->name ?? 'Guru',
            ],
            'assignments' => [
                $record->created_at->format('d/m/Y H:i'),
                $record->semester->label,
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
                $record->location_name,
                $record->creator?->name ?? 'Guru',
            ],
        };
    }

    private function formatDecimal(mixed $value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }

    private function escapeSpreadsheetFormula(string $value): string
    {
        if (str_starts_with($value, '=') || str_starts_with($value, '+') || str_starts_with($value, '-') || str_starts_with($value, '@')) {
            return "'".$value;
        }

        return $value;
    }
}
