<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Iqionly\Laraddon\Debugs\Profiler;

Route::get('/_profiler', function () {
    return App::get(Profiler::class)->render();
})->name('laraddon.profiler');