<?php

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<fieldset id="<?php echo $this->id; ?>-cc-form">
						
	<p class="form-row form-row-wide">
		<label for="<?php echo $this->id; ?>-card-name">Nome impresso no cartão: <span class="required">*</span></label>		
		<input id="<?php echo $this->id; ?>-card-name" class="input-text wc-credit-card-form-card-name" type="text" maxlength="50" autocomplete="off" name="<?php echo $this->id; ?>-card-name" style="font-size: 1.5em; padding: 8px;"/>
	</p>

	<p class="form-row form-row-wide">
		
		<label for="<?php echo $this->id; ?>-card-installments">Parcelas: <span class="required">*</span></label>

		<select id="<?php echo $this->id; ?>-installments" name="<?php echo $this->id; ?>-installments" style="font-size: 1.5em;padding: 4px;width: 100%;">

		<?php
			

			

			for ($n = 1; $n <= $this->parcelas; $n++) {

				// Calcula as parcelas.
				$value = $this->get_order_total() / $n;
				
				echo '<option value="' . $n . '">' . $n . ' parcelas de R$' . $value . ' sem juros.</option>';
			}						
		?>

		</select>
	</p>

	<p class="form-row form-row-wide">
		<label for="<?php echo $this->id; ?>-card-number">Número do cartão <span class="required">*</span></label>
		<input id="<?php echo $this->id; ?>-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••" name="<?php echo $this->id; ?>-card-number" />
	</p>

	<p class="form-row form-row-first">
		<label for="<?php echo $this->id; ?>-card-expiry">Validade (MM/AA) <span class="required">*</span></label>
		<input id="<?php echo $this->id; ?>-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="MM / AA" name="<?php echo $this->id; ?>-card-expiry" />
	</p>

	<p class="form-row form-row-last">
		<label for="<?php echo $this->id; ?>-card-cvc">Código do cartão <span class="required">*</span></label>
		<input id="<?php echo $this->id; ?>-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="CVC" name="<?php echo $this->id; ?>-card-cvc" />
	</p>

</fieldset>