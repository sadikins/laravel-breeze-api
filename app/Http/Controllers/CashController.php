<?php

namespace App\Http\Controllers;

use App\Models\Cash;
use Illuminate\Support\Str;
use App\Http\Resources\CashResource;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CashController extends Controller
{
    public function index()
    {
        $from = request('from');
        $to = request('to');

        if ($from && $to) {
            $debit  = $this->getBalace($from, $to, ">=");
            $credit = $this->getBalace($from, $to, "<");
            $transaction = Auth::user()->cashes()->whereBetween('when', [$from, $to])->latest()->get();
        } else {
            $debit  = $this->getBalace(now()->firstOfMonth(), now(), ">=");
            $credit = $this->getBalace(now()->firstOfMonth(), now(), "<");
            $transaction = Auth::user()->cashes()->whereBetween('when', [now()->firstOfMonth(), now()])->latest()->get();
        }

        return response()->json([
            'debit'        => formatPrice($debit),
            'credit'       => formatPrice($credit),
            'balances'     => formatPrice(Auth::user()->cashes()->get('amount')->sum('amount')),
            'transaction'  => CashResource::collection($transaction),
            'now'          => now()->format("Y-m-d"),
            'firstOfMonth' => now()->firstOfMonth()->format("Y-m-d"),
        ]);
    }

    public function store()
    {
        request()->validate([
            'name'   => 'required',
            'amount' => 'required|numeric',
        ]);

        $slug = Str::slug(request('name')) . "-" . Str::random(6);
        $when = request('when') ?? Carbon::now()->toDateTimeString();

        $cash = Auth::user()->cashes()->create([
            'name'        => request('name'),
            'slug'        => Str::slug($slug),
            'when'        => $when,
            'amount'      => request('amount'),
            'description' => request('description'),
        ]);

        return response()->json([
            'message' => 'The transaction has been saved.',
            'cash'    => new CashResource($cash)
        ]);
    }


    public function show(Cash $cash)
    {
        $this->authorize('show', $cash);

        return new CashResource($cash);
    }


    public function getBalace($from, $to, $operator)
    {

        return Auth::user()->cashes()
            ->whereBetween('when', [$from, $to])
            ->where('amount', $operator, 0)
            ->get('amount')
            ->sum('amount');
    }
}
