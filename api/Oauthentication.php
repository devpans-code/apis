<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Oauthentication extends REST_Controller {

    function __construct()
    {
        parent::__construct();
		 header("Access-Control-Allow-Origin: *");
		 $this->load->model('User_model');
         $this->load->model('Adminmaster_model');
		 $this->load->model('Api_model');
    }
	
    /**
     * @api {post} Oauthentication/signin Technician Login
     * @apiVersion 0.1.0
     * @apiName Technician Login
     * @apiGroup Technician
     * @apiParam {string}   username Enter Username.
     * @apiParam {password} password Enter Password.
     * @apiParam {varchar}  push-token  For Push Notifications
     *
     * @apiSuccessExample Success
     *     {
     *          "response_code": "200",
     *          "response_message": "success",
     *          "response_data": {
     *              "user_id": "25",
     *              "push_token": "a1111sdfafasfrwewe4w5e645g64g6sdf3dd4df1d2f31231234564564dbvg",
     *              "name": "devangen",
     *              "username": "devangen",
     *              "email": "codExalters.test@gmail.com",
     *              "mobile": "9898989898",
     *              "state": "17",
     *              "city": "1558",
     *              "country": "101",
     *              "role_name": "Technician",
     *              "vAuthKey": "40046647c48dff56a9f6295cc4bb2839"
     *          }
     *      }
     * @apiErrorExample Invalid Username and Password
     *     {
     *          "response_code": 400,
     *          "response_message": "Invalid username and password",
     *          "response_data": {}
     *     }
     * @apiErrorExample Parameter Missing
     *    {
     *      "response_code": "400",
     *      "response_message": "Parameter Missing",
     *      "response_data": {}
     *    }
     */
    public function signin_post()
    {

        $data = $this->post();
        if(isset($data['username']) && isset($data['username']) && isset($data['password']) && isset($data['password']) && isset($data['push-token']) && isset($data['push-token']))
        {
            $where = array('username' => $data['username'],
                                'password' => md5($data['password']),
                                'users.status' => 'Active',
                                'role.shortcode' => 'TE'
                             );
                            
            $user = $this->Adminmaster_model->signinApi('users', 'user_id,push_token,name,username,email,mobile,state,city,country,role.role_name', $where);

            if(count($user) > 0)
            {
                $user[0]->vAuthKey = md5( $user[0]->email . time());
                $updateUser['vAuthKey'] = $user[0]->vAuthKey;
                $user[0]->push_token = $data['push-token'];
                $updateUser['push_token'] = $data['push-token'];
                $this->Api_model->updateData('users',$updateUser , array("user_id" => $user[0]->user_id));  
                $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => (object)$user[0])); 
            }
            else
            {
                $this->response(array('response_code'=>'400', 'response_message' => 'Invalid username and password' , 'response_data' => (object)[]));
            } 
        }
        else 
        {
            $this->response(array('response_code'=>'400', 'response_message' => 'Parameter Missing' , 'response_data' => (object)[]));
        }
    }
    
    /**
     * @api {post} Oauthentication/cussignin Customer Login
     * @apiVersion 0.1.0
     * @apiName Customer Login
     * @apiGroup Customer
     * @apiParam {string}   username    Enter Username.
     * @apiParam {password} password    Enter Password.
     * @apiParam {varchar}  push-token  For Push Notifications
     *
     * @apiSuccessExample Success
     * {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "client_id": "73",
     *          "woo_cus_id": "0",
     *          "push_token": "54as56dfcsa5c1v1gegerer1g561sdf54be6ber",
     *          "name": "Joanne Sonia",
     *          "email": "tahasethwala@yahoo.com",
     *          "mobile": "9925738146",
     *          "address": "Unit Hills",
     *          "address2": "Juhapura",
     *          "country_name": "India",
     *          "state_name": "Karnataka",
     *          "city_name": "Bengaluru",
     *          "vAuthKey": "a5cbee46c99526766fcd15e78896450e"
     *      }
     *  }
     * @apiErrorExample Invalid Username and Password
     *     {
     *          "response_code": 400,
     *          "response_message": "Invalid username and password",
     *          "response_data": {}
     *     }
     * @apiErrorExample Parameter Missing
     *  {
     *      "response_code": "400",
     *      "response_message": "Parameter Missing",
     *      "response_data": {}
     *  }
     *
    */
     public function cussignin_post()
    {

        $data = $this->post();
        if(isset($data['username']) && isset($data['username']) && isset($data['password']) && isset($data['password']) && isset($data['push-token']) && isset($data['push-token']))
        {
                
                
                $where = array( 'email' => $data['username'],
                                'password' => md5($data['password']),
                                'status' => 'Active'
                        );
                            # pr($where,1);
                $user= $this->Api_model->cussignin('clients','client_id,woo_cus_id,push_token,name,email,mobile,address,c.country_name,s.state_name,ct.city_name',$where);
                if(count($user)>0)
                { 
                    // pr($user,1);
                    $user[0]->vAuthKey = md5( $user[0]->email . time());
                    $user[0]->push_token = $data['push-token'];
                    $updateUser['vAuthKey'] = $user[0]->vAuthKey;
                    $updateUser['push_token'] = $data['push-token'];
                    $this->Api_model->updateData('clients',$updateUser , array("client_id" => $user[0]->client_id));
                    $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => (object)$user[0])); 
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'Invalid username and password' , 'response_data' => (object)[]));
                }     
        }
        else 
        {
            $this->response(array('response_code'=>'400', 'response_message' => 'Parameter Missing' , 'response_data' => (object)[]));     
        }
    }


    /**
     * @api {post} Oauthentication/forgotPasswordCust Customer Forgot Password
     * @apiVersion 0.1.0
     * @apiName  Customer Forgot Password
     * @apiGroup Customer
     * @apiParam {int} mobile Mobile Number.
     *
     * @apiSuccessExample Success
     * {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "client_id": "73",
     *          "name": "Joanne Sonia",
     *          "otp": 117924
     *      }
     *  }
     * @apiErrorExample No account exists for this mobile number
     *     {
     *          "response_code": 400,
     *          "response_message": "No account exists for this mobile number",
     *          "response_data": {}
     *     }
     * @apiErrorExample Parameter Missing
     *  {
     *      "response_code": "400",
     *      "response_message": "Parameter Missing",
     *      "response_data": {}
     *  }
     *
    */
    public function forgotPasswordCust_post()
    {
    	$data=$this->post();
        // 

        if(isset($data['mobile']) && $data['mobile']!=null)
        {
                $user = $this->Api_model->selectData('clients','client_id,name',array('mobile'=>$data['mobile'],'status'=>'Active'));
                // print_r($user);exit();

                if(count($user) == 1)
                { 
                    $user[0]->otp=mt_rand(100000,999999);
                    $updateUser['otp']=$user[0]->otp;
                    $this->Api_model->updateData('clients',$updateUser ,array("client_id" => $user[0]->client_id));

                    $messageCus="Hello ".$user[0]->name.",\n\nYour OTP is: ". $user[0]->otp ." \n\nThanks,\nPlumb Vision";

                    $smsCus=send_sms($data['mobile'],$messageCus);

                    $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => (object)$user[0])); 
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'No account exists for this mobile number' , 'response_data' => (object)[]));
                }     
        }
        else 
        {
            $this->response(array('response_code'=>'400', 'response_message' => 'Parameter Missing' , 'response_data' => (object)[]));     
        }
    }

     /**
     * @api {post} Oauthentication/resetPasswordCust Customer Reset Password
     * @apiVersion 0.1.0
     * @apiName  Customer Reset Password
     * @apiGroup Customer
     * @apiParam {int}      client_id   Client Id
     * @apiParam {password} password    New Password
     *
     * @apiSuccessExample Success
     * {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "success": "Password Changed Successfully"
     *      }
     *  }
     * @apiErrorExample Please enter new passsword, this password was used previously
     *       {
     *      "response_code": "400",
     *      "response_message": "Please enter new passsword, this password was used previously",
     *      "response_data": {}
     *  }
     * @apiErrorExample Parameter Missing
     *  {
     *      "response_code": "400",
     *      "response_message": "Parameter Missing",
     *      "response_data": {}
     *  }
     *
    */
    public function resetPasswordCust_post()
    {
        $data=$this->post();
        if(isset($data['password']) && $data['password']!=null && isset($data['client_id']) && $data['client_id']!=null)
        {
                $update = $this->Api_model->updateData('clients',array('password'=>md5($data['password'])),array('client_id'=>$data['client_id']));
                // print_r($update);exit();
                if($update)
                { 

                    $result['success']='Password Changed Successfully';
                    $this->response(array('response_code'=>'200', 'response_message' => 'success' , 'response_data' => (object)$result)); 
                }
                else
                {
                    $this->response(array('response_code'=>'400', 'response_message' => 'Please enter new passsword, this password was used previously' , 'response_data' => (object)[]));
                }     
        }
        else 
        {
            $this->response(array('response_code'=>'400', 'response_message' => 'Parameter Missing' , 'response_data' => (object)[]));     
        }
    }


    public function editProfile_post()
    {
    	//	Param :- vName 

    	//	Header Param :- auth_key

    	$data = $this->post();
    	$headerArray = array();
    	$headerArray['nonce'] = $this->head('nonce');
    	$headerArray['timestamp'] = $this->head('timestamp');
    	$headerArray['token'] = $this->head('token');

    	$uaerAuthToken = $this->head('auth_key');

    	$authValidation = validateToken($headerArray);

    	if($authValidation && $this->Common_model->code == 200) 	// validate token
    	{
    		$userAuthVerify = verifyUserAuthToken($uaerAuthToken);

    		if($userAuthVerify && $this->Common_model->code == 200)
    		{
				if($this->User_model->AuthAttributes('editProfile',$data))  // validate required filed 
				{
					$userUpdate = array();
					$userUpdate['vName'] = $data['vName']; 
					$userUpdate['vContact'] = @$data['vContact'];
					
					if(isset($data['tGender']))
						$userUpdate['tGender'] = $data['tGender'];
					
					$userUpdate['vAddress'] = @$data['vAddress'];
					$userUpdate['vPostCode'] = @$data['vPostCode'];
					$userUpdate['vCountry'] = @$data['vCountry'];

					$this->User_model->updateData($userUpdate , array("iUserId" => $this->Common_model->iUserId));

					$userData = $this->User_model->fetchUserData('iUserId',$this->Common_model->iUserId);
					//$userData['imageUrl'] = ($userData['vProfileImage'] != '')? base_url('public/userupload/'.$userData['iUserId'].'/'.$userData['vProfileImage']) : '';

					$this->Common_model->code = 200;
			        $this->Common_model->message = "User profile edited Successfully";
			        $this->Common_model->data = $userData;
				}
			}
    	}

    	$this->set_response(array('response_code'=>$this->Common_model->code , 'response_message' => $this->Common_model->message , 'response_data' => $this->Common_model->data));	
    }


    public function changePassword_post()
    {
    	//	Param :- oldPassword , newPassword 

    	//	Header Param :- auth_key

    	$data = $this->post();
    	$headerArray = array();
    	$headerArray['nonce'] = $this->head('nonce');
    	$headerArray['timestamp'] = $this->head('timestamp');
    	$headerArray['token'] = $this->head('token');

    	$uaerAuthToken = $this->head('auth_key');

    	$authValidation = validateToken($headerArray);

    	if($authValidation && $this->Common_model->code == 200) 	// validate token
    	{
    		$userAuthVerify = verifyUserAuthToken($uaerAuthToken);

    		if($userAuthVerify && $this->Common_model->code == 200)
    		{
				if($this->User_model->AuthAttributes('changePassword',$data))  // validate required filed 
				{
					if($this->Common_model->data['vPassword'] == md5($data['oldPassword']))
					{
						$userUpdate = array();
						$userUpdate['vPassword'] = md5($data['newPassword']);

						$this->User_model->updateData($userUpdate , array("iUserId" => $this->Common_model->iUserId));

						$this->Common_model->code = 200;
				        $this->Common_model->message = "Password change Successfully";
				        $this->Common_model->data = (object) array();
					}
					else
					{
						$this->Common_model->code = 402;
				        $this->Common_model->message = "Invalid old password";
				        $this->Common_model->data = (object) array();
					}				
				}
			}
    	}
    	$this->set_response(array('response_code'=>$this->Common_model->code , 'response_message' => $this->Common_model->message , 'response_data' => $this->Common_model->data));	
    }

    public function fetchUserData_get()
    {
    	// Header Param :- auth_key

    	$headerArray = array();
    	$headerArray['nonce'] = $this->head('nonce');
    	$headerArray['timestamp'] = $this->head('timestamp');
    	$headerArray['token'] = $this->head('token');

    	$userAuthToken = $this->head('auth_key');

    	$authValidation = validateToken($headerArray);

    	if($authValidation && $this->Common_model->code == 200)
    	{
    		$userAuthVerify = verifyUserAuthToken($userAuthToken);

    		if($userAuthVerify && $this->Common_model->code == 200)
    		{
    		
    			$userData = $this->User_model->fetchUserAllData("u.iUserId",$this->Common_model->iUserId);

    			if(!empty($userData))
    			{
    				//$userData['imageUrl'] = ($userData['vProfileImage'] != '')? base_url('public/userupload/'.$userData['iUserId'].'/'.$userData['vProfileImage']) : '';
    				
    				$this->Common_model->code = 200;
	                $this->Common_model->message = 'Success';
	                $this->Common_model->data = $userData;
    			}	
    			else
    			{
    				$this->Common_model->code = 402;
	                $this->Common_model->message = 'No data found';
	                $this->Common_model->data = (object) array();
    			}
    		}
    	}

		$this->set_response(array('response_code' => $this->Common_model->code , 'response_message' => $this->Common_model->message , 'response_data' => $this->Common_model->data));
    }


    public function userImageUpload($userId)
    {
    	
        $name3=explode('.',$_FILES['vProfileImage']['name']);
        $_FILES['vProfileImage']['name']='user-'.$userId.'-'.time().'.'.$name3[1];

        if(!is_dir(DOC_ROOT_USER_UPLOAD_PATH))
        {
        	mkdir(DOC_ROOT_USER_UPLOAD_PATH, 0777);
        	chmod(DOC_ROOT_USER_UPLOAD_PATH, 0777);
        }

        if(!is_dir(DOC_ROOT_USER_UPLOAD_PATH."/".$userId))
        {
        	mkdir(DOC_ROOT_USER_UPLOAD_PATH."/".$userId, 0777);
        	chmod(DOC_ROOT_USER_UPLOAD_PATH."/".$userId, 0777);
        }

        $config['upload_path'] = DOC_ROOT_USER_UPLOAD_PATH."/".$userId;
    	$config['allowed_types'] = '*'; 
    	$this->load->library('upload', $config); 

    	if ( ! $this->upload->do_upload('vProfileImage'))
        {
        	$this->Common_model->code = 402;
	        $this->Common_model->message = "User Image upload error";
	        $this->Common_model->data = (object) array();

            $status = array("ststus" => false);
            return $status;
        }
        else
        {
            $upload_data =  $this->upload->data();

            if(!empty($this->Common_model->data['vProfileImage']))
			{
				$filePath = DOC_ROOT_USER_UPLOAD_PATH."/".$userId."/".$this->Common_model->data['vProfileImage'];

				if(file_exists($filePath))
				{
					unlink($filePath);
				}
			}

            $status = array("status" => true , "imageName" => $upload_data['file_name']);

            return $status;
        }

    }

    public function updateSubscription_post()
    {
    	// Param :- tIsSubscribe , Header Param :- auth_key

    	$data = $this->post();
    	$headerArray = array();
    	$headerArray['nonce'] = $this->head('nonce');
    	$headerArray['timestamp'] = $this->head('timestamp');
    	$headerArray['token'] = $this->head('token');

    	$uaerAuthToken = $this->head('auth_key');

    	$authValidation = validateToken($headerArray);

    	if($authValidation && $this->Common_model->code == 200) 	// validate token
    	{
    		$userAuthVerify = verifyUserAuthToken($uaerAuthToken);

    		if($userAuthVerify && $this->Common_model->code == 200)
    		{
				if($data['tIsSubscribe'] != '')  // validate required filed 
				{
					$this->User_model->updateData(array('tIsSubscribe' => $data['tIsSubscribe']) , array("iUserId" => $this->Common_model->iUserId));

					$this->Common_model->code = 200;
	                $this->Common_model->message = 'Success';
	                $this->Common_model->data = (object) array();
				}
				else
				{
					$this->Common_model->code = 400;
			        $this->Common_model->message = "tIsSubscribe must not be blank.";
			        $this->Common_model->data = (object) array();
				}
			}
    	}

    	$this->set_response(array('response_code'=>$this->Common_model->code , 'response_message' => $this->Common_model->message , 'response_data' => $this->Common_model->data));	
    }



    public function signOut_get()
    {
    	// Header Param :- auth_key

    	$data = $this->post();
    	$headerArray = array();
    	$headerArray['nonce'] = $this->head('nonce');
    	$headerArray['timestamp'] = $this->head('timestamp');
    	$headerArray['token'] = $this->head('token');

    	$uaerAuthToken = $this->head('auth_key');

    	$authValidation = validateToken($headerArray);

    	if($authValidation && $this->Common_model->code == 200) 	// validate token
    	{
    		$userAuthVerify = verifyUserAuthToken($uaerAuthToken);

    		if($userAuthVerify && $this->Common_model->code == 200)
    		{
					$this->User_model->updateData(array('tIsLogin' => 0) , array("iUserId" => $this->Common_model->iUserId));

					$this->Common_model->code = 200;
	                $this->Common_model->message = 'Success';
	                $this->Common_model->data = (object) array();
			}
    	}

    	$this->set_response(array('response_code'=>$this->Common_model->code , 'response_message' => $this->Common_model->message , 'response_data' => $this->Common_model->data));	
    }

    public function setUserOrderAddress_post()
    {
    	// Param :- vName , vEmail , vContact , vAddress , vPostCode , vCountry 
    	// Header Param :- auth_key

    	$data = $this->post();
    	$headerArray = array();
    	$headerArray['nonce'] = $this->head('nonce'); 
    	$headerArray['timestamp'] = $this->head('timestamp'); 
    	$headerArray['token'] = $this->head('token');

    	$uaerAuthToken = $this->head('auth_key');

    	$authValidation = validateToken($headerArray);

    	if($authValidation && $this->Common_model->code == 200)
    	{
    		$userAuthVerify = verifyUserAuthToken($uaerAuthToken);

    		if($userAuthVerify && $this->Common_model->code == 200)
    		{
    			if($this->User_model->AuthAttributes('userOrderAddress',$data))  // validate required filed 
				{
					$userData = $this->Userorderaddress_model->fetchUserData("iUserId",$this->Common_model->iUserId);
					if(empty($userData))
					{
						$orderAddressArray = array();
						$orderAddressArray['iUserId'] = $this->Common_model->iUserId;
						$orderAddressArray['vName'] = $data['vName'];
						$orderAddressArray['vEmail'] = $data['vEmail'];
						$orderAddressArray['vContact'] = $data['vContact'];
						$orderAddressArray['vAddress'] = $data['vAddress'];
						$orderAddressArray['vPostCode'] = $data['vPostCode'];
						$orderAddressArray['vCountry'] = $data['vCountry'];

						$this->Userorderaddress_model->insertData($orderAddressArray);
					}
					else
					{
						$orderAddressArray = array();
						$orderAddressArray['iUserId'] = $this->Common_model->iUserId;
						$orderAddressArray['vName'] = $data['vName'];
						$orderAddressArray['vEmail'] = $data['vEmail'];
						$orderAddressArray['vContact'] = $data['vContact'];
						$orderAddressArray['vAddress'] = $data['vAddress'];
						$orderAddressArray['vPostCode'] = $data['vPostCode'];
						$orderAddressArray['vCountry'] = $data['vCountry'];

						$this->Userorderaddress_model->updateData($orderAddressArray,array("iUserAddressId" => $userData['iUserAddressId']));						
					}

					$this->Common_model->code = 200;
	                $this->Common_model->message = 'Success';
	                $this->Common_model->data = (object) array();			
				}
    		}
    	} 

    	$this->set_response(array('response_code'=>$this->Common_model->code , 'response_message' => $this->Common_model->message , 'response_data' => $this->Common_model->data));
    }

    public function sendForgotPassMail_get()
    {
    	$data = json_decode($this->get('jsonData'),1);

    	$forgotPassData = array();
    	$forgotPassData['vName'] = $data['vName'];
    	$forgotPassData['forgotPassLink'] = $data['forgotPassLink'];

    	$html = forgotTpl($forgotPassData);
		$ret = sendEmail(@$data['vEmail'], SUBJECT_FORGOTPASS, $html);
    }

   
}
