<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Get the tickets for the authenticated user (or all open tickets for admin).
     */
    public function inbox()
    {
        $me = Auth::user();

        if ($me->role === 'admin') {
            // Admin sees all tickets with their unread counts
            $tickets = SupportTicket::with(['creator'])
                ->withCount(['messages as unread_count' => function ($query) use ($me) {
                    $query->where('is_read', false)
                          ->where(function($q) use ($me) {
                              $q->whereNull('to_user_id')->orWhere('to_user_id', $me->id);
                          });
                }])
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn($t) => [
                    'id'           => $t->id,
                    'from_user_id' => $t->creator_id,
                    'from_name'    => $t->creator_name ?? 'User',
                    'from_role'    => $t->creator_role ?? 'student',
                    'subject'      => $t->subject,
                    'status'       => $t->status,
                    'unread_count' => $t->unread_count,
                    'time'         => $t->updated_at->diffForHumans(),
                ])->values();
        } else {
            // Users see their own tickets
            $tickets = SupportTicket::where('creator_id', $me->id)
                ->withCount(['messages as unread_count' => function ($query) use ($me) {
                    $query->where('is_read', false)->where('to_user_id', $me->id);
                }])
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn($t) => [
                    'id'           => $t->id,
                    'from_user_id' => $me->id, // doesn't matter for user view
                    'from_name'    => 'Support Inbox',
                    'from_role'    => '',
                    'subject'      => $t->subject,
                    'status'       => $t->status,
                    'unread_count' => $t->unread_count,
                    'time'         => $t->updated_at->diffForHumans(),
                ])->values();
        }

        return response()->json($tickets);
    }

    /**
     * Create a new ticket.
     */
    public function createTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:100',
            'message' => 'required|string|max:2000'
        ]);

        $me = Auth::user();

        if ($me->role === 'admin') {
            return response()->json(['error' => 'Admins cannot create tickets.'], 403);
        }

        $ticket = SupportTicket::create([
            'creator_id'   => $me->id,
            'creator_name' => $me->name,
            'creator_role' => $me->role,
            'subject'      => $request->subject,
            'status'       => 'open',
        ]);

        SupportMessage::create([
            'ticket_id'    => $ticket->id,
            'from_user_id' => $me->id,
            'from_name'    => $me->name,
            'from_role'    => $me->role,
            'to_user_id'   => null,
            'message'      => $request->message,
        ]);

        $ticket->touch(); // trigger updated_at

        return response()->json(['success' => true, 'ticket' => $ticket]);
    }

    /**
     * Get the conversation thread for a specific ticket
     */
    public function messages(Request $request)
    {
        $me = Auth::user();
        $ticketId = $request->integer('ticket_id');

        if (!$ticketId) {
            return response()->json([]);
        }

        $ticket = SupportTicket::find($ticketId);
        if (!$ticket) return response()->json([], 404);

        if ($me->role !== 'admin' && $ticket->creator_id !== $me->id) {
            return response()->json([], 403);
        }

        $messages = SupportMessage::where('ticket_id', $ticketId)->orderBy('created_at', 'asc')->get();

        // Mark unread as read
        if ($me->role === 'admin') {
            SupportMessage::where('ticket_id', $ticketId)
                ->where('from_user_id', '!=', $me->id)
                ->whereIn('to_user_id', [null, $me->id])
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } else {
            SupportMessage::where('ticket_id', $ticketId)
                ->where('to_user_id', $me->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json($messages->map(fn($m) => [
            'id'           => $m->id,
            'from_user_id' => $m->from_user_id,
            'from_name'    => $m->from_name,
            'from_role'    => $m->from_role,
            'message'      => $m->message,
            'is_read'      => $m->is_read,
            'mine'         => $m->from_user_id === $me->id,
            'time'         => $m->created_at->diffForHumans(),
            'time_full'    => $m->created_at->format('M d, g:i a'),
        ]));
    }

    /**
     * Send a support message into a specific ticket.
     */
    public function send(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'message'   => 'required|string|max:2000'
        ]);

        $me = Auth::user();
        $ticket = SupportTicket::find($request->ticket_id);

        if ($me->role !== 'admin' && $ticket->creator_id !== $me->id) {
            return response()->json([], 403);
        }

        if ($me->role === 'admin') {
            SupportMessage::create([
                'ticket_id'    => $ticket->id,
                'from_user_id' => $me->id,
                'from_name'    => $me->name,
                'from_role'    => $me->role,
                'to_user_id'   => $ticket->creator_id,
                'message'      => $request->message,
            ]);
        } else {
            SupportMessage::create([
                'ticket_id'    => $ticket->id,
                'from_user_id' => $me->id,
                'from_name'    => $me->name,
                'from_role'    => $me->role,
                'to_user_id'   => null,
                'message'      => $request->message,
            ]);
        }

        $ticket->touch();

        return response()->json(['success' => true]);
    }

    /**
     * Count of unread messages for the authenticated user.
     */
    public function unreadCount()
    {
        $me = Auth::user();

        if ($me->role === 'admin') {
            $count = SupportMessage::whereNull('to_user_id')
                ->where('from_user_id', '!=', $me->id)
                ->where('is_read', false)
                ->count();
        } else {
            $count = SupportMessage::where('to_user_id', $me->id)
                ->where('is_read', false)
                ->count();
        }

        return response()->json(['count' => $count]);
    }

    /**
     * ─── CENTRAL SUPPORT INTEGRATION (For Tenant Admin -> Central Admin) ───
     */

    public function centralInbox()
    {
        $me = Auth::user();
        if ($me->role !== 'admin') return response()->json([], 403);

        $tenantId = tenant('id');

        $tickets = tenancy()->central(function () use ($tenantId) {
            return \App\Models\CentralSupportConversation::where('tenant_id', $tenantId)
                ->withCount(['messages as unread_count' => function ($query) use ($tenantId) {
                    $query->where('is_read', false)->where('to_user_id', $tenantId); // using tenant_id as proxy to_user_id for now? 
                    // Actually, "to_user_id" in central DB could just be NULL meaning "To Central Admin", and if Central Admin replies it sets "to_user_id = creator_id" (which is tenant->owner_id).
                    // Let's rely on is_read=false where from_user_id != user's central id. But the Tenant Admin doesn't have a central user ID easily.
                    // Wait, Central Admin replies to "tenant_id".
                }])
                ->orderByDesc('updated_at')
                ->get();
        });

        // Let's refine the unread logic inside central.
        $tickets = tenancy()->central(function () use ($tenantId, $me) {
            return \App\Models\CentralSupportConversation::where('tenant_id', $tenantId)
                ->withCount(['messages as unread_count' => function ($query) use ($me) {
                    $query->where('is_read', false)->whereNotNull('from_role')->where('from_role', '!=', 'admin');
                    // Wait, central admin is 'admin'. So any message NOT from 'admin' is unread... no, if Central Admin sends it, they are role='central_admin' or just to_user_id.
                    // Actually, if we just check is_read=false AND from != me.id ... wait, central DB doesn't know $me->id exactly? Yes it does, but it's a different table.
                }])
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn($t) => [
                    'id'           => $t->id,
                    'from_name'    => 'Central Support',
                    'from_role'    => '',
                    'subject'      => $t->subject,
                    'status'       => $t->status,
                    'unread_count' => \App\Models\CentralSupportMessage::where('conversation_id', $t->id)
                                        ->where('is_read', false)
                                        ->where('from_user_id', '!=', $me->id)
                                        ->count(),
                    'time'         => $t->updated_at->diffForHumans(),
                ])->values();
        });

        return response()->json($tickets);
    }

    public function centralCreateTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:100',
            'message' => 'required|string|max:2000'
        ]);

        $me = Auth::user();
        if ($me->role !== 'admin') return response()->json([], 403);
        $tenantId = tenant('id');

        $ticket = tenancy()->central(function () use ($tenantId, $me, $request) {
            $t = \App\Models\CentralSupportConversation::create([
                'tenant_id'    => $tenantId,
                'creator_id'   => $me->id,
                'creator_name' => $me->name,
                'creator_role' => 'tenant_admin',
                'subject'      => $request->subject,
                'status'       => 'open',
            ]);

            \App\Models\CentralSupportMessage::create([
                'conversation_id' => $t->id,
                'from_user_id'    => $me->id,
                'from_name'       => $me->name . ' (' . tenant('school_name') . ')',
                'from_role'       => 'tenant_admin',
                'to_user_id'      => null, // to central admin
                'message'         => $request->message,
            ]);

            $t->touch();
            return $t;
        });

        return response()->json(['success' => true, 'ticket' => $ticket]);
    }

    public function centralMessages(Request $request)
    {
        $me = Auth::user();
        if ($me->role !== 'admin') return response()->json([], 403);
        $ticketId = $request->integer('ticket_id');
        $tenantId = tenant('id');

        $messages = tenancy()->central(function () use ($ticketId, $tenantId, $me) {
            $ticket = \App\Models\CentralSupportConversation::where('tenant_id', $tenantId)->find($ticketId);
            if (!$ticket) return [];

            // mark as read
            \App\Models\CentralSupportMessage::where('conversation_id', $ticketId)
                ->where('from_user_id', '!=', $me->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return \App\Models\CentralSupportMessage::where('conversation_id', $ticketId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($m) => [
                    'id'           => $m->id,
                    'from_user_id' => $m->from_user_id,
                    'from_name'    => $m->from_name,
                    'from_role'    => $m->from_role,
                    'message'      => $m->message,
                    'is_read'      => $m->is_read,
                    'mine'         => $m->from_user_id === $me->id,
                    'time'         => $m->created_at->diffForHumans(),
                    'time_full'    => $m->created_at->format('M d, g:i a'),
                ]);
        });

        return response()->json($messages);
    }

    public function centralSend(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|integer',
            'message'   => 'required|string|max:2000'
        ]);

        $me = Auth::user();
        if ($me->role !== 'admin') return response()->json([], 403);
        $tenantId = tenant('id');

        tenancy()->central(function () use ($tenantId, $me, $request) {
            $ticket = \App\Models\CentralSupportConversation::where('tenant_id', $tenantId)->find($request->ticket_id);
            if ($ticket) {
                \App\Models\CentralSupportMessage::create([
                    'conversation_id' => $ticket->id,
                    'from_user_id'    => $me->id,
                    'from_name'       => $me->name . ' (' . tenant('school_name') . ')',
                    'from_role'       => 'tenant_admin',
                    'to_user_id'      => null,
                    'message'         => $request->message,
                ]);
                $ticket->touch();
            }
        });

        return response()->json(['success' => true]);
    }
}
