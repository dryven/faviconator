<?php

	use DDM\Faviconator\Faviconator;
	use DDM\Faviconator\Http\Controllers\SettingsController;
	use Illuminate\Support\Facades\Route;

	Route::prefix(Faviconator::NAMESPACE . '/')->name(Faviconator::NAMESPACE . '.')->group(function () {
		Route::name('settings.')->group(function () {
			Route::get('/', [SettingsController::class, 'index'])->name('index');
			Route::post('/', [SettingsController::class, 'update'])->name('update');
		});
	});