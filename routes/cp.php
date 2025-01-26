<?php

use Illuminate\Support\Facades\Route;
use Lwekuiper\StatamicMailchimp\Http\Controllers\FormConfigController;
use Lwekuiper\StatamicMailchimp\Http\Controllers\GetFormFieldsController;
use Lwekuiper\StatamicMailchimp\Http\Controllers\GetMergeFieldsController;
use Lwekuiper\StatamicMailchimp\Http\Controllers\GetTagsController;

Route::name('mailchimp-pro.')->prefix('mailchimp-pro')->group(function () {
    Route::get('/', [FormConfigController::class, 'index'])->name('index');
    Route::get('/{form}/edit', [FormConfigController::class, 'edit'])->name('edit');
    Route::patch('/{form}', [FormConfigController::class, 'update'])->name('update');
    Route::delete('/{form}', [FormConfigController::class, 'destroy'])->name('destroy');

    Route::get('form-fields/{form}', [GetFormFieldsController::class, '__invoke'])->name('form-fields');
    Route::get('merge-fields/{list}', [GetMergeFieldsController::class, '__invoke'])->name('merge-fields');
    Route::get('tags/{list}', [GetTagsController::class, '__invoke'])->name('tags');
});
