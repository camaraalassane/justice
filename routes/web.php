<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\MilitaireController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InfractionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PhaseTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // ==================== PROFIL ====================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==================== API ROUTES ====================
    Route::get('/api/phase-types', [ProcedureController::class, 'getPhaseTypes'])->name('api.phase-types');
    Route::get('/api/militaires/search', [MilitaireController::class, 'search'])->name('api.militaires.search');
    Route::post('/api/infractions/quick-create', [InfractionController::class, 'quickCreate'])->name('api.infractions.quick-create');
    Route::get('/infractions-data', [InfractionController::class, 'allData'])->name('infractions.data');

    // ==================== PROCÉDURES ====================
    Route::get('/procedures', [ProcedureController::class, 'index'])->name('procedures.index');
    Route::get('/procedures/create', [ProcedureController::class, 'create'])->name('procedures.create');
    Route::post('/procedures', [ProcedureController::class, 'store'])->name('procedures.store');
    Route::get('/procedures/{procedure}', [ProcedureController::class, 'show'])->name('procedures.show');
    Route::delete('/procedures/{procedure}', [ProcedureController::class, 'destroy'])->name('procedures.destroy');
    
    // Phases
    Route::post('/procedures/{procedure}/ajouter-phase', [ProcedureController::class, 'ajouterPhase'])->name('procedures.ajouter-phase');
    Route::put('/procedures/{procedure}/update-phase/{phase}', [ProcedureController::class, 'updatePhase'])->name('procedures.update-phase');
    Route::delete('/procedures/{procedure}/retourner-phase/{phase}', [ProcedureController::class, 'retournerPhase'])->name('procedures.retourner-phase');
    
    // Modifications rapides
    Route::patch('/procedures/{procedure}/update-parquet', [ProcedureController::class, 'updateParquet'])->name('procedures.update-parquet');
    Route::patch('/procedures/{procedure}/update-date-ouverture', [ProcedureController::class, 'updateDateOuverture'])->name('procedures.update-date-ouverture');
    Route::patch('/procedures/{procedure}/update-infractions', [ProcedureController::class, 'updateInfractions'])->name('procedures.update-infractions');
    Route::patch('/procedures/{procedure}/update-parties-civiles', [ProcedureController::class, 'updatePartiesCiviles'])->name('procedures.update-parties-civiles');
    Route::patch('/procedures/{procedure}/update-fautes', [ProcedureController::class, 'updateFautes'])->name('procedures.update-fautes');
    
    // Export PDF
    Route::get('/procedures/{procedure}/export-pdf', [ProcedureController::class, 'exportPdf'])->name('procedures.export-pdf');

    // ==================== MILITAIRES ====================
    Route::get('/militaires', [MilitaireController::class, 'index'])->name('militaires.index');
    Route::get('/militaires/create', [MilitaireController::class, 'create'])->name('militaires.create');
    Route::post('/militaires', [MilitaireController::class, 'store'])->name('militaires.store');
    Route::get('/militaires/{militaire}', [MilitaireController::class, 'show'])->name('militaires.show');
    Route::get('/militaires/{militaire}/edit', [MilitaireController::class, 'edit'])->name('militaires.edit');
    Route::patch('/militaires/{militaire}', [MilitaireController::class, 'update'])->name('militaires.update');
    Route::delete('/militaires/{militaire}', [MilitaireController::class, 'destroy'])->name('militaires.destroy');
    Route::get('/militaires/{militaire}/casier', [MilitaireController::class, 'imprimerCasier'])->name('militaires.casier');

    // ==================== INFRACTIONS ====================
    Route::get('/infractions', [InfractionController::class, 'index'])->name('infractions.index');
    Route::get('/infractions/create', [InfractionController::class, 'create'])->name('infractions.create');
    Route::post('/infractions', [InfractionController::class, 'store'])->name('infractions.store');
    Route::get('/infractions/{infraction}/edit', [InfractionController::class, 'edit'])->name('infractions.edit');
    Route::patch('/infractions/{infraction}', [InfractionController::class, 'update'])->name('infractions.update');
    Route::delete('/infractions/{infraction}', [InfractionController::class, 'destroy'])->name('infractions.destroy');

    // ==================== HISTORIQUE ====================
    Route::get('/historique', [ActivityLogController::class, 'index'])->name('historique.index');
    Route::get('/historique/{log}', [ActivityLogController::class, 'show'])->name('historique.show');

    // ==================== UTILISATEURS (SD uniquement) ====================
    Route::middleware(['auth'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::patch('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // ==================== PROCÉDURES - MILITAIRES ====================
Route::patch('/procedures/{procedure}/militaire/{procedureMilitaire}/update', [ProcedureController::class, 'updateMilitaireInfo'])
    ->name('procedure.militaire.update');
Route::patch('/procedures/{procedure}/militaire/{procedureMilitaire}/infractions', [ProcedureController::class, 'updateMilitaireInfractions'])
    ->name('procedure.militaire.infractions.update');
Route::patch('/procedures/{procedure}/militaire/{procedureMilitaire}/fautes', [ProcedureController::class, 'updateMilitaireFautes'])
    ->name('procedure.militaire.fautes.update');
Route::patch('/procedures/{procedure}/militaire/{procedureMilitaire}/parties-civiles', [ProcedureController::class, 'updateMilitairePartiesCiviles'])
    ->name('procedure.militaire.parties-civiles.update');
    // ==================== PROCÉDURES - MILITAIRES ====================
Route::post('/procedures/{procedure}/militaire/ajouter', [ProcedureController::class, 'ajouterMilitaire'])
    ->name('procedures.militaire.ajouter');
Route::patch('/procedures/{procedure}/militaire/{procedureMilitaire}/update', [ProcedureController::class, 'updateMilitaireInfo'])
    ->name('procedure.militaire.update');
Route::patch('/procedures/{procedure}/militaire/{procedureMilitaire}/infractions', [ProcedureController::class, 'updateMilitaireInfractions'])
    ->name('procedure.militaire.infractions.update');
Route::patch('/procedures/{procedure}/militaire/{procedureMilitaire}/fautes', [ProcedureController::class, 'updateMilitaireFautes'])
    ->name('procedure.militaire.fautes.update');
Route::patch('/procedures/{procedure}/militaire/{procedureMilitaire}/parties-civiles', [ProcedureController::class, 'updateMilitairePartiesCiviles'])
    ->name('procedure.militaire.parties-civiles.update');
    // ==================== EXPORTS EXCEL ====================
    Route::get('/exports/infractions/armee', [ExportController::class, 'infractionsParArmee'])->name('exports.infractions.armee');
    Route::get('/exports/infractions/categorie-grade', [ExportController::class, 'infractionsParCategorieGrade'])->name('exports.infractions.categorie-grade');
    Route::get('/exports/infractions/grade', [ExportController::class, 'infractionsParGrade'])->name('exports.infractions.grade');
    Route::get('/exports/infractions/genre', [ExportController::class, 'infractionsParGenre'])->name('exports.infractions.genre');
    Route::get('/exports/fautes/armee', [ExportController::class, 'fautesParArmee'])->name('exports.fautes.armee');
    Route::get('/exports/fautes/categorie-grade', [ExportController::class, 'fautesParCategorieGrade'])->name('exports.fautes.categorie-grade');
    Route::get('/exports/fautes/grade', [ExportController::class, 'fautesParGrade'])->name('exports.fautes.grade');
    Route::get('/exports/fautes/genre', [ExportController::class, 'fautesParGenre'])->name('exports.fautes.genre');
    Route::get('/exports/top-infractions', [ExportController::class, 'topInfractions'])->name('exports.top-infractions');
    Route::get('/exports/top-fautes', [ExportController::class, 'topFautes'])->name('exports.top-fautes');
});

require __DIR__.'/auth.php';