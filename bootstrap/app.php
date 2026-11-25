<?php

use App\Http\Middleware\IsCanteenOfficer;
use App\Http\Middleware\IsCleaningOfficer;
use App\Http\Middleware\IsOnDuty;
use App\Http\Middleware\IsStudent;
use App\Http\Middleware\IsTeacher;
use App\Http\Middleware\IsToiletOfficer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'teacher' => IsTeacher::class,
            'student' => IsStudent::class,
            'on.duty' => IsOnDuty::class,
            'cleaning.officer' => IsCleaningOfficer::class,
            'toilet.officer' => IsToiletOfficer::class,
            'canteen.officer' => IsCanteenOfficer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
