<?php

/*
|--------------------------------------------------------------------------
| Dashboard AJAX endpoints
|--------------------------------------------------------------------------
|
| JSON (and HTML partial) endpoints called by the jQuery / fetch code of the
| dashboard pages. They are not a public API: bootstrap/app.php loads this file
| into the "web" middleware group behind auth:admin, under the /api prefix and
| the "api." name prefix, so they share the admin session and CSRF protection
| with the pages that call them. The /api/... URLs are hard-coded in the views.
|
| Every route requires the permission of the page (or the @can section of the
| page) that calls it, so a role that can open a page can use all of it. The
| booking calendars' routes that name an employee or an appointment also pass
| ReceptionPolicy: a doctor's account works only on its own calendar.
|
*/

use App\Http\Controllers\Dashboard\Api\DiagnosisController;
use App\Http\Controllers\Dashboard\Api\DocumentScoreController;
use App\Http\Controllers\Dashboard\Api\ExaminationMapController;
use App\Http\Controllers\Dashboard\Api\MedicationController;
use App\Http\Controllers\Dashboard\Api\PatientController;
use App\Http\Controllers\Dashboard\Api\PatientHistoryController;
use App\Http\Controllers\Dashboard\Api\PatientMedicationController;
use App\Http\Controllers\Dashboard\Api\PatientServiceController;
use App\Http\Controllers\Dashboard\Api\ReceptionController;
use App\Http\Controllers\Dashboard\Api\ScoreFinishedController;
use App\Http\Controllers\Dashboard\Api\SelectedDiagnosisController;
use App\Http\Controllers\Dashboard\Api\ServiceController;
use App\Http\Controllers\Dashboard\Api\StaffController;
use App\Http\Controllers\Dashboard\Api\SurveyController;
use App\Http\Controllers\Dashboard\Api\SurveyResponseController;
use App\Http\Controllers\Dashboard\Api\TransferController;
use App\Http\Controllers\Dashboard\PatientFileController;
use App\Models\Reception;
use Illuminate\Support\Facades\Route;

// Patient card (patients/show): its history partials
Route::post('admin/patient/history/partial', [PatientHistoryController::class, 'partial'])
    ->middleware('permission:view_patients')
    ->name('patients.history.partial');

// The status select of a service in the patient card history: changing an order is editing it.
Route::patch('patient/service/update-status/{service}', [PatientServiceController::class, 'changeStatus'])
    ->middleware('permission:edit_patient_services')
    ->name('patient-services.update-status');

Route::get('service/by-type/{type}', [ServiceController::class, 'getByType'])
    ->middleware('permission:create_patient_services')
    ->name('services.by-type');

Route::get('diagnoses', [DiagnosisController::class, 'search'])
    ->middleware('permission:create_patient_diagnoses')
    ->name('diagnoses.search');

Route::post('selected-diagnosis', [SelectedDiagnosisController::class, 'store'])
    ->middleware('permission:create_patient_diagnoses')
    ->name('selected-diagnoses.toggle');

// Cancelling or extending a prescription changes it (a cancelled dispensed drug returns to stock).
Route::middleware('permission:edit_patient_medication')->group(function (): void {
    Route::post('patient-medication/{medication}/cancel', [PatientMedicationController::class, 'cancel'])
        ->name('patient-medications.cancel');
    Route::post('patient-medication/{medication}/extend', [PatientMedicationController::class, 'extend'])
        ->name('patient-medications.extend');
});

// Patient edit form: attached documents
Route::delete('patients/{patient}/files/{file}', [PatientFileController::class, 'destroy'])
    ->scopeBindings()
    ->middleware('permission:edit_patients')
    ->name('patients.files.destroy');

// Medical documents: patient card, examination map pages (reached from the patient card and
// the patient edit form), the examination map edit form and the examination-map survey page
Route::middleware('permission:view_patients|edit_patients|edit_maps|create_survey_response')->group(function (): void {
    Route::get('surveys/{survey}/questions', [SurveyController::class, 'getQuestions'])
        ->name('surveys.questions');
    Route::get('surveys-response/show/{response}', [SurveyResponseController::class, 'show'])
        ->name('survey-responses.show');
});

// Medication pickers: patient card, prescription edit, examination maps, patient services
Route::get('medication/show/{medication}', [MedicationController::class, 'show'])
    ->middleware('permission:view_patients|edit_patients|create_patient_map|edit_maps|create_survey_response|create_patient_services|edit_patient_services')
    ->name('medications.show');
Route::get('medication/active-substance/{substance}', [MedicationController::class, 'medicationByActiveSubstance'])
    ->middleware('permission:view_patients|edit_patients|create_patient_map|edit_maps|edit_patient_medication')
    ->name('medications.by-active-substance');

// Patient service forms
Route::get('examination-map-by-patient/{patient}', [ExaminationMapController::class, 'mapByPatient'])
    ->middleware('permission:create_patient_services|edit_patient_services')
    ->name('examination-maps.by-patient');

// Reception booking: receptions/appointment and receptions/calendar. The ids are numbers: anything
// else is a 404, not a database error on the bigint key. After the permission, a route naming an
// employee or an appointment passes ReceptionPolicy; a booking names its employee in the body
// (ReceptionRequest asks the policy), and the doctors of a service are the calendars it allows.
Route::middleware('permission:view_appointment_receptions')->group(function (): void {
    Route::get('staff-by-service/{service}', [ReceptionController::class, 'staffByService'])
        ->whereNumber('service')
        ->name('staff.by-service');
    Route::get('staff-availability-today/{staff}', [StaffController::class, 'availabilityToday'])
        ->whereNumber('staff')
        ->can('workOnCalendar', [Reception::class, 'staff'])
        ->name('staff.availability-today');
    Route::get('staff-booked-list-today/{staff}', [StaffController::class, 'bookedToday'])
        ->whereNumber('staff')
        ->can('workOnCalendar', [Reception::class, 'staff'])
        ->name('staff.booked-today');
});

Route::get('staff-availability-week/{staff}', [StaffController::class, 'availabilityWeek'])
    ->whereNumber('staff')
    ->middleware('permission:view_reception_doctor_calendar')
    ->can('workOnCalendar', [Reception::class, 'staff'])
    ->name('staff.availability-week');

Route::get('receptions/{reception}', [ReceptionController::class, 'show'])
    ->middleware('permission:view_appointment_receptions|view_reception_doctor_calendar')
    ->can('workOnCalendar', 'reception')
    ->name('receptions.show');

// Patient picker: the booking dialogs of both calendars and the patient service form
Route::get('patients', [PatientController::class, 'get'])
    ->middleware('permission:view_appointment_receptions|view_reception_doctor_calendar|create_patient_services|edit_patient_services')
    ->name('patients.index');

Route::post('receptions', [ReceptionController::class, 'store'])
    ->middleware('permission:create_receptions')
    ->name('receptions.store');
Route::put('reception-status-update/{reception}', [ReceptionController::class, 'changeStatus'])
    ->middleware('permission:change_receptions_status')
    ->can('workOnCalendar', 'reception')
    ->name('receptions.update-status');

// Nurse window (sister_window/show)
Route::middleware('permission:sister_window_show')->group(function (): void {
    // The nurse window passes the id of the filled-in document; its stored total picks the range.
    // DocumentResponsePolicy (in the controller and the request) accepts only a sheet of an open
    // stay the account's nurse window shows.
    Route::get('document-scores/{document}/{response}', [DocumentScoreController::class, 'getActions'])
        ->scopeBindings()
        ->name('document-scores.actions');
    Route::post('document/finish/action', [ScoreFinishedController::class, 'finish'])
        ->name('document-actions.finish');
});

// Transfer list (transfers/index)
Route::patch('transfers/{transfer}/update-status', [TransferController::class, 'updateStatus'])
    ->middleware('permission:edit_transfers')
    ->name('transfers.update-status');
