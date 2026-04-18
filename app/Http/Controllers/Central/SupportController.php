<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralSupportConversation;
use App\Models\CentralSupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function inbox()
    {
        $me = Auth::user();
        if (!$me) return response()->json([], 403);

        if ($me->is_admin) {
            $conversations = CentralSupportConversation::withCount(['messages as unread_count' => function ($query) use ($me) {
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
                    'from_name'    => $t->creator_name,
                    'from_role'    => $t->creator_role,
                    'subject'      => $t->subject,
                    'status'       => $t->status,
                    'unread_count' => $t->unread_count,
                    'time'         => $t->updated_at->diffForHumans(),
                ])->values();
        } else {
            // Regular central users
            $conversations = CentralSupportConversation::where('creator_id', $me->id)
                ->withCount(['messages as unread_count' => function ($query) use ($me) {
                    $query->where('is_read', false)->where('to_user_id', $me->id);
                }])
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn($t) => [
                    'id'           => $t->id,
                    'from_user_id' => $me->id,
                    'from_name'    => 'Central Support',
                    'from_role'    => '',
                    'subject'      => $t->subject,
                    'status'       => $t->status,
                    'unread_count' => $t->unread_count,
                    'time'         => $t->updated_at->diffForHumans(),
                ])->values();
        }

        return response()->json($conversations);
    }

    public function createTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:100',
            'message' => 'required|string|max:2000'
        ]);

        $me = Auth::user();

        if ($me->is_admin) {
            return response()->json(['error' => 'Admins cannot create tickets.'], 403);
        }

        $ticket = CentralSupportConversation::create([
            'tenant_id'    => null, // Central users don't have a specific tenant logic for their central account here
            'creator_id'   => $me->id,
            'creator_name' => $me->name,
            'creator_role' => 'central_user',
            'subject'      => $request->subject,
            'status'       => 'open',
        ]);

        CentralSupportMessage::create([
            'conversation_id' => $ticket->id,
            'from_user_id'    => $me->id,
            'from_name'       => $me->name,
            'from_role'       => 'central_user',
            'to_user_id'      => null,
            'message'         => $request->message,
        ]);

        $ticket->touch();

        return response()->json(['success' => true, 'ticket' => $ticket]);
    }

    public function messages(Request $request)
    {
        $me = Auth::user();
        if (!$me) return response()->json([], 403);
        
        $ticketId = $request->integer('ticket_id');
        $ticket = CentralSupportConversation::find($ticketId);
        
        if (!$ticket) return response()->json([], 404);

        if (!$me->is_admin && $ticket->creator_id !== $me->id) {
            return response()->json([], 403);
        }

        if ($me->is_admin) {
            CentralSupportMessage::where('conversation_id', $ticketId)
                ->where('from_user_id', '!=', $me->id)
                ->whereIn('to_user_id', [null, $me->id])
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } else {
            CentralSupportMessage::where('conversation_id', $ticketId)
                ->where('to_user_id', $me->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $messages = CentralSupportMessage::where('conversation_id', $ticketId)
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

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:central_support_conversations,id',
            'message'   => 'required|string|max:2000'
        ]);

        $me = Auth::user();
        $ticket = CentralSupportConversation::find($request->ticket_id);

        if (!$me->is_admin && $ticket->creator_id !== $me->id) {
            return response()->json([], 403);
        }

        if ($me->is_admin) {
            CentralSupportMessage::create([
                'conversation_id' => $ticket->id,
                'from_user_id'    => $me->id,
                'from_name'       => $me->name,
                'from_role'       => 'central_admin',
                'to_user_id'      => $ticket->creator_id,
                'message'         => $request->message,
            ]);
        } else {
            CentralSupportMessage::create([
                'conversation_id' => $ticket->id,
                'from_user_id'    => $me->id,
                'from_name'       => $me->name,
                'from_role'       => 'central_user',
                'to_user_id'      => null,
                'message'         => $request->message,
            ]);
        }

        $ticket->touch();
        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        $me = Auth::user();
        if (!$me) return response()->json(['count' => 0]);

        if ($me->is_admin) {
            $count = CentralSupportMessage::whereNull('to_user_id')
                ->where('from_user_id', '!=', $me->id)
                ->where('is_read', false)
                ->count();
        } else {
            $count = CentralSupportMessage::where('to_user_id', $me->id)
                ->where('is_read', false)
                ->count();
        }

        return response()->json(['count' => $count]);
    }
}
