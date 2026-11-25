<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;

/**
 * Why a stock quantity changed (stock_movements.reason). The values are frozen in the
 * stock_movements migration.
 */
enum StockMovementReason: string implements HasLabel
{
    use HasValues;

    /** Goods received by the main warehouse from a supplier. */
    case Receipt = 'receipt';

    /** A batch or item removed from the main warehouse (expired, damaged, lost). */
    case WriteOff = 'write_off';

    /** Moved from the main warehouse to a department warehouse by an approved supply request. */
    case SupplyRequest = 'supply_request';

    /** Moved from a department warehouse to a pharmacy by a completed transfer. */
    case Transfer = 'transfer';

    /** Sent to the ward for a patient's prescription. */
    case Dispense = 'dispense';

    /** Brought back from the ward when a dispensed prescription was returned. */
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::Receipt => 'Приход',
            self::WriteOff => 'Списание',
            self::SupplyRequest => 'Заказ отделения',
            self::Transfer => 'Перевод в аптеку',
            self::Dispense => 'Выдача по назначению',
            self::Return => 'Возврат с отделения',
        };
    }
}
