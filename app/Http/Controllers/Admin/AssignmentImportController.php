<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\DutySchedule;
use App\Models\Semester;
use App\Services\AssignmentImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentImportController extends Controller
{
    public function template(Request $request): StreamedResponse
    {
        $context = $this->validatedContext($request);
        $this->ensureScheduledClassroom($context);

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                throw new RuntimeException('Template CSV tidak dapat dibuat.');
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['NIS'], ';', '"', '\\');
            fclose($handle);
        }, 'template-'.($context['assignment_type'] === 'cleaning' ? 'kebersihan' : 'kantin').'.csv');
    }

    public function create(Request $request): View
    {
        $context = $this->validatedContext($request);
        $this->ensureScheduledClassroom($context);

        return view('admin.assignments.import', [
            'classroom' => Classroom::findOrFail($context['classroom_id']),
            'semester' => Semester::findOrFail($context['semester_id']),
            'assignmentType' => $context['assignment_type'],
            'taskLabel' => Assignment::TYPES[$context['assignment_type']],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimes:csv,txt'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'assignment_type' => ['required', 'string', Rule::in(array_keys(Assignment::TYPES))],
        ]);
        $context = [
            'classroom_id' => (int) $validated['classroom_id'],
            'semester_id' => (int) $validated['semester_id'],
            'assignment_type' => $validated['assignment_type'],
        ];
        $this->ensureScheduledClassroom($context);

        $path = Storage::disk('local')->putFile('imports', $validated['file']);
        if (! is_string($path)) {
            throw new RuntimeException('Berkas penugasan tidak dapat disimpan.');
        }

        $request->session()->put('assignment_import_path', $path);
        $request->session()->put('assignment_import_context', $context);

        return redirect()->route('admin.assignments.import.preview');
    }

    public function preview(Request $request, AssignmentImportService $assignmentImportService): View
    {
        $path = $request->session()->get('assignment_import_path');
        $context = $this->sessionContext($request);
        if (! is_string($path) || $context === null) {
            abort(404);
        }

        $rows = $this->previewRows($path, $context, $assignmentImportService);
        $counts = [
            'new' => collect($rows)->where('status', 'new')->count(),
            'updated' => collect($rows)->where('status', 'updated')->count(),
            'unchanged' => collect($rows)->where('status', 'unchanged')->count(),
            'error' => collect($rows)->where('status', 'error')->count(),
        ];

        return view('admin.assignments.preview', [
            'rows' => $rows,
            'counts' => $counts,
            'classroom' => Classroom::findOrFail($context['classroom_id']),
            'semester' => Semester::findOrFail($context['semester_id']),
            'taskLabel' => Assignment::TYPES[$context['assignment_type']],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function previewRows(
        string $path,
        array $context,
        AssignmentImportService $assignmentImportService,
    ): array
    {
        return $assignmentImportService->parseStoredFile(
            $path,
            $context['classroom_id'],
            $context['semester_id'],
            $context['assignment_type'],
        );
    }

    /**
     * @return array{classroom_id: int, semester_id: int, assignment_type: string}
     */
    private function validatedContext(Request $request): array
    {
        $validated = $request->validate([
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'assignment_type' => ['required', 'string', Rule::in(array_keys(Assignment::TYPES))],
        ]);

        return [
            'classroom_id' => (int) $validated['classroom_id'],
            'semester_id' => (int) $validated['semester_id'],
            'assignment_type' => $validated['assignment_type'],
        ];
    }

    /**
     * @param array{classroom_id: int, semester_id: int, assignment_type: string} $context
     */
    private function ensureScheduledClassroom(array $context): void
    {
        $classroomIsScheduled = Classroom::whereKey($context['classroom_id'])
            ->where('grade_level', 10)
            ->exists()
            && DutySchedule::where('classroom_id', $context['classroom_id'])
                ->where('semester_id', $context['semester_id'])
                ->exists();

        abort_unless($classroomIsScheduled, 404);
    }

    /**
     * @return array{classroom_id: int, semester_id: int, assignment_type: string}|null
     */
    private function sessionContext(Request $request): ?array
    {
        $context = $request->session()->get('assignment_import_context');
        if (! is_array($context)) {
            return null;
        }

        $validator = validator($context, [
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'assignment_type' => ['required', 'string', Rule::in(array_keys(Assignment::TYPES))],
        ]);
        if ($validator->fails()) {
            return null;
        }

        $validated = $validator->validated();
        $validated = [
            'classroom_id' => (int) $validated['classroom_id'],
            'semester_id' => (int) $validated['semester_id'],
            'assignment_type' => $validated['assignment_type'],
        ];
        $this->ensureScheduledClassroom($validated);

        return $validated;
    }

    public function confirm(
        Request $request,
        AssignmentImportService $assignmentImportService
    ): RedirectResponse {
        $path = $request->session()->get('assignment_import_path');
        $context = $this->sessionContext($request);
        if (! is_string($path) || $context === null) {
            abort(404);
        }

        $rows = $this->previewRows($path, $context, $assignmentImportService);
        $errorCount = collect($rows)->where('status', 'error')->count();
        if ($errorCount > 0) {
            return redirect()->route('admin.assignments.import.preview');
        }

        $createdCount = 0;
        $updatedCount = 0;
        DB::transaction(function () use ($rows, $request, &$createdCount, &$updatedCount): void {
            foreach ($rows as $row) {
                if (($row['status'] ?? null) === 'error') {
                    continue;
                }

                $assignment = Assignment::updateOrCreate(
                    [
                        'student_id' => $row['user_id'],
                        'semester_id' => $row['semester_id'],
                    ],
                    [
                        'assignment_type' => $row['assignment_type'],
                        'created_by' => $request->user()->id,
                    ]
                );

                if ($assignment->wasRecentlyCreated) {
                    $createdCount++;
                } elseif (($row['status'] ?? null) === 'updated') {
                    $updatedCount++;
                }
            }
        });

        Storage::disk('local')->delete($path);
        $request->session()->forget('assignment_import_path');
        $request->session()->forget('assignment_import_context');

        $message = "{$createdCount} penugasan dibuat dan {$updatedCount} penugasan diperbarui.";

        return redirect()->route('admin.assignments.index')->with('success', $message);
    }
}
