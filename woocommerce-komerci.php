<?php

/*
* Plugin Name: Komerci WooCommerce Payment Gateway
* Description: This plugin adds a payment option in WooCommerce for customers to pay with their Credit Cards via RedeCard.
* Version: 0.0.1
* Author: Luciano Junior
* Author URI: https://lucianojunior.com.br/
* License: GPLv2
*/

if ( ! defined( 'ABSPATH' ) ) exit;

function komerci_init()
{
	
	function add_komerci_gateway_class( $methods ) 
	{
		$methods[] = 'WC_Komerci_Gateway'; 
		return $methods;
	}
	
	add_filter( 'woocommerce_payment_gateways', 'add_komerci_gateway_class' );
	
	if( class_exists( 'WC_Payment_Gateway' ) )
	{

		class WC_Komerci_Gateway extends WC_Payment_Gateway 
		{
	        	
			public function __construct()
			{

				$this->id               			= 'komercigateway';
				$this->icon             			= plugins_url( 'images/komerci.png' , __FILE__ )  ;
				$this->has_fields       			= true;
				$this->method_title    				= 'Komerci';	
				
				$this->init_form_fields();
				$this->init_settings();

				$this->title                		= $this->get_option( 'title' );
				$this->description       		 	= $this->get_option( 'description' );
				$this->filiacao         		    = $this->get_option( 'filiacao' );
				$this->method               		= $this->get_option( 'method' );

				$this->user               			= $this->get_option( 'user' );
				$this->password               		= $this->get_option( 'password' );

				$this->supports 					= array('refunds');

				$this->method 		       			= $this->get_option( 'method' );
				$this->test               			= $this->get_option( 'test' );
						
				$this->komerci_cardtypes       		= $this->get_option( 'komerci_cardtypes'); 
				$this->parcelas 		       		= $this->get_option( 'parcelas'); 

				$this->siteurl 						= $_SERVER['SERVER_NAME'];
				$this->serverip 					= getHostByName(php_uname('n'));
                
				if (is_admin()) 
				{
					add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				}		

				add_action( 'woocommerce_order_status_refunded', 'komerci_refunds');
			}

			/**
			 * Get the payment fields with transparent checkout
			 */
			public function payment_fields() {
				
				wp_enqueue_script( 'wc-credit-card-form' );

				if ( $description = $this->get_description() ) {
					echo wpautop( wptexturize( $description ) );
				}

				$cart_total = $this->get_order_total();

				require dirname(__FILE__) . '/' . 'templates/wc-fields-komerci-card-name.php';
			}

			/*
			 * Options into painel.
			 */
	        public function admin_options()
			{
			?>			
				<h3><?php _e( 'Komerci Payment Gateway for WooCommerce', 'woocommerce' ); ?></h3>
				<p><?php  _e( 'Komerci is a payment gateway service provider allowing merchants to accept credit card.', 'woocommerce' ); ?></p>
	
				<table class="form-table">
				
				<div id="screen-meta" style="display:block;">
					<div id="screen-options-wrap">
						<h2><?php echo 'The IP of the website ' . $this->siteurl . ' is ' . $this->serverip . '.'; ?></h2>
						<p><?php _e('You should request the release of this IP to the RedeCard.', 'komerci'); ?></p>
					</div>
				</div>

				<?php $this->generate_settings_html(); ?>
				</table>			
			<?php
			}
	
			/**
			 * Initialise Gateway Settings Form Fields.
			 */
			public function init_form_fields()
			{				
				  $this->form_fields = array(
					'enabled' => array(
					  'title' => __('Enable/Disable', 'komerci'),
					  'type' => 'checkbox',
					  'label' => __('Enable Komerci Payment Module.', 'komerci'),
					  'default' => 'no'),

					'title' => array(
					  'title' => __('Title:', 'komerci'),
					  'type'=> 'text',
					  'description' => __('This controls the title which the user sees during checkout.', 'komerci'),
					  'default' => __('Komerci', 'komerci')),

					'user' => array(
					  'title' => __('Komerci User:', 'komerci'),
					  'type'=> 'text',
					  'description' => __('The user RedeCard.', 'komerci')),
					 
					'password' => array(
					  'title' => __('Komerci Password:', 'komerci'),
					  'type'=> 'password',
					  'description' => __('The user password.', 'komerci')),

					'description' => array(
					  'title' => __('Description:', 'komerci'),
					  'type' => 'textarea',
					  'description' => __('This controls the description which the user sees during checkout.', 'komerci'),
					  'default' => __('It is a practical and safe solution for accepting payments with credit cards MasterCard, Visa and Diners Club International on the Internet.', 'komerci')),

					'filiacao' => array(
					  'title' => __('Filiation:', 'komerci'),
					  'type' => 'text',
					  'description' => __('This id is available by RedeCard.')),

					'test' => array(
					  'title' => __('Use Server Test:', 'komerci'),
					  'type' => 'checkbox',
					  'label' => 'Enable/Disable',
					  'default' => 'no',
					  'description' =>  __('Only use the WSDL test server.', 'komerci'),
					),

					'method' => array(
						'title'       => __( 'Método de Funcionamento:', 'komerci' ),
						'type'        => 'select',
						'description' => __( 'Escolha como será o funcionamento da RedeCard perante a loja.', 'komerci' ),
						'desc_tip'    => true,
						'default'     => 'direct',
						'class'       => 'wc-enhanced-select',
						'options'     => array(
							'auth_simple'	=> 'Autorização em um passo',
							'pre_auth'		=> 'Autorização em dois passos (Pré-Autorização)'
						),

						'default' => 'auth_simple'
					),

					'parcelas' => array(
						'title'       => __( 'Parcelas:', 'komerci' ),
						'type'        => 'select',
						'description' => __( 'Escolha até quanto você deixará o cliente parcelar.', 'komerci' ),
						'desc_tip'    => true,
						'default'     => 'direct',
						'class'       => 'wc-enhanced-select',
						'options'     => array(
							'1'		=> 'Apenas pagamentos à vista.',
							'2'		=> 'Até 2x (sem juros).',
							'3'		=> 'Até 3x (sem juros).',
							'4'		=> 'Até 4x (sem juros).',
							'5'		=> 'Até 5x (sem juros).',
							'6'		=> 'Até 6x (sem juros).',
							'7'		=> 'Até 7x (com juros).',
							'8'		=> 'Até 8x (com juros).',
							'9'		=> 'Até 9x (com juros).',
							'10'	=> 'Até 10x (com juros).',
							'11'	=> 'Até 11x (com juros).',
							'12'	=> 'Até 12x (com juros).'
						),
					),
			
					'komerci_cardtypes' => array(
						'title'    => __( 'Accepted Cards', 'woocommerce' ),
						'type'     => 'multiselect',
						'class'    => 'chosen_select',
						'css'      => 'width: 350px;',
						'desc_tip' => __( 'Select the card types to accept.', 'woocommerce' ),
						
						'options'  => array(
							'mastercard'       	=> 'MasterCard',
							'visa'             	=> 'Visa',
							'dinersclub'       	=> 'Dinners Club'
						),
						
						'default' => array( 'mastercard', 'visa', 'dinersclub' ),
					)
		
				  );
			}

        	/**
        	 * Includes.
        	 */
	
			public function includes()
			{
				include_once 'woocommerce-komerci-api.php';
				include_once 'woocommerce-komerci-errors.php';
			}

			/**
			 * Check cards that can be accepted to display the icons on checkout.
			 */
					
			public function get_icon()
			{				
				$icon = '';
				
				if( is_array( $this->komerci_cardtypes ) )
				{
					foreach ($this->komerci_cardtypes as $card_type ) {
						if ( $url = $this->get_payment_method_image_url( $card_type ) ) {
							$icon .= '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( strtolower( $card_type ) ) . '" />';
						}
					}
				}
				else
				{
					$icon .= '<img src="' . esc_url( plugins_url( 'images/komerci.png' , __FILE__ ) ).'" alt="Komerci Gateway" />';	  
				}

				return apply_filters( 'woocommerce_komerci_icon', $icon, $this->id );
			}

			public function get_payment_method_image_url( $type )
			{				
				$image_type = strtolower( $type );
				return  WC_HTTPS::force_https_url( plugins_url( 'images/' . $image_type . '.png' , __FILE__ ) ); 
			}			
			
			function get_card_type( $number )
			{
				
				$number = preg_replace('/[^\d]/','',$number);
				
				if (preg_match('/^3[47][0-9]{13}$/',$number))
				{
					return 'amex';
				}
				elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',$number))
				{
					return 'dinersclub';
				}
				elseif (preg_match('/^6(?:011|5[0-9][0-9])[0-9]{12}$/',$number))
				{
					return 'discover';
				}
				elseif (preg_match('/^(?:2131|1800|35\d{3})\d{11}$/',$number))
				{
					return 'jcb';
				}
				elseif (preg_match('/^5[1-5][0-9]{14}$/',$number))
				{
					return 'mastercard';
				}
				elseif (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/',$number))
				{
					return 'visa';
				}
				else
				{
					return 'inserido.';
				}
			}

			/**
			 * Get the client IP.
			 *
			 * @return string $ipaddress
			 */

			function get_client_ip() 
			{
				$ipaddress = '';
				if (getenv('HTTP_CLIENT_IP'))
					$ipaddress = getenv('HTTP_CLIENT_IP');
				else if(getenv('HTTP_X_FORWARDED_FOR'))
					$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
				else if(getenv('HTTP_X_FORWARDED'))
					$ipaddress = getenv('HTTP_X_FORWARDED');
				else if(getenv('HTTP_FORWARDED_FOR'))
					$ipaddress = getenv('HTTP_FORWARDED_FOR');
				else if(getenv('HTTP_FORWARDED'))
					$ipaddress = getenv('HTTP_FORWARDED');
				else if(getenv('REMOTE_ADDR'))
					$ipaddress = getenv('REMOTE_ADDR');
				else
					$ipaddress = '0.0.0.0';
				return $ipaddress;
			}
			
			/**
			 * Stores all parameters (payment, users, delivery, etc.) in an array for processing the request.
			 *
			 * @param 	$wc_order
			 * @return 	array
			 */

			public function komerci_params( $wc_order )
			{
				$exp_date         = explode( "/", sanitize_text_field( $_POST['komercigateway-card-expiry'] ) );
				$exp_month        = str_replace( ' ', '', $exp_date[0]);
				$exp_year         = str_replace( ' ', '', $exp_date[1]);						

				$komerci_args = array(
					'filiation'  		=> $this->filiacao,
					'method'  			=> $this->method,
					'parcelas'			=> $this->parcelas,
					'installments'  	=> $_POST['komercigateway-installments'],

					'card-holder-name'	=> $_POST['komercigateway-card-name'],
					'ccnumber'  		=> sanitize_text_field( str_replace(" ", "", $_POST['komercigateway-card-number'] ) ),
					'ccexp'     		=> $exp_month . $exp_year,
					'ccexp_month'		=> $exp_month,
					'ccexp_year'		=> $exp_year,
					'amount'    		=> number_format( $wc_order-> order_total, 2, ".", ""),
					'cvv'       		=> sanitize_text_field( $_POST['komercigateway-card-cvc'] ),
					
					'ipaddress' 		=> $this->get_client_ip(),
					'orderid'   		=> $wc_order->get_order_number() ,
					'orderdescription' 	=> get_bloginfo('blogname').' Order #'.$wc_order->get_order_number() ,
					'tax'       		=> number_format($wc_order->get_total_tax(),2,".","") ,
					'shipping'  		=> number_format($wc_order->get_total_shipping(),2,".","") ,
					'ponumber'  		=> $wc_order->get_order_number(),
					
					'firstname'         => $wc_order->billing_first_name, 
					'lastname'          => $wc_order->billing_last_name,
					'company'           => $wc_order->billing_company,
					'address1'          => $wc_order->billing_address_1,
					'address2'          => $wc_order->billing_address_2,
					'city'              => $wc_order->billing_city,
					'state'             => $wc_order->billing_state,
					'zip'               => $wc_order->billing_postcode,
					'country'           => $wc_order->billing_country,
					'phone'             => $wc_order->billing_phone,
					'fax'               => $wc_order->billing_phone,
					'email'             => $wc_order->billing_email,
					'website'           => get_bloginfo('url'),
					
					'shipping_firstname'=> $wc_order->shipping_first_name,
					'shipping_lastname' => $wc_order->shipping_last_name,
					'shipping_company'  => $wc_order->shipping_company,
					'shipping_address1' => $wc_order->shipping_address_1,
					'shipping_address2' => $wc_order->shipping_address_2,
					'shipping_city'     => $wc_order->shipping_city,
					'shipping_state'    => $wc_order->shipping_state,
					'shipping_zip'      => $wc_order->shipping_postcode,
					'shipping_country'  => $wc_order->shipping_country,
					'shipping_email'    => $wc_order->shipping_email				
				);
					
				return $komerci_args;
			}

			/**
			 * Validates all user-filled fields to send only if they are all right.
			 *
			 * @return 
			 */

			public function validate_fields()
			{
				$errors = 0;

				if ( $_POST['komercigateway-card-name'] === '') {
					wc_add_notice('Por favor, insira o nome do portador do cartão.', 'error');
					$errors++;
				}

				if ( $_POST['komercigateway-card-number'] === '') {
					wc_add_notice('Por favor, insira o número do seu cartão de crédito.', 'error');
					$errors++;
				}

				if ( $_POST['komercigateway-card-number'] === '') {
					wc_add_notice('Please enter credit card number.', 'error');
					$errors++;
				}

				return $errors === 0;
			}

			/**
			 * Process all the payment.
			 *
			 * @param 	$order_id
			 * @return 	bool
			 */
	
			public function process_payment( $order_id )
			{
				global $woocommerce;
				$wc_order = new WC_Order($order_id);

				$wc_order->get_formatted_order_total();
			
				$cardtype = $this->get_card_type( sanitize_text_field(str_replace(" ", "", $_POST['komercigateway-card-number']) ) );

				/*
				 * Check if the card is accepted.
				 */
				if( !in_array( $cardtype, $this->komerci_cardtypes ) )
         		{
         			wc_add_notice('Komerci não aceita cartões do tipo '.$cardtype .'.',  $notice_type = 'error' );

         			return array (
						'result'   => 'error',
						'redirect' => WC()->cart->get_checkout_url(),
					);

					die;
         		}

         		/*
         		 * All the data needed to make the request.
         		 */
         		$this->includes();
          		$params 	= $this->komerci_params( $wc_order );
          		$test 		= $this->test;

           		/**
           		 * Call the API class.
           		 */
          		$api = new WC_Komerci_API();
          		
          		/**
          		 * Here it checks if is auth_simple or pre auth to send the data correctly.
          		 */
          		if('auth_simple' == $this->method)
          		{
        			/**
        			 * Do a request with the method auth_simple (only GetAuthorized).
        			 *
	         		 * @param $params 			= Get all card and user data.
	         		 * @param $webservice 		= Returns the link's web service.
	         		 * @param $methods 			= Returns the methods.
	         		 *
	         		 * @return array
        			 */
          			$request = $api->do_request_soap_simple( $params, $test );
					$validate = $this->validate_return( $request );

					/**
					 * If everything is right, he withdraws from the stock and place the order.
					 */

					if(isset( $validate ) ){

						if( $validate == true)
						{

							/* Say if is a order by test. */
							if('yes' == $test)
							{
								$msg = '<span style="background:#f00;color: #fff;font-size:10px;font-weight:bold;padding:3px;display:table;">ORDER REQUEST IN SERVER TEST</span>';
							}

							// print_r($params);

							/* Transform the card on syntax. */
							$digits = substr($params['ccnumber'], -4);
							$size = strlen($params['ccnumber']);

							for($i=0; $i<$size; $i++){
								$var = $var . 'x';
							}

							$card = $var . $digits;

							$wc_order->add_order_note( __(
								'<h4 style="margin:0;">Dados para a Komerci:</h4>
								<b>Total:</b> ' . $params['amount'] . '<br/>
								<b>Parcelas:</b> ' . $params['installments'] . '<br/>
								<b>Filiação:</b> ' . $params['filiation'] . '<br/>
								<b>Nome:</b> ' . $params['card-holder-name'] . '<br/>
								<b>Cartão:</b> ' . $card . '<br/>'
								)
							);
	
							$wc_order->add_order_note( __(
								'<h4 style="margin:0;">Retorno da Komerci:</h4>'
								. $msg .'
								<b>Código de Retorno:</b> ' . $request['CODRET'] .'<br/>
								<b>Mensagem:</b> ' . $request['MSGRET'] .'<br/>
								<b>Data:</b> ' . $request['DATA'] . '<br/>
								<b>Número do Pedido:</b> ' . $request['NUMPEDIDO'] .'<br/>
								<b>Número de Autorização:</b> '. $request['NUMAUTOR'] .'<br/>
								<b>Número de CV:</b> ' . $request['NUMCV'] . '<br/>
								<b>Número de Autenticação:</b> ' . $request['NUMAUTENT'] . '<br/>
								<b>Número Sequencial Único:</b> ' . $request['NUMSQN'] . '<br/>
								<b>Código do País Emissor:</b> ' . $request['ORIGEM_BIN'] . '<br/>
								<b>Confirmação Automática:</b> ' . $request['CONFCODRET'] . '<br/>
								<b>Descrição:</b> ' . $request['CONFMSGRET'] . '<br/>'
								) );

							$wc_order->payment_complete( $request['NUMPEDIDO'] );

							WC()->cart->empty_cart();

							return array (
								'result'   => 'success',
								'redirect' => $this->get_return_url( $wc_order ),
							);

						}
			
						return false;
					}
          		}
          		elseif('pre_auth' == $this->method)
        		{
        			/**
        			 * Do a request with the method pre_auth (GetAuthorized and ConfPreAuthorized).
        			 *
	         		 * @param $params 			= Get all card and user data.
	         		 * @param $webservice 		= Returns the link's web service.
	         		 * @param $methods 			= Returns the methods.
	         		 *
	         		 * @return array
        			 */
  					$request = $api->do_request_soap_pre_auth( $params, $test );
  					$validate = $this->validate_return( $request );

  					/**
					 * If everything is right, he withdraws from the stock and place the order.
					 */

					if(isset( $validate ) ){

						if( $validate == true)
						{

							/* Insert multiples post_metas to refunds. */
							add_post_meta ( $request['NUMPEDIDO'], '_komerci_codret', $request['CODRET'], true );
							add_post_meta ( $request['NUMPEDIDO'], '_komerci_data', $request['DATA'], true );
							add_post_meta ( $request['NUMPEDIDO'], '_komerci_numautor', $request['NUMAUTOR'], true );
							add_post_meta ( $request['NUMPEDIDO'], '_komerci_numcv', $request['NUMCV'], true );

							/* Say if is a order by test. */
							if('yes' == $test)
							{
								$msg = '<span style="background:#f00;color: #fff;font-size:10px;font-weight:bold;padding:3px;display:table;">ORDER REQUEST IN SERVER TEST</span>';
							}

							/* Do the checkout */
							$wc_order->add_order_note( __(
								'<h4 style="margin:0;">Retorno da Komerci:</h4>'
								. $msg .'
								<b>Código de Retorno:</b> ' . $request['CODRET'] .'<br/>
								<b>Mensagem:</b> ' . $request['MSGRET'] .'<br/>
								<b>Data:</b> ' . $request['DATA'] . '<br/>
								<b>Número do Pedido:</b> ' . $request['NUMPEDIDO'] .'<br/>
								<b>Número de Autorização:</b> '. $request['NUMAUTOR'] .'<br/>
								<b>Número de CV:</b> ' . $request['NUMCV'] . '<br/>
								<b>Número de Autenticação:</b> ' . $request['NUMAUTENT'] . '<br/>
								<b>Número Sequencial Único:</b> ' . $request['NUMSQN'] . '<br/>
								<b>Código do País Emissor:</b> ' . $request['ORIGEM_BIN'] . '<br/>
								<b>Confirmação Automática:</b> ' . $request['CONFCODRET'] . '<br/>
								<b>Descrição:</b> ' . $request['CONFMSGRET'] . '<br/>'
								) );

							$wc_order->payment_complete( $request['NUMPEDIDO'] );

							WC()->cart->empty_cart();

							return array (
								'result'   => 'success',
								'redirect' => $this->get_return_url( $wc_order ),
							);

						}
			
						return false;
					}
        		}    
			}

			/**
			 * This functions get the result and validate if all be right.
			 *
			 * @param 
			 */
			public function validate_return( $request )
			{

				if( $request['CODRET'] == '0' && $request['NUMCV'] != '' )
				{
					wc_add_notice('Até aqui ok.', $notice_type = 'success' );
					return true;
				}
				else
				{
					$errors = new WC_Komerci_Errors();
					wc_add_notice('Problemas no pagamento: ' . $errors->get_error_message( $request['CODRET'] ) . '', $notice_type = 'error' );
					return false;
				}
			}

			/**
			 * Do the refunds on the method Pre Autorization.
			 *
			 * @param $order_id, $amount, $reason
			 * @return bool
			 */
			public function process_refund( $order_id, $amount = null, $reason = '' )
			{

				$wc_order = new WC_Order( $order_id );

				try {
		
					$Total = str_replace(',', '.', $_POST['refund_amount']);
					$Data = get_post_meta( $order_id, '_komerci_data' );
					$NumAutor = get_post_meta( $order_id, '_komerci_numautor' );
					$NumCV = get_post_meta( $order_id, '_komerci_numcv' );
				
					$args = array(
						'Filiacao'			=> $this->filiacao,
						'Distribuidor'		=> '',
						'Total'				=> $Total,
						'Parcelas'			=> '00',
						'Data'				=> $Data['0'],
						'NumAutor'			=> $NumAutor['0'],
						'NumCV' 			=> $NumCV['0'],
						'Concentrador'		=> '',
						'Usr' 				=> $this->user,
						'Pwd'				=> $this->password
					);


					// TO DO: VERIFICAR SE ESTÁ USANDO O SERVER DE TESTE OU NÃO.
					$soap = new SoapClient( 'https://ecommerce.redecard.com.br/pos_virtual/wskomerci/cap.asmx?WSDL',
											array(
												'trace'                 => 1,
												'exceptions'            => 1,
												'style'                 => SOAP_DOCUMENT,
												'use'                   => SOAP_LITERAL,
												'soap_version'          => SOAP_1_1,
												'encoding'              => 'UTF-8'
												)
											);

					$ConfPreAuthorization = $soap->ConfPreAuthorizationTst ( $args );
					$ConfPreAuthorizationResponse = $ConfPreAuthorization->ConfPreAuthorizationTstResult->any;

					$resultado = $this->clear_confpreauthorization( $ConfPreAuthorizationResponse );

					if( $resultado['CODRET'] == 0)
					{

						$wc_order->add_order_note( __(
							'<h4 style="margin:0">Retorno do Estorno:</h4>
							<b>Código de Retorno:</b> ' . $resultado['CODRET'] . '<br/>
							<b>Mensagem:</b> ' . $resultado['MSGRET'] . '<br/>
							<b>Compr:</b> ' . $resultado['COMPR'] . '<br/>
							<b>Valor:</b> ' . $resultado['VALOR'] . '<br/>
							<b>Parcelas:</b> ' . $resultado['PARCELAS'] . '<br/>
							<b>Estabelecimento:</b> ' . $resultado['ESTABELECIMENTO'] . '<br/>
							<b>Data:</b> ' . $resultado['DATA'] . '<br/>
							<b>Horário:</b> ' . $resultado['HORARIO'] . '<br/>
							<b>Term:</b> ' . $resultado['TERM'] . '<br/>
							<b>Autorização Emissor:</b> ' . $resultado['AUTH_EMISSOR'] . '<br/>
							<b>Código:</b> ' . $resultado['CODPREAUTH'] . '<br/>
							<b>Cartão:</b> ' . $resultado['CARTAO'] . '<br/>'
							)
						);

						return true;
					}

				} catch (Exception $e) {

					$wc_order->add_order_note( 
						'<small>Houveram erros ao tentar realizar o estorno.<br/>' . $ConfPreAuthorizationResponse . '</small>'
					);

					return false;
				}

				return false;
	        }

	        /**
	         * Just clean a confpreauthorization.
	         *
	         * @param $arg
	         */

	        public function clear_confpreauthorization( $arg )
	        {

	  			$msg = explode( "@", $arg );

	  			$result = array(
					'CODRET' 		=> trim( preg_replace( "/[^0-9]/", "", $msg[0] ) ),
					'BUSINESS'		=> trim( $this->clear( $msg[0] ) ),
					'MSGRET' 		=> trim( $this->clear( $msg[1] ) ),
					'AUTH' 			=> str_replace('+', '|', $msg[2]),
					'INSTALLMENTS'	=> trim( $this->clear( $msg[3] ) ),
					'LOCAL' 		=> trim( $this->clear( $msg[4] ) ),
					'DATE' 			=> trim( $this->clear( $msg[5] ) ),
					'AUTH_LOCAL' 	=> trim( $this->clear( $msg[6] ) ),
					'PRE_AUTH'		=> trim( $this->clear( $msg[7] ) ),
					'CARD'			=> trim( $this->clear( $msg[8] ) )
				);

	  			/* Tratando todos os dados */
				$auth_explode = explode('VALOR:', $result['AUTH'] );
				$compr_explode = explode('COMPR:', $auth_explode['0']);
				$parcelas_explode = explode(':', $result['INSTALLMENTS']);
				$estabelecimento_explode = explode(' ', $result['LOCAL'] );
				$estab_explode = explode('ESTAB:', $estabelecimento_explode[0]);
				$count = count( $estabelecimento_explode );

				$when_explode 		= 	explode(' ', $result['DATE']);
				$data_explode 		= 	explode('-', $when_explode['0']);
				$term_explode 		= 	explode('TERM:', $when_explode['1']);
				$emissor_explode 	= 	explode(':', $result['AUTH_LOCAL']);
				$preauth_explode 	= 	explode(':', $result['PRE_AUTH']);
				$cartao_explode 	=	explode(':', $result['CARD'] );

				$var = array(
					'CODRET' 			=> trim( $result['CODRET'] ),
					'MSGRET' 			=> trim( $result['MSGRET'] ),
					'COMPR' 			=> trim( str_replace( '|', '', $compr_explode['1'] ) ),
					'VALOR' 			=> trim( str_replace( '|', '', $auth_explode['1'] ) ),
					'PARCELAS' 			=> trim( str_replace( ' ', '', $parcelas_explode['1'] ) ),
					'ESTABELECIMENTO'	=> trim( $estab_explode['1'] ),
					'HORARIO' 			=> trim( str_replace(' ', '', $data_explode['1'] ) ),
					'DATA' 				=> trim( str_replace( '.', '/', $data_explode['0'] ) ),
					'TERM' 				=> trim( $term_explode['1'] ),
					'EMISSOR' 			=> trim( $emissor_explode['1'] ),
					'CODPREAUTH' 		=> trim( $preauth_explode['1'] ),
					'CARTAO' 			=> trim( $cartao_explode['1'] )
				);

				$array = array(
					'CODRET'			=> $var['CODRET'],
					'MSGRET'			=> $var['MSGRET'],
					'COMPR'				=> $var['COMPR'],
					'VALOR'				=> $var['VALOR'],
					'PARCELAS'			=> $var['PARCELAS'],
					'ESTABELECIMENTO'	=> $var['ESTABELECIMENTO'],
					'DATA'				=> $var['DATA'],
					'HORARIO'			=> $var['HORARIO'],
					'TERM'				=> $var['TERM'],
					'AUTH_EMISSOR'		=> $var['EMISSOR'],
					'CODPREAUTH'		=> $var['CODPREAUTH'],
					'CARTAO'			=> $var['CARTAO'],
				);

				return $array;
	        }

	        public function clear( $arg )
			{
				$clear = str_replace('+', ' ', $arg);
				return $clear;
			}

		}
	}
}

add_action( 'add_meta_boxes', 'myplugin_add_custom_box' );
add_action( 'save_post', 'myplugin_save_postdata' );

function myplugin_add_custom_box() {

    $screens = array( 'shop_order' );

    foreach ($screens as $screen) {
        add_meta_box(
            'myplugin_sectionid',
            __( 'My Post Section Title', 'myplugin_textdomain' ),
            'myplugin_inner_custom_box',
            $screen,
            'side',
            'high'
        );
    }
}

function myplugin_inner_custom_box( $post ) {

	if($POST['submit_new_field']){
		print_r($post);
	}

	 echo '<input type="text" id="myplugin_new_field" name="myplugin_new_field" value="'.esc_attr($value).'" size="25" />';
	 echo '<input type="submit" id="submit_new_field" name="submit_new_field" value="Que?" size="25" />';
}



add_action( 'plugins_loaded', 'komerci_init' );

function komerci_addon_activate()
{
	if( !function_exists( 'curl_exec' ) )
	{
		wp_die( '<pre>This plugin requires PHP CURL library installled in order to be activated</pre>' );
	}
}

register_activation_hook( __FILE__, 'komerci_addon_activate' );

function komerci_settings_link( $links )
{
    $settings_link = '<a href="admin.php?page=wc-settings&tab=checkout&section=wc_komerci_gateway">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'komerci_settings_link' );

?>