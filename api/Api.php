<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH . 'libraries/REST_Controller.php');

class Api extends REST_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Api_model');
    }

        /* Display all record from the table */
    public function allrecord_get() { 
        $result = $this->Api_model->selectData('info', 'id, name, email');
        if($result == null)
            $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);
        else
            $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
    }

    /* Add information to table */
    public function addinfo_post()
    {
        $data = $this->input->post();
        //$flag = 0;
        if($data['name'] == NULL || $data['email'] == NULL){
            $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            return;
        }
        if(count($this->Api_model->selectData('info','id',array('email'=>$data['email']))) > 0){
            $this->set_response(array('response_code'=>400,'response_message'=>'Email is already used','response_data'=>array()), REST_Controller::HTTP_OK);
            return;   
        }
        $result = $this->Api_model->insertData('info',$data);
        if($result == false) {
            $this->set_response(array('response_code'=>400,'response_message'=>'Error occurs while process','response_data'=>array()), REST_Controller::HTTP_OK);
            return;            
        }
        $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
        return;        
    }

    /* Get only single record using id from table */
    public function singlerecord_post() {
        $data = $this->input->post();
        if($data['id'] == NULL) {
            $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND);
            return;            
        }
        $result = $this->Api_model->selectData('info', 'id, name, email',array('id' => $data['id']));
        if($result == NULL){
            $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK); 
            return;           
        }
        $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
        return;
    }

    /* Update information for particular id to the table */
    public function updaterecord_post() {
        $data = $this->input->post();
        if( $data['id'] == null || $data['name'] == null || $data['email'] == null) {
            $this->set_response(array('response_code'=>400, 'response_message'=>'Parameter missing', 'response_data'=>array()), REST_Controller::HTTP_OK);
            return;
        }
        $result = $this->Api_model->updateData('info', array('name'=>$data['name'], 'email'=>$data['email']), array('id'=>$data['id']));
        if($result != 1) {
            $this->set_response(array('response_code'=>400,'response_message'=>'Error occurs while process','response_data'=>array()), REST_Controller::HTTP_OK); 
            return;           
        }
        $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
        return;
    }

    /* Delete information from table */
    public function delete_post() {
        $data = $this->input->post();
        if($data['id'] == null) {
            $this->set_response(array('response_code'=>400, 'response_message'=>'Parameter missing', 'response_data'=>array()), REST_Controller::HTTP_OK);
            return;
        }
        $result = $this->Api_model->deleteData('info', array('id'=>$data['id']));
        if($result == 1) {
            $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK); 
            return;           
        }
        $this->set_response(array('response_code'=>400,'response_message'=>'Error occurs while process','response_data'=>array()), REST_Controller::HTTP_OK);
        return;
    }
} 

?>