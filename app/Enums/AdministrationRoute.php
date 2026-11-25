<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * How a prescribed drug is given to the patient (patient_medications.method).
 */
enum AdministrationRoute: string implements HasLabel
{
    use HasValues;

    case IvBolusSlow = 'iv_bolus_slow';
    case IvDrip = 'iv_drip';
    case Oral = 'oral';
    case Intramuscular = 'intramuscular';
    case Subcutaneous = 'subcutaneous';
    case Intradermal = 'intradermal';
    case Rectal = 'rectal';
    case Nasal = 'nasal';
    case Nasogastric = 'nasogastric';
    case Sublingual = 'sublingual';
    case Auricular = 'auricular';
    case Ophthalmic = 'ophthalmic';
    case Vaginal = 'vaginal';
    case Epidural = 'epidural';
    case Transdermal = 'transdermal';
    case Intrathecal = 'intrathecal';
    case Intraosseous = 'intraosseous';
    case Endotracheal = 'endotracheal';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::IvBolusSlow => 'Внутривенно болюсно медленно',
            self::IvDrip => 'Внутривенно капельно',
            self::Oral => 'Перорально',
            self::Intramuscular => 'Внутримышечно',
            self::Subcutaneous => 'Подкожно',
            self::Intradermal => 'Внутрикожно',
            self::Rectal => 'Ректально',
            self::Nasal => 'Назально',
            self::Nasogastric => 'Назогастрально',
            self::Sublingual => 'Под язык',
            self::Auricular => 'В ухо',
            self::Ophthalmic => 'В глаз',
            self::Vaginal => 'Вагинально',
            self::Epidural => 'Эпидурально',
            self::Transdermal => 'Трансдермально',
            self::Intrathecal => 'Интратекально',
            self::Intraosseous => 'Внутрикостно',
            self::Endotracheal => 'Эндотрахеально',
            self::Other => 'Другое',
        };
    }
}
