<input type="hidden" id="card-type-hidden" name="card_type" />
<div class="memberdeck" id="card-type-container">
	<div class="form-row card-type-field" style="display:none;">
		<center>
			<label for="card_type_select"><?php _e('Card Type', 'memberdeck'); ?></label>
			<select class="required" name="card_type_select" id="card_type_select">
				<option value="0"><?php _e('CB', 'memberdeck') ?></option>
				<option value="1"><?php _e('Visa', 'memberdeck') ?></option>
				<option value="2"><?php _e('Mastercard', 'memberdeck') ?></option>
			</select>
		</center>
	</div>
</div>