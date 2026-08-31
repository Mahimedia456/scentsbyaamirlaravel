<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $setupRequired = collect(['orders','customers','products','order_items'])->contains(fn ($t) => !Schema::hasTable($t));
        $from = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->subDays(29)->startOfDay();
        $to = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();

        $stats = ['revenue'=>0,'orders'=>0,'customers'=>0,'products'=>0,'aov'=>0,'units'=>0];
        $top = collect();
        $daily = collect();
        $statuses = collect();

        if (!$setupRequired) {
            $orders = Order::whereBetween('created_at',[$from,$to]);
            $sales = (clone $orders)->whereNotIn('status',['cancelled','refunded']);

            $stats = [
                'revenue'=>(float)(clone $sales)->sum('grand_total'),
                'orders'=>(clone $orders)->count(),
                'customers'=>Customer::whereBetween('created_at',[$from,$to])->count(),
                'products'=>Product::count(),
                'aov'=>(float)((clone $sales)->avg('grand_total')??0),
                'units'=>(int)DB::table('order_items')->join('orders','orders.id','=','order_items.order_id')->whereBetween('orders.created_at',[$from,$to])->sum('order_items.quantity'),
            ];

            $top = DB::table('order_items')
                ->join('orders','orders.id','=','order_items.order_id')
                ->whereBetween('orders.created_at',[$from,$to])
                ->whereNotIn('orders.status',['cancelled','refunded'])
                ->select('order_items.product_name',DB::raw('SUM(order_items.quantity) qty'),DB::raw('SUM(order_items.line_total) revenue'))
                ->groupBy('order_items.product_name')->orderByDesc('revenue')->limit(10)->get();

            $daily = Order::whereBetween('created_at',[$from,$to])->whereNotIn('status',['cancelled','refunded'])
                ->selectRaw('DATE(created_at) day, COUNT(*) orders_count, SUM(grand_total) revenue')
                ->groupBy('day')->orderBy('day')->get();

            $statuses = Order::whereBetween('created_at',[$from,$to])
                ->select('status',DB::raw('COUNT(*) total'))->groupBy('status')->orderByDesc('total')->get();
        }

        return view('admin.analytics.index', compact('stats','top','daily','statuses','setupRequired','from','to'));
    }

    public function export(Request $request): StreamedResponse
    {
        $type = $request->validate(['type'=>['required','in:orders,customers,products']])['type'];

        return response()->streamDownload(function () use ($type) {
            $out=fopen('php://output','w');

            if ($type==='orders') {
                fputcsv($out,['Order','Placed','Customer','Email','Status','Payment','Currency','Total','Tracking']);
                Order::orderByDesc('id')->chunk(500,function($rows)use($out){foreach($rows as $r)fputcsv($out,[$r->order_number,optional($r->placed_at)->toDateTimeString(),$r->customer_name,$r->customer_email,$r->status,$r->payment_status,$r->currency,$r->grand_total,$r->tracking_number]);});
            } elseif ($type==='customers') {
                fputcsv($out,['Name','Email','Phone','Active','Verified','Marketing','Created']);
                Customer::orderByDesc('id')->chunk(500,function($rows)use($out){foreach($rows as $r)fputcsv($out,[$r->full_name,$r->email,$r->phone,$r->is_active?'yes':'no',$r->email_verified_at?'yes':'no',$r->marketing_opt_in?'yes':'no',$r->created_at?->toDateTimeString()]);});
            } else {
                fputcsv($out,['Name','SKU','Status','Price','Size','Tracked','Stock','Available']);
                Product::orderBy('name')->chunk(500,function($rows)use($out){foreach($rows as $r)fputcsv($out,[$r->name,$r->sku,$r->status,$r->base_price,$r->size_label,$r->track_inventory?'yes':'no',$r->stock,$r->is_in_stock?'yes':'no']);});
            }

            fclose($out);
        }, $type.'-export-'.now()->format('Ymd-His').'.csv',['Content-Type'=>'text/csv']);
    }
}
