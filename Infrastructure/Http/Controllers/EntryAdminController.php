<?php


namespace App\Modules\Commerce\Infrastructure\Http\Controllers;

use App\Modules\Commerce\DomainModel\Balance\Charge;
use App\Modules\Commerce\DomainModel\Balance\Income;
use App\Modules\Commerce\DomainModel\Balance\DeleteAnEntryJob;
use App\Modules\Commerce\DomainModel\Balance\EntryRepository;
use App\Modules\Commerce\DomainModel\Order\OrderRepository;
use App\Modules\Commerce\DomainModel\Order\Status;
use App\Modules\Commerce\DomainModel\Payment\PaymentRepository;
use App\Modules\Common\Infrastructure\viewEntity;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;


class EntryAdminController extends Controller
{

    public function __construct(){
        \Assets::add('admin-entries');
        view()->share('model', null);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $statuses = Income::getPresentationData();
        $selected = array_where($statuses, function ($key, $value) {
            return $value['tip'] == 'ni-ok' ? $key : false;
        });

        \JavaScript::put([
            'statuses' => array_keys($selected),
        ]);

        \Assets::add('assets/modules/admin/entries/js/entries.index.js');
        return view('admin.entries.index', compact('statuses'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $job = new DeleteAnEntryJob($id);
            $this->dispatch($job);

            return redirect()
                ->route('admin.entries.index')
                ->with('alert-success', \Lang::get('common.deleted_ok'));

        }catch(\Exception $e) {

//			dd($e->getMessage());

            $result = $this->getForeignKeyErrorIfExists($e, 'entries', 'admin.entries.index');
            if($result) return $result;
            return $this->getCommonError('admin.entries.index');

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @param EntryRepository $repository
     * @return Response
     */
    public function show($id, EntryRepository $repository)
    {
        try{

            $order = $repository->findByIdOrFail($id);
            $order = new viewEntity($order);
            view()->share('model', $order);
            return view('admin.entries.show',  compact('order'));

        }catch(Exception $e){

            return $this->getCommonError('admin.entries.index');

        }
    }

    /**
     * @param Request $request
     * @param OrderRepository $repository
     * @return mixed
     */
    public function datatableForOrders(Request $request, OrderRepository $repository)
    {
        $orders = $repository->getDataTableDataForEntry();

        $datatables =  \Datatables::of($orders)
            ->filter(function ($instance) use ($request) {
                if ($request->has('search')) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        $needle = array_get($request->get('search'), 'value');
                        $values = array_filter(array_values($row));
                        $result = preg_grep('~' . $needle . '~', $values);
                        return count($result) ? true : false;
                    });
                }
            })
            ->editColumn('amount',function($row){
                $aMoney = json_decode($row['price'], true);
                return $aMoney['amount'].' '.$aMoney['currency'] ;
            })
            ->editColumn('paymentStatus',function($row){
                $data = Charge::getPresentationData($row['paymentStatus']);
                return '<div class="label label-'.$data['class'].'">'.$data['name'].'</div>';
            })
            ->editColumn('buyer',function($row){
                return 'narocnik';
            })
            ->addColumn('row_buttons', function ($row) {
                return
//					\HtmlHelper::btnDeleteDT("admin.orders.destroy", $row['id']) .
                    \HtmlHelper::btnEditDT("admin.orders.edit", $row['id']);
            });

        return $datatables->make(true);
    }

    /**
     * @param Request $request
     * @param PaymentRepository $repository
     * @return mixed
     */
    public function datatableForPayments(Request $request, PaymentRepository $repository)
    {
        $payments = $repository->getDataTableDataForEntry();

        $datatables =  \Datatables::of($payments)
            ->filter(function ($instance) use ($request) {
                if ($request->has('search')) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        $needle = array_get($request->get('search'), 'value');
                        $values = array_filter(array_values($row));
                        $result = preg_grep('~' . $needle . '~', $values);
                        return count($result) ? true : false;
                    });
                }
            })
            ->editColumn('amount',function($row){
                $aMoney = json_decode($row['price'], true);
                return $aMoney['amount'] . ' ' . $aMoney['currency'];
            })
            ->editColumn('status',function($row){
                $data = Income::getPresentationData($row['status']);
                return '<div class="label label-'.$data['class'].'">'.$data['name'].'</div>';
            })
            ->editColumn('payer',function($row){
                return 'placnik';
            })
            ->addColumn('row_buttons', function ($row) {
                return
//					\HtmlHelper::btnDeleteDT("admin.payments.destroy", $row['id']) .
                    \HtmlHelper::btnEditDT("admin.payments.edit", $row['id']);
            });

        return $datatables->make(true);
    }


}