<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pelanggan extends MY_Controller {
    function __construct(){
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('form_validation');

        // Check Session Login
        if(!isset($_SESSION['logged_in'])){
            redirect(site_url('auth/login'));
        }
    }

    public function index(){
        if(isset($_GET['search'])){
            $filter = array();
            if(!empty($_GET['value']) && $_GET['value'] != ''){
                $filter[$_GET['search_by'].' LIKE'] = "%".$_GET['value']."%";
            }
            
            $total_row = $this->user_model->count_total_filter($filter);
            $data['users'] = $this->user_model->get_filter($filter,url_param());
        }else{
            $total_row = $this->user_model->count_total();
            $data['users'] = $this->user_model->get_all(url_param());
        }
        $data['paggination'] = get_paggination($total_row,get_search());

        $this->load->view('user/index',$data);
    }

    public function create(){
        $code_user = $this->user_model->get_last_id();
        if($code_user){
            $id = $code_user[0]->id;
            $data['code_user'] = generate_code('USR',$id,4);
        }else{
            $data['code_user'] = 'USR0001';
        }

        $this->load->view('user/form',$data);
    }

    public function edit($id = ''){
        $check_id = $this->user_model->get_by_id($id);
        if($check_id){
            $data['user'] = $check_id[0];
            $this->load->view('user/form',$data);
        }else{
            redirect(site_url('user'));
        }
    }

    public function save($id = ''){
        $this->form_validation->set_rules('id', 'ID', 'required');
        $this->form_validation->set_rules('username', 'Nama', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');
        $data['id'] = escape($this->input->post('id'));
        $data['username'] = escape($this->input->post('username'));
        $data['email'] = escape($this->input->post('email'));
        $data['password'] = escape($this->input->post('password'));
        if ($this->form_validation->run() != FALSE && !empty($id)) {
            // EDIT
            $check_id = $this->user_model->get_by_id($id);
            if($check_id){
                unset($data['id']);
                $this->user_model->update($id,$data);
            }
        }elseif($this->form_validation->run() != FALSE && empty($id)){
            // INSERT NEW
            $this->user_model->insert($data);
        }else{
            $this->session->set_flashdata('form_false', 'Harap periksa form anda.');
            redirect(site_url('user/create'));
        }
        redirect(site_url('user'));
    }
    public function delete($id){
        $check_id = $this->user_model->get_by_id($id);
        if($check_id){
            $this->user_model->delete($id);
        }
        redirect(site_url('user'));
    }
}
