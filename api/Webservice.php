<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Webservice extends REST_Controller {

    function __construct()
    {
        parent::__construct();
         header("Access-Control-Allow-Origin: *");
         $this->load->model('Api_model');
    }
    
    /**
     * @api {post} webservice/gettechjobs Fetch assigned job for technician
     * @apiVersion 0.1.0
     * @apiName Technician Job
     * @apiGroup Technician
     *
     * @apiParam {int} user_id User id from login response.
     *
     * @apiSuccessExample Success
     * {
     *     "response_code": 200,
     *     "response_message": "Success",
     *     "response_data": {
     *         "job": [
     *             {
     *                 "app_date": "2018-11-01",
     *                 "app_time": "08:20:00",
     *                 "app_name": "Nathan Fisher",
     *                 "app_company": "",
     *                 "app_address": "Maecenas lacinia porta",
     *                 "state_name": "Karnataka",
     *                 "city_name": "Bengaluru",
     *                 "app_pincode": "560002",
     *                 "app_mobile": "85996658574",
     *                 "app_phone": "",
     *                 "app_email": "nathan@xyz.com",
     *                 "app_desc": "Sed tincidunt placerat dolor",
     *                 "job_id": "1",
     *                 "job_assign_id": "1",
     *                 "user_id": "12",
     *                 "app_status": "Ongoing",
     *                 "type": "job"
     *             }
     *         ],
     *         "projectJob": [
     *             {
     *                 "app_date": "2018-11-21",
     *                 "app_time": "08:20:00",
     *                 "app_name": "Peter Fisher",
     *                 "app_company": "",
     *                 "app_address": "Duis quis sem et nulla interdum auctor",
     *                 "state_name": "Karnataka",
     *                 "city_name": "Bengaluru",
     *                 "app_pincode": "560002",
     *                 "app_mobile": "7536852014",
     *                 "app_phone": "",
     *                 "app_email": "peter@xyz.com",
     *                 "app_desc": "Curabitur efficitur, sapien in rhoncus",
     *                 "app_id": "5",
     *                 "mile_id": "1",
     *                 "mile_name": "Peter-Mile1",
     *                 "job_id": "1",
     *                 "mile_job_name": "Peter-Mile1-Job1",
     *                 "mile_jobassign_id": "1",
     *                 "user_id": "12",
     *                 "app_status": "Ongoing",
     *                 "mile_job_status": "Ongoing",
     *                 "type": "project"
     *             }
     *         ]
     *     }
     *  }
     * @apiError Message Error Code 400.
     *
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "No data found",
     *          "response_data": []
     *     }
     */
    public function gettechjobs_post()
    {
        $data=$this->post();
        $authValidation = verifyUserAuthTokenTech($this->head('AuthKey'));
        if($authValidation)
        {
            $user_id = $data['user_id'];
            if($user_id!=NULL)
            {
                $result['job']=$this->Api_model->selectTechJob($user_id);
                $result['projectJob']=$this->Api_model->selectTechProjectJob($user_id);

                if(count($result['job'])>0 || count($result['projectJob'])>0)
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
     * @api {post} webservice/gettechjobsStatus Fetch status filtered assigned job for technician 
     * @apiVersion 0.1.0
     * @apiName Get Job By Status
     * @apiGroup Technician
     *
     * @apiParam {int} user_id User id from login response.
     * @apiParam {text} status Status from technician response.
     *
     * @apiError Message Error Code 400.
     *
     * @apiSuccessExample Success
     *     {
     *      "response_code": 200,
     *      "response_message": "Success",
     *      "response_data": {
     *          "job": [
     *              {
     *                  "app_date": "1970-01-01",
     *                  "app_time": "08:20:00",
     *                  "app_name": "Taha Sethwala",
     *                  "app_company": "",
     *                  "app_address": "Unit Hills, Union park Society",
     *                  "app_address1": null,
     *                  "state_name": "Central Kingstown",
     *                  "city_name": "Level Gardens",
     *                  "app_pincode": null,
     *                  "app_mobile": "9925738146",
     *                  "app_phone": "",
     *                  "app_email": "tahasethwala@yahoo.com",
     *                  "app_desc": "Lorem is simply dummy text.",
     *                  "app_file": [
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak3.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak3.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak3.jpg"
     *                  ],
     *                  "job_assign_id": "2",
     *                  "user_id": "10",
     *                  "app_status": "Requested",
     *                  "modified_date": "26-02-2019 16:48:11",
     *                  "job_id": "2",
     *                  "category": "Plumbing Repairs",
     *                  "service": "Leak",
     *                  "type": "job"
     *              },
     *              {
     *                  "app_date": "2019-02-20",
     *                  "app_time": "08:20:00",
     *                  "app_name": "Taha Sethwala",
     *                  "app_company": "",
     *                  "app_address": "Unit Hills, Union park Society",
     *                  "app_address1": null,
     *                  "state_name": "Central Kingstown",
     *                  "city_name": "Level Gardens",
     *                  "app_pincode": null,
     *                  "app_mobile": "9925738146",
     *                  "app_phone": "",
     *                  "app_email": "tahasethwala@yahoo.com",
     *                  "app_desc": "Lorem is simply dummy text.",
     *                  "app_file": [
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak6.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak6.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak6.jpg"
     *                  ],
     *                  "job_assign_id": "30",
     *                  "user_id": "10",
     *                  "app_status": "Requested",
     *                  "modified_date": "26-02-2019 16:51:07",
     *                  "job_id": "3",
     *                  "category": "Plumbing Repairs",
     *                  "service": "Leak",
     *                  "type": "job"
     *              }
     *          ],
     *          "projectJob": [
     *              {
     *                  "app_date": "1970-01-01",
     *                  "app_time": "08:20:00",
     *                  "app_name": "Taha Sethwala",
     *                  "app_company": "",
     *                  "app_address": "Unit Hills, Union park Society",
     *                  "app_address1": null,
     *                  "state_name": "Central Kingstown",
     *                  "city_name": "Level Gardens",
     *                  "app_pincode": null,
     *                  "app_mobile": "9925738146",
     *                  "app_phone": "",
     *                  "app_email": "tahasethwala@yahoo.com",
     *                  "app_desc": "Lorem is simply dummy text.",
     *                  "app_file": [
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak.jpg",
     *                      "http://resourceserver.in/demo/vp-admin/public/uploads/appointment/leak.jpg"
     *                  ],
     *                  "app_id": "1",
     *                  "modified_date": "26-02-2019 17:05:38",
     *                  "mile_id": "1",
     *                  "mile_name": "Mile1",
     *                  "mile_job_name": "Job1",
     *                  "mile_jobassign_id": "1",
     *                  "user_id": "10",
     *                  "app_status": "Requested",
     *                  "job_id": "1",
     *                  "mile_job_status": "Requested",
     *                  "category": "Plumbing from Start to Finish",
     *                  "service": "Domestic",
     *                  "type": "project"
     *              }
     *          ]
     *      }
     *  }
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "Parameter missing",
     *          "response_data": []
     *     }
     * @apiErrorExample Parameter invalid
     *     {
     *          "response_code": 400,
     *          "response_message": "No data found",
     *          "response_data": []
     *     }
     */
    public function gettechjobsStatus_post()
    {
        $data=$this->post();
        $authValidation = verifyUserAuthTokenTech($this->head('AuthKey'));
        if($authValidation)
        {
            $user_id=$data['user_id'];
            $status=$data['status'];
            if($user_id!=NULL && $status!=NULL)
            {
                $result['job']=$this->Api_model->selectTechJob($user_id,$status);
                
                
                if(count($result['job']) > 0 )
                {
                    for($i=0; $i < count($result['job']); $i++)
                    {
                        $app_images=$this->Api_model->selectData('appointment_images','image_id,image_name',array('app_id'=>$result['job'][$i]->job_id));
                        if(count($app_images)>0)
                        {
                            for ($j=0; $j < count($app_images); $j++) 
                            { 
                                $result['job'][$i]->app_file[$j]=base_url().'public/uploads/appointment/'.$app_images[$j]->image_name;
                            }
                        }
                        else
                        {
                            $result['job'][$i]->app_file=array();
                        }
                        
                    }
                }


                
                $result['projectJob']=$this->Api_model->selectTechProjectJob($user_id,$status);

                if(count($result['projectJob']) > 0 )
                {
                    
                    for($i=0; $i < count($result['projectJob']); $i++)
                    {
                        $app_images=$this->Api_model->selectData('appointment_images','image_id,image_name',array('app_id'=>$result['projectJob'][$i]->app_id));
                        if(count($app_images)>0)
                        {
                            for ($j=0; $j < count($app_images); $j++) 
                            { 
                                $result['projectJob'][$i]->app_file[$j]=base_url().'public/uploads/appointment/'.$app_images[$j]->image_name;
                            }
                        }
                        else
                        {
                            $result['projectJob'][$i]->app_file=array();
                        }
                        
                    }
                }

                 // echo"<pre>";print_r($result);exit();
                         
                foreach($result['job'] as $key=>$value)
                {
                   $result['job'][$key]->modified_date=convertDateTime($result['job'][$key]->modified_date);
                }

                foreach($result['projectJob'] as $key1=>$value1)
                {
                   $result['projectJob'][$key1]->modified_date=convertDateTime($result['projectJob'][$key1]->modified_date);
                }

                if(count($result['job'])>0 || count($result['projectJob'])>0)
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
     * @api {post} webservice/startJob When the Technician Starts The Job 
     * @apiVersion 0.1.0
     * @apiName Job Started
     * @apiGroup Technician
     *
     * @apiParam {int}      user_id User id from login response.
     * @apiParam {text}     type Type of Job i.e Job/Project
     * @apiParam {int}      job_id Job_id of the job.
     * @apiParam {text}     comment Comment from user response.
     * @apiParam {file}     image[]  (Array) image[0],image[1]....

     * @apiError Message Error Code 400.
     *
     * @apiSuccessExample Success
     *     {
     *          "response_code": 200,
     *          "response_message": "Success",
     *          "response_data": {
     *              "job_history": 1,
     *              "update": true,
     *              "job_history_image": [
     *                  1,
     *                  2,
     *                  3
     *              ]
     *          }
     *      }
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
     */
    public function startJob_post() //Edited on 12-12-2018
    {
        // error_reporting(0);
        $data=$this->post();
        $authValidation = verifyUserAuthTokenTech($this->head('AuthKey'));
        if($authValidation) 
        {
            // echo "<pre>";print_r($data);exit();

            $user_id=$data['user_id'];
            $job_type=strtolower($data['type']);
            $job_id=$data['job_id'];
            $comment=$data['comment'];



            // Image Upload
            $count=count($_FILES['image']['name']); 

            $config['upload_path'] = './public/image_file/';
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

                // print_r( $_FILES['img']);exit();

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

            // print_r($img_name);exit();
            
            // Image Upload
            $startTime=date('Y-m-d H:i:s');
            if($user_id!=NULL && $job_type!=NULL && $job_id!=NULL && $img_name!=NULL && $comment!=NULL)
            {
                $result['job_history']=$this->Api_model->insertData('job_history',
                                                                                    array
                                                                                    (
                                                                                    'job_id'=>$job_id,
                                                                                    'job_type'=>$job_type,
                                                                                    'user_id'=>$user_id,
                                                                                    'comment'=>$comment,
                                                                                    'current_status'=>'Ongoing',
                                                                                    'createdat'=>date('Y-m-d h:i:s')
                                                                                    )
                                                                    );

                $result['update']=$this->Api_model->updateJobStatus($job_id,$job_type,'Ongoing');

                // Code for push notifications=====made by Gopal sir-=======edited by Taha 21-12-2018=======
                if($job_type == 'job')
                {
                    $client_id=$this->Api_model->details_job($job_id);
                }
                else
                {
                    $client_id=$this->Api_model->details_project($job_id);
                }
                
                 // echo "<pre>";print_r($client_id);exit();
                $client_token=$this->Api_model->selectData('clients','push_token',array('client_id'=>$client_id[0]->client_id));
                // echo "<pre>";print_r($client_token);exit();
                $title='Ongoing';
                $body='Your Appointment #'.$job_id.' for '.$client_id[0]->category.' in '.$client_id[0]->service.' is ongoing';
                $badge=1;


                if($job_type == 'project')
                {
                    $app_id=$this->Api_model->getAppId($job_id);

                    $ins_noti=$this->Api_model->insertData('app_notification',array(
                                                                                    'app_id'=>$app_id[0]->app_id,
                                                                                    'client_id'=>$client_id[0]->client_id,
                                                                                    'title'=>$title,
                                                                                    'body'=>$body,
                                                                                    'read_status'=>'unread',
                                                                                    'job_type'=>$job_type,
                                                                                    'app_type'=>'cust',
                                                                                    'createdat'=>date('Y-m-d h:i:s')
                                                                                    )
                                                                    );
                }
                else
                {
                    $ins_noti=$this->Api_model->insertData('app_notification',array(
                                                                                    'app_id'=>$job_id,
                                                                                    'client_id'=>$client_id[0]->client_id,
                                                                                    'title'=>$title,
                                                                                    'body'=>$body,
                                                                                    'read_status'=>'unread',
                                                                                    'job_type'=>$job_type,
                                                                                    'app_type'=>'cust',
                                                                                    'createdat'=>date('Y-m-d h:i:s')
                                                                                    )
                                                                    );
                }

                // print_r($ins_noti);exit();
                // echo "<pre>";print_r($client_token);exit();
                if($client_token)
                {
                    sendPush($client_token[0]->push_token,$title,$body,$badge);
                }
                
                // ==========================================================================================

               // $result['startTime']=$startTime;
                // To automatically change the status of Milestones and Project
                if($job_type=='project')
                {
                    $mile_id=$this->Api_model->selectData('milestones_job','mile_id',array('mile_job_id'=>$job_id));
                    // print_r($mile_id[0]->mile_id);exit();
                    if(count($this->Api_model->selectData('milestones','mile_id',array('mile_id'=>$mile_id[0]->mile_id,'mile_status'=>'Requested'))==0))
                    {
                        $this->Api_model->updateData('milestones',array('mile_status'=>'Ongoing'),array('mile_id'=>$mile_id[0]->mile_id));
                        $app_id=$this->Api_model->selectData('milestones','app_id',array('mile_id'=>$mile_id[0]->mile_id));
                         // print_r($app_id[0]->app_id);exit();
                        if(count($this->Api_model->selectData('job_appointment','app_id',array('app_id'=>$app_id[0]->app_id,'app_status'=>'Requested'))==0))
                        {
                            $this->Api_model->updateData('job_appointment',array('app_status'=>'Ongoing'),array('app_id'=>$app_id[0]->app_id));
                        }
                        
                    }    
                }

                $c=count($img_name);

                for ($i=0; $i < $c ; $i++) 
                { 
                    $result['job_history_image'][$i]=$this->Api_model->insertData('job_history_image',array('image_name'=>$img_name[$i],'job_history_id'=>$result['job_history'],'createdat'=>date('Y-m-d h:i:s')));
                }

                if($result['job_history'] && $result['update'])
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
     * @api {post} webservice/stopJob When the Technician Stops the Job 
     * @apiVersion 0.1.0
     * @apiName Job Completed
     * @apiGroup Technician
     * 
     * @apiParam {int} user_id                              User id from login response.
     * @apiParam {int} job_id                               Job_id of the job.
     * @apiParam {text} type                                Type of Job i.e job/project
     * @apiParam {text} status                              Status of Job i.e Pending/Completed
     * @apiParam {time} time                                TIME of the timer.
     * @apiParam {text} comment                             Comment from user response.
     * @apiParam {file} image[]                             (Array) image[0],image[1]....
     * @apiParam {array} material_use[n]                    Multidimensional array for materials [product_id],[product_name],[product_price],[product_quantity],[product_tax] FYI: 'n' is the index of material.
     * @apiError Message Error Code 400.
     * 
     * @apiSuccessExample Success
     *      {
     *          "response_code": 200,
     *          "response_message": "Success",
     *          "response_data": {
     *              "material_used": [
     *                  2
     *              ],
     *              "job_history": 2,
     *              "update": true,
     *              "job_history_image": [
     *                  4,
     *                  5
     *              ]
     *          }
     *      }
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
     */
    public function stopJob_post() //Edited on 12-12-2018
    {
        // error_reporting(0);
        $data=$this->post();
        $authValidation = verifyUserAuthTokenTech($this->head('AuthKey'));
        if($authValidation)
        {
            // echo "<pre>";print_r($data);exit();
            $user_id=$data['user_id'];
            $job_id=$data['job_id'];
            $job_type=strtolower($data['type']);
            $comment=$data['comment'];
            $material=$data['material_use'];
            $time=$data['time'];
            $status=$data['status'];
            // echo"<pre>";print_r($job_type);exit();


            // Image Upload
            $count=count($_FILES['image']['name']); 

            $config['upload_path'] = './public/image_file/';
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

                // print_r( $_FILES['img']);exit();

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

            // print_r($img_name);exit();
            
            // Image Upload

            if($user_id!=NULL && $job_type!=NULL && $job_id!=NULL && $comment!=NULL && $material!=NULL && $status!=NULL)
            {

                $result['job_history']=$this->Api_model->insertData('job_history',
                                                                                    array
                                                                                    (
                                                                                    'job_id'=>$job_id,
                                                                                    'job_type'=>$job_type,
                                                                                    'user_id'=>$user_id,
                                                                                    'comment'=>$comment,
                                                                                    'time'=>$time,
                                                                                    'current_status'=>$status,
                                                                                    'createdat'=>date('Y-m-d h:i:s')
                                                                                    )
                                                                    );
                // print_r($result['job_history']);exit();

                // Code for push notifications=====made by gopal sir-=======edited by taha 21-12-2018=======
                if($job_type == 'job')
                {
                    $client_id = $this->Api_model->details_job($job_id);   
                }
                if($job_type == 'project')
                {
                    $client_id = $this->Api_model->details_project($job_id);
                }
                // print_r($client_id);exit();
                $client_token=$this->Api_model->selectData('clients','push_token',array('client_id'=>$client_id[0]->client_id));
                $title=$status;
                $body='Your Appointment #'.$job_id.' for '.$client_id[0]->category.' in '.$client_id[0]->service.' is '.$status;
                $badge=1;
                $ins_noti=$this->Api_model->insertData('app_notification',array(
                                                                                    'app_id'=>$job_id,
                                                                                    'client_id'=>$client_id[0]->client_id,
                                                                                    'title'=>$title,
                                                                                    'body'=>$body,
                                                                                    'read_status'=>'unread',
                                                                                    'job_type'=>$job_type,
                                                                                    'app_type'=>'cust',
                                                                                    'createdat'=>date('Y-m-d h:i:s')
                                                                                    )
                                                                    );
                if($client_token)
                {
                    sendPush($client_token[0]->push_token,$title,$body,$badge);
                }
                
                // ============================================================================================


                for($i=0;$i<count($material);$i++) 
                {
                    $insert = array(
                                        'job_id'           =>  $job_id,
                                        'job_history_id'   =>  $result['job_history'],
                                        'job_type'         =>  $job_type,
                                        'user_id'          =>  $user_id,
                                        'product_id'       =>  $material[$i]['product_id'],
                                        'product_name'     =>  $material[$i]['product_name'],
                                        'product_price'    =>  $material[$i]['product_price'],
                                        'product_quantity' =>  $material[$i]['product_quantity'],
                                        'product_tax'      =>  $material[$i]['product_tax'],
                                        'createdat'        =>  date('Y-m-d h:i:s')
                                    );
                    $result['material_used'][$i]=$this->Api_model->insertData('material_used',$insert);
                }

                $result['update']=$this->Api_model->updateJobStatus($job_id,$job_type,$status);

                // print_r($result);exit();

                // To automatically change the status of Milestones and Project
                if($job_type=='project' && $status=='Completed')
                {
                    $mile_id=$this->Api_model->selectData('milestones_job','mile_id',array('mile_job_id'=>$job_id));
                     // print_r($mile_id[0]->mile_id);exit();
                    if(count($this->Api_model->checkMileJobStatus($mile_id[0]->mile_id))==0)
                    {
                        $this->Api_model->updateData('milestones',array('mile_status'=>'Completed'),array('mile_id'=>$mile_id[0]->mile_id));
                        $app_id=$this->Api_model->selectData('milestones','app_id',array('mile_id'=>$mile_id[0]->mile_id));
                         // print_r($app_id[0]->app_id);exit();
                        if(count($this->Api_model->checkProjectMileStatus($app_id[0]->app_id))==0)
                        {
                            $this->Api_model->updateData('job_appointment',array('app_status'=>'Completed'),array('app_id'=>$app_id[0]->app_id));
                        }
                        
                    }    
                }

                $c=count($img_name);

                for ($i=0; $i < $c ; $i++) 
                { 
                    $result['job_history_image'][$i]=$this->Api_model->insertData('job_history_image',array('image_name'=>$img_name[$i],'job_history_id'=>$result['job_history'],'createdat'=>date('Y-m-d h:i:s')));
                }


                if($result['job_history'] && $result['update'] && $result['material_used'])
                {
                    $this->set_response(array('response_code'=>200,'response_message'=>'Success','response_data'=>$result));
                }
                else
                {
                    $this->set_response(array('response_code'=>400,'response_message'=>'No Data Found','response_data'=>array()));
                }      
            }
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()));
            }
        }
    }

    //Changes made by Taha 30-11-2018 ==================================================================
    /**
     * @api {post} webservice/pendingJobDetails Pending Job Details
     * @apiVersion 0.1.0
     * @apiName Pending Job Details
     * @apiGroup Technician
     *
     * @apiParam {int}    job_id    Job_id 
     * @apiParam {string} job_type Type of Job i.e job/project
     *  
     * @apiError Message Error Code 400.
     * 
     * @apiSuccessExample Success
     *   {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "job_details": [
     *              {
     *                  "app_date": "2018-11-15",
     *                  "app_time": "08:20:00",
     *                  "app_name": "Gopal  Bhuva",
     *                  "app_company": "",
     *                  "app_address": "Dev Aurum Complex, Near Anand Nagar ",
     *                  "app_address1": "Crossroad, Prahlad Nagar",
     *                  "state_name": "Karnataka",
     *                  "city_name": "Bengaluru",
     *                  "app_pincode": "560001",
     *                  "app_mobile": "7779056790",
     *                  "app_phone": "",
     *                  "app_email": "gopal@hmmbiz.com",
     *                  "app_desc": "Test",
     *                  "app_status": "Completed",
     *                  "job_id": "17",
     *                  "category": "Emergency Service",
     *                  "service": "Leak",
     *                  "type": "job"
     *              }
     *          ],
     *          "work_items": [
     *              {
     *                  "job_history_id": "9",
     *                  "job_id": "17",
     *                  "job_type": "job",
     *                  "current_status": "Ongoing",
     *                  "createdat": "2018-11-28 02:03:32"
     *              },
     *              {
     *                  "job_history_id": "10",
     *                  "job_id": "17",
     *                  "job_type": "job",
     *                  "current_status": "Pending",
     *                  "createdat": "2018-11-28 03:08:13"
     *              }
     *          ]
     *      }
     *  }
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
     */
    public function pendingJobDetails_post() //Job details for the Pending Jobs
    {
        $data=$this->post();
        $authValidation = verifyUserAuthTokenTech($this->head('AuthKey'));
        if($authValidation)
        {
            $job_id     =$data['job_id'];
            $job_type   =$data['job_type'];

            if($job_id!=null && $job_type!=null)
            {
                if($job_type='job')
                {
                    $result['job_details']=$this->Api_model->details_job($job_id);
                    
                    $result['work_items']=$this->Api_model->selectData('job_history','job_history_id,job_id,job_type,current_status,createdat',array('job_id'=>$job_id,'job_type'=>$job_type));

                }

                else
                {
                    $result['job_details']=$this->Api_model->details_job($job_id);
                    
                    $result['work_items']=$this->Api_model->selectData('job_history','job_history_id,job_id,job_type,current_status,createdat',array('job_id'=>$job_id,'job_type'=>$job_type));
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
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        }
    }

    /**
     * @api {post} webservice/completedJobDetails Completed Job Details
     * @apiVersion 0.1.0
     * @apiName Completed Job Details
     * @apiGroup Technician
     *
     * @apiParam {int}    job_id    Job_id 
     * @apiParam {string} job_type  Type of Job i.e job/project
     *  
     * @apiError Message Error Code 400.
     * 
     * @apiSuccessExample Success
     *   {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "job_details": [
     *              {
     *                  "app_date": "2018-11-15",
     *                  "app_time": "08:20:00",
     *                  "app_name": "Gopal  Bhuva",
     *                  "app_company": "",
     *                  "app_address": "Dev Aurum Complex, Near Anand Nagar ",
     *                  "app_address1": "Crossroad, Prahlad Nagar",
     *                  "state_name": "Karnataka",
     *                  "city_name": "Bengaluru",
     *                  "app_pincode": "560001",
     *                  "app_mobile": "7779056790",
     *                  "app_phone": "",
     *                  "app_email": "gopal@hmmbiz.com",
     *                  "app_desc": "Test",
     *                  "app_status": "Completed",
     *                  "job_id": "17",
     *                  "category": "Emergency Service",
     *                  "service": "Leak",
     *                  "type": "job"
     *              }
     *          ],
     *          "work_items": [
     *              {
     *                  "job_history_id": "9",
     *                  "job_id": "17",
     *                  "job_type": "job",
     *                  "current_status": "Ongoing",
     *                  "createdat": "2018-11-28 02:03:32"
     *              },
     *              {
     *                  "job_history_id": "10",
     *                  "job_id": "17",
     *                  "job_type": "job",
     *                  "current_status": "Pending",
     *                  "createdat": "2018-11-28 03:08:13"
     *              }
     *          ],
     *          "ratings": [
     *              {
     *                  "rating_id": "1",
     *                  "ratings": "4"
     *              }
     *          ]
     *      }
     *  }
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
     */
    public function completedJobDetails_post()  //Job details for the Completed Jobs
    {
        $data=$this->post();
        $authValidation = verifyUserAuthTokenTech($this->head('AuthKey'));
        if($authValidation)
        {
            $job_id     =$data['job_id'];
            $job_type   =$data['job_type'];

            if($job_id!=null && $job_type!=null)
            {
                if($job_type == 'job')
                {
                    $result['job_details']=$this->Api_model->details_job($job_id);
    
                    $result['work_items']=$this->Api_model->selectData('job_history','job_history_id,job_id,job_type,current_status,createdat',array('job_id'=>$job_id,'job_type'=>$job_type));

                    $result['ratings']=$this->Api_model->selectData('ratings','rating_id,ratings',array('job_id'=>$job_id,'job_type'=>$job_type));
                }
                
                else
                {
                    $result['job_details']=$this->Api_model->details_project($job_id);
                    
                    $result['work_items']=$this->Api_model->selectData('job_history','job_history_id,job_id,job_type,current_status,createdat',array('job_id'=>$job_id,'job_type'=>$job_type));

                    $result['ratings']=$this->Api_model->selectData('ratings','rating_id,ratings',array('job_id'=>$job_id,'job_type'=>$job_type));
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
            else
            {
                $this->set_response(array('response_code'=>400,'response_message'=>'Parameter missing','response_data'=>array()), REST_Controller::HTTP_OK); 
            }
        } 
    }

    /**
     * @api {post} webservice/dailyWorkItemsDetails Work Item Details
     * @apiVersion 0.1.0
     * @apiName Work Item Details
     * @apiGroup Technician
     * @apiParam {int}     job_id           Job Id
     * @apiParam {int}     job_history_id   Job History Id.
     * @apiParam {string}  job_type         job/project
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "history": [
     *              {
     *                  "job_history_id": "9",
     *                  "current_status": "Ongoing",
     *                  "comment": "Job Started by me",
     *                  "time": null,
     *                  "createdat": "2018-11-28 02:03:32"
     *              }
     *          ],
     *          "images": [
     *              "http://localhost/vp-admin/public/image_file/images-project.jpg",
     *              "http://localhost/vp-admin/public/image_file/plumbing-upgrades.jpg",
     *              "http://localhost/vp-admin/public/image_file/images-1231.jpg"
     *          ],
     *          "material_used": [
     *              {
     *                  "product_id": "1",
     *                  "product_name": "Pipe",
     *                  "product_quantity": "3"
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
    public function dailyWorkItemsDetails_post()    //Daily Work Item Details created by Taha for Technician 30-11-2018 
    {
        $data=$this->post();
        $authValidation = verifyUserAuthTokenTech($this->head('AuthKey'));
        if($authValidation)
        {
            $job_id     =$data['job_id'];
            $histrory_id=$data['job_history_id'];
            $job_type   =$data['job_type'];

            if($histrory_id!=null && $job_id)
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


                $result['material_used']=$this->Api_model->selectData('material_used','product_id,product_name,product_quantity',array('job_history_id'=>$result['history'][0]->job_history_id,'job_id'=>$job_id,'job_type'=> $job_type));


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
    //Changes made by Taha 30-11-2018 ==================================================================

    /**
     * @api {post} webservice/dailyWorkItemsDetailsUpdate Work Item Details Update
     * @apiVersion 0.1.0
     * @apiName Work Item Details Update
     * @apiGroup Technician
     * @apiParam {int}     user_id          User Id.
     * @apiParam {int}     job_history_id   Job History Id.
     * @apiParam {text}    comment          Comment (Updated) from user response.(Optional)
     * @apiParam {file}    image[]          (Array) image[0],image[1].... (Already existing and newly added)(Optional)
     * @apiParam {array}   material_use[n]  Multidimensional array for materials (Updated) [product_id],[product_name],[product_price],[product_quantity],[product_tax] FYI: 'n' is the index of material.(Optional)
     * @apiSuccessExample Success
     *  {
     *      "response_code": "200",
     *      "response_message": "success",
     *      "response_data": {
     *          "comment_updated": 0,
     *          "delete_images": 1,
     *          "image_updated": [
     *              432,
     *              433
     *          ],
     *          "delete_material": 1,
     *          "material_used_updated": [
     *              290,
     *              291
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
    public function dailyWorkItemsDetailsUpdate_post()    //Daily Work Item Details Update created by Taha for Technician 19-11-2018 
    {
        $data=$this->post();
        $authValidation = verifyUserAuthTokenTech($this->head('AuthKey'));
        if($authValidation)
        {
            if(isset($data['job_history_id']) && $data['job_history_id']!=null && isset($data['user_id']) && $data['user_id']!=null)
            {
                // Update comment==================================== 
                if(isset($data['comment']) && $data['comment'])
                {
                    $result['comment_updated']=$this->Api_model->updateData('job_history',array('comment'=>$data['comment']),array('job_history_id'=>$data['job_history_id']));
                }
                // Update comment==================================== 
                
                // Image Upload======================================
                if(isset($_FILES['image']) && $_FILES['image']!=NULL)
                {
                    // print_r( $_FILES['image']);exit();
                    $count=count($_FILES['image']['name']); 

                    $config['upload_path'] = './public/image_file/';
                    $config['allowed_types'] = '*'; 
                    $this->load->library('upload', $config);                    

                    for ($i=0; $i < $count ; $i++) 
                    { 
                        $_FILES['img']=array(
                            'name'=>$_FILES['image']['name'][$i],
                            'type'=>$_FILES['image']['type'][$i],
                            'tmp_name'=>$_FILES['image']['tmp_name'][$i],
                            'error'=>$_FILES['image']['error'][$i],
                            'size'=>$_FILES['image']['size'][$i]
                        );
                        // print_r( $_FILES['img']);exit();
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

                    if(count($img_name) > 0)
                    {
                        $c=count($img_name);

                        $result['delete_images']=$this->Api_model->deleteData('job_history_image',array('job_history_id'=>$data['job_history_id']));

                        for ($i=0; $i < $c ; $i++) 
                        { 
                            $result['image_updated'][$i]=$this->Api_model->insertData('job_history_image',array('image_name'=>$img_name[$i],'job_history_id'=>$data['job_history_id'],'createdat'=>date('Y-m-d h:i:s')));
                        }
                    }

                }
                else
                {
                    $result['delete_images']=$this->Api_model->deleteData('job_history_image',array('job_history_id'=>$data['job_history_id']));
                }
                // Image Upload======================================

                // Add new material if any===========================
                $history=$this->Api_model->selectData('job_history','job_id,job_type',array('job_history_id'=>$data['job_history_id']));

                $result['delete_material']=$this->Api_model->deleteData('material_used',array('job_history_id'=>$data['job_history_id']));

                if(isset($data['material_use']) && $data['material_use']!=null)
                {
                    $material=$data['material_use'];

                    for($i=0;$i<count($material);$i++)  
                    {
                        $insert = array(
                                            'job_id'           =>  $history[0]->job_id,
                                            'job_history_id'   =>  $data['job_history_id'],
                                            'job_type'         =>  $history[0]->job_type,
                                            'user_id'          =>  $data['user_id'],
                                            'product_id'       =>  $material[$i]['product_id'],
                                            'product_name'     =>  $material[$i]['product_name'],
                                            'product_price'    =>  $material[$i]['product_price'],
                                            'product_quantity' =>  $material[$i]['product_quantity'],
                                            'product_tax'      =>  $material[$i]['product_tax'],
                                            'createdat'        =>  date('Y-m-d h:i:s')
                                        );
                        $result['material_used_updated'][$i]=$this->Api_model->insertData('material_used',$insert);
                    }
                }
                // Add new material if any===========================
                

                if(count($result) > 0)
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

    public function test_post()
    {
        $result=$this->Api_model->selectData('users');
      //  print_r($result[0]->updatedat);exit();
        $userTimezone = new DateTimeZone('Asia/Kolkata');
        $gmtTimezone = new DateTimeZone('GMT');
        $myDateTime = new DateTime($result[0]->updatedat, $gmtTimezone);
        $offset = $userTimezone->getOffset($myDateTime);
        $newdate=strtotime($result[0]->updatedat)+$offset;
        echo date('d-m-Y H:i:s',$newdate);
    }
    
}
