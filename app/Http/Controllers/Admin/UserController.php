<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roleRecord')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->pluck('name', 'slug');

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $data['password'] = bcrypt($data['password']);
        $data['is_active'] = true;

        $user = User::create($data);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Usuario creado correctamente',
            'text' => 'El usuario ha sido creado con éxito'
        ]);

        // Si el usuario creado es un cliente, redirigir a editar el cliente
        if ($user->role === User::ROLE_CLIENTE) {
            return redirect()->route('admin.clients.edit', $user->client);
        }

        return redirect()->route('admin.users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->pluck('name', 'slug');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Si el usuario quiere editar contraseña
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        // Si no viene is_active es porque el checkbox no se marcó (false)
        $data['is_active'] = $request->has('is_active');

        $user->update($data);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Usuario actualizado',
            'text' => 'El usuario ha sido actualizado correctamente'
        ]);

        return redirect()->route('admin.users.edit', $user->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // No permitir que el usuario logueado se borre a si mismo
        if ($user->id == Auth::user()->id) {
            session()->flash('swal', [
                'icon' => 'error',
                'title' => 'Acción denegada',
                'text' => 'No puedes eliminarte a ti mismo',
            ]);
            return redirect(route('admin.users.index'));
        }

        // Eliminar usuario
        $user->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Usuario eliminado',
            'text' => 'El usuario ha sido eliminado correctamente'
        ]);

        return redirect(route('admin.users.index'));
    }
}
