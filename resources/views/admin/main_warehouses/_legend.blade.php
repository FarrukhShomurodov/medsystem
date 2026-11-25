{{-- What the row colours of the remains tables mean (main and department warehouses). --}}
<ul class="list-inline small text-secondary mb-0">
    <li class="list-inline-item me-3">
        <span class="badge bg-danger text-white">просрочен</span> срок годности прошёл: партия больше не выдаётся и ждёт списания
    </li>
    <li class="list-inline-item me-3">
        <span class="badge bg-danger-lt">истекает</span> красная строка: срок годности истекает в ближайшие дни (срок задан в карточке препарата)
    </li>
    <li class="list-inline-item me-3">
        <span class="badge bg-warning-lt">мало</span> жёлтая строка: остаток не больше порога (для препарата он задан в его карточке)
    </li>
    <li class="list-inline-item">Если совпадают оба условия, строка красная.</li>
</ul>
