<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Events\TenantNotificationEvent;

class MessageNotifyController extends Controller
{
    public function handleNotification(Request $request)
    {
        // 🔹 Log the full incoming payload for debugging
        Log::info('Incoming Notification:', $request->all());

        // 🔹 Validate input
        $data = $request->validate([
            'tenant_id'       => 'nullable|string',
            'sender_id'       => 'nullable|string',
            'sender_email'    => 'nullable|email',
            'receiver_id'     => 'nullable|string',
            'receiver_email'  => 'nullable|email',
            'message'         => 'nullable|string',
            'group_id'        => 'nullable|string',
            'type'            => 'nullable|string',
            'status'          => 'nullable|string',
            'sent_at'         => 'nullable|date',
        ]);
        Log::info("Validation Passed");
        // 🔹 Initialize tenant context if using multi-tenancy
        if (function_exists('tenancy')) {
            tenancy()->initialize($data['tenant_id']);
        }

        // 🔹 Helper closure to find a person in User or Student
        $findPerson = function ($id, $email) {
            $person = User::where('id', $id)->where('email', $email)->first();
            if ($person) {
                $person->role = 'instructor'; // or use $person->role if User table has it
            } else {
                $person = Student::where('id', $id)->where('email', $email)->first();
                if ($person) {
                    $person->role = 'student';
                }
            }
            return $person;
        };

        // 🔹 Find sender and receiver
        $sender = $findPerson($data['sender_id'], $data['sender_email']);
        $receiver = $findPerson($data['receiver_id'], $data['receiver_email']);

        if (!$receiver) {
            Log::warning('Receiver not found', ['receiver_id' => $data['receiver_id']]);
            return response()->json(['status' => 'error', 'message' => 'Receiver not found'], 404);
        }

        if (!$sender) {
            Log::warning('Sender not found', ['sender_id' => $data['sender_id']]);
            return response()->json(['status' => 'error', 'message' => 'Sender not found'], 404);
        }

        // 🔹 Send notification based on user status
        if ($data['status'] === 'offline') {
            // Send email to offline users
            Mail::raw("New message from {$sender->name}: {$data['message']}", function ($msg) use ($receiver) {
                $msg->to($receiver->email)
                    ->subject('You have a new chat message');
            });

            Log::info('📧 Email notification sent to offline receiver', [
                'receiver_email' => $receiver->email,
            ]);
        } else {
            event(new TenantNotificationEvent(
                $data['tenant_id'],
                $receiver->id,
                $data['message'],
                $receiver->role ?? 'student', // role included for channel name
                $sender->name,
            ));
            // For online users, you might push a WebSocket or real-time alert
            Log::info('💬 Online user notification handled (no email)', [
                'receiver_id' => $receiver->id,
            ]);
        }

        Log::info('📨 Message notification processed successfully', $data);

        if (function_exists('tenancy')) {
            tenancy()->end();
        }

        return response()->json(['status' => 'success', 'message' => 'Notification processed']);
    }

    // public function handleNotification(Request $request)
    // {


    //     Log::info($request->all());
    //     return;

    //     $data = $request->validate([
    //         'tenant_id'   => 'required|string',
    //         'sender_id'   => 'required|string',
    //         'receiver_id' => 'required|string',
    //         'message'     => 'required|string',
    //         'group_id'    => 'nullable|string',
    //         'type'        => 'nullable|string',
    //         'sent_at'     => 'nullable|date',
    //     ]);



    //     // 🔹 Switch tenant context if you're using tenancy (Stancl or custom)

    //         tenancy()->initialize($data['tenant_id']);


    //     // 🔹 Lookup receiver
    //     // Try to find the sender first in the users table
    //     $sender = User::where('id', $data['sender_id'])
    //         ->where('email', $data['sender_email'])
    //         ->first();

    //     // If not found, try the students table
    //     if (!$sender) {
    //         $sender = Student::where('id', $data['sender_id'])
    //             ->where('email', $data['sender_email'])
    //             ->first();
    //     }

    //     // Try to find the receiver first in the users table
    //     $receiver = User::where('id', $data['receiver_id'])
    //         ->where('email', $data['receiver_email'])
    //         ->first();

    //     // If not found, try the students table
    //     if (!$receiver) {
    //         $receiver = Student::where('id', $data['receiver_id'])
    //             ->where('email', $data['receiver_email'])
    //             ->first();
    //     }

    //     if (!$receiver) {
    //         return response()->json(['status' => 'error', 'message' => 'Receiver not found'], 404);
    //     }

    //     if ($status == 'offiline') {
    //         // 🔹 Example: Send Email Notification (only if user is offline)
    //         Mail::raw("New message from {$sender->name}: {$data['message']}", function ($msg) use ($receiver) {
    //             $msg->to($receiver->email)
    //                 ->subject('You have a new chat message');
    //         });
    //     } else {
    //         // Show some kind of alert
    //     }

    //     Log::info('📨 Message notification sent', $data);

    //     tenancy()->end();

    //     return response()->json(['status' => 'success', 'message' => 'Notification processed']);
    // }
}
