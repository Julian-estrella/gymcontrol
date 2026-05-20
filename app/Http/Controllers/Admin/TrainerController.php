<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainerRequest;
use App\Http\Requests\UpdateTrainerRequest;
use App\Models\Trainer;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index()
    {
        $trainers = Trainer::withCount('gymClasses')->latest()->get();
        return view('admin.trainers.index', compact('trainers'));
    }

    public function create()
    {
        return view('admin.trainers.create');
    }

    public function store(StoreTrainerRequest $request)
    {
        $data = $request->validated();

        $data['is_active'] = $request->boolean('is_active', true);
        Trainer::create($data);

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Entrenador registrado',
            'text'  => 'El entrenador ha sido registrado correctamente.',
        ]);

        return redirect()->route('admin.trainers.index');
    }

    public function show(Trainer $trainer)
    {
        $trainer->load('gymClasses');
        return view('admin.trainers.show', compact('trainer'));
    }

    public function edit(Trainer $trainer)
    {
        return view('admin.trainers.edit', compact('trainer'));
    }

    public function update(UpdateTrainerRequest $request, Trainer $trainer)
    {
        $data = $request->validated();

        $data['is_active'] = $request->boolean('is_active');
        $trainer->update($data);

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Entrenador actualizado',
            'text'  => 'Los datos del entrenador han sido actualizados.',
        ]);

        return redirect()->route('admin.trainers.index');
    }

    public function destroy(Trainer $trainer)
    {
        $trainer->delete();

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Entrenador eliminado',
            'text'  => 'El entrenador ha sido eliminado correctamente.',
        ]);

        return redirect()->route('admin.trainers.index');
    }

    public function toggle(Trainer $trainer)
    {
        $trainer->update(['is_active' => !$trainer->is_active]);

        $estado = $trainer->is_active ? 'activado' : 'desactivado';

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Estado actualizado',
            'text'  => "El entrenador ha sido {$estado}.",
        ]);

        return redirect()->route('admin.trainers.index');
    }
}
