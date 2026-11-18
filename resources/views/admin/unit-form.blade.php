@extends('layouts.admin')
@section('title', 'Edit unit')
@section('content')
<a href="{{ route('admin.units', $car) }}" class="admin-back"><span class="material-symbols-outlined">arrow_back</span>Unit {{ $car->name }}</a>
<header class="admin-page-header"><div><p class="admin-eyebrow">UNIT KENDARAAN</p><h1>Edit {{ $unit->color }}</h1><p>{{ $unit->code }} · {{ $car->name }}</p></div></header>
<form method="post" action="{{ route('admin.units.update', [$car, $unit]) }}" enctype="multipart/form-data" class="admin-panel admin-form admin-unit-create" style="max-width:640px">
    @csrf @method('PUT')
    @include('admin.unit-fields')
    <button class="admin-button" type="submit">Simpan perubahan</button>
</form>
@endsection
