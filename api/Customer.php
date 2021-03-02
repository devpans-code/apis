<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Customer extends REST_Controller 
{

    function __construct()
    {
        parent::__construct();
        error_reporting(E_ALL & ~E_NOTICE | E_WARNING);
         header("Access-Control-Allow-Origin: *");
         $this->load->model('Api_model');
         $this->load->model('Email_model');
    }
    
    /**
     * @api {post} customer/cusregister Customer Registration
     * @apiVersion 0.1.0
     * @apiName Customer Registration
     * @apiGroup Customer
     *
     * @apiParam {string}           name        Fullname
     * @apiParam {string}           email       Email
     * @apiParam {string}           mobile      Phone Number
     * @apiParam {string}           address     Address Line 1
     * @apiParam {int}              country     country_id
     * @apiParam {int}              state       state_id
     * @apiParam {int}              city        city_id
     * @apiParam {password}         password    Password
     * @apiParam {int}              woo_cus_id  WooCommerce-CustomerID 
     *
     * @apiSuccessExample Success
     *  {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": {
     *          "otp": 243048,
     *          "client_id": 1
     *      }
     *  }
     *
     * @apiError Message Error Code 400.
     *
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     * @apiErrorExample No data found
     *     {
     *          "response_code": 400,
     *          "response_message": "No data found",
     *          "response_data": []
     *     }
     *  @apiErrorExample Email address and mobile number are already registered
     *     {
     *          "response_code": 400,
     *          "response_message": "Email address and mobile number are already registered",
     *          "response_data": []
     *      }
     *  @apiErrorExample Mobile number already registered
     *      {
     *          "response_code": 400,
     *          "response_message": "Mobile number already registered",
     *          "response_data": []
     *      }
     *  @apiErrorExample Email address already registered
     *      {
     *          "response_code": 400,
     *          "response_message": "Email address already registered",
     *          "response_data": []
     *      }
    */
    public function cusregister_post() //Customer Registration
    {

            $data = $this->post();
            if($data['woo_cus_id']!=NULL && $data['name']!=NULL && $data['password']!=NULL && $data['email']!=NULL && $data['mobile']!=NULL && $data['address']!=NULL && $data['country']!=NULL && $data['state']!=NULL && $data['city']!=NULL )
            {
                
                if(count($this->Api_model->selectData('clients','client_id',array('email'=>$data['email'],'mobile'=>$data['mobile']))) > 0)
                {
                    $flag=1;
                }
                else if(count($this->Api_model->selectData('clients','client_id',array('email'=>$data['email']))) > 0)
                {
                    $flag=2;
                }
                else if(count($this->Api_model->selectData('clients','client_id',array('mobile'=>$data['mobile']))) > 0)
                {
                    $flag=3;
                }
                else
                {
                    $flag=0;
                }
                // print_r($flag);exit();

                if($flag == 0)
                {
                    $data['password']=md5($data['password']);
                    $data['createdat']=date('Y-m-d h:i:s');
                    $data['otp']=mt_rand(111111,999999);
                    $data['status']='Inactive';

                    // print_r($data);exit();
                    $result['otp']=$data['otp'];
                    $otpmsg="Hello ".$data['name'].",\n\nYour OTP: ".$data['otp']."\n\nThanks,\nPlumb Tech";
                    send_sms($data['mobile'],$otpmsg);
                    $result['client_id']=$this->Api_model->insertData('clients',$data);
                    if(count($result)>0)
                    {
                        $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                    } 
                    else
                    {
                        $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);            
                    }
                }
                else
                {
                    if($flag == 1)
                    {
                        $this->set_response(array('response_code'=>400,'response_message'=>'Email address and mobile number are already registered','response_data'=>array()), REST_Controller::HTTP_OK);
                    }
                    else if($flag == 2)
                    {
                        $this->set_response(array('response_code'=>400,'response_message'=>'Email address already registered','response_data'=>array()), REST_Controller::HTTP_OK);
                    }
                    else
                    {
                        $this->set_response(array('response_code'=>400,'response_message'=>'Mobile number already registered','response_data'=>array()), REST_Controller::HTTP_OK);
                    }
                    
                }
                
            }
            else
            {
                 $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            }

    }


    /**
     * @api {post} customer/updateAddress Update address
     * @apiVersion 0.1.0
     * @apiName Update address
     * @apiGroup Customer
     *   
     * @apiParam {int}              client_id           Client ID
     * @apiParam {string}           app_address         Address Line
     * @apiParam {string}           app_state           State
     * @apiParam {string}           app_city            City
     * @apiParam {string}           app_country         Country
     *   
     *
     * @apiSuccessExample Success
     *  {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": 1
     *  }
     *
     * @apiError Message Error Code 400.
     *
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
    */
    public function updateAddress_post() //Update address
    {
        $data = $this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $data = $this->post();
            if($data['client_id']!=NULL && $data['app_address']!=NULL && $data['app_state']!=NULL && $data['app_city']!=NULL && $data['app_country']!=NULL)
            {
                $result=getStateCountry($data['app_city']);
                $insert=array(
                    'address'=>$data['app_address'],
                    'city'=>$result['city_id'],
                    'state'=>$result['state_id'],
                    'country'=>$result['country_id']
                );
                $update=$this->Api_model->updateData('clients',$insert,array('client_id'=>$data['client_id']));
                $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$update), REST_Controller::HTTP_OK);  
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            }
        }    
    }

    /**
     * @api {post} customer/fetchProfile Fetch Profile
     * @apiVersion 0.1.0
     * @apiName Fetch Profile
     * @apiGroup Customer
     *
     * @apiParam {int}              client_id   Client's ID
     *
     * @apiSuccessExample Success
     *      {
     *          "response_code": 200,
     *          "response_message": "Success",
     *          "response_data": [
     *              {
     *                  "name": "Gopal  Bhuva",
     *                  "email": "gopal@hmmbiz.com",
     *                  "mobile": "7779056790",
     *                  "profile_image": "http://resourceserver.in/demo/vp-admin/public/uploads/profile/IMG_3636v21.jpg"
     *              }
     *          ]
     *      }
     *
     * @apiError Message Error Code 400.
     *
     * @apiErrorExample No Data Found
     *      {
     *          "response_code": 400,
     *          "response_message": "No Data Found",
     *          "response_data": []
     *      } 
     * @apiErrorExample Parameter missing
     *      {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *      }
    */
    public function fetchProfile_post() //Fetch Profile created by Taha 28-01-2019
    {
        $data = $this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            if(isset($data['client_id']) && $data['client_id']!='')
            {
                $result=$this->Api_model->selectData('clients','name,email,mobile,profile_image',array('client_id'=>$data['client_id']));
                if($result)
                {
                    if($result[0]->profile_image != '')
                    {
                        $result[0]->profile_image=base_url().'public/uploads/profile/'.$result[0]->profile_image;
                        $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                    }
                    else
                    {
                        $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                    }
                }
                else
                {
                    $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK); 
                }       
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            }
        }   
    }

    /**
     * @api {post} customer/editProfile Edit Profile
     * @apiVersion 0.1.0
     * @apiName Edit Profile
     * @apiGroup Customer
     *
     * @apiParam {int}              client_id   Client's ID      (Required)
     * @apiParam {string}           name        Full name        (Optional)
     * @apiParam {string}           email       Email            (Optional) 
     * @apiParam {string}           mobile      Phone Number     (Optional) 
     * @apiParam {file}             profile     Profile Image    (Optional) 
     *
     * @apiSuccessExample Success
     *      {
     *          "response_code": 200,
     *          "response_message": "Profile updated",
     *          "response_data": 1
     *      }
     *      {
     *          "response_code": 200,
     *          "response_message": "Profile updated, please verify mobile number with OTP.",
     *          "response_data": {
     *              "update": 1,
     *              "newmobile": "9925738146"
     *          }
     *      }
     *
     * @apiError Message Error Code 400.
     *
     * @apiErrorExample Parameter missing
     *      {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *      }
     *  @apiErrorExample Mobile number already registered
     *      {
     *         "response_code": "400",
     *         "response_message": "Mobile number is already registered.",
     *         "response_data": []
     *      }
     * @apiErrorExample Email address already registered
     *      {
     *          "response_code": "400",
     *          "response_message": "Email address is already registered.",
     *          "response_data": []
     *      }
    */
    public function editProfile_post() //Edit Profile created by Taha 16-01-2019 //Edited 21-01-2019
    {
        $data = $this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            if(isset($data['client_id']) && $data['client_id']!='')
            {
                $update=array();

                // *******************Profile image upload********************
                $config['upload_path'] ='./public/uploads/profile';
                $config['allowed_types'] = '*'; 
                $this->load->library('upload', $config);   
                // *******************Profile image upload********************

                $update['client_id']=$data['client_id'];

                if(isset($data['name']))
                {
                    $update['name']=$data['name'];
                }
                // print_r($update);exit();
                if(!isset($data['email']))
                {
                    if(!isset($data['mobile']))
                    {
                        if(isset($_FILES['profile']))
                        {                       
                            if ( ! $this->upload->do_upload('profile'))
                            {
                                $error = array('error' => $this->upload->display_errors());
                            }
                            else
                            {
                                $profile =$this->upload->data();
                                $update['profile_image']=$profile['file_name'];
                            }                           
                        }

                        $result=$this->Api_model->updateData('clients',$update,array('client_id'=>$data['client_id']));
                       
                        $this->set_response(array('response_code'=>200,'response_message'=>'Profile updated','response_data'=>$result), REST_Controller::HTTP_OK);
                    }
                    else
                    {   
                        $verifyMobile=$this->Api_model->selectData('clients','mobile',array('mobile'=>$data['mobile'],'client_id != '=>$data['client_id']));
                        if(count($verifyMobile) == 0)
                        {
                            $update['otp']=mt_rand(111111,999999);
                            $otpmsg="Hello,\n\nYour OTP: ".$update['otp']."\n\nThanks,\nPlumb Tech";
                            send_sms($data['mobile'],$otpmsg);

                            if(isset($_FILES['profile']))
                            {                       
                                if ( ! $this->upload->do_upload('profile'))
                                {
                                    $error = array('error' => $this->upload->display_errors());
                                }
                                else
                                {
                                    $profile =$this->upload->data();
                                    $update['profile_image']=$profile['file_name'];
                                }                           
                            }

                            $result['update']=$this->Api_model->updateData('clients',$update,array('client_id'=>$data['client_id']));
                            $result['newmobile']=$data['mobile'];
                            $this->set_response(array('response_code'=>200,'response_message'=>'Profile updated, please verify mobile number with OTP.','response_data'=>$result), REST_Controller::HTTP_OK);
                        }
                        else
                        {
                            $this->response(array('response_code'=>'400', 'response_message' => 'Mobile number is already registered.' , 'response_data' => array()));
                        }
                    }
                }
                else
                {
                    $verifyEmail=$this->Api_model->selectData('clients','email',array('email'=>$data['email'],'client_id != '=>$data['client_id'])); 
                    
                    if(count($verifyEmail) == 0)
                    {
                        if(!isset($data['mobile']))
                        {
                            $update['email']=$data['email'];

                            if(isset($_FILES['profile']))
                            {                       
                                if ( ! $this->upload->do_upload('profile'))
                                {
                                    $error = array('error' => $this->upload->display_errors());
                                }
                                else
                                {
                                    $profile =$this->upload->data();
                                    $update['profile_image']=$profile['file_name'];
                                }                           
                            } 

                            $result=$this->Api_model->updateData('clients',$update,array('client_id'=>$data['client_id']));

                            $this->set_response(array('response_code'=>200,'response_message'=>'Profile updated','response_data'=>$result), REST_Controller::HTTP_OK);
                        }
                        else
                        {   
                            $verifyMobile=$this->Api_model->selectData('clients','mobile',array('mobile'=>$data['mobile'],'client_id != '=>$data['client_id']));
                            if(count($verifyMobile) == 0)
                            {
                                $update['email']=$data['email'];
                                $update['otp']=mt_rand(111111,999999);
                                $otpmsg="Hello,\n\nYour OTP: ".$update['otp']."\n\nThanks,\nPlumb Tech";
                                send_sms($data['mobile'],$otpmsg);

                                if(isset($_FILES['profile']))
                                {                       
                                    if ( ! $this->upload->do_upload('profile'))
                                    {
                                        $error = array('error' => $this->upload->display_errors());
                                    }
                                    else
                                    {
                                        $profile =$this->upload->data();
                                        $update['profile_image']=$profile['file_name'];
                                    }                           
                                } 

                                $result['update']=$this->Api_model->updateData('clients',$update,array('client_id'=>$data['client_id']));
                                $result['newmobile']=$data['mobile'];
                                // echo "<pre>";print_r($this->db->last_query());exit();
                                $this->set_response(array('response_code'=>200,'response_message'=>'Profile updated, please verify mobile number with OTP','response_data'=>$result), REST_Controller::HTTP_OK);
                            }
                            else
                            {
                                $this->response(array('response_code'=>'400', 'response_message' => 'Mobile number is already registered.' , 'response_data' => array()));
                            }
                        }
                    }
                    else
                    {
                        $this->response(array('response_code'=>'400', 'response_message' => 'Email address is already registered.' , 'response_data' => array()));
                    } 
                }        
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            }
        }   
    }

    /**
     * @api {post} Customer/verifyUpdatedMobile Verify Updated Mobile
     * @apiVersion 0.1.0
     * @apiName Verify Upated Mobile
     * @apiGroup Customer
     * @apiParam {int}   client_id   Client ID.
     * @apiParam {int}   otp         OTP.
     * @apiParam {int}   mobile      Mobile (Which was entered by client in Edit Profile Form).
     *
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "Mobile number verified.",
     *      "response_data": {
     *          "update": 1
     *      }
     *  }
     * @apiErrorExample Incorrect OTP
     *  {
     *      "response_code": "400",
     *      "response_message": "Incorrect OTP",
     *      "response_data": []
     *  }
     * @apiErrorExample Parameter invalid
     *  {
     *      "response_code": "400",
     *      "response_message": "Parameter missing",
     *      "response_data": []
     *  }
    */
    public function verifyUpdatedMobile_post() //Verify Upated Mobile created by Taha 16-01-2019
    {
        $data=$this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            if($data['client_id']!=NULL && $data['mobile']!=NULL && $data['otp']!=NULL)
            {

                $where=array(
                    'client_id'=>$data['client_id'],
                    'otp'=>$data['otp']
                );
                $verify=$this->Api_model->selectData('clients','otp',$where);

                if($verify)
                {
                    $result['update']=$this->Api_model->updateData('clients',array('mobile'=>$data['mobile'],'otp'=>''),array('client_id'=>$data['client_id']));
                    $this->response(array('response_code'=>'200', 'response_message' => 'Mobile number verified.' , 'response_data' => $result));                   
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'Incorrect OTP' , 'response_data' => array()));
                }
            }
            else
            {
                $this->set_response(array('response_code'=>'400','response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK);
            }
        }    
    }

    /**
     * @api {post} Customer/resendOtpMobileUpdate Resend Otp (For Mobile Updation)
     * @apiVersion 0.1.0
     * @apiName Resend Otp (For Mobile Updation)
     * @apiGroup Customer
     * @apiParam {int}   client_id   Client ID.
     * @apiParam {int}   mobile      Mobile (Which was entered by client in Edit Profile Form).
     * 
     *
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "update": 1
     *      }
     *  }
     * @apiErrorExample No Data Found
     *   {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     * @apiErrorExample Parameter invalid
     *   {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *   }
    */
    public function resendOtpMobileUpdate_post() //Resend Otp (For Mobile Updation) created by Taha 16-01-2019
    {
        $data=$this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            if($data['client_id']!=NULL && $data['mobile']!=NULL)
            {

                $data['otp']=mt_rand(100000,999999);
                $result['update']=$this->Api_model->updateData('clients',array('otp'=>$data['otp']),array('client_id'=>$data['client_id']));
                $otpmsg="Hello ,\n\nYour OTP: ".$data['otp']."\n\nThanks,\nPlumb Tech";
                send_sms($data['mobile'],$otpmsg);

                if($result['update'])
                {
                    
                    $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                }
            }
            else
            {
                $this->set_response(array('response_code'=>'400','response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK);
            }
        }    
    }

    /**
     * @api {post} Customer/verifyOtp Otp Verification
     * @apiVersion 0.1.0
     * @apiName Otp Verification
     * @apiGroup Customer
     * @apiParam {int}   client_id   Client ID.
     * @apiParam {int}   otp         OTP.
     *
     * @apiSuccessExample Success
     *        {
     *       "response_code": "200",
     *       "response_message": "success",
     *      "response_data": [
     *          {
     *               "otp": "592875"
     *          }
     *       ]
     *   }
     * @apiErrorExample Incorrect OTP
     *  {
     *      "response_code": "400",
     *      "response_message": "Incorrect OTP",
     *      "response_data": []
     *  }
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     */
    public function verifyOtp_post() //Verify otp created by Taha 14-11-2018
    {
        $data=$this->post();
        $client_id=$data['client_id'];
        $otp=$data['otp'];
        // print_r($data);exit();
        if(($client_id!=NULL) && ($otp!=NULL))
        {
            $where=array(
                'client_id'=>$client_id,
                'otp'=>$otp
            );
            $verify=$this->Api_model->selectData('clients','otp',$where);
            if($verify)
            {
                $update['result']=$this->Api_model->updateData('clients',array('status'=>'Active','otp'=>''),array('client_id'=>$client_id));
            }
            if(count($verify)>0)
            {
                 $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $verify)); 
            }
            else
            {
                $this->response(array('response_code'=>'400', 'response_message' => 'Incorrect OTP' , 'response_data' => array()));
            }
        }
        else
        {
            $this->set_response(array('response_code'=>'400','response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK);
        }
    }

    /**
     * @api {post} Customer/resendOtp Resend Otp
     * @apiVersion 0.1.0
     * @apiName Resend Otp
     * @apiGroup Customer
     * @apiParam {int}   client_id   Client ID.
     *
     * @apiSuccessExample Success
     *   {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "result": 1,
     *          "otp": 824727
     *      }
     *  }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     */
    public function resendOtp_post() //Resend otp created by Taha 14-11-2018
    {
        $data=$this->post();
        $client_id=$data['client_id'];
        if($client_id!=NULL)
        {
            $data['otp']=mt_rand(100000,999999);
            $update['result']=$this->Api_model->updateData('clients',array('otp'=>$data['otp']),array('client_id'=>$client_id));
            $update['otp']=$data['otp'];
            $mobile=$this->Api_model->selectData('clients','mobile,name',array('client_id'=>$client_id));

            $otpmsg="Hello ".$mobile[0]->name.",\n\nYour OTP: ".$update['otp']."\n\nThanks,\nPlumb Tech";
            send_sms($mobile[0]->mobile,$otpmsg);

            if(count($update['result'])>0)
            {
                
                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $update)); 
            }
            else
            {
                $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
            }
        }
        else
        {
            $this->set_response(array('response_code'=>'400','response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK);
        }
    }

    /**
     * @api {post} Customer/changePassword Change Password Customer
     * @apiVersion 0.1.0
     * @apiName Change Password
     * @apiGroup Customer
     * @apiParam {id}     client_id   Client ID.
     * @apiParam {password}   old_password    Old Password
     * @apiParam {password}   new_password    New Password
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "success",
     *       "response_data": 1
     *   }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     *  @apiErrorExample Incorrect Current Password
     *  {
     *      "response_code": "400",
     *      "response_message": "Current Password Is Incorrect",
     *      "response_data": []
     *  }
     * @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     */
    public function changePassword_post() //Change Password created by Taha 14-11-2018
    {
        $data=$this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $client_id=$data['client_id'];
            $old_password=$data['old_password'];
            $new_password=$data['new_password'];
            if($client_id!=null && $old_password!=null && $new_password!=null)
            {
                $old_password=md5($old_password);
                $check=$this->Api_model->selectData('clients','client_id,woo_cus_id',array('password'=>$old_password,'client_id'=>$client_id));

                if(count($check)>0)
                {   
                    $customers[0] = woocomerceapi('customers/'.$check[0]->woo_cus_id);
                
                    if($customers[0])
                    {

                        $password=array('password'=>$new_password);

                        $updatewoo=woocomerceapiput('customers/'.$check[0]->woo_cus_id,$password);

                    }   

                    $new_password=md5($data['new_password']);
                    $update=$this->Api_model->updateData('clients',array('password'=>$new_password),array('client_id'=>$client_id));
                    if(count($update)>0)
                    {
                        $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $update)); 
                    }
                    else
                    {
                        $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                    }
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'Current Password Is Incorrect' , 'response_data' => array()));
                }
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        }
    }

    /**
     * @api {post} Customer/listServices List all Services
     * @apiVersion 0.1.0
     * @apiName Services List
     * @apiGroup Customer
     *
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": [
     *          {
     *              "cat_id": "1",
     *              "service_name": "Domestic",
     *              "service_type": "project",
     *              "service_desc": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.",
     *              "service_image": "http://localhost/vp-admin/public/uploads/services/working.jpg"
     *          },
     *          {
     *              "cat_id": "1",
     *              "service_name": "Commercial",
     *              "service_type": "project",
     *              "service_desc": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.",
     *              "service_image": "http://localhost/vp-admin/public/uploads/services/bigstock-Plumber-10442993.jpg"
     *          },
     *          {
     *              "cat_id": "1",
     *              "service_name": "Institutional",
     *              "service_type": "project",
     *              "service_desc": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.",
     *              "service_image": "http://localhost/vp-admin/public/uploads/services/working.jpg"
     *          }
     *      ]
     *   }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     *
     */
    public function listServices_post()  //Services List created by Taha 16-11-2018
    {
        $data=$this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {               
            $result=$this->Api_model->selectData('services','cat_id,service_name,service_type,service_desc,service_image',array('status'=>'Active'));

            for($i=0; $i<count($result); $i++)
            {
                    $result[$i]->service_image=base_url().'public/uploads/services/'.$result[$i]->service_image;
            }

            if(count($result)>0)
            {
                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
            }   
            else
            {
                $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
            }
        }
    }

    /**
     * @api {post} customer/applist Fetch Application list of Customer
     * @apiVersion 0.1.0
     * @apiName Customer Applications
     * @apiGroup Customer
     *
     * @apiParam {int} client_id Client id from login response.
     *
     * @apiSuccessExample Success
     *    {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": [
     *          {
     *              "app_id": "3",
     *              "app_type": "job",
     *              "cat_name": "Plumbing Repairs",
     *              "service_name": "Leak",
     *              "app_status": "Requested"
     *          },
     *          {
     *              "app_id": "4",
     *              "app_type": "job",
     *              "cat_name": "Plumbing Repairs",
     *              "service_name": "Leak",
     *              "app_status": "Requested"
     *          },
     *          {
     *              "app_id": "1",
     *              "app_type": "project",
     *              "cat_name": "Plumbing from Start to Finish",
     *              "service_name": "Industrial",
     *              "app_status": "Unassigned"
     *          },
     *          {
     *              "app_id": "2",
     *              "app_type": "job",
     *              "cat_name": "Plumbing Repairs",
     *              "service_name": "Leak",
     *              "app_status": "Requested"
     *          }
     *      ]
     *  }
     *
     *
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     * @apiErrorExample No Data Found
     *     {
     *          "response_code": 400,
     *          "response_message": "No data found",
     *          "response_data": []
     *     }
     */
    public function applist_post() //Customer's Application list
    {
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $data = $this->post();
            $client_id = $data['client_id'];
            if($client_id!=NULL)
            {
                $result=$this->Api_model->getCustomerApp($client_id);
                if(count($result)>0)
                {
                    $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                } 
                else
                {
                    $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);            
                }
            }
            else
            {
                 $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        }
    }

    /**
     * @api {post} customer/applistStatusWise Fetch Application list of Customer Status Wise
     * @apiVersion 0.1.0
     * @apiName Customer Applications Status Wise
     * @apiGroup Customer
     *
     *  @apiParam {int}  client_id   Client id from login response.
     *  @apiParam {string} app_status  Application status from client response. (Requested/Ongoing/Pending/Completed)
     *
     * @apiSuccessExample Success
     *    {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": [
     *          {
     *              "app_id": "267",
     *              "app_type": "job",
     *              "cat_name": "Plumbing Repairs",
     *              "service_name": "Unusual Smell",
     *              "app_status": "Requested",
     *              "createdat": "2019-01-30 06:52:14",
     *              "updatedat": "2019-01-30 13:22:15",
     *              "city_name": "Vadodara",
     *              "app_location": "Home"
     *          },
     *          {
     *              "app_id": "268",
     *              "app_type": "job",
     *              "cat_name": "Plumbing Repairs",
     *              "service_name": "Unusual Smell",
     *              "app_status": "Requested",
     *              "createdat": "2019-01-30 07:07:07",
     *              "updatedat": "2019-01-30 13:37:07",
     *              "city_name": "Vadodara",
     *              "app_location": "Home"
     *          }
     *      ]
     *  }
     *
     * @apiError Message Error Code 400.
     *
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     * @apiErrorExample No Data Found
     *     {
     *          "response_code": 400,
     *          "response_message": "No data found",
     *          "response_data": []
     *     }
     */
    public function applistStatusWise_post() //Customer's app list status wise
    {
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $data = $this->post();
            $client_id = $data['client_id'];
            $app_status = $data['app_status'];
            if($client_id!=NULL && $app_status!=NULL)
            {
                $result=$this->Api_model->getCustomerApp($client_id,$app_status);
                if(count($result)>0)
                {
                    $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                } 
                else
                {
                    $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);            
                }
            }
            else
            {
                 $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        }
    }

    // *
    //  * @api {post} customer/appdetails Fetch Application details of Customer
    //  * @apiVersion 0.1.0
    //  * @apiName Customer Application Details
    //  * @apiGroup Customer
    //  *
    //  * @apiParam {int} app_id       App id response.
    //  *
    //  * @apiSuccessExample Success
    //  *   {
    //  *     "response_code": 200,
    //  *     "response_message": "Success",
    //  *     "response_data": [
    //  *         {
    //  *             "app_date": "2019-01-02",
    //  *             "app_type": "job",
    //  *             "app_time": "11:50:00",
    //  *             "service_name": "Leak",
    //  *             "app_phone": "",
    //  *             "app_mobile": "9925738146",
    //  *             "app_email": "taha@hmmbiz.com",
    //  *             "app_desc": "Excepteur sint occaecat cupidatat",
    //  *             "app_id": "3",
    //  *             "app_status": "Requested",
    //  *             "status": "Active",
    //  *             "city_name": "Bengaluru"
    //  *         }
    //  *     ]
    //  * }
    //  *
    //  * @apiError Message Error Code 400.
    //  *
    //  * @apiErrorExample Parameter invalid
    //  *     {
    //  *          "response_code": 400,
    //  *          "response_message": "Parameter missing",
    //  *          "response_data": []
    //  *     }
    //  * @apiErrorExample No Data Found
    //  *     {
    //  *          "response_code": 400,
    //  *          "response_message": "No data found",
    //  *          "response_data": []
    //  *     }
     
    // public function appdetails_post() //Customer's app detail
    // {
    //     $data = $this->post();
    //     $app_id = $data['app_id'];
    //     if($app_id!=NULL)
    //     {
    //         $result=$this->Api_model->getCustomerAppdetails($app_id);
    //         if(count($result)>0)
    //         {
    //             $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
    //         } 
    //         else 
    //         {
    //             $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);            
    //         }
    //     }
    //     else
    //     {
    //         $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
    //     }
    // }  

    //Edited by Taha 21-12-2018
    /**
     * @api {post} customer/jobdetails Fetch Application details of Customer (Job)
     * @apiVersion 0.1.0
     * @apiName Customer Application Details (Job)
     * @apiGroup Customer
     *
     * @apiParam {int} app_id       App id response.
     *
     * @apiSuccessExample Success
     *   {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": {
     *          "job_details": [
     *              {
     *                  "app_id": "268",
     *                  "app_status": "Requested",
     *                  "app_type": "job",
     *                  "service_name": "Unusual Smell",
     *                  "cat_name": "Plumbing Repairs",
     *                  "app_date": "2018-12-14",
     *                  "app_time": "11:50:00",
     *                  "app_phone": "",
     *                  "app_mobile": "9925738146",
     *                  "app_email": "tahasethwala@yahoo.com",
     *                  "app_location": "Home",
     *                  "app_address": "A/4 Unit Hills",
     *                  "app_address1": "Near Union Park Society, Juhapura",
     *                  "city_name": "Vadodara",
     *                  "state_name": "Gujarat",
     *                  "country_name": "India",
     *                  "app_desc": "Emergency Service",
     *                  "status": "Active",
     *                  "internal_app_desc": "This is the test description edited again. again and again",
     *                  "end_date": "2018-12-14 04:50:42"
     *              }
     *          ],
     *          "dailworkitems-list": [
     *              {
     *                  "job_history_id": "235",
     *                  "current_status": "Ongoing",
     *                  "createdat": "2018-12-14 04:48:47"
     *              },
     *              {
     *                  "job_history_id": "237",
     *                  "current_status": "Completed",
     *                  "createdat": "2018-12-14 04:50:42"
     *              }
     *          ],
     *          "images": [
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/cropped-2054964220.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/cropped-1891585191.jpg"
     *          ],
     *          "app_images": [
     *              "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak3.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak3.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak3.jpg"
     *          ],
     *          "ratings_and_feedback": [
     *              {
     *                  "ratings": "4",
     *                  "feedback": "Good work",
     *                  "image": [
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/feedback/12.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/feedback/22.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/feedback/34.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/feedback/35.jpg"
     *                  ]
     *              }
     *          ],
     *          "invoice": [
     *              {
     *                  "app_id": "13",
     *                  "invoice_status": "Paid",
     *                  "invoice_id": "5",
     *                  "invoice_date": "2018-11-22",
     *                  "issue_date": "2018-11-22",
     *                  "total": "3245",
     *                  "discount": "0",
     *                  "paid_date": "2018-11-27"
     *              }
     *          ]
     *      }
     *  }
     * @apiError Message Error Code 400.
     *
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     * @apiErrorExample No Data Found
     *     {
     *          "response_code": 400,
     *          "response_message": "No data found",
     *          "response_data": []
     *     }
     */
    public function jobdetails_post() //Details of Application (Job)
    {
        error_reporting(0);
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $data = $this->post();
            if(isset($data['app_id']) && $data['app_id']!=NULL)
            {
                $result['job_details']=$this->Api_model->getCustomerAppdetails($data['app_id']);
                // print_r($result);exit();
                $where=array(
                        'job_id'=>$data['app_id'],
                        'job_type'=>'job'
                    );

                if($result['job_details'][0]->app_status=='Completed')
                {
                    $end_date=$this->Api_model->enddate($data['app_id'],'job');
                    $result['job_details'][0]->end_date=$end_date[0]->end_date;
                }
                else
                {
                    $result['job_details'][0]->end_date='';                
                }    

                $result['dailworkitems-list']=$this->Api_model->selectData('job_history','job_history_id,current_status,createdat,',$where);
                
                $image=$this->Api_model->jobPhotographs($data['app_id']);

                $result['images']=array();

                for($i=0; $i <count($image); $i++)
                {
                    $result['images'][$i]=base_url().'public/image_file/'.$image[$i]->image_name;
                }

                $app_images=$this->Api_model->selectData('appointment_images','image_id,image_name',array('app_id'=>$data['app_id']));

                for ($i=0; $i < count($app_images); $i++) 
                { 
                    $result['app_images'][$i]=base_url().'public/uploads/appointment/'.$app_images[$i]->image_name;
                }                

                $result['ratings_and_feedback']=array();
                $result['invoice']=array();

                if($result['job_details'][0]->app_status != 'Ongoing' && $result['job_details'][0]->app_status != 'Requested' && $result['job_details'][0]->app_status != 'Pending')
                {
                    $result['ratings_and_feedback']=$this->Api_model->selectData('ratings','ratings',array('job_id'=>$data['app_id'],'job_type'=>'job'));
                    $feedback=$this->Api_model->selectData('feedback','fed_id,fed_desc,fed_image',array('job_id'=>$data['app_id'],'fed_type'=>'job'));  

                    if(count($result['ratings_and_feedback']) > 0 && count($feedback) > 0)
                    {
                        $result['ratings_and_feedback'][0]->feedback= $feedback[0]->fed_desc;
                        $fed_images=$this->Api_model->selectData('feedback_images','image_id,image_name',array('fed_id'=>$feedback[0]->fed_id));

                        for ($i=0; $i < count($fed_images); $i++) 
                        { 
                            $result['ratings_and_feedback'][0]->image[$i]=base_url().'public/uploads/feedback/'.$fed_images[$i]->image_name;
                        } 
                        // $result['ratings_and_feedback'][0]->image = base_url().'image/uploads/feedback'.$feedback[0]->fed_image;
                    }
                    else
                    {
                        $result['ratings_and_feedback']=array();
                    }

                    $result['invoice']=$this->Api_model->getInvoiceDetails($data['app_id']);

                    // Edited to add paid_date parameter if invoice is paid successfully
                    if(count($result['invoice']) > 0)
                    {
                        if($result['invoice'][0]->invoice_status == 'Paid')
                        {
                            $paid=$this->Api_model->selectData('user_transaction','payment_date',array('app_id'=>$result['job_details'][0]->app_id,'status'=>'success'));
                            $result['invoice'][0]->paid_date=date('Y-m-d',strtotime($paid[0]->payment_date));
                            // print_r($result['invoice']);exit();

                        }
                        else
                        {
                            $result['invoice'][0]->paid_date=''; 
                        }
                    }    
                }    

                if(count($result['job_details']) > 0)
                {
                    $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                } 
                else 
                {
                    $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);            
                }
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            }
        }    
    }


    /**
     * @api {post} customer/projectdetails Fetch Application details of Customer (Project)
     * @apiVersion 0.1.0
     * @apiName Customer Application Details (Project)
     * @apiGroup Customer
     *
     * @apiParam {int} app_id       App id response.
     *
     * @apiSuccessExample Success
     * {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": {
     *          "project_details": [
     *              {
     *                  "app_id": "128",
     *                  "app_status": "Completed",
     *                  "app_type": "project",
     *                  "service_name": "Renovations",
     *                  "cat_name": "Plumbing Upgrades",
     *                  "app_date": "2018-11-12",
     *                  "app_time": "10:52:00",
     *                  "app_phone": "",
     *                  "app_mobile": "123456789",
     *                  "app_email": "ashish@gmail.com",
     *                  "app_location": "Office",
     *                  "app_address": "akhabarnagar",
     *                  "app_address1": "nava vadaj",
     *                  "city_name": "Eshkashem",
     *                  "state_name": "Badakhshan",
     *                  "country_name": "Afghanistan",
     *                  "app_desc": "I want a plumbing service for my upcoming building projects",
     *                  "status": "Active",
     *                  "internal_app_desc": "This is the test description edited again. again and again",
     *                  "end_date": "2018-12-12 12:57:04"
     *              }
     *          ],
     *          "dailworkitems-list": [
     *              {
     *                  "job_history_id": "134",
     *                  "current_status": "Ongoing",
     *                  "createdat": "2018-12-12 12:47:59",
     *                  "mile_job_id": "13",
     *                  "mile_job_name": "Ashish-Mil1-Job1",
     *                  "mile_id": "14",
     *                  "mile_name": "Ashish-Mile1"
     *              },
     *              {
     *                  "job_history_id": "135",
     *                  "current_status": "Completed",
     *                  "createdat": "2018-12-12 12:49:20",
     *                  "mile_job_id": "13",
     *                  "mile_job_name": "Ashish-Mil1-Job1",
     *                  "mile_id": "14",
     *                  "mile_name": "Ashish-Mile1"
     *              },
     *              {
     *                  "job_history_id": "136",
     *                  "current_status": "Ongoing",
     *                  "createdat": "2018-12-12 12:50:56",
     *                  "mile_job_id": "14",
     *                  "mile_job_name": "Ashish-Mile1-Job2",
     *                  "mile_id": "14",
     *                  "mile_name": "Ashish-Mile1"
     *              },
     *              {
     *                  "job_history_id": "137",
     *                  "current_status": "Completed",
     *                  "createdat": "2018-12-12 12:52:10",
     *                  "mile_job_id": "14",
     *                  "mile_job_name": "Ashish-Mile1-Job2",
     *                  "mile_id": "14",
     *                  "mile_name": "Ashish-Mile1"
     *              },
     *              {
     *                  "job_history_id": "138",
     *                  "current_status": "Ongoing",
     *                  "createdat": "2018-12-12 12:54:00",
     *                  "mile_job_id": "15",
     *                  "mile_job_name": "Ashish-Mile2-Job1",
     *                  "mile_id": "15",
     *                  "mile_name": "Ashish-Mile2"
     *              },
     *              {
     *                  "job_history_id": "139",
     *                  "current_status": "Completed",
     *                  "createdat": "2018-12-12 12:55:05",
     *                  "mile_job_id": "15",
     *                  "mile_job_name": "Ashish-Mile2-Job1",
     *                  "mile_id": "15",
     *                  "mile_name": "Ashish-Mile2"
     *              },
     *              {
     *                  "job_history_id": "140",
     *                  "current_status": "Ongoing",
     *                  "createdat": "2018-12-12 12:56:02",
     *                  "mile_job_id": "16",
     *                  "mile_job_name": "Ashish-Mile2-Job2",
     *                  "mile_id": "15",
     *                  "mile_name": "Ashish-Mile2"
     *              },
     *              {
     *                  "job_history_id": "141",
     *                  "current_status": "Completed",
     *                  "createdat": "2018-12-12 12:57:04",
     *                  "mile_job_id": "16",
     *                  "mile_job_name": "Ashish-Mile2-Job2",
     *                  "mile_id": "15",
     *                  "mile_name": "Ashish-Mile2"
     *              }
     *          ],
     *          "images": [
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/plumbing-upgrades25.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/images-job28.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/plumbing-upgrades26.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/images2.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/images-latest1.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/images3.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/images-latest2.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/images-12332.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/cropped2538275666999233100.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/image_file/cropped751368039815127382.jpg"
     *          ],
     *          "app_images": [
     *              "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak1.jpg",
     *              "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak2.jpg"
     *          ],
     *          "ratings_and_feedback": [
     *              {
     *                  "ratings": "5",
     *                  "feedback": "Good work",
     *                  "image": [
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/feedback/11.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/feedback/21.jpg"
     *                  ]
     *              }
     *          ],
     *          "invoice": [
     *              {
     *                  "app_id": "128",
     *                  "invoice_status": "Approved",
     *                  "invoice_id": "4",
     *                  "invoice_date": "2018-11-22",
     *                  "issue_date": "2018-11-22",
     *                  "total": "4790",
     *                  "discount": "0"
     *              }
     *          ]
     *      }
     *  }
     * @apiError Message Error Code 400.
     *
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     * @apiErrorExample No Data Found
     *     {
     *          "response_code": 400,
     *          "response_message": "No data found",
     *          "response_data": []
     *     }
     */
    public function projectdetails_post() //Details of Application (Project)
    {
        error_reporting(0);
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $data = $this->post();
            if(isset($data['app_id']) && $data['app_id']!=NULL)
            {
                $result['project_details']=$this->Api_model->getCustomerAppdetails($data['app_id']);

                $result['dailworkitems-list']=$this->Api_model->projectWorkItems($data['app_id']);

                $image=$this->Api_model->projectPhotographs($data['app_id']);

                $result['images']=array();

                for($i=0; $i < count($image); $i++)
                {
                    $result['images'][$i]=base_url().'public/image_file/'.$image[$i]->image_name;
                }

                $app_images=$this->Api_model->selectData('appointment_images','image_id,image_name',array('app_id'=>$data['app_id']));

                for ($i=0; $i < count($app_images); $i++) 
                { 
                    $result['app_images'][$i]=base_url().'public/uploads/appointment/'.$app_images[$i]->image_name;
                }                


                if($result['project_details'][0]->app_status=='Completed')
                {
                    $end_date=$this->Api_model->projectenddate($data['app_id'],'project');
                    $result['project_details'][0]->end_date=$end_date[0]->end_date;
                }
                else
                {
                    $result['project_details'][0]->end_date='';
                }  

                $result['ratings_and_feedback']=array();
                $result['invoice']=array();

                if($result['project_details'][0]->app_status != 'Ongoing' && $result['project_details'][0]->app_status != 'Requested' && $result['project_details'][0]->app_status != 'Pending')
                {
                    $result['ratings_and_feedback']=$this->Api_model->selectData('ratings','ratings',array('job_id'=>$data['app_id'],'job_type'=>'project'));

                    $feedback=$this->Api_model->selectData('feedback','fed_id,fed_desc,fed_image',array('job_id'=>$data['app_id'],'fed_type'=>'project'));  

                    if(count($result['ratings_and_feedback']) > 0 && count($feedback) > 0)
                    {

                        $result['ratings_and_feedback'][0]->feedback= $feedback[0]->fed_desc;
                        $fed_images=$this->Api_model->selectData('feedback_images','image_id,image_name',array('fed_id'=>$feedback[0]->fed_id));

                        for ($i=0; $i < count($fed_images); $i++) 
                        { 
                            $result['ratings_and_feedback'][0]->image[$i]=base_url().'public/uploads/feedback/'.$fed_images[$i]->image_name;
                        } 
                    }
                    else
                    {
                        $result['ratings_and_feedback']=array();
                    }

                    $result['invoice']=$this->Api_model->getInvoiceDetails($data['app_id']);


                    // Edited to add paid_date parameter if invoice is paid successfully
                    if(count($result['invoice']) > 0)
                    {
                        if($result['invoice'][0]->invoice_status == 'Paid')
                        {
                            $paid=$this->Api_model->selectData('user_transaction','payment_date',array('app_id'=>$result['project_details'][0]->app_id,'status'=>'success'));
                            $result['invoice'][0]->paid_date=date('Y-m-d',strtotime($paid[0]->payment_date));
                            // print_r($result['invoice']);exit();

                        }
                        else
                        {
                            $result['invoice'][0]->paid_date=''; 
                        }
                    }
                }    

                if(count($result['project_details']) > 0)
                {
                    $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                } 
                else 
                {
                    $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);            
                }
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            }
        }    
    }
    //=========================   

    // /**
    //  * @api {post} Customer/dailyWorkItems Daily Work Items List
    //  * @apiVersion 0.1.0
    //  * @apiName Daily Work Items List
    //  * @apiGroup Customer
    //  * @apiParam {int}     app_id   Application Id.
    //  * @apiSuccessExample Success
    //  *  {
    //  *      "response_code": "200",
    //  *      "response_message": "success",
    //  *      "response_data": [
    //  *          {
    //  *              "job_history_id": "2",
    //  *              "current_status": "Ongoing",
    //  *              "createdat": "2018-11-12 12:34:33"
    //  *          },
    //  *          {
    //  *              "job_history_id": "6",
    //  *              "current_status": "Completed",
    //  *              "createdat": "2018-11-12 12:54:52"
    //  *          }
    //  *      ]
    //  *  }
    //  * @apiErrorExample No Data Found
    //  *       {
    //  *      "response_code": "400",
    //  *      "response_message": "No Data Found",
    //  *      "response_data": []
    //  *   }
    //  * @apiErrorExample Parameter missing
    //  *     {
    //  *          "response_code": "400",
    //  *          "response_message": "Parameter missing",
    //  *          "response_data": []
    //  *     }
    //  */
    // public function dailyWorkItems_post() //Daily Work Items List created by Taha 15-11-2018
    // {
    //     $data=$this->post();
    //     $authValidation = verifyUserAuthToken($this->head('AuthKey'));
    //     if($authValidation)
    //     {
    //         $job_id=$data['app_id'];

    //         if($job_id!=null)
    //         {
    //             $where=array(
    //                 'job_id'=>$job_id
    //             );
    //             $result=$this->Api_model->selectData('job_history','job_history_id,current_status,createdat,',$where);
    //             if(count($result)>0)
    //             {   
    //                 $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
    //             }
    //             else
    //             {
    //                 $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                    
    //             }
    //         }
    //         else
    //         {
    //             $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
    //         }
    //     }
    // }

    /**
     * @api {post} Customer/dailyWorkItemsDetails Daily Work Item Details
     * @apiVersion 0.1.0
     * @apiName Daily Work Item Details
     * @apiGroup Customer
     * @apiParam {int}     app_id           Application Id.
     * @apiParam {int}     job_history_id   Job History Id.
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "history": [
     *              {
     *                  "job_history_id": "2",
     *                  "current_status": "Pending",
     *                  "comment": "Work Completed",
     *                  "time": "01:00:00",
     *                  "createdat": "2018-11-28 01:15:02"
     *              }
     *          ],
     *          "images": [
     *              "http://localhost/vp-admin/public/image_file/cmd_victree2.PNG",
     *              "http://localhost/vp-admin/public/image_file/cmd_victree3.PNG"
     *          ],
     *          "material_used": [
     *              {
     *                  "product_id": "1",
     *                  "product_name": "Pipe",
     *                  "product_quantity": "3"
     *              },
     *              {
     *                  "product_id": "1",
     *                  "product_name": "Pipe",
     *                  "product_quantity": "3"
     *              }
     *          ],
     *          "getPlumber": [
     *              {
     *                  "user_id": "10",
     *                  "name": "TECH1"
     *              }
     *          ]
     *      }
     *  }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     * @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     **/
    public function dailyWorkItemsDetails_post()    //Daily Work Item Details created by Taha 16-11-2018 //Edited on 28-11-2018
    {
        $data=$this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $histrory_id =$data['job_history_id'];
            $app_id      =$data['app_id'];
            if($histrory_id!=null && $app_id)
            {
                $where=array(
                    'job_history_id'=>$histrory_id
                );
                $result['history']=$this->Api_model->selectData('job_history','job_history_id,current_status,comment,time,createdat,',$where);

                // print_r($result['history'][0]->job_history_id);exit();

                
                $image=$this->Api_model->selectData('job_history_image','image_id,image_name',array('job_history_id'=>$result['history'][0]->job_history_id));

                for($i=0; $i <count($image); $i++)
                {
                    $result['images'][$i]=base_url().'public/image_file/'.$image[$i]->image_name;
                }


                $result['material_used']=$this->Api_model->selectData('material_used','product_id,product_name,product_quantity',array('job_history_id'=>$result['history'][0]->job_history_id));

                $result['getPlumber']=$this->Api_model->getPlumberJob($result['history'][0]->job_history_id);

                if(count($result)>0)
                {   
                    $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                    
                }
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        }
    }

    /**
     * @api {post} Customer/feedback Customer Feedback
     * @apiVersion 0.1.0
     * @apiName Customer Feedback
     * @apiGroup Customer
     * @apiParam {int}          app_id       Application Id.
     * @apiParam {int}          client_id    Client Id.
     * @apiParam {int}          app_type     (job/project).
     * @apiParam {int}          ratings      Ratings (Number ex:1,2,3,4,5)
     * @apiParam {int}          feedback     Feedback.
     * @apiParam {file}         image[]      (Array) image[0],image[1]....
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "Thankyou so much for valuable feedback.",
     *      "response_data": {
     *          "feedback": 11,
     *          "feedback_images": [
     *              5,
     *              6,
     *              7,
     *              8
     *          ],
     *          "ratings": 7
     *      }
     *  }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     * @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     */
    public function feedback_post() //Customer Feedback created by Taha 17-11-2018 edited: 21-12-2018
    {
        $data=$this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            if(isset($data['app_id']) && isset($data['client_id']) && isset($data['app_type']) && isset($data['ratings']) && isset($data['feedback']) && $data['app_id']!=Null && $data['app_type']!=NULL && $data['ratings']!=NULLL && $data['feedback']!=NULL && $data['client_id']!=NULL)
            {

                $data['createdate']=date('Y-m-d h:i:s');

                $insert_feedback=array( 'fed_desc'=>$data['feedback'],
                                        'user_id'=>$data['client_id'],
                                        'job_id'=>$data['app_id'],
                                        'fed_type'=>$data['app_type'],
                                        'createdate'=>$data['createdate']
                                );

                $result['feedback']=$this->Api_model->insertData('feedback',$insert_feedback);

                // echo "<pre>";print_r($result['feedback']);exit();

                 // Image Upload
                $count=count($_FILES['image']['name']); 

                $config['upload_path'] = './public/uploads/feedback';
                $config['allowed_types'] = '*'; 
                $this->load->library('upload', $config);                    

                for ($i=0; $i <$count ; $i++) 
                { 
                    $_FILES['img']=array(
                        'name'=>$_FILES['image']['name'][$i],
                        'type'=>$_FILES['image']['type'][$i],
                        'tmp_name'=>$_FILES['image']['tmp_name'][$i],
                        'error'=>$_FILES['image']['error'][$i],
                        'size'=>$_FILES['image']['size'][$i]
                    );

                    if(isset($_FILES['img']) && $_FILES['img']!='') 
                    {                       
                        if ( ! $this->upload->do_upload('img'))
                        {
                            $error = array('error' => $this->upload->display_errors());
                        }
                        else
                        {
                            $upload=$this->upload->data();
                            $img_name[$i]=$upload['file_name'];
                        }                           
                    }
                }

                $c=count($img_name);

                for ($i=0; $i < $c ; $i++) 
                { 
                    $result['feedback_images'][$i]=$this->Api_model->insertData('feedback_images',array('image_name'=>$img_name[$i],'fed_id'=>$result['feedback'],'createdat'=>date('Y-m-d h:i:s')));
                }

                 // echo "<pre>";print_r($result['feedback_images']);exit();


                $insert_ratings=array(  'job_id'=>$data['app_id'],
                                        'job_type'=>$data['app_type'],
                                        'ratings'=>$data['ratings']
                            );
                $result['ratings']=$this->Api_model->insertData('ratings',$insert_ratings);



                if(count($result)>0)
                {   
                    $this->response(array('response_code'=>'200', 'response_message' => 'Thankyou so much for valuable feedback.' , 'response_data' => $result)); 
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                    
                }
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        }
    }

    /**
     * @api {post} Customer/ratingJob Job Ratings By Customer
     * @apiVersion 0.1.0
     * @apiName Job Ratings By Customer
     * @apiGroup Customer
     * @apiParam {int}      job_id      Application Id.
     * @apiParam {int}      ratings     Ratings.
     * @apiParam {int}      job_type    job/project.
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "success",
     *       "response_data": 1
     *   }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     * @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     */
    public function ratingJob_post() //Job Ratings created by Taha 15-11-2018 //Edited on 30-11-2018
    {
        $data=$this->post();
        // print_r($data);exit();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $job_id=$data['job_id'];
            $job_type=$data['job_type'];
            $ratings=$data['ratings'];

            if($job_id!=null && $ratings!=null && $job_type!=null)
            {
                $insert=$this->Api_model->insertData('ratings',$data);
                if(count($insert)>0)
                {   
                    $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $insert)); 
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                    
                }
            }

            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        }
    }

    /**
     * @api {post} Customer/raiseIssue Raise Issue By Customer
     * @apiVersion 0.1.0
     * @apiName Raise Issue
     * @apiGroup Customer
     * @apiParam {int}        app_id        Application Id.
     * @apiParam {file}       issue_image[] (Array) issue_image[0],issue_image[1]....
     * @apiParam {string}     issue         Description of issue
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "Issue Raised Successfully, we will get back to you.",
     *      "response_data": {
     *          "insert": 5,
     *          "issue_raised_images": [
     *              5,
     *              6,
     *              7,
     *              8
     *          ]
     *      }
     *  }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     * @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     */
    public function raiseIssue_post() //Raise Issue created by Taha 16-11-2018
    {
        $data=$this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $app_id=$data['app_id'];
            $issue=$data['issue'];
            if($app_id!=null && $issue!=null)
            {

                $data['createdat']=date('Y-m-d h:i:s');

                $result['insert']=$this->Api_model->insertData('issue_raised',$data);


                 // Image Upload
                $count=count($_FILES['issue_image']['name']); 

                $config['upload_path'] = './public/uploads/issue-raised';
                $config['allowed_types'] = '*'; 
                $this->load->library('upload', $config);                    

                for ($i=0; $i <$count ; $i++) 
                { 
                    $_FILES['img']=array(
                        'name'=>$_FILES['issue_image']['name'][$i],
                        'type'=>$_FILES['issue_image']['type'][$i],
                        'tmp_name'=>$_FILES['issue_image']['tmp_name'][$i],
                        'error'=>$_FILES['issue_image']['error'][$i],
                        'size'=>$_FILES['issue_image']['size'][$i]
                    );

                    if(isset($_FILES['img']) && $_FILES['img']!='') 
                    {                       
                        if ( ! $this->upload->do_upload('img'))
                        {
                            $error = array('error' => $this->upload->display_errors());
                        }
                        else
                        {
                            $upload=$this->upload->data();
                            $img_name[$i]=$upload['file_name'];
                        }                           
                    }
                }

                $c=count($img_name);

                for ($i=0; $i < $c ; $i++) 
                { 
                    $result['issue_raised_images'][$i]=$this->Api_model->insertData('issue_images',array('image_name'=>$img_name[$i],'issue_id'=>$result['insert'],'createdat'=>date('Y-m-d h:i:s')));
                }

                if(count($result)>0)
                {   
                    $this->response(array('response_code'=>'200', 'response_message' => 'Issue Raised Successfully, we will get back to you.' , 'response_data' => $result)); 
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                    
                }
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        }
    }
    
    /**
     * @api {post} Customer/getCategory Get Category For Form
     * @apiVersion 0.1.0
     * @apiName Get Category
     * @apiGroup Customer
     *
     * @apiSuccessExample Success
     * {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": [
     *          {
     *              "cat_id": "1",
     *              "cat_name": "Plumbing from Start to Finish"
     *          },
     *          {
     *              "cat_id": "2",
     *              "cat_name": "Plumbing Repairs"
     *          },
     *          {
     *              "cat_id": "3",
     *              "cat_name": "Plumbing Upgrades"
     *          },
     *          {
     *              "cat_id": "4",
     *              "cat_name": "Other Services"
     *          }
     *      ]
     *  }
     *
     *  @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     *
     *
     */
    public function getCategory_post() //Get Category created by Taha 16-11-2018
    {
        $data=$this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {               
            $result=$this->Api_model->selectData('category','cat_id,cat_name',array('status'=>'Active'));
            if(count($result)>0)
            {
                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
            }   
            else
            {
                $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
            }
        }
    }

    /**
     * @api {post} Customer/getCategoryServices Get Services For Particular Category
     * @apiVersion 0.1.0
     * @apiName Get Category Services
     * @apiGroup Customer
     * @apiParam {int}     cat_id           Category ID
     * @apiSuccessExample Success
     * {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": [
     *          {
     *              "service_id": "1",
     *              "service_name": "Domestic"
     *          },
     *          {
     *              "service_id": "2",
     *              "service_name": "Commercial"
     *          },
     *          {
     *              "service_id": "3",
     *              "service_name": "Institutional"
     *          },
     *          {
     *              "service_id": "4",
     *              "service_name": "Industrial"
     *          }
     *      ]
     *  }
     *
     * @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     *  @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     *
     *
     */
    public function getCategoryServices_post()
    {
        $data=$this->post();
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {             
            if($data['cat_id']!=NULL)
            {
                $result=$this->Api_model->selectData('services','service_id,service_name',array('status'=>'Active','cat_id'=>$data['cat_id']));
                if(count($result)>0)
                {
                    $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
                }   
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                }
            }  
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
            
        }
    }

    /**
     * @api {post} Customer/getCountries Get Country List
     * @apiVersion 0.1.0
     * @apiName Get Country
     * @apiGroup Customer
     *
     * @apiSuccessExample Success
     * {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": [
     *          {
     *              "country_id": "1",
     *              "country_name": "Afghanistan"
     *          },
     *          {
     *              "country_id": "2",
     *              "country_name": "Albania"
     *          },
     *          {
     *              "country_id": "3",
     *              "country_name": "Algeria"
     *          },
     *          {
     *              "country_id": "4",
     *              "country_name": "American Samoa"
     *          }
     *      ]
     *  }
     *
     *  @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     *
     *
     *
     */
    public function getCountries_post() //Get Countries created by Taha 16-11-2018
    {
        $data=$this->post();
                    
        $result=$this->Api_model->selectData('countries','country_id,country_name');
        if(count($result)>0)
        {
            $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
        }   
        else
        {
            $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
        }
    }

    /**
     * @api {post} Customer/getStates Get States Of Country
     * @apiVersion 0.1.0
     * @apiName Get States Of Country
     * @apiGroup Customer
     * @apiParam {int}  country_id  Country Id from user response
     * @apiSuccessExample Success
     * {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": [
     *          {
     *              "state_id": "1",
     *              "state_name": "Andaman and Nicobar Islands"
     *          },
     *          {
     *              "state_id": "2",
     *              "state_name": "Andhra Pradesh"
     *          },
     *          {
     *              "state_id": "3",
     *              "state_name": "Arunachal Pradesh"
     *          },
     *          {
     *              "state_id": "4",
     *              "state_name": "Assam"
     *          },
     *          {
     *              "state_id": "5",
     *              "state_name": "Bihar"
     *          }
     *      ]
     *  }
     *
     *  @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     *
     *  @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     *
     *
     */
    public function getStates_post() //Get States created by Taha 16-11-2018
    {
        $data=$this->post(); 
        if($data['country_id'])   
        {
            $result=$this->Api_model->selectData('states','state_id,state_name',array('country_id'=>$data['country_id']));
            if(count($result)>0)
            {
                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
            }   
            else
            {
                $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
            }
        }   
        else
        {
            $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK);
        }              
    }

    /**
     * @api {post} Customer/getCities Get Cites Of States
     * @apiVersion 0.1.0
     * @apiName Get Cites Of States
     * @apiGroup Customer
     * @apiParam {int}  state_id  State Id from user response
     * @apiSuccessExample Success
     * {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": [
     *          {
     *              "city_id": "779",
     *              "city_name": "Abrama"
     *          },
     *          {
     *              "city_id": "780",
     *              "city_name": "Adalaj"
     *          },
     *          {
     *              "city_id": "781",
     *              "city_name": "Adityana"
     *          },
     *          {
     *              "city_id": "782",
     *              "city_name": "Advana"
     *          },
     *          {
     *              "city_id": "783",
     *              "city_name": "Ahmedabad"
     *          },
     *      ]
     *  }
     *
     *  @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     *
     *  @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     *
     *
     */
    public function getCities_post() //Get Cities created by Taha 16-11-2018
    {
        $data=$this->post();
        if($data['state_id'])   
        {
            $result=$this->Api_model->selectData('cities','city_id,city_name',array('state_id'=>$data['state_id']));
            if(count($result)>0)
            {
                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
            }   
            else
            {
                $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
            }
        }   
        else
        {
            $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK);
        }              
    }

    /**
     * @api {post} Customer/getPincode Get Pincode For Form 
     * @apiVersion 0.1.0
     * @apiName Get Pincode
     * @apiGroup Customer
     *
     * @apiSuccessExample Success
     * {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": [
     *          {
     *              "pin_id": "1",
     *              "pincode": "560004"
     *          },
     *          {
     *              "pin_id": "2",
     *              "pincode": "560034"
     *          },
     *          {
     *              "pin_id": "3",
     *              "pincode": "560007"
     *          },
     *          {
     *              "pin_id": "4",
     *              "pincode": "560092"
     *          }
     *      ]
     *  }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     *
     */
    public function getPincode_post() //Get Pincode for form validation created by Taha 16-11-2018
    {
        $data=$this->post();           
        $result=$this->Api_model->selectData('pincode','pin_id,pincode');
        if(count($result)>0)
        {
            $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
        }   
        else
        {
            $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
        }
    }

    /**
     * @api {post} Customer/fetchAd Fetch Advertisement
     * @apiVersion 0.1.0
     * @apiName Fetch Advertisement
     * @apiGroup Customer
     *  {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": [
     *          {
     *              "ad_id": "1",
     *              "ad_heading": "Ad 1",
     *              "ad_desc": "abc",
     *              "ad_image": "http://localhost/vp-admin/public/advertisement/images-job9.jpg"
     *          },
     *          {
     *              "ad_id": "4",
     *              "ad_heading": "Ad 4",
     *              "ad_desc": "efg",
     *              "ad_image": "http://localhost/vp-admin/public/advertisement/images-job12.jpg"
     *          }
     *      ]
     *  }
     * @apiErrorExample No Data Found
     *   {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     */
    public function fetchAd_post()   //Fetch advertisements created by TAHA 21-11-2018
    {     
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $today=date('Y-m-d');
            $result=$this->Api_model->selectData('advertisement','ad_id,ad_heading,ad_desc,ad_image',array('status'=>'Active','ad_expiry_date >'=>$today));
            for($i=0; $i<count($result); $i++)
            {
                $result[$i]->ad_image=base_url().'public/uploads/advertisement/'.$result[$i]->ad_image;
            }

            if(count($result)>0)
            {
                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
            }   
            else
            {
                $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
            } 
        }          
    } 

    /**
     * @api {post} Customer/fetchBanner Fetch Banners
     * @apiVersion 0.1.0
     * @apiName Fetch Banners
     * @apiGroup Customer
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": [
     *          {
     *              "banner_id": "1",
     *              "banner_image": "http://resourceserver.in/demo/vp-admin/public/uploads/banner/images.jpg"
     *          },
     *          {
     *              "banner_id": "2",
     *              "banner_image": "http://resourceserver.in/demo/vp-admin/public/uploads/banner/plumbing-upgrades.jpg"
     *          }
     *      ]
     *  }
     * @apiErrorExample No Data Found
     *   {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     */
    public function fetchBanner_post()   //Fetch Banner created by TAHA 12-12-2018
    {     
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            // $today=date('Y-m-d');
            $result=$this->Api_model->selectData('banner-images','banner_id,banner_image',array('status'=>'Active'));
            for($i=0; $i<count($result); $i++)
            {
                $result[$i]->banner_image=base_url().'public/uploads/banner/'.$result[$i]->banner_image;
            }

            if(count($result)>0)
            {
                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
            }   
            else
            {
                $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
            } 
        }          
    } 

    /**
     * @api {post} Customer/bookAppointment Book Appointment
     * @apiVersion 0.1.0
     * @apiName  Book Appointment
     * @apiGroup Customer
     * @apiParam {int}       client_id      Client Id from Mobile APP session
     * @apiParam {int}       cat_id         Category Id from user response
     * @apiParam {date}      app_date       Application Date from user response
     * @apiParam {time}      app_time       Application Time from user response
     * @apiParam {int}       app_service    Service id from user response
     * @apiParam {string}    firstname      First name from user response
     * @apiParam {string}    lastname       Last name from user response
     * @apiParam {string}    app_location   Location (ex. Home,Work etc)
     * @apiParam {string}    app_address    Address Line 1 from user response
     * @apiParam {string}    app_country    Country from user response
     * @apiParam {string}    app_state      State from user response
     * @apiParam {string}    app_city       City from user response
     * @apiParam {int}       app_mobile     Mobile Number from user response
     * @apiParam {string}    app_email      Email Address from user response
     * @apiParam {string}    app_desc       Application Desc from user response
     * @apiParam {file}      app_file[]     (Array) app_file[0],app_file[1]....
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "New service request was successfull",
     *      "response_data": {
     *          "appointment_images": [
     *              8,
     *              9,
     *              10
     *          ],
     *          "app_id": 61
     *      }
     *  }
     *
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     *
     *  @apiErrorExample Parameter missing
     *   {
     *      "response_code": 400,
     *      "response_message": "Parameter missing",
     *      "response_data": []
     *  }
     */
    public function bookAppointment_post()          //Book Appointment created by Taha 16-11-2018 //Mail send completed 21-11-2018 //SMS 24-11-2018
    {
        // echo "jdf";
        $this->load->model('Applications_model');
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $post = $this->input->post();
            // print_r($post);exit();
            if($post['client_id']!=NULL && $post['cat_id']!=NULL  && $post['app_service']!=NULL && $post['firstname']!=NULL && $post['app_address']!=NULL && $post['app_country']!=NULL && $post['app_state']!=NULL && $post['app_city']!=NULL && $post['app_mobile']!=NULL && $post['app_email']!=NULL && $post['app_desc']!=NULL)
            {
                $getids=getStateCountry($post['app_city']);
                // print_r($getids);exit();

                $post['app_country']=$getids['country_id'];
                $post['app_state']=$getids['state_id'];
                $post['app_city']=$getids['city_id'];


                if($post['cat_id'] == 5)
                {

                    if(isset($post['lastname']))
                    {
                        $name=$post['firstname'].' '.$post['lastname'];
                    } 
                    else
                    {
                        $name=$post['firstname'];
                    }

                    $data=array(
                        'name'=>$name,
                        'mobile'=>$post['app_mobile'],
                        'email'=>$post['app_email'],
                        'description'=>$post['app_desc'],
                        'location'=>$post['app_location'],
                        'address'=>$post['app_address'],
                        'country'=>$post['app_country'],
                        'state'=>$post['app_state'],
                        'city'=>$post['app_city'],
                        'createdat'=>date('Y-m-d')
                    );
                    $insert=$this->Applications_model->insertData('enquiry',$data);


                    // Image Upload
                    $count=count($_FILES['app_file']['name']); 

                    $config['upload_path'] = './public/uploads/enquiry';
                    $config['allowed_types'] = '*'; 
                    $this->load->library('upload', $config);                    

                    for ($i=0; $i <$count ; $i++) 
                    { 
                        $_FILES['img']=array(
                            'name'=>$_FILES['app_file']['name'][$i],
                            'type'=>$_FILES['app_file']['type'][$i],
                            'tmp_name'=>$_FILES['app_file']['tmp_name'][$i],
                            'error'=>$_FILES['app_file']['error'][$i],
                            'size'=>$_FILES['app_file']['size'][$i]
                        );

                        if(isset($_FILES['img']) && $_FILES['img']!='') 
                        {                       
                            if ( ! $this->upload->do_upload('img'))
                            {
                                $error = array('error' => $this->upload->display_errors());
                            }
                            else
                            {
                                $upload=$this->upload->data();
                                $img_name[$i]=$upload['file_name'];
                            }                           
                        }
                    }

                    $c=count($img_name);

                    for ($i=0; $i < $c ; $i++) 
                    { 
                        $result['enquiry_images'][$i]=$this->Api_model->insertData('enquiry_images',array('image_name'=>$img_name[$i],'enquiry_id'=>$insert,'createdat'=>date('Y-m-d h:i:s')));
                    }
                    // Image Upload

                    if($insert)
                    {
                        $notification=array('notify_type'=>'Enquiry',
                                        'app_id'=>$insert,
                                        'notify_link'=>base_url().'enquiry/view/'.$insert,
                                        'createdat'=>date('Y-m-d')
                                    );
                        $notify=$this->Applications_model->insertData('notifications',$notification);   

                        $result['enuiry_id']=$insert;

                        $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result));
                    }
                    else
                    {
                        $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                    }
                }
                else
                {
                    if(isset($post['lastname']))
                    {
                        $post['app_name']=$post['firstname'].' '.$post['lastname'];
                        unset($post['firstname']);
                        unset($post['lastname']);
                    } 
                    else
                    {
                        $post['app_name']=$post['firstname'];
                        unset($post['firstname']);
                    }

                    $post['app_date']=date('Y-m-d',strtotime($post['app_date']));
                    $post['createdat']=date('Y-m-d h:i:s');

                    $type=$this->Applications_model->selectData('services','service_id',array('service_id'=>$post['app_service'],'service_type'=>'job'));

                    // *******************Application Type = Project********************
                    if(count($type)==0)
                    {
                        $insert_id=$this->Applications_model->insertData('job_appointment',$post);
                        if($insert_id)
                        {

                            // Image Upload
                            $count=count($_FILES['app_file']['name']); 

                            $config['upload_path'] = './public/uploads/appointment';
                            $config['allowed_types'] = '*'; 
                            $this->load->library('upload', $config);                    

                            for ($i=0; $i <$count ; $i++) 
                            { 
                                $_FILES['img']=array(
                                    'name'=>$_FILES['app_file']['name'][$i],
                                    'type'=>$_FILES['app_file']['type'][$i],
                                    'tmp_name'=>$_FILES['app_file']['tmp_name'][$i],
                                    'error'=>$_FILES['app_file']['error'][$i],
                                    'size'=>$_FILES['app_file']['size'][$i]
                                );

                                if(isset($_FILES['img']) && $_FILES['img']!='') 
                                {                       
                                    if ( ! $this->upload->do_upload('img'))
                                    {
                                        $error = array('error' => $this->upload->display_errors());
                                    }
                                    else
                                    {
                                        $upload=$this->upload->data();
                                        $img_name[$i]=$upload['file_name'];
                                    }                           
                                }
                            }

                            $c=count($img_name);

                            for ($i=0; $i < $c ; $i++) 
                            { 
                                $result['appointment_images'][$i]=$this->Api_model->insertData('appointment_images',array('image_name'=>$img_name[$i],'app_id'=>$insert_id,'createdat'=>date('Y-m-d h:i:s')));
                            }
                            // Image Upload


                            //*********Mail Sent to customer about booking details************************
                            $bookingDetails['app']=$this->Email_model->fetchProjectDetails($insert_id);
                            $content=$this->load->view('email/projectAppointment.html',$bookingDetails,true);
                            $mailToCustomer=sendEmail($bookingDetails['app'][0]->app_email,'Booking Confirmation',$content);
                            //***************************************************************************

                            // Customer
                            $messageCus="Hello ".$bookingDetails['app'][0]->app_name.",\n\nYour Appointment has been scheduled for \nDate:".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                            $numberCus=$bookingDetails['app'][0]->app_mobile;
                            $smsCus=send_sms($numberCus,$messageCus);

                            $notification=array('notify_type'=>'Project',
                                            'app_id'=>$insert_id,
                                            'notify_link'=>base_url().'project/view/'.$insert_id,
                                            'createdat'=>date('Y-m-d')
                                        );
                            $notify=$this->Applications_model->insertData('notifications',$notification);

                            $result['app_id']=$insert_id;
                            $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result));
                        }
                        else
                        {
                            $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                        }   
                    }
                    // ******************************************************************
                    // *******************AUTOMATED SUPERVISOR AND TECHNICIAN ASSIGN IF TYPE = JOB********************
                    else
                    {
                        $post['app_status']='Requested';
                        $insert_id=$this->Applications_model->insertData('job_appointment',$post);

                        // Image Upload
                        $count=count($_FILES['app_file']['name']); 

                        $config['upload_path'] = './public/uploads/appointment';
                        $config['allowed_types'] = '*'; 
                        $this->load->library('upload', $config);                    

                        for ($i=0; $i <$count ; $i++) 
                        { 
                            $_FILES['img']=array(
                                'name'=>$_FILES['app_file']['name'][$i],
                                'type'=>$_FILES['app_file']['type'][$i],
                                'tmp_name'=>$_FILES['app_file']['tmp_name'][$i],
                                'error'=>$_FILES['app_file']['error'][$i],
                                'size'=>$_FILES['app_file']['size'][$i]
                            );

                            if(isset($_FILES['img']) && $_FILES['img']!='') 
                            {                       
                                if ( ! $this->upload->do_upload('img'))
                                {
                                    $error = array('error' => $this->upload->display_errors());
                                }
                                else
                                {
                                    $upload=$this->upload->data();
                                    $img_name[$i]=$upload['file_name'];
                                }                           
                            }
                        }

                        $c=count($img_name);

                        for ($i=0; $i < $c ; $i++) 
                        { 
                            $result['appointment_images'][$i]=$this->Api_model->insertData('appointment_images',array('image_name'=>$img_name[$i],'app_id'=>$insert_id,'createdat'=>date('Y-m-d h:i:s')));
                        }
                        // Image Upload

                        if($insert_id)
                        {
                            $getSup=$this->Applications_model->selectData('users','user_id,name,mobile,email',array('city'=>$post['app_city'],'role'=>5));


                            if(count($getSup)>0)
                            {
                                $min=0;
                                $max=count($getSup)-1;
                                $random=mt_rand($min,$max);
                                $insertSupData=array('app_id'=>$insert_id,'user_id'=>$getSup[$random]->user_id,'createdat'=>date('Y-m-d H:i:s'));
                                $sup_id=$this->Applications_model->insertData('job_supervisor',$insertSupData);

                                if($sup_id)
                                {
                                    $getTechPincode=$this->Applications_model->selectData('users','user_id',array('role'=>6,'city'=>$post['app_city']));
                                    // echo "<pre>";print_r($getTechPincode);exit();

                                    if(count($getTechPincode)>0)
                                    {
                                        $getTechJobOccupied=$this->Applications_model->techJobOccupied($post['app_date'],$post['app_time'],$getTechPincode);
                                        if(count($getTechJobOccupied)>0)
                                        {
                                            for($x = 0; $x < count($getTechPincode); $x++) 
                                            {
                                                $arr1[$x]=$getTechPincode[$x]->user_id;
                                            }
                                            for($x = 0; $x < count($getTechJobOccupied); $x++) 
                                            {
                                                $arr2[$x]=$getTechJobOccupied[$x]->user_id;
                                            }
                                            $freeTech=array_values(array_diff($arr1,$arr2));                              
                                            if($freeTech)
                                            {
                                                $min=0;
                                                $max=count($freeTech)-1;
                                                $random=mt_rand($min,$max);
                                                $insertTechData=array(
                                                    'app_id'=>$insert_id,
                                                    'user_id'=>$freeTech[$random],
                                                    'createdat'=>date('Y-m-d H:i:s')
                                                );
                                                $tech_id=$this->Applications_model->insertData('job_assign',$insertTechData);

                                                //*********Mail Sent to customer & supervisor about booking details************************
                                                $bookingDetails['app']=$this->Email_model->fetchJobDetails($insert_id);
                                                // print_r($bookingDetails);exit();
                                                $contentCus=$this->load->view('email/jobAppointment.html',$bookingDetails,true);
                                                // $mailToCustomer=sendEmail('taha@hmmbiz.com','Booking Confirmation',$contentCus);
                                                $mailToCustomer=sendEmail($bookingDetails['app'][0]->app_email,'Booking Confirmation',$contentCus);
                                                $contentSup=$this->load->view('email/newAppointSup.html',$bookingDetails,true);
                                                // $mailToSupervisor=sendEmail('taha@hmmbiz.com','New Appointment Scheduled',$contentSup);
                                                $mailToSupervisor=sendEmail($bookingDetails['app'][0]->suemail,'New Appointment Scheduled',$contentSup);
                                                //*****************************************************************8************************
                                                $contentTech=$this->load->view('email/newAppointTech.html',$bookingDetails,true);
                                                // $mailToTechnician=sendEmail('taha@hmmbiz.com','New Appointment Scheduled',$contentTech);
                                                $mailToTechnician=sendEmail($bookingDetails['app'][0]->tuemail,'New Appointment Scheduled',$contentTech);
                                                //***************************************************************************

                                                // Customer
                                                $messageCus="Hello ".$bookingDetails['app'][0]->app_name.",\n\nYour Appointment has been scheduled for \nDate:".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                                $numberCus=$bookingDetails['app'][0]->app_mobile;
                                                $smsCus=send_sms($numberCus,$messageCus);


                                                // Supervisor
                                                $messageSup="Hello ".$bookingDetails['app'][0]->supname.",\n\nNew Appointment Scheduled For: \n\nName: ".$bookingDetails['app'][0]->app_name."\nAddress: ".$bookingDetails['app'][0]->app_address.",\n".$bookingDetails['app'][0]->city_name." ,\n\nDate: ".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nMobile: ".$bookingDetails['app'][0]->app_mobile."\n\nTechnician: ".$bookingDetails['app'][0]->techname." (".$bookingDetails['app'][0]->techmobile.")\n\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                                $numberSup=$bookingDetails['app'][0]->sumobile;
                                                $smsSup=send_sms($numberSup,$messageSup);


                                                // Technician
                                                $messageTech="Hello ".$bookingDetails['app'][0]->techname.",\n\nNew Appointment Scheduled For: \n\nName: ".$bookingDetails['app'][0]->app_name."\nAddress: ".$bookingDetails['app'][0]->app_address.",\n".$bookingDetails['app'][0]->city_name." ,\n\nDate: ".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nMobile: ".$bookingDetails['app'][0]->app_mobile."\n\nSupervisor: ".$bookingDetails['app'][0]->supname." (".$bookingDetails['app'][0]->sumobile.")\n\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                                $numberTech=$bookingDetails['app'][0]->techmobile;
                                                $smsTech=send_sms($numberTech,$messageTech);

                                                $notification=array('notify_type'=>'Job',
                                                'app_id'=>$insert_id,
                                                'notify_link'=>base_url().'job/view/'.$insert_id,
                                                'createdat'=>date('Y-m-d')
                                                );
                                                $notify=$this->Applications_model->insertData('notifications',$notification);

                                                // Send Push notification to the Technician
                                                sendPushHelper($insert_id,$freeTech[$random]);

                                                $result['app_id']=$insert_id;
                                                $result['message']="Application created successfully";
                                                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result));
                                            }
                                            else
                                            {

                                                $notification=array('notify_type'=>'Job',
                                                'app_id'=>$insert_id,
                                                'notify_link'=>base_url().'job/view/'.$insert_id,
                                                'createdat'=>date('Y-m-d')
                                                );
                                                $notify=$this->Applications_model->insertData('notifications',$notification);

                                                //*********Mail Sent to customer & supervisor about booking details************************
                                                $bookingDetails['app']=$this->Email_model->fetchJobDetails($insert_id);
                                                // print_r($bookingDetails);exit();
                                                $contentCus=$this->load->view('email/jobAppointment.html',$bookingDetails,true);
                                                // $mailToCustomer=sendEmail('taha@hmmbiz.com','Booking Confirmation',$contentCus);
                                                $mailToCustomer=sendEmail($bookingDetails['app'][0]->app_email,'Booking Confirmation',$contentCus);
                                                $contentSup=$this->load->view('email/newAppointSup.html',$bookingDetails,true);
                                                // $mailToSupervisor=sendEmail('taha@hmmbiz.com','New Appointment Scheduled',$contentSup);
                                                $mailToSupervisor=sendEmail($bookingDetails['app'][0]->suemail,'New Appointment Scheduled',$contentSup);
                                                //*****************************************************************8************************

                                                // Customer
                                                $messageCus="Hello ".$bookingDetails['app'][0]->app_name.",\n\nYour Appointment has been scheduled for \nDate:".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                                $numberCus=$bookingDetails['app'][0]->app_mobile;
                                                $smsCus=send_sms($numberCus,$messageCus);


                                                // Supervisor
                                                $messageSup="Hello ".$bookingDetails['app'][0]->supname.",\n\nNew Appointment Scheduled For: \n\nName: ".$bookingDetails['app'][0]->app_name."\nAddress: ".$bookingDetails['app'][0]->app_address.",\n".$bookingDetails['app'][0]->city_name." ,\n\nDate: ".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nMobile: ".$bookingDetails['app'][0]->app_mobile."\n\nTechnician: ".$bookingDetails['app'][0]->techname." (".$bookingDetails['app'][0]->techmobile.")\n\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                                $numberSup=$bookingDetails['app'][0]->sumobile;
                                                $smsSup=send_sms($numberSup,$messageSup);

                                                
                                                $result['app_id']=$insert_id;
                                                $result['message']="Application created successfully";
                                                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result)); 
                                            }
                                        }
                                        else
                                        {
                                            $min=0;
                                            $max=count($getTechPincode)-1;
                                            $random=mt_rand($min,$max);
                                            $insertTechData=array(
                                                'app_id'=>$insert_id,
                                                'user_id'=>$getTechPincode[$random]->user_id,
                                                'createdat'=>date('Y-m-d H:i:s')
                                            );
                                            $tech_id=$this->Applications_model->insertData('job_assign',$insertTechData);

                                            //*********Mail Sent to customer & supervisor about booking details************************
                                            $bookingDetails['app']=$this->Email_model->fetchJobDetails($insert_id);
                                            // print_r($bookingDetails);exit();
                                            $contentCus=$this->load->view('email/jobAppointment.html',$bookingDetails,true);
                                            // $mailToCustomer=sendEmail('taha@hmmbiz.com','Booking Confirmation',$contentCus);
                                            $mailToCustomer=sendEmail($bookingDetails['app'][0]->app_email,'Booking Confirmation',$contentCus);
                                            $contentSup=$this->load->view('email/newAppointSup.html',$bookingDetails,true);
                                            // $mailToSupervisor=sendEmail('taha@hmmbiz.com','New Appointment Scheduled',$contentSup);
                                            $mailToSupervisor=sendEmail($bookingDetails['app'][0]->suemail,'New Appointment Scheduled',$contentSup);
                                            //*****************************************************************8************************
                                            $contentTech=$this->load->view('email/newAppointTech.html',$bookingDetails,true);
                                            // $mailToTechnician=sendEmail('taha@hmmbiz.com','New Appointment Scheduled',$contentTech);
                                            $mailToTechnician=sendEmail($bookingDetails['app'][0]->tuemail,'New Appointment Scheduled',$contentTech);
                                            //***************************************************************************

                                            // Customer
                                            $messageCus="Hello ".$bookingDetails['app'][0]->app_name.",\n\nYour Appointment has been scheduled for \nDate:".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                            $numberCus=$bookingDetails['app'][0]->app_mobile;
                                            $smsCus=send_sms($numberCus,$messageCus);


                                            // Supervisor
                                            $messageSup="Hello ".$bookingDetails['app'][0]->supname.",\n\nNew Appointment Scheduled For: \n\nName: ".$bookingDetails['app'][0]->app_name."\nAddress: ".$bookingDetails['app'][0]->app_address.",\n".$bookingDetails['app'][0]->city_name." ,\n\nDate: ".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nMobile: ".$bookingDetails['app'][0]->app_mobile."\n\nTechnician: ".$bookingDetails['app'][0]->techname." (".$bookingDetails['app'][0]->techmobile.")\n\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                            $numberSup=$bookingDetails['app'][0]->sumobile;
                                            $smsSup=send_sms($numberSup,$messageSup);


                                            // Technician
                                            $messageTech="Hello ".$bookingDetails['app'][0]->techname.",\n\nNew Appointment Scheduled For: \n\nName: ".$bookingDetails['app'][0]->app_name."\nAddress: ".$bookingDetails['app'][0]->app_address.",\n".$bookingDetails['app'][0]->city_name." ,\n\nDate: ".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nMobile: ".$bookingDetails['app'][0]->app_mobile."\n\nSupervisor: ".$bookingDetails['app'][0]->supname." (".$bookingDetails['app'][0]->sumobile.")\n\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                            $numberTech=$bookingDetails['app'][0]->techmobile;
                                            $smsTech=send_sms($numberTech,$messageTech);

                                            $notification=array('notify_type'=>'Job',
                                            'app_id'=>$insert_id,
                                            'notify_link'=>base_url().'job/view/'.$insert_id,
                                            'createdat'=>date('Y-m-d')
                                            );
                                            $notify=$this->Applications_model->insertData('notifications',$notification);

                                            // Send Push notification to the Technician
                                            sendPushHelper($insert_id,$getTechPincode[$random]->user_id);

                                            $result['app_id']=$insert_id;
                                            $result['message']="Application created successfully";
                                            $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result));
                                        }                               
                                    }
                                    else
                                    {
                                        //*********Mail Sent to customer & supervisor about booking details************************
                                        $bookingDetails['app']=$this->Email_model->fetchJobDetails($insert_id);
                                        // print_r($bookingDetails);exit();
                                        $contentCus=$this->load->view('email/jobAppointment.html',$bookingDetails,true);
                                         // print_r($content);exit();
                                        // $mailToCustomer=sendEmail('taha@hmmbiz.com','Booking Confirmation',$contentCus);
                                        $mailToCustomer=sendEmail($bookingDetails['app'][0]->app_email,'Booking Confirmation',$contentCus);
                                        $contentSup=$this->load->view('email/newAppointSup.html',$bookingDetails,true);
                                        // $mailToSupervisor=sendEmail('taha@hmmbiz.com','New Appointment Scheduled',$contentSup);
                                        $mailToSupervisor=sendEmail($bookingDetails['app'][0]->suemail,'New Appointment Scheduled',$contentSup);
                                        //*****************************************************************8************************

                                        // Customer
                                        $messageCus="Hello ".$bookingDetails['app'][0]->app_name.",\n\nYour Appointment has been scheduled for \nDate:".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                        $numberCus=$bookingDetails['app'][0]->app_mobile;
                                        $smsCus=send_sms($numberCus,$messageCus);


                                        // Supervisor
                                        $messageSup="Hello ".$bookingDetails['app'][0]->supname.",\n\nNew Appointment Scheduled For: \n\nName: ".$bookingDetails['app'][0]->app_name."\nAddress: ".$bookingDetails['app'][0]->app_address.",\n".$bookingDetails['app'][0]->city_name." ,\n\nDate: ".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nMobile: ".$bookingDetails['app'][0]->app_mobile."\n\nTechnician: ".$bookingDetails['app'][0]->techname." (".$bookingDetails['app'][0]->techmobile.")\n\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                        $numberSup=$bookingDetails['app'][0]->sumobile;
                                        $smsSup=send_sms($numberSup,$messageSup);

                                        $notification=array('notify_type'=>'Job',
                                        'app_id'=>$insert_id,
                                        'notify_link'=>base_url().'job/view/'.$insert_id,
                                        'createdat'=>date('Y-m-d')
                                        );
                                        $notify=$this->Applications_model->insertData('notifications',$notification);


                                        $result['app_id']=$insert_id;
                                        $result['message']="Application created successfully";
                                        $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result));
                                    }   
                                }
                                else
                                {
                                    $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                                }

                            }
                            else
                            {

                               //*********Mail Sent to customer about booking details************************
                                $bookingDetails['app']=$this->Email_model->fetchJobDetails($insert_id);
                                $contentCus=$this->load->view('email/jobAppointment.html',$bookingDetails,true);
                                // $mailToCustomer=sendEmail('taha@hmmbiz.com','Your Appointment Details',$contentCus);
                                $mailToCustomer=sendEmail($bookingDetails['app'][0]->app_email,'Booking Confirmation',$contentCus);
                                //***************************************************************************

                                // Customer
                                $messageCus="Hello ".$bookingDetails['app'][0]->app_name.",\n\nYour Appointment has been scheduled for \nDate:".$bookingDetails['app'][0]->app_date."\nTime: ".$bookingDetails['app'][0]->app_time."\nPlease check your email for more details\n\nThanks,\nPlumb Tech";

                                $numberCus=$bookingDetails['app'][0]->app_mobile;
                                $smsCus=send_sms($numberCus,$messageCus);

                                $notification=array('notify_type'=>'Job',
                                        'app_id'=>$insert_id,
                                        'notify_link'=>base_url().'job/view/'.$insert_id,
                                        'createdat'=>date('Y-m-d')
                                    );
                                $notify=$this->Applications_model->insertData('notifications',$notification);

                                $result['app_id']=$insert_id;
                                $result['message']="Application created successfully";
                                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => $result));
                            }
                        }
                        else
                        {
                            $this->response(array('response_code'=>'400', 'response_message' => 'No Data Found' , 'response_data' => array()));
                        }
                    }
                    // ***********************************************************************************************
                }                
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        }     
    }

    /**
     * @api {post} Customer/notification Customer notification
     * @apiVersion 0.1.0
     * @apiName Notification
     * @apiGroup Customer
     * @apiParam {id}     client_id   Client ID.
     * @apiSuccessExample Success
     *  {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": [
     *          {
     *              "title": "Ongoing",
     *              "app_id": "235",
     *              "job_type": "job",
     *              "body": "Your Appointment #235 for Plumbing Repairs in Leak is ongoing",
     *              "read_status": "unread",
     *              "createdat": "2018-12-27 10:54:20"
     *          },
     *          {
     *              "title": "Completed",
     *              "app_id": "235",
     *              "job_type": "job",
     *              "body": "Your Appointment #235 for Plumbing Repairs in Leak is Completed",
     *              "read_status": "unread",
     *              "createdat": "2018-12-27 10:55:08"
     *          }
     *      ]
     *  }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     * @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     */

    public function notification_post(){
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $data = $this->post();
            if(isset($data['client_id']) && $data['client_id']!=NULL)
            {
                $result=$this->Api_model->selectData('app_notification','title,app_id,job_type,body,read_status,createdat',array('client_id'=>$data['client_id'],'app_type'=>'cust'));
                    

                if(count($result) > 0)
                {
                    $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                } 
                else 
                {
                    $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);            
                }
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            }
        }
    }

    /**
     * @api {post} Customer/getInvoice Detailed Invoice
     * @apiVersion 0.1.0
     * @apiName Invoice Details
     * @apiGroup Customer
     * @apiParam {id}     app_id   Application id.
     * @apiSuccessExample Success
     *     {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": {
     *          "invoice": [
     *              {
     *                  "invoice_id": "32",
     *                  "invoice_date": "2019-03-27",
     *                  "issue_date": "2019-03-27",
     *                  "sub_total_material_cost": "9000",
     *                  "plumbing_charge_desc": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.",
     *                  "plumbing_charge": "1100",
     *                  "sub_total": "10100",
     *                  "discount": "0",
     *                  "total": "10100",
     *                  "job_id": "7",
     *                  "app_name": "Mommy Saunders",
     *                  "app_location": "Mom's Home",
     *                  "app_address": "Flamingo Street",
     *                  "app_mobile": "123456799",
     *                  "app_email": "mommysaunders@mail.com",
     *                  "city_name": "Questelles",
     *                  "state_name": "South Leeward",
     *                  "country_name": "Saint Vincent And The Grenadines",
     *                  "your_app_desc": "High smell from powder room",
     *                  "from_com": "VicTree Plumbing Inc.",
     *                  "from_add1": "Vigie Highway,PO Box 1124,Kingstown",
     *                  "from_add2": "St. Vincent and the Grenadines",
     *                  "from_mobile": "(784) 456-4157"
     *              }
     *          ],
     *          "material": [
     *              {
     *                  "invoice_detail_id": "66",
     *                  "invoice_id": "32",
     *                  "product_name": "SOAP DISPENSER WITH ALL-PURPOSE VALVE",
     *                  "product_price": "3000",
     *                  "product_tax": "0",
     *                  "product_quantity": "1",
     *                  "full_price": "3000",
     *                  "type": "product"
     *              },
     *              {
     *                  "invoice_detail_id": "67",
     *                  "invoice_id": "32",
     *                  "product_name": "Proplus 4 in. W x 3 in. D Soap Dish Concealed Scre...",
     *                  "product_price": "3000",
     *                  "product_tax": "0",
     *                  "product_quantity": "1",
     *                  "full_price": "3000",
     *                  "type": "product"
     *              },
     *              {
     *                  "invoice_detail_id": "69",
     *                  "invoice_id": "32",
     *                  "product_name": "SOAP DISPENSER WITH ALL-PURPOSE VALVE",
     *                  "product_price": "3000",
     *                  "product_tax": "0",
     *                  "product_quantity": "1",
     *                  "full_price": "3000",
     *                  "type": "product"
     *              }
     *          ]
     *      }
     *  }
     * @apiErrorExample No Data Found
     *       {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *   }
     * @apiErrorExample Parameter missing
     *     {
     *          "response_code": "400",
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
    */
    public function getInvoice_post(){
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $data = $this->post();
            if(isset($data['app_id']) && $data['app_id']!=NULL)
            {
                $result['invoice'] = $this->Api_model->fetchInvoicedetail($data['app_id']);
                //echo "<pre>";print_r($data);exit();
                if($result['invoice'] != null)
                {
                    $result['invoice'][0]->from_com='VicTree Plumbing Inc.';
                    $result['invoice'][0]->from_add1='Vigie Highway,PO Box 1124,Kingstown';
                    $result['invoice'][0]->from_add2='St. Vincent and the Grenadines';
                    $result['invoice'][0]->from_mobile='(784) 456-4157';
                    // $result['material']=$this->Api_model->selectData('invoice_detail','*',array('invoice_id'=>$result['invoice'][0]->invoice_id,'type'=>'product'));
                    $result['material']=$this->Api_model->selectData('invoice_detail','invoice_detail_id,invoice_id,product_name,product_price,product_tax,sum(product_quantity) as product_quantity,sum(full_price) as full_price,type',array('invoice_id'=>$result['invoice'][0]->invoice_id,'type'=>'product'),'','','product_name');
                } 
                    

                if(count($result['invoice']) > 0)
                {
                    $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                } 
                else 
                {
                    $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);            
                }
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            }
        }

               
    } 


    /**
     * @api {post} Customer/insertTransaction Payment Transaction
     * @apiVersion 0.1.0
     * @apiName Payment Transaction
     * @apiGroup Customer
     * @apiParam {int}     app_id            Application id
     * @apiParam {int}     client_id         Client id
     * @apiParam {text}    name              Client Name
     * @apiParam {int}     amount            Payment Amount
     * @apiParam {int}     txn_id            Transaction id
     * @apiParam {int}     bank_ref_num      Bank Reference Number
     * @apiParam {text}    mode              Payment Mode
     * @apiParam {text}    email             Email Address
     * @apiParam {text}    status            Status (success/failure)
     * @apiSuccessExample Success
     *  {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": {
     *          "insert_transaction": 11,
     *          "update": 1
     *      }
     *  }
     * @apiErrorExample No Data Found
     *  {
     *      "response_code": "400",
     *      "response_message": "No Data Found",
     *      "response_data": []
     *  }
     * @apiErrorExample Parameter missing
     *  {
     *       "response_code": "400",
     *       "response_message": "Parameter missing",
     *       "response_data": []
     *  }
    */
    public function insertTransaction_post() //Insert transaction details in the table API created by taha 21-12-2018
    {
        $authValidation = verifyUserAuthToken($this->head('AuthKey'));
        if($authValidation)
        {
            $data = $this->post();
            if(isset($data['app_id']) && $data['app_id']!=NULL && isset($data['client_id']) && $data['client_id']!=NULL && isset($data['name']) && $data['name']!=NULL && isset($data['amount']) && $data['amount']!=NULL && isset($data['txn_id']) && $data['txn_id']!=NULL && isset($data['bank_ref_num']) && $data['bank_ref_num']!=NULL && isset($data['mode']) && $data['mode']!=NULL && isset($data['email']) && $data['email']!=NULL && isset($data['status']) && $data['status']!=NULL )
            {
                $data['payment_date']=date('Y-m-d H:i:s');

                $result['insert_transaction'] = $this->Api_model->insertData('user_transaction',$data);

                if($data['status']=='success')
                {
                   $result['update'] = $this->Api_model->updateData('job_appointment',array('invoice_status'=>'Paid'),array('app_id'=>$data['app_id']));
                } 
                    
                if($result['insert_transaction'])
                {
                    $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result), REST_Controller::HTTP_OK);
                } 
                else 
                {
                    $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()), REST_Controller::HTTP_OK);            
                }
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_NOT_FOUND); 
            }
        }       
    } 
}


?>
