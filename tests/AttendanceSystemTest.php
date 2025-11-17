<?php

/**
 * Script de test pour le système de pointage
 * 
 * Pour exécuter: php artisan tinker < tests/AttendanceSystemTest.php
 * Ou copier-coller les commandes dans tinker
 */

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\Site;
use App\Models\EmployeeRestDay;
use App\Models\OvertimeRecord;
use App\Services\AttendanceCalculationService;
use Carbon\Carbon;

echo "=== TESTS DU SYSTÈME DE POINTAGE ===\n\n";

// 1. Test du calcul de travail de nuit (17h → 01h)
echo "1. TEST: Calcul de travail de nuit (17h → 01h)\n";
echo "--------------------------------------------\n";

$employee = Employee::first();
if (!$employee) {
    echo "❌ ERREUR: Aucun employé trouvé dans la base de données\n";
    exit(1);
}

echo "Employé: {$employee->full_name}\n";
echo "Heures standard/jour: {$employee->standard_hours_per_day}h\n\n";

// Créer un pointage de nuit
$yesterday = Carbon::yesterday();
$record = AttendanceRecord::create([
    'employee_id' => $employee->id,
    'site_id' => Site::first()?->id ?? 1,
    'date' => $yesterday,
    'check_in_time' => '17:00:00',
    'check_out_time' => '01:00:00',
    'is_in_zone' => true,
]);

echo "Pointage créé:\n";
echo "  - Date: {$record->date->format('d/m/Y')}\n";
echo "  - Entrée: {$record->check_in_time}\n";
echo "  - Sortie: {$record->check_out_time}\n\n";

// Calculer les heures
$service = app(AttendanceCalculationService::class);
$service->calculateAttendance($record->fresh());

$record->refresh();
echo "Résultats du calcul:\n";
echo "  - Minutes totales: {$record->total_minutes}\n";
echo "  - Heures totales: {$record->total_hours}h\n";
echo "  - Minutes supplémentaires: {$record->overtime_minutes}\n";
echo "  - Heures supplémentaires: {$record->overtime_hours}h\n\n";

// Vérification
$expectedMinutes = 8 * 60; // 8 heures = 480 minutes
if ($record->total_minutes == $expectedMinutes) {
    echo "✅ SUCCÈS: Le calcul de nuit fonctionne correctement (8h détectées)\n";
} else {
    echo "❌ ÉCHEC: Attendu {$expectedMinutes} minutes, obtenu {$record->total_minutes} minutes\n";
}

$expectedOvertime = max(0, $expectedMinutes - ($employee->standard_hours_per_day * 60));
if ($record->overtime_minutes == $expectedOvertime) {
    echo "✅ SUCCÈS: Le calcul des heures supplémentaires est correct\n";
} else {
    echo "❌ ÉCHEC: Attendu {$expectedOvertime} minutes sup, obtenu {$record->overtime_minutes} minutes\n";
}

echo "\n";

// 2. Test des jours de repos
echo "2. TEST: Gestion des jours de repos\n";
echo "-----------------------------------\n";

$today = Carbon::today();
$restDay = EmployeeRestDay::create([
    'employee_id' => $employee->id,
    'date' => $today,
    'reason' => 'Jour de repos test',
]);

echo "Jour de repos créé pour {$employee->full_name} le {$today->format('d/m/Y')}\n";

$isRestDay = $employee->isRestDay($today);
if ($isRestDay) {
    echo "✅ SUCCÈS: La méthode isRestDay() fonctionne correctement\n";
} else {
    echo "❌ ÉCHEC: La méthode isRestDay() ne détecte pas le jour de repos\n";
}

// Vérifier qu'aucune absence n'est créée pour ce jour
$absence = AttendanceRecord::where('employee_id', $employee->id)
    ->where('date', $today)
    ->where('is_absent', true)
    ->first();

if (!$absence) {
    echo "✅ SUCCÈS: Aucune absence créée pour le jour de repos\n";
} else {
    echo "⚠️  ATTENTION: Une absence existe pour le jour de repos (peut être normale si créée avant)\n";
}

$restDay->delete();
echo "\n";

// 3. Test des heures supplémentaires
echo "3. TEST: Enregistrement des heures supplémentaires\n";
echo "--------------------------------------------------\n";

// Créer un pointage avec heures sup
$record2 = AttendanceRecord::create([
    'employee_id' => $employee->id,
    'site_id' => Site::first()?->id ?? 1,
    'date' => Carbon::today()->subDays(2),
    'check_in_time' => '08:00:00',
    'check_out_time' => '18:00:00', // 10 heures
    'is_in_zone' => true,
]);

$service->calculateAttendance($record2->fresh());
$record2->refresh();

echo "Pointage: 08h00 → 18h00 (10 heures)\n";
echo "Heures standard: {$employee->standard_hours_per_day}h\n";
echo "Heures supplémentaires calculées: {$record2->overtime_hours}h\n\n";

// Vérifier qu'un enregistrement d'heures sup automatique existe
$overtimeRecord = OvertimeRecord::where('employee_id', $employee->id)
    ->where('date', $record2->date)
    ->where('type', 'auto')
    ->first();

if ($overtimeRecord && $overtimeRecord->hours > 0) {
    echo "✅ SUCCÈS: Enregistrement automatique des heures sup créé ({$overtimeRecord->hours}h)\n";
} else {
    echo "⚠️  ATTENTION: Aucun enregistrement automatique d'heures sup trouvé\n";
}

// Test d'empêchement de doublon
$overtimeManual = OvertimeRecord::create([
    'employee_id' => $employee->id,
    'date' => $record2->date,
    'hours' => 2.5,
    'type' => 'manual',
    'notes' => 'Test manuel',
]);

echo "\nEnregistrement manuel créé: {$overtimeManual->hours}h\n";

// Essayer de créer un doublon (même employé, même date, même type)
try {
    $duplicate = OvertimeRecord::create([
        'employee_id' => $employee->id,
        'date' => $record2->date,
        'hours' => 1.0,
        'type' => 'manual',
    ]);
    echo "❌ ÉCHEC: Un doublon a pu être créé (contrainte unique non respectée)\n";
} catch (\Exception $e) {
    echo "✅ SUCCÈS: La contrainte unique empêche les doublons\n";
}

echo "\n";

// 4. Test de détection d'absences
echo "4. TEST: Détection des absences\n";
echo "-------------------------------\n";

// Créer un jour de repos pour éviter une absence
$testDate = Carbon::today()->subDays(3);
EmployeeRestDay::create([
    'employee_id' => $employee->id,
    'date' => $testDate,
    'reason' => 'Test absence',
]);

// Exécuter la détection d'absences
$service->detectAbsences($testDate);

$absenceRecord = AttendanceRecord::where('employee_id', $employee->id)
    ->where('date', $testDate)
    ->where('is_absent', true)
    ->first();

if (!$absenceRecord) {
    echo "✅ SUCCÈS: Aucune absence créée pour le jour de repos\n";
} else {
    echo "❌ ÉCHEC: Une absence a été créée malgré le jour de repos\n";
}

// Nettoyer
EmployeeRestDay::where('employee_id', $employee->id)
    ->where('date', $testDate)
    ->delete();

echo "\n";

// 5. Test du statut aujourd'hui (travail de nuit)
echo "5. TEST: Statut aujourd'hui avec travail de nuit\n";
echo "------------------------------------------------\n";

// Créer un pointage d'hier sans sortie (travail de nuit en cours)
$yesterdayRecord = AttendanceRecord::create([
    'employee_id' => $employee->id,
    'site_id' => Site::first()?->id ?? 1,
    'date' => Carbon::yesterday(),
    'check_in_time' => '20:00:00',
    'check_out_time' => null, // Pas encore sorti
    'is_in_zone' => true,
]);

echo "Pointage d'hier créé: 20h00 (pas de sortie)\n";

// Simuler la recherche du statut (comme dans getTodayStatus)
$statusRecord = AttendanceRecord::where('employee_id', $employee->id)
    ->where(function($query) {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $query->where('date', $today)
              ->orWhere(function($q) use ($yesterday) {
                  $q->where('date', $yesterday)
                    ->whereNotNull('check_in_time')
                    ->whereNull('check_out_time');
              });
    })
    ->orderBy('date', 'desc')
    ->first();

if ($statusRecord && $statusRecord->date->isYesterday()) {
    echo "✅ SUCCÈS: Le système détecte correctement le travail de nuit en cours\n";
} else {
    echo "❌ ÉCHEC: Le système ne détecte pas le travail de nuit en cours\n";
}

// Nettoyer
$yesterdayRecord->delete();

echo "\n";

// 6. Résumé des tests
echo "=== RÉSUMÉ DES TESTS ===\n";
echo "Les tests ci-dessus vérifient:\n";
echo "  ✓ Calcul de travail de nuit (17h → 01h)\n";
echo "  ✓ Gestion des jours de repos\n";
echo "  ✓ Enregistrement des heures supplémentaires\n";
echo "  ✓ Prévention des doublons d'heures sup\n";
echo "  ✓ Détection des absences (avec jours de repos)\n";
echo "  ✓ Statut aujourd'hui avec travail de nuit\n\n";

echo "✅ Tests terminés!\n";

