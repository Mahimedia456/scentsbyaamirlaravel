<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $setupRequired=!Schema::hasTable('audit_logs');

        $logs=$setupRequired
            ? new LengthAwarePaginator([],0,50,1,['path'=>$request->url(),'query'=>$request->query()])
            : AuditLog::query()
                ->when($request->filled('q'), fn($q)=>$q->where('action','like','%'.$request->q.'%'))
                ->when($request->filled('entity_type'), fn($q)=>$q->where('entity_type',$request->entity_type))
                ->when($request->filled('user_id'), fn($q)=>$q->where('user_id',$request->user_id))
                ->when($request->filled('date_from'), fn($q)=>$q->whereDate('created_at','>=',$request->date_from))
                ->when($request->filled('date_to'), fn($q)=>$q->whereDate('created_at','<=',$request->date_to))
                ->latest()->paginate(50)->withQueryString();

        $users=Schema::hasTable('users') ? \App\Models\User::orderBy('name')->get(['id','name']) : collect();

        return view('admin.audit.index',compact('logs','setupRequired','users'));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless(Schema::hasTable('audit_logs'),404);

        $rows=AuditLog::query()->latest()->limit(10000)->get();

        return response()->streamDownload(function()use($rows){
            $out=fopen('php://output','w');
            fputcsv($out,['Date','User ID','Action','Entity','Entity ID','IP','Metadata']);
            foreach($rows as $r)fputcsv($out,[$r->created_at?->toDateTimeString(),$r->user_id,$r->action,$r->entity_type,$r->entity_id,$r->ip_address,json_encode($r->meta)]);
            fclose($out);
        },'admin-audit-'.now()->format('Ymd-His').'.csv',['Content-Type'=>'text/csv']);
    }
}
