<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('contacts.index');
});

// Main route for the view
Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');

// API/AJAX routes for contacts
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
Route::put('/contacts/{contact}', [ContactController::class, 'update']);
Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

// API/AJAX routes for custom fields
Route::resource('custom-fields', CustomFieldController::class);

// Contact merge feature routes
Route::get('contacts/list/list-for-merge', [App\Http\Controllers\ContactController::class, 'mergelist'])->name('contacts.listForMerge');
Route::get('contacts/list/merge-preview', [App\Http\Controllers\ContactController::class, 'mergePreview'])->name('contacts.mergePreview');
Route::post('contacts/list/merge', [App\Http\Controllers\ContactController::class, 'merge'])->name('contacts.merge');

Route::get('/contacts/{contact}/merged-data', [App\Http\Controllers\ContactController::class, 'mergedData'])->name('contacts.mergedData');
