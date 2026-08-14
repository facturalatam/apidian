{{--
    Barra de búsqueda reutilizable (documentos, nómina, eventos RADIAN).

    Parámetros del @include:
      $searchPrefix : prefijo de los parámetros GET. Ej.: 'invoice_', 'payroll_', 'event_', '' .
      $fields       : lista ordenada de campos a filtrar. Cada uno:
                        ['key' => 'number', 'label' => 'Número', 'control' => 'text']
                        ['key' => 'date',   'label' => 'Fecha',  'control' => 'date']
                        ['key' => 'type',   'label' => 'Tipo',   'control' => 'select', 'options' => [id => 'Nombre']]
      $default      : key del campo seleccionado por defecto.
      $total        : total de registros (para decidir si mostrar la barra).

    Los nombres de parámetros que envía son: {prefix}search (valor / "desde"),
    {prefix}search_field (campo) y {prefix}search_to ("hasta" del rango de fechas).
--}}
@php
    $sp         = $searchPrefix ?? '';
    $valParam   = $sp.'search';
    $fieldParam = $sp.'search_field';
    $toParam    = $sp.'search_to';
    $fieldsList = $fields ?? [];
    $defaultKey = $default ?? (count($fieldsList) ? $fieldsList[0]['key'] : 'number');

    $searchField = request($fieldParam, $defaultKey);
    $searchTerm  = request($valParam);
    $searchTo    = request($toParam);

    $activeDef     = collect($fieldsList)->firstWhere('key', $searchField) ?? collect($fieldsList)->first();
    $activeControl = $activeDef['control'] ?? 'text';
    $activeLabel   = $activeDef['label'] ?? '';
    $isTextRole    = $activeControl === 'text';
    $isDateRole    = $activeControl === 'date';
    $hasDateField  = collect($fieldsList)->contains(function ($f) { return ($f['control'] ?? '') === 'date'; });

    $hasSearch    = filled($searchTerm) || ($isDateRole && filled($searchTo));
    $totalRecords = $total ?? 0;

    // Texto legible del valor buscado para el mensaje de resultados.
    if ($activeControl === 'select') {
        $opts = $activeDef['options'] ?? [];
        $searchDisplay = $opts[$searchTerm] ?? $searchTerm;
    } elseif ($activeControl === 'date') {
        $searchDisplay = (filled($searchTerm) && filled($searchTo)) ? ($searchTerm.' a '.$searchTo) : ($searchTerm ?: $searchTo);
    } else {
        $searchDisplay = $searchTerm;
    }
@endphp

@if($totalRecords > 0 || $hasSearch)
    <div class="card mb-2">
        <div class="card-body py-2 px-3">
            <form method="GET" role="search" class="mb-0 doc-search-bar">
                <div class="d-flex flex-wrap align-items-center doc-search-row">

                    <span class="doc-search-icon"><i class="fas fa-filter"></i></span>

                    {{-- Selector de campo a filtrar --}}
                    <select name="{{ $fieldParam }}" class="form-control doc-search-field" aria-label="Campo a filtrar">
                        @foreach($fieldsList as $f)
                            @php $role = ($f['control'] === 'text') ? 'text' : (($f['control'] === 'date') ? 'date' : $f['key']); @endphp
                            <option value="{{ $f['key'] }}" data-role="{{ $role }}" {{ $searchField === $f['key'] ? 'selected' : '' }}>{{ $f['label'] }}</option>
                        @endforeach
                    </select>

                    {{-- Valor: texto (cualquier campo de tipo "text") --}}
                    <input type="text" name="{{ $valParam }}" data-role="text" autocomplete="off"
                        class="form-control doc-search-value" aria-label="Valor a buscar"
                        placeholder="Escriba el valor a buscar..."
                        value="{{ $isTextRole ? $searchTerm : '' }}"
                        @unless($isTextRole) style="display:none;" disabled @endunless>

                    {{-- Valor: rango de fechas (Desde / Hasta) que abren el mismo calendario --}}
                    @if($hasDateField)
                        <input type="text" name="{{ $valParam }}" data-role="date" readonly autocomplete="off"
                            class="form-control doc-search-value doc-search-date doc-search-daterange"
                            aria-label="Desde" placeholder="Desde"
                            value="{{ $isDateRole ? $searchTerm : '' }}"
                            @unless($isDateRole) style="display:none;" disabled @endunless>
                        <span class="doc-search-value doc-search-sep" data-role="date" @unless($isDateRole) style="display:none;" @endunless>–</span>
                        <input type="text" name="{{ $toParam }}" data-role="date" readonly autocomplete="off"
                            class="form-control doc-search-value doc-search-date doc-search-dateto"
                            aria-label="Hasta" placeholder="Hasta"
                            value="{{ $isDateRole ? $searchTo : '' }}"
                            @unless($isDateRole) style="display:none;" disabled @endunless>
                    @endif

                    {{-- Valor: un select por cada campo de tipo "select" --}}
                    @foreach($fieldsList as $f)
                        @if(($f['control'] ?? '') === 'select')
                            <select name="{{ $valParam }}" data-role="{{ $f['key'] }}" class="form-control doc-search-value" aria-label="{{ $f['label'] }}"
                                @unless($searchField === $f['key']) style="display:none;" disabled @endunless>
                                @foreach(($f['options'] ?? []) as $optVal => $optLabel)
                                    <option value="{{ $optVal }}" {{ (string) $searchTerm === (string) $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                @endforeach
                            </select>
                        @endif
                    @endforeach

                    {{-- Acciones --}}
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                    @if($hasSearch)
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </a>
                    @endif
                </div>
                @if($hasSearch)
                    <small class="text-muted d-block mt-2">
                        {{ $totalRecords }} resultado(s){{ $activeLabel ? ' en '.$activeLabel : '' }} para "<strong>{{ $searchDisplay }}</strong>".
                    </small>
                @endif
            </form>
        </div>
    </div>
@endif

@pushOnce('styles')
<style>
    /* ----- Barra de búsqueda: controles compactos, misma altura y espaciado parejo ----- */
    .doc-search-bar .doc-search-row { gap: .5rem; max-width: 940px; }
    .doc-search-bar .form-control,
    .doc-search-bar .custom-select,
    .doc-search-bar .btn { height: 36px; }
    .doc-search-bar .btn { display: inline-flex; align-items: center; white-space: nowrap; font-size: .875rem; padding: 0 .85rem; }
    .doc-search-bar .doc-search-icon {
        display: inline-flex; align-items: center; justify-content: center;
        flex: 0 0 auto; width: 34px; height: 36px; font-size: .8rem;
        color: #6c757d; background: #f1f3f5; border: 1px solid #ced4da; border-radius: .25rem;
    }
    .doc-search-bar .doc-search-field { flex: 0 0 auto; width: auto; min-width: 150px; }
    .doc-search-bar .doc-search-value { flex: 1 1 240px; min-width: 200px; max-width: 460px; }
    .doc-search-bar .doc-search-date { flex: 0 0 auto; width: 140px; min-width: 120px; max-width: 150px; background-color: #fff; cursor: pointer; }
    .doc-search-bar .doc-search-sep { flex: 0 0 auto; min-width: 0; max-width: none; padding: 0 .15rem; display: inline-flex; align-items: center; color: #6c757d; }

    /* ----- Calendario (daterangepicker): ordenado y legible ----- */
    .daterangepicker.doc-drp { border: 1px solid #e2e6ea; border-radius: .5rem; box-shadow: 0 8px 28px rgba(0,0,0,.14); font-family: inherit; color: #2b323d; }
    .daterangepicker.doc-drp .calendar,
    .daterangepicker.doc-drp .drp-calendar { padding: 8px 10px; max-width: none; }
    .daterangepicker.doc-drp .calendar-table { border: none; padding: 0; background: #fff; }
    .daterangepicker.doc-drp th.month { font-weight: 600; font-size: .9rem; color: #2b323d; padding-bottom: 6px; }
    .daterangepicker.doc-drp tbody td,
    .daterangepicker.doc-drp thead tr:last-child th { width: 36px; min-width: 36px; height: 34px; line-height: 34px; font-size: .85rem; border: none; border-radius: 50%; text-align: center; padding: 0; }
    .daterangepicker.doc-drp thead tr:last-child th { color: #8a94a6; font-weight: 600; font-size: .72rem; text-transform: uppercase; }
    .daterangepicker.doc-drp td.available:hover { background-color: #eef1f5; border-radius: 50%; }
    .daterangepicker.doc-drp td.off, .daterangepicker.doc-drp td.off.in-range { color: #c7ccd6; background: #fff; }
    .daterangepicker.doc-drp td.in-range { background-color: #e8f0fe; color: #1c4a8a; border-radius: 0; }
    .daterangepicker.doc-drp td.active, .daterangepicker.doc-drp td.active:hover { background-color: #2563eb; color: #fff; border-radius: 50%; }
    .daterangepicker.doc-drp td.today:not(.active) { box-shadow: inset 0 0 0 1px #b9c2d0; font-weight: 700; }
    .daterangepicker.doc-drp .prev:hover span, .daterangepicker.doc-drp .next:hover span { border-color: #2563eb; }
    .daterangepicker.doc-drp .input-mini { height: 32px; line-height: 30px; border: 1px solid #ced4da; border-radius: .25rem; font-size: .8rem; color: #2b323d; }
    .daterangepicker.doc-drp .input-mini.active { border-color: #2563eb; background: #fff; }
</style>
@endPushOnce

@pushOnce('scripts')
<script src="{{ asset('porto-light/vendor/moment/moment.js') }}"></script>
<script src="{{ asset('porto-light/vendor/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script>
// Mostrar/ocultar el control de valor según el campo elegido (lee data-role de la opción).
(function () {
    function syncForm(form) {
        if (!form) return;
        var fieldEl = form.querySelector('.doc-search-field');
        if (!fieldEl) return;
        var opt = fieldEl.options[fieldEl.selectedIndex];
        var role = (opt && opt.getAttribute('data-role')) || 'text';
        form.querySelectorAll('.doc-search-value').forEach(function (el) {
            var active = el.getAttribute('data-role') === role;
            el.disabled = !active;            // los deshabilitados no se envían
            el.style.display = active ? '' : 'none';
        });
    }
    document.addEventListener('change', function (e) {
        var t = e.target;
        if (t && t.classList && t.classList.contains('doc-search-field')) syncForm(t.closest('form'));
    });
    function initAll() {
        document.querySelectorAll('form .doc-search-field').forEach(function (sel) { syncForm(sel.closest('form')); });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAll); else initAll();
})();

// Rango de fechas: "Desde" y "Hasta" abren el mismo calendario doble.
jQuery(function ($) {
    if (!$.fn.daterangepicker) return;
    $('.doc-search-daterange').each(function () {
        var $from = $(this);
        if ($from.data('drpReady')) return;
        $from.data('drpReady', true);

        var $to = $from.closest('form').find('.doc-search-dateto');

        var opts = {
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD', separator: ' a ',
                applyLabel: 'Aplicar', cancelLabel: 'Limpiar',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            }
        };
        if ($from.val()) {
            opts.startDate = $from.val();
            opts.endDate = $to.val() || $from.val();
        }

        $from.daterangepicker(opts);

        var drp = $from.data('daterangepicker');
        if (drp && drp.container) drp.container.addClass('doc-drp');

        $from.on('apply.daterangepicker', function (ev, picker) {
            $from.val(picker.startDate.format('YYYY-MM-DD'));
            $to.val(picker.endDate.format('YYYY-MM-DD'));
        });
        $from.on('cancel.daterangepicker', function () {
            $from.val('');
            $to.val('');
        });
        $to.on('click', function () {
            if (!$from.prop('disabled')) drp.show();
        });
    });
});
</script>
@endPushOnce
