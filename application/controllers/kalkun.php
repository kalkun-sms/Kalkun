<?php
Class Kalkun extends MY_Controller {
	
	function Kalkun()
	{
		parent::MY_Controller();	
	}
	
	//=================================================================
	// DASHBOARD
	//=================================================================	

	function index() 
	{
		$data['main'] = 'main/dashboard/home';
		$data['title'] = 'Dashboard';
        $data['data_url'] = site_url('kalkun/get_statistic');
		$this->load->view('main/layout', $data);
	}
	
	function get_statistic()
	{
		$this->load->library('OpenFlashChartLib', NULL, 'OFCL');
		$data_1 = array();
		$data_2 = array();
		
		// generate 7 data points
		for( $i=0; $i<=7; $i++ )
		{
		    $x = mktime(0, 0, 0, date("m"), date("d")-$i, date('Y'));	    
		    $param['sms_date']=date('Y-m-d', mktime(0,0,0,date("m"),date("d")-$i,date("Y")));
		    $param['user_id']=$this->session->userdata('id_user');		    
		    $y=$this->Kalkun_model->get_sms_used('date', $param);
		    $data_1[] = new scatter_value($x, $y);
		    $data_2[] = $y;
		}
		
		$def = new solid_dot();
		$def->size(4)->halo_size(0)->colour('#21759B')->tooltip('#date:d M y#<br>#val# SMS');
		
		$line = new scatter_line( '#21759B', 3); 
		$line->set_values($data_1);
		$line->set_default_dot_style( $def );
		$line->set_key( "SMS used in last 7 days", 10);
		
		
		$x = new x_axis();
		// grid line and tick every 10
		$x->set_range(
		    mktime(0, 0, 0, date("m"), date("d")-7, date('Y')), // <-- min == 7 day before
		    mktime(0, 0, 0, date("m"), date("d"), date('Y'))    // <-- max == Today
		    );
		
		// show ticks and grid lines for every day:
		$x->set_steps(86400);
		
		$labels = new x_axis_labels();
		// tell the labels to render the number as a date:
		$labels->text('#date:M-d#');
		// generate labels for every day
		$labels->set_steps(86400);
		// only display every other label (every other day)
		$labels->visible_steps(1);
		$labels->rotate(45);
		
		// finally attach the label definition to the x axis
		$x->set_labels($labels);
		
		$y = new y_axis();
		if(max($data_2)>0) $max=max($data_2); else $max=10;
		$y->set_range(0, $max, 10); 
		
		$chart = new open_flash_chart();
		//$chart->set_title( $title );
		$chart->add_element( $line );
		$chart->set_x_axis( $x );
		$chart->set_y_axis( $y );
		
		echo $chart->toPrettyString();		
	}
	
	function about()
	{
		$data['main'] = 'main/about';
		$this->load->view('main/layout', $data);		
	}
	
	function auto_update_notification()
	{
		$this->load->view('main/dashboard/notification');
	}
	
	function notification()
	{
		$this->load->view('main/notification');
	}	
	
	function unread_inbox()
	{		
		$tmp_unread = $this->Message_model->getUnread();
		echo ($tmp_unread > 0)? "(".$tmp_unread.")" : "";		
	}	
	
	function get_clock()
	{
		echo date('l, M dS Y, h:i A');	
	}	

	//=================================================================
	// FOLDER
	//=================================================================		
	
	function add_folder()
	{
		$this->Kalkun_model->addFolder(); 
		redirect($this->input->post('source_url'));
	}
	
	function rename_folder()
	{
		$this->Kalkun_model->renameFolder();
		redirect($this->input->post('source_url'));
	}
	
	function delete_folder($id_folder=NULL)
	{
		$this->Kalkun_model->deleteFolder($id_folder);
		redirect('/', 'refresh');
	}
	
	//=================================================================
	// SETTINGS
	//=================================================================	
	function settings()
	{
		$data['title'] = 'Settings';
		$type = $this->uri->segment(2);
		$valid_type = array('general', 'personal', 'appearance', 'password', 'save');
		if(!in_array($type, $valid_type)) show_404();
		
		if($_POST && $type=='save') { 		
			$option = $this->input->post('option');
			// check password
			if($option=='password' && sha1($this->input->post('current_password'))!=$this->Kalkun_model->getSetting()->row('password')) 
			{
				$this->session->set_flashdata('notif', 'You entered wrong password');
				redirect('settings/'.$option);
			}
			else if($option=='personal') 
			{
				if($this->input->post('username')!=$this->session->userdata('username'))
				{
					if($this->Kalkun_model->checkSetting(array('option' => 'username', 'username' => $this->input->post('username')))->num_rows>0) 
					{
						$this->session->set_flashdata('notif', 'Username already exist');
						redirect('settings/'.$option);					
					}
				}
			}
			$this->Kalkun_model->UpdateSetting($option);
			$this->session->set_flashdata('notif', 'Your settings has been saved');
			redirect('settings/'.$option);
		}
		$data['main'] = 'main/settings/setting';
		$data['settings'] = $this->Kalkun_model->getSetting();
		$data['type'] = 'main/settings/'.$type;
		$this->load->view('main/layout', $data);
	}
	
}
?>
