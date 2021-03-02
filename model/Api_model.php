<?php
class Api_model extends CI_Model{
		var $table;
	public function  __construct(){
		parent::__construct();
		$this->load->database();
		$this->table ='services';
	}


	/*
	| -------------------------------------------------------------------
	| check unique fields
	| -------------------------------------------------------------------
	|
	*/
	public function isUnique($field, $value,$id='')
	{
		$this->db->select('*');
		$this->db->from('services');
		$this->db->where($field,$value);
		if($id!='')
		{
			$this->db->where("iUserId != ",$id);
		}
		$this->db->where("is_deleted",0);

		$query = $this->db->get();
		// echo "<pre>";
		// print_r($query);exit();
		$data = $query->num_rows();
		return ($data > 0)?FALSE:TRUE;
	}

	public function fetchUserData($column , $value)
	{
		$this->db->select('*');
		$this->db->from('services');
		$this->db->where($column,$value);
		$this->db->where('is_deleted',0);
		$this->db->where('tStatus',1);
		
		$query = $this->db->get();
		// echo "<pre>";
		// print_r($query);exit();
		$data = (array) $query->first_row();
		$dataArray = array();
		foreach ($data as $key => $value) {
			if($value == null)
				$data[$key] = '';
		}
		return $data;
	}

	public function fetchLastUserId()
	{
		$this->db->select('iUserId');
		$this->db->from('services');
		$this->db->order_by("iUserId","DESC");
		$query = $this->db->get();

		return (array) $query->first_row();
	}

	public function fetchUserAllData($column,$value)
	{
		$this->db->select('u.* ,d.vDeviceToken , d.tDeviceType ');
		$this->db->from('services as u');
 		$this->db->join("device_master as d","u.iUserId = d.iUserId","LEFT");
		$this->db->where($column,$value);
		$this->db->where('is_deleted',0);
		
		$query = $this->db->get();

		// echo "<pre>";
		// print_r($query);exit();
		$data = (array) $query->first_row();

		$dataArray = array();
		foreach ($data as $key => $value) {
			if($value == null)
				$data[$key] = '';
		}
		return $data;
	}


	/*
	| -------------------------------------------------------------------
	| Insert data
	| -------------------------------------------------------------------
	|
	| General function to insert data in table
	|
	*/
	public function insertData($table,$data)
	{
		$result = $this->db->insert($table, $data);
		if($result == 1){
			return $this->db->insert_id();
		}else{
			return false;
		}
	}
	
	/*
	| -------------------------------------------------------------------
	| Update data
	| -------------------------------------------------------------------
	|
	| General function to update data
	|
	*/
	public function updateData($table,$data, $where)
	{
		$this->db->where($where);
		$this->db->update($table, $data);
		if($this->db->affected_rows()!=0){
			return 1;
		}else{
			return 0;
		}
	}	
	
	/*
	| -------------------------------------------------------------------
	| Select data
	| -------------------------------------------------------------------
	|
	| General function to get result by passing nesessary parameters
	|
	*/
	public function selectData($table,$fields='*', $where='', $order_by="", $order_type="", $group_by="", $limit="", $rows="", $type='')
	{
		$this->db->select($fields);
		$this->db->from($table);
		if ($where != "") {
			$this->db->where($where);
		}

		if ($order_by != '') {
			$this->db->order_by($order_by,$order_type);
		}

		if ($group_by != '') {
			$this->db->group_by($group_by);
		}

		if ($limit > 0 && $rows == "") {
			$this->db->limit($limit);
		}
		if ($rows > 0) {
			$this->db->limit($rows, $limit);
		}


		$query = $this->db->get();

		if ($type == "rowcount") {
			$data = $query->num_rows();
		}else{
			$data = $query->result();
		}
		// print_r($this->db->last_query());exit();
		#echo "<pre>"; print_r($this->db->queries); exit;
		$query->result();

		return $data;
	}

	/*
	| --------------------------------------------------------------------
	| Get Technician assigned on Job
	| --------------------------------------------------------------------
	*/
	// public function selectTechJob($user_id,$status=NULL)
	// {
	// 	$this->db->select('
	// 		app.app_date,app.app_time,app.app_name,app.app_company,
	// 		app.app_address,s.state_name,ct.city_name,app.app_pincode,
	// 		app.app_mobile,app.app_phone,app.app_email,app.app_desc,
	// 		ja.job_id as job_assign_id,
	// 		ja.user_id,
	// 		app.app_status,
	// 		app.app_id as job_id,
	// 		cat.cat_name as category,
	// 		ser.service_name as service,
	// 		ser.service_type as type
	// 	');
	// 	//type:used for job type i.e project/job
	// 	//job_id==app_id in case of job
	// 	//job_id==mile_job_id in case of project
	// 	$this->db->from('job_assign as ja');
	// 	$this->db->join('job_appointment as app','app.app_id=ja.app_id');
	// 	$this->db->join('services as ser','app.app_service=ser.service_id');
	// 	$this->db->join('states as s','s.state_id=app.app_state');
	// 	$this->db->join('cities as ct','ct.city_id=app.app_city');
	// 	$this->db->where('app.status','Active');

	// 	if(isset($status))
	// 	{
	// 		$this->db->where('app.app_status',$status);
	// 	}

	// 	$this->db->where('ja.user_id',$user_id);
	// 	$query=$this->db->get();
	// 	return $query->result();
	// }
	public function selectTechJob($user_id,$status=NULL)
	{
		$this->db->select('
			app.app_date,app.app_time,app.app_name,app.app_company,
			app.app_address,app.app_address1,s.state_name,ct.city_name,app.app_pincode,
			app.app_mobile,app.app_phone,app.app_email,app.app_desc,
			ja.job_id as job_assign_id,
			ja.user_id,
			app.app_status,
			app.updatedat as modified_date,
			app.app_id as job_id,
			cat.cat_name as category,
			ser.service_name as service,
			ser.service_type as type
		');
		//type:used for job type i.e project/job
		//job_id==app_id in case of job
		//job_id==mile_job_id in case of project
		$this->db->from('job_assign as ja');
		$this->db->join('job_appointment as app','app.app_id=ja.app_id');

		$this->db->join('services as ser','app.app_service=ser.service_id');
		$this->db->join('category as cat','app.cat_id=cat.cat_id');

		$this->db->join('states as s','s.state_id=app.app_state','left');
		$this->db->join('cities as ct','ct.city_id=app.app_city','left');

		$this->db->where('app.status','Active');

		if(isset($status))
		{
			$this->db->where('app.app_status',$status);
		}

		$this->db->where('ja.user_id',$user_id);
		$query=$this->db->get();
		return $query->result();
	}

	/*
	| --------------------------------------------------------------------
	| Get Technician assigned on Project
	| --------------------------------------------------------------------
	*/
	// public function selectTechProjectJob($user_id,$status=NULL)
	// {
	// 	$this->db->select('
	// 		app.app_date,app.app_time,app.app_name,app.app_company,
	// 		app.app_address,s.state_name,ct.city_name,app.app_pincode,
	// 		app.app_mobile,app.app_phone,app.app_email,app.app_desc,
	// 		app.app_id,
	// 		mile.mile_id,mile.mile_name,
	// 		mile_job_name,
	// 		ja.mile_jobassign_id,
	// 		ja.user_id,
	// 		app.app_status,
	// 		ja.mile_job_id as job_id,
	// 		mj.mile_job_status,
	// 		ser.service_type as type
	// 	');
	// 	//type:used for job type i.e project/job
	// 	//job_id==app_id in case of job
	// 	//job_id==mile_job_id in case of project
	// 	$this->db->from('milestones_job_technician as ja');
	// 	$this->db->join('milestones_job as mj','mj.mile_job_id=ja.mile_job_id');
	// 	$this->db->join('milestones as mile','mj.mile_id=mile.mile_id');
	// 	$this->db->join('job_appointment as app','app.app_id=mile.app_id');
	// 	$this->db->join('services as ser','app.app_service=ser.service_id');
	// 	$this->db->join('states as s','s.state_id=app.app_state');
	// 	$this->db->join('cities as ct','ct.city_id=app.app_city');
	// 	$this->db->where('app.status','Active');

	// 	if(isset($status))
	// 	{
	// 		$this->db->where('mj.mile_job_status',$status);
	// 	}

	// 	$this->db->where('ja.user_id',$user_id);
	// 	$query=$this->db->get();
	// 	return $query->result();
	// }
	public function selectTechProjectJob($user_id,$status=NULL)
	{
		$this->db->select('
			app.app_date,app.app_time,app.app_name,app.app_company,
			app.app_address,app.app_address1,s.state_name,ct.city_name,app.app_pincode,
			app.app_mobile,app.app_phone,app.app_email,app.app_desc,
			app.app_id,
			mj.updatedat as modified_date,
			mile.mile_id,mile.mile_name,
			mile_job_name,
			ja.mile_jobassign_id,
			ja.user_id,
			app.app_status,
			ja.mile_job_id as job_id,
			mj.mile_job_status,
			cat.cat_name as category,
			ser.service_name as service,
			ser.service_type as type
		');
		//type:used for job type i.e project/job
		//job_id==app_id in case of job
		//job_id==mile_job_id in case of project
		$this->db->from('milestones_job_technician as ja');
		$this->db->join('milestones_job as mj','mj.mile_job_id=ja.mile_job_id');
		$this->db->join('milestones as mile','mj.mile_id=mile.mile_id');
		$this->db->join('job_appointment as app','app.app_id=mile.app_id');

		$this->db->join('services as ser','app.app_service=ser.service_id');
		$this->db->join('category as cat','app.cat_id=cat.cat_id');

		$this->db->join('states as s','s.state_id=app.app_state');
		$this->db->join('cities as ct','ct.city_id=app.app_city');

		$this->db->where('app.status','Active');

		if(isset($status))
		{
			$this->db->where('mj.mile_job_status',$status);
		}

		$this->db->where('ja.user_id',$user_id);
		$query=$this->db->get();
		return $query->result();
	}

	/*
	| --------------------------------------------------------------------
	| Update Status of Job (job/project)
	| --------------------------------------------------------------------
	*/
	public function updateJobStatus($job_id,$job_type,$status)
	{
		if($job_type=='job')
		{
			$this->db->set('app_status',$status);
			$this->db->where('app_id',$job_id);
			return $this->db->update('job_appointment');
		}
		else
		{
			$this->db->set('mile_job_status',$status);
			$this->db->where('mile_job_id',$job_id);
			return $this->db->update('milestones_job');
		}
	}

	/*
	| --------------------------------------------------------------------
	| Update Start time of Job (job/project)
	| --------------------------------------------------------------------
	*/
	public function updateStarttime($job_id,$job_type,$time)
	{
		if($job_type=='job')
		{
			$this->db->set('start_time',$time);
			$this->db->where('app_id',$job_id);
			return $this->db->update('job_appointment');
		}
		else
		{
			$this->db->set('start_time',$time);
			$this->db->where('mile_job_id',$job_id);
			return $this->db->update('milestones_job');
		}
	}
	
	/*
	| --------------------------------------------------------------------
	| Customer sign in api
	| --------------------------------------------------------------------
	*/
	public function cussignin($table,$fields='*', $where=''){
		$this->db->select($fields);
		$this->db->from('clients');
		$this->db->join('countries as c','c.country_id=clients.country','left');
		$this->db->join('states as s','s.state_id=clients.state','left');
		$this->db->join('cities as ct','ct.city_id=clients.city','left');
		$this->db->where($where);
		$query = $this->db->get();
		$result= $query->result();	
		return $result;
	}

	/*
	| -------------------------------------------------------------------
	| Get Customer Application List (Status Wise)
	| -------------------------------------------------------------------
	*/	
	public function getCustomerApp($client_id,$app_status=''){
		$this->db->select("app.app_id as app_id,ser.service_type as app_type,cat.cat_name,ser.service_name,app.app_status,app.createdat,app.updatedat,c.city_name,app.app_location");
		$this->db->from('job_appointment as app');
		$this->db->join('cities as c','c.city_id=app.app_city');
		// $this->db->join('job_assign as js','js.app_id=app.app_id','left');
		$this->db->join('services as ser','ser.service_id=app.app_service');
		$this->db->join('category cat','app.cat_id=cat.cat_id');
		$this->db->where('app.client_id',$client_id);		
		if($app_status!='')
		{
			$this->db->where('app.app_status',$app_status);
			if($app_status=='Requested'){
				$this->db->or_where('app.app_status','Unassigned');
			}
		}
		$query = $this->db->get();
		$result= $query->result();	
		return $result;					
	}

	/*
	| -------------------------------------------------------------------
	| Get Details of application 
	| -------------------------------------------------------------------
	*/
	public function getCustomerAppdetails($app_id){

		$this->db->select("
			app.app_id as app_id,
			app.app_status,
			ser.service_type as app_type,
			ser.service_name,
			cat.cat_name,
			app.app_date,
			app.app_time,
			app.app_phone,
			app.app_mobile,
			app.app_email,
			app.app_location,
			app.app_address,
			app.app_address1,
			ct.city_name,
			s.state_name,
			c.country_name,
			app.app_desc,
			app.status as status,
			app.internal_app_desc
		");

		$this->db->from('job_appointment as app');

		$this->db->join('services as ser','app.app_service=ser.service_id');
		$this->db->join('category as cat','app.cat_id=cat.cat_id');

		$this->db->join('states as s','s.state_id=app.app_state');
		$this->db->join('cities as ct','ct.city_id=app.app_city');
		$this->db->join('countries as c','c.country_id=app.app_country');


		$this->db->where('app.app_id',$app_id);

		$query = $this->db->get();
		$result= $query->result();	
		return $result;			
	}

	/*
	| -------------------------------------------------------------------
	| Delete data
	| -------------------------------------------------------------------
	|
	| General function to delete the records
	|
	*/
	public function deleteData($table,$where)
	{
		if($this->db->delete($table,$where)){
			return 1;
		}else{
			return 0;
		}
	}

	/*
	| -------------------------------------------------------------------
	| Plumbers working on Job
	| -------------------------------------------------------------------
	|
	| Function to get the list of plumbers working on a job
	|
	*/
	// public function getPlumberJob($app_id)
	// {
	// 	$this->db->select('u.user_id,u.name');
	// 	$this->db->from('users as u','u.role=6');
	// 	$this->db->join('job_assign as ja','u.user_id=ja.user_id AND ja.app_id ='.$app_id.'');
	// 	$query=$this->db->get();
	// 	return $query->result();
	// }

	public function getPlumberJob($job_history_id)
	{
		$this->db->select('u.user_id,u.name');
		$this->db->from('users as u','u.role=6');
		$this->db->join('job_history as his','u.user_id=his.user_id AND his.job_history_id ='.$job_history_id.'');
		$query=$this->db->get();
		return $query->result();
	}

	// 30-11-2018
	public function details_job($job_id)
	{
		$this->db->select('
			app.app_date,app.app_time,app.app_name,app.app_company,
			app.app_address,app.app_address1,s.state_name,ct.city_name,app.app_pincode,
			app.app_mobile,app.app_phone,app.app_email,app.app_desc,app.client_id,
			
			app.app_status,
			app.app_id as job_id,
			cat.cat_name as category,
			ser.service_name as service,
			ser.service_type as type
		');

		$this->db->from('job_appointment as app');

		$this->db->join('services as ser','app.app_service=ser.service_id');
		$this->db->join('category as cat','app.cat_id=cat.cat_id');

		$this->db->join('states as s','s.state_id=app.app_state');
		$this->db->join('cities as ct','ct.city_id=app.app_city');

		$this->db->where('app.status','Active');

		$this->db->where('app.app_id',$job_id);

		$query=$this->db->get();

		return $query->result();
	}

	// 30-11-2018
	public function details_project($job_id)
	{
		$this->db->select('
			app.app_date,app.app_time,app.app_name,app.app_company,
			app.app_address,s.state_name,ct.city_name,app.app_pincode,
			app.app_mobile,app.app_phone,app.app_email,app.app_desc,
			app.app_id,app.client_id,app.app_status,

			mile.mile_id,mile.mile_name,
			mj.mile_job_name,

			
			mj.mile_job_id as job_id,
			mj.mile_job_status,
			cat.cat_name as category,
			ser.service_name as service,
			ser.service_type as type
		');

		$this->db->from('milestones_job as mj');
		$this->db->join('milestones as mile','mj.mile_id=mile.mile_id');
		$this->db->join('job_appointment as app','app.app_id=mile.app_id');

		$this->db->join('services as ser','app.app_service=ser.service_id');
		$this->db->join('category as cat','app.cat_id=cat.cat_id');

		$this->db->join('states as s','s.state_id=app.app_state');
		$this->db->join('cities as ct','ct.city_id=app.app_city');

		$this->db->where('app.status','Active');

		$this->db->where('mj.mile_job_id',$job_id);

		$query=$this->db->get();

		return $query->result();
	}

	// Photographic memories section query (JOB)
	public function jobPhotographs($job_id)
	{
		$this->db->select('
			img.image_id,
			img.image_name
		');

		$this->db->from('job_history_image as img');
		$this->db->join('job_history as his','his.job_history_id = img.job_history_id');

		$this->db->where('his.job_id',$job_id);

		$query=$this->db->get();

		return $query->result();
	}

	// Photographic memories section query (Project)
	public function projectPhotographs($app_id)
	{
		$this->db->select('
			img.image_id,
			img.image_name
		');

		$this->db->from('job_history_image as img');
		$this->db->join('job_history as his','his.job_history_id = img.job_history_id');
		$this->db->join('milestones_job as mj','his.job_id = mj.mile_job_id');
		$this->db->join('milestones as mile','mj.mile_id = mile.mile_id');
		$this->db->join('job_appointment as app','mile.app_id = app.app_id');

		$this->db->where('app.app_id',$app_id);

		$query=$this->db->get();

		return $query->result();
	}

	// Dailywork item list section (Project)
	public function projectWorkItems($app_id)
	{
		$this->db->select('
			his.job_history_id,
			his.current_status,
			his.createdat,
			mj.mile_job_id,
			mj.mile_job_name,
			mile.mile_id,
			mile.mile_name
		');

		$this->db->from('job_history as his');
		$this->db->join('milestones_job as mj','his.job_id = mj.mile_job_id');
		$this->db->join('milestones as mile','mj.mile_id = mile.mile_id');
		$this->db->join('job_appointment as app','mile.app_id = app.app_id');

		$this->db->where('his.job_type','project');
		$this->db->where('app.app_id',$app_id);

		$query=$this->db->get();

		return $query->result();
	}

	// Invoice
	public function getInvoiceDetails($app_id)
	{
		$this->db->select('
			app.app_id,
			app.invoice_status,
			in.invoice_id,
			in.invoice_date,
			in.issue_date,
			in.total,
			in.discount
		');

		$this->db->from('job_appointment as app');
		$this->db->join('invoice as in','app.app_id = in.app_id','left');
		// $this->db->join('user_transaction as tran','in.invoice_id = in.app_id','left');

		$this->db->where('app.app_id',$app_id);
		$this->db->where_in('app.invoice_status',array('Approved','Paid'));

		$query=$this->db->get();

		return $query->result();
	}

	public function enddate($app_id,$type)
	{
		$this->db->select('
			his.createdat as end_date
		');

		$this->db->from('job_history as his');
		$this->db->join('job_appointment as app','app.app_id = his.job_id');

		$this->db->where('his.job_id',$app_id);
		$this->db->where('his.job_type',$type);

		$this->db->where('his.current_status','Completed');

		$query=$this->db->get();

		return $query->result();
	}

	public function projectenddate($app_id,$type)
	{
		$this->db->select('
			his.createdat as end_date
		');

		$this->db->from('job_history as his');
		$this->db->join('milestones_job as mj','his.job_id = mj.mile_job_id');
		$this->db->join('milestones as mile','mj.mile_id = mile.mile_id');
		$this->db->join('job_appointment as app','mile.app_id = app.app_id');


		$this->db->where('app.app_id',$app_id);
		$this->db->where('his.job_type',$type);
		$this->db->where('his.current_status','Completed');

		$this->db->order_by('his.job_history_id','DESC');

		$this->db->limit(1);

		$query=$this->db->get();

		return $query->result();
	}

	/*
	| --------------------------------------------------------------------
	| Check the status of Jobs of a particular milestones
	| --------------------------------------------------------------------
	*/
	public function checkMileJobStatus($mile_id)
	{
		$this->db->select('mj.mile_job_id');
		$this->db->from('milestones_job as mj');
		$this->db->join('milestones as mile','mj.mile_id = mile.mile_id');
		$this->db->where('mile.mile_id',$mile_id);
		$this->db->where('mj.mile_job_status!=','Completed');
		$query=$this->db->get();
		return $query->result();
	}

	/*
	| --------------------------------------------------------------------
	| Check the status of Milestones of a particular project
	| --------------------------------------------------------------------
	*/
	public function checkProjectMileStatus($app_id)
	{
		$this->db->select('mile.mile_id');
		$this->db->from('milestones as mile');
		$this->db->join('job_appointment as app','mile.app_id = app.app_id');
		$this->db->where('app.app_id',$app_id);
		$this->db->where('mile.mile_status!=','Completed');
		$query=$this->db->get();
		return $query->result();
	}

	public function fetchInvoicedetail($id)
	{
		$this->db->select('inv.invoice_id,inv.invoice_date,inv.issue_date,inv.material_cost as sub_total_material_cost,inv.service_desc as plumbing_charge_desc,inv.plumbing_charge,inv.sub_total,inv.discount,inv.total,app.app_id as job_id,app.app_name,app.app_location,app.app_address,app.app_mobile,app.app_email,c.city_name, s.state_name, cu.country_name,app.app_desc as your_app_desc');
		$this->db->from('invoice as inv');
		//$this->db->join('invoice_detail as ivnd','ivnd.invoice_id = inv.invoice_id');
		$this->db->join('job_appointment as app','app.app_id = inv.app_id');
		$this->db->join('cities as c','c.city_id=app.app_city');
		$this->db->join('states as s','s.state_id=app.app_state');
		$this->db->join('countries as cu','cu.country_id=app.app_country');
		$this->db->where('inv.app_id', $id);
		$query = $this->db->get();
		$result= $query->result();	
		if(empty($result))
		{
			echo "No Recaords.";
		}
		else
		{
			return $result;
		}
		
	}


	public function getFedRating($app_id)
	{
		$this->db->select();
		$this->db->from();
		$this->db->join();
		$this->db->where();
		$query=$this->db->get();
		return $query->result();
	}

	public function getAppId($job_id)
	{
		$this->db->select('app.app_id');
		$this->db->from('job_appointment as app');
		$this->db->join('milestones as mile','mile.app_id=app.app_id');
		$this->db->join('milestones_job as job','job.mile_id=mile.mile_id');
		$this->db->where('job.mile_job_id',$job_id);
		$query=$this->db->get();
		return $query->result();
	}

	public function getLike($table,$fields='*',$tosearch='',$like='')
	{
		$this->db->select($fields);
		$this->db->from($table);
		$this->db->like($tosearch,$like);
		$query=$this->db->get();
		return $query->result();
	}

	
}
?>
