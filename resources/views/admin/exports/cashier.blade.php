<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Тип</th>
        <th>Услуга</th>
        <th>Пациент</th>
        <th>Ответственный</th>
        <th>Цена</th>
        <th>Валюта</th>
        <th>Дата и время</th>
        <th>Статус</th>
    </tr>
    </thead>
    <tbody>
    @foreach($records as $row)
        <tr>
            <td>{{ $row['id'] }}</td>
            <td>{{ $row['type'] }}</td>
            <td>{{ $row['name'] }}</td>
            <td>{{ $row['patient'] }}</td>
            <td>{{ $row['doctor'] }}</td>
            <td>{{ $row['price'] }}</td>
            <td>{{ $row['currency'] }}</td>
            <td>{{ $row['date_time'] }}</td>
            <td>{!! $row['status'] !!}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="5"><strong>ИТОГО:</strong></td>
        <td colspan="4"><strong>{{ $total }}</strong></td>
    </tr>
    </tbody>
</table>
