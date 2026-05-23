@extends('layouts.admin')

@section('content')
<x-breadcrumb :links="['Roles' => route('admin.roles.index'), 'Registrar' => '']" />

<div class="mb-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Crear Nuevo Rol</h1>
        <a href="{{ route('admin.roles.index') }}" class="text-indigo-600 hover:text-indigo-800">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver a la lista
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden max-w-4xl">
    <form action="{{ route('admin.roles.store') }}" method="POST" class="p-8">
        @csrf
        @include('admin.roles.partials.form', ['role' => null])
    </form>
</div>
@endsection
