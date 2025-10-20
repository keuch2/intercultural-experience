<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy para verificar permisos de usuarios
 * Asegura que agentes solo puedan ver/editar participantes que crearon
 */
class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can view any participants.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'agent']);
    }

    /**
     * Determine if the given user can view the participant.
     */
    public function view(User $user, User $participant): bool
    {
        // Admins pueden ver todo
        if ($user->isAdmin()) {
            return true;
        }

        // Agentes solo pueden ver participantes que crearon
        if ($user->role === 'agent') {
            return $participant->created_by_agent_id === $user->id;
        }

        // Usuarios solo pueden ver su propio perfil
        return $user->id === $participant->id;
    }

    /**
     * Determine if the given user can create participants.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'agent']);
    }

    /**
     * Determine if the given user can update the participant.
     */
    public function update(User $user, User $participant): bool
    {
        // Admins pueden editar todo
        if ($user->isAdmin()) {
            return true;
        }

        // Agentes solo pueden editar participantes que crearon
        if ($user->role === 'agent') {
            return $participant->created_by_agent_id === $user->id;
        }

        // Usuarios solo pueden editar su propio perfil
        return $user->id === $participant->id;
    }

    /**
     * Determine if the given user can delete the participant.
     */
    public function delete(User $user, User $participant): bool
    {
        // Solo admins pueden eliminar
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the given user can assign programs to participants.
     */
    public function assignProgram(User $user, User $participant): bool
    {
        // Admins pueden asignar a cualquiera
        if ($user->isAdmin()) {
            return true;
        }

        // Agentes solo pueden asignar a participantes que crearon
        if ($user->role === 'agent') {
            return $participant->created_by_agent_id === $user->id;
        }

        return false;
    }
}
