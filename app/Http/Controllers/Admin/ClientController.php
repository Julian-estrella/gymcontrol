<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('user')->get();
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        // Obtener usuarios con rol cliente que no tengan ya un cliente asociado
        $users = User::where('role', User::ROLE_CLIENTE)->doesntHave('client')->get();
        return view('admin.clients.create', compact('users'));
    }

    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();

        $client = Client::create($data);

        // Registrar historial si hay un status inicial
        if ($data['membership_status'] !== 'sin_membresia') {
            $client->membershipHistories()->create([
                'status' => $data['membership_status'],
                'observations' => 'Registro inicial',
            ]);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Cliente creado',
            'text' => 'El cliente ha sido registrado correctamente.'
        ]);

        return redirect()->route('admin.clients.index');
    }

    public function show(Client $client)
    {
        $client->load('membershipHistories', 'user');
        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $users = User::where('role', User::ROLE_CLIENTE)
            ->where(function ($query) use ($client) {
                $query->doesntHave('client')
                      ->orWhere('id', $client->user_id);
            })->get();

        return view('admin.clients.edit', compact('client', 'users'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $data = $request->validated();

        $oldStatus = $client->membership_status;
        $client->update([
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'membership_status' => $data['membership_status'],
        ]);

        // Si cambió el estado, agregar al historial
        if ($oldStatus !== $data['membership_status']) {
            $client->membershipHistories()->create([
                'status' => $data['membership_status'],
                'observations' => $request->observations ?? 'Cambio de estado manual',
            ]);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Cliente actualizado',
            'text' => 'Los datos del cliente han sido actualizados.'
        ]);

        return redirect()->route('admin.clients.edit', $client->id);
    }

    public function destroy(Client $client)
    {
        $client->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Cliente eliminado',
            'text' => 'El cliente ha sido eliminado correctamente.'
        ]);

        return redirect()->route('admin.clients.index');
    }
}
