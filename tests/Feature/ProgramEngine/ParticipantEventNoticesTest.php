<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Currency;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Program;
use App\Models\ProgramDocument;
use Database\Seeders\WorkTravelProgramSeeder;

/** Avisos in-app al participante ante eventos del admin (motor). */
class ParticipantEventNoticesTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    public function test_document_review_notifies_participant(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $doc = ProgramDocument::create(['program_process_id' => $process->id, 'requirement_key' => 'cedula', 'stage_key' => 'admission', 'file_path' => 'x.pdf', 'original_filename' => 'x.pdf', 'status' => 'pending']);
        $url = fn (string $name, array $extra = []) => route("admin.program.{$name}", array_merge(['program' => 'work-travel', 'process' => $process->id], $extra));

        $this->actingAs($admin)->put($url('documents.review', ['document' => $doc->id]), ['action' => 'reject', 'rejection_reason' => 'Foto ilegible'])->assertRedirect();
        $n = Notification::where('user_id', $process->user_id)->where('category', 'documents')->latest('id')->first();
        $this->assertStringStartsWith('Documento rechazado: Cédula', $n->title);
        $this->assertStringContainsString('Foto ilegible', $n->message);

        $this->actingAs($admin)->put($url('documents.review', ['document' => $doc->id]), ['action' => 'approve'])->assertRedirect();
        $this->assertDatabaseHas('notifications', ['user_id' => $process->user_id, 'category' => 'documents', 'title' => 'Documento aprobado: Cédula de Identidad']);
    }

    public function test_visa_appointment_set_and_changed_notifies_once_each(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $url = route('admin.program.visa.update', ['program' => 'work-travel', 'process' => $process->id]);

        $this->actingAs($admin)->put($url, ['appointment_date' => '2026-10-05', 'appointment_time' => '10:30', 'embassy' => 'Asunción'])->assertRedirect();
        $this->actingAs($admin)->put($url, ['appointment_date' => '2026-10-05', 'appointment_time' => '10:30', 'embassy' => 'Asunción'])->assertRedirect();
        $this->actingAs($admin)->put($url, ['appointment_date' => '2026-10-12'])->assertRedirect();

        $visa = Notification::where('user_id', $process->user_id)->where('category', 'visa')->orderBy('id')->get();
        $this->assertCount(2, $visa);
        $this->assertSame('Cita de visa agendada', $visa[0]->title);
        $this->assertStringContainsString('05/10/2026 a las 10:30 en Asunción', $visa[0]->message);
        $this->assertSame('Cita de visa modificada', $visa[1]->title);
    }

    public function test_payment_verify_and_reject_notify_participant(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $app = $process->application;
        $mk = fn () => Payment::create([
            'application_id' => $app->id, 'user_id' => $app->user_id, 'program_id' => $app->program_id,
            'currency_id' => Currency::firstOrCreate(['code' => 'USD'], ['name' => 'Dólar', 'symbol' => '$', 'exchange_rate_to_pyg' => 1, 'is_active' => true])->id,
            'amount' => 150, 'converted_amount' => 150, 'concept' => 'Inscripción', 'payment_date' => '2026-06-01', 'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.payments.verify', $mk()->id))->assertRedirect();
        $this->assertDatabaseHas('notifications', ['user_id' => $app->user_id, 'category' => 'payment', 'title' => 'Pago verificado']);
        $this->assertStringContainsString('USD 150.00 del 01/06/2026', Notification::where('title', 'Pago verificado')->value('message'));

        $this->actingAs($admin)->post(route('admin.payments.reject', $mk()->id), ['rejection_reason' => 'Comprobante ilegible'])->assertRedirect();
        $this->assertStringContainsString('Comprobante ilegible', Notification::where('title', 'Pago rechazado')->value('message'));
    }
}
