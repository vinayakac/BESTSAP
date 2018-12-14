<?php
class ID_FES_Team_Info {
	var $form;
	var $vars;

	function __construct($vars) {

	}

	public static function id_fes_get_team_info($form, $vars) {
		$form = array(
			array(
				'before' => '<div class="fes_section"><h3>'.apply_filters('fes_team_information_label', __('Team Information', 'ignitiondeck')).'</h3>',
				'label' => __('Company Name', 'ignitiondeck'),
				'value' => (isset($vars['company_name']) ? $vars['company_name'] : ''),
				'name' => 'company_name',
				'id' => 'company_name',
				'type' => 'text',
				'class' => 'required',
				'wclass' => 'form-row half left'
			),
			array(
				'value' => wp_create_nonce( 'idcf_fes_section_nonce' ),
				'type' => 'hidden',
				'id' => 'idcf_fes_wp_nonce',
				'name' => 'idcf_fes_wp_nonce',
				'wclass' => 'hide',
			),
			array(
				'label' => __('Company Logo', 'ignitiondeck'),
				'value' => (isset($vars['company_logo']) ? $vars['company_logo'] : ''),
				'misc' => (isset($vars['company_logo']) ? 'data-url="'.$vars['company_logo'].'" accept="image/*"' : 'accept="image/*"'),
				'name' => 'company_logo',
				'id' => 'company_logo',
				'type' => 'file',
				'wclass' => 'form-row half',
				),
			array(
				'label' => __('Company Location', 'ignitiondeck'),
				'value' => (isset($vars['company_location']) ? $vars['company_location'] : ''),
				'name' => 'company_location',
				'id' => 'company_location',
				'type' => 'text',
				'class' => 'required',
				'wclass' => 'form-row half left'
			),
			array(
				'label' => __('Company URL', 'ignitiondeck'),
				'value' => (isset($vars['company_url']) ? $vars['company_url'] : ''),
				'name' => 'company_url',
				'id' => 'company_url',
				'type' => 'text',
				'class' => 'required',
				'wclass' => 'form-row half'
			),
			array(
				'label' => __('Company Facebook', 'ignitiondeck'),
				'value' => (isset($vars['company_fb']) ? $vars['company_fb'] : ''),
				'name' => 'company_fb',
				'id' => 'company_fb',
				'type' => 'text',
				'class' => '',
				'wclass' => 'form-row half left'
			),
			array(
				'label' => __('Company Twitter', 'ignitiondeck'),
				'value' => (isset($vars['company_twitter']) ? $vars['company_twitter'] : ''),
				'name' => 'company_twitter',
				'id' => 'company_twitter',
				'type' => 'text',
				'class' => '',
				'wclass' => 'form-row half',
				'after' => '</div>'
			)
		);
		return $form;
	}
}

add_filter('id_fes_form_init', array('ID_FES_Team_Info', 'id_fes_get_team_info'), 10, 2);
?>