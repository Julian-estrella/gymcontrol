@extends('layouts.admin')

@section('content')
<x-breadcrumb :links="['Roles' => '']" />

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Gestion de Roles</h1>
    <a href="{{ route('admin.roles.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
        <i class="fa-solid fa-plus mr-2"></i> Nuevo Rol
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acceso admin</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Modulos</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Usuarios</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900 font-semibold">{{ $role->name }}</p>
                        <p class="text-gray-500">{{ $role->slug }}</p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        @if($role->can_access_admin)
                            <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                <span class="relative">Si</span>
                            </span>
                        @else
                            <span class="relative inline-block px-3 py-1 font-semibold text-gray-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-gray-200 opacity-50 rounded-full"></span>
                                <span class="relative">No</span>
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-700">
                        {{ count($role->modules ?? []) }}
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-700">
                        {{ $role->users_count }}
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="text-blue-500 hover:text-blue-800 mr-3">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        @if(! $role->is_system && $role->users_count === 0)
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="text-red-500 hover:text-red-800 btn-delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach

            @if($roles->isEmpty())
                <tr>
                    <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                        No hay roles registrados.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Estas seguro?',
                    text: 'El rol sera eliminado.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Si, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection
