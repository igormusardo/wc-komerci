<?php

if ( ! defined( 'ABSPATH' ) ) exit;
/*
 * Main class WC_Komerci_Errors
 */
class WC_Komerci_Errors
{
	/**
	 * Get the error message by the code.
	 *
	 * @param int $code
	 * @return string
	 */
	public function get_error_message( $code )
	{
		$messages = array(
			'20' => __( 'Parâmetro obrigatório ausente.', 'komerci' ),
			'21' => __( 'Número de filiação em formato inválido.', 'komerci' ),
			'22' => __( 'Número de parcelas incompatível com a transação.', 'komerci' ),
			'23' => __( 'Problemas no cadastro do estabelecimento.', 'komerci' ),
			'24' => __( 'Problemas no cadastro do estabelecimento.', 'komerci' ),
			'25' => __( 'Estabelecimento não cadastrado.', 'komerci' ),
			'26' => __( 'Estabelecimento não cadastrado.', 'komerci' ),
			'27' => __( 'Cartão inválido.', 'komerci' ),
			'28' => __( 'CVC2 em formato inválido.', 'komerci' ),
			'29' => __( 'Operação não permitida.', 'komerci' ),
			'30' => __( 'Parâmetro AVS Ausente.', 'komerci' ),
			'31' => __( 'Número do pedido maior que o permitido.', 'komerci' ),
			'32' => __( 'Código IATA inválido ou inexistente.', 'komerci' ),
			'33' => __( 'Código IATA inválido.', 'komerci'),
			'34' => __( 'Distribuidor inválido ou inexistente.', 'komerci' ),
			'35' => __( 'Problemas no cadastro do estabelecimento.', 'komerci' ),
			'36' => __( 'Operação não permitida.', 'komerci' ),
			'37' => __( 'Distribuidor inválido ou inexistente.', 'komerci' ),
			'38' => __( 'Operação não permitida no ambiente de teste.', 'komerci' ),
			'39' => __( 'Operação não permitida para o código IATA informado.', 'komerci' ),
			'40' => __( 'Código IATA inválido ou inexistente.', 'komerci' ),
			'41' => __( 'Problemas no cadastro do estabelecimento.', 'komerci' ),
			'42' => __( 'Problemas no cadastro do usuário do estabelecimento.', 'komerci' ),
			'43' => __( 'Problemas na autentificação do usuário.', 'komerci' ),
			'44' => __( 'Usuário incorreto para testes.', 'komerci' ),
			'45' => __( 'Problemas no cadastro do estabelecimento para testes.', 'komerci' ),
			'56' => __( 'Dados inválidos.', 'komerci' )
		);

		if ( isset( $messages[ $code ] ) ) {
			return $messages[ $code ];
		}
        
		return __( 'Houve um erro em processar o seu pagamento. Por favor, salve todos os dados e entre em contato conosco.', 'komerci' );
	}
	
	/**
	 * Get the error message in authorization.
	 *
	 * @param int $code
	 * @return string
	 */
	public function get_error_authorization( $authorization )
	{
		$code = array(
			'0'  => __( 'Transação aprovada.', 'komerci'),
			'50' => __( 'Transação não autorizada.', 'komerci'),
			'52' => __( 'Transação não autorizada.', 'komerci'),
			'54' => __( 'Transação não autorizada.', 'komerci'),
			'55' => __( 'Transação não autorizada.', 'komerci'),
			'57' => __( 'Transação não autorizada.', 'komerci'),
			'59' => __( 'Transação não autorizada.', 'komerci'),
			'61' => __( 'Transação não autorizada.', 'komerci'),
			'62' => __( 'Transação não autorizada.', 'komerci'),
			'64' => __( 'Transação não autorizada.', 'komerci'),
			'66' => __( 'Transação não autorizada.', 'komerci'),
			'67' => __( 'Transação não autorizada.', 'komerci'),
			'68' => __( 'Transação não autorizada.', 'komerci'),
			'70' => __( 'Transação não autorizada.', 'komerci'),
			'71' => __( 'Transação não autorizada.', 'komerci'),
			'73' => __( 'Transação não autorizada.', 'komerci'),
			'75' => __( 'Transação não autorizada.', 'komerci'),
			'78' => __( 'Transação não autorizada.', 'komerci'),
			'79' => __( 'Transação não autorizada.', 'komerci'),
			'80' => __( 'Transação não autorizada.', 'komerci'),
			'82' => __( 'Transação não autorizada.', 'komerci'),
			'83' => __( 'Transação não autorizada.', 'komerci'),
			'84' => __( 'Transação não autorizada.', 'komerci'),
			'85' => __( 'Transação não autorizada.', 'komerci'),
			'87' => __( 'Transação não autorizada.', 'komerci'),
			'89' => __( 'Transação não autorizada.', 'komerci'),
			'90' => __( 'Transação não autorizada.', 'komerci'),
			'91' => __( 'Transação não autorizada.', 'komerci'),
			'93' => __( 'Transação não autorizada.', 'komerci'),
			'94' => __( 'Transação não autorizada.', 'komerci'),
			'95' => __( 'Transação não autorizada.', 'komerci'),
			'97' => __( 'Transação não autorizada.', 'komerci'),
			'99' => __( 'Transação não autorizada.', 'komerci'),
			'51' => __( 'Estabelecimento inválido.', 'komerci'),
			'92' => __( 'Estabelecimento inválido.', 'komerci'),
			'98' => __( 'Estabelecimento inválido.', 'komerci'),
			'53' => __( 'Transação inválida.', 'komerci'),
			'56' => __( 'Refaça a transação.', 'komerci'),
			'76' => __( 'Refaça a transação.', 'komerci'),
			'86' => __( 'Refaça a transação.', 'komerci'),
			'58' => __( 'Problemas com o cartão.', 'komerci'),
			'63' => __( 'Problemas com o cartão.', 'komerci'),
			'65' => __( 'Problemas com o cartão.', 'komerci'),
			'69' => __( 'Problemas com o cartão.', 'komerci'),
			'72' => __( 'Problemas com o cartão.', 'komerci'),
			'77' => __( 'Problemas com o cartão.', 'komerci'),
			'96' => __( 'Problemas com o cartão.', 'komerci'),
			'60' => __( 'Valor inválido.', 'komerci'),
			'74' => __( 'Instituição sem comunicação - Resposta AVS.', 'komerci'),
			'81' => __( 'Banco não pertence à rede.', 'komerci')
		);

		if ( isset( $code[ $authorization ] ) ) {
			return $code[ $authorization ];
		}
        
		return __( 'Problemas para autorizar a sua compra. Por favor, salve todos os dados e entre em contato conosco.', 'komerci' );
	}

}

?>
