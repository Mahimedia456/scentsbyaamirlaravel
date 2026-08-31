<?php
namespace App\Http\Controllers\Admin; use App\Http\Controllers\Controller; use App\Models\StoreSetting; use Illuminate\Http\Request;
class SettingsController extends Controller { public function index(){return view('admin.settings.index',['settings'=>StoreSetting::pluck('value','key')]);} public function update(Request $r){foreach($r->except('_token','_method') as $k=>$v)StoreSetting::updateOrCreate(['key'=>$k],['group'=>'general','value'=>is_array($v)?json_encode($v):$v]);return back()->with('success','Settings saved.');} }
