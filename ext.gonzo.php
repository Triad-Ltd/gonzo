<?php

class Gonzo_ext {

	var $name			= 'Gonzo File Scraper Field';
	var $version 		= '1';
	var $description	= 'Populates any gonzo fields with data from their related file fields. Requires cli programs: antiword, pdftotext';
	var $settings_exist	= 'n';
	var $docs_url		= ''; // 'http://expressionengine.com/user_guide/';

	var $settings 		= array();
	var $query_new		= array();
    
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function Entry_permissions_ext($settings = '') {
		$this->EE =& get_instance();

		$this->settings = $settings;
	}
	
	function activate_extension() {

		$this->EE =& get_instance();
				
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> "parse_gonzo_fields",
			'hook'		=> "entry_submission_absolute_end",
			'settings'	=> "",
			'priority'	=> 2,
			'version'	=> $this->version,
			'enabled'	=> "y"
		);
	
		// insert in database
		$this->EE->db->insert('exp_extensions', $data);
	}
	
	function disable_extension() {
		$this->EE =& get_instance();
				
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('exp_extensions');
	}

	function parse_gonzo_fields($entry_id,$meta,$data) {
		$this->EE =& get_instance();

		echo $entry_id;
		print_r($data);

		$this->EE->db->where('channel_id',$meta['channel_id']);
		$res = $this->EE->db->get('channels');
		$row = $res->result_array();

		$this->EE->db->where('group_id',$row[0]['field_group']);
		$this->EE->db->where('field_type','gonzo');
		$res = $this->EE->db->get('channel_fields');
		$row = $res->result_array();

		$gonzo_field_id = $row[0]['field_id'];
		$file_field_id = $row[0]['field_related_id'];

		$directory_id = $data['revision_post']['field_id_'.$file_field_id.'_directory'];

		$this->EE->db->where('id',$directory_id);
		$res = $this->EE->db->get('upload_prefs');
		$row = $res->result_array();


		$file_name = $data['field_id_'.$file_field_id];
		$file_source = str_replace('{filedir_'.$directory_id.'}',$row[0]['server_path'],$file_name);

		if (file_exists($file_source)){
			$file_bits = explode(".",$file_name);
			$ext = $file_bits[count($file_bits) - 1];

			switch ($ext) {
				case 'pdf':
					$grabbed_text = $this->pdf_text($file_source);
					break;

				case 'docx':
					$grabbed_text = $this->docx_text($file_source);
					break;

				case 'doc':
					$grabbed_text = $this->doc_text($file_source);
					break;

				case 'doc':
					$grabbed_text = $this->txt_text($file_source);
					break;
				
				default:
					$grabbed_text = "";
					break;
			}

			if ($grabbed_text != ""){
				$grabbed_text = str_replace("\n"," ",htmlentities($grabbed_text));

				// swap this back to active record class, put like this for debugging purposes.
				$sql = "UPDATE exp_channel_data SET field_id_$gonzo_field_id = '".$this->EE->db->escape_str($grabbed_text)."' WHERE entry_id = '".$entry_id."'";
				$this->EE->db->query($sql) or die ();
			}

			return;
		} else {

			return;
		}

	}

	private function docx_text($file_name) {
		return $this->readZippedXML($file_name, 'word/document.xml');
	}

	private function pdf_text($file_name) {
		return shell_exec("pdftotext -enc Latin1 '$file_name' -");
	}

	private function doc_text($file_name) {
		return shell_exec('antiword '.$file_name);
	}

	private function txt_text($file_name) {
		return file_get_contents($file_name);
	}

	function readZippedXML($archiveFile, $dataFile) {
		// Create new ZIP archive
		$zip = new ZipArchive;
		// Open received archive file
		if (true === $zip->open($archiveFile)) {
			// If done, search for the data file in the archive
			if (($index = $zip->locateName($dataFile)) !== false) {
				// If found, read it to the string
				$data = $zip->getFromIndex($index);
				// Close archive file
				$zip->close();
				// Load XML from a string
				// Skip errors and warnings
				$xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
				// Return data without XML formatting tags
				return strip_tags($xml->saveXML());
			}
			$zip->close();
		}
	}
	
}