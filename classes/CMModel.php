<?php

/*
All internal logic for the plugin (db etc.)
*/

require_once("CMArticles.php");

class CMModel
{
	public $wpdb; // wpdb object
	
	public $db_table_search_terms;
	public $db_table_added_videos;
    public $db_table_keyword;
	public $db_table_clickbank_categories;
	public $db_table_clickbank_products;
	public $db_table_clickbank_salesrank;
	
	private $db_version = 10; // plugin db version
	
	//default code for posts
	public $default_post_code = "<object width=\"425\" height=\"355\"><param name=\"movie\" value=\"{video_url}&showsearch=0&rel=0\"></param><param name=\"wmode\" value=\"transparent\"></param><embed src=\"{video_url}&showsearch=0&rel=0\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"425\" height=\"355\"></embed></object>\n\n{video_description}";
	
	// on how many percents we want the time for humanized posts to be changed
	// 1 = 100%, so 0.1 will be + or - 10% to it's usual time
	private $humanize_seed = 0.1;
	
	public function __construct()
	{
		global $wpdb; // grab $wpdb :)
		
		$this->wpdb = $wpdb;
		
		// set tables names
		$this->db_table_search_terms = $this->wpdb->prefix . "CMsearch_terms";
		$this->db_table_added_videos = $this->wpdb->prefix . "CMadded_videos";
	    $this->db_table_keyword = $this->wpdb->prefix."CMkeyword";
				
		//RICKY CLICKBANK
		$this->db_table_clickbank_categories = $this->wpdb->prefix . "CMclickbank_categories";
		$this->db_table_clickbank_products = $this->wpdb->prefix . "CMclickbank_products";
		$this->db_table_clickbank_salesrank = $this->wpdb->prefix . "CMclickbank_salesrank";
	}
	
	public function create_tables()
	{
		global $wpdb; // grab $wpdb :)
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		// CREATE TABLES
		// add search terms table
		$table=$this->db_table_search_terms;
		$query=$this->wpdb->get_var("show tables like '$table'");
		if ((empty($query)) || ($query!= $table))
		{			
			$sql = "CREATE TABLE ".$table." (
			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`search_type` VARCHAR(255) NOT NULL,
			`search_query` VARCHAR(255) NOT NULL,
			`run_num` BIGINT UNSIGNED NOT NULL DEFAULT 1,
			`run_period` VARCHAR(255) NOT NULL DEFAULT 'day',
			`humanized` BIGINT UNSIGNED NOT NULL DEFAULT 0,
			`category_id` BIGINT NOT NULL DEFAULT -1,
			`post_comments` VARCHAR(255) NOT NULL DEFAULT 'yes',
			`post_author_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
			`post_status` VARCHAR(255) NOT NULL DEFAULT  'publish',
			`state` VARCHAR(255) NOT NULL DEFAULT 'active',
			`sr_post_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
			`sr_video_chosen` BIGINT UNSIGNED NOT NULL DEFAULT 0,
			`sr_videos_found` BIGINT UNSIGNED NOT NULL DEFAULT 0,
			`sr_next_runtime` BIGINT UNSIGNED NOT NULL DEFAULT 0,
			`adding_video` BIGINT UNSIGNED NOT NULL DEFAULT 0,
			`network` VARCHAR (255) NOT NULL, 
			PRIMARY KEY  (`id`)
			);";
			dbDelta($sql);

		}
		
		// add added videos table
		$table = $this->db_table_added_videos;
		$query=$this->wpdb->get_var("show tables like '$table'");
		if ((empty($query)) || ($query!= $table))
		{	
			$sql = "CREATE TABLE ".$table." (
			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`video_id` VARCHAR(255) NOT NULL,
			`post_id` BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY  (`id`),
			UNIQUE KEY (`video_id`)
			);";
        
		   dbDelta($sql);
		}
		    	    
        //add added keyword table ANTERO+
        $table = $this->db_table_keyword;
		$query=$this->wpdb->get_var("show tables like '$table'");
		if ((empty($query)) || ($query!= $table))
		{
		   
		    $sql = "CREATE TABLE ".$table." (
            `CMkeyword` VARCHAR(255) NOT NULL,
             PRIMARY KEY(`CMkeyword`)
             );";
			 
            dbDelta($sql);
        }
		
        //Add CB Products RICKY
        $table = $this->db_table_clickbank_products;
		$query=$this->wpdb->get_var("show tables like '$table'");
		if ((empty($query)) || ($query!= $table))
		{
              $sql = 'CREATE TABLE ' .$table.' ('
               . '`id` int(6) NOT NULL auto_increment,'
               . '`clickbank_id` varchar(50),'
               . '`produCMname` varchar(250),'
               . '`description` text,'
               . '`image` text,'
               . '`no_adjust` int(1),'
               . ' PRIMARY KEY  (`id`)'
               . ' )'
               . ' ENGINE = myisam'
               . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
            dbDelta($sql);
        }		


        /*Add CB Categories RICKY
        $table = $this->db_table_clickbank_categories;
        if($this->wpdb->get_var("show tables like '$table'") != $table
        || $this->db_version != get_option("CMdb_version"))
        {
              $sql = 'CREATE TABLE '.$table.' ('
               . '`id` int(6) NOT NULL auto_increment,'
               . '`parent_id` int(6),'
               . '`category_name` varchar(250),'
               . '`clickbank_cat_id` varchar(15),'
               . '`clickbank_cat_path` text,'
               . ' PRIMARY KEY  (`id`)'
               . ' )'
               . ' ENGINE = myisam'
               . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
            dbDelta($sql);
        }
		*/

        /*Add CB Sales Rank RICKY
        $table = $this->db_table_clickbank_salesrank;
        if($this->wpdb->get_var("show tables like '$table'") != $table
        || $this->db_version != get_option("CMdb_version"))
        {
              $sql = 'CREATE TABLE '.$table.' ('
               . '`id` int(6) NOT NULL auto_increment,'
               . '`category_id` int(6),'
               . '`produCMid` int(6),'
               . '`sales_rank` int(4),'
               . ' PRIMARY KEY  (`id`)'
               . ' )'
               . ' ENGINE = myisam'
               . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
            dbDelta($sql);
        }
		*/
		
		//Ricky ADD ALL CB PRODUCTS
		$this->_add_clickbank_products();

		// if database version has been changed, update it here
		if ($this->db_version != get_option("CMdb_version"))
		{
			$this->set_db_version();
		}
	}
	
	// set db version
	private function set_db_version()
	{
		update_option('CMdb_version', $this->db_version);
	}
	
	public function set_default_post_code()
	{
		update_option('CMpostcode', $this->default_post_code);
	}
	
	// set default wp options on activation
	public function set_default_wp_options()
	{
		// set default wpa db version
		if (false === get_option('CMdb_version'))
		{
			add_option('CMdb_version');
		}
		
		// set default post code
		if (false === get_option('CMpostcode'))
		{
			add_option('CMpostcode', $this->default_post_code);
		}
	}
	
	// regeneration of ct cron hooks, schedules
	public function cron_schedules_regen()
	{
		// delete cron hooks, schedules
		$this->cron_schedules_delete();
		
		// set new ones
		// get new ones from the database
		$sql = "SELECT id, run_num, run_period, humanized, sr_next_runtime FROM $this->db_table_search_terms";
		
		// if there are any
		if (0 < ($results = $this->wpdb->get_results($sql)))
		{
			// set em
			foreach ($results as $searchterm)
			{
				$recurrance = $this->set_schedule($searchterm->run_num, $searchterm->run_period, $searchterm->humanized);
				wp_schedule_event($searchterm->sr_next_runtime, $recurrance, 'CMpost_hook', array((int) $searchterm->id));
			}
		}
		
		// set blog posts hook
		wp_schedule_event(time(), 'daily', 'CMblog_posts');
	}
	
	// delete wp cron hooks schedules
	public function cron_schedules_delete()
	{
		// delete cron hooks
		$crons = _get_cron_array();
		
		// if there are any crons
		if (is_array($crons))
		{
			foreach ($crons as $key => $value)
			{
				// if this is our hook
				if (isset($value['CMpost_hook']))
				{
					// delete it
					unset($crons[$key]);
				}
			}
			
			_set_cron_array($crons);
		}
		
		// delete schedules
		$schedules = get_option('CMschedules');
		
		// if schedules are set
		if (is_array($schedules))
		{
			// null 'em up
			update_option('CMschedules', array());
		}
		// clear blog posts hook
		wp_clear_scheduled_hook('CMblog_posts');
	}
	
	
	// add schedule, returns $recurrance
	public function set_schedule($run_num, $run_period, $humanize)
	{
		// create new schedule
		// set in seconds day or hour
		$period_sec = ('day' == $run_period) ? 86400 : 3600 ;
		
		// we create interval
		$interval = $period_sec * $run_num;
		
		// if humanize is set
		if (1 == $humanize)
		{
			// in case we'll have an infinite loop, prevent it
			$i = 0;
			do
			{
				// create new humanized posts interval
				
				$rand_num = mt_rand() % 3;
				
				// normal, nothing happenes
				if (2 == $rand_num)
				{
					$h_interval = $interval;
				}
				// if we want +
				else if (1 == $rand_num)
				{
					$h_interval = $interval + ($interval * $this->humanize_seed);
				}
				// if -
				else if (0 == $rand_num)
				{
					$h_interval = $interval - ($interval * $this->humanize_seed);
				}
				
				++$i;
			}
			// prevent interval from bein less than 0
			// in case there are probs with the seed
			while (0 >= $h_interval && 30 < $i);
			
			$interval = $h_interval;
			
			$recurrance = "CM" . $run_num . "_" . $run_period . "_humanized";
		}
		else
		{
			$recurrance = "CM" . $run_num . "_" . $run_period;
		}
		
		$schedule = array(
			$recurrance => array(
				'interval' => $interval,
				'display' => sprintf("%s %s", "WPL", str_replace("_", " ", $recurrance)),
				)
			);
			
		// set schedule
		// if schedules were set
		if (is_array($opt_schedules = get_option('CMschedules')))
		{
			// check whether this schedule exists
			if (!array_key_exists($recurrance, $opt_schedules))
			{
				// if not, add it
				update_option('CMschedules', array_merge($schedule, $opt_schedules));
			}
			else
			{
				// if yes and humanize is set change it
				if (1 == $humanize)
				{
					// delete previous
					unset($opt_schedules[$recurrance]);
					
					// and add a new one
					update_option('CMschedules', array_merge($schedule, $opt_schedules));
				}
				else
				{
					// otherwise just return recurrance
					return $recurrance;
				}
			}
		}
		// if schedules weren't set yet
		else
		{
			// set a new one
			add_option('CMschedules', $schedule);
		}
		
		return $recurrance;
	}

	// remove schedule
	private function remove_schedule($run_num, $run_period, $humanized)
	{
		// create recurrance
		if (1 == $humanized)
		{
			$recurrance = "CM" . $run_num . "_" . $run_period . "_humanized";
		}
		else
		{
			$recurrance = "CM" . $run_num . "_" . $run_period;
		}
		
		// delete schedule
		// checks whether schedules are set or not
		if (is_array($opt_schedules = get_option('CMschedules')))
		{
			// check whether our schedule exists, not paused
			// and there are no more wp cron jobs that use it
			$sql = "SELECT id FROM $this->db_table_search_terms 
			WHERE `run_num` = $run_num AND `run_period` = '$run_period' 
			AND `state` = 'active' AND `humanized` = '$humanized'";
			
			if (array_key_exists($recurrance, $opt_schedules)
			&& 0 === $this->wpdb->query($sql))
			{
				// and if yes, removes it
				unset($opt_schedules[$recurrance]);
				
				// update schedules
				update_option('CMschedules', $opt_schedules);
			}
		}
	}
	
	// wp cron hack func adds additional schedules, internally for wp
	public static function get_schedules($arr)
	{
		// get schedules from the options
		$schedules = get_option('CMschedules');
		
		// if schedules weren't set send empty array
		$schedules = (is_array($schedules)) ? $schedules : array();
		
		// merge all schedules
		return array_merge($schedules, $arr);
	}
	
	/************************************************/
	
	// select search terms
	public function seleCMsearch_terms()
	{
		$sql = "SELECT * FROM $this->db_table_search_terms";
		
		return $this->wpdb->get_results($sql);
	}
	
	// select search term
	public function seleCMsearch_term($id)
	{
		$id = $this->wpdb->escape($id);
		
		$sql = "SELECT * FROM $this->db_table_search_terms WHERE id = $id";
		
		return $this->wpdb->get_row($sql);
	}
	
	// add search term
	public function add_search_term($post)
	{   
		$run_num = $this->wpdb->escape($post['run_num']);
		$run_period = $this->wpdb->escape($post['run_period']);
		$humanized = (isset($post['humanized']) && "on" == $post['humanized']) ? 1 : 0;
		$search_type = $this->wpdb->escape($post['search_type']);
		
		if($post['search_query']=='none'){
		//manually entered
		$search_query = $this->wpdb->escape($post['search_query_2']);
		}else{
		$search_query = $this->wpdb->escape($post['search_query']);
		}
		
		$category_id = $this->wpdb->escape($post['category_id']);
		$post_comments = $this->wpdb->escape($post['post_comments']);
		$post_author_id = $this->wpdb->escape($post['post_author_id']);
		$post_status = $this->wpdb->escape($post['post_status']);
		$network = $this->wpdb->escape($post['network']);
		
		// check nonce
		check_admin_referer('add-searchterm');
		
		/* if light version is on, check search terms limit
		if (0 < CMLV_LIMIT)
		{
			$sql = "SELECT COUNT(*) FROM $this->db_table_search_terms";
			
			$search_terms_count = $this->wpdb->get_var($sql);
			
			// if we are at the limit, return false
			if ($search_terms_count >= WPA_LV_LIMIT)
			{
				return false;
			}
		}
		*/
		
		// a lil check
		if (empty($search_query))
		{
			return false;
		}
		
		// a lil repair
		$run_num = (0 >= $run_num) ? 1 : $run_num;
		
		//Ricky Added Network
		$sql = "INSERT INTO $this->db_table_search_terms (`run_num`, `run_period`, `humanized`, `search_type`, `search_query`, `category_id`, `post_comments`, `post_author_id`, `post_status`,`network`) VALUES ('$run_num', '$run_period', '$humanized', '$search_type', '$search_query', '$category_id', '$post_comments', '$post_author_id', '$post_status', '$network')";
		
		if (false != $this->wpdb->query($sql))
		{
			// get last id from mysql for wp cron
			$sql = "SELECT LAST_INSERT_ID()";
			
			$id = (int) $this->wpdb->get_var($sql);
			
			// we set stat row to 0 to notify stat row that we are adding a video
			$sql = "UPDATE $this->db_table_search_terms SET `sr_post_id` = 0, `sr_video_chosen` = 0, `sr_videos_found` = 0, `sr_next_runtime` = 0 WHERE id = $id";
			
			$this->wpdb->query($sql);
			
			// add wp cron hook
			$recurrance = $this->set_schedule($run_num, $run_period, $humanized);
            wp_schedule_event(time(), $recurrance, 'CMpost_hook', array($id));
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	// update search term
	public function update_search_term($post)
	{
		// never trust wordpress about escaped variables :D
		$id = (int) $post['id'];
		$run_num = $this->wpdb->escape($post['run_num']);
		$run_period = $this->wpdb->escape($post['run_period']);
		$humanized = (isset($post['humanized']) && "on" == $post['humanized']) ? 1 : 0;
		$search_type = $this->wpdb->escape($post['search_type']);
		
		if($post['search_query']=='none'){
		//manually entered
		$search_query = $this->wpdb->escape($post['search_query_2']);
		}else{
		$search_query = $this->wpdb->escape($post['search_query']);
		}	
	
		$category_id = $this->wpdb->escape($post['category_id']);
		$post_comments = $this->wpdb->escape($post['post_comments']);
		$post_author_id = $this->wpdb->escape($post['post_author_id']);
		$post_status = $this->wpdb->escape($post['post_status']);
		$network = $this->wpdb->escape($post['network']);
		
		// check nonce
		check_admin_referer('update-searchterm-' . $id);
		
		// a lil check
		if (empty($search_query))
		{
			return false;
		}
		
		// a lil repair
		$run_num = (0 >= $run_num) ? 1 : $run_num;
		
		// get previous search term info to delete it
		$sql = "SELECT run_num, run_period, humanized, state, sr_next_runtime FROM $this->db_table_search_terms WHERE id = $id";
		$oldresult = $this->wpdb->get_row($sql);
		
		$sql = "UPDATE $this->db_table_search_terms SET `run_num` = '$run_num', `run_period` = '$run_period', `humanized` = '$humanized', `search_type` = '$search_type', `search_query` = '$search_query', `category_id` = '$category_id', `post_comments` = '$post_comments', `post_author_id` = '$post_author_id', `post_status` = '$post_status', `network` = '$network' WHERE id = $id";
		
		if (false !== $this->wpdb->query($sql))
		{
			// if it's not paused
			if ("paused" != $oldresult->state)
			{
				// delete wp cron hook
				wp_clear_scheduled_hook('CMpost_hook', $id);
				$this->remove_schedule($oldresult->run_num, $oldresult->run_period, $oldresult->humanized);
				
				// add wp cron hook
				$recurrance = $this->set_schedule($run_num, $run_period, $humanized);
				wp_schedule_event($oldresult->sr_next_runtime, $recurrance, 'CMpost_hook', array($id));
			}
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	// delete search terms
	public function delete_search_terms($ids)
	{
		check_admin_referer('bulk-searchterms');
		
		foreach ($ids as $cat_id) {
			$sql = "SELECT run_num, run_period, humanized FROM $this->db_table_search_terms WHERE id = $cat_id";

			$result = $this->wpdb->get_row($sql);

			$sql = "DELETE FROM $this->db_table_search_terms WHERE id = $cat_id";

			if (false != $this->wpdb->query($sql))
			{
				// delete wp cron hook
				wp_clear_scheduled_hook('CMpost_hook', (int) $cat_id);
				$this->remove_schedule($result->run_num, $result->run_period, $result->humanized);
			}
		}
		
		return true;
	}
	
	// pause search term
	public function pause_search_term($id)
	{
		$id = (int) $id;
		
		check_admin_referer('pause-searchterm-' . $id);
		
		$sql = "SELECT run_num, run_period, humanized, state FROM $this->db_table_search_terms WHERE id = $id";
		
		$result = $this->wpdb->get_row($sql);
		
		// check that state is active
		if ('active' == $result->state)
		{
			// update searchterm's state to 'paused'
			$sql = "UPDATE $this->db_table_search_terms SET `state` = 'paused' WHERE id = $id";
			
			$this->wpdb->query($sql);
			
			// delete wp cron hook
			wp_clear_scheduled_hook('CMpost_hook', $id);
			$this->remove_schedule($result->run_num, $result->run_period, $result->humanized);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	// activate search term
	public function activate_search_term($id)
	{
		$id = (int) $id;
		
		check_admin_referer('activate-searchterm-' . $id);
		
		$sql = "SELECT run_num, run_period, humanized, state, sr_next_runtime FROM $this->db_table_search_terms WHERE id = $id";
		
		$result = $this->wpdb->get_row($sql);
		
		// check that state is active
		if ('paused' == $result->state)
		{
			// update searchterm's state to 'paused'
			$sql = "UPDATE $this->db_table_search_terms SET `state` = 'active' WHERE id = $id";
			
			$this->wpdb->query($sql);
			
			// if next_runtime is smaller than time
			$timestamp = ($result->sr_next_runtime < time()) ?
			// set it to time, otherwise leave next run time
			time() : $result->sr_next_runtime ;
			
			// add wp cron hook
			$recurrance = $this->set_schedule($result->run_num, $result->run_period, $result->humanized);
			wp_schedule_event($timestamp, $recurrance, 'CMpost_hook', array($id));
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	// run now
	public function run_now($id=0,$network='rand')
	{		
		$id = (int) $id;
		
		check_admin_referer('run-now-' . $id);
		
		//Ricky MOD
		if($id==0){
			//select 1 to RUN NOW - This is for testing only
			//Assums you have 1 Search Term added with
			//all the options desired stored in the DB
			$sql = "SELECT id, run_num, run_period, humanized FROM $this->db_table_search_terms ORDER BY run_num LIMIT 1";
		}else{
			$sql = "SELECT run_num, run_period, humanized FROM $this->db_table_search_terms WHERE id = $id";
		}
		if (false != ($result = $this->wpdb->get_row($sql)))
		{
			//Ricky mod
			if($id==0){
				//Based on the above test
				//needed to GET the ID!!
				$id=$result->id;			
			}
			// we set stat row to 0 to notify stat row that we are adding a video
			$sql = "UPDATE $this->db_table_search_terms SET `sr_post_id` = 0, `sr_video_chosen` = 0, `sr_videos_found` = 0, `sr_next_runtime` = 0 WHERE id = $id";
			$this->wpdb->query($sql);
			
			// delete hook
			wp_clear_scheduled_hook('CMpost_hook', $id);
			$this->remove_schedule($result->run_num, $result->run_period, $result->humanized);
			
			// set it again NOW!! Ricky
			$recurrance = $this->set_schedule($result->run_num, $result->run_period, $result->humanized);
			wp_schedule_event(time(), $recurrance, 'CMpost_hook', array($id));
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	// edit post code
	public function edit_post_code()
	{
		check_admin_referer('edit-post-code');
		
		// write option to the database
		update_option('CMpostcode', stripslashes($_POST['CMpostcode']));
		
		return true;
	}
	
	// set stat row
	public function set_stat_row($searchterm_id, $post_id, $video_num, $videos_found)
	{
		$searchterm_id = (int) $searchterm_id;
		
		// get next run time
		$sr_next_runtime = wp_next_scheduled('CMpost_hook', array($searchterm_id));
		
		$sql = "UPDATE $this->db_table_search_terms SET `sr_post_id` = '$post_id', `sr_video_chosen` = '$video_num', `sr_videos_found` = '$videos_found', `sr_next_runtime` = '$sr_next_runtime' WHERE id = $searchterm_id";
		
		return $this->wpdb->query($sql);
	}
	
	///////////////////////////////////////////////////////////////////
    
	//Antero+
    function seleCMkey()
    {
        global $wpdb;
        $result = array();
        $sql = "SELECT CMkeyword FROM ".$this->wpdb->prefix . "CMkeyword";
        $arr_obj = $wpdb->get_results($sql);                  
        
        foreach($arr_obj as $obj)
        {
            $result[] =  $obj->CMkeyword;
        }
        
        return $result;
    }
	
	
	//RICKY Insert CB Products	
	function _add_clickbank_products()
	{
		
		$url ="http://edwinboiten.com/blogcashcow/clickbank_products.csv";
		$row = 0;
		if (($handle = fopen($url, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 4000, '|')) !== FALSE) {
					if ($row!=0){
					$num = count($data);
					$sql_field_values="";
						for($i=0;$i<$num;$i++){
						$sql_field_values .="'". mysql_real_escape_string($data[$i]) ."',";
						}
					$sql_field_values = substr($sql_field_values, 0, -1);
					$sql = @mysql_query("INSERT INTO ".$this->db_table_clickbank_products." (id,clickbank_id,produCMname,description,image,no_adjust) VALUES ($sql_field_values)");
					}
		$row++;
			}	
		}
	
		/*
		$url ="http://edwinboiten.com/blogcashcow/clickbank_categories.csv";
		$row = 0;
		if (($handle = fopen($url, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 4000, '|')) !== FALSE) {
					if ($row!=0){
					$num = count($data);
					$sql_field_values="";
						for($i=0;$i<$num;$i++){
						$sql_field_values .="'". mysql_real_escape_string($data[$i]) ."',";
						}
					$sql_field_values = substr($sql_field_values, 0, -1);
					$sql = @mysql_query("INSERT INTO ". $this->db_table_clickbank_categories ." (id,parent_id,category_name,clickbank_cat_id,clickbank_cat_path) VALUES ($sql_field_values)");
					}
		$row++;
			}
		}
		$url ="http://edwinboiten.com/blogcashcow/clickbank_salesrank.csv";
		$row = 0;
		if (($handle = fopen($url, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 4000, '|')) !== FALSE) {
					if ($row!=0){
					$num = count($data);
					$sql_field_values="";
						for($i=0;$i<$num;$i++){
						$sql_field_values .="'". mysql_real_escape_string($data[$i]) ."',";
						}
					$sql_field_values = substr($sql_field_values, 0, -1);
					$sql = @mysql_query("INSERT INTO ".$this->db_table_clickbank_salesrank." (id,category_id,produCMid,sales_rank) VALUES ($sql_field_values)");
					}
		$row++;
			}		
		}
		*/
	
	}	
	
	
}
?>