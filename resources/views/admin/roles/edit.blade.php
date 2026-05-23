@extends('layouts.admin')

@section('content')
<x-breadcrumb :links="['Roles' => route('admin.roles.index'), 'Editar' => '']" />

<div class="mb-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Editar Rol: {{ $role->name }}</h1>
        <a href="{{ route('admin.roles.index') }}" class="text-indigo-600 hover:text-indigo-800">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver a la lista
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden max-w-4xl">
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="p-8">
        @csrf
        @method('PUT')
        @include('admin.roles.partials.form', ['role' => $role])
    </form>
</div>
@endsection
