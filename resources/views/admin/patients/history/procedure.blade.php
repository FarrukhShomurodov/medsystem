{{-- A procedure of the patient card history (api.patients.history.partial). --}}
@use('App\Support\Display')
<div class="datagrid">
    <div class="datagrid-item">
        <div class="datagrid-title">Процедура</div>
        <div class="datagrid-content">{{ $data->procedure->name }}</div>
    </div>
    <div class="datagrid-item">
        <div class="datagrid-title">Дата</div>
        <div class="datagrid-content">{{ Display::date($data->procedure_date) }}</div>
    </div>
</div>
