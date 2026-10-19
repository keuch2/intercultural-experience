<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupportInbox;
use Illuminate\Http\Request;

/** Acciones sobre los reportes del participante (todos los programas): leído / solucionado. */
class SupportReportController extends Controller
{
    public function __construct(private readonly SupportInbox $inbox) {}

    public function markRead(Request $request, string $kind, int $id)
    {
        $this->inbox->markRead($this->inbox->find($kind, $id), $request->user());

        return back()->with('success', 'Reporte marcado como leído.');
    }

    public function resolve(Request $request, string $kind, int $id)
    {
        $data = $request->validate(['resolution' => 'required|string|max:2000']);
        $this->inbox->resolve($this->inbox->find($kind, $id), $request->user(), $data['resolution']);

        return back()->with('success', 'Reporte marcado como solucionado. El participante fue notificado.');
    }
}
