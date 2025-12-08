<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Index');
    })->name('dashboard');
    
    // Map
    Route::get('/map', function () {
        return Inertia::render('Map/Index');
    })->name('map.index');
    
    // Alerts
    Route::prefix('alerts')->group(function () {
        Route::get('/active', function () {
            return Inertia::render('Alerts/Active');
        })->name('alerts.active');
        
        Route::get('/history', function () {
            return Inertia::render('Alerts/History');
        })->name('alerts.history');
        
        Route::get('/config', function () {
            return Inertia::render('Alerts/Config');
        })->name('alerts.config');
    });
    
    // Sensors
    Route::prefix('sensors')->group(function () {
        Route::get('/status', function () {
            return Inertia::render('Sensors/Status');
        })->name('sensors.status');
        
        Route::get('/manage', function () {
            return Inertia::render('Sensors/Manage');
        })->name('sensors.manage');
    });
    
    // Cameras
    Route::prefix('cameras')->group(function () {
        Route::get('/live', function () {
            return Inertia::render('Cameras/Live');
        })->name('cameras.live');
        
        Route::get('/recordings', function () {
            return Inertia::render('Cameras/Recordings');
        })->name('cameras.recordings');
    });
    
    // Access Control
    Route::get('/access', function () {
        return Inertia::render('Access/Index');
    })->name('access.index');
    
    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/daily', function () {
            return Inertia::render('Reports/Daily');
        })->name('reports.daily');
        
        Route::get('/monthly', function () {
            return Inertia::render('Reports/Monthly');
        })->name('reports.monthly');
        
        Route::get('/custom', function () {
            return Inertia::render('Reports/Custom');
        })->name('reports.custom');
    });
    
    // Users
    Route::get('/users', function () {
        return Inertia::render('Users/Index');
    })->name('users.index');
    
    // Devices
    Route::get('/devices', function () {
        return Inertia::render('Devices/Index');
    })->name('devices.index');
    
    // Logs
    Route::get('/logs', function () {
        return Inertia::render('Logs/Index');
    })->name('logs.index');
    
    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/general', function () {
            return Inertia::render('Settings/General');
        })->name('settings.general');
        
        Route::get('/security', function () {
            return Inertia::render('Settings/Security');
        })->name('settings.security');
        
        Route::get('/notifications', function () {
            return Inertia::render('Settings/Notifications');
        })->name('settings.notifications');
    });
});
