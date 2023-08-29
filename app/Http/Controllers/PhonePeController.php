<?php

namespace App\Http\Controllers;

use Ixudra\Curl\Facades\Curl;

use Illuminate\Http\Request;

class PhonePeController extends Controller
{
    public function phonepe()
    {
        $data = [
            "merchantId" => "MERCHANTUAT",
            "merchantTransactionId" => "MT7850590068188104",
            "merchantUserId" => "MUID123",
            "amount" => 100,
            "redirectUrl" => route('response'),
            "redirectMode" => "POST",
            "callbackUrl" => route('response'),
            "mobileNumber" => "9999999999",
            "paymentInstrument" => ["type" => "PAY_PAGE"],
        ];


        $encode =   base64_encode(json_encode($data));
        $saltIndex = 1;
        $saltKey = '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399';

        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);
        $finalXHeader =  $sha256 . '###' . $saltIndex;

        $response = Curl::to('https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay')
            ->withHeader('Content-Type: application/json')
            ->withHeader('X-VERIFY:' . $finalXHeader)
            ->withData(json_encode(['request' => $encode]))
            ->post();

        $rData = json_decode($response);
        return redirect()->to($rData->data->instrumentResponse->redirectInfo->url);
    }

    public function response(Request $request)
    {
        $input = $request->all();
        $saltIndex = 1;
        $saltKey = '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399';
        $finalXHeader = hash('sha256', '/pg/v1/status/' . $input['merchantId'] . '/' . $input['transactionId'] . $saltKey) . '###' . $saltIndex;
        $response = Curl::to('https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/status/' . $input['merchantId'] . '/' . $input['transactionId'])
            ->withHeader('Content-Type: application/json')
            ->withHeader('accept: application/json')
            ->withHeader('X-VERIFY:' . $finalXHeader)
            ->withHeader('X-MERCHANT-ID:' . $input['transactionId'])
            ->get();
        dd(json_decode($response));
    }
}
