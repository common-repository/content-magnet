<?php

/*
Main plugin class (also is used as controller)
*/

class CMMain
{
	public $model;
	
   
	private $plugin_page = 'comm-trends'; // page=$this->plugin_page	
	private $powered_by_wpl = "";
	
	private $search_type_arr = array(
		'videos' => 'Videos (Random)',
		'top_rated' => 'Videos (Top Rated)',
		'most_viewed' => 'Videos (Most Viewed)',
		'recently_featured' => 'Videos (Recently Featured)',
		//'categories_keywords' => 'Categories & Keywords',
		//'profile' => 'Profile',
		'articles' => 'Articles (Random)',
	);
	
	private $run_period_arr = array(
		'hour' => 'hours',
		'day' => 'days',
	);
	
	private $post_status_arr = array(
		'publish' => 'Published', 
		'pending' => 'Pending Review', 
		'draft' => 'Draft', 
		'private' => 'Private',
	);
	
		
	private $network_arr = array(
		'amazon'=>'Amazon', 
		'linkshare'=>'Link Share', 
		'cj'=>'Commission Junction',
		'ebay'=>'Ebay Partners Network',
		'clickbank'=>'ClickBank'
		/*'rand'=>'Let Us Choose'*/
	);	
	
	private $date_format = "F j, Y g:i a"; // by default plugin will use date formating set in your wordpress
	private $use_this_date = false; // if you want it to show your custom date, change false to true
	
   
	public function __construct()
	{
		// create model
		$this->model = new CMModel;
	}
	
	// on activation
	public function on_activation()
	{
		// set default wp options
		$this->model->set_default_wp_options();
		
		// create db tables
		$this->model->create_tables();
		
		// regenerate wp cron hooks, schedules
		$this->model->cron_schedules_regen();
		if(file_exists(CMPATH.'/CMerror_log.txt')){
			@unlink(CMPATH.'/CMerror_log.txt');
		}
		
	}
	
	// on deactivation
	public function on_deactivation()
	{
		// delete wp cron hooks and schedules
		$this->model->cron_schedules_delete();
		if(file_exists(CMPATH.'/CMerror_log.txt')){
			@unlink(CMPATH.'/CMerror_log.txt');
		}
		
	}
	
	// adds wp videotube submenu to manage tab
	public function add_admin_menu()
	{
		$mypage = add_menu_page('Content Magnet', 'Content Magnet', 8, $this->plugin_page, array($this, 'management_page'),plugins_url().'/contentmagnet/images/ct.ico.png'); //Ricky
		$mySetupOption = add_submenu_page($this->plugin_page,'Setup Options','Setup Options',8,'SetupOption' , array($this, 'setupOption_page')); //Antero +
        $myGatherTrend = add_submenu_page($this->plugin_page,'Gather Trends','Gather Trends',8,'GatherTrend' , array($this, 'gatherTrend_page')); //Antero +
        $mySetupPost = add_submenu_page($this->plugin_page,'Setup Posts','Setup Posts',8,'SetupPost',array($this,'management_page')); //Antero+ (auto)
        if(CMDEBUG==1){
			$myAutoCode = add_submenu_page($this->plugin_page,'Test Networks','Test Networks',8,'networks_page',array($this,'networks_page')); //Antero+ (auto)
		}
		// only load this on this plugin's page
		add_action("admin_print_scripts-$mypage", array('CMDesign', 'admin_head')); // admin head
        
	}
	
	/***** SETUP POSTS ***********************************************************/
	/***** *  *        ***********************************************************/
	/***** *  *        ***********************************************************/	
	
	
	public function management_page()
	{
		// set action if it exists
		$action = (isset($_REQUEST['action'])) ? htmlspecialchars($_REQUEST['action']) : null;
		
		// set id if it exists
		$id = (!empty($_GET['id'])) ? (int) $_GET['id'] : null;
		
		// show edit form
		if ("edit" == $action && !empty($id))
		{
			CMDesign::edit_search_term_page(array(
				'plugin_page' => $this->plugin_page,
				'model' => $this->model,
				'search_type_arr' => $this->search_type_arr,
				'run_period_arr' => $this->run_period_arr,
				'post_status_arr' => $this->post_status_arr,
				'network_arr' => $this->network_arr,
			), $id);
		}
		/// NOT SURE USING
		// show edit post code
		else if ("editpostcode" == $action)
		{
			// if we want to set default post code
			if (isset($_GET['reset']) && "true" == $_GET['reset'])
			{
				// set default post code option
				$this->model->set_default_post_code();
				
				// post code reset
				CMDesign::edit_post_code(array(
					'plugin_page' => $this->plugin_page,
					'message' => CMMessages::st_post_code_reset,
					'post_code' => get_option('CMpostcode'),
				));
			}
			// just show post code
			else
			{
				CMDesign::edit_post_code(array(
					'plugin_page' => $this->plugin_page,
					'post_code' => get_option('CMpostcode'),
				));
			}
		}
		// show main form
		else
		{
			// set default message
			$message = null;
			
			// update post code
			if ("updatepostcode" == $action)
			{
				if (true == $this->model->edit_post_code())
				{
					$message = CMMessages::st_post_code_edited;
				}
				else
				{
					$message = CMMessages::st_error;
				}
			}
			// we add search term
			else if ("addsearchterm" == $action)
			{
				if (true == $this->model->add_search_term($_POST))
				{
					$message = CMMessages::st_search_term_added;
				}
				else
				{
					$message = CMMessages::st_error;
				}
			}
			// we update search term
			else if ("editsearchterm" == $action)
			{
				if (true == $this->model->update_search_term($_POST))
				{
					$message = CMMessages::st_search_term_updated;
				}
				else
				{
					$message = CMMessages::st_error;
				}
			}
			// we delete search term
			else if (isset($_POST['deleteit']) && !empty($_POST['delete']))
			{
				if (true == $this->model->delete_search_terms((array) $_POST['delete']))
				{
					$message = CMMessages::st_search_terms_deleted;
				}
				else
				{
					$message = CMMessages::st_error;
				}
			}

			// pause search term
			else if ("pause" == $action && !empty($id))
			{
				if (true == $this->model->pause_search_term($id))
				{
					$message = CMMessages::st_search_term_paused;
				}
				else
				{
					$message = CMMessages::st_error;
				}
			}
			// activate search term
			else if ("activate" == $action && !empty($id))
			{
				if (true == $this->model->activate_search_term($id))
				{
					$message = CMMessages::st_search_term_activated;
				}
				else
				{
					$message = CMMessages::st_error;
				}
			}
			// post now
			else if ("run_now" == $action && !empty($id))
			{
				$this->model->run_now($id);
			}
			// get search terms
			$sql_search_terms = $this->model->seleCMsearch_terms();
			
			// calculate allowed searchterms
			$allowed_search_terms = 0 > ($result = (CMLV_LIMIT - count($sql_search_terms))) ? 0 : $result ;
            
			//Get saved Keys
            $saved_keys = $this->model->seleCMkey();   //Antero+
           
		   // print_r ($this->search_type_arr);
           // exit();
            
			CMDesign::management_page(array(
				'message' => $message,
				'plugin_page' => $this->plugin_page,
				'allowed_search_terms' => $allowed_search_terms,
				'upgrade_lv_link' => $this->upgrade_lv_link,
				'sql_search_terms' => $sql_search_terms,
				'model' => $this->model,
				'search_type_arr' => $this->search_type_arr,
				'run_period_arr' => $this->run_period_arr,
				'post_status_arr' => $this->post_status_arr,
				//Ricky Networks
				'network_arr' => $this->network_arr,
				'date_format' => $this->date_format,
				'use_this_date' => $this->use_this_date,
				'blog_posts' => get_option('CMblog_posts'),
                
			));
		}
	}

	
	/***** DEV       ***********************************************************/
	/***** *  *      ***********************************************************/
	/***** *  *      ***********************************************************/	


	public function networks_page(){
		
		// set action if it exists
		$action = (isset($_POST['action'])) ? htmlspecialchars($_POST['action']) : null;	
		if ("testProducts" == $action)
		{
			$return=$this->testNetworks($_POST['keyword']);
		}	
		if("runnow"==$action){
			$poster=$this->model->run_now($id=0,$network=$_POST['network']);	
		}	
		if("testSpiner"==$action){
			
		 	$spinContent=$this->testSpiner($_POST['keyword']);
		}
		CMDesign::networks_page(array(
			'message' => $message,
			'plugin_page' => 'networks_page',
			'model' => $this->model,
			'date_format' => $this->date_format,
			'use_this_date' => $this->use_this_date,
			'return'=>$return,
			'network_arr'=>$this->network_arr,
			'keyword'=>$_POST['keyword'],
			'poster'=>$poster,
			'spinContent'=>$spinContent
			));

	}
	
	function testSpiner($keyword){
		require_once("CMArticles.php");
		require_once("CMSpinx.php");	
		$spinner = new SpinMe;
		$articles = articlex($keyword);  
		$use_article=$articles[array_rand($articles)];				
		$article_title = $use_article["title"];
		$content = $use_article["content"];
		$new_content=$spinner->spinText( $content, 'replaceEveryonesFavorites', '1', $quality, $excluded);
		return array('org'=>$content,'spin'=>$new_content);
	}
	
	function testNetworks($keyword=''){
		set_time_limit(0);
		require_once("CMnetworks.php");
		$return=array();
		$networks=new CMnetworks;
		
		//It explores the content with YAHOO
		//this in gets the most likey product
		//keywords to get the products based
		//on the content.
		$minprice='0';
		
		//WORKS! TESTED
		$return['amazon']=$networks->CMget_amazon($keyword);
		if(count($return['amazon'])>0){
		$return['amazon']['success']=true;	
		}else{$return['amazon']['success']=false;}
		sleep(1);
	
		//Search Retuns NOTHING!!
		$return['linkshare']=$networks->CMget_linkshare($keyword,$minprice);
		if(count($return['linkshare'])>0){
		$return['linkshare']['success']=true;	
		}else{$return['linkshare']['success']=false;}
		sleep(1);
		
		//WORKS! TESTED
		$return['cj']=$networks->CMget_cj($keyword,$minprice);
		if(count($return['cj'])>0){
		$return['cj']['success']=true;	
		}else{$return['cj']['success']=false;}		
		sleep(1);
		
		//WORKS TESTED
		$return['ebay']=$networks->CMget_ebay_content($keyword);	
		if(count($return['ebay'])>0){
		$return['ebay']['success']=true;	
		}else{$return['ebay']['success']=false;}		
		sleep(1);
		
		$return['clickbank']=$networks->CMget_clickbank_content($keyword);
		if(count($return['clickbank'])>0){
		$return['clickbank']['success']=true;	
		}else{$return['clickbank']['success']=false;}
							
		return $return;
	}

	
	/***** OPTIONS   ***********************************************************/
	/***** *  *      ***********************************************************/
	/***** *  *      ***********************************************************/	
	
	public function setupOption_page(){
		
		// set action if it exists
		$action = (isset($_POST['action'])) ? htmlspecialchars($_POST['action']) : null;
		if ("saveOptions" == $action)
		{
			$message=$this->addOptions();
		}
		CMDesign::setupOption_page(array(
			'message' => $message,
			'plugin_page' => 'SetupOption',
			'model' => $this->model,
			'date_format' => $this->date_format,
			'use_this_date' => $this->use_this_date
			));

	}
	
	public function addOptions(){

		$amazon_public=$_POST['amazon_public'];
		$amazon_secret=$_POST['amazon_secret'];
		$amazon_id=$_POST['amazon_id'];
		$amazon_country=$_POST['amazon_country'];
		$cj_id=$_POST['cj_id'];
		$cj_api = $_POST['cj_api'];
		$linkshare_id=$_POST['linkshare_id'];
		$shareasale_id = $_POST['shareasale_id'];
		$shareasale_ftp_id = $_POST['shareasale_ftp_id'];
		$shareasale_ftp_pass =  $_POST['shareasale_ftp_pass'];
		$cache = $_POST['cache'];
		$newlink= $_POST['newlink'];

 	 	update_option('CMamazon_country',$amazon_country); 
    	update_option('CMamazon_associate',$amazon_id); 
    	update_option('CMamazon_api_public',$amazon_public); 
    	update_option('CMamazon_api_secret',$amazon_secret); 
		update_option('CMcj_site',$cj_id); 
		update_option('CMcj_api',$cj_api); 
		update_option('CMlinkshare_api',$linkshare_id); 
		update_option('CMcache',$cache);
		update_option('CMnewlink',$newlink);
		update_option('CMclickbank_id', (string) $_POST["clickbank_id"]);
		update_option('CMoverstock_id', (string) $_POST["overstock_id"]);
		update_option('CMebaylist_networkid', (string) $_POST["CMebaylist_networkid"]);
		update_option('CMebaylist_campaignid', (string) $_POST["CMebaylist_campaignid"]);		
		update_option('CMebaylist_country', (string) $_POST["CMebaylist_country"]);
		update_option('CMebaylist_minprice', (string) $_POST["CMebaylist_minprice"]);
		update_option('CMebaylist_maxprice', (string) $_POST["CMebaylist_maxprice"]);
		update_option('CMebaylist_maxresults', (string) $_POST["CMebaylist_maxresults"]);
		update_option('CMebaylist_itemsort', (string) $_POST["CMebaylist_itemsort"]);
		update_option('CMebaylist_itemsortorder', (string) $_POST["CMebaylist_itemsortorder"]);	
		
		return 'Your Options Have been Saved';
		
	}
	

	/***** TRENDS    ***********************************************************/
	/***** *  *      ***********************************************************/
	/***** *  *      ***********************************************************/		
    
	// Antero +
	// Ricky Assist
    /// NEEDS INTERFACE CLEANING
	public function gatherTrend_page()
    {
        // set action if it exists
        $action = (isset($_REQUEST['action'])) ? htmlspecialchars($_REQUEST['action']) : null;
        $id = (!empty($_GET['id'])) ? (int) $_GET['id'] : null;
		// calculate allowed searchterms
		$allowed_search_terms = 0 > ($result = (CMLV_LIMIT - count($sql_search_terms))) ? 0 : $result ;
		CMDesign::gatherTrend_page(array(
			'message' => $message,
			'plugin_page' => $this->plugin_page,
			'allowed_search_terms' => $allowed_search_terms,
			'sql_search_terms' => $sql_search_terms,
			'model' => $this->model,
			'date_format' => $this->date_format
		));
     
    }
	
	
	
	/***** POWERED   ***********************************************************/
	/***** *  *      ***********************************************************/
	/***** *  *      ***********************************************************/		
	
    
	public function powered_by_wpl()
	{
		// if powered by link is not empty (disabled), and is not shown already
		if (!empty($this->powered_by_wpl) && !defined("WPL_POWERED_BY"))
		{
			CMDesign::powered_by_wpl(array(
				'powered_by_wpl' => $this->powered_by_wpl,
			));
			
			// define this to show other plugins that link is shown already
			define("WPL_POWERED_BY", true);
		}
	}
}
?>