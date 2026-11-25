{{-- The basic data of a patient (patients/show: the "Общая информация" modal and accordion item). --}}
@use('App\Support\Display')
<div class="datagrid">
    @foreach ([
        'ФИО' => $info['full_name'],
        'Дата рождения' => filled($info['birth_date']) ? Display::date($info['birth_date']).' ('.Display::age($info['birth_date']).')' : null,
        'Пол' => $info['gender'],
        'Гражданство' => $info['citizenship'],
        'Откуда узнали о нас' => $info['referral_source'],
    ] as $infoLabel => $infoValue)
        <div class="datagrid-item">
            <div class="datagrid-title">{{ $infoLabel }}</div>
            <div class="datagrid-content">{{ filled($infoValue) ? $infoValue : '—' }}</div>
        </div>
    @endforeach
</div>
