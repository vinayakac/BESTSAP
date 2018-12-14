<li class="md-box half">
	<div class="md-profile lemonway-settings">
		<h3><?php _e('LemonWay', 'memberdeck'); ?></h3>
		<p> <?php _e('Signup as Creator in LemonWay and receive funds', 'memberdeck'); ?></p>
		<p>
			<?php if (isset($_GET['kyc_status']) && $_GET['kyc_status'] == "error") { ?>
				<?php $message = urldecode($_GET['msg']); ?>
				<small class="kyc-upload-error"><?php echo $message; ?></small>
			<?php } else if (isset($_GET['kyc_status']) && $_GET['kyc_status'] == "success") { ?>
				<?php
				if (isset($_GET['msg'])) {
					$message = urldecode($_GET['msg']);
				} else {
					$message = __('KYC document uploaded successfully', 'memberdeck');
				}
				?>
				<small class="kyc-upload-error"><?php echo $message; ?></small>
			<?php } ?>
		</p>
		<div class="form-row inline">
			<input type="checkbox" id="lemonway_connect" name="lemonway_connect" class="required" value="1" <?php echo ((isset($lemonway_connect) && $lemonway_connect == '1') ? 'checked="checked"' : ''); ?> />
			<label for="lemonway_connect"><?php _e('Signup as Creator in LemonWay', 'memberdeck'); ?></label>
		</div>
		<?php if (isset($lemonway_connect) && $lemonway_connect == '1') { ?>
			<div class="form-row half">
				<label for="kyc_document_type"><?php _e('Document Type', 'memberdeck'); ?></label>
				<select id="kyc_document_type" name="kyc_document_type" class="kyc_document_type">
					<option value="0"><?php _e('ID card or Passport', 'memberdeck'); ?></option>
					<option value="1"><?php _e('Proof of Address', 'memberdeck'); ?></option>
					<option value="2"><?php _e('IBAN', 'memberdeck'); ?></option>
					<option value="7"><?php _e('Proof of existence at the Chamber of Commerce and Industry', 'memberdeck'); ?></option>
				</select>
			</div>
            <div class="form-row half">
				<label for="kyc_document"><?php _e('KYC Document', 'memberdeck'); ?></label>
				<input type="file" id="kyc_document" name="kyc_document" class="kyc_document" value="" accept="image/*">
			</div>
			<div class="form-row half">
				<input type="hidden" id="kyc_document_uploaded" name="kyc_document_uploaded" value="no" />
				<input type="button" id="upload_kyc_document" name="upload_kyc_document" value="Upload Document">
			</div>
		<?php } ?>
	</div>
</li>