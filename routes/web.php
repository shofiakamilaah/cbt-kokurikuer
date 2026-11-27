<?php

use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AssignmentImportController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Duty\ActivityLogController;
use App\Http\Controllers\Duty\CanteenSaleController;
use App\Http\Controllers\Duty\CollectionController;
use App\Http\Controllers\Duty\DashboardController as DutyDashboardController;
use App\Http\Controllers\Duty\FloorMapController;
use App\Http\Controllers\Duty\SortedWasteController;
use App\Http\Controllers\Duty\ToiletDutyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/export', [ProfileController::class, 'export'])->name('profile.export');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::prefix('duty')
    ->name('duty.')
    ->middleware(['auth', 'student'])
    ->group(function (): void {
        Route::get('/dashboard', [DutyDashboardController::class, 'index'])
            ->middleware('on.duty')
            ->name('dashboard');

        Route::get('/floors', [FloorMapController::class, 'index'])
            ->middleware('cleaning.officer')
            ->name('floors.index');
        Route::post('/floors/rooms/{room}/toggle', [FloorMapController::class, 'toggle'])
            ->middleware('cleaning.officer')
            ->name('floors.toggle');

        Route::get('/sorted-wastes', [SortedWasteController::class, 'index'])
            ->middleware('cleaning.officer')
            ->name('sorted-wastes.index');
        Route::post('/sorted-wastes', [SortedWasteController::class, 'store'])
            ->middleware('cleaning.officer')
            ->name('sorted-wastes.store');

        Route::get('/activities', [ActivityLogController::class, 'index'])
            ->middleware('cleaning.officer')
            ->name('activities.index');
        Route::post('/activities', [ActivityLogController::class, 'store'])
            ->middleware('cleaning.officer')
            ->name('activities.store');

        Route::get('/collections', [CollectionController::class, 'index'])
            ->middleware('cleaning.officer')
            ->name('collections.index');
        Route::post('/collections', [CollectionController::class, 'store'])
            ->middleware('cleaning.officer')
            ->name('collections.store');

        Route::get('/sales', [CanteenSaleController::class, 'index'])
            ->middleware('canteen.officer')
            ->name('sales.index');
        Route::post('/sales', [CanteenSaleController::class, 'store'])
            ->middleware('canteen.officer')
            ->name('sales.store');
        Route::delete('/sales/{sale}', [CanteenSaleController::class, 'destroy'])
            ->middleware('canteen.officer')
            ->name('sales.destroy');

        Route::get('/toilet', [ToiletDutyController::class, 'index'])
            ->middleware('cleaning.officer')
            ->name('toilet.index');
        Route::get('/toilet/student-lookup', [ToiletDutyController::class, 'studentLookup'])
            ->middleware('cleaning.officer')
            ->name('toilet.student-lookup');
        Route::post('/toilet/record', [ToiletDutyController::class, 'recordCleaning'])
            ->middleware('cleaning.officer')
            ->name('toilet.record');
        Route::post('/toilet/replacement', [ToiletDutyController::class, 'recordCleaning'])
            ->middleware('cleaning.officer')
            ->name('toilet.replacement');
        Route::post('/toilet/{assignment}/toggle', [ToiletDutyController::class, 'toggle'])
            ->middleware('cleaning.officer')
            ->name('toilet.toggle');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'teacher'])
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
        Route::post('/attendances', [AdminAttendanceController::class, 'store'])->name('attendances.store');

        Route::get('/assignments/template', [AssignmentImportController::class, 'template'])->name('assignments.template');
        Route::get('/assignments/import', [AssignmentImportController::class, 'create'])->name('assignments.import.create');
        Route::post('/assignments/import', [AssignmentImportController::class, 'store'])->name('assignments.import.store');
        Route::get('/assignments/import/preview', [AssignmentImportController::class, 'preview'])->name('assignments.import.preview');
        Route::post('/assignments/import/confirm', [AssignmentImportController::class, 'confirm'])->name('assignments.import.confirm');

        Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/assignments/schedule', [AssignmentController::class, 'generateSchedule'])->name('assignments.schedule');
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

        Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
        Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroy'])->name('classrooms.destroy');
    });
