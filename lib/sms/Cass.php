<?php

include_once '../../samples/log.php';

class DirectDebitSender
{
    var $server;
    var $applicationId;
    var $password;
    var $status_code = 0;


    public function __construct($server, $applicationId, $password)
    {
        $this->server = $server;
        $this->applicationId = $applicationId;
        $this->password = $password;
    }

    /*
        Get parameters form the application
        check one or more addresses
        Send them to cassMany
    **/
    public function cass($externalTrxId, $subscriberId, $amount)
    {

        if (isset($subscriberId)) {
            return $this->cassMany($externalTrxId, $subscriberId, $amount);
        } else {
            throw new Exception("Subscriber Id not found ");
        }

    }

    private function cassMany($externalTrxId, $subscriberId, $amount)
    {
        $arrayField = array(
            "applicationId" => $this->applicationId,
            "password" => $this->password,
            "subscriberId" => $subscriberId,
            "amount" => $amount,
            "externalTrxId" => $externalTrxId,
            "currency" => "BDT",
            "paymentInstrumentName" => "Mobile Account"
        );
        $jsonObjectFields = json_encode($arrayField);
	    logFile("[ Sending param for Debit = $jsonObjectFields]");
        return $this->sendRequest($jsonObjectFields);
    }

    public function sendRequest($jsonStream)
    {

        $ch = curl_init($this->server);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStream);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        logFile("[ Received Response for Debit = $res]");
        return $this->handleResponse($res);

    }


    private function handleResponse($jsonResponse)
    {

       // logFile("[response handling class   =$jsonResponse]");
        $obj = json_decode($jsonResponse);
        foreach ($obj as $index => $user) {
            // insert into database here
            //logFile("[value = $index .$user]");
            if ($index == "statusCode") {
                $this->status_code = $user;
            }
        }
        logFile("[returned status code from debit is =  $this->status_code]");
        // this status code is then checked in "SampleSmsApp.php"
        return $this->status_code;
    }

}


class CassException extends Exception
{ //Cass Exception Handler

    var $code;
    var $response;
    var $statusMessage;

    public function __construct($message, $code, $response = null)
    {
        parent::__construct($message);
        $this->statusMessage = $message;
        $this->code = $code;
        $this->response = $response;
    }

    public function getStatusCode()
    {
        return $this->code;
    }

    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    public function getRawResponse()
    {
        return $this->response;
    }

}