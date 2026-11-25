<?php

namespace Database\Seeders;

use App\Enums\EquipmentStatus;
use App\Enums\PatientMedicationStatus;
use App\Enums\QuestionType;
use App\Enums\ReceptionStatus;
use App\Enums\ScheduleType;
use App\Enums\ServiceStatus;
use App\Models\Admin;
use App\Models\Allergy;
use App\Models\ChronicDisease;
use App\Models\City;
use App\Models\DangerousCombination;
use App\Models\Department;
use App\Models\Diagnosis;
use App\Models\Document;
use App\Models\DocumentQuestion;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\ExaminationMap;
use App\Models\ExaminationMapVitalParameter;
use App\Models\Floor;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MainWarehouseMedication;
use App\Models\Manufacturer;
use App\Models\MedicalDevice;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\MedicationGroup;
use App\Models\Office;
use App\Models\Patient;
use App\Models\PatientDiagnosis;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Pharmacy;
use App\Models\PharmacyType;
use App\Models\Procedure;
use App\Models\Reception;
use App\Models\Region;
use App\Models\RoomBed;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\ServiceMethod;
use App\Models\ServiceType;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\Supplier;
use App\Models\SupplyRequest;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyType;
use App\Models\Transfer;
use App\Models\TransferMedication;
use App\Models\VitalParameter;
use App\Models\WardRoom;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseType;
use App\Services\Clinical\DocumentResponseService;
use App\Services\Clinical\PatientMedicationService;
use App\Services\Clinical\Scoring\MedicalScale;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * A small, realistic hospital for local development: structure, staff with schedules (every day,
 * so doctors can always be booked today), patients, price list, an ICD-10 subset, pharmacy stock
 * and some clinical activity. Runs once: it skips itself when the database already has patients.
 */
class DemoSeeder extends Seeder
{
    /** Shared by the demo logins; this seeder only runs in the local environment. */
    private const string PASSWORD = 'password';

    /** Length of an appointment slot for every demo doctor, in minutes. */
    private const int SLOT_MINUTES = 20;

    /** Slots in the doctors' 09:00-13:00 reception hours. */
    private const int RECEPTION_SLOTS = 4 * 60 / self::SLOT_MINUTES;

    public function run(PatientMedicationService $prescriptions): void
    {
        if (Patient::query()->exists()) {
            $this->command?->warn('Demo data skipped: the database already has patients.');

            return;
        }

        DB::transaction(function () use ($prescriptions): void {
            $cities = $this->seedGeography();
            $departments = $this->seedHospitalStructure();
            [$consultations, $services, $procedures] = $this->seedPriceList();
            $doctors = $this->seedStaff($departments, $consultations);
            $diagnoses = $this->seedDiagnoses();
            $medications = $this->seedPharmacy($departments->firstOrFail());
            $patients = $this->seedPatients($cities);

            $this->seedInpatients($patients->take(12), $doctors, $diagnoses, $services, $procedures, $medications, $prescriptions);
            $this->seedOutpatients($patients->slice(12)->values(), $doctors, $diagnoses, $services);
            $this->seedCatalogues($doctors->firstOrFail(), $patients->firstOrFail());
        });
    }

    /**
     * @return Collection<int, City>
     */
    private function seedGeography(): Collection
    {
        $geography = [
            'Ташкент' => ['Ташкент'],
            'Ташкентская область' => ['Чирчик', 'Ангрен', 'Алмалык'],
            'Самаркандская область' => ['Самарканд', 'Каттакурган'],
            'Ферганская область' => ['Фергана', 'Коканд', 'Маргилан'],
            'Бухарская область' => ['Бухара'],
        ];

        return collect($geography)->flatMap(function (array $cities, string $region) {
            $region = Region::factory()->create(['name' => $region]);

            return collect($cities)->map(fn (string $city) => City::factory()->for($region)->create(['name' => $city]));
        });
    }

    /**
     * Four floors with offices, five departments with two three-bed wards each.
     *
     * @return Collection<int, Department>
     */
    private function seedHospitalStructure(): Collection
    {
        $floors = array_map(function (int $number): Floor {
            $floor = Floor::factory()->create(['name' => "{$number} этаж"]);

            foreach (range(1, 3) as $office) {
                Office::factory()->for($floor)->create(['name' => sprintf('Кабинет %d%02d', $number, $office)]);
            }

            return $floor;
        }, range(1, 4));

        $names = ['Терапевтическое', 'Кардиологическое', 'Неврологическое', 'Хирургическое', 'Педиатрическое'];

        return collect($names)->map(function (string $name, int $index) use ($floors) {
            $department = Department::factory()->create([
                'name' => "{$name} отделение",
                // Two departments per floor from the second floor up.
                'floor_id' => $floors[intdiv($index, 2) + 1]->id,
            ]);

            foreach (range(1, 2) as $room) {
                $ward = WardRoom::factory()->create(['room_number' => sprintf('%d%02d', $index + 2, $room)]);

                RoomBed::factory()
                    ->count(3)
                    ->sequence(fn (Sequence $sequence): array => ['bed_number' => (string) ($sequence->index + 1)])
                    ->create(['ward_room_id' => $ward->id]);

                $department->rooms()->attach($ward);
            }

            return $department;
        });
    }

    /**
     * Consultations (keyed by specialty), the other priced services and the procedures.
     *
     * @return array{Collection<string, Service>, Collection<int, Service>, Collection<int, Procedure>}
     */
    private function seedPriceList(): array
    {
        $types = ServiceType::query()->pluck('id', 'name');
        $groups = collect(['Консультации', 'Лабораторная диагностика', 'Функциональная диагностика', 'Лучевая диагностика'])
            ->mapWithKeys(fn (string $name): array => [$name => ServiceGroup::factory()->create(['name' => $name])->id]);

        $consultations = collect([
            'Терапевт' => 'Консультация терапевта',
            'Кардиолог' => 'Консультация кардиолога',
            'Невролог' => 'Консультация невролога',
            'Хирург' => 'Консультация хирурга',
            'Педиатр' => 'Консультация педиатра',
        ])->map(fn (string $name) => Service::factory()->appointment()->create([
            'name' => $name,
            'code' => null,
            'price' => 150_000,
            'service_type_id' => $types['Консультация'],
            'service_group_id' => $groups['Консультации'],
        ]));

        // [name, code, price in UZS, service type, service group]
        $catalogue = [
            ['Общий анализ крови', 'B03.016.002', 45_000, 'Лабораторное тестирование', 'Лабораторная диагностика'],
            ['Биохимический анализ крови', 'B03.016.004', 120_000, 'Лабораторное тестирование', 'Лабораторная диагностика'],
            ['Общий анализ мочи', 'B03.016.006', 35_000, 'Лабораторное тестирование', 'Лабораторная диагностика'],
            ['Глюкоза крови', 'A09.05.023', 25_000, 'Лабораторное тестирование', 'Лабораторная диагностика'],
            ['Электрокардиография', 'A05.10.006', 60_000, 'Инструментальные исследования', 'Функциональная диагностика'],
            ['Эхокардиография', 'A04.10.002', 180_000, 'Инструментальные исследования', 'Функциональная диагностика'],
            ['УЗИ органов брюшной полости', 'A04.16.001', 120_000, 'Инструментальные исследования', 'Функциональная диагностика'],
            ['Рентгенография органов грудной клетки', 'A06.09.007', 90_000, 'Инструментальные исследования', 'Лучевая диагностика'],
            ['КТ головного мозга', 'A06.23.004', 550_000, 'Инструментальные исследования', 'Лучевая диагностика'],
            ['МРТ поясничного отдела позвоночника', 'A05.03.002', 750_000, 'Инструментальные исследования', 'Лучевая диагностика'],
        ];

        $services = collect($catalogue)->map(fn (array $row) => Service::factory()->create([
            'name' => $row[0],
            'code' => $row[1],
            'price' => $row[2],
            'service_type_id' => $types[$row[3]],
            'service_group_id' => $groups[$row[4]],
        ]));

        $procedures = collect([
            'Внутривенная инъекция' => 20_000,
            'Внутримышечная инъекция' => 15_000,
            'Внутривенная капельная инфузия' => 45_000,
            'Перевязка' => 30_000,
            'Измерение артериального давления' => 10_000,
            'Лечебный массаж' => 80_000,
        ])->map(fn (int $price, string $name) => Procedure::factory()->create(['name' => $name, 'price' => $price]))->values();

        return [$consultations, $services, $procedures];
    }

    /**
     * Two doctors and a nurse per department, a head nurse, a head of department and the front
     * desk. Doctors offer the consultation of their specialty.
     *
     * @param  Collection<int, Department>  $departments
     * @param  Collection<string, Service>  $consultations  keyed by specialty, in department order
     * @return Collection<int, Staff> the doctors
     */
    private function seedStaff(Collection $departments, Collection $consultations): Collection
    {
        $specialties = collect([...$consultations->keys(), 'Медицинская сестра'])
            ->mapWithKeys(fn (string $name): array => [$name => Specialty::factory()->create(['name' => $name])->id]);
        $nurseSpecialty = $specialties['Медицинская сестра'];
        $mainDepartment = $departments->firstOrFail()->id;
        $consultationsBySpecialty = $consultations->all();
        $specialtyOrder = array_keys($consultationsBySpecialty);

        $doctors = $departments->flatMap(function (Department $department, int $index) use ($consultationsBySpecialty, $specialtyOrder, $specialties, $nurseSpecialty) {
            $specialty = $specialtyOrder[$index];

            Staff::factory()->nurse(['department_id' => $department->id, 'speciality_id' => $nurseSpecialty])->create();

            return Staff::factory()
                ->count(2)
                ->doctor([
                    'department_id' => $department->id,
                    'speciality_id' => $specialties[$specialty],
                    'admission_period' => self::SLOT_MINUTES,
                ])
                ->hasAttached($consultationsBySpecialty[$specialty], relationship: 'services')
                ->create();
        });

        Staff::factory()->withPosition('Старшая медсестра', ['department_id' => $mainDepartment, 'speciality_id' => $nurseSpecialty])->create();
        Staff::factory()->withPosition('Заведующий', ['department_id' => $mainDepartment, 'speciality_id' => $specialties['Терапевт']])->create();

        foreach (['Регистратор' => 'registrar@medsystem.test', 'Кассир' => 'cashier@medsystem.test'] as $position => $email) {
            $employee = Staff::factory()
                ->withPosition($position, ['department_id' => $mainDepartment, 'speciality_id' => $specialties['Терапевт']])
                ->create();

            Admin::factory()->forStaff($employee)->role($position)->create(['email' => $email, 'password' => self::PASSWORD]);
        }

        $this->seedWeeklySchedules($doctors->pluck('id'));

        return $doctors;
    }

    /**
     * Everyone works every day 08:00-17:00 and doctors also see patients 09:00-13:00,
     * so the demo always has someone available "today".
     *
     * @param  Collection<int, int>  $doctorIds
     */
    private function seedWeeklySchedules(Collection $doctorIds): void
    {
        $now = now();
        $rows = [];

        foreach (Staff::query()->pluck('id') as $staffId) {
            foreach (array_keys(DayOfWeekSeeder::DAYS) as $day) {
                $shift = ['staff_id' => $staffId, 'day_of_week_id' => $day, 'created_at' => $now, 'updated_at' => $now];

                $rows[] = [...$shift, 'type' => ScheduleType::Work, 'start_time' => '08:00', 'end_time' => '17:00'];

                if ($doctorIds->contains($staffId)) {
                    $rows[] = [...$shift, 'type' => ScheduleType::Reception, 'start_time' => '09:00', 'end_time' => '13:00'];
                }
            }
        }

        StaffSchedule::query()->insert($rows);
    }

    /**
     * @return Collection<int, Diagnosis>
     */
    private function seedDiagnoses(): Collection
    {
        $icd10 = [
            'A09' => 'Диарея и гастроэнтерит предположительно инфекционного происхождения',
            'B34.9' => 'Вирусная инфекция неуточнённая',
            'D50.9' => 'Железодефицитная анемия неуточнённая',
            'E03.9' => 'Гипотиреоз неуточнённый',
            'E11.9' => 'Инсулиннезависимый сахарный диабет без осложнений',
            'E66.0' => 'Ожирение, обусловленное избыточным поступлением энергетических ресурсов',
            'E78.0' => 'Чистая гиперхолестеринемия',
            'F41.1' => 'Генерализованное тревожное расстройство',
            'G40.9' => 'Эпилепсия неуточнённая',
            'G43.9' => 'Мигрень неуточнённая',
            'G44.2' => 'Головная боль напряжённого типа',
            'I10' => 'Эссенциальная (первичная) гипертензия',
            'I11.9' => 'Гипертензивная болезнь с преимущественным поражением сердца без сердечной недостаточности',
            'I20.8' => 'Другие формы стенокардии',
            'I21.9' => 'Острый инфаркт миокарда неуточнённый',
            'I25.1' => 'Атеросклеротическая болезнь сердца',
            'I48.0' => 'Пароксизмальная фибрилляция предсердий',
            'I50.0' => 'Застойная сердечная недостаточность',
            'I63.9' => 'Инфаркт мозга неуточнённый',
            'I83.9' => 'Варикозное расширение вен нижних конечностей без язвы или воспаления',
            'J00' => 'Острый назофарингит (насморк)',
            'J02.9' => 'Острый фарингит неуточнённый',
            'J06.9' => 'Острая инфекция верхних дыхательных путей неуточнённая',
            'J18.9' => 'Пневмония неуточнённая',
            'J20.9' => 'Острый бронхит неуточнённый',
            'J44.9' => 'Хроническая обструктивная лёгочная болезнь неуточнённая',
            'J45.9' => 'Астма неуточнённая',
            'K21.0' => 'Гастроэзофагеальный рефлюкс с эзофагитом',
            'K25.9' => 'Язва желудка, не уточнённая как острая или хроническая, без кровотечения или прободения',
            'K29.5' => 'Хронический гастрит неуточнённый',
            'K35.8' => 'Острый аппендицит, другой и неуточнённый',
            'K80.2' => 'Камни желчного пузыря без холецистита',
            'K81.1' => 'Хронический холецистит',
            'K85.9' => 'Острый панкреатит неуточнённый',
            'M17.9' => 'Гонартроз неуточнённый',
            'M42.1' => 'Остеохондроз позвоночника у взрослых',
            'M54.5' => 'Боль внизу спины',
            'N10' => 'Острый тубулоинтерстициальный нефрит',
            'N20.0' => 'Камни почки',
            'N39.0' => 'Инфекция мочевыводящих путей без установленной локализации',
            'R50.9' => 'Лихорадка неуточнённая',
            'Z00.0' => 'Общий медицинский осмотр',
        ];

        return collect($icd10)
            ->map(fn (string $name, string $code) => Diagnosis::factory()->create(['code' => $code, 'name' => $name]))
            ->values();
    }

    /**
     * The medication catalogue with stock in the main warehouse, a department warehouse,
     * the hospital pharmacy and the dangerous combinations checked when prescribing.
     *
     * @return Collection<string, Medication> keyed by name
     */
    private function seedPharmacy(Department $department): Collection
    {
        $manufacturers = collect([
            'Ташкентский фармацевтический завод' => 'Узбекистан',
            'Самаркандфарм' => 'Узбекистан',
            'Северфарм' => 'Россия',
            'Indus Pharma' => 'Индия',
            'Adler Pharma' => 'Германия',
        ])->map(fn (string $country, string $name) => Manufacturer::factory()->create([
            'name' => $name,
            'country_of_manufacture' => $country,
        ]))->values();

        $suppliers = collect(['ООО «МедСнаб»', 'ООО «ФармЛогистик»', 'ООО «Шифо Дистрибуция»'])
            ->map(fn (string $name) => Supplier::factory()->hasAttached($manufacturers->random(2))->create(['name' => $name]));

        // name => [release form, dosage, group, retail price per unit in UZS]
        $catalogue = [
            'Парацетамол' => ['Таблетка', '500 мг', 'Анальгетики и антипиретики', 400],
            'Метамизол натрия' => ['Ампула', '500 мг/мл, 2 мл', 'Анальгетики и антипиретики', 2_500],
            'Ибупрофен' => ['Таблетка', '400 мг', 'НПВС', 600],
            'Диклофенак' => ['Ампула', '75 мг/3 мл', 'НПВС', 3_000],
            'Аспирин' => ['Таблетка', '100 мг', 'Антикоагулянты и антиагреганты', 300],
            'Варфарин' => ['Таблетка', '2,5 мг', 'Антикоагулянты и антиагреганты', 900],
            'Гепарин' => ['Флакон', '5000 ЕД/мл, 5 мл', 'Антикоагулянты и антиагреганты', 45_000],
            'Амоксициллин' => ['Капсула', '500 мг', 'Антибиотики', 1_200],
            'Цефтриаксон' => ['Порошок', '1 г', 'Антибиотики', 9_000],
            'Кларитромицин' => ['Таблетка', '500 мг', 'Антибиотики', 4_500],
            'Эналаприл' => ['Таблетка', '10 мг', 'Антигипертензивные средства', 350],
            'Амлодипин' => ['Таблетка', '5 мг', 'Антигипертензивные средства', 500],
            'Спиронолактон' => ['Таблетка', '25 мг', 'Диуретики', 700],
            'Фуросемид' => ['Ампула', '10 мг/мл, 2 мл', 'Диуретики', 2_000],
            'Симвастатин' => ['Таблетка', '20 мг', 'Гиполипидемические средства', 800],
            'Метформин' => ['Таблетка', '850 мг', 'Противодиабетические средства', 600],
            'Омепразол' => ['Капсула', '20 мг', 'Антисекреторные средства', 700],
            'Лоратадин' => ['Таблетка', '10 мг', 'Антигистаминные средства', 500],
            'Натрия хлорид' => ['Раствор', '0,9%, 200 мл', 'Растворы для инфузий', 8_000],
            'Глюкоза' => ['Раствор', '5%, 200 мл', 'Растворы для инфузий', 9_000],
        ];

        $groups = collect($catalogue)->pluck(2)->unique()
            ->mapWithKeys(fn (string $name): array => [$name => MedicationGroup::factory()->create(['name' => $name])->id]);

        $medications = collect($catalogue)->map(function (array $row, string $name) use ($groups, $manufacturers, $suppliers) {
            [$form, $dosage, $group, $retailPrice] = $row;

            $medication = Medication::factory()->create([
                'name' => $name,
                'release_form' => $form,
                'dosage' => $dosage,
                'group_id' => $groups[$group],
                'manufacturer_id' => $manufacturers->random()->id,
            ]);

            $quantity = fake()->numberBetween(200, 1_000);
            $wholesalePrice = round($retailPrice / 1.25, 2);

            MainWarehouseMedication::factory()->create([
                'main_warehouse_id' => MainWarehouse::MAIN,
                'medication_id' => $medication->id,
                'supplier_id' => $suppliers->random()->id,
                'quantity' => $quantity,
                'wholesale_price' => $wholesalePrice,
                'retail_price' => $retailPrice,
                'incoming_price_per_unit' => $wholesalePrice,
                'incoming_price_total' => $wholesalePrice * $quantity,
                'incoming_sum' => $wholesalePrice * $quantity,
                'total_price' => $retailPrice * $quantity,
            ]);

            return $medication;
        });

        // A batch close to its expiry date and one already past it, waiting to be written off: the
        // remains pages mark both, and the dashboard counts them.
        $expiresInDays = ['Глюкоза' => 12, 'Натрия хлорид' => -3];

        foreach ($medications->only(array_keys($expiresInDays)) as $name => $medication) {
            MainWarehouseMedication::factory()->create([
                'main_warehouse_id' => MainWarehouse::MAIN,
                'medication_id' => $medication->id,
                'supplier_id' => $suppliers->random()->id,
                'quantity' => 40,
                'expiration_date' => now()->addDays($expiresInDays[$name])->toDateString(),
                'date_of_receipt' => now()->subMonths(10)->toDateString(),
            ]);
        }

        // The department's inpatients are dispensed from its own warehouse, so it holds a batch of
        // every drug; the other departments are served by the main warehouse.
        $warehouse = Warehouse::factory()->create([
            'name' => 'Склад терапевтического отделения',
            'warehouse_type_id' => WarehouseType::MEDICATIONS,
        ]);
        $department->update(['warehouse_id' => $warehouse->id]);

        foreach ($medications as $medication) {
            WarehouseAccounting::factory()->create([
                'warehouse_id' => $warehouse->id,
                'medication_id' => $medication->id,
                'quantity' => fake()->numberBetween(100, 300),
            ]);
        }

        // An older batch of paracetamol, to be used up before it expires.
        WarehouseAccounting::factory()->create([
            'warehouse_id' => $warehouse->id,
            'medication_id' => $medications->only(['Парацетамол'])->firstOrFail()->id,
            'quantity' => 30,
            'expiration_date' => now()->addDays(20)->toDateString(),
        ]);

        $pharmacy = Pharmacy::factory()->create([
            'name' => 'Аптека стационара',
            'type_id' => PharmacyType::factory()->create(['name' => 'Больничная аптека'])->id,
        ]);

        $this->seedPendingStockDocuments($warehouse, $pharmacy, $medications);
        $pharmacy->license()->create([
            'license_number' => 'ФА-0001234',
            'license_issue_date' => now()->subYears(2)->toDateString(),
            'license_expiry_date' => now()->addYears(3)->toDateString(),
        ]);
        $pharmacy->support()->create([
            'consultation_phone' => '+998 71 200-00-03',
            'medication_consultation_available' => true,
        ]);

        // [drug 1 (ru, en), drug 2 (ru, en), severity, mechanism, comment]
        $combinations = [
            ['Варфарин', 'Warfarin', 'Ибупрофен', 'Ibuprofen', 'Высокая', 'Суммирование антикоагулянтного и ульцерогенного действия', 'Высокий риск желудочно-кишечного кровотечения.'],
            ['Варфарин', 'Warfarin', 'Аспирин', 'Aspirin', 'Высокая', 'Суммирование антикоагулянтного и антиагрегантного действия', 'Риск кровотечения; сочетание допустимо только по строгим показаниям.'],
            ['Варфарин', 'Warfarin', 'Кларитромицин', 'Clarithromycin', 'Высокая', 'Ингибирование CYP3A4 повышает концентрацию варфарина', 'Контроль МНО в первые дни совместного приёма.'],
            ['Кларитромицин', 'Clarithromycin', 'Симвастатин', 'Simvastatin', 'Высокая', 'Ингибирование CYP3A4 повышает концентрацию статина', 'Риск миопатии и рабдомиолиза; приём симвастатина приостановить.'],
            ['Эналаприл', 'Enalapril', 'Спиронолактон', 'Spironolactone', 'Средняя', 'Совместная задержка калия', 'Риск гиперкалиемии; контроль калия крови.'],
            ['Ибупрофен', 'Ibuprofen', 'Диклофенак', 'Diclofenac', 'Средняя', 'Дублирование НПВС', 'Растёт риск поражения ЖКТ без усиления эффекта.'],
        ];

        foreach ($combinations as [$drug1, $drug1En, $drug2, $drug2En, $severity, $mechanism, $comment]) {
            DangerousCombination::factory()->create([
                'drug1_ru' => $drug1,
                'drug1_en' => $drug1En,
                'drug2_ru' => $drug2,
                'drug2_en' => $drug2En,
                'severity_ru' => $severity,
                'severity_en' => $severity === 'Высокая' ? 'Major' : 'Moderate',
                'mechanism_ru' => $mechanism,
                'mechanism_en' => null,
                'comment_ru' => $comment,
            ]);
        }

        return $medications;
    }

    /**
     * A transfer to the pharmacy and a supply request from the main warehouse, both waiting for
     * a decision, so the stock document pages have something to show.
     *
     * @param  Collection<string, Medication>  $medications
     */
    private function seedPendingStockDocuments(Warehouse $warehouse, Pharmacy $pharmacy, Collection $medications): void
    {
        $transfer = Transfer::factory()->create([
            'from_warehouse_id' => $warehouse->id,
            'to_pharmacy_id' => $pharmacy->id,
            'notes' => 'Пополнение аптеки стационара',
        ]);

        foreach ($medications->only(['Парацетамол', 'Амоксициллин']) as $medication) {
            TransferMedication::factory()->create([
                'transfer_id' => $transfer->id,
                'medication_id' => $medication->id,
                'quantity' => 20,
            ]);
        }

        $request = SupplyRequest::factory()->create(['warehouse_id' => $warehouse->id]);

        foreach ($medications->only(['Цефтриаксон', 'Натрия хлорид']) as $medication) {
            $request->items()->create([
                'supplyable_type' => $medication->getMorphClass(),
                'supplyable_id' => $medication->id,
                'requested_quantity' => 50,
            ]);
        }
    }

    /**
     * A few rows in each of the smaller catalogues, so that every list and edit page of the
     * dashboard has a record to show; the first patient gets a fall-risk sheet result and an
     * answered survey.
     */
    private function seedCatalogues(Staff $doctor, Patient $patient): void
    {
        foreach (['Пенициллин', 'Пыльца растений'] as $name) {
            Allergy::query()->create(['name' => $name]);
        }

        foreach (['Артериальная гипертензия', 'Сахарный диабет 2 типа'] as $name) {
            ChronicDisease::query()->create(['name' => $name]);
        }

        foreach (['Кардиостимулятор', 'Инсулиновая помпа'] as $name) {
            MedicalDevice::query()->create(['name' => $name]);
        }

        foreach (['Амбулаторно', 'Стационарно'] as $name) {
            ServiceMethod::factory()->create(['name' => $name]);
        }

        // The main warehouse keeps the supplies; the gloves have run out and wait for a receipt.
        foreach (['Шприц 5 мл' => 800, 'Перчатки нитриловые' => 0] as $name => $quantity) {
            MainWarehouseMedicalSupply::query()->create([
                'main_warehouse_id' => MainWarehouse::MAIN,
                'supply_id' => MedicalSupply::factory()->create(['name' => $name])->id,
                'quantity' => $quantity,
            ]);
        }

        MainWarehouseHouseholdMaterial::query()->create([
            'main_warehouse_id' => MainWarehouse::MAIN,
            'household_material_id' => HouseholdMaterial::factory()->create(['name' => 'Дезинфицирующее средство'])->id,
            'quantity' => 60,
        ]);

        Equipment::query()->create([
            'name' => 'Аппарат УЗИ',
            'equipment_type_id' => EquipmentType::query()->create(['name' => 'Диагностическое оборудование'])->id,
            'manufacturer_id' => Manufacturer::query()->orderBy('id')->value('id'),
            'staff_id' => $doctor->id,
            'serial_number' => 'US-2024-0042',
            'date_purchase' => now()->subYears(2)->toDateString(),
            'calibration_date' => now()->subMonths(3)->toDateString(),
            'repair' => 'Не требуется',
            'term_date' => now()->addYears(5)->toDateString(),
            'term_notify_days' => 30,
            'upcoming_service_at' => 90,
            'location' => 'Кабинет ультразвуковой диагностики',
            'price' => 185_000_000,
            'status' => EquipmentStatus::Working,
        ]);

        $survey = Survey::factory()->create([
            'title' => 'Опросник качества сна',
            'survey_type_id' => SurveyType::factory()->create(['name' => 'Анкеты пациентов'])->id,
        ]);
        $group = $survey->groups()->create(['title' => 'Сон']);
        [$sleepHours, $nightWaking] = $group->questions()->createMany([
            ['survey_id' => $survey->id, 'question' => 'Сколько часов вы спите?', 'type' => QuestionType::Text],
            ['survey_id' => $survey->id, 'question' => 'Просыпаетесь ли вы ночью?', 'type' => QuestionType::Boolean],
        ])->all();

        $response = SurveyResponse::query()->create(['patient_id' => $patient->id, 'survey_id' => $survey->id]);
        $response->answers()->createMany([
            ['question_id' => $sleepHours->id, 'answer' => '6'],
            // Yes/no questions store the value the survey forms post: 'yes' or 'no'.
            ['question_id' => $nightWaking->id, 'answer' => 'yes'],
        ]);

        // A high fall risk (Morse 45 and more), so the nurse window shows its recommended actions:
        // a recent fall (25), a concomitant disease (15) and a slightly unsteady gait (10). Filled in
        // with answers, so documents:rescore finds the stored score matching them.
        $morse = Document::query()->with('questions')->findOrFail(MedicalScale::MorseFallRisk->value);
        $highRisk = [
            'Падал ли в последние 3 мес.?' => 'Да',
            'Есть ли сопутствующее заболевание? (со стороны сердечно-сосудистой, костно-суставной и мышечной, нервной систем, судорожный синдром, нарушение зрения, анемия)' => 'Да',
            'Походка' => 'Слегка несвободная (ходит с остановками, шаги короткие, иногда с задержкой)',
        ];

        app(DocumentResponseService::class)->store([
            'patient_id' => $patient->id,
            'document_id' => $morse->id,
            'answers' => $morse->questions
                ->mapWithKeys(fn (DocumentQuestion $question): array => [
                    $question->id => $highRisk[$question->question]
                        ?? $question->options[0]
                        ?? throw new LogicException("Question {$question->id} of the Morse scale has no options."),
                ])
                ->all(),
        ]);
    }

    /**
     * @param  Collection<int, City>  $cities
     * @return Collection<int, Patient>
     */
    private function seedPatients(Collection $cities): Collection
    {
        $addresses = $cities->map(fn (City $city): array => [
            'region_id' => $city->region_id,
            'city_id' => $city->id,
            'registered_region_id' => $city->region_id,
            'registered_city_id' => $city->id,
        ])->all();

        return collect([
            ...Patient::factory()->count(30)->sequence(...$addresses)->create(),
            ...Patient::factory()->count(10)->insured()->sequence(...$addresses)->create(),
        ]);
    }

    /**
     * Eight active and four finished hospital stays with a diagnosis, services, a procedure,
     * prescriptions and, for active stays, nurse-station vital signs.
     *
     * @param  Collection<int, Patient>  $patients
     * @param  Collection<int, Staff>  $doctors
     * @param  Collection<int, Diagnosis>  $diagnoses
     * @param  Collection<int, Service>  $services
     * @param  Collection<int, Procedure>  $procedures
     * @param  Collection<string, Medication>  $medications
     */
    private function seedInpatients(
        Collection $patients,
        Collection $doctors,
        Collection $diagnoses,
        Collection $services,
        Collection $procedures,
        Collection $medications,
        PatientMedicationService $prescriptions,
    ): void {
        $vitals = VitalParameter::query()->pluck('id', 'name');

        foreach ($patients->values() as $index => $patient) {
            $doctor = $this->doctorInTurn($doctors, $index);
            $diagnosis = $diagnoses->random();
            $active = $index < 8;
            $admittedAt = now()->subDays($active ? fake()->numberBetween(1, 9) : fake()->numberBetween(20, 120));

            $map = ExaminationMap::factory()
                ->unless($active, fn ($factory) => $factory->discharged())
                ->create([
                    'patient_id' => $patient->id,
                    'staff_id' => $doctor->id,
                    'department_id' => $doctor->professionalData()->value('department_id'),
                    'admission_date' => $admittedAt->toDateString(),
                    'admission_diagnosis_code' => $diagnosis->code,
                    'admission_diagnosis_description' => $diagnosis->name,
                    ...($active ? [] : [
                        'discharge_date' => $admittedAt->copy()->addDays(fake()->numberBetween(5, 14))->toDateString(),
                        'discharge_diagnosis_code' => $diagnosis->code,
                        'discharge_diagnosis_description' => $diagnosis->name,
                    ]),
                ]);

            $stay = ['patient_id' => $patient->id, 'examination_map_id' => $map->id];

            PatientDiagnosis::factory()->create([...$stay, 'diagnosis_id' => $diagnosis->id]);

            foreach ($services->random(2) as $service) {
                PatientService::factory()->create([
                    ...$stay,
                    'service_id' => $service->id,
                    'service_date' => $admittedAt->toDateString(),
                    'status' => $active ? fake()->randomElement([ServiceStatus::Assigned, ServiceStatus::Confirmed]) : ServiceStatus::Completed,
                ]);
            }

            PatientProcedure::factory()->create([
                ...$stay,
                'procedure_id' => $procedures->random()->id,
                'procedure_date' => $admittedAt->toDateString(),
                'status' => $active ? ServiceStatus::Confirmed : ServiceStatus::Completed,
            ]);

            // The first patient gets warfarin with ibuprofen, which triggers the dangerous-combination warning.
            $prescribed = $index === 0
                ? $medications->only(['Варфарин', 'Ибупрофен', 'Омепразол'])
                : $medications->except(['Варфарин'])->random(2);

            foreach ($prescribed as $medication) {
                $status = $active
                    ? fake()->randomElement([PatientMedicationStatus::Ordered, PatientMedicationStatus::SentToFloor, PatientMedicationStatus::ReceivedAtStation])
                    : PatientMedicationStatus::Completed;

                $prescription = PatientMedication::factory()->create([
                    ...$stay,
                    'medication_id' => $medication->id,
                    'status' => $active ? PatientMedicationStatus::Ordered : $status,
                    'date_of_end' => $admittedAt->copy()->addDays(7)->toDateString(),
                ]);

                // Sent to the ward the way the registrar does it, so the units leave the warehouse
                // and a return can put them back; arrival at the nurse station moves no stock.
                if ($status === PatientMedicationStatus::SentToFloor || $status === PatientMedicationStatus::ReceivedAtStation) {
                    $prescriptions->sendToFloor($prescription);
                }

                if ($status === PatientMedicationStatus::ReceivedAtStation) {
                    $prescription->update(['status' => $status]);
                }
            }

            if ($active) {
                $this->recordVitals($map, $vitals);
            }
        }
    }

    /**
     * Measurements every eight hours over the last two days of the stay.
     *
     * @param  Collection<string, int>  $vitals  vital parameter ids keyed by name
     */
    private function recordVitals(ExaminationMap $map, Collection $vitals): void
    {
        $rows = [];
        $measuredAt = Carbon::parse($map->admission_date)->max(now()->subDays(2))->setTime(8, 0);

        for (; $measuredAt->isPast(); $measuredAt->addHours(8)) {
            $readings = [
                'Систолическое АД' => fake()->numberBetween(110, 160),
                'Диастолическое АД' => fake()->numberBetween(65, 100),
                'Пульс' => fake()->numberBetween(60, 100),
                'Температура' => fake()->randomFloat(1, 36.2, 38.4),
                'Сатурация (SPO₂)' => fake()->numberBetween(93, 99),
                'Частота дыхания (ЧДД)' => fake()->numberBetween(14, 22),
            ];

            foreach ($readings as $name => $value) {
                $rows[] = [
                    'examination_map_id' => $map->id,
                    'vital_parameter_id' => $vitals[$name],
                    'value' => $value,
                    'created_at' => $measuredAt->copy(),
                    'updated_at' => $measuredAt->copy(),
                ];
            }
        }

        ExaminationMapVitalParameter::query()->insert($rows);
    }

    /**
     * The doctor of the patient with this index: the doctors take the patients in turn.
     *
     * @param  Collection<int, Staff>  $doctors
     */
    private function doctorInTurn(Collection $doctors, int $index): Staff
    {
        return $doctors->all()[$index % $doctors->count()];
    }

    /**
     * Where the appointment of the outpatient with this index stands: the first visit of the morning
     * took place and the last one was cancelled; of the rest some were paid at booking, some
     * confirmed by the registrar and the others are new.
     */
    private function outpatientStatus(int $index): ReceptionStatus
    {
        return match (true) {
            $index === 0 => ReceptionStatus::Done,
            $index === 15 => ReceptionStatus::Cancelled,
            $index % 4 === 0 => ReceptionStatus::Paid,
            $index % 4 === 1 => ReceptionStatus::Confirmed,
            default => ReceptionStatus::New,
        };
    }

    /**
     * Outpatients with appointments today; every second one also has a service, paid or waiting at
     * the cashier, and a diagnosis.
     *
     * @param  Collection<int, Patient>  $patients
     * @param  Collection<int, Staff>  $doctors
     * @param  Collection<int, Diagnosis>  $diagnoses
     * @param  Collection<int, Service>  $services
     */
    private function seedOutpatients(Collection $patients, Collection $doctors, Collection $diagnoses, Collection $services): void
    {
        $visits = [];

        foreach ($patients->take(16) as $index => $patient) {
            $doctor = $this->doctorInTurn($doctors, $index);
            $visit = $visits[$doctor->id] = ($visits[$doctor->id] ?? -1) + 1;
            // Each doctor's first patient comes one slot later than the previous doctor's, so the
            // day's list reads as a schedule rather than everyone at 09:00. A doctor's own visits
            // still take consecutive slots and never overlap.
            $slot = ($index % $doctors->count() + $visit) % self::RECEPTION_SLOTS;
            $start = now()->setTime(9, 0)->addMinutes($slot * self::SLOT_MINUTES);

            Reception::factory()->create([
                'patient_id' => $patient->id,
                'staff_id' => $doctor->id,
                'service_id' => $doctor->services()->value('services.id'),
                'date' => now()->toDateString(),
                'start_time' => $start->format('H:i'),
                'end_time' => $start->copy()->addMinutes(self::SLOT_MINUTES)->format('H:i'),
                'status' => $this->outpatientStatus($index),
            ]);

            if ($index % 2 === 0) {
                // Those who paid for the visit at booking paid for the test with it.
                PatientService::factory()->create([
                    'patient_id' => $patient->id,
                    'service_id' => $services->random()->id,
                    'status' => $index % 4 === 0 ? ServiceStatus::Paid : ServiceStatus::Confirmed,
                ]);

                PatientDiagnosis::factory()->create([
                    'patient_id' => $patient->id,
                    'diagnosis_id' => $diagnoses->random()->id,
                ]);
            }
        }
    }
}
