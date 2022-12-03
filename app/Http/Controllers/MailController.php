<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\refund;
use App\Mail\Receipt;
use App\Mail\Member;
use App\Mail\Fund;
use Illuminate\Support\Facades\Auth;
use Session;

class MailController extends Controller
{
    public function sendMail(){

        $msg=Session::get('msg');
        $name=Session::get('name');
        $mail=Session::get('mail');
        $attch=Session::get('attch');
        Mail::to($mail)->send(new refund($msg, $name, $attch));
        
        Session::flash('success', "Refund Processed");
        return back();

    }

    public function sendReceipt(Request $request){

        $msg='Thank you for auctioning with us. See you until your next bid with us!';
        
        Session::put('msg',$msg);
        Session::put('id',$request->id);
        Session::put('refnum',$request->refnum);
        Session::put('total',$request->total);
        Session::put('tracknum',$request->tracknum);
        Session::put('placed_at',$request->placed_at);
        $name=Auth::user()->username;
        $mail=Auth::user()->email;
        Mail::to($mail)->send(new Receipt($msg, $name,$mail));
        
        Session::flash('success', "Check your email to view your receipt.");
        return back();

    }

    public function sendMember(Request $request){


        $name = Auth::user()->username;
        $mail=Auth::user()->email;

        
        Mail::to($mail)->send(new Member($name));
        
        Session::flash('success', "You have Succesfully Paid the Membership Fee.");
        return redirect('/home');
    }

    public function sendFund(Request $request){

        
        $name = Auth::user()->username;
        $amt = Session::get('amount');
        $mail=Auth::user()->email;

        
        Mail::to($mail)->send(new fund($name, $amt));
        
        Session::flash('success', "You have Succesfully Added Php ".$amt." to your account");
        return redirect('/fundings');
    }
    
}
