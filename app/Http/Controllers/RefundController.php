<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Http\Requests\StoreRefundRequest;
use App\Http\Requests\UpdateRefundRequest;
use App\Models\Biddings;
use App\Models\Bag;
use App\Models\Messages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class RefundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $title = "Refunds";
        return view('pages.refund', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRefundRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRefundRequest $request)
    {   
        $this->validate($request,[
            'gcashnum'=>['required','regex:/^(09|\+639)\d{9}$/u']
        ]);
        $current_date = \Carbon\Carbon::now()->format('Y/m/d');
        $bid_stat = Biddings::where('retractstat',0)
                            ->where('endDate','<',$current_date)
                            ->where('uname', Auth::user()->username)
                            ->get();
        $bag_stat = Bag::where('user_id', Auth::user()->id)
        ->get();

        // if(Auth::user()->funds < 500 ||  $bid_stat !== null || $bag_stat !== null){
        //     Session::flash('error', "Inillegible for a refund. Please check the guidelines.");
        //             return redirect()->back()->withInput();
        // }
        // else{
            $in = new Refund;
                    $in->uname=Auth::User()->username;
                    $in->uid=Auth::User()->id;
                    $in->amount=Auth::User()->funds;
                    $in->gcashnum=$request->input('gcashnum');
                    $in->email=Auth::User()->email;
                    $in->save();

                    $up = User::find(Auth::User()->id);
                    $up->user_status=3;
                    $up->save();

                    Session::flash('success', "Request successfully submitted. Your Account is now Frozen ");
                    return redirect('/refund');
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Refund  $refund
     * @return \Illuminate\Http\Response
     */
    public function show(Refund $refund)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Refund  $refund
     * @return \Illuminate\Http\Response
     */
    public function edit(Refund $refund)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRefundRequest  $request
     * @param  \App\Models\Refund  $refund
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRefundRequest $request, $id)
    {
        $stat = Refund::find($id);
        $stat->status=$request->input('apr');
        $stat->save();

        $fnd = User::find($request->uid);
        
        $fnd->user_status = 1;
        $fnd->funds = 0;
        $fnd->save();

        $msg = $request->input('refundmsg');
        $mail = $request->email;

        $filename= $fnd->username."email.".$request->file('img')->getClientOriginalExtension();
        $request->file('img')->storeAs('mailAttch',$filename,'public_uploads');


        Session::flash('success', "Refund Processed");
        session::put('attch',$filename);
        Session::put('msg',$msg);
        Session::put('name',$fnd->username);
        Session::put('mail',$mail);
        return redirect('/mailex');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Refund  $refund
     * @return \Illuminate\Http\Response
     */
    public function destroy(Refund $refund)
    {
        //
    }

    public function adminindex()
    {
        $title = "Admin | Refund Requests";

        $data = Refund::orderBy('created_at','DESC')
                    ->get();

        return view('admin.refundreq', compact('title','data'));
    }


    public function deny(StoreRefundRequest $request)
    {


        return $request;
    }
}
