<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTeamPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get current team
        $team = $user->currentTeam;
        
        // If no current team, check if user has any team
        if (!$team) {
            $team = $user->teams()->first();
            if ($team) {
                $user->current_team_id = $team->id;
                $user->save();
            } else {
                // User doesn't belong to any team
                return redirect()->route('dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke tim mana pun.');
            }
        }

        // Check if user is owner (owner has all permissions)
        if ($team->isOwner($user)) {
            // Add team to request for later use
            $request->merge(['current_team' => $team]);
            return $next($request);
        }

        // Check team membership and permission
        $membership = $team->members()->where('user_id', $user->id)->first();
        
        if (!$membership) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda bukan anggota tim ini.');
        }

        // Check specific permission
        $permissions = $membership->pivot->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        // Add team and membership to request
        $request->merge([
            'current_team' => $team,
            'team_role' => $membership->pivot->role,
        ]);

        return $next($request);
    }
}
