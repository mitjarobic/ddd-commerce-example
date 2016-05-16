<?php


namespace App\Modules\Commerce\Infrastructure\Http\Controllers;

use App\Events\Event;
use App\Modules\Commerce\DomainModel\Balance\Income;
use App\Modules\Commerce\DomainModel\Payment\DeleteAPaymentJob;
use App\Modules\Commerce\DomainModel\Payment\Statement\ImportStatementsJob;
use App\Modules\Commerce\DomainModel\Payment\Statement\StatementRepository;
use App\Modules\Common\Infrastructure\viewEntity;
use App\Modules\Commerce\DomainModel\Payment\PaymentRepository;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;



class StatementAdminController extends Controller
{

	public function __construct(){
		\Assets::add('admin-statements');
		view()->share('model', null);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		\Assets::add('assets/modules/admin/statements/js/statements.index.js');
		return view('admin.payments.statements.index', []);
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
			$job = new DeleteAPaymentJob($id);
			$this->dispatch($job);

			return redirect()
				->route('admin.payments.index')
				->with('alert-success', \Lang::get('common.deleted_ok'));

		}catch(\Exception $e) {

//			dd($e->getMessage());

			$result = $this->getForeignKeyErrorIfExists($e, 'payments', 'admin.payments.index');
			if($result) return $result;
			return $this->getCommonError('admin.payments.index');

		}

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 * @param PaymentRepository $repository
	 * @return Response
	 */
	public function show($id, PaymentRepository $repository)
	{
		try{

			$order = $repository->findByIdOrFail($id);
			$order = new viewEntity($order);
			view()->share('model', $order);
			return view('admin.payments.show',  compact('order'));

		}catch(Exception $e){

			return $this->getCommonError('admin.payments.index');

		}
	}

	/**
	 * @param Request $request
	 * @param PaymentRepository $repository
	 * @return mixed
     */
	public function datatable(Request $request, StatementRepository $repository)
	{

		$statements = $repository->getDataTableData($request->get('date_from'), $request->get('date_to'), $request->get('statuses'));

		$datatables =  \Datatables::of($statements)
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
//				$aMoney = json_decode($row['price'], true);
//				return $aMoney['amount'] . ' ' . $aMoney['currency'];
			})
			->addColumn('details_url', function($row) {
				return url('admin/payments/statements/'.$row['id'].'/datatable');
			})
			->editColumn('file',function($row){
				return '<a href="">'.$row['fileName'].'</a>';
			})
			->addColumn('row_buttons', function ($row) {
				return
//					\HtmlHelper::btnDeleteDT("admin.payments.destroy", $row['id']) .
					\HtmlHelper::btnEditDT("admin.payments.edit", $row['id']);
			});

		return $datatables->make(true);
	}

	public function paymentsDatatable($id, Request $request, PaymentRepository $repository){

		$payments = $repository->getDataTableDataForStatement($id);

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

	/**
	 * Upload xmls
	 * @param Request $request
     */
	public function upload(Request $request)
	{
		try {

			$job = ImportStatementsJob::fromRequest($request);
			if($job)$this->dispatch($job);

			return \Response::json(['status' => 1]);

		}catch(Exception $e){

			dd($e->getMessage().' - '.$e->getFile().' - '.$e->getLine());

		}

	}

}