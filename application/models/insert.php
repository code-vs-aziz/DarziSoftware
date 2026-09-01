<?php
class insert extends CI_Model

{
/* we will use the function getUsers */
  function insert_dat($data,$tab,$PAGE)
  {
  	 
	$this->db->insert($tab, $data);
	$this->_clear_master_dat_cache($tab);
	$this->load->helper('url');			   
	$this->session->set_flashdata('suss', 'yes');
			 	 redirect($PAGE); 
  }
    function insert_dat1($data,$tab)
  {
  	 
	$this->db->insert($tab, $data);
	//$this->load->helper('url');			   
	$this->session->set_flashdata('suss', 'yes');
			 	// redirect($PAGE); 
  }
  
	public function entry_update1($field,$id,$data,$table) {

    $this->db->where($field, $id);
    $this->db->update($table, $data);
	$this->_clear_master_dat_cache($table);
	$this->session->set_flashdata('suss', 'yes');
	}

  protected function _clear_master_dat_cache($table)
  {
    $cache_tables = array('embroidery', 'style', 'suit', 'employee');
    if ( ! in_array($table, $cache_tables, TRUE))
    {
      return;
    }
    $CI =& get_instance();
    $CI->load->model('Master_data_model', 'master_data');
    $CI->master_data->clear_dat_cache($table);
  }
	public function entry_update1_transfer($id, $data,$table) {
    $this->db->where('trnno', $id);
    $this->db->update($table, $data);
	$this->session->set_flashdata('suss', 'yes');
	}
 
		public function entry_update3_transfer($id,$id1, $data,$table) {
    $this->db->where('order_id', $id);
    $this->db->where('sr', $id1);
    $this->db->update($table, $data);
	$this->session->set_flashdata('suss', 'yes');
	}
	  function row_delete($field,$id,$table)
{
	//$this->db->where($field, $id);
	 $this->db->delete('order_dtl'); 
}
}
?>