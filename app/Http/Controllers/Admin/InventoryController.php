<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request){$products=Product::with(['variants'])->when($request->filled('q'),fn($q)=>$q->where(fn($x)=>$x->where('name','like','%'.$request->q.'%')->orWhere('sku','like','%'.$request->q.'%')))->orderBy('name')->paginate(20)->withQueryString();$adjustments=InventoryAdjustment::with(['product','variant','user'])->latest()->limit(30)->get();return view('admin.inventory.index',compact('products','adjustments'));}
    public function adjust(Request $request)
    {
        $data=$request->validate(['product_id'=>['required','exists:products,id'],'product_variant_id'=>['nullable','exists:product_variants,id'],'quantity_change'=>['required','integer','not_in:0'],'reason'=>['required','string','max:80'],'reference'=>['nullable','string','max:160'],'note'=>['nullable','string','max:1000']]);
        DB::transaction(function()use($data){$product=Product::lockForUpdate()->findOrFail($data['product_id']);$variant=!empty($data['product_variant_id'])?ProductVariant::lockForUpdate()->where('product_id',$product->id)->findOrFail($data['product_variant_id']):null;$current=$variant?(int)$variant->stock:(int)$product->stock;$after=max(0,$current+(int)$data['quantity_change']);if($variant)$variant->update(['stock'=>$after]);else $product->update(['stock'=>$after]);InventoryAdjustment::create($data+['quantity_after'=>$after,'user_id'=>auth()->id()]);});
        return back()->with('success','Inventory adjusted.');
    }
}
