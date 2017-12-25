<?php
/**
 *   (C) Copyright 1997-2013 hSenid International (pvt) Limited.
 *   All Rights Reserved.
 *
 *   These materials are unpublished, proprietary, confidential source code of
 *   hSenid International (pvt) Limited and constitute a TRADE SECRET of hSenid
 *   International (pvt) Limited.
 *
 *   hSenid International (pvt) Limited retains all title to and intellectual
 *   property rights in these materials.
 */

include_once 'lib/sms/SmsReceiver.php';
include_once 'lib/sms/SmsSender.php';

include_once 'lib/sms/Cass.php';
include_once 'log.php';


ini_set( 'error_log', 'sms-app-error.log' );

//check Log.txt to debug send and recieved parameters

try {


	$receiver             = new SmsReceiver(); // Create the Receiver object
	//$address              = $receiver->getAddress(); // get the sender's address

	$destinationAddresses = $receiver->getDestinationAddresses(); // where the final message will be sent

	$password             = $receiver->getPassward();// pass is hardcoded in the reciever class

	$requestId            = $receiver->getRequestID(); // get the request ID

	$applicationId        = $receiver->getApplicationId(); // App id is hardcoded in the reciever class

	$encoding             = $receiver->getEncoding(); // get the encoding value

	$version              = $receiver->getVersion(); // get the version

	$externalTxId         = explode( ':', $destinationAddresses );
	//logFile("[Mobile Number = $parts[1]");
	$responseMsg;
	$charging_amount = "1.25";

	//change the check value to 1 for live production
	$check = 0;

	switch ( $check ) {
		case 0:
			$debit = new DirectDebitSender( "http://localhost:7000/caas/direct/debit", $applicationId, $password );
			break;
		case 1:
			$debit = new DirectDebitSender( "http://developer.bdapps.com/caas/direct/debit", $applicationId, $password );
			break;
	}


//=============================Debit Credit =====================================================


	$status_code = $debit->cass( $externalTxId, $destinationAddresses, $charging_amount );

	logFile( "[recieved  status code from debit is    = $status_code]" );

	if ( $status_code == "E1308" ) {
		$responseMsg = "You dont have enough balance ";
	} else if ( $status_code == "S1000" ) {
		$responseMsg = "Taka " . $charging_amount . " is deducted from your account.";
	} else if ( $status_code == "E1313" ) {
		$responseMsg = "The service is longer active ,please contact Team Shunno via email ";
	} else {
		$responseMsg = "Service is no longer available ";
	}
//=======================Sms Sending  ========================================================
	switch ( $check ) {
		case 0:
			$sender = new SmsSender( "http://localhost:7000/sms/send" );
			break;
		case 1:
			$sender = new SmsSender( "http://developer.bdapps.com/sms/send" );
			break;
	}
	//$sourceAddress              = $address; //not using anywhere
	$deliveryStatusRequest      = "1";
	$destinationAddresses_value = $destinationAddresses;
	$destinationAddresses       = array( $destinationAddresses );
	$binary_header              = "";
	logFile( "[destinationAddresse=$destinationAddresses_value, address=$address, pass=$password, applicationId=$applicationId, encoding=$encoding, version=$version ]" );
	//source_address is not mandatory
	$res = $sender->sms( $responseMsg, $destinationAddresses, $password, $applicationId, $deliveryStatusRequest, $charging_amount, $encoding, $version, $binary_header );
	logFile("[ ============================##########========================]");
} catch
( SmsException $ex ) {
	//throws when failed sending or receiving the sms
	error_log( "ERROR: {$ex->getStatusCode()} | {$ex->getStatusMessage()}" );
}
?>
