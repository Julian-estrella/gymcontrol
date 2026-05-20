<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGymClassRequest;
use App\Http\Requests\UpdateGymClassRequest;
use App\Models\GymClass;
use App\Models\Trainer;
use Illuminate\Http\Request;

class GymClassController extends Controller
{
    public function index()
    {
        $classes = GymClass::with('trainer')->latest()->get();
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $trainers = Trainer::where('is_active', true)->orderBy('name')->get();
        return view('admin.classes.create', compact('trainers'));
    }

    public function store(StoreGymClassRequest $request)
    {
        $data = $request->validated();

        GymClass::create([
            'name'         => $data['name'],
            'description'  => $data['description'] ?? null,
            'trainer_id'   => $data['trainer_id'],
            'schedule'     => $data['schedule'],
            'max_capacity' => $data['max_capacity'],
            'is_active'    => $request->boolean('is_active', true),
        ]);

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Clase creada',
            'text'  => 'La clase ha sido creada correctamente.',
        ]);

        return redirect()->route('admin.classes.index');
    }

    public function show(GymClass $class)
    {
        $class->load(['trainer', 'clients']);
        
        $enrolledClientIds = $class->clients->pluck('id')->toArray();
        $availableClients = \App\Models\Client::where('membership_status', 'activo')
            ->whereNotIn('id', $enrolledClientIds)
            ->orderBy('name')
            ->get();

        return view('admin.classes.show', compact('class', 'availableClients'));
    }

    public function edit(GymClass $class)
    {
        $trainers = Trainer::where('is_active', true)->orderBy('name')->get();
        return view('admin.classes.edit', compact('class', 'trainers'));
    }

    public function update(UpdateGymClassRequest $request, GymClass $class)
    {
        $data = $request->validated();

        $class->update([
            'name'         => $data['name'],
            'description'  => $data['description'] ?? null,
            'trainer_id'   => $data['trainer_id'],
            'schedule'     => $data['schedule'],
            'max_capacity' => $data['max_capacity'],
            'is_active'    => $request->boolean('is_active'),
        ]);

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Clase actualizada',
            'text'  => 'Los datos de la clase han sido actualizados.',
        ]);

        return redirect()->route('admin.classes.index');
    }

    public function destroy(GymClass $class)
    {
        $class->delete();

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Clase eliminada',
            'text'  => 'La clase ha sido eliminada correctamente.',
        ]);

        return redirect()->route('admin.classes.index');
    }

    public function enroll(Request $request, GymClass $class)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $clientId = $request->client_id;

        if ($class->clients()->count() >= $class->max_capacity) {
            session()->flash('swal', [
                'icon'  => 'error',
                'title' => 'Clase llena',
                'text'  => 'No se puede inscribir más clientes. Se ha alcanzado el cupo máximo.',
            ]);
            return back();
        }

        if ($class->clients()->where('client_id', $clientId)->exists()) {
            session()->flash('swal', [
                'icon'  => 'warning',
                'title' => 'Ya inscrito',
                'text'  => 'El cliente ya se encuentra inscrito en esta clase.',
            ]);
            return back();
        }

        $class->clients()->attach($clientId);

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Inscripción exitosa',
            'text'  => 'El cliente ha sido inscrito a la clase correctamente.',
        ]);

        return back();
    }

    public function unenroll(GymClass $class, \App\Models\Client $client)
    {
        $class->clients()->detach($client->id);

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Baja exitosa',
            'text'  => 'El cliente ha sido dado de baja de la clase.',
        ]);

        return back();
    }
}
