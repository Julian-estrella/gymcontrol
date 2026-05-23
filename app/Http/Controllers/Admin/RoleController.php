<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $modules = config('gymcontrol_modules');

        return view('admin.roles.create', compact('modules'));
    }

    public function store(StoreRoleRequest $request)
    {
        $data = $this->roleData($request->validated());

        Role::create($data);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Rol creado',
            'text' => 'El rol ha sido creado correctamente',
        ]);

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        $modules = config('gymcontrol_modules');

        return view('admin.roles.edit', compact('role', 'modules'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $this->roleData($request->validated(), $role);

        $role->update($data);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Rol actualizado',
            'text' => 'El rol ha sido actualizado correctamente',
        ]);

        return redirect()->route('admin.roles.edit', $role);
    }

    public function destroy(Role $role)
    {
        if ($role->is_system || $role->users()->exists()) {
            session()->flash('swal', [
                'icon' => 'error',
                'title' => 'Accion denegada',
                'text' => 'No puedes eliminar un rol del sistema o con usuarios asignados',
            ]);

            return redirect()->route('admin.roles.index');
        }

        $role->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Rol eliminado',
            'text' => 'El rol ha sido eliminado correctamente',
        ]);

        return redirect()->route('admin.roles.index');
    }

    private function roleData(array $data, ?Role $role = null): array
    {
        $canAccessAdmin = (bool) ($data['can_access_admin'] ?? false);

        return [
            'name' => $data['name'],
            'slug' => $role?->is_system ? $role->slug : Str::slug(($data['slug'] ?? null) ?: $data['name']),
            'can_access_admin' => $canAccessAdmin,
            'modules' => $canAccessAdmin ? array_values($data['modules'] ?? []) : [],
        ];
    }
}
