<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Models\AdminSelectedDiagnosis;
use App\Models\Allegory;
use App\Models\ChronicDisease;
use App\Models\City;
use App\Models\Department;
use App\Models\Diagnosis;
use App\Models\Document;
use App\Models\MedicalDevice;
use App\Models\Medication;
use App\Models\NormalParameter;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PatientNormalParameter;
use App\Models\Region;
use App\Models\Service;
use App\Models\Survey;
use App\Services\PatientService;
use App\Traits\HandlesStaffAvailability;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    use HandlesStaffAvailability;

    protected PatientService $service;

    public function __construct(PatientService $service)
    {
        $this->middleware(['permission:view_patients'])->only('index');
        $this->middleware(['permission:create_patients'])->only(['create', 'store']);
        $this->middleware(['permission:edit_patients'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_patients'])->only('destroy');

        $this->service = $service;
    }

    public function index(Request $request): View
    {
        $departments = Department::query()->select(['id', 'name'])->get();

        $today = Carbon::parse($date ?? now());
        $staffs = $this->scopeAvailableStaffOnDate(1);

        $patients = Patient::query()
            ->select(
                [
                    'id',
                    'first_name',
                    'last_name',
                    'middle_name',
                    'birth_date',
                    'gender',
                    'citizenship',
                    'passport_series',
                    'created_at'
                ]
            )
            ->with('examinationMaps')
            ->orderBy('id', 'desc')
            ->when($request->filled('id'), function ($query) use ($request) {
                $query->where('id', 'ILIKE', '%' . $request->input('id') . '%');
            })
//            ->when($request->filled('full_name'), function ($query) use ($request) {
//                $term = $request->input('full_name');
//                $query->where(
//                    \DB::raw("CONCAT_WS(' ', first_name, last_name, middle_name)"),
//                    'ILIKE',
//                    '%' . $term . '%'
//                );
//            })
            ->when($request->filled('first_name'), function ($query) use ($request) {
                $query->where('first_name', 'ILIKE', $request->input('first_name'));
            })
            ->when($request->filled('second_name'), function ($query) use ($request) {
                $query->where('last_name', 'ILIKE', $request->input('second_name'));
            })
            ->when($request->filled('middle_name'), function ($query) use ($request) {
                $query->where('middle_name', 'ILIKE', $request->input('middle_name'));
            })
            ->when($request->filled('birth_date'), function ($query) use ($request) {
                $query->where('birth_date', '>=', $request->input('birth_date'));
            })
            ->when($request->filled('department_id'), function ($query) use ($request) {
                $query->whereHas('examinationMaps', function ($q) use ($request) {
                    $q->where('is_active', true)->where('department_id', $request->input('department_id'));
                });
            })
            ->when($request->filled('staff_id'), function ($query) use ($request) {
                $query->whereHas('examinationMaps', function ($q) use ($request) {
                    $q->where('is_active', true)->where('staff_id', $request->input('staff_id'));
                });
            })
            ->paginate(10);

        return view('admin.patients.index', compact('patients', 'departments', 'staffs'));
    }

    public function show(Patient $patient): View
    {
        // last view
        $admin = auth()->guard('admin')->user();

        if ($admin) {
            $lastViewsQuery = $admin->lastViewed();

            if (!$lastViewsQuery->where('patient_id', $patient->id)->exists()) {
                $lastViewsCount = $lastViewsQuery->count();

                if ($lastViewsCount >= 10) {
                    $oldest = $lastViewsQuery->orderBy('pivot_created_at')->limit($lastViewsCount - 9)->get();

                    foreach ($oldest as $old) {
                        $admin->lastViewed()->detach($old->id);
                    }
                }

                $admin->lastViewed()->attach($patient->id);
            }

            $lastViews = $admin->lastViewed;
        }

        $patient->load([
            'examinationMaps' => function ($query) {
                $query->with(['department', 'doctor', 'services', 'diagnosises'])
                    ->orderByDesc('admission_date')
                    ->get()
                    ->each(function ($map) {
                        $map->admission_date = Carbon::parse($map->admission_date);
                        $map->discharge_date = $map->discharge_date
                            ? Carbon::parse($map->discharge_date)
                            : null;
                    });
            },
            'assignments' => function ($query) {
                $query->with(['service', 'staff'])
                    ->orderByDesc('created_at')
                    ->get()
                    ->each(function ($assignment) {
                        $assignment->created_at = Carbon::parse($assignment->created_at);
                    });
            },
            'services' => function ($query) {
                $query->orderByDesc('created_at')
                    ->get()
                    ->each(function ($service) {
                        $service->created_at = Carbon::parse($service->created_at);
                    });
            },
            'receptions' => function ($query) {
                $query->orderByDesc('created_at')
                    ->get()
                    ->each(function ($reception) {
                        $reception->created_at = Carbon::parse($reception->created_at);
                    });
            },
            'surveyResponses' => function ($query) {
                $query->orderByDesc('created_at')
                    ->get()
                    ->each(function ($survey) {
                        $survey->created_at = Carbon::parse($survey->created_at);
                    });
            },
            'diagnosises' => function ($query) {
                $query->orderByDesc('created_at')
                    ->get()
                    ->each(function ($diagnosis) {
                        $diagnosis->created_at = Carbon::parse($diagnosis->created_at);
                    });
            },
            'documentResponse' => function ($query) {
                $query->orderByDesc('created_at')
                    ->get()
                    ->each(function ($document) {
                        $document->created_at = Carbon::parse($document->created_at);
                    });
            },
            'medications' => function ($query) {
                $query->orderByDesc('created_at')
                    ->get()
                    ->each(function ($medication) {
                        $medication->created_at = Carbon::parse($medication->created_at);
                    });
            },
        ]);

        $patientDetails = [
            'basic_info' => [
                'full_name' => "{$patient->last_name} {$patient->first_name} {$patient->middle_name}",
                'birth_date' => $patient->birth_date,
                'gender' => $patient->gender === 'male' ? 'Мужской' : 'Женский',
                'citizenship' => $patient->citizenship,
            ],
            'contact_info' => [
                'phone' => $patient->phone,
                'additional_phone' => $patient->additional_phone,
                'email' => $patient->email,
                'residential_address' => "{$patient->residential_address_street}, {$patient->residential_address_house}, квартира {$patient->residential_address_apartment}",
                'registered_address' => "{$patient->registered_address_street}, {$patient->registered_address_house}, квартира {$patient->registered_address_apartment}",
            ],
            'insurance_info' => [
                'policy_number' => $patient->insurance_policy_number ?? 'Не указана',
                'company' => $patient->insurance_company ?? 'Не указана',
                'type' => $patient->insurance_type ?? 'Не указана',
                'expiry_date' => $patient->insurance_expiry_date ?? 'Не указана',
            ],
            'emergency_contact' => [
                'full_name' => $patient->emergency_contact_full_name,
                'relation' => $patient->emergency_contact_relation,
                'phone' => $patient->emergency_contact_phone,
                'address' => $patient->emergency_contact_address,
            ],
            'medical_info' => [
                'blood_type' => $patient->blood_type,
                'rh_factor' => $patient->rh_factor ? ($patient->rh_factor === '+' ? 'Положительный' : 'Отрицательный') : 'Не указан',
                'special_instructions' => $patient->special_instructions,
            ],
            'related_data' => [
                'allegories' => $patient->allegories,
                'devices' => $patient->devices,
                'diseases' => $patient->diseases,
                'doctors' => $patient->doctors,
            ],
            'history' => [
                'map_is_active' => $patient->examinationMaps()->where('is_active', 1)->count() >= 1 ? false : true,
                'surveyResponses' => $patient->surveyResponses,
            ],
            'patient_id' => $patient->id,
            'services' => Service::query()->get(),
            'surveys' => Survey::query()->get(),

            'diagnosises' => $patient->diagnosises,
        ];

        $timeline = collect();

        foreach ($patient->examinationMaps as $map) {
            $timeline->push([
                'type' => 'examination_map',
                'date' => $map->admission_date,
                'end_date' => $map->discharge_date,
                'data' => $map,
                'sort_date' => Carbon::parse($map->admission_date)
            ]);
        }

        // Обработка направление
        foreach ($patient->assignments as $assignment) {
            $timeline->push([
                'type' => 'assignment',
                'date' => $assignment->created_at,
                'data' => $assignment,
                'sort_date' => $assignment->created_at
            ]);
        }

        // Обработка услугу
        foreach ($patient->services as $service) {
            $timeline->push([
                'type' => 'service',
                'date' => $service->created_at,
                'data' => $service,
                'sort_date' => $service->created_at
            ]);
        }

        // Обработка мед документа
        foreach ($patient->surveyResponses as $response) {
            $timeline->push([
                'type' => 'patient_med_document',
                'date' => $response->created_at,
                'data' => $response,
                'sort_date' => $response->created_at
            ]);
        }

        // Обработка мед документа
        foreach ($patient->receptions as $reception) {
            $timeline->push([
                'type' => 'reception',
                'date' => $reception->created_at,
                'data' => $reception,
                'sort_date' => $reception->created_at
            ]);
        }

        // Обработка диагноза
        foreach ($patient->diagnosises as $diagnosis) {
            $timeline->push([
                'type' => 'diagnosis',
                'date' => $diagnosis->created_at,
                'data' => $diagnosis,
                'sort_date' => $diagnosis->created_at
            ]);
        }

        foreach ($patient->documentResponse as $document) {
            $timeline->push([
                'type' => 'document',
                'date' => $document->created_at,
                'data' => $document,
                'sort_date' => $document->created_at
            ]);
        }

        foreach (PatientMedication::query()->where('patient_id', $patient->id)->get() as $medication) {
            $timeline->push([
                'type' => 'medication',
                'date' => $medication->created_at,
                'data' => $medication,
                'sort_date' => $medication->created_at
            ]);
        }

        // Сортировка по дате
        $patientDetails['timeline'] = $timeline->sortByDesc('sort_date');
        $departments = Department::query()->select(['id', 'name'])->get();

        $documents = Document::query()->where('id', '!=', 1)->get();

        $medications = Medication::query()->get();

        $admin = Auth::guard('admin')->user();

        $favoriteDiagnosisIds = AdminSelectedDiagnosis::where('admin_id', $admin->id)
            ->pluck('diagnosis_id')
            ->toArray();

        $favoriteDiagnoses = Diagnosis::whereIn('id', $favoriteDiagnosisIds)->get();

        $regularDiagnoses = Diagnosis::whereNotIn('id', $favoriteDiagnosisIds)->get();

        $normalParameters = NormalParameter::query()->get();
        $patientNormalParameters = PatientNormalParameter::where('patient_id', $patientDetails['patient_id'])->get();

        return view(
            'admin.patients.show',
            compact(
                'patientDetails',
                'lastViews',
                'departments',
                'documents',
                'medications',
                'favoriteDiagnoses',
                'regularDiagnoses',
                'normalParameters',
                'patientNormalParameters'
            )
        );
    }

    public function create(): View
    {
        $regions = Region::query()->get();
        $cities = City::query()->get();
        $allegories = Allegory::query()->get();
        $devices = MedicalDevice::query()->get();
        $diseases = ChronicDisease::query()->get();

        return view('admin.patients.create', compact('regions', 'cities', 'allegories', 'devices', 'diseases'));
    }

    public function store(PatientRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('patients.index')->with('success', 'Пациент устройство успешно добавлена!');
    }

    public function edit(Patient $patient): View
    {
        $regions = Region::query()->get();
        $cities = City::query()->get();
        $allegories = Allegory::query()->get();
        $devices = MedicalDevice::query()->get();
        $diseases = ChronicDisease::query()->get();
        $maps = $patient->examinationMaps;

        return view(
            'admin.patients.edit',
            compact('patient', 'regions', 'cities', 'allegories', 'devices', 'diseases', 'maps')
        );
    }

    public function update(PatientRequest $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($patient, (array)$validated);

        return redirect()->route('patients.index')->with('success', 'Пациент устройство  успешно обновлена!');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->service->destroy($patient);

        return redirect()->route('patients.index')->with('success', 'Пациент устройство успешно удалена!');
    }

    public function storeNormalParameter(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'parameters' => 'required|array',
        ]);

        $patientId = $validated['patient_id'];

        foreach ($validated['parameters'] as $normalParameterId => $normalValue) {
            if ($normalValue === null) {
                continue; // Пропускаем пустые
            }

            PatientNormalParameter::updateOrCreate(
                [
                    'patient_id' => $patientId,
                    'normal_parameter_id' => $normalParameterId,
                ],
                [
                    'normal' => $normalValue,
                ]
            );
        }

        return redirect()->back()->with('success', 'Показатели успешно сохранены!');
    }
}
