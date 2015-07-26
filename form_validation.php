<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| Form validation config file
| -------------------------------------------------------------------
|
*/

$config = array(
    array(
        'field'   => 'firstname',
        'label'   => 'first name',
        'rules'   => 'required|trim|min_length[2]|max_length[50]',
        'groups'  => 'login[1]|signup[2]|admin'
    ),
    array(
        'field'   => 'initials',
        'label'   => 'initials',
        'rules'   => 'required|trim|min_length[1]|max_length[10]',
        'groups'  => 'login[1]|signup[2]|admin'
    ),
    array(
        'field'   => 'lastname',
        'label'   => 'last name',
        'rules'   => 'required|trim|min_length[2]|max_length[50]',
        'groups'  => 'login[1]|signup[2]|admin'
    ),
    array(
        'field'   => 'email',
        'label'   => 'e-mail',
        'rules'   => 'required|trim|valid_email|max_length[75]',
        'groups'  => 'login[1]|signup[2]|admin'
    ),
);

/* End of file Template.php */
/* Location: ./application/config/form_validation.php */
