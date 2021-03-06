{{-- Copyright (c) 2015 - 2016 Dane Everitt <dane@daneeveritt.com> --}}

{{-- Permission is hereby granted, free of charge, to any person obtaining a copy --}}
{{-- of this software and associated documentation files (the "Software"), to deal --}}
{{-- in the Software without restriction, including without limitation the rights --}}
{{-- to use, copy, modify, merge, publish, distribute, sublicense, and/or sell --}}
{{-- copies of the Software, and to permit persons to whom the Software is --}}
{{-- furnished to do so, subject to the following conditions: --}}

{{-- The above copyright notice and this permission notice shall be included in all --}}
{{-- copies or substantial portions of the Software. --}}

{{-- THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR --}}
{{-- IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, --}}
{{-- FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE --}}
{{-- AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER --}}
{{-- LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, --}}
{{-- OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE --}}
{{-- SOFTWARE. --}}
@extends('layouts.admin')

@section('title')
    Service Packs for {{ $service->name }}
@endsection

@section('content')
<div class="col-md-12">
    <ul class="breadcrumb">
        <li><a href="/admin">Admin Control</a></li>
        <li><a href="/admin/services">Services</a></li>
        <li><a href="{{ route('admin.services.packs') }}">Packs</a></li>
        <li class="active">{{ $service->name }}</li>
    </ul>
    <h3 class="nopad">Service Packs</h3><hr />
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Service Option</th>
                <th>Total Packs</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($options as $option)
                <tr>
                    <td><a href="{{ route('admin.services.packs.option', $option->id) }}">{{ $option->name }}</a></td>
                    <td>{{ $option->p_count }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">
                    <a href="{{ route('admin.services.packs.new') }}">
                        <button class="pull-right btn btn-xxs btn-primary"><i class="fa fa-plus"></i></button>
                    </a>
                    <a href="{{ route('admin.services.packs.new') }}">
                        <button class="pull-right btn btn-xxs btn-default" style="margin-right:5px;"><i class="fa fa-upload"></i> Install from Template</button>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<script>
$(document).ready(function () {
    $('#sidebar_links').find("a[href='/admin/services/packs']").addClass('active');
});
</script>
@endsection
