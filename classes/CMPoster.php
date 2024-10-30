<?php

/*
Separate class for posting videos
*/

//Function SET  - Antero+
require_once("CMArticles.php");
//Network Class - Ricky
require_once("CMnetworks.php");
//Class Spin
require_once("CMSpinx.php");


class CMPoster extends CMnetworks
{
	private $model;
	private $post_comment_status = 'open'; // post comment status open or closed for commenting
	private $post_ping_status = 'open'; // post ping status open or closed
	public $BestSpiner;
	public $store_log='';
	
	public function __construct()
	{
		// make a new model
		$this->model = new CMModel;
		$this->BestSpiner = new SpinMe;
	}
	
	/* This Does The Posting */
	public function post_video($id)
	{
			
		// a lil check :)
		$id = (int) $id;
        // get search term info
		$sql = "SELECT * FROM {$this->model->db_table_search_terms} WHERE id = $id";
		$result = $this->model->wpdb->get_row($sql);
		
		//Ricky USE Publisher Network
		$network=$result->network;
		
		// if there is such search term and it's not adding a new video right now
		if (null != $result && 0 == $result->adding_video)
		{
			
			// we set adding_video flag to 1 // Same Thing For Articles
			$sql = "UPDATE {$this->model->db_table_search_terms} SET `adding_video` = 1 WHERE id = $id";
			$this->model->wpdb->query($sql);
			
			// we choose function's name for search_type
			switch($result->search_type)
			{
				case 'videos':
				$yt_videos_func = 'CMyoutube_videos';
				break;
                
				case 'profile':
				$yt_videos_func = 'CMyoutube_profile';
				break;

				case 'most_viewed':
				$yt_videos_func = 'CMyoutube_most_viewed';
				break;

				case 'top_rated':
				$yt_videos_func =  'CMyoutube_top_rated';
				break;

				case 'recently_featured':
				$yt_videos_func = 'CMyoutube_recently_featured';
				break;

				case 'categories_keywords':
				$yt_videos_func = 'youtube_categories_keywords';
				break;
				//Ricky ARTICLE X
                case 'articles':
                $yt_videos_func = 'CMarticles';
                break;		
						
				default:
				return false;
			}

			//Ricky Get Network
			switch($network)
			{
				case 'amazon':
				$CMnetwork_func = 'CMget_amazon';
				break;
                
				case 'linkshare':
				$CMnetwork_func = 'CMget_linkshare';
				break;

				case 'cj':
				$CMnetwork_func = 'CMget_cj';
				break;

				case 'ebay':
				$CMnetwork_func =  'CMget_ebay_content';
				break;

				case 'clickbank':
				$CMnetwork_func = 'CMget_clickbank_content';
				break;
							
				default:
				$CMnetwork_func = 'CMget_amazon';
				break;
			}
			
			
			////// NOW WE CALL THEM WHEN INJECTING SON
			
			
			// now we try each video and find a unique one
			$loop_number = 0;
			$videos_per_try = 50; // how many videos get per one request
			$video_num = 0; // counts each video
			$videos_found = 0; // zero currently
			$video_new = false; // false by default
            
            if ($result->search_type !='articles')
            {
			    do
			    {
				    ++$loop_number; // increment it right away
				    
				    // get videos
				    $videos = $this->$yt_videos_func($result->search_query, $loop_number, $videos_per_try);
				    
				    // set total num of videos for this query
				    #$videos_found = $videos->totalResults->text;
				    $counts = $videos->children('http://a9.com/-/spec/opensearch/1.1/');
				    $videos_found = $counts->totalResults;
				    
				    foreach ($videos->entry as $video)
				    {
					    // loop thru each video, to find a unique one
					    
					    // we increment $video_num right way, the first video will be #1
					    ++$video_num;
					    
					    // get nodes in media: namespace for media information
					    $media = $video->children('http://search.yahoo.com/mrss/');
					    
					    // get nodes in yt: namespace
					    $yt_ns = $media->children('http://gdata.youtube.com/schemas/2007');
					    
					    // get content attributes for video url
					    $cont_attr = $video->content->attributes();
					    
					    // if we have such content type
					    if ("application/x-shockwave-flash" == $cont_attr['type'])
					    {
						    // get video url
						    $video_url = (string) $cont_attr['src'];
					    }
					    // if we couldn't get video_url, skip the video
					    else
					    {
						    // this should not happen, but just in case
						    continue;
					    }
					    
					    // we get its video id
					    $video_id = $yt_ns->videoid;
					    
					    //RICKY this double checks that WE have the video
						//Already Stored Not to Duplicate
						//However This is not possible with Articles
						
						// and see whether we have this id (video) in our database already
					    $sql = "SELECT id FROM {$this->model->db_table_added_videos} WHERE video_id = '$video_id'";
					    
					    $a = $this->model->wpdb->get_var($sql);
					    
					    // if there is no such video, we have a unique one
					    if (null == $a && false !== $a)
					    {
						    $video_new = $media;
						    
						    // we break out of the loop... twice
						    break 2;
					    }
				    }
			    }
			    while ($loop_number * $videos_per_try < $videos_found);
			    
			    
			    // if we have a unique video
			    if (false !== $video_new)
			    {
				    // get post_code
					// This gets the posting code in where we need to inject shit -- Ricky
				    if (false === ($CMpostcode = get_option('CMpostcode')))
				    {
					    // if for some reason it couldn't get it, fallback to default post code
					    $CMpostcode = $this->model->default_post_code;
				    }
					
					//Ricky Try to get product
					$video_description=$video_new->group->description;
					$keywords=$this->CMget_content_keywords($content);
					sleep(1);	

					$this->_log('Any Video Keywords Worth While?');
					$this->_log($keywords);					
					
					if(count($keywords)>0){
						$rand_keyword=$keywords[array_rand($keywords)];	
						$this->_log('Video Yahoo: '.$rand_keyword);
					}else{
						//Yahoo Did not come back with
						//anything good so we need to default
						$rand_keyword=$result->search_query;	
						$this->_log('Video Default: '.$rand_keyword);
					}

					//Call Network Function
					$items=$this->$CMnetwork_func($rand_keyword);
					sleep(1);					

					$this->_log('Video Calling:'. $CMnetwork_func);
					$this->_log($items);														
					
				    $pattern = array(
					    "/{video_url}/i",
					    "/{video_description}/i",
					    "/{search_query}/i",
				    );
				    
				    $replacement = array(
					    $video_url, // this has been set already before
					    $video_new->group->description,
					    $result->search_query,
				    );
                    
				    // substitute values in post content
				    $post_content = preg_replace($pattern, $replacement, $CMpostcode);
					
					/// INJECT ADS INTO CONTENT -- Ricky
					if(count($items)>0){
						$rand_item=$items[array_rand($items)];		
						$item_string=$rand_item['clean'];
					}					
					//Final Content
					$post_content=$post_content.'<br><br>'.$item_string;
					/// INJECT ADS INTO CONTENT					
				    
				    // and create a post
				    $CMpost = array(
					    'post_title' => $this->model->wpdb->escape($video_new->group->title),
					    'post_content' => $this->model->wpdb->escape($post_content),
					    'post_author' => $result->post_author_id,
					    'post_status' => $result->post_status,
					    'post_category' => array($result->category_id), // category id
					    'tags_input' => $this->model->wpdb->escape($video_new->group->keywords),
					    'comment_status' => $this->post_comment_status,
					    'ping_status' => $this->post_ping_status,
					    );
				    
				    // remove wp internal filters for posts (to allow <object> tag)
				    kses_remove_filters();
				    
				    // insert the post
				    $post_id = wp_insert_post($CMpost);
				    
				    // we add removed filters back
				    kses_init_filters();
				    
				    // add video to added videos table
					// Records The Video Added - Not sure needed for articles
				    $this->add_video_2db($video_id, $post_id);
				    
				    // if we are allowed to add comments
				    if ("yes" == $result->post_comments)
				    {
					    // add comments to the post
					    $this->add_comments($post_id, $video_id, $result->post_status);
				    }
								    
				    // if humanize posts is set
				    if (1 == $result->humanized)
				    {
					    // call this func to change humanize post schedule seconds for search term
					    $this->model->set_schedule($result->run_num, $result->run_period, $result->humanized);
				    }
				    
				    // set stat row
				    $this->model->set_stat_row($id, $post_id, $video_num, $videos_found);
			    }
			    // if we couldn't find a new video
			    else
			    {
				    // if humanize posts is set
				    if (1 == $result->humanized)
				    {
					    // call this func to change human post schedule seconds for search term
					    $this->model->set_schedule($result->run_num, $result->run_period, $result->humanized);
				    }
				    
				    // set stat row for videos wasn't found
				    $this->model->set_stat_row($id, 0, 0, $videos_found);
			    }
			    
			    // set adding_video back to 0
			    $sql = "UPDATE {$this->model->db_table_search_terms} SET `adding_video` = 0 WHERE id = $id";
			    $this->model->wpdb->query($sql);
			    
			    // regenrate wp cron hooks, schedules for about every 30th run
			    if (0 == mt_rand() % 30)
			    {
				    $this->model->cron_schedules_regen();
			    }
            }
            else if($result->search_type == 'articles')  //Article test and post code Antero+
            {			 
		          
			    //calling Article X
				$articles = $this->$yt_videos_func($result->search_query);
				sleep(2);
				
				$this->_log( $articles );
								
				if ($articles == false )
                {
				    $this->_log('Didnt Get Articles');
					
					// if humanize posts is set
				    if (1 == $result->humanized)
				    {
					    // call this func to change human post schedule seconds for search term
					    $this->model->set_schedule($result->run_num, $result->run_period, $result->humanized);
				    }
				    // set stat row for videos wasn't found
				    $this->model->set_stat_row($id, 0, 0, count($articles));

					// set adding_video back to 0
					$sql = "UPDATE {$this->model->db_table_search_terms} SET `adding_video` = 0 WHERE id = $id";
					$this->model->wpdb->query($sql);
					
					// regenrate wp cron hooks, schedules for about every 30th run
					if (0 == mt_rand() % 30)
					{
						$this->model->cron_schedules_regen();
					}
					
                }
                else
                {
					//Ricky Spin and Inject Product				
					$use_article=$articles[array_rand($articles)];				
					$article_title = $use_article["title"];
					$content = $use_article["content"];
					
					if(strlen($content)>5)
					{
						$this->_log('HAVE ARTICLE == '.$article_title.$content);
						
						//Explores the content with YAHOO
						//this in gets the most likey product
						//keywords to get the products based
						//on the content.
						$keywords=$this->CMget_content_keywords($content);
						sleep(1);
	
						$this->_log('Any Keywords Worth While?');
						$this->_log($keywords);					
						
						if(count($keywords)>0){
							$rand_keyword=$keywords[array_rand($keywords)];	
							$this->_log('Yahoo: '.$rand_keyword);
						}else{
							//Yahoo Did not come back with
							//anything good so we need to default
							$rand_keyword=$result->search_query;	
							$this->_log('Default: '.$rand_keyword);
						}
	
						//Call Network Function
						$items=$this->$CMnetwork_func($rand_keyword);
						sleep(1);					
	
						$this->_log('Calling:'. $CMnetwork_func);
						$this->_log($items);
						
					
						//Ricky Spin IT
						$this->_log('Spinning Content');
						$new_content=$this->spinx('',$content);
						
						if($new_content==''){
							$this->_log('Spinning Failed - Reverting To Old');
							$new_content=$content;
						}
						/// INJECT ADS INTO CONTENT -- Ricky
						if(count($items)>0){
							$rand_item=$items[array_rand($items)];		
							$item_string=$rand_item['clean'];
						}
						//Final Content
						$post_content=$new_content.'<br>'.$item_string;
						
						$this->_log('POSTING Content');
						$this->_log($post_content);
									  
						$CMpost = array(
							'post_title' => $this->model->wpdb->escape($article_title),
							'post_content' => $this->model->wpdb->escape($post_content),
							'post_author' => $result->post_author_id,
							'post_status' => $result->post_status,
							'post_category' => array($result->category_id), // category id
							'tags_input' => '',
							'comment_status' => $this->post_comment_status,
							'ping_status' => $this->post_ping_status,
							'post_content_filtered' => '',
							'post_excerpt' => ''
							);
						
						 //POST The Article
						 $post_id = wp_insert_post($CMpost);
		
						 // if humanize posts is set
						 if (1 == $result->humanized)
						 {
							// call this func to change humanize post schedule seconds for search term
							$this->model->set_schedule($result->run_num, $result->run_period, $result->humanized);
						 }
						
						 // set stat row
						 $this->model->set_stat_row($id, $post_id, 1, count($articles));							
					}
					else
					{
						//No Content NO POST
						$this->_log('Content Was Left Blank');
						
						// if humanize posts is set
						if (1 == $result->humanized)
						{
							// call this func to change human post schedule seconds for search term
							$this->model->set_schedule($result->run_num, $result->run_period, $result->humanized);
						}
						// set stat row for videos wasn't found
						$this->model->set_stat_row($id, 0, 0, 0);
	
						// set adding_video back to 0
						$sql = "UPDATE {$this->model->db_table_search_terms} SET `adding_video` = 0 WHERE id = $id";
						$this->model->wpdb->query($sql);
						
						// regenrate wp cron hooks, schedules for about every 30th run
						if (0 == mt_rand() % 30)
						{
							$this->model->cron_schedules_regen();
						}
					}
                }    
            }
		}
		// couldn't find such search term or it's already adding a video right now
		else
		{
			$this->_log('Could Not Find Any Articles Or Videos');
			return false;
		}
		//write log if DEBUG TRUE
		$this->_log_commit();
	}
	
  
	///***** ARTICLE X FUNCTIONS ***********************************

    //Antero Get Articles
	public function CMarticles($keyword)
    {
        if($keyword != '' && $keyword !=null )
        {
            //mail('rickmataka@gmail.com','getting articles',$keyword);
			$articles = articlex($keyword);  
            return $articles;  
        }
        else
        {
            return false;
        }
    }
	
	//Ricky Spinx
	public function spinx($spin_title, $content, $quality=1, $excluded=FALSE, $identify = FALSE ) 
	{
		set_time_limit(0);
		$return = $this->BestSpiner->spinText( $content, 'replaceEveryonesFavorites', '1', $quality, $excluded);
		return $return;
	}

  
	///***** HELPER FUNCTIONS ***********************************	

	// add comments
	private function add_comments($post_id, $videoid, $post_status)
	{
		// set comment status to unapproved
		if ("pending" == $post_status || "draft" == $post_status)
		{
			$comment_approved = 0;
		}
		// set comment status to approved
		else
		{
			$comment_approved = 1;
		}
		
		// get video comments
		$video_comments = $this->simpleXML("http://gdata.youtube.com/feeds/api/videos/$videoid/comments");
		
		// gmt offset seconds
		$gmt_offset_seconds = get_option('gmt_offset') * 3600;
        
		foreach ($video_comments->entry as $entry)
		{
			$commentdata = array(
				'comment_post_ID' => $post_id,
				'comment_author' => $entry->author->name,
				'comment_author_email' => "wp@video.tube",
				'comment_author_IP' => '0.0.0.0', // none
				'comment_approved' => $comment_approved, // approved or unapproved comment
				// escape comment text when adding it to the db
				'comment_content' => $this->model->wpdb->escape($entry->content),
				'user_ID' => 0,
				'comment_agent' => '',
				'comment_author_url' => '',
			);
			
			// get timestamp of 2007-09-19T05:36:50.000Z
			preg_match("/^([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}).?([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $entry->updated, $matches);
			
			$updated_gmt_seconds = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
			
			// set local and GMT time
			$commentdata['comment_date']     = date("Y-m-d H:i:s", $updated_gmt_seconds + $gmt_offset_seconds);
			$commentdata['comment_date_gmt'] = date("Y-m-d H:i:s", $updated_gmt_seconds);
			
			// we need this
			$commentdata = wp_filter_comment($commentdata);
			
			#black list check
			if ( wp_blacklist_check($commentdata['comment_author'], $commentdata['comment_author_email'], "", $commentdata['comment_content'], $commentdata['comment_author_IP'], "") )
			{
				$commentdata['comment_approved'] = 'spam';
			}
        
			wp_insert_comment($commentdata);
		}
        
		return true;
	}
			
	// add video to added videos table
	private function add_video_2db($video_id, $post_id)
	{	
		$sql = "INSERT INTO {$this->model->db_table_added_videos} (`video_id`, `post_id`) 

		VALUES ('$video_id', '$post_id')";
		
		return $this->model->wpdb->query($sql);
	}
    
    //add article to added article table Antero+
    private function add_article_2db($article_id,$post_id)
    {
        $sql = "INSERT INTO {$this->model->db_table_added_articles} (`article_id`, `post_id`) 
        VALUES ('$article_id', '$post_id')";
        
        return $this->model->wpdb->query($sql);
    }
	
	//Ricky Extract keywords From Content
	//this would be the best way to get products
	//for the trending ideas.. this is like
	//second level keywords
	function CMget_content_keywords($postdata){
	$this_content = $postdata;
	$this_content = strip_tags($this_content);
	$this_content = str_replace("\"","",$this_content);
	$this_content =urlencode($this_content);
	//lets do some magic :)
	$url = 'http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20contentanalysis.analyze%20where%20text%3D%22'. $this_content .'%22';
	$data = $this->CMget_page($url,'');
	$xml_response = $data[EXE];
	$xml= @simplexml_load_string($xml_response);
	
	$this->_log('Got Yahoo XML');
	$this->_log($xml_response);
	
	  if ($xml->results->entities){
		 foreach($xml->results->entities->entity as $this_entity){
			foreach($this_entity->attributes() as $a => $b) { 
				if($b>0.76){
				$this_key = $this_entity->text;
				$keywords[]=$this_key;
				}
			}
		}
	  }
	return $keywords;
	}		
		
	// blog posts
	public function blog_posts()
	{
		// include required file
		require_once(ABSPATH . WPINC . '/rss.php');
		
		// use Magpie rss from /wp-includes/rss.php
		init(); // init
		$resp = _fetch_remote_file('http://www.neil-harvey.com/feed');
		if (is_success($resp->status)) {
			$rss = _response_to_rss($resp);
			
			// we get last 3 blog's entries
			$blog_posts = array_slice($rss->items, 0, 3);
			
			$posts_arr = array();
			foreach ($blog_posts as $item)
			{
				$posts_arr[] = array(
					'title' => $item['title'],
					'description' => $item['description'],
					'link' => $item['link'],
				);
			}
			
			// write everything to an option
			if (false === get_option('CMblog_posts'))
			{
				add_option('CMblog_posts', $posts_arr);
			}
			else
			{
				update_option('CMblog_posts', $posts_arr);
			}
		} else {
			// if couldn't fetch posts
			return false;
		}
	}
	
	public function _log( $message ) {
		if(CMDEBUG == 1){
		 if( is_array( $message ) || is_object( $message ) ){
		   $this->store_log.=print_r( $message, true );
		   $this->store_log.="\n\n";
		  } else {
			$this->store_log.=$message."\n\n";
		  }
		} else { return false; }
	  }


	public function _log_commit() {
		if(CMDEBUG == 1){
			$logerror='['.date('Y-m-d h:m:s').']'."\n\n";
			$logerror.=$this->store_log;
			if(file_exists(CMPATH.'/CMerror_log.txt')){
			$fp=fopen(CMPATH.'/CMerror_log.txt','a+');
			}else{
			$fp=fopen(CMPATH.'/CMerror_log.txt','w+');
			}
			fwrite($fp,$logerror);
			fclose($fp);
		} else { return false; }
	  }
	  
	/*  
	//$old_error_handler = set_error_handler("my_error_handler");
	function CMcustom_error($errno, $errstr, $errfile, $errline)
	{  
		  switch ($errno) {
			case E_USER_ERROR:
			  error_log("Error: $errstr \n Fatal error on line $errline in file $errfile \n", 3, CMPATH.'/CMerror_log.txt');
			  break;
		 
			case E_USER_WARNING:
			  // Write the error to our log file
			  error_log("Warning: $errstr \n in $errfile on line $errline \n", 3, CMPATH.'/CMerror_log.txt');
			  break;
		 
			case E_USER_NOTICE:
			  // Write the error to our log file
			  error_log("Notice: $errstr \n in $errfile on line $errline \n", 3, CMPATH.'/CMerror_log.txt');
			  break;
		 
			default:
			  // Write the error to our log file
			  error_log("Unknown error [#$errno]: $errstr \n in $errfile on line $errline \n", 3, CMPATH.'/CMerror_log.txt');
			  break;
		  }
	  // Don't execute PHP's internal error handler
	  return TRUE;
	}	  
	*/	  
}
?>