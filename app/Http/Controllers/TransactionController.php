<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use App\Models\Products;
use App\Models\TransactionDetails;
use App\Models\Carts;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $price = 0;
        $total = 0;
        $carts = Carts::where('user_id', "=", '1')->where('status', '=', 'notyet')->get();
        foreach ($carts as $cart) {
            foreach ($cart->products->discounts as $diskon) {

                if (date('Y-m-d') >= $diskon->start  &&  date('Y-m-d') < $diskon->end) {
                    $price = $cart->products->price - ($diskon->percentage / 100 * $cart->products->price);
                }
            }
            if ($price == 0) {
                $total = $total + ($cart->products->price * $cart->qty);
            } else {
                $total = $total + ($price * $cart->qty);
            }

            // dd($cart->qty);
        }
        $transaksis = Transactions::where('user_id', '=', '1')->get();
        return view('transaksi', compact('transaksis', 'carts', 'total'));
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function show(Transactions $transactions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function edit(Transactions $transactions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transactions $transactions)
    {
        if ($request->proof_of_payment) {

            $gambar = $request->file('proof_of_payment');
            $urlgambar = $gambar->storeAs("img/bukti", md5('Bukti' . $transactions->id . microtime()) . '.' . $gambar->extension());
            $transactions->proof_of_payment = $urlgambar;
            $transactions->update();
        }
        return redirect()->back();
    }

    public function cancel(Transactions $transactions)
    {

        $transactions->status = 'canceled';
        $transactions->update();
        return redirect()->back();
    }
    public function verifbarang(Transactions $transactions)
    {


        $detailTrans = TransactionDetails::where('transaction_id', $transactions->id)->get();

        // dd($detailTrans);
        foreach ($detailTrans as  $detail) {
            $products = Products::where('id', $detail->product_id)->get();
            foreach ($products as $key => $value) {
                Products::where('id', "=", $detail->product_id)->update([
                    'stock' => $value->stock - $detail->qty,
                ]);
            }
        }
        $transactions->status = 'success';
        $transactions->update();
        return redirect()->back();
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transactions $transactions)
    {
        //
    }
}
