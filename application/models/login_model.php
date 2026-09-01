<?php
class login_model extends CI_Model

{
  function get_user_by_username($username)
  {
    $username = trim((string) $username);
    if ($username === '')
    {
      return NULL;
    }

    $this->db->where('UNAME', $username);
    $this->db->select('UNAME, UPASS');
    $q = $this->db->get('users', 1);

    if ($q->num_rows() > 0)
    {
      return $q->row();
    }

    return NULL;
  }

  function update_password_hash($username, $hash)
  {
    $username = trim((string) $username);
    if ($username === '' || $hash === '')
    {
      return FALSE;
    }

    return $this->db->where('UNAME', $username)->update('users', array('UPASS' => $hash));
  }
}
?>
