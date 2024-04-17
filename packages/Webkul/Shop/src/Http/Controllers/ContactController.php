<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Support\Facades\Event;
use Webkul\Admin\Mail\Customer\ContactNotification;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Subscribes email to the email subscription list
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'name' => 'required',
            'email' => 'email|required',
            'message' => 'required',
        ]);

        $name = request()->input('name');
        $email = request()->input('email');
        $content = request()->input('message');

        Event::dispatch('bagisto.shop.layout.footer.contact.before');

        Mail::queue(new ContactNotification($name, $email, $content));

        Event::dispatch('bagisto.shop.layout.footer.contact.after');

        session()->flash('success', trans('shop::app.contact.contact-success'));

        return redirect()->back();
    }
}
