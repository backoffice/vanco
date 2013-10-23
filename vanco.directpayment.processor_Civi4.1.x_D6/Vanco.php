<?php
/*
@author Saad Bashir <imsaady@gmail.com>
class for Implementation of Payment Processor with Vanco
*/
require_once 'vanco_settings.inc';
require_once 'CRM/Core/Payment.php';
require_once 'packages/Vanco/VancoWebService.php';

class vanco_directpayment_processor extends CRM_Core_Payment {
    const
        CHARSET = 'iso-8859-1';

    const AUTH_APPROVED = 1;
    const AUTH_DECLINED = 2;
    const AUTH_ERROR = 3;

    static protected $_mode = null;

    static protected $_params = array();

    //Modified for Civicrm Ver3.3.5	
    static private $_singleton = null;

    function __construct( $mode, &$paymentProcessor ) 
	{
        $this->_mode             = $mode;
        $this->_paymentProcessor = $paymentProcessor;
        $this->_processorName    = ts('Vanco');

        $config =& CRM_Core_Config::singleton();
        $this->_setParam( 'paymentType', 'Vanco' );
        
        $this->_setParam( 'timestamp', time( ) );
        srand( time( ) );
        $this->_setParam( 'sequence', rand( 1, 1000 ) );

		
    }
	private function debugMsg($params)    {
		$msg='';
		foreach($params as $name=>$value)
		{
			$msg .= "$name:$value<BR>";
		}
		return self::error( 'DEBUG', $msg);
	}

    //Modified for Civicrm Ver3.3.5	
    static function singleton( $mode, $paymentProcessor ) {
        $processorName = $paymentProcessor['name'];
        if (self::$_singleton[$processorName] === null ) {
            self::$_singleton[$processorName] = new vanco_directpayment_processor( $mode, $paymentProcessor );
        }
        return self::$_singleton[$processorName];
    }

    function doDirectPayment( &$params)
    {
        //CRM_Core_Error::debug( '$params', $params );
        foreach ( $params as $field => $value ) {
            $this->_setParam( $field, $value );
           
        }
		$this->_setParam( 'card_expiry_month', $params['credit_card_exp_date']['M'] );
		$this->_setParam( 'card_expiry_year', $params['credit_card_exp_date']['Y'] );
        
        $paymentProcessorDetails = $this->getVar('_paymentProcessor');
        $paymentApiURL = $paymentProcessorDetails[ 'url_api' ];


        $paymentProcessorDetails = $this->getVar('_paymentProcessor');
        $paymentApiURL = $paymentProcessorDetails[ 'url_api' ];
        
        $vanco_obj = new VancoPaymentService( $paymentApiURL, 443, 15);

        //----------------------------LOGIN
		$credentials['username'] = $this->_paymentProcessor['user_name'];
      
		$credentials['password'] = $this->_paymentProcessor['password'];
        $session = $vanco_obj->Login($credentials);

		$vancoFields = $this->_getVancoPaymentFields( $vanco_obj, $session['sessionID'] );
        $vancoFields['CustomerID'] = $params['contactID'];
        
    
		if($session['status']== 'FAILED')
		{
			return self::error( $credentials['username'], $credentials['password'] );
			return self::error( $session['error'], $session['desc'] );
		}
		//--------------------MAKE TRANSACTION
		$response = $vanco_obj->EFTAddCompleteTransaction($session['sessionID'], $vancoFields);

		if($response['status']== 'FAILED')
		{
			return self::error( $response['error'], $response['desc'] );
		}

		//--------------------END - TRANSACTION
		$vanco_obj->Logout($params);

        $result['trxn_id'] = $response['TransactionRef'];
		$result['fee_amount'] = $response['TransactionFee'];
		$result['gross_amount'] = $this->_getParam('amount') + $response['TransactionFee'];
        
        //Modified to add TransactionRef to civicrm_contribution table
        require_once 'api/v2/Contribution.php';
        if( $params['is_recur'] ){
            $updateContri = array( 'id'         => $params['contributionID'],
                                   'contact_id' => $params['contactID'],
                                   'total_amount' => $params['amount'],
                                   'currency' => $params['currencyID'],
                                   'contribution_type_id' =>  $params['contributionTypeID'],
                                   'trxn_id'    => $response['TransactionRef']
                                   );
            $status = civicrm_contribution_add( $updateContri );
            
            //Modified to add Transactionref to civicrm_contribution_recur
            
            $recurParams = array( 'id'      => $params['contributionRecurID'],
                                  'trxn_id' => $response['TransactionRef']
                                  );
            $ids = array( 'contribution' => $params['contributionRecurID'] );
            
            require_once 'CRM/Contribute/BAO/ContributionRecur.php';
            $recurring =& CRM_Contribute_BAO_ContributionRecur::add( $recurParams, $ids );
        }
		return $result;
    }
	
	function &error( $errorCode = null, $errorMessage = null ) 
	{
        $e =& CRM_Core_Error::singleton( );
        if ( $errorCode ) {
            $e->push( $errorCode, 0, null, $errorMessage );
        } else {
            $e->push( 9001, 0, null, 'Unknown System Error.' );
        }
        return $e;
    }
	function checkConfig( ) 
	{
        $error = array();
        if ( empty( $this->_paymentProcessor['user_name'] ) ) {
            $error[] = ts( 'APILogin is not set for this payment processor' );
        }
        
        if ( empty( $this->_paymentProcessor['password'] ) ) {
            $error[] = ts( 'Key is not set for this payment processor' );
        }

        if ( ! empty( $error ) ) {
            return implode( '<p>', $error );
        } else {
            return null;
        }
    }
	
	function _getVancoPaymentFields( $vancoObj = null, $sessionVal = null ) 
	{    
		$params['CustomerName']		= $this->_getParam( 'billing_last_name' ).", ".$this->_getParam('billing_first_name');
		$params['CustomerAddress1']	= $this->_getParam( 'street_address' );
		$params['CustomerCity']		= $this->_getParam( 'city' );
		$params['CustomerState']	= $this->_getParam( 'state_province' );
		$params['CustomerZip']		= $this->_getParam( 'postal_code' );

		$payment_method = $this->_getParam( 'payment_method' );
		include_once 'CRM/Contribute/PseudoConstant.php';
		$account_type='';
		$paymentMethods = CRM_Contribute_PseudoConstant::paymentInstrument();
		if($paymentMethods[$payment_method] == 'ACH')
		{
            $params['TransactionTypeCode'] = 'WEB';
			if($this->_getParam( 'account_type' ) == 'checking')
				$account_type = 'C';
			else if($this->_getParam( 'account_type' ) == 'savings')
				$account_type = 'S';
		}else if($paymentMethods[$payment_method] == 'Credit Card')
		{
			$account_type = 'CC';
		}
		
		$params['AccountType']	= $account_type;
		if($account_type== 'CC')
		{
			$params['AccountNumber']	= $this->_getParam('credit_card_number');
			$params['CardCVV2']			= $this->_getParam('cvv2');
			$params['CardExpMonth']		= str_pad( $this->_getParam( 'card_expiry_month' ), 2, '0', STR_PAD_LEFT );
			$params['CardExpYear']		= $this->_getParam('card_expiry_year');	
			$params['CardBillingName']	= $this->_getParam('billing_first_name')." ".$this->_getParam('billing_middle_name')." ".$this->_getParam('billing_last_name');
			
			$params['SameCCBillingAddrAsCust']		= "YES";
			/*
			$params['CardBillingAddr1']			= $this->_getParam('billing_street_address-5');
			$params['CardBillingAddr2']			= $this->_getParam('');
			$params['CardBillingCity']			= $this->_getParam('billing_city-5');
			$params['CardBillingState']			= $this->_getParam('billing_state_province-5');
			$params['CardBillingZip']			= $this->_getParam('billing_postal_code-5');
			$params['CardBillingCountryCode']	= $this->_getParam('billing_country-5');
			*/
			
		}else{
			$params['AccountNumber'] 	= $this->_getParam('account_number');
			$params['RoutingNumber']	= $this->_getParam('routing_number');
		}

        require_once "CRM/Utils/Rule.php";
		$params['Amount']               = CRM_Utils_Rule::cleanMoney( number_format($this->_getParam('amount'),2 ) );
        $params['StartDate']			= '0000-00-00';	
        $params['FrequencyCode']		= 'O';
        
        if( $this->_getParam('is_recur') ){
            $frequencyOptions = array( 'month'   => 'M',
                                       'week'    => 'W',
                                       'biweek'  => 'BW',
                                       'quarter' => 'Q',
                                       'year'    => 'A');
            $params['FrequencyCode'] = $frequencyOptions[ $this->_getParam('frequency_unit') ];
            if ( $this->_getParam('frequency_unit') == 'biweek' ) {
                $dateFrequency = 2;
                $dateUnit = 'week';
            } elseif ( $this->_getParam('frequency_unit') == 'quarter' ) {
                $dateFrequency = 3;
                $dateUnit = 'month';
            } else {
                $dateFrequency = 1;
                $dateUnit = $this->_getParam('frequency_unit');
            }
		    //To get Vanco holidays
			
            $currentDay = date("l");
            if ( $sessionVal && $vancoObj ) {
                $vancoFields_holiday['ClientID'] = ClientID;
                $responseHolidays = $vancoObj->EFTGetFederalHoliday( $sessionVal, $vancoFields_holiday );
                $vancoHolidays    = $responseHolidays->Holidays;
                
                foreach( $vancoHolidays->Holiday as $key => $value ) {
                    $date = (array) $value;
                    $holidayDates[] = $date['HolidayDate'];
                }
                
            }
            $dateParam = $this->calculateDates( $paymentMethods[$payment_method], $currentDay, $holidayDates);
            $params['StartDate'] = $dateParam['startDate'];
            
            $params['EndDate']   = date("Y-m-d", strtotime( $params['StartDate'] .'+'. (($this->_getParam('installments') * $dateFrequency)-1). " " . $dateUnit ));
        }
        return $params;
       
    }
    static function calculateDates( $paymentMethod = null, $currentDay, $vancoHolidays ) {
        if ( $paymentMethod == 'ACH' ){ 
            $timezone = new DateTimeZone( "CST" );
            $date = new DateTime();
            $date->setTimezone( $timezone );
            $hour = $date->format( 'H' );
            $mins = $date->format( 'i' );
            
            $dateParam['startDate'] = date("Y-m-d", strtotime( "1 day" ) );
            
            if( $hour >= 15 && $mins >= 01 ){
                $dateParam['startDate'] = date("Y-m-d", strtotime( $dateParam['startDate']."+ 1 day" ));
            }
            
            $day = date("l", strtotime($dateParam['startDate'] ) );
            if( $day  == 'Saturday' || $day == 'Sunday' ) {
                $dateParam['startDate'] = date('Y-m-d', strtotime( $dateParam['startDate']. 'next monday'));
            }

            //if holiday present then increment day by 1
            foreach( $vancoHolidays as $holidayValue ) {
                if ( strtotime( $dateParam['startDate'] ) == strtotime( $holidayValue ) ) {
                    $dateParam['startDate'] = date("Y-m-d", strtotime( $dateParam['startDate']."+ 1 day" ));
                }
            }
        } else {
            $dateParam['startDate'] = date("Y-m-d");
        }
        return $dateParam;
    }
    
    function _getParam( $field ) 
    {
        return CRM_Utils_Array::value( $field, $this->_params, '' );
    }
    
    function _setParam( $field, $value ) 
    {
        if ( ! is_scalar($value) ) {
           return false;
        } else {
            $this->_params[$field] = $value;
        }
    }
    
    //example:-  $details =
    //self::getRecurPaymentDetails( array( 'id',
    //'contact_id', 'amount' ), array( 'contribution_status_id' => 2,
    //'invoice_id' => '28719fc6eca8fc422ec58302e441768b' ) );

    function getRecurPaymentDetails( $recurSelectParam, $recurWhereParam ) { 
        $selectParams = implode( ',', $recurSelectParam );
        $whereParams = "";
        foreach( $recurWhereParam as $whereKey => $whereValue ) {
            $whereParamsArray[] = $whereKey . " = '" . $whereValue ."'";
        }
        $whereParams = implode( ' AND ', $whereParamsArray );
        $sql = "SELECT " . $selectParams ." FROM civicrm_contribution_recur where " . $whereParams .";" ;
        $recurDetails =& CRM_Core_DAO::executeQuery( $sql );
        $index = 0;
        while( $recurDetails->fetch() ){
            foreach( $recurSelectParam as $selectKey ) {
                $details[$index][$selectKey] = $recurDetails->$selectKey;
            }
            $index++;
        }
        return $details;
    }
    
//END - CRM_Core_Payment_Vanco CLASS
}         

