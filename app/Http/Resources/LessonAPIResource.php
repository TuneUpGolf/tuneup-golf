<?php

namespace App\Http\Resources;

use App\Models\Instructor;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $purchaseQuantity = Purchase::where('lesson_id', $this->id);
        return [
            "lesson_id" => $this->id,
            "lesson_name" => $this->lesson_name,
            "lesson_price" => $this->lesson_price,
            "lesson_quantity" => $this->lesson_quantity,
            "duration" => $this->lesson_duration,
            "lesson_type" => $this->type,
            "payment_method" => $this->payment_method,
            'totalPurchases' => $purchaseQuantity->count(),
            'completedPurchases' => $purchaseQuantity->where('status', Purchase::STATUS_COMPLETE)->count(),
            "instructor" => new InstructorAPIResource(User::find($this->created_by)),
            "logo" => asset('/storage' . '/' . tenant('id') . '/' . $this->logo_image),
            "banner" => asset('/storage' . '/' . tenant('id') . '/' . $this->banner_image),
            "lesson_description" => $this->lesson_description,
            "slots" => $this->slots,
            "created_at" => $this->created_at,
        ];
    }
}
