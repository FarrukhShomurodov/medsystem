<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Тип</th>
        <th>Название</th>
        <th>Цена</th>
        <th>Валюта</th>
        <th>Статус</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->type->label() }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->price->toDecimal() }}</td>
            <td>{{ $item->currency }}</td>
            <td>{{ $item->status->label() }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    @foreach($totals->all() as $currency => $sum)
        <tr>
            <td colspan="3"><strong>Итого:</strong></td>
            <td><strong>{{ $sum->toDecimal() }}</strong></td>
            <td colspan="2"><strong>{{ $currency }}</strong></td>
        </tr>
    @endforeach
    </tfoot>
</table>
