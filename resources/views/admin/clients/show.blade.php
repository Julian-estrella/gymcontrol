@extends('layouts.admin')

@section('content')
<x-breadcrumb :links="['Clientes' => route('admin.clients.index'), 'Detalles' => '']" />
<div class="mb-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Perfil del Cliente</h1>
        <a href="{{ route('admin.clients.index') }}" class="text-indigo-600 hover:text-indigo-800">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver a la lista
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Información del Cliente -->
    <div class="bg-white rounded-lg shadow p-6 col-span-1">
        <div class="text-center mb-6">
            <div class="h-24 w-24 rounded-full bg-indigo-100 text-indigo-500 mx-auto flex items-center justify-center text-4xl mb-4">
                <i class="fa-solid fa-user"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800">{{ $client->name }}</h2>
            <p class="text-gray-500 text-sm mt-1">
                @if($client->membership_status == 'activo')
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Membresía Activa</span>
                @elseif($client->membership_status == 'vencido')
                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">Membresía Vencida</span>
                @else
                    <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded">Sin Membresía</span>
                @endif
            </p>
        </div>

        <div class="border-t border-gray-200 pt-4 space-y-3">
            <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase">Correo Electrónico</span>
                <span class="text-sm text-gray-800">{{ $client->email ?? 'No registrado' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase">Teléfono</span>
                <span class="text-sm text-gray-800">{{ $client->phone ?? 'No registrado' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase">Usuario de Sistema</span>
                @if($client->user)
                    <span class="text-sm text-indigo-600 font-medium">{{ $client->user->name }}</span>
                @else
                    <span class="text-sm text-gray-500">No vinculado</span>
                @endif
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase">Fecha de Registro</span>
                <span class="text-sm text-gray-800">{{ $client->created_at->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="mt-6 space-y-2">
            <a href="{{ route('admin.clients.edit', $client->id) }}" class="inline-block w-full text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold py-2 px-4 border border-indigo-200 rounded shadow-sm">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Editar Perfil
            </a>
            <a href="{{ route('admin.client-memberships.create', $client->id) }}" class="inline-block w-full text-center bg-green-50 hover:bg-green-100 text-green-700 font-semibold py-2 px-4 border border-green-200 rounded shadow-sm">
                <i class="fa-solid fa-credit-card mr-1"></i> Asignar Membresía
            </a>
        </div>
    </div>

    <div class="col-span-1 md:col-span-2 space-y-6">

        <!-- Membresías asignadas (ClientMembership) -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fa-solid fa-credit-card mr-2 text-indigo-600"></i> Membresías Asignadas
                </h3>
            </div>
            <div class="p-6">
                @php $memberships = $client->clientMemberships()->with('membershipPlan')->orderByDesc('created_at')->get(); @endphp
                @if($memberships->count() > 0)
                    <div class="space-y-3">
                        @foreach($memberships as $membership)
                        <div class="flex items-center justify-between p-4 rounded-lg border
                            {{ $membership->computed_status === 'activo' ? 'border-green-200 bg-green-50' : ($membership->computed_status === 'cancelado' ? 'border-red-100 bg-red-50' : ($membership->computed_status === 'expirado' ? 'border-yellow-200 bg-yellow-50' : 'border-gray-200 bg-gray-50')) }}">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $membership->membershipPlan->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fa-solid fa-calendar mr-1"></i>
                                    {{ $membership->start_date->format('d/m/Y') }} → {{ $membership->end_date->format('d/m/Y') }}
                                </p>
                                @if($membership->notes)
                                    <p class="text-xs text-gray-500 mt-1">{{ $membership->notes }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $membership->computed_status === 'activo' ? 'bg-green-200 text-green-800' : ($membership->computed_status === 'cancelado' ? 'bg-red-200 text-red-800' : ($membership->computed_status === 'expirado' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-200 text-gray-700')) }}">
                                    {{ ucfirst($membership->computed_status) }}
                                </span>
                                @if($membership->status === 'activo')
                                <form action="{{ route('admin.client-memberships.cancel', $membership->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-red-500 hover:text-red-800 text-xs" title="Cancelar membresía"
                                        onclick="return confirm('¿Cancelar esta membresía?')">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.client-memberships.deactivate', $membership->id) }}" method="POST" class="inline ml-2">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-yellow-500 hover:text-yellow-800 text-xs" title="Desactivar membresía"
                                        onclick="return confirm('¿Desactivar esta membresía?')">
                                        <i class="fa-solid fa-pause"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-400 py-6">
                        <i class="fa-solid fa-credit-card text-3xl mb-2"></i>
                        <p class="text-sm">No hay membresías asignadas a este cliente.</p>
                        <a href="{{ route('admin.client-memberships.create', $client->id) }}" class="mt-2 inline-block text-indigo-600 hover:underline text-sm">
                            Asignar primera membresía
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Historial de cambios de estado -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fa-solid fa-clock-rotate-left mr-2 text-indigo-600"></i> Historial de Estado
                </h3>
            </div>
            <div class="p-6">
                @if($client->membershipHistories->count() > 0)
                    <div class="relative border-l border-gray-200 ml-3 space-y-6">
                        @foreach($client->membershipHistories->sortByDesc('created_at') as $history)
                            <div class="mb-6 ml-6">
                                <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-4 ring-white
                                    {{ $history->status == 'activo' ? 'bg-green-100 text-green-600' : ($history->status == 'vencido' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600') }}">
                                    @if($history->status == 'activo')
                                        <i class="fa-solid fa-check text-xs"></i>
                                    @elseif($history->status == 'vencido' || $history->status == 'cancelada')
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    @else
                                        <i class="fa-solid fa-minus text-xs"></i>
                                    @endif
                                </span>
                                <h4 class="flex items-center mb-1 text-sm font-semibold text-gray-900">
                                    Estado: {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                                </h4>
                                <time class="block mb-2 text-xs font-normal leading-none text-gray-400">
                                    {{ $history->created_at->format('d M Y, h:i A') }}
                                </time>
                                <p class="mb-4 text-sm font-normal text-gray-600">
                                    {{ $history->observations ?? 'Sin observaciones' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        <i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300"></i>
                        <p>No hay registros en el historial de este cliente.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Historial de Pagos -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fa-solid fa-receipt mr-2 text-indigo-600"></i> Historial de Pagos
                </h3>
            </div>
            <div class="p-6">
                @php $payments = $client->payments()->with('membershipPlan')->orderByDesc('created_at')->get(); @endphp
                @if($payments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio / Fecha</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Monto / Plan</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ver</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($payments as $payment)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $payment->folio }}</div>
                                        <div class="text-xs text-gray-500">{{ $payment->created_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-green-600">${{ number_format($payment->amount, 2) }}</div>
                                        <div class="text-xs text-gray-500">{{ $payment->membershipPlan->name }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($payment->status === 'paid')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Pagado</span>
                                        @elseif($payment->status === 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendiente</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelado</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('admin.payments.show', $payment->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2" title="Detalles">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.payments.pdf', $payment->id) }}" class="text-gray-600 hover:text-gray-900" title="PDF">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-gray-400 py-6">
                        <i class="fa-solid fa-receipt text-3xl mb-2"></i>
                        <p class="text-sm">No hay pagos registrados para este cliente.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
