<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentQuestion;
use App\Models\DocumentScore;
use App\Models\DocumentScoreAction;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $initialInspection = Document::query()->create([
            'title' => 'Первичный сестринский осмотр. Детский.',
        ]);

        $questions = [
            // Основная информация о пациенте
            [
                'question' => 'ФИО пациента',
                'type' => 'text',
            ],
            [
                'question' => 'Дата рождения',
                'type' => 'text',
            ],
            [
                'question' => 'Дата и время начала осмотра (дата)',
                'type' => 'text',
            ],
            [
                'question' => 'Дата и время начала осмотра (время)',
                'type' => 'text',
            ],

            // Опрос пациента
            [
                'question' => 'Осмотр ведется со слов',
                'type' => 'choice',
                'options' => [
                    'Пациента',
                    'Члена семьи/друга',
                    'Другого лица'
                ]
            ],
            [
                'question' => 'Язык общения',
                'type' => 'choice',
                'options' => [
                    'Узбекский',
                    'Русский',
                    'Иностранный (нужен переводчик)',
                    'Жестами (глухонемой)'
                ]
            ],

            // Социально-экономический статус
            [
                'question' => 'Живет (бытовые условия)',
                'type' => 'choice',
                'options' => [
                    'Благоустроенно',
                    'Не благоустроенно',
                    'Дом ребенка'
                ]
            ],
            [
                'question' => 'Семья',
                'type' => 'choice',
                'options' => [
                    'Полная',
                    'Не полная (с матерью)',
                    'Не полная (с отцом)'
                ]
            ],
            [
                'question' => 'Ребенок в семье (порядковый номер)',
                'type' => 'text',
            ],
            [
                'question' => 'Всего детей в семье',
                'type' => 'text',
            ],
            [
                'question' => 'Есть ли вредные привычки у родителей',
                'type' => 'choice',
                'options' => [
                    'Нет',
                    'Да (указать какие)'
                ]
            ],

            // Осмотр пациента
            [
                'question' => 'Сознание',
                'type' => 'text',
            ],
            [
                'question' => 'Контакт',
                'type' => 'text',
            ],
            [
                'question' => 'Развитие ребенка',
                'type' => 'choice',
                'options' => [
                    'Согласно возрасту',
                    'Отстает в умственном развитии',
                    'Отстает в физическом развитии'
                ]
            ],
            [
                'question' => 'Психоэмоциональная оценка',
                'type' => 'choice',
                'options' => [
                    'Адекватен',
                    'Не адекватен',
                    'Тихий',
                    'Взволнованный',
                    'Ориентированный',
                    'Не ориентированный (в месте)',
                    'Не ориентированный (во времени)',
                    'Не ориентированный (в пространстве)'
                ]
            ],
            [
                'question' => 'Сон',
                'type' => 'choice',
                'options' => [
                    'Нет проблем',
                    'Трудности со сном (указать какие)'
                ]
            ],
            [
                'question' => 'Зрение',
                'type' => 'choice',
                'options' => [
                    'Не нарушено',
                    'Снижено справа',
                    'Снижено слева',
                    'Снижено оба',
                    'Слепота справа',
                    'Слепота слева',
                    'Слепота оба'
                ]
            ],
            [
                'question' => 'Слух',
                'type' => 'choice',
                'options' => [
                    'Не нарушено',
                    'Снижено справа',
                    'Снижено слева',
                    'Снижено оба',
                    'Глухота справа',
                    'Глухота слева',
                    'Глухота оба'
                ]
            ],
            [
                'question' => 'Двигательная функция',
                'type' => 'choice',
                'options' => [
                    'Не нарушена',
                    'Нарушение движения рук справа',
                    'Нарушение движения рук слева',
                    'Нарушение движения ног справа',
                    'Нарушение движения ног слева',
                    'Не подвижен'
                ]
            ],
            [
                'question' => 'Речь',
                'type' => 'choice',
                'options' => [
                    'Не нарушена',
                    'Нарушена'
                ]
            ],
            [
                'question' => 'Состояние кожи',
                'type' => 'choice',
                'options' => [
                    'Нет изменений',
                    'Есть изменения',
                    'Отеки',
                    'Пролежни',
                    'Другие изменения (указать)'
                ]
            ],

            // Функциональные оценки
            [
                'question' => 'Степень зависимости по шкале Бартелл',
                'type' => 'choice',
                'options' => [
                    'Независим',
                    'Легкая',
                    'Средняя',
                    'Высокая'
                ]
            ],
            [
                'question' => 'Риск падения (по шкале Хампти-Дампти)',
                'type' => 'choice',
                'options' => [
                    '7-11 - низкий риск',
                    '12-23 - высокий риск'
                ]
            ],
            [
                'question' => 'Риск развития пролежней (по шкале Брадена)',
                'type' => 'choice',
                'options' => [
                    'Выше 16 - нет риска',
                    '15-16 - низкий',
                    '13-14 - средний',
                    '12 и менее - высокий'
                ]
            ],

            // Оценка питания
            [
                'question' => 'Аппетит',
                'type' => 'choice',
                'options' => [
                    'Хороший',
                    'Снижен',
                    'Повышен'
                ]
            ],
            [
                'question' => 'Способ кормления',
                'type' => 'choice',
                'options' => [
                    'Через рот',
                    'Грудное молоко',
                    'Искусственное молоко',
                    'Парентеральное',
                    'Кормление через зонд',
                    'Другое (указать)'
                ]
            ],
            [
                'question' => 'Трудности при питании',
                'type' => 'choice',
                'options' => [
                    'Трудностей нет',
                    'Жевании',
                    'Глотании',
                    'Не может самостоятельно принимать пищу'
                ]
            ],

            // Возрастные категории
            [
                'question' => 'Ребёнок относится к категории',
                'type' => 'choice',
                'options' => [
                    'Дети до 1 года',
                    'От 1 до 3 лет',
                    'От 3 до 5 лет',
                    'От 5 до 7 лет',
                    'От 7 до 10 лет',
                    'От 10 до 18 лет'
                ]
            ],

            // Для детей до 1 года
            [
                'question' => 'Оценка боли для детей до 1 года (выражение лица)',
                'type' => 'choice',
                'options' => [
                    '0 - обычное, спокойное',
                    '1 - гримаса (нос, губы и брови)'
                ],
                'condition' => ['Дети до 1 года']
            ],
            [
                'question' => 'Оценка боли для детей до 1 года (крик/плач)',
                'type' => 'choice',
                'options' => [
                    '0 - не плачет',
                    '1 - хнычет, плачет и успокаивается',
                    '2 - сильно кричит и плачет'
                ],
                'condition' => ['Дети до 1 года']
            ],

            // Для детей 1-3 лет
            [
                'question' => 'Оценка боли по шкале FLACC (лицо)',
                'type' => 'choice',
                'options' => [
                    '0 - без особых гримас',
                    '1 - иногда хмурое, напряжено',
                    '2 - часто/постоянно дрожит подбородок'
                ],
                'condition' => ['От 1 до 3 лет']
            ],

            // Для детей 3-18 лет
            [
                'question' => 'Оценка боли для детей 3-18 лет',
                'type' => 'choice',
                'options' => [
                    'Нет боли',
                    'Есть боль (острая)',
                    'Есть боль (хроническая)'
                ],
                'condition' => ['От 3 до 5 лет', 'От 5 до 7 лет', 'От 7 до 10 лет', 'От 10 до 18 лет']
            ],

            // Планирование выписки
            [
                'question' => 'Приехал/а из-за пределов г.Ташкент',
                'type' => 'boolean',
                'options' => ['Нет', 'Да']
            ],
            [
                'question' => 'До выписки необходимо обучить',
                'type' => 'text'
            ],
            [
                'question' => 'Уровень необходимого обучения',
                'type' => 'choice',
                'options' => [
                    'Обычное обучение',
                    'Дополнительные темы обучения (указать какие)'
                ]
            ]
        ];

        foreach ($questions as $q) {
            $questionData = [
                'document_id' => $initialInspection->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
            ];

            DocumentQuestion::query()->create($questionData);
        }

        $morseFallScale = Document::query()->create([
            'title' => 'Лист оценки риска падения по шкале Морзе',
        ]);

        $morseQuestions = [
            // Morse Scale questions
            [
                'question' => 'Падал ли в последние 3 мес.?',
                'type' => 'choice',
                'options' => ['Нет', 'Да'],
            ],
            [
                'question' => 'Есть ли сопутствующее заболевание? (со стороны сердечно-сосудистой, костно-суставной и мышечной, нервной систем, судорожный синдром, нарушение зрения, анемия)',
                'type' => 'choice',
                'options' => ['Нет', 'Да'],
            ],
            [
                'question' => 'Ходит самостоятельно',
                'type' => 'choice',
                'options' => [
                    'Сам / постельный режим',
                    'Костыли / ходунки / трость',
                    'Опора о мебель или стены'
                ],
            ],
            [
                'question' => 'Принимает внутривенное вливание (есть система) / принимает гепарин',
                'type' => 'choice',
                'options' => ['Нет', 'Да'],
            ],
            [
                'question' => 'Походка',
                'type' => 'choice',
                'options' => [
                    'Нормальная (ходит свободно)',
                    'Слегка несвободная (ходит с остановками, шаги короткие, иногда с задержкой)',
                    'Нарушения (не может встать, ходит опираясь, смотрит вниз)'
                ],
            ],
            [
                'question' => 'Психическое состояние',
                'type' => 'choice',
                'options' => [
                    'Осознает свою способность двигаться',
                    'Не знает или забывает, что нужна помощь при движении'
                ],
            ],

        ];

        foreach ($morseQuestions as $q) {
            $questionData = [
                'document_id' => $morseFallScale->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
            ];

            DocumentQuestion::query()->create($questionData);
        }

        $morseScoreAction = [
            [
                'from_score' => '0',
                'to_score' => '24',
                'actions' => [
                    'Сообщить членам семьи/пациенту о его/ее риске падения',
                    'Ознакомить пациента с местностью',
                    'Обучить родителей/пациента о предотвращении падения',
                    'Установить кнопку вызова в пределах досягаемости'
                ],
            ],
            [
                'from_score' => '25',
                'to_score' => '50',
                'actions' => [
                    'Установить высоту кровати на нижнем положении',
                    'Закрепить кроватные колеса, ходунки, инвалидное кресло в устойчивом положении',
                    'Поставить коляску и др. вспомогательные предметы рядом с пациентом',
                    'Обеспечить хорошее освещение',
                    'Сообщить тех. отделам о неисправностях',
                    'Напомнить о нескользкой обуви',
                    'При сопровождении сообщить персоналу другого отделения о риске падения'
                ],
            ],
            [
                'from_score' => '51',
                'to_score' => null,
                'actions' => [
                    'Наклеить красный знак на браслет или одеть красный браслет',
                    'Разместить наклейку о риске падения на двери палаты',
                    'Совершать обходы в палату каждые 2 часа',
                    'Сопровождать пациента в туалет',
                    'Сопровождать пациента после операции и процедур',
                    'Разместить пациента ближе к посту',
                    'Обеспечить присутствие члена семьи или санитарки',
                    'Вовлекать пациента и семью в профилактику',
                    'Обучить о принимаемых ЛС и их влиянии на падение',
                    'Установить боковые ограждения при необходимости'
                ],
            ],
        ];

        foreach ($morseScoreAction as $scoreRange) {
            $scoreModel = DocumentScore::create([
                'document_id' => $morseFallScale->id,
                'from_score' => (int)$scoreRange['from_score'],
                'to_score' => $scoreRange['to_score'] !== null ? (int)$scoreRange['to_score'] : null,
            ]);

            foreach ($scoreRange['actions'] as $actionText) {
                DocumentScoreAction::create([
                    'document_score_id' => $scoreModel->id,
                    'action' => $actionText,
                ]);
            }
        }


        $humptyDumptyScale = Document::query()->create([
            'title' => 'Лист оценки риска падения детей по шкале "Хампти-Дампти"',
        ]);

        $humptyDumptyQuestions = [
            // Humpty Dumpty Scale questions
            [
                'question' => 'Возраст',
                'type' => 'choice',
                'options' => [
                    'Младше 3-х лет',
                    'От 3 до 7 лет',
                    'От 7 до 13 лет',
                    'От 13 до 18 лет'
                ],
            ],
            [
                'question' => 'Пол',
                'type' => 'choice',
                'options' => ['Мужской', 'Женский'],
            ],
            [
                'question' => 'Диагноз',
                'type' => 'choice',
                'options' => [
                    'Неврологические нарушения',
                    'Нарушения оксигенации',
                    'Психологические/поведенческие расстройства',
                    'Другое'
                ],
            ],
            [
                'question' => 'Когнитивные расстройства',
                'type' => 'choice',
                'options' => [
                    'Не осознает ограничений',
                    'Забывает об ограничениях',
                    'Ориентируется в способностях'
                ],
            ],
            [
                'question' => 'Факторы среды',
                'type' => 'choice',
                'options' => [
                    'Падения в анамнезе',
                    'Вспомогательные средства',
                    'Лежачий / на руках',
                    'Ходит самостоятельно'
                ],
            ],
            [
                'question' => 'Состояние после операции/седации/анестезии/инвазивной процедуры',
                'type' => 'choice',
                'options' => [
                    'До 24 ч',
                    '24-48 ч',
                    'Более 48 ч / не проводилось'
                ],
            ],
            [
                'question' => 'Медикаменты',
                'type' => 'choice',
                'options' => [
                    '2 и более из указанных групп',
                    '1 медикамент',
                    'Не принимает / другие'
                ],
            ],

            // General preventive measures
//            [
//                'question' => 'Общие профилактические меры для всех пациентов',
//                'type' => 'choice',
//                'options' => [
//                    'Сообщить членам семьи/пациенту о риске падения',
//                    'Установить кнопку вызова в пределах досягаемости',
//                    'Ознакомить пациента с местностью',
//                    'Обучить родителей/пациента о предотвращении падения'
//                ],
//            ],
        ];

        foreach ($humptyDumptyQuestions as $q) {
            $questionData = [
                'document_id' => $humptyDumptyScale->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
            ];

            DocumentQuestion::query()->create($questionData);
        }

        $humptyDumptyScaleAction = [
            // Low risk measures
            [
                'from_score' => '7',
                'to_score' => 11,
                'actions' => [
                    'Поднять боковые борты кровати',
                    'Закрепить кроватные колеса, ходунки, инвалидное кресло',
                    'Поставить коляску и др. вспомогательные предметы рядом',
                    'Обеспечить хорошее освещение',
                    'Сообщать персоналу при сопровождении',
                    'Документировать обучение по профилактике падения'
                ],
            ],

            // High risk measures
            [
                'from_score' => 12,
                'to_score' => null,
                'actions' => [
                    'Наклеить красный знак на браслет пациента',
                    'Установить красный знак на двери палаты',
                    'Совершать обходы в палату каждые 2 часа',
                    'Сопровождение в туалет',
                    'Сопровождение после процедур',
                    'Разместить пациента ближе к посту',
                    'Держать дверь палаты открытой',
                    'Обеспечить присутствие члена семьи или санитарки',
                    'Вовлекать пациента и семью в профилактику',
                    'Обучить о влиянии лекарств на падение',
                    'Установить боковые ограждения/борты койки',
                    'Документировать обучение и изменения риска',
                    'Повторной оценки риска'
                ],
            ],
        ];

        foreach ($humptyDumptyScaleAction as $scoreRange) {
            $scoreModel = DocumentScore::create([
                'document_id' => $humptyDumptyScale->id,
                'from_score' => (int)$scoreRange['from_score'],
                'to_score' => $scoreRange['to_score'] !== null ? (int)$scoreRange['to_score'] : null,
            ]);

            foreach ($scoreRange['actions'] as $actionText) {
                DocumentScoreAction::create([
                    'document_score_id' => $scoreModel->id,
                    'action' => $actionText,
                ]);
            }
        }

        // Create new document for Outpatient Fall Risk Assessment
        $outpatientFallRisk = Document::query()->create([
            'title' => 'Лист оценки риска падения амбулаторного пациента (КДО)',
        ]);

        $outpatientQuestions = [
            // Assessment questions
            [
                'question' => 'Падали ли вы в последние 3 мес.? (При повторной оценке: падал/а ли за последние сутки?)',
                'type' => 'boolean',
                'options' => ['Нет', 'Да'],
            ],
            [
                'question' => 'Чувствуете ли вы сейчас головокружение?',
                'type' => 'boolean',
                'options' => ['Нет', 'Да'],
            ],
            [
                'question' => 'Чувствуете слабость в ногах?',
                'type' => 'boolean',
                'options' => ['Нет', 'Да'],
            ],
            [
                'question' => 'Ходит с костылями/тростью/в инвалидном кресле',
                'type' => 'boolean',
                'options' => ['Нет', 'Да'],
            ],
            [
                'question' => 'Принял процедуру, медикамент или анестезию, повышающую риск падения или риск головокружения',
                'type' => 'boolean',
                'options' => ['Нет', 'Да'],
            ],
        ];

        foreach ($outpatientQuestions as $q) {
            $questionData = [
                'document_id' => $outpatientFallRisk->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
            ];

            DocumentQuestion::query()->create($questionData);
        }

        $outpatientFallRiskScore = [
            // Preventive measures - no risk
            [
                'from_score' => 0,
                'to_score' => 2,
                'actions' => [
                    'Сообщить членам семьи/пациенту о риске падения',
                    'Ознакомить пациента с местностью',
                    'Обучить пациента о предотвращении падения',
                    'Напомнить о нескользкой обуви'
                ],
            ],

            // High risk measures
            [
                'from_score' => 3,
                'to_score' => 5,
                'actions' => [
                    'Выдать памятку по профилактике и снижению риска падения',
                    'Обучить пациента о предотвращении падения',
                    'Сопровождать пациента после процедур до родственников',
                    'Обучить о влиянии лекарств на падение'
                ],
            ],
        ];

        foreach ($outpatientFallRiskScore as $scoreRange) {
            $scoreModel = DocumentScore::create([
                'document_id' => $outpatientFallRisk->id,
                'from_score' => (int)$scoreRange['from_score'],
                'to_score' => $scoreRange['to_score'] !== null ? (int)$scoreRange['to_score'] : null,
            ]);

            foreach ($scoreRange['actions'] as $actionText) {
                DocumentScoreAction::create([
                    'document_score_id' => $scoreModel->id,
                    'action' => $actionText,
                ]);
            }
        }


        $painAssessment = Document::query()->create([
            'title' => 'Лист оценки боли (для взрослых и детей от 3 лет)',
        ]);

        $painAssessmentQuestions = [
            // Pain intensity
            [
                'question' => 'Интенсивность боли (0-10)',
                'type' => 'choice',
                'options' => [
                    '0 - Боли нет',
                    '1 - Очень слабая боль',
                    '2 - Слабая боль',
                    '3 - Умеренная боль',
                    '4 - Довольно сильная боль',
                    '5 - Сильная боль',
                    '6 - Очень сильная боль',
                    '7 - Крайне сильная боль',
                    '8 - Жестокая боль',
                    '9 - Невыносимая боль',
                    '10 - Максимально возможная боль'
                ],
            ],

            // Pain location
            [
                'question' => 'Локализация боли',
                'type' => 'text',
            ],
            [
                'question' => 'Схематичное расположение боли (отметить на схеме)',
                'type' => 'text', // You'll need to implement this custom type
            ],

            // Pain characteristics
            [
                'question' => 'Характер боли',
                'type' => 'choice',
                'options' => [
                    'Ж - жгучая',
                    'Р - режущая',
                    'Кол - колющая',
                    'Стр - стреляющая',
                    'Н - ноющая',
                    'Сх - схваткообразная',
                    'О - острая',
                    'Туп - тупая',
                    'П - постоянная',
                    'Тян - тянущая',
                    'Пл - пульсирующая',
                    'Другое (указать)'
                ],
            ],

            // Pain interventions
            [
                'question' => 'Оказанная помощь (медикаментозная)',
                'type' => 'choice',
                'options' => [
                    'НР - наркотические анальгетики',
                    'НН - ненаркотические анальгетики',
                    'СП - спазмолитики',
                    'АН - анестетики',
                    'Другое (указать)'
                ],
            ],
            [
                'question' => 'Оказанная помощь (немедикаментозная)',
                'type' => 'choice',
                'options' => [
                    'п - психологическая помощь, обучение',
                    'у - упражнения, массаж',
                    'м - местная терапия',
                    'и - иммобилизация',
                    'Другое (указать)'
                ],
            ],

            // Side effects
            [
                'question' => 'Побочные эффекты',
                'type' => 'choice',
                'options' => [
                    'Аллергическая реакция',
                    'Привыкание',
                    'Тошнота/рвота',
                    'Боли в желудке',
                    'Запор',
                    'Другое (указать)'
                ],
            ],

            [
                'question' => 'Комментарии',
                'type' => 'text',
            ],
        ];

        foreach ($painAssessmentQuestions as $q) {
            $questionData = [
                'document_id' => $painAssessment->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
            ];

            DocumentQuestion::query()->create($questionData);
        }

        $painAssessmentRiskScore = [
            [
                'from_score' => 0,
                'to_score' => null,
                'actions' => [
                    'Боли нет',
                ],
            ],
            [
                'from_score' => 1,
                'to_score' => null,
                'actions' => [
                    'Очень слабая боль',
                ],
            ],
            [
                'from_score' => 2,
                'to_score' => null,
                'actions' => [
                    'Слабая боль',
                ],
            ],
            [
                'from_score' => 3,
                'to_score' => null,
                'actions' => [
                    'Умеренная боль',
                ],
            ],
            [
                'from_score' => 4,
                'to_score' => null,
                'actions' => [
                    'Довольно сильная боль',
                ],
            ],
            [
                'from_score' => 5,
                'to_score' => null,
                'actions' => [
                    'Сильная боль',
                ],
            ],
            [
                'from_score' => 6,
                'to_score' => null,
                'actions' => [
                    'Очень сильная боль',
                ],
            ],
            [
                'from_score' => 7,
                'to_score' => null,
                'actions' => [
                    'Крайне сильная боль',
                ],
            ],
            [
                'from_score' => 8,
                'to_score' => null,
                'actions' => [
                    'Жестокая боль',
                ],
            ],
            [
                'from_score' => 9,
                'to_score' => null,
                'actions' => [
                    'Невыносимая боль',
                ],
            ],
            [
                'from_score' => 10,
                'to_score' => null,
                'actions' => [
                    'Максимально возможная боль'
                ],
            ],
        ];


        foreach ($painAssessmentRiskScore as $scoreRange) {
            $scoreModel = DocumentScore::create([
                'document_id' => $painAssessment->id,
                'from_score' => (int)$scoreRange['from_score'],
                'to_score' => $scoreRange['to_score'] !== null ? (int)$scoreRange['to_score'] : null,
            ]);

            foreach ($scoreRange['actions'] as $actionText) {
                DocumentScoreAction::create([
                    'document_score_id' => $scoreModel->id,
                    'action' => $actionText,
                ]);
            }
        }

        $flaccPainAssessment = Document::query()->create([
            'title' => 'Лист оценки боли FLACC (дети 1-3 года)',
        ]);

        $flaccQuestions = [
            // FLACC assessment components
            [
                'question' => 'Лицо (Face)',
                'type' => 'choice',
                'options' => [
                    '0 - без особых гримас и выражений',
                    '1 - иногда хмурое, напряжено, отвлечено',
                    '2 - часто/постоянно дрожит подбородок, сжаты нижние челюсти'
                ],
            ],
            [
                'question' => 'Ноги (Legs)',
                'type' => 'choice',
                'options' => [
                    '0 - нормально расположены / расслаблены',
                    '1 - беспокойные движения, напряжены',
                    '2 - пинается, или подняты вверх'
                ],
            ],
            [
                'question' => 'Активность (Activity)',
                'type' => 'choice',
                'options' => [
                    '0 - лежит спокойно, в нормальном положении, двигается легко',
                    '1 - корчится, движется вперед и назад, напряжен',
                    '2 - изогнут дугой, ригидный или резко движется (рывками)'
                ],
            ],
            [
                'question' => 'Плач (Cry)',
                'type' => 'choice',
                'options' => [
                    '0 - не плачет (бодрствует или спит)',
                    '1 - ноет или хнычет; беспокоит редко',
                    '2 - плачет часто, кричит или всхлипывает, часто беспокоит'
                ],
            ],
            [
                'question' => 'Успокаиваемость (Consolability)',
                'type' => 'choice',
                'options' => [
                    '0 - спокоен, расслаблен',
                    '1 - успокаивается при прикосновении, от слов, объятий, на руках, отвлекается',
                    '2 - трудно поддается успокоению'
                ],
            ],
        ];

        foreach ($flaccQuestions as $q) {
            DocumentQuestion::query()->create([
                'document_id' => $flaccPainAssessment->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
            ]);
        }

        $flaccRiskScore = [
            [
                'from_score' => 0,
                'to_score' => null,
                'actions' => [
                    'спокоен',
                ],
            ],
            [
                'from_score' => 1,
                'to_score' => 3,
                'actions' => [
                    'небольшой дискомфорт',
                ],
            ],
            [
                'from_score' => 4,
                'to_score' => 6,
                'actions' => [
                    'небольшая боль',
                ],
            ],
            [
                'from_score' => 7,
                'to_score' => 10,
                'actions' => [
                    'сильная боль'
                ],
            ],
        ];

        foreach ($flaccRiskScore as $scoreRange) {
            $scoreModel = DocumentScore::create([
                'document_id' => $flaccPainAssessment->id,
                'from_score' => (int)$scoreRange['from_score'],
                'to_score' => $scoreRange['to_score'] !== null ? (int)$scoreRange['to_score'] : null,
            ]);

            foreach ($scoreRange['actions'] as $actionText) {
                DocumentScoreAction::create([
                    'document_score_id' => $scoreModel->id,
                    'action' => $actionText,
                ]);
            }
        }

        $nipsPainAssessment = Document::query()->create([
            'title' => 'Лист оценки боли NIPS (новорожденные и дети до года)',
        ]);

        $nipsQuestions = [
            // NIPS assessment components
            [
                'question' => 'Выражение лица',
                'type' => 'choice',
                'options' => [
                    '0 - обычное, спокойное',
                    '1 - гримаса (нос, губы и брови)'
                ],
            ],
            [
                'question' => 'Крик / плач',
                'type' => 'choice',
                'options' => [
                    '0 - не плачет',
                    '1 - хнычет, плачет и успокаивается',
                    '2 - сильно кричит и плачет'
                ],
            ],
            [
                'question' => 'Характер дыхания',
                'type' => 'choice',
                'options' => [
                    '0 - дыхание спокойное',
                    '1 - нерегулярное, частое, задержка'
                ],
            ],
            [
                'question' => 'Руки',
                'type' => 'choice',
                'options' => [
                    '0 - нет ригидности мышц, иногда двигаются',
                    '1 - ригидные, натянуты прямо или быстрое сгибание/разгибание'
                ],
            ],
            [
                'question' => 'Ноги',
                'type' => 'choice',
                'options' => [
                    '0 - нет ригидности мышц, иногда двигаются',
                    '1 - ригидные, натянуты прямо или быстрое сгибание/разгибание'
                ],
            ],
            [
                'question' => 'Состояние возбуждения',
                'type' => 'choice',
                'options' => [
                    '0 - спокойно спит, бодрствует',
                    '1 - беспокойный, мечется'
                ],
            ],
        ];

        foreach ($nipsQuestions as $q) {
            DocumentQuestion::query()->create([
                'document_id' => $nipsPainAssessment->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
            ]);
        }

        $nipsRiskScore = [
            [
                'from_score' => 0,
                'to_score' => 3,
                'actions' => [
                    'боль маловероятна',
                ],
            ],
            [
                'from_score' => 4,
                'to_score' => 7,
                'actions' => [
                    'боль вероятна'
                ],
            ],
        ];

        foreach ($nipsRiskScore as $scoreRange) {
            $scoreModel = DocumentScore::create([
                'document_id' => $nipsPainAssessment->id,
                'from_score' => (int)$scoreRange['from_score'],
                'to_score' => $scoreRange['to_score'] !== null ? (int)$scoreRange['to_score'] : null,
            ]);

            foreach ($scoreRange['actions'] as $actionText) {
                DocumentScoreAction::create([
                    'document_score_id' => $scoreModel->id,
                    'action' => $actionText,
                ]);
            }
        }

        $cpotPainAssessment = Document::query()->create([
            'title' => 'Лист оценки боли CPOT (невербальные пациенты)',
        ]);

        $cpotQuestions = [
            // CPOT assessment components
            [
                'question' => 'Выражение лица (Facial expressions)',
                'type' => 'choice',
                'options' => [
                    '0 - Расслабленное, нейтральное',
                    '1 - Напряженное (хмурится, опускает бровь)',
                    '2 - Гримасы (крепко закрыты веки, открыт рот)'
                ],
            ],
            [
                'question' => 'Движения тела (Body movements)',
                'type' => 'choice',
                'options' => [
                    '0 - Отсутствие движений или нормальное положение',
                    '1 - Защита (медленные, осторожные движения)',
                    '2 - Беспокойство (пытается встать, борется)'
                ],
            ],
            [
                'question' => 'Толерантность к ИВЛ (для интубированных)',
                'type' => 'choice',
                'options' => [
                    '0 - Толерантен к ИВЛ',
                    '1 - Кашляет но толерантен',
                    '2 - Борется с ИВЛ'
                ],
            ],
            [
                'question' => 'Издавание звуков (для неинтубированных)',
                'type' => 'choice',
                'options' => [
                    '0 - Нормальный тон или нет звука',
                    '1 - Вздыхает, стонет',
                    '2 - Кричит, рыдает'
                ],
            ],
            [
                'question' => 'Напряжение мышц (Muscle tension)',
                'type' => 'choice',
                'options' => [
                    '0 - Расслаблены',
                    '1 - Напряжены, ригидны',
                    '2 - Очень напряжены или очень ригидны'
                ],
            ],

            [
                'question' => 'Интерпретация баллов CPOT',
                'type' => 'choice',
                'options' => [
                    '0-1 - нет боли, покой / выжидательная тактика',
                    '2-5 - боль есть, но медикаментозная помощь не обязательна',
                    '6-8 - боль есть, рассмотреть медикаментозную терапию'
                ],
            ],
        ];

        foreach ($cpotQuestions as $q) {
            DocumentQuestion::query()->create([
                'document_id' => $cpotPainAssessment->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
            ]);
        }

        $cpotRiskScore = [
            [
                'from_score' => 0,
                'to_score' => 1,
                'actions' => [
                    'Наблюдать за пациентом в течение 1 минуты два раза в сутки',
                ],
            ],
            [
                'from_score' => 2,
                'to_score' => 5,
                'actions' => [
                    'Смена трахеостомической трубки',
                    'установка/смена назогастрольного зонда',
                    'установка/смена мочевого катетера',
                ],
            ],
            [
                'from_score' => 6,
                'to_score' => 8,
                'actions' => [
                    'Оценить реакцию (Р) через 15-60 мин после вмешательства'
                ],
            ]
        ];

        foreach ($cpotRiskScore as $scoreRange) {
            $scoreModel = DocumentScore::create([
                'document_id' => $cpotPainAssessment->id,
                'from_score' => (int)$scoreRange['from_score'],
                'to_score' => $scoreRange['to_score'] !== null ? (int)$scoreRange['to_score'] : null,
            ]);

            foreach ($scoreRange['actions'] as $actionText) {
                DocumentScoreAction::create([
                    'document_score_id' => $scoreModel->id,
                    'action' => $actionText,
                ]);
            }
        }
    }
}
