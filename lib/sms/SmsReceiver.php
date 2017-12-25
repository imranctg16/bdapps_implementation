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
class SmsReceiver {
	//Define parameters for receive sms data
	// private $sourceAddress = "";
	private $message;
	private $requestId;
	private $applicationId = "APP_004334";
	private $encoding = "0";
	private $version = "1.0";
	private $destinationAddresses;
	private $passward = "0705afb938daad5a10f03af24c0bc71c";

	/*
		decode the json data an get them to an array
		Get data from Json objects
		check the validity of the response
	**/
	public function __construct() {
		$array                      = json_decode( file_get_contents( 'php://input' ), true );
		$this->destinationAddresses = $array['sourceAddress'];
		$this->message              = $array['message'];
		//$this->applicationId = $array['applicationId'];
		$this->requestId = $array['requestId'];
		$this->encoding  = $array['encoding'];
		$this->version   = $array['version'];

		if ( ! ( ( isset( $this->destinationAddresses ) && isset( $this->message ) ) ) ) {
			throw new Exception( "Some of the required parameters are not provided" );
		} else {
			// Successfully received response
			$responses = array( "statusCode" => "S1000", "statusDetail" => "Success" );
			header( "Content-type: application/json" );
			echo json_encode( $responses );
		}
	}

	/*
		Define getters to return receive data
	**/

	public function getAddress() {
		//return $this->sourceAddress;
		return " ";
	}

	/**
	 * @return mixed
	 */
	public function getPassward() {
		return $this->passward;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getRequestID() {
		return $this->requestId;
	}

	public function getApplicationId() {
		return $this->applicationId;
	}

	public function getEncoding() {
		return $this->encoding;
	}

	public function getVersion() {
		return $this->version;
	}

	/**
	 * @return mixed
	 */
	public function getDestinationAddresses() {
		return $this->destinationAddresses;
	}

}

?>