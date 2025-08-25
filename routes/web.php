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
});


Auth::routes(['register' => false]);
