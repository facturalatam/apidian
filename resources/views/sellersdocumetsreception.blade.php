@extends('layouts.app', ['is_seller' => true])
@section('title', 'Contact')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Recibir documento, Seleccione el archivo Attached Document XML') }}</div>

                <div class="card-body">
                    <form class="form-horizontal"  name="myform" id="myform" method="POST" action="{{ url('/sellers-document-reception/'.$company_idnumber) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-12">
                                <input class="form-control form-control-lg" type="file" id="formFileInput" name="formFileInput">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-12 offset-md-4">
                                <button type="submit" name="button1" id="button1" class="btn btn-primary">
                                    {{ __('Recibir documento') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
