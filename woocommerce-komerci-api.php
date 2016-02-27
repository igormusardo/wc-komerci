<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main class WC_Komerci_API.
 */
class WC_Komerci_API
{	
	/**
	 * Get the webservice url.
	 *
	 * @return string.
	 */

	public function get_webservice( $server )
	{
		if('yes' == $server) :
			return 'https://ecommerce.redecard.com.br/pos_virtual/wskomerci/cap_teste.asmx?WSDL';
		else :
			return 'https://ecommerce.redecard.com.br/pos_virtual/wskomerci/cap.asmx?WSDL';
		endif;
	}

	/**
	 * Transform the installments in param.
	 *
	 * @return string
	 */
	public function get_installments( $value )
	{
		switch( $value )
		{
			case 1:
				$installments = '00';
				break;
			case 2:
				$installments = '02';
				break;
			case 3:
				$installments = '03';
				break;
			case 4:
				$installments = '04';
				break;
			case 5:
				$installments = '05';
				break;
			case 6:
				$installments = '06';
				break;
			case 7:
				$installments = '07';
				break;
			case 8:
				$installments = '08';
				break;
			case 9:
				$installments = '09';
				break;
			case 10:
				$installments = '10';
				break;
			case 11:
				$installments = '11';
				break;
			case 12:
				$installments = '12';
				break;
		}

		return $installments;
	}

	public function get_authorized_komerci( $server, $args )
	{
		/**
		 * It connects via SOAP in the Komerci server. The connection is in the variable $komerci.
		 */
		$komerci = new SoapClient( $this->get_webservice( $server ), array(
	                                'trace'                 => 1,
	                                'exceptions'            => 1,
	                                'style'                 => SOAP_DOCUMENT,
	                                'use'                   => SOAP_LITERAL,
	                                'soap_version'          => SOAP_1_1,
	                                'encoding'              => 'UTF-8'
	                        	));

		/*
		* Do request.
		*/
		if('yes' == $server) :
			$GetAuthorized = $komerci->GetAuthorizedTst( $args );
			$GetAuthorizedResponse = $GetAuthorized->GetAuthorizedTstResult->any;
		else :
			$GetAuthorized = $komerci->GetAuthorized( $args );
			$GetAuthorizedResponse = $GetAuthorized->GetAuthorizedResult->any;
		endif;

		return $GetAuthorizedResponse;
	}

	/**
	* Do a SOAP request into Komerci server.
	*
	* @return bool
	*/
	public function do_request_soap_simple( $params, $test )
	{
		/**
		 * Get webservice and installments.
		 */
		$webservice = $this->get_webservice( $test );
		$installments = $this->get_installments( $params['installments'] );

		/**
		 * Prepares the array to send.
		 */		
		$args = array(
			'Total'     		=> number_format($params['amount'], 2, '.', ''),
			'Transacao'  		=> '04',
			'Parcelas'   		=> $installments,
			'Filiacao' 			=> $params['filiation'],
			'NumPedido'  		=> $params['orderid'],
			'Nrcartao'   		=> $params['ccnumber'],
			'CVC2'      		=> $params['cvv'],
			'Mes' 		 		=> $params['ccexp_month'],
			'Ano'     	 		=> $params['ccexp_year'],
			'Portador'			=> $params['card-holder-name'],
			'IATA'				=> '',
			'Distribuidor'   	=> '',
			'Concentrador'   	=> '',
			'TaxaEmbarque'   	=> '',
			'Entrada'   		=> '',
			'Pax1'   			=> '',
			'Pax2'   			=> '',
			'Pax3'   			=> '',
			'Pax4'   			=> '',
			'Numdoc1'   		=> '',
			'Numdoc2'  			=> '',
			'Numdoc3'   		=> '',
			'Numdoc4'  			=> '',
			'ConfTxn'   		=> 'S',
			'Add_Data'   		=> ''
		);
		      
        /**
         * Do request.
         *
         * @param array 	$args
         * @return array 	$result
         */        
       $request 	= $this->get_authorized_komerci( $test, $args);
       $result = $this->get_authorized_result_array( $request );

       // To test.
       // print_r($args);
       // print_r($result);

       return $result;
	}

	/**
	 * Transform the XML in string.
	 *
	 * @param value, string
	 *
	 */
	public function get_value_xml( $value, $string )
	{
		if ( preg_match("!<$value>(.+)</$value>!i", $string, $data ) ) 
		{ 
			return $data['1'];
		}
	}

	/**
	 * Insert the result of GetAuthorized into array.
	 *
	 * @param $value = XML.
	 * @return array
	 */
	public function get_authorized_result_array( $value )
	{
		return array(
			'CODRET'		=> $this->get_value_xml('CODRET', $value ),
			'MSGRET'		=> $this->get_value_xml('MSGRET', $value ),
			'NUMPEDIDO'		=> $this->get_value_xml('NUMPEDIDO', $value ),
			'DATA'			=> $this->get_value_xml('DATA', $value ),
			'NUMAUTOR'		=> $this->get_value_xml('NUMAUTOR', $value ),
			'NUMCV'			=> $this->get_value_xml('NUMCV', $value ),
			'NUMAUTENT'		=> $this->get_value_xml('NUMAUTENT', $value ),
			'NUMSQN'		=> $this->get_value_xml('NUMSQN', $value ),
			'ORIGEM_BIN'	=> $this->get_value_xml('ORIGEM_BIN', $value ),
			'CONFCODRET'	=> $this->get_value_xml('CONFCODRET', $value ),
			'CONFMSGRET'	=> $this->get_value_xml('CONFMSGRET', $value )
		);
	}

	/**
	 * Do a request with the method Pre Authorization.
	 *
	 * @param $params = All parameters
	 * @param $test = The server test
	 */
	public function do_request_soap_pre_auth( $params, $test )
	{
		/**
		 * Get webservice and installments.
		 */
		$webservice = $this->get_webservice( $test );
		$installments = $this->get_installments( $params['installments'] );

		/* All args */
		$args = array(
			'Total'     		=> number_format($params['amount'], 2, '.', ''),
			'Transacao'  		=> '73',
			'Parcelas'   		=> '00',
			'Filiacao' 			=> $params['filiation'],
			'NumPedido'  		=> $params['orderid'],
			'Nrcartao'   		=> $params['ccnumber'],
			'CVC2'      		=> $params['cvv'],
			'Mes' 		 		=> $params['ccexp_month'],
			'Ano'     	 		=> $params['ccexp_year'],
			'Portador'			=> $params['card-holder-name'],
			'IATA'				=> '',
			'Distribuidor'   	=> '',
			'Concentrador'   	=> '',
			'TaxaEmbarque'   	=> '',
			'Entrada'   		=> '',
			'Pax1'   			=> '',
			'Pax2'   			=> '',
			'Pax3'   			=> '',
			'Pax4'   			=> '',
			'Numdoc1'   		=> '',
			'Numdoc2'  			=> '',
			'Numdoc3'   		=> '',
			'Numdoc4'  			=> '',
			'ConfTxn'   		=> 'S',
			'Add_Data'   		=> ''
		);

		/**
         * Do request.
         *
         * @param array 	$args
         * @return array 	$result
         */        
       $request = $this->get_authorized_komerci( $test, $args);
       $result = $this->get_authorized_result_array( $request );

       // To test.
       // print_r($args);
       // print_r($result);

       return $result;

	}

}

?>
