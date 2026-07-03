<Trabajador
    CodigoTrabajador="{{preg_replace("/[\r\n|\n|\r]+/", "", $request->worker_code)}}"
    TipoTrabajador="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->type_worker->code)}}"
    SubTipoTrabajador="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->sub_type_worker->code)}}"
    AltoRiesgoPension="{{preg_replace("/[\r\n|\n|\r]+/", "", json_encode($worker->high_risk_pension))}}"
    TipoDocumento="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->payroll_type_document_identification->code)}}"
    NumeroDocumento="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->identification_number)}}"
    PrimerApellido="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->surname)}}"
    SegundoApellido="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->second_surname)}}"
    PrimerNombre="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->first_name)}}"
    @if(isset($worker->middle_name))
        OtrosNombres="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->middle_name)}}"
    @endif
    LugarTrabajoPais="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->country->code)}}"
    LugarTrabajoDepartamentoEstado="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->department->code)}}"
    LugarTrabajoMunicipioCiudad="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->municipality->code)}}"
    LugarTrabajoDireccion="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->address)}}"
    SalarioIntegral="{{preg_replace("/[\r\n|\n|\r]+/", "", json_encode($worker->integral_salarary))}}"
    TipoContrato="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->type_contract->code)}}"
    Sueldo="{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($worker->salary, 2, '.', ''))}}"
    @isset($worker->worker_code)
        CodigoTrabajador="{{preg_replace("/[\r\n|\n|\r]+/", "", $worker->worker_code)}}"
    @endisset
></Trabajador>
