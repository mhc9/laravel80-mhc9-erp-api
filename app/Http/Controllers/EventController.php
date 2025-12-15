<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\WpmEvent;

class EventController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        // $type = $req->get('type');
        // $cate = $req->get('cate');
        // $group = $req->get('group');
        // $name = $req->get('name');
        // $inStock = $req->get('in_stock');
        // $haveSubitem = $req->get('have_subitem');

        // $items = Item::with('planType','category','group','unit')
        //             ->when(!empty($type), function($q) use ($type) {
        //                 $q->where('plan_type_id', $type);
        //             })
        //             ->when(!empty($cate), function($q) use ($cate) {
        //                 $q->where('category_id', $cate);
        //             })
        //             ->when(!empty($group), function($q) use ($group) {
        //                 $q->where('group_id', $group);
        //             })
        //             ->when($inStock != '', function($q) use ($inStock) {
        //                 $q->where('in_stock', $inStock);
        //             })
        //             ->when($haveSubitem != '', function($q) use ($haveSubitem) {
        //                 $q->where('have_subitem', $haveSubitem);
        //             })
        //             ->when(!empty($name), function($q) use ($name) {
        //                 $q->where(function($query) use ($name) {
        //                     $query->where('item_name', 'like', '%'.$name.'%');
        //                     $query->orWhere('en_name', 'like', '%'.$name.'%');
        //                 });
        //             })
        //             ->orderBy('category_id', 'ASC')
        //             ->orderBy('item_name', 'ASC')
        //             ->orderBy('price_per_unit', 'ASC')
        //             ->paginate(10);

        // return [
        //     'items' => $items,
        // ];
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $sdate  = $req->get('sdate');
        $edate  = $req->get('edate');
        $type   = $req->get('type');

        $events = WpmEvent::with('employee')
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('OTType', $type);
                    })
                    ->when(!empty($sdate) && !empty($edate), function($q) use ($sdate, $edate) {
                        $q->whereBetween('OTDateGo', [$sdate, $edate]);
                        $q->orWhere(function($sq) use ($sdate, $edate) {
                            $sq->where('OTDateBack', '>=', $sdate);
                            $sq->where('OTDateBack', '<=', $edate);
                        });
                    })
                    ->orderBy('OTDateGo', 'ASC')
                    ->get();

        return $events;
    }

    public function getById($id)
    {
        // return [
        //     'item' => Item::with('planType','category','group','unit')->find($id),
        // ];
    }

    public function store(Request $req)
    {
        try {
            // $item = new Item();
            // $item->plan_type_id = $req['plan_type_id'];
            // $item->category_id  = $req['category_id'];
            // $item->group_id     = $req['group_id'];
            // $item->asset_no     = $req['asset_no'];
            // $item->item_name    = $req['item_name'];
            // $item->en_name      = $req['en_name'];
            // $item->price_per_unit = currencyToNumber($req['price_per_unit']);
            // $item->unit_id      = $req['unit_id'];
            // $item->in_stock     = $req['in_stock'];
            // $item->have_subitem = $req['have_subitem'];
            // $item->calc_method  = $req['calc_method'];
            // $item->is_fixcost   = $req['is_fixcost'];
            // $item->is_repairing_item = $req['is_repairing_item'];
            // $item->is_addon     = $req['is_addon'];
            // $item->first_year   = $req['first_year'];
            // $item->remark       = $req['remark'];

            // if($item->save()) {
            //     return [
            //         'status'    => 1,
            //         'message'   => 'Insertion successfully!!',
            //         'item'      => $item
            //     ];
            // } else {
            //     return [
            //         'status'    => 0,
            //         'message'   => 'Something went wrong!!'
            //     ];
            // }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function update(Request $req, $id)
    {
        try {
            // $item = Item::find($id);
            // $item->plan_type_id = $req['plan_type_id'];
            // $item->category_id  = $req['category_id'];
            // $item->group_id     = $req['group_id'];
            // $item->asset_no     = $req['asset_no'];
            // $item->item_name    = $req['item_name'];
            // $item->en_name      = $req['en_name'];
            // $item->price_per_unit = currencyToNumber($req['price_per_unit']);
            // $item->unit_id      = $req['unit_id'];
            // $item->in_stock     = $req['in_stock'];
            // $item->calc_method  = $req['calc_method'];
            // $item->have_subitem = $req['have_subitem'];
            // $item->is_fixcost   = $req['is_fixcost'];
            // $item->is_repairing_item = $req['is_repairing_item'];
            // $item->is_addon     = $req['is_addon'];
            // $item->first_year   = $req['first_year'];
            // $item->remark       = $req['remark'];

            // if($item->save()) {
            //     return [
            //         'status'    => 1,
            //         'message'   => 'Updating successfully!!',
            //         'item'      => $item
            //     ];
            // } else {
            //     return [
            //         'status'    => 0,
            //         'message'   => 'Something went wrong!!'
            //     ];
            // }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function destroy(Request $req, $id)
    {
        try {
            // $item = Item::find($id);

            // if($item->delete()) {
            //     return [
            //         'status'    => 1,
            //         'message'   => 'Deleting successfully!!',
            //         'item'      => $item
            //     ];
            // } else {
            //     return [
            //         'status'    => 0,
            //         'message'   => 'Something went wrong!!'
            //     ];
            // }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }
}
