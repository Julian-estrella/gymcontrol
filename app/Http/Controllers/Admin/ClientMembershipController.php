<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientMembershipRequest;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientMembershipController extends Controller
{
    public function create(Client $client)
    {
        $plans = MembershipPlan::where('is_active', true)->orderBy('name')->get();
        return view('admin.client-memberships.create', compact('client', 'plans'));
    }

    public function store(StoreClientMembershipRequest $request, Client $client)
    {
        $data = $request->validated();

        $data['client_id'] = $client->id;
        $data['status']    = 'activo';

        // Mark previous active memberships as vencido
        $client->clientMemberships()
            ->where('status', 'activo')
            ->update(['status' => 'vencido']);

        ClientMembership::create($data);

        // Update client membership_status
        $client->update(['membership_status' => 'activo']);

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Membresía asignada',
            'text'  => 'La membresía ha sido asignada al cliente correctamente.',
        ]);

        return redirect()->route('admin.clients.show', $client->id);
    }

    /**
    * Deactivate (set to 'vencido') a client membership.
    * Updates client overall membership_status accordingly and logs to history.
    */
    public function deactivate(ClientMembership $clientMembership)
    {
        $clientMembership->update(['status' => 'vencido']);

        $client = $clientMembership->client;
        // Determine if any other active memberships exist
        $hasActive = $client->clientMemberships()->where('status', 'activo')->exists();
        $clientStatus = $hasActive ? 'activo' : 'sin_membresia';
        $client->update(['membership_status' => $clientStatus]);

        // Log to membership history
        $client->membershipHistories()->create([
            'status' => $clientStatus,
            'observations' => 'Membresía desactivada manualmente por admin.',
        ]);

        session()->flash('swal', ['icon' => 'warning', 'title' => 'Membresía desactivada', 'text' => 'La membresía ha sido desactivada (vencida) correctamente.']);

        return redirect()->route('admin.clients.show', $client->id);
    }

    /**
     * Allows an admin to manually change a membership's status to activo, vencido or cancelado.
     * Syncs client's global membership_status and logs to history.
     */
    public function updateStatus(Request $request, ClientMembership $clientMembership)
    {
        $request->validate([
            'status'       => ['required', Rule::in(['activo', 'vencido', 'cancelado'])],
            'observations' => ['nullable', 'string', 'max:255'],
        ]);

        $client    = $clientMembership->client;
        $newStatus = $request->status;
        $oldStatus = $clientMembership->status;

        // If activating this membership, expire all other active ones first
        if ($newStatus === 'activo') {
            $client->clientMemberships()
                ->where('id', '!=', $clientMembership->id)
                ->where('status', 'activo')
                ->update(['status' => 'vencido']);
        }

        $clientMembership->update(['status' => $newStatus]);

        // Sync client's overall membership_status
        if ($newStatus === 'activo') {
            $clientStatus = 'activo';
        } elseif ($client->clientMemberships()->where('status', 'activo')->exists()) {
            $clientStatus = 'activo';
        } else {
            $clientStatus = 'sin_membresia';
        }

        $client->update(['membership_status' => $clientStatus]);

        // Log to membership history
        $observations = $request->observations
            ?? 'Estado cambiado manualmente de ' . $oldStatus . ' a ' . $newStatus;

        $client->membershipHistories()->create([
            'status'       => $clientStatus,
            'observations' => $observations,
        ]);

        $labels = [
            'activo'    => ['icon' => 'success', 'title' => 'Membresía activada',   'text' => 'La membresía ha sido activada correctamente.'],
            'vencido'   => ['icon' => 'warning', 'title' => 'Membresía vencida',    'text' => 'La membresía ha sido marcada como vencida.'],
            'cancelado' => ['icon' => 'warning', 'title' => 'Membresía cancelada',  'text' => 'La membresía ha sido cancelada.'],
        ];

        session()->flash('swal', $labels[$newStatus]);

        return redirect()->route('admin.clients.show', $client->id);
    }

    /**
     * Cancel (set to 'cancelado') a client membership.
     * Updates client overall membership_status accordingly and logs to history.
     */
    public function destroy(ClientMembership $clientMembership)
    {
        $clientMembership->update(['status' => 'cancelado']);

        $client = $clientMembership->client;
        // Determine if any other active memberships exist
        $hasActive = $client->clientMemberships()->where('status', 'activo')->exists();
        $clientStatus = $hasActive ? 'activo' : 'sin_membresia';
        $client->update(['membership_status' => $clientStatus]);

        // Log to membership history
        $client->membershipHistories()->create([
            'status' => $clientStatus,
            'observations' => 'Membresía cancelada manualmente por admin.',
        ]);

        session()->flash('swal', [
            'icon' => 'warning',
            'title' => 'Membresía cancelada',
            'text' => 'La membresía ha sido cancelada correctamente.'
        ]);

        return redirect()->route('admin.clients.show', $client->id);
    }
}

