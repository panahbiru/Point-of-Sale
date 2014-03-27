<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends CI_Controller{
     
    function __construct() {
       
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('unit_test');              
        $this->load->library('posnic');  
        $this->load->helper(array('form', 'url'));
        $this->load->library('poslanguage');                 
        $this->poslanguage->set_language();
    }
    function index(){
        $this->load->library('poslanguage');                 
        $this->poslanguage->set_language();
        
        $this->get_pos_users_details();
     //$this->load->view('sample');
       //$this->users_data_table();
        
       // $this->pos_users_testing();
    } 
    function photo_upload($name){
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size']	= '10000';
		$config['max_width']  = '11024';
		$config['max_height']  = '3768';
                $config['file_name'] = $name;

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload())
		{
			$error = array('error' => $this->upload->display_errors());
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());
		}
              
                      $upload_data = $this->upload->data();
                      return  $file_name =$upload_data['file_name'];
	}
   
  
    function pos_users_testing(){
        $this->load->model('pos_users_model');
        $test= $this->pos_users_model->get();
        $expected_result ='is_true';

        $test_name = 'Adds one plus one';
       
        $this->unit->run($test, $expected_result, $test_name);
        return $this->unit->report();
        
        
        
    }
    function users_data_table(){     
	$aColumns = array( 'guid','email','username',  'first_name','last_name','phone', 'email',  'user_active', 'user_id','user_active', );	
	$start = "";
        $end="";
	if ( $this->input->get_post('iDisplayLength') != '-1' )	{
		$start = $this->input->get_post('iDisplayStart');
		$end=	 $this->input->get_post('iDisplayLength');              
	}	
	$order="";
	if ( isset( $_GET['iSortCol_0'] ) )
	{	
		for ( $i=0 ; $i<intval($this->input->get_post('iSortingCols') ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($this->input->get_post('iSortCol_'.$i)) ] == "true" )
			{
				$order.= $aColumns[ intval( $this->input->get_post('iSortCol_'.$i) ) ]." ".$this->input->get_post('sSortDir_'.$i ) .",";
			}
		}
		
                $order = substr_replace( $order, "", -1 );
                
	}
	
	$like = array();
	
        if ( $_GET['sSearch'] != "" )
	{
	$like =array('first_name'=>  $this->input->get_post('sSearch'),
            'user_id'=>  $this->input->get_post('sSearch'),
            'email'=>  $this->input->get_post('sSearch'),
            'phone'=>  $this->input->get_post('sSearch'),
            'last_name'=>  $this->input->get_post('sSearch'),
            'email'=>$this->input->get_post('sSearch'));
            
        }
        $this->load->model('core_model');
        $join_where='users_x_branches.user_id=users.guid ';
      
        $rResult1 = $this->core_model->posnic_data_table($end,$start,'users','users_x_branches',$join_where,$_SESSION['Bid'],$_SESSION['Uid'],$order,$like);
        $this->load->model('pos_users_model');
	$iFilteredTotal =  count($rResult1);
	$iTotal = count($rResult1);	
	$output1 = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
	foreach ($rResult1 as $aRow )
	{
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( $aColumns[$i] == "id" )
			{
				$row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
			}
			else if ( $aColumns[$i] != ' ' )
			{
				$row[] = $aRow[$aColumns[$i]];
			}
		}
	$output1['aaData'][] = $row;
	}
       echo json_encode($output1);
    }
    function get_pos_users_details(){         
           
        if($_SESSION['users_per']['access']==1){
         $this->load->model('user_groups');
                    $this->load->model('branch');
                     if($_SESSION['admin']==2){ 
        $data['branch']=$this->branch->get_user_for_branch_admin();
                     }
                     else{
        $data['branch']= $this->branch->get_user_for_branch($_SESSION['Uid']);
                     }
        $data['depa']= $this->user_groups->get_user_groups();  
        $this->load->view('template/app/header'); 
        $this->load->view('header/header');         
        $this->load->view('template/branch',$this->posnic->branches());
        $this->load->view('pos_users_list',$data);
        $this->load->view('template/app/navigation',$this->posnic->modules());
        $this->load->view('template/app/footer');
        }else{
            redirect('home');
        }
    }
  

   
    function upadate_pos_users_details(){
       if($_SESSION['users_per']['edit']==1){ 
       $this->load->library('form_validation');
               
                $this->form_validation->set_rules("first_name",$this->lang->line('first_name'),"required"); 
                $this->form_validation->set_rules('phone', $this->lang->line('phone'), 'required|max_length[10]|regex_match[/^[0-9]+$/]|xss_clean');
                $this->form_validation->set_rules('age', $this->lang->line('age'), 'required|max_length[2]|regex_match[/^[0-9]+$/]|xss_clean');
                $this->form_validation->set_rules("last_name",$this->lang->line('last_name'),"required"); 
                $this->form_validation->set_rules('email', $this->lang->line('email'), 'valid_email|required');                
                $this->form_validation->set_rules('address',$this->lang->line('address'),"required");
                $this->form_validation->set_rules('city',$this->lang->line('city'),"required");
                $this->form_validation->set_rules('state',$this->lang->line('state'),"required");
                $this->form_validation->set_rules('zip',$this->lang->line('zip'),"required");
                $this->form_validation->set_rules('dob',$this->lang->line('date_of'),"required");                 
              //  $this->form_validation->set_rules('department',$this->lang->line('user_groups'),"required");              
                $this->form_validation->set_rules('pos_users_id','pos_users_id',"required");
                $this->form_validation->set_rules('guid','guid',"required");
                $this->form_validation->set_rules('country','country',"required");
               $id=  $this->input->post('guid');	  
	    if ( $this->form_validation->run() !== false ) {
			  $this->load->model('pos_users_model');
                          $first_name=$this->input->post('first_name');
                          $last_name=  $this->input->post('last_name');
                          $email=$this->input->post('email');
			  $username=$this->input->post('pos_users_id');
                          $password=$this->input->post('password');
                          $address=$this->input->post('address');
                          $phone=$this->input->post('phone');
                          $city=$this->input->post('city');
                          $state=$this->input->post('state');
                          $zip=$this->input->post('zip');
                          $country=$this->input->post('country');
                          $user_groups=urldecode($this->input->post('department'));
                          $yourdatetime =$this->input->post('dob');
                          $image_name=$this->input->post('image_name');
                          $age=  $this->input->post('age');
                          $sex= $this->input->post('sex');                          
                          $blood= $this->input->post('blood');                          
                          $dob= strtotime($yourdatetime);  
                         
                             if($this->pos_users_model->user_update_checking($email,$phone,$id)==FALSE){
                           
                                           $file_name='';
                            $this->pos_users_model->update_pos_users($blood,$file_name,$age,$sex,$id,$first_name,$last_name,$username,$address,$city,$state,$zip,$country,$email,$phone,$dob,$password);
                            $deleted_group=$this->input->post('deleted_groups') ;
                            $deleted_groups=array_unique($deleted_group);
                            for($j=0;$j<count($deleted_groups);$j++){
                                $this->pos_users_model->remove_user_groups($deleted_groups[$j],$id);
                            }        
                            $user_branchs=  $this->input->post('user_branchs');
                            $user_branchs=  array_unique($user_branchs);
                            for($j=0;$j<count($user_branchs);$j++){
                                $this->pos_users_model->add_user_branchs_for_user($user_branchs[$j],$id);
                            }
                                echo 'TRUE';
                         
                            }else{
                               echo "ALREADY";
                            }   
    }else{
           echo "FALSE";       
        }
       }else{
           echo "NOOP";
         
       }
       if(isset($_POST['Cancel'])){
           redirect('users');
       }
}
    function edit_users($id){
        $user_data=array();
        $this->load->model('pos_users_model');
        $user_data= $this->pos_users_model->get_user_details($id); 
     
    
     echo json_encode($user_data);
}
    function update_user_branch($id,$depapartment){   
     $this->load->model('branch');
     $this->branch->delete_user_branch($id);
              
           $new_depa=array();
           $branch=array();        
           $bid=array();
           $bid = explode(' ',$depapartment);
           $l=0;
           for($i=1;$i<count($bid);$i++){
               $depart=array();
               $depart=explode('.',$bid[$i]);
                        $branch[$l]=$depart[0];
                       $l++;               
               }               
               $arr=$branch;
        $len = count($arr);
        for ($i = 0; $i < $len; $i++) {
        $temp = $arr[$i];
        $j = $i;
        for ($k = 0; $k < $len; $k++) {
            if ($k != $j) {
            if ($temp == $arr[$k]) {               
                $arr[$k]=" ";                
            }
            }
        }
        }
$r=0;
        for ($i = 0; $i < $len; $i++) {
       if($arr[$i]==" "){           
       }
       else{
            $new_depa[$r]=$arr[$i];
           $r++;
       }
        }           
            for($k=0;$k<count($new_depa);$k++)
            {
               $this->branch->set_branch($id,$new_depa[$k]);
            }
}
    function update_user_user_groups($id,$depapartment){
     
     $this->load->model('user_groups');
     $this->user_groups->delete_user_depart($id);
     
           $bid=array();
           $bid = explode(' ',$depapartment);
           for($i=1;$i<count($bid);$i++){
               $depart=array();
               $depart=explode('.',$bid[$i]);
                            
               $this->user_groups->set_user_groups($id,$depart[1],$depart[0]);
           }
             

    
}
    function do_upload($id){
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size']	= '100';
		$config['max_width']  = '1024';
		$config['max_height']  = '768';

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload())
		{
			$error = array('error' => $this->upload->display_errors());
                        $file_name='null';
			$this->after_uploading($id, $error);
		}
		else
		{
                   
                      $upload_data = $this->upload->data();
                      $file_name =$upload_data['file_name'];
                      $error="";
                      $this->after_uploading($id, $error,$file_name);
			
		}
                
	}
    function after_uploading($id,$error,$file_name){
            
                $data['error']=$error;
                $data['file_name']=$file_name;
                $this->load->model('pos_users_model');
                $this->load->model('branch');
                $this->load->model('user_groups');
                $data['row']=  $this->pos_users_model->edit_pos_users($id); 
               
                $data['selected_branch']=$this->branch->get_selected_branch($id);
                $data['selected_depart']=$this->user_groups->get_user_depart($id);
                
                $data['branch']= $this->branch->get_user_for_branch($_SESSION['Uid']);
                $data['depa']= $this->user_groups->get_user_groups(); 
                $this->load->view('template/header');
                $this->load->view('edit_pos_users_details',$data);
                $this->load->view('template/footer');
                
        }
   
    function add_pos_users_details(){
            
           if($_SESSION['users_per']['add']==1){                     
                $this->load->library('form_validation');
               
                $this->form_validation->set_rules("first_name",$this->lang->line('first_name'),"required"); 
                $this->form_validation->set_rules('phone', $this->lang->line('phone'), 'required|max_length[10]|regex_match[/^[0-9]+$/]|xss_clean');
                $this->form_validation->set_rules('age', $this->lang->line('age'), 'required|max_length[2]|regex_match[/^[0-9]+$/]|xss_clean');
                $this->form_validation->set_rules("last_name",$this->lang->line('last_name'),"required"); 
                $this->form_validation->set_rules('email', $this->lang->line('email'), 'valid_email|required');
                $this->form_validation->set_rules('password',$this->lang->line('password'),"required");
                $this->form_validation->set_rules('confirm_password',$this->lang->line('confirm_password'),"required");
                $this->form_validation->set_rules('address',$this->lang->line('address'),"required");
                $this->form_validation->set_rules('city',$this->lang->line('city'),"required");
                $this->form_validation->set_rules('state',$this->lang->line('state'),"required");
                $this->form_validation->set_rules('zip',$this->lang->line('zip'),"required");
                $this->form_validation->set_rules('pos_users_id',$this->lang->line('user_name'),"required");
                $this->form_validation->set_rules('country',$this->lang->line('country'),"required");
                $this->form_validation->set_rules('user_groups[]',$this->lang->line('user_groups'),"required");
                $this->form_validation->set_rules('user_branchs[]',$this->lang->line('user_branchs'),"required");	  
	    if ( $this->form_validation->run() !== false ) {        
			  $this->load->model('pos_users_model');
                          $first_name=$this->input->post('first_name');
                          $last_name=  $this->input->post('last_name');
                          $email=$this->input->post('email');
			  $username=$this->input->post('pos_users_id');
                          $password=$this->input->post('confirm_password');
                          $address=$this->input->post('address');
                          $phone=$this->input->post('phone');
                          $city=$this->input->post('city');
                          $state=$this->input->post('state');
                          $zip=$this->input->post('zip');
                          $country=$this->input->post('country');
                          $user_groups=urldecode($this->input->post('depa'));
                          $yourdatetime =$this->input->post('dob');                          
                          $age=  $this->input->post('age');
                          $sex= $this->input->post('sex');
                          $blood= $this->input->post('blood');
                          $dob= strtotime($yourdatetime);
                          $created_by=$_SESSION['Uid'];
                          $this->load->model('pos_users_model');
                          if($this->pos_users_model->user_checking($email,$username,$dob,$phone)==FALSE){
                          $id= $this->pos_users_model->add_new_pos_users($blood,$dob,$created_by,$sex,$age,$first_name,$last_name,$username,$password,$address,$city,$state,$zip,$country,$email,$phone,$username);
                          $this->add_user_branches($id,$user_groups);
                          $this->add_user_user_groups($id,$user_groups); 
                          $user_groups=$this->input->post('user_groups');
                          for($i=0;$i<count($user_groups);$i++){
                              $this->pos_users_model->add_user_groups_for_user($user_groups[$i],$id);
                          }
                         $user_branchs=  $this->input->post('user_branchs');
                         $user_branchs=  array_unique($user_branchs);
                         for($j=0;$j<count($user_branchs);$j++){
                           
                                 $this->pos_users_model->add_user_branchs_for_user($user_branchs[$j],$id);
                            
                         }
                             echo 'true';
                          }
                          else{
                              echo 'already';
                                                  
                          }
            }else{
                  echo "false";
              }    
                            
        }else{
               echo "noop";
           }
        }
    function add_user_user_groups($id,$depapartment){
           
           $this->load->model('user_groups');
           $bid=array();
           $bid = explode(' ',$depapartment);
           for($i=1;$i<count($bid);$i++){
               $depart=array();
               $depart=explode('.',$bid[$i]);
                            
               $this->user_groups->set_user_groups($id,$depart[1],$depart[0]);
           }
        }
    function add_user_branches($id,$depapartment){
           $this->load->model('branch');
           $new_depa=array();
           $branch=array();
           $this->load->model('user_groups');
           $bid=array();
           $bid = explode(' ',$depapartment);
           $l=0;
           for($i=1;$i<count($bid);$i++){
               $depart=array();
               $depart=explode('.',$bid[$i]);
                        $branch[$l]=$depart[0];
                       $l++;
               
               }
               
               $arr=$branch;

         $len = count($arr);
        for ($i = 0; $i < $len; $i++) {
        $temp = $arr[$i];
        $j = $i;
        for ($k = 0; $k < $len; $k++) {
            if ($k != $j) {
            if ($temp == $arr[$k]) {
               
                $arr[$k]=" ";
                
            }
            }
        }
        }
        $r=0;
        for ($i = 0; $i < $len; $i++) {
       if($arr[$i]==" "){
           
       }
       else{
            $new_depa[$r]=$arr[$i];
           $r++;
       }
        }            
            for($k=0;$k<count($new_depa);$k++)
            {
               $this->branch->set_branch($id,$new_depa[$k]);                
            }
        }       
    function add_pos_users_image(){
              $uploaddir = './uploads/'; 
               $file = $uploaddir . basename($_FILES['uploadfile']['name']); 
               $_SESSION['image_name']=basename($_FILES['uploadfile']['name']); 
               
                if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) { 
                echo "success"; 
                } else {
                        echo "error";
                }
        }
    function to_deactivate_user(){  
            $id=  $this->input->post('guid');
                $this->load->model('pos_users_model');
                $this->pos_users_model->deactivate_user($id,$_SESSION['Bid']);   
               // redirect('users');
               echo 'TRUE';
        }
    function to_activate_user($id){
                $this->load->model('pos_users_model');
                $this->pos_users_model->activate_user($id,$_SESSION['Bid']);   
                redirect('users');
        }
    function delete(){
            if($_SESSION['users_per']['delete']==1){  
                $id=  $this->input->post('guid');
                $this->load->model('pos_users_model');
                $this->pos_users_model->delete_pos_users($id,$_SESSION['Uid'],$_SESSION['Bid']);   
                echo 'TRUE';
            }else{
                redirect('home');
            }
        }
    function deactive(){
            $id=  $this->input->post('guid');
            $this->load->model('pos_users_model');
            $this->pos_users_model->deactive_pos_users($id,$_SESSION['Bid']);   
            echo 'TRUE';
        }
    function active(){
            $id=  $this->input->post('guid');
            $this->load->model('pos_users_model');
            $this->pos_users_model->active_pos_users($id,$_SESSION['Bid']);   
            echo 'TRUE';
        }
    function add_new_user(){
       if($_SESSION['users_per']['add']==1){  
             $this->load->model('user_groups');
             $this->load->model('branch');
             if($_SESSION['admin']==2){ 
                 $data['branch']=$this->branch->get_user_for_branch_admin();
             }
             else{
                $data['branch']= $this->branch->get_user_for_branch($_SESSION['Uid']);
             }
             $data['depa']= $this->user_groups->get_user_groups();  
             $this->load->view('template/app/header'); 
             $this->load->view('template/table/header');         
             $this->load->view('template/branch',$this->posnic->branches());
             $this->load->view('add_new_pos_users',$data);
             $this->load->view('template/app/navigation',$this->posnic->modules());
             $this->load->view('template/app/footer');          
             }else{
                 echo "U have No Permission to Add New User";
                $this->get_pos_users_details();
             }
           }
    function selected_user_group($id){
        $this->load->model('pos_users_model');
        $this->load->model('branch');
        $this->load->model('user_groups');
        $data=  $this->pos_users_model->edit_pos_users($id); 
        $deapart="";
        foreach ($data as $b_row){
            $selected_branch=$this->branch->get_selected_branch($id);
            $selected_depart=$this->user_groups->get_user_depart($id);
              foreach ($selected_depart as $s_b_row) {
                foreach ($selected_branch as $b_row){
                   if($b_row->branch_id==$s_b_row->branch_id ){
                        $deapart=$deapart.' '.$b_row->branch_id.'.'.$s_b_row->user_group_id;          
                        echo "<option value='$s_b_row->branch_id.$s_b_row->user_group_id'>$b_row->branch_name($s_b_row->depart_name)</option>";
                    } 

                }                 
              }
        }
    }  
    function selected_user_branch($id){
     $this->load->model('pos_users_model');
        $this->load->model('branch');
        $this->load->model('user_groups');
        $data=  $this->pos_users_model->edit_pos_users($id); 
        $deapart="";
        foreach ($data as $b_row){
            $selected_branch=$this->branch->get_selected_branch($id);
            $selected_depart=$this->user_groups->get_user_depart($id);
              foreach ($selected_depart as $s_b_row) {
                foreach ($selected_branch as $b_row){
                   if($b_row->branch_id==$s_b_row->branch_id ){
                        $deapart=$deapart.' '.$b_row->branch_id.'.'.$s_b_row->user_group_id;          
                        echo "<option value='$s_b_row->branch_id.$s_b_row->user_group_id'>$s_b_row->depart_name</option>";
                    } 

                }                 
              }
        }
    }
    function get_users_groups(){
        $this->load->model('pos_users_model');
        $guid=  $this->input->post('branch_id');
        $data=  $this->pos_users_model->get_user_grous($guid);
        echo json_encode($data);
    }


}
?>
