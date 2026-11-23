<?php

namespace App\Support;

/**
 * Catálogo fijo de avisos al participante (Fase 6, nov 2026).
 *
 * Todo aviso queda SIEMPRE en Avisos (in-app). El catálogo solo decide la notificación push:
 *  - cada tipo pertenece a una categoría que el participante puede silenciar desde la app;
 *  - IE puede apagar la push de un tipo o marcarlo obligatorio (`notification_types`, editable en el admin);
 *  - los obligatorios (y los avisos especiales marcados "Importante") se envían aunque la categoría esté silenciada.
 */
class NotificationCatalog
{
    /** Categorías visibles para el participante (Perfil > Notificaciones). */
    public const CATEGORIES = [
        'process' => ['label' => 'Mi proceso', 'description' => 'Aprobación de tu postulación, avances de etapa y fechas del programa.', 'icon' => 'git-branch-outline'],
        'documents' => ['label' => 'Documentos', 'description' => 'Revisión de tus documentos y documentos pendientes.', 'icon' => 'document-text-outline'],
        'payments' => ['label' => 'Pagos', 'description' => 'Pagos verificados o rechazados, cuotas y vencimientos.', 'icon' => 'card-outline'],
        'visa' => ['label' => 'Visa', 'description' => 'Tu cita en la embajada y sus recordatorios.', 'icon' => 'airplane-outline'],
        'job_offers' => ['label' => 'Ofertas laborales', 'description' => 'Pool de ofertas laborales y tu selección.', 'icon' => 'briefcase-outline'],
        'support' => ['label' => 'Soporte', 'description' => 'Seguimientos de tu coordinador y respuestas a tus reportes.', 'icon' => 'chatbubbles-outline'],
        'news' => ['label' => 'Novedades y promociones', 'description' => 'Novedades, promociones y avisos generales de IE.', 'icon' => 'megaphone-outline'],
    ];

    /**
     * Tipos de aviso: key => [categoría, etiqueta, obligatorio por defecto].
     * Obligatorios acordados con IE (23/11/2026): postulación aprobada/no aprobada, documento rechazado,
     * pago rechazado, cita de visa y su recordatorio (+ avisos especiales "Importante").
     */
    public const TYPES = [
        'application_approved' => ['process', 'Postulación aprobada', true],
        'application_rejected' => ['process', 'Postulación no aprobada', true],
        'stage_advanced' => ['process', 'Avance de etapa del proceso', false],
        'program_dates' => ['process', 'Inicio y fin del programa', false],
        'personal_info_pending' => ['process', 'Recordatorio: completar información personal', false],

        'document_approved' => ['documents', 'Documento aprobado', false],
        'document_rejected' => ['documents', 'Documento rechazado', true],
        'documents_pending' => ['documents', 'Recordatorio: documentos pendientes', false],

        'payment_verified' => ['payments', 'Pago verificado', false],
        'payment_rejected' => ['payments', 'Pago rechazado', true],
        'installment_due' => ['payments', 'Recordatorio: cuota por vencer', false],
        'installment_overdue' => ['payments', 'Cuota vencida', false],
        'payment_deadline' => ['payments', 'Recordatorio: fecha límite de pago', false],

        'visa_appointment' => ['visa', 'Cita de visa agendada o modificada', true],
        'visa_appointment_reminder' => ['visa', 'Recordatorio de la cita de visa', true],

        'job_pool_enabled' => ['job_offers', 'Pool de ofertas habilitado', false],
        'job_pool_offer_published' => ['job_offers', 'Nueva oferta laboral disponible', false],
        'job_offer_selected' => ['job_offers', 'Oferta laboral seleccionada', false],
        'job_offer_released' => ['job_offers', 'Asignación de oferta liberada o cancelada', false],

        'support_followup' => ['support', 'Nuevo seguimiento del coordinador', false],
        'support_report_resolved' => ['support', 'Reporte solucionado', false],

        'campaign' => ['news', 'Avisos especiales de IE', false],
    ];

    /** Pantallas de la app a las que puede llevar un aviso especial (la app resuelve la pantalla según el programa). */
    public const LINK_TARGETS = [
        'process' => 'Mi proceso',
        'documents' => 'Documentos',
        'payments' => 'Pagos',
        'visa' => 'Visa',
        'job_pool' => 'Pool de ofertas',
        'support' => 'Soporte',
        'personal_info' => 'Mi información personal',
        'notifications' => 'Avisos',
    ];

    public static function categoryOf(?string $type): ?string
    {
        return $type ? (self::TYPES[$type][0] ?? null) : null;
    }

    public static function label(string $type): string
    {
        return self::TYPES[$type][1] ?? $type;
    }

    public static function defaultMandatory(string $type): bool
    {
        return (bool) (self::TYPES[$type][2] ?? false);
    }

    /** @return array<string, list<string>> categoría => tipos */
    public static function typesByCategory(): array
    {
        $out = array_fill_keys(array_keys(self::CATEGORIES), []);
        foreach (self::TYPES as $key => [$category]) {
            $out[$category][] = $key;
        }

        return $out;
    }
}
