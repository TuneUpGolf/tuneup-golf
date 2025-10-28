<?php

namespace App\Events;

use App\Models\Announcement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AnnouncementCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $announcement;
    public $studentIds;


    public function __construct(Announcement $announcement, array $studentIds = [])
    {
         $this->announcement = $announcement;
        $this->studentIds = $studentIds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // return new Channel('announcements');

        // return new PrivateChannel('channel-name');
        // if (empty($this->studentIds)) {
        //     // Broadcast to all students
        //     return [new Channel('announcements')];
        // }

        // Broadcast to specific students
        $channels = [];
        foreach ($this->studentIds as $studentId) {
            $channels[] = new PrivateChannel("announcements.{$studentId}");
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'announcement.published';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'content' => $this->announcement->content,
            'created_at' => $this->announcement->created_at->toDateTimeString(),
            'type' => 'success',
        ];
    }
}
