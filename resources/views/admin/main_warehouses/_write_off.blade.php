{{-- Writes off exactly this stock line ($row of the StockSection $type), whatever else the warehouse holds of the item. $due: an expired batch, which only waits for this button. --}}
<form action="{{ route('main.warehouse.receipts.goods.discard', ['type' => $type->value, 'id' => $row->getKey()]) }}"
      method="POST" class="d-inline"
      data-confirm="Списать со склада «{{ $name }}» ({{ $row->quantity }} шт.)?" data-confirm-button="Списать">
    @csrf
    @method('DELETE')
    <button type="submit" @class(['btn btn-sm', 'btn-danger' => $due ?? false, 'btn-outline-danger' => ! ($due ?? false)])>Списать</button>
</form>
