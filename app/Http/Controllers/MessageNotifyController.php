<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class MessageNotifyController extends Controller
{
    public function handleNotification(Request $request)
    {


        Log::info($request->all());
        return;

        $data = $request->validate([
            'tenant_id'   => 'required|string',
            'sender_id'   => 'required|string',
            'receiver_id' => 'required|string',
            'message'     => 'required|string',
            'group_id'    => 'nullable|string',
            'type'        => 'nullable|string',
            'sent_at'     => 'nullable|date',
        ]);



        // 🔹 Switch tenant context if you're using tenancy (Stancl or custom)
        if (function_exists('tenancy')) {
            tenancy()->initialize($data['tenant_id']);
        }

        // 🔹 Lookup receiver
        $receiver = User::find($data['receiver_id']);
        $sender   = User::find($data['sender_id']);

        if (!$receiver) {
            return response()->json(['status' => 'error', 'message' => 'Receiver not found'], 404);
        }

        // 🔹 Example: Send Email Notification (only if user is offline)
        Mail::raw("New message from {$sender->name}: {$data['message']}", function ($msg) use ($receiver) {
            $msg->to($receiver->email)
                ->subject('You have a new chat message');
        });

        Log::info('📨 Message notification sent', $data);

        return response()->json(['status' => 'success', 'message' => 'Notification processed']);
    }
}
