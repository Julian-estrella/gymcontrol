<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipPlanRequest;
use App\Http\Requests\UpdateMembershipPlanRequest;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    public function index()
    {
        $plans = MembershipPlan::withCount('clientMemberships')->latest()->get();
        return view('admin.membership-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.membership-plans.create');
    }

    public function store(StoreMembershipPlanRequest $request)
    {
        $data = $request->validated();

        $data['is_active'] = $request->boolean('is_active', true);
        MembershipPlan::create($data);

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Plan creado',
            'text'  => 'El plan de membresía ha sido creado correctamente.',
        ]);

        return redirect()->route('admin.membership-plans.index');
    }

    public function edit(MembershipPlan $membershipPlan)
    {
        return view('admin.membership-plans.edit', compact('membershipPlan'));
    }

    public function update(UpdateMembershipPlanRequest $request, MembershipPlan $membershipPlan)
    {
        $data = $request->validated();

        $data['is_active'] = $request->boolean('is_active');
        $membershipPlan->update($data);

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Plan actualizado',
            'text'  => 'El plan de membresía ha sido actualizado.',
        ]);

        return redirect()->route('admin.membership-plans.index');
    }

    public function destroy(MembershipPlan $membershipPlan)
    {
        $membershipPlan->delete();

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Plan eliminado',
            'text'  => 'El plan de membresía ha sido eliminado.',
        ]);

        return redirect()->route('admin.membership-plans.index');
    }

    public function toggle(MembershipPlan $membershipPlan)
    {
        $membershipPlan->update(['is_active' => !$membershipPlan->is_active]);

        $estado = $membershipPlan->is_active ? 'activado' : 'desactivado';

        session()->flash('swal', [
            'icon'  => 'success',
            'title' => 'Estado actualizado',
            'text'  => "El plan ha sido {$estado}.",
        ]);

        return redirect()->route('admin.membership-plans.index');
    }
}
