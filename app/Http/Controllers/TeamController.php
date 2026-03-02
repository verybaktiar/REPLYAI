<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * Display team settings.
     */
    public function index()
    {
        $user = auth()->user();
        $teams = $user->teams()->with('owner')->get();
        $currentTeam = $user->currentTeam;
        
        return view('teams.index', compact('teams', 'currentTeam'));
    }

    /**
     * Create a new team.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        // Check if user already has a team (limit to 1 team for now)
        if ($user->ownedTeams()->count() >= 1) {
            return back()->with('error', 'Anda hanya dapat memiliki 1 tim.');
        }

        $team = Team::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(6),
            'owner_id' => $user->id,
            'settings' => [],
        ]);

        // Add owner as member
        $team->addMember($user, TeamMember::ROLE_OWNER);

        // Set as current team
        $user->current_team_id = $team->id;
        $user->save();

        return redirect()->route('teams.index')
            ->with('success', 'Tim berhasil dibuat.');
    }

    /**
     * Switch current team.
     */
    public function switch(Team $team)
    {
        $user = auth()->user();

        if (!$team->hasMember($user)) {
            return back()->with('error', 'Anda bukan anggota tim ini.');
        }

        $user->current_team_id = $team->id;
        $user->save();

        return redirect()->route('dashboard')
            ->with('success', 'Beralih ke tim: ' . $team->name);
    }

    /**
     * Display team members.
     */
    public function members(Team $team)
    {
        $this->authorize('view', $team);

        $members = $team->members()->withPivot('role', 'permissions', 'joined_at')->get();
        $invitations = []; // TODO: Implement invitations

        return view('teams.members', compact('team', 'members', 'invitations'));
    }

    /**
     * Invite a new member.
     */
    public function invite(Request $request, Team $team)
    {
        $this->authorize('manageMembers', $team);

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,manager,agent,viewer',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if already a member
        if ($team->hasMember($user)) {
            return back()->with('error', 'User sudah menjadi anggota tim.');
        }

        $permissions = TeamMember::getDefaultPermissions($request->role);
        $team->addMember($user, $request->role, $permissions);

        // TODO: Send notification email

        return back()->with('success', 'Undangan berhasil dikirim.');
    }

    /**
     * Update member role.
     */
    public function updateMember(Request $request, Team $team, User $user)
    {
        $this->authorize('manageMembers', $team);

        $request->validate([
            'role' => 'required|in:admin,manager,agent,viewer',
        ]);

        if (!$team->hasMember($user)) {
            return back()->with('error', 'User bukan anggota tim.');
        }

        // Cannot change owner's role
        if ($team->isOwner($user)) {
            return back()->with('error', 'Tidak dapat mengubah peran owner.');
        }

        $permissions = TeamMember::getDefaultPermissions($request->role);
        $team->updateMemberRole($user, $request->role, $permissions);

        return back()->with('success', 'Peran anggota berhasil diperbarui.');
    }

    /**
     * Remove a member.
     */
    public function removeMember(Team $team, User $user)
    {
        $this->authorize('manageMembers', $team);

        if ($team->isOwner($user)) {
            return back()->with('error', 'Tidak dapat menghapus owner dari tim.');
        }

        $team->removeMember($user);

        return back()->with('success', 'Anggota berhasil dihapus dari tim.');
    }

    /**
     * Leave team.
     */
    public function leave(Team $team)
    {
        $user = auth()->user();

        if ($team->isOwner($user)) {
            return back()->with('error', 'Owner tidak dapat meninggalkan tim. Transfer ownership terlebih dahulu.');
        }

        $team->removeMember($user);

        // Switch to another team if available
        $otherTeam = $user->teams()->where('teams.id', '!=', $team->id)->first();
        $user->current_team_id = $otherTeam?->id;
        $user->save();

        return redirect()->route('teams.index')
            ->with('success', 'Anda telah meninggalkan tim.');
    }

    /**
     * Delete team.
     */
    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        $team->delete();

        $user = auth()->user();
        $otherTeam = $user->teams()->first();
        $user->current_team_id = $otherTeam?->id;
        $user->save();

        return redirect()->route('teams.index')
            ->with('success', 'Tim berhasil dihapus.');
    }
}
