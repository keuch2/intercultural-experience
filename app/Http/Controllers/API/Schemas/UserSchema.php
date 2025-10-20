<?php

namespace App\Http\Controllers\API\Schemas;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="Usuario",
 *     description="Modelo de usuario del sistema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del usuario"),
 *     @OA\Property(property="name", type="string", example="Juan Pérez", description="Nombre completo del usuario"),
 *     @OA\Property(property="email", type="string", format="email", example="juan@example.com", description="Email del usuario"),
 *     @OA\Property(property="role", type="string", enum={"user", "admin"}, example="user", description="Rol del usuario"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+595981234567", description="Teléfono del usuario"),
 *     @OA\Property(property="nationality", type="string", nullable=true, example="Paraguaya", description="Nacionalidad del usuario"),
 *     @OA\Property(property="birth_date", type="string", format="date", nullable=true, example="1990-01-01", description="Fecha de nacimiento"),
 *     @OA\Property(property="address", type="string", nullable=true, example="Av. Mcal. López 123", description="Dirección del usuario"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Estado del usuario"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-01T00:00:00Z", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-01T00:00:00Z", description="Fecha de última actualización")
 * )
 * 
 * @OA\Schema(
 *     schema="Program",
 *     type="object",
 *     title="Programa",
 *     description="Programa de intercambio cultural",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del programa"),
 *     @OA\Property(property="name", type="string", example="Work and Travel USA 2024", description="Nombre del programa"),
 *     @OA\Property(property="description", type="string", example="Programa de trabajo y turismo en Estados Unidos", description="Descripción detallada"),
 *     @OA\Property(property="country", type="string", example="Estados Unidos", description="País destino"),
 *     @OA\Property(property="main_category", type="string", enum={"IE", "YFU"}, example="IE", description="Categoría principal"),
 *     @OA\Property(property="subcategory", type="string", example="Work and Travel", description="Subcategoría específica"),
 *     @OA\Property(property="location", type="string", nullable=true, example="Nueva York", description="Ciudad o región específica"),
 *     @OA\Property(property="start_date", type="string", format="date", nullable=true, example="2024-06-01", description="Fecha de inicio"),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2024-12-31", description="Fecha de finalización"),
 *     @OA\Property(property="application_deadline", type="string", format="date", nullable=true, example="2024-05-01", description="Fecha límite de aplicación"),
 *     @OA\Property(property="duration", type="string", nullable=true, example="6 meses", description="Duración del programa"),
 *     @OA\Property(property="capacity", type="integer", example=20, description="Capacidad total de participantes"),
 *     @OA\Property(property="available_spots", type="integer", example=15, description="Cupos disponibles"),
 *     @OA\Property(property="cost", type="number", format="float", example=5000.00, description="Costo del programa"),
 *     @OA\Property(property="currency_id", type="integer", example=1, description="ID de la moneda"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Estado del programa"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-01T00:00:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Application",
 *     type="object",
 *     title="Aplicación",
 *     description="Aplicación de un usuario a un programa",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la aplicación"),
 *     @OA\Property(property="user_id", type="integer", example=1, description="ID del usuario aplicante"),
 *     @OA\Property(property="program_id", type="integer", example=1, description="ID del programa"),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, example="pending", description="Estado de la aplicación"),
 *     @OA\Property(property="motivation_letter", type="string", nullable=true, example="Estoy muy interesado en...", description="Carta de motivación"),
 *     @OA\Property(property="emergency_contact_name", type="string", nullable=true, example="María Pérez", description="Nombre del contacto de emergencia"),
 *     @OA\Property(property="emergency_contact_phone", type="string", nullable=true, example="+595981234567", description="Teléfono del contacto de emergencia"),
 *     @OA\Property(property="progress_percentage", type="number", format="float", example=75.5, description="Porcentaje de progreso"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-01T00:00:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     title="Respuesta API",
 *     description="Estructura estándar de respuesta de la API",
 *     @OA\Property(property="status", type="string", enum={"success", "error"}, example="success", description="Estado de la respuesta"),
 *     @OA\Property(property="message", type="string", example="Operación exitosa", description="Mensaje descriptivo"),
 *     @OA\Property(property="data", type="object", nullable=true, description="Datos de la respuesta")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="Error de Validación",
 *     description="Estructura de error de validación",
 *     @OA\Property(property="status", type="string", example="error", description="Estado del error"),
 *     @OA\Property(property="message", type="string", example="The given data was invalid.", description="Mensaje de error"),
 *     @OA\Property(property="errors", type="object", description="Errores específicos por campo")
 * )
 */
class UserSchema
{
    // This class exists only to hold OpenAPI schema annotations
}
