<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		http://kalkun.sourceforge.net/license.php
 * @link		http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * Kalkun Class
 *
 * @package		Kalkun
 * @subpackage	Base
 * @category	Controllers
 */
class Kalkun extends MY_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */	
	function __construct()
	{
		parent::__construct();
	}		
		
	// --------------------------------------------------------------------
	
	/**
	 * Index/Dashboard
	 *
	 * Display dashboard page
	 *
	 * @access	public   		 
	 */		
	function index() 
	{
		$this->load->model('Phonebook_model');
		$data['main'] = 'main/dashboard/home';
		$data['title'] = 'Dashboard';
        $data['data_url'] = site_url('kalkun/get_statistic');
        if($this->config->item('disable_outgoing'))
        {
          $data['alerts'][] = "<div class=\"warning\">Outgoing SMS Disabled. Contact System Administrator</div>";
        }
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * About
	 *
	 * Display about page
	 *
	 * @access	public   		 
	 */
	function about()
	{
		$data['main'] = 'main/about';
		$this->load->view('main/layout', $data);		
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Get Statistic
	 *
	 * Get statistic data that used to render the graph
	 *
     * @param string (days, weeks, months)
	 * @access	public   		 
	 */	
	function get_statistic($type = 'days')
	{
        // count number of days
        switch ($type)
        {
            case 'days':
            default:
                $days = 10;
                $format = 'M-d';
            break;

            case 'weeks':
                $days = 30;
                $format = 'W';
                $prefix = ucwords(lang('kalkun_week')).' ';
            break;

            case 'months':
                $days = 60;
                $format = 'M-Y';
            break;
        }

		// generate data points
        $x = array();
		for ($i=0; $i<=$days; $i++)
		{
		    $key = date($format, mktime(0, 0, 0, date("m"), date("d")-$i, date('Y')));

            if (isset($prefix)) 
            {
                $key = $prefix.$key;
            }

            if(!isset($yout[$key]))
            {
                $yout[$key] = 0;
            }

            if(!isset($yin[$key]))
            {
                $yin[$key] = 0;
            }

            if(!in_array($key, $x))
            {
                $x[] = $key;
            }

		    $param['sms_date'] = date('Y-m-d', mktime(0,0,0,date("m"),date("d")-$i,date("Y")));
		    if ($this->session->userdata('level')!='admin')
		    {
		    	$param['user_id'] = $this->session->userdata('id_user');		    
		    }
		    $yout[$key] += $this->Kalkun_model->get_sms_used('date', $param, 'out');
            $yin[$key] += $this->Kalkun_model->get_sms_used('date', $param,'in');
		}

        $yout = array_values($yout);
        $yin = array_values($yin);
        $points = count($x)-1;
		$this->_render_statistic($x, $yout,  $yin, 'bar', $points);
	}
	
	function _render_statistic($x = array(), $yout = array(),  $yin = array(), $type='bar', $points)
	{
		$this->load->helper('date');
		$this->load->library('OpenFlashChartLib', NULL, 'OFCL');
		$data_1 = array();
		$data_2 = array();	
        $data_3 = array();	

		switch($type)
		{	
			case 'bar':
				for($i=0; $i<=$points;$i++)
				{
				    $data_1[$i] = $x[$i];
				    $data_2[$i] = (int)$yout[$i]; // force to integer
                    $data_3[$i] = (int)$yin[$i]; // force to integer
				}				
				
				$data_1 = array_reverse($data_1);
				$data_2 = array_reverse($data_2);
                $data_3 = array_reverse($data_3);
				
				$bar_1 = new bar();
				$bar_1->set_values($data_3);
				$bar_1->set_colour('#639F45');
                $bar_1->key(lang('kalkun_incoming_sms'), 10 ); 
				$bar_1->set_tooltip('#x_label#<br>#val# SMS');
				//$bar_1->set_key("SMS used in last 7 days", 10);
                
                $bar_2 = new bar();
                $bar_2->set_values($data_2);
			    $bar_2->set_colour('#21759B');
                $bar_2->key(lang('kalkun_outgoing_sms'), 10 ); 
			    $bar_2->set_tooltip('#x_label#<br>#val# SMS');
				 
                
				
				$x = new x_axis();				
				$labels = new x_axis_labels();
				$labels->set_labels($data_1);
				$labels->set_steps(1);
				$x->set_labels($labels);
				
				$y = new y_axis();
				$max = max(max($data_2),max($data_3));
				if($max < 10)  $max=10;
				$max = ceil($max/5)*5;
				$range = ceil($max/5);
				$range = ceil($range/10)*10;
				$y->set_range(0, $max, $range); 
				
				$element1 = $bar_1;
                $element2 = $bar_2;
			break;
			
			case 'line':
				for($i=0; $i<=7;$i++)
				{
				    $data_1[$i] = new scatter_value($x[$i], $yin[$i]);
                    $data_2[$i] = new scatter_value($x[$i], $yout[$i]);
                    $data_3[$i] = (int)$yin[$i];
				    $data_4[$i] = (int)$yout[$i];
				}
				    		
				$def = new solid_dot();
				$def->size(4)->halo_size(0)->colour('#21759B')->tooltip('#date:d M y#<br>#val# SMS');
				
				$line_1 = new scatter_line('#639F45', 3); 
				$line_1->set_values($data_1);
				$line_1->set_default_dot_style($def);
				$line_1->set_key("Incoming SMS", 10);
                
                $line_2 = new scatter_line('#21759B', 3); 
				$line_2->set_values($data_2);
				$line_2->set_default_dot_style($def);
				$line_2->set_key("Outgoing SMS", 10);

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
                 
                $max = max(max($data_3),max($data_4));
				if($max < 1)  $max=10;
				$y->set_range(0, $max, round($max/100)*10);	
							
				$element1 = $line_1;
                $element2 = $line_2;
			break;
		}		
		$chart = new open_flash_chart();
		$chart->add_element($element1);
        $chart->add_element($element2);
		$chart->set_x_axis($x);
		$chart->set_y_axis($y);
		
		echo $chart->toPrettyString();			
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Notification
	 *
	 * Display notification
	 * Modem status
	 * Used by the autoload function and called via AJAX.
	 *
	 * @access	public   		 
	 */		
	function notification()
	{
		$this->load->view('main/notification');
	}	

	// --------------------------------------------------------------------
       
    /**
	 * Unread Count
	 *
	 * Show unread inbox/spam/draft and alert when new sms arrived
	 * Used by the autoload function and called via AJAX.
	 *
	 * @access	public   		 
	 */
    function unread_count()
    {
        $tmp_unread = $this->Message_model->get_messages(array('readed' => FALSE , 'uid' => $this->session->userdata('id_user')))->num_rows();
		$in =  ($tmp_unread > 0)? "(".$tmp_unread.")" : "";	
        
        $tmp_unread = 0;
		$draft =  ($tmp_unread > 0)? "(".$tmp_unread.")" : "";
        
        $tmp_unread = $this->Message_model->get_messages(array('readed' => FALSE , 'id_folder' => '6' ,'uid' => $this->session->userdata('id_user')) )->num_rows();
		$spam =  ($tmp_unread > 0)? "(".$tmp_unread.")" : "";
        
        echo $in. '/' . $draft . '/' . $spam;
    }

	// --------------------------------------------------------------------
	
	/**
	 * Add Folder
	 *
	 * Add custom folder
	 *
	 * @access	public   		 
	 */				
	function add_folder()
	{
		$this->Kalkun_model->add_folder(); 
		redirect($this->input->post('source_url'));
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Rename Folder
	 *
	 * Rename custom folder
	 *
	 * @access	public   		 
	 */	
	function rename_folder()
	{
		$this->Kalkun_model->rename_folder();
		redirect($this->input->post('source_url'));
	}

	// --------------------------------------------------------------------
	
	/**
	 * Delete Folder
	 *
	 * Delete custom folder
	 *
	 * @access	public   		 
	 */		
	function delete_folder($id_folder=NULL)
	{
		$this->Kalkun_model->delete_folder($id_folder);
		redirect('/', 'refresh');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Settings
	 *
	 * Display and handle change on settings/user preference
	 *
	 * @access	public   		 
	 */	
	function settings()
	{
		$this->load->helper('country_dial_code_helper');
		$data['title'] = 'Settings';
		$type = $this->uri->segment(2);
		$valid_type = array('general', 'personal', 'appearance', 'password', 'save', 'filters');
		if(!in_array($type, $valid_type)) show_404();
		
		if($_POST && $type=='save') { 		
			$option = $this->input->post('option');
			// check password
			if($option=='password' && sha1($this->input->post('current_password'))!=$this->Kalkun_model->get_setting()->row('password')) 
			{
				$this->session->set_flashdata('notif', 'You entered wrong password');
				redirect('settings/'.$option);
			}
			else if($option=='personal') 
			{
				if($this->input->post('username')!=$this->session->userdata('username'))
				{
					if($this->Kalkun_model->check_setting(array('option' => 'username', 'username' => $this->input->post('username')))->num_rows>0) 
					{
						$this->session->set_flashdata('notif', 'Username already exist');
						redirect('settings/'.$option);					
					}
				}
			}
			$this->Kalkun_model->update_setting($option);
			$this->session->set_flashdata('notif', 'Your settings has been saved');
			redirect('settings/'.$option);
		}

        if($type == 'filters')
        {
            $data['filters'] = $this->Kalkun_model->get_filters($this->session->userdata('id_user'));
            $data['my_folders'] = $this->Kalkun_model->get_folders('all');
        }

		$data['main'] = 'main/settings/setting';
		$data['settings'] = $this->Kalkun_model->get_setting();
		$data['type'] = 'main/settings/'.$type;
		
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Delete Filter
	 *
	 * @access	public
	 */		
	function delete_filter($id_filter=NULL)
	{
		$this->Kalkun_model->delete_filter($id_filter);
	}
	
}

/* End of file kalkun.php */
/* Location: ./application/controllers/kalkun.php */
