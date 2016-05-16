<?php


namespace App\Modules\Commerce\Infrastructure\Http\Controllers;

use App\Modules\Commerce\DomainModel\Balance\Charge;
use App\Modules\Commerce\DomainModel\Order\Status;
use App\Modules\Common\Infrastructure\viewEntity;
use App\Modules\Commerce\DomainModel\Order\OrderRepository;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;


class OrderAdminController extends Controller
{

	public function __construct(){
		\Assets::add('admin-orders');
		view()->share('model', null);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$statuses = Status::getPresentationData();
		$selected = array_where($statuses, function ($key, $value) {
			return $value['tip'] == 'ok' ? $key : false;
		});

		\JavaScript::put([
			'statuses' => array_keys($selected),
		]);

		\Assets::add('assets/modules/admin/orders/js/orders.index.js');
		return view('admin.orders.index', compact('statuses'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 * @param OrderRepository $repository
	 * @return Response
	 */
	public function edit($id, OrderRepository $repository)
	{
		try{

			$order = $repository->findByIdOrFail($id);
			$order = new viewEntity($order);
			return view('admin.orders.edit', compact('order'));

		}catch(Exception $e){

			return $this->getCommonError('admin.orders.index');

		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 * @param OrderUpdateRequest $request
	 * @return Response
	 */
	public function update($id, OrderUpdateRequest $request)
	{
		try{

			$job = UpdateAnOrderJob::fromRequest($id, $request);
			$this->dispatch($job);

			return redirect()
				->route('admin.orders.index')
				->with('alert-success', \Lang::get('common.updated_ok'));

		}catch(Exception $e){

			return back()
				->with('alert-error', \Lang::get('common.error'));

		}
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

			$job = new DeleteAnOrderJob($id);
			$this->dispatch($job);

			return redirect()
				->route('admin.orders.index')
				->with('alert-success', \Lang::get('common.deleted_ok'));

		}catch(\Exception $e) {

			$result = $this->getForeignKeyErrorIfExists($e, 'orders', 'admin.orders.index');
			if($result) return $result;
			return $this->getCommonError('admin.orders.index');

		}

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id, OrderRepository $repository)
	{
		try{

			$order = $repository->findByIdOrFail($id);
			$order = new viewEntity($order);
			view()->share('model', $order);
			return view('admin.orders.show',  compact('order'));

		}catch(Exception $e){

			return $this->getCommonError('admin.orders.index');

		}
	}

	public function datatable(Request $request, OrderRepository $repository)
	{
		$orders = $repository->getDataTableData($request->get('date_from'), $request->get('date_to'), $request->get('statuses'));

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
			->editColumn('status', function($row){
				$data = Status::getPresentationData($row['status']);
				return '<div class="label label-'.$data['class'].'">'.$data['name'].'</div>';
			})
			->editColumn('amount',function($row){
				$aMoney = json_decode($row['price'], true);
				return $aMoney['amount'].' '.$aMoney['currency'] ;
			})
			->editColumn('paymentType',function($row){
				return Lang::get('orders.payment_types.'.$row['paymentType']);
			})
			->editColumn('paymentStatus',function($row){
				$data = Charge::getPresentationData($row['paymentStatus']);
				return '<div class="label label-'.$data['class'].'">'.$data['name'].'</div>';
			})
			->editColumn('buyer',function($row){
				return 'narocnik';
			})
			->editColumn('exports',function($row){
				return '';
			})
			->editColumn('payments',function($row){
				if(!$row['payments']) return '';
				$payments = explode('|', $row['payments']);
				$html = [];
				foreach($payments as $payment){
					list($id, $money) = explode('-', $payment);
					$aMoney = json_decode($money, true);
					$html[]='<a href="">'.$aMoney['amount'].' '.$aMoney['currency'].'</a>';
				}
				return implode('\n', $html);
			})
			->addColumn('row_buttons', function ($row) {
				return
//					\HtmlHelper::btnDeleteDT("admin.orders.destroy", $row['id']) .
					\HtmlHelper::btnEditDT("admin.orders.edit", $row['id']);
			});

		return $datatables->make(true);
	}

}