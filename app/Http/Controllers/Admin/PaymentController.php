<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Client;
use App\Models\MembershipPlan;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['client', 'membershipPlan', 'registeredBy'])->orderByDesc('created_at');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $payments = $query->paginate(15);
        $clients = Client::orderBy('name')->get();

        return view('admin.payments.index', compact('payments', 'clients'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        $plans = MembershipPlan::where('is_active', true)->orderBy('name')->get();
        
        $selectedClient = $request->has('client_id') ? Client::find($request->client_id) : null;
        
        return view('admin.payments.create', compact('clients', 'plans', 'selectedClient'));
    }

    public function store(StorePaymentRequest $request)
    {
        $data = $request->validated();

        $data['registered_by'] = auth()->id();
        
        $client = Client::find($data['client_id']);

        // Si el pago es registrado como pagado, le asignamos la membresía correspondiente
        if ($data['status'] === 'paid') {
            $plan = MembershipPlan::find($data['membership_plan_id']);
            
            // Marcar membresías activas anteriores como vencido
            $client->clientMemberships()
                ->where('status', 'activo')
                ->update(['status' => 'vencido']);

            // Crear la membresía para el cliente
            $membership = \App\Models\ClientMembership::create([
                'client_id' => $client->id,
                'membership_plan_id' => $plan->id,
                'start_date' => now(),
                'end_date' => now()->addDays($plan->duration_days),
                'status' => 'activo',
            ]);

            // Actualizar estado de membresía del cliente
            $client->update(['membership_status' => 'activo']);

            $data['client_membership_id'] = $membership->id;
        }

        $payment = Payment::create($data);

        // Enviar comprobante por correo electrónico al cliente SOLO si el pago está pagado ("paid")
        if ($payment->status === 'paid') {
            try {
                $payment->load(['client', 'membershipPlan']);
                $recipientEmail = !empty($payment->client->email) ? $payment->client->email : 'julianstarbe@gmail.com';
                
                \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\PaymentReceiptMail($payment));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al enviar correo de recibo de pago: ' . $e->getMessage());
            }
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Pago registrado',
            'text' => 'El pago ha sido registrado exitosamente.'
        ]);

        return redirect()->route('admin.payments.index');
    }

    public function show(Payment $payment)
    {
        $payment->load(['client', 'clientMembership', 'membershipPlan', 'registeredBy']);
        return view('admin.payments.show', compact('payment'));
    }

    public function cancel(Payment $payment)
    {
        $payment->update(['status' => 'cancelled']);

        // If this payment was linked to a membership, cancel it too
        if ($payment->client_membership_id) {
            $membership = \App\Models\ClientMembership::find($payment->client_membership_id);
            if ($membership && $membership->status === 'activo') {
                $membership->update(['status' => 'cancelado']);

                $client = $membership->client;
                // If the client no longer has any active memberships, update their status
                if (!$client->clientMemberships()->where('status', 'activo')->exists()) {
                    $client->update(['membership_status' => 'sin_membresia']);

                    $client->membershipHistories()->create([
                        'status'       => 'sin_membresia',
                        'observations' => 'Membresía cancelada al anular el pago folio ' . $payment->folio,
                    ]);
                }
            }
        }

        session()->flash('swal', [
            'icon'  => 'warning',
            'title' => 'Pago cancelado',
            'text'  => 'El pago ha sido marcado como cancelado y su membresía asociada fue cancelada.',
        ]);

        return back();
    }

    public function downloadPdf(Payment $payment)
    {
        // Require barryvdh/laravel-dompdf to be fully installed.
        // If not installed, it will throw an error, which we handle or just let it crash since user knows.
        if (!class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            session()->flash('swal', [
                'icon' => 'error',
                'title' => 'Error de PDF',
                'text' => 'El paquete dompdf no está instalado.'
            ]);
            return back();
        }

        $payment->load(['client', 'clientMembership', 'membershipPlan', 'registeredBy']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.payments.pdf', compact('payment'));
        
        return $pdf->download('comprobante-pago-' . $payment->folio . '.pdf');
    }
}
