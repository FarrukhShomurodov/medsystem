<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Тип</th>
        <th>Название</th>
        <th>Цена</th>
        <th>Статус</th>
    </tr>
    </thead>
    <tbody>
    @foreach($allRecords as $i => $item)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item['type'] }}</td>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['price'] }}</td>
            <td>{{ $item['status'] }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="3"><strong>Итого:</strong></td>
        <td colspan="2">{{ $total }} UZS</td>
    </tr>
    </tfoot>
</table>
