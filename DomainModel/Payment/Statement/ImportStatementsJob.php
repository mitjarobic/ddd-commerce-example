<?php

namespace App\Modules\Commerce\DomainModel\Payment\Statement;

use App\Jobs\Job;
use App\Modules\Commerce\DomainModel\Balance\Income;
use App\Modules\Commerce\DomainModel\Order\OrderRepository;
use App\Modules\Commerce\DomainModel\Payment\Iban\PaymentIban;
use App\Modules\Commerce\DomainModel\Payment\PaymentRepository;
use App\Modules\Common\DomainModel\Event\EventDispatcher;
use App\Modules\Common\DomainModel\File\CustomFileInfo;
use App\Modules\Common\DomainModel\File\FileRepository;
use App\Modules\Common\DomainModel\File\FileValidationFailedEvent;
use App\Modules\Common\DomainModel\Money;
use DateTime;
use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Gedmo\Uploadable\UploadableListener;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use LaravelDoctrine\ORM\Facades\EntityManager;


class ImportStatementsJob extends Job{

    use InteractsWithQueue, SerializesModels, EventDispatcher;

    /**
     * @var FileInfoInterface
     */
    private $fileInfo;
    private $storagePathToFile;
    private $absolutePathToFile;
    private $storageFileDir;
    private $statementOKCounter = 0;
    private $documentStatementCounter = 0;
    private $paymentsCounter = 0;
    private $paymentsDuplicatedCounter = 0;
    private $entriesCounter = 0;
    private $documentPaymentsCounter = 0;
    private $documentEntriesCounter = 0;
    private $documentPaymentsDuplicatedCounter = 0;
    private $paymentsOkCounter=0;
    private $documentPaymentsOkCounter=0;


    /**
     * Create a new job instance.
     * @param array|FileInfoInterface $fileInfo
     */
    public function __construct($fileInfo)
    {
        $this->fileInfo = $fileInfo;
        $this->absolutePathToFile = $fileInfo->getTmpName();
        $this->storageFileDir = Config::get('nsk.filemanager.storage.temp');
        $this->storagePathToFile = Config::get('nsk.filemanager.storage.files') . $fileInfo->getName();
    }

    public static function fromRequest(Request $request, $key='file')
    {
        $pathToMove = storage_path(Config::get('nsk.storage.files'));

        if ($request->hasFile($key) && $request->file($key)->isValid())
        {
            $validator = Validator::make($request->only(['file']), [
                'file' => 'mimes:xml|max:2000',
            ]);

            $fileName = $request->file($key)->getClientOriginalName();

            if ($validator->fails()){
                event(new FileValidationFailedEvent([
                    'errors' => $validator->errors()->all(),
                    'name' => $fileName
                ]));
            }else{

                //must move the file or the implicit serialization throws an error!
                $request->file($key)->move($pathToMove, $fileName);
                return new self(new CustomFileInfo($pathToMove.$fileName));

            }
        }

        return false;
    }

    public function handle(StatementRepository $statementRepository, PaymentRepository $paymentRepository, FileRepository $fileRepository, OrderRepository $orderRepository){

        try{

            $parser = new XMLSepaParser($this->absolutePathToFile);
            $headerData = $parser->getHeaderData($parser);
            $statementParsers = $parser->getStatementParsers();

            $xmlFile = $this->getXmlFileEntity($fileRepository);

            foreach ($statementParsers as $parser) {

                $statementData = $parser->getParams();
                $payments = $this->getPaymentsToBeStored($paymentRepository, $parser->getPaymentsData());

                if($payments){
                    $statement = $this->createStatementAndAddItToRepository($statementRepository, $headerData, $statementData, $payments, $xmlFile);
                    $this->bindPaymentsAndOrders($statement, $orderRepository);
                }

                $this->fireStatementWasImportedEvent($statementData);
                $this->updateCounters();
            }

            $this->fireDocumentWasImportedEvent();

        }catch(\Exception $e){

            dd($e->getMessage().'-'.$e->getFile());
            \Storage::disk('local')->delete($this->storagePathToFile);

            if($e->getCode() == 101){
                event(new FileValidationFailedEvent([
                    'errors' => [
                        'Xml format ni pravi. Izvoziti je potrebno izpiske ne promet!'
                    ],
                    'name' => $this->fileInfo->getName()
                ]));
            }else{
                throw $e;
            }


        }
    }


    /**
     * @param $fileRepository
     * @return XMLSepaFile
     */
    private function getXmlFileEntity($fileRepository)
    {
        $listener = app(UploadableListener::class);

        if(str_contains(realpath('.'),'public'))
            $this->storageFileDir = '../storage/' . $this->storageFileDir;


        $listener->setDefaultPath($this->storageFileDir);
        $xmlSepa = new XMLSepaFile(
            $fileRepository->nextIdentity()
        );
        $listener->addEntityFileInfo($xmlSepa, $this->fileInfo);
        $fileRepository->add($xmlSepa);
        EntityManager::flush(); //must be flushed or file events are not triggered
        return $xmlSepa;
    }

    /**
     * @param PaymentRepository $paymentRepository
     * @param $paymentsData
     * @return array
     */
    private function getPaymentsToBeStored(PaymentRepository $paymentRepository, $paymentsData )
    {
        $payments = [];

        foreach ($paymentsData as $paymentData) {

            $this->paymentsCounter++;

            if($this->paymentAlreadyExists($paymentRepository, $paymentData)){
                $this->paymentsDuplicatedCounter++;
                continue;
            }

            $payments[] = new PaymentIban(
                $paymentRepository->nextIdentity(),
                new Income(new Money($paymentData['amount'])),
                $paymentData['id'],
                $paymentData['reference'],
                $paymentData['iban']
            );

            $this->paymentsOkCounter++;

        }

        return $payments;
    }

    /**
     * @param PaymentRepository $paymentRepository
     * @param $paymentData
     * @return mixed
     */
    private function paymentAlreadyExists(PaymentRepository $paymentRepository, $paymentData)
    {
        return $paymentRepository->findByExternalId($paymentData['id']);
    }

    /**
     * @param StatementRepository $statementRepository
     * @param $headerData
     * @param $statementData
     * @param $payments
     * @param $xmlFile
     * @return Statement
     */
    private function createStatementAndAddItToRepository(StatementRepository $statementRepository, $headerData, $statementData, $payments, $xmlFile)
    {
        $statement = new Statement(
            $statementRepository->nextIdentity(),
            $headerData['MsgId'],
            new DateTime($headerData['CreDtTm']),
            $statementData['Id'],
            $statementData['LglSeqNb'],
            $statementData['ElctrncSeqNb'],
            new DateTime($statementData['CreDtTm']),
            $payments,
            $xmlFile
        );

        $statementRepository->add($statement);
        $this->statementOKCounter++;
        return $statement;
    }

    private function bindPaymentsAndOrders($statement, OrderRepository $orderRepository)
    {
        foreach($statement->getPayments() as $payment){
            $order = $orderRepository->findByReference($payment->getReference());
            if($order){
                $order->pay($payment);
                $this->entriesCounter++;
            }
        }
    }

    private function updateCounters()
    {
        $this->documentStatementCounter++;
        $this->documentPaymentsCounter += $this->paymentsCounter;
        $this->documentPaymentsOkCounter += $this->paymentsOkCounter;
        $this->documentPaymentsDuplicatedCounter += $this->paymentsDuplicatedCounter;
        $this->documentEntriesCounter += $this->entriesCounter;
        $this->paymentsOkCounter = 0;
        $this->paymentsCounter = 0;
        $this->paymentsDuplicatedCounter = 0;
        $this->entriesCounter = 0;
    }

    /**
     * @param $statementData
     */
    private function fireStatementWasImportedEvent($statementData)
    {
        event(new StatementWasImportedEvent([
            'name' => $this->fileInfo->getName(),
            'number' => $statementData['LglSeqNb'],
            'date' => $statementData['CreDtTm'],
            'numOfPayments' => $this->paymentsCounter,
            'numOfPaymentsOk' => $this->paymentsOkCounter,
            'numOfPaymentsDuplicated' => $this->paymentsDuplicatedCounter,
            'numOfEntries' => $this->entriesCounter,
        ]));
    }

    private function fireDocumentWasImportedEvent()
    {
        event(new DocumentWasImportedEvent([
            'name' => $this->fileInfo->getName(),
            'numOfStatementsOk' => $this->statementOKCounter,
            'numOfStatements' => $this->documentStatementCounter,
            'numOfPaymentsOk' => $this->documentPaymentsOkCounter,
            'numOfPayments' => $this->documentPaymentsCounter,
            'numOfDuplicatedPayments' => $this->documentPaymentsDuplicatedCounter,
            'numOfEntries' => $this->documentEntriesCounter,
        ]));
    }

}