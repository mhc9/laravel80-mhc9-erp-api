@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="px-10 pt-2 pb-4 flex justify-center">
                <object
                    data="{{ url('/uploads/manuals/manual-user.pdf') }}"
                    type="application/pdf"
                    width="100%"
                    height="720px"
                >
                    <p>Unable to display PDF file.<a href="{{ url('/uploads/manuals/manual-user.pdf') }}" target="_blank" class="ml-2 underline">Download</a> instead.</p>
                </object>
            </div>
        </div>
    </div>
</div>
@endsection
