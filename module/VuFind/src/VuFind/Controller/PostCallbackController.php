<?php

namespace VuFind\Controller;

use VuFind\Exception\Auth as AuthException,
    VuFind\Exception\Mail as MailException,
    VuFind\Exception\ListPermission as ListPermissionException,
    VuFind\Exception\RecordMissing as RecordMissingException,
    Zend\Http\Client,
    Zend\Http\Request,
    Zend\Stdlib\Parameters;

/**
 * This class takes in the XML response from the payment gateway and tests to see if the message id is valid.
 * This prevents fraud by way of people directly submitting data to the callback url.
 */
class WpmXmlValidator {

    /**
     * @param $xml
     * @param $sharedSecret
     */
    public function __construct($xml, $sharedSecret) {
        $this->xml = simplexml_load_string($xml);
        $this->sharedSecret = $sharedSecret;
    }

    /**
     * @return bool
     */
    public function valid() {
        $expected = $this->expectedMessageId();
        $actual = $this->sentMessageId();
        return $expected == $actual;
    }

    /**
     * Returns the message id that we got back from the payment gateway (or wherever the POST came from).
     *
     * @return string
     */
    public function sentMessageId() {
        $attrs = $this->xml->attributes();
        return (string)$attrs['msgid'];
    }

    /**
     * Takes the details of the POST data and tells us what the message id should have been.
     *
     * @return string
     */
    public function expectedMessageId() {
        $clientId = trim($this->xml->clientid);
        $transactionReference = trim($this->xml->transactionreference);
        $payments = $this->xml->payments->xpath('//amounttopay');
        $totalAmountToPay = sprintf('%0.2f', $payments[0]);
        $sharedSecret = $this->sharedSecret;
        $concatenatedBits = $clientId . $transactionReference . $totalAmountToPay . $sharedSecret;
        return md5($concatenatedBits);
    }

    /**
     * @return bool
     */
    public function isPaid() {
        $payments = $this->xml->payments->xpath('//payment');
        $payid = $payments[0]['paid'];
        return $payid == 1;
    }

   /**
     * @return string
     */
    public function transId() {
        $transId = $this->xml->transaction->xpath('//transid');
        return $transId[0];
    }

    /**
     * Returns an array of billIds that are no paid
     *
     * @return array
     */
    public function billIds() {
        return explode(' ', trim($this->xml->transactionreference));
    }

    /**
     * @return string
     */
    public function paymentDate() {
        $payments = $this->xml->payments->xpath('//payment');
        return $payments[0]->dateofpayment;    
    }

    /**
    * return amount paid
    */
    public function amtPaid() {
	$totalPaid = $this->xml->transaction->xpath('//totalpaid');
        return $totalPaid[0];
    }

    /**
    *return barcode
    **/
    public function barcode() {
	$barcode = $this->xml->barcode;
	return $barcode[0];
    } 
    
    /**
    *return failure_reason
    **/
    public function failureReason() {
	$failureReason = $this->xml->transaction->xpath('//failurereason');
        return $failureReason[0];
    } 
}

class PostCallbackController extends AbstractBase {
	public function paymentcallbackAction() {
	$catalog = $this->getILS();
        $db=$catalog->getConnection();
        $post_xml = file_get_contents("php://input");
	$validator = new WpmXmlValidator($post_xml, 'h46dhs!d6');
        if ($validator->valid()) {
            if ($validator->isPaid()) {
		$transId=$validator->transId();
		$dop=$validator->paymentDate();
		$totalAmt=$validator->amtPaid();
		$billIds=$validator->billIds();
		$bills=implode(',',$billIds);
		$patron=$validator->barcode();
		$msgid=$validator->sentMessageId();
		$circService=$catalog->getCirculation();
		$uri = $circService.'?service=finePayment&paymentType=credit card&patronBarcode='.$patron.'&operatorId=VUFIND&transcationReference='.$transId.'&fineType=OVR_DUE&billIds='.$bills.'&amountPaid='.$totalAmt.'&responseFormatType=JSON';
             	$responseString=simplexml_load_file($uri);
		$pos = strpos($responseString, "successfully");
  		if ($pos == false) {
			$sql= "insert into OLE_VUFIND_PTRN_PAYMENT_LOG(PTRN_BARCODE,PAY_SYS_TRANS_ID,FEE_TYPE,PYMT_TYP,BILL_IDS,AMT_PAID,ISPAID,PAY_SYS_MESSAGE,PAY_DT_TM,UPTD_OLE_STAS,REC_DT_TM) values('".$patron."','".$transId."',' ','credit card','".$bills."',".$totalAmt.",'YES','".$msgid."','".$dop."','NO',now())";
                	try {
                    		$sqlStmt = $db->prepare($sql);
                    		$sqlStmt->execute();
                	}
                	catch (PDOException $e) {
                    		throw new ILSException($e->getMessage());
                	}
			$this->send_failure_response($msgid);
			return $this->getResponse();			
	    	}else{
			$sql= "insert into OLE_VUFIND_PTRN_PAYMENT_LOG(PTRN_BARCODE,PAY_SYS_TRANS_ID,FEE_TYPE,PYMT_TYP,BILL_IDS,AMT_PAID,ISPAID,PAY_SYS_MESSAGE,PAY_DT_TM,UPTD_OLE_STAS,REC_DT_TM) values('".$patron."','".$transId."',' ','credit card','".$bills."',".$totalAmt.",'YES','".$msgid."','".$dop."','YES',now())";

                	try {
                                $sqlStmt = $db->prepare($sql);
                                $sqlStmt->execute();
                        }
                        catch (PDOException $e) {
                                throw new ILSException($e->getMessage());
                        }
			$this->send_success_response($msgid);
			return $this->getResponse();
		}
             } 
    	  }
    }	
     /**
     * @param $validator
     */
    protected function send_success_response($msgid) {

         $success_return ='<wpmmessagevalidation msgid="'.$msgid.'">
	                	<validation>1</validation>
        	        	<validationmessage><![CDATA[Success]]></validationmessage>
               		   </wpmmessagevalidation>';

              //echo response on page so WPM pick it up.

	      echo $success_return;
    }

    /**
     * @param $validator
     */
    protected function send_failure_response($msgid) {
	$success_return ='<wpmmessagevalidation msgid="'.$msgid.'">
                                <validation>0</validation>
                                <validationmessage><![CDATA[Message can not be validated]]></validationmessage>
                           </wpmmessagevalidation>';

              //echo response on page so WPM pick it up.

              echo $success_return;
       
    }
}
?>

