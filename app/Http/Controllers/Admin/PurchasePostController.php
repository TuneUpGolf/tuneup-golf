<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\PurchasePost;
use App\Models\Student;
use App\Http\Controllers\Controller;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchasePostController extends Controller
{
    public function purchasePost(Request $request)
    {
        $request->validate([
            'post_id' => 'required'
        ]);

        try {
            $post = Post::where('paid', true)->where('id', $request->post_id)->where('status', 'active')->first();
            $purchasePost = PurchasePost::firstOrCreate(
                [
                    'student_id' => Auth::user()->id,
                    'post_id' => $post->id,
                ],
                [
                    'active_status' => false,
                ]
            );

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = Session::create(
                [
                    'line_items'            => [[
                        'price_data'    => [
                            'currency'      => config('services.stripe.currency'),
                            'product_data'  => [
                                'name'      => "$post->title",
                            ],
                            'unit_amount'   => $post->price * 100,
                        ],
                        'quantity'      => 1,
                    ]],
                    'customer' => Auth::user()?->stripe_cus_id,
                    'mode' => 'payment',
                    'success_url' => route('purchase-post-success', [
                        'purchase_post_id' => $purchasePost?->id,
                        'student_id' => Auth::user()->id,
                        'redirect' => $request->redirect
                    ]),
                    'cancel_url' => route('subscription-unsuccess'),
                ]
            );
            if (!empty($session?->id)) {
                $purchasePost->session_id = $session?->id;
                $purchasePost->save();
            }
            if ($request->redirect == 1) {
                return response($session->url);
            }
            return redirect($session->url);
        } catch (Error $e) {
            return response($e, 419);
        }
    }

    public function purchasePostSuccess(Request $request)
    {
        $purchasePost = PurchasePost::find($request->query('purchase_post_id'));
        try {
            if (!!$purchasePost) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session  = Session::retrieve($purchasePost->session_id);

                if ($session->payment_status == "paid") {
                    $purchasePost->active_status = true;
                    $purchasePost->session_id = $session->id;
                    $purchasePost->save();
                    $student = Student::find($request->query('student_id'));
                    if (!isset($student->stripe_cus_id)) {
                        $student->stripe_cus_id = $session->customer;
                        $student->save();
                    }
                }

                if ($request->redirect == 1) {
                    return response('Post Purchased Successfully');
                }

                return redirect()->back()->with('success', 'Post Purchased Successfully');
            }
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        };
    }
}
