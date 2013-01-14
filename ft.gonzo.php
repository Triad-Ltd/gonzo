<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gonzo_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'Gonzo',
		'version'	=> '1.0'
	);
	
	// --------------------------------------------------------------------
	
	/**
	 * Display Field on Publish Tab
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function display_field($data)
	{
		return form_textarea($this->field_name, $data);
	}
	
	
	/**
	 * Display Relationship Field Settings
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function display_settings($data)
	{
		$this->EE->db->where('group_id',$data['group_id']);
		$this->EE->db->where('field_type','file');
		$dbres = $this->EE->db->get('channel_fields');
		$fields = $dbres->result_array();

		// print_r($fields);
		$options = array("-");

		foreach ($fields as $i => $field){
			$options[$field['field_id']] = $field['field_label'];
		}

		$this->EE->table->add_row(
			"Select File Field",
			form_dropdown('field_related_id', $options, $data['field_related_id'], 'id="field_related_id" maxlength="100000"')
		);
	}
	
	
	function save_settings($data)
	{
		$data['field_fmt'] = 'none';
		$data['field_show_fmt'] = 'n';
		$data['field_search'] = 'y';	
		return $data;
	}	
}

// END Rel_ft class

/* End of file ft.rel.php */
/* Location: ./system/expressionengine/fieldtypes/ft.rel.php */