<?php

namespace App\Http\Controllers\Admin;

use App\Mail\ReplyMail;
use App\Models\Contact;
use App\Http\Controllers\Controller;
use App\Mail\SupportMail;
use App\Rules\ValidateEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index(){
        $contacts=Contact::whereIn('status',['unread','read'])->where('for',ADMIN)->orderByDesc('id')->paginate(PAGINATION);
        return view('admin.contact.index',compact('contacts'));
    }
    public function reply(Request $request){
        $request->validate([
            'email'  =>['required','email',new ValidateEmail()],
            'subject'=>'required',
            'message'=>'required'
        ]);

        Mail::to($request->email)->send(new ReplyMail($request->subject,$request->message));
        success("Mail Sent Successfully !!");
        return redirect()->route('contact.index');
    }

    public function destroy($id){
        $contact=Contact::findOrFail($id);
        if ($contact->for == ADMIN && auth()->user()->role == ADMIN){
            $contact->delete();
            success();
        }else{
            fail();
        }
        return redirect()->route('contact.index');
    }
    public function update($id){
        $contact=Contact::findOrFail($id);
        if ($contact->status == 'read')
            $contact->status='unread';
        else
            $contact->status='read';

        $contact->save();
        success();
        return redirect()->route('contact.index');
    }
}
