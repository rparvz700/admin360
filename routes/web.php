<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SubcenterController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\FacilitiesManagement\BuildingsController;
use App\Http\Controllers\FacilitiesManagement\FloorsController;
use App\Http\Controllers\FacilitiesManagement\DashboardController;
use App\Http\Controllers\FacilitiesManagement\AgreementsController;
use App\Http\Controllers\FacilitiesManagement\Rent\RentController;
use App\Http\Controllers\FacilitiesManagement\AssetManagement\AssetController;
use App\Http\Controllers\FacilitiesManagement\AssetManagement\AssetCategoryController;
use App\Http\Controllers\FacilitiesManagement\AssetManagement\AssetAttributeController;

use App\Http\Controllers\VehicleManagement\DriverController;

use App\Http\Controllers\GenericDocumentManagement\GenericDocumentController;


use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

Route::middleware(['auth'])->group(function(){
    // Vehicle Management: Vehicle Documents CRUD
    //Route::resource('vehicle-documents', App\Http\Controllers\VehicleManagement\VehicleDocumentController::class);
    //Route::get('vehicle-documents-list', [App\Http\Controllers\VehicleManagement\VehicleDocumentController::class, 'list'])->name('vehicle-documents.list');
    // Vehicle Management: Vehicle Document Attributes CRUD
    // Route::get('vehicle-document-attributes/list', [\App\Http\Controllers\VehicleManagement\VehicleDocumentAttributeController::class, 'list'])->name('vehicle-document-attributes.list');
    // Route::resource('vehicle-document-attributes', \App\Http\Controllers\VehicleManagement\VehicleDocumentAttributeController::class);
    // // Vehicle Management: Vehicle Document Categories CRUD
    // Route::get('vehicle-document-categories/list', [\App\Http\Controllers\VehicleManagement\VehicleDocumentCategoryController::class, 'list'])->name('vehicle-document-categories.list');
    // Route::resource('vehicle-document-categories', \App\Http\Controllers\VehicleManagement\VehicleDocumentCategoryController::class);
    // Vehicle Management: Vehicles CRUD
    Route::get('vehicles/list', [\App\Http\Controllers\VehicleManagement\VehicleController::class, 'list'])->name('vehicles.list');
    Route::resource('vehicles', \App\Http\Controllers\VehicleManagement\VehicleController::class);
    // Vehicle Management: Vehicle Types CRUD
    Route::get('vehicle-types-list', [\App\Http\Controllers\VehicleManagement\VehicleTypeController::class, 'list'])->name('vehicle-types.list');
    Route::resource('vehicle-types', \App\Http\Controllers\VehicleManagement\VehicleTypeController::class);
    // Vehicle Management: Import drivers from external API
    Route::get('drivers/import/api', [\App\Http\Controllers\VehicleManagement\DriverController::class, 'importFromApi'])->name('drivers.import.api');
    // Vehicle Management DataTables route
    Route::get('drivers-list', [\App\Http\Controllers\VehicleManagement\DriverController::class, 'list'])->name('drivers.list');
    Route::get('/', [DashboardController::class, 'index']);

    Route::resources([
        'roles' => RoleController::class,
        'users' => UserController::class,
        'departments' => DepartmentController::class,
        'subcenters' => SubcenterController::class,
        'buildings' => BuildingsController::class,
        'floors' => FloorsController::class,
        'agreements' => AgreementsController::class,
        'rent' => RentController::class,
        'assets' => AssetController::class,
        'asset-categories' => AssetCategoryController::class,
        'asset-attributes' => AssetAttributeController::class,
    ]);

    // Vehicle Management
    Route::resource('drivers', DriverController::class);
    Route::get('rent-list', [RentController::class, 'list'])->name('rent.list');
    Route::get('floors-list', [FloorsController::class, 'list'])->name('floors.list');
    Route::get('floors/{floor}', [FloorsController::class, 'show'])->name('floors.show');
    Route::get('buildings-list', [BuildingsController::class, 'list'])->name('buildings.list');
    //-----------------Subcenter extra routes---------------
    Route::get('subcenter-list', [SubcenterController::class, 'subcenterList'])->name('subcenterList');
    //-----------------User extra routes--------------------
    Route::get('user-list', [UserController::class, 'userList'])->name('userList');
    // Facilities Management Dashboard
    Route::get('facilities/dashboard', [DashboardController::class, 'index'])->name('facilities.dashboard');



    // Generic Document Management: Generic Documents CRUD
    Route::resource('generic-documents', App\Http\Controllers\GenericDocumentManagement\GenericDocumentController::class);
    Route::get('generic-documents-list', [App\Http\Controllers\GenericDocumentManagement\GenericDocumentController::class, 'list'])->name('generic-documents.list');
    // Generic Document Management: Generic Document Attributes CRUD
    Route::get('generic-document-attributes/list', [\App\Http\Controllers\GenericDocumentManagement\GenericDocumentAttributeController::class, 'list'])->name('generic-document-attributes.list');
    Route::resource('generic-document-attributes', \App\Http\Controllers\GenericDocumentManagement\GenericDocumentAttributeController::class);
    // Generic Document Management: Generic Document Categories CRUD
    Route::get('generic-document-categories/list', [\App\Http\Controllers\GenericDocumentManagement\GenericDocumentCategoryController::class, 'list'])->name('generic-document-categories.list');
    Route::resource('generic-document-categories', \App\Http\Controllers\GenericDocumentManagement\GenericDocumentCategoryController::class);
    Route::get('/fetch-documentable-records', [GenericDocumentController::class, 'fetchDocumentables'])
    ->name('documentable.fetch');

});


Auth::routes(['register' => false]);

// Document Manager Prototype Route
Route::get('document-manager/prototype', function () {
    return view('DocumentManagement.document_manager');
})->name('document-manager.prototype');

