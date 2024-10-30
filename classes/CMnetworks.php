<?php

class CMnetworks{
	
	public $wpdb; // wpdb object
	public $db_table_clickbank_products;
	
	public function __construct()
	{
		global $wpdb; // grab $wpdb :)
		$this->wpdb = $wpdb;	
		$this->db_table_clickbank_products = $this->wpdb->prefix . "CMclickbank_products";
	}
	
	//Amazon  
	function CMget_amazon($keyword){
		$amazon_secret= get_option('CMamazon_api_secret');
		$amazon_public= get_option('CMamazon_api_public');
		$amazon_id= get_option('CMamazon_associate');
		$amazon_country= get_option('CMamazon_country');
		$ama_details = $this->CMamazon_tld_details($amazon_country);
		$ama_tld = $ama_details['ama_tld'];
			if($searchIndex==""){$searchIndex=$ama_details['searchIndex'];}
			if($ama_tld=="com"){
				 $xmlfeed = $this->CMamazon_signed_request($ama_tld, array("Operation"=>"ItemSearch","Keywords"=>$keyword,"ResponseGroup"=>"Large,Variations,VariationSummary","SearchIndex"=>$searchIndex, "AssociateTag"=>$amazon_id,"MinimumPrice"=>$minprice));
					 $ama_products = $this->CMamazon_search($xmlfeed);
			}else{
					  $xmlfeed = $this->CMamazon_signed_request($ama_tld, array("Operation"=>"ItemSearch","Keywords"=>$keyword,"ResponseGroup"=>"Large,Variations,VariationSummary","SearchIndex"=>$searchIndex, "AssociateTag"=>$amazon_id));
					  $ama_products = $this->CMamazon_search($xmlfeed);
			}
					  
		  return $ama_products;
	 }
	
	//Link Share  
	function CMget_linkshare($keyword,$minprice=0)
	{	
	  $ls_products =$this->CMget_linkshare_products($keyword,$minprice);	  
	  return $ls_products;
	}

	//CJ 
	function CMget_cj($keyword,$minprice=0)
	{
		$cj_products =$this->CMget_cj_products($keyword,$minprice);
		return $cj_products;
	
	}

	//EBAY
	function CMget_ebay_content($keyword)
	{
		$return=array();	
		$ebay_url = $this->CMebay_url($keyword);
		//Dont Work For Ebay
		//$resp = @simplexml_load_file($ebay_url);
		//Curl Work Around --Ricky
		$resp = $this->simpleXML($ebay_url);	
		$ebay_products =$this->CMebay_output($resp);
		  
	  return $ebay_products;
	}
	
	//CLICKBANK
	function CMget_clickbank_content($keyword){
		$clickbank_id=get_option('CMclickbank_id');
		$sql = @mysql_query("SELECT * FROM ".$this->db_table_clickbank_products." WHERE produCMname like '%".$keyword."%' OR description like '%".$keyword."%'");
		$i=0;
		while ($rij=@mysql_fetch_array($sql)){
			$products[$i]['title']=$rij['produCMname'];
			$products[$i]['link']='http://'. $clickbank_id .'.'. $rij['clickbank_id'].'.hop.clickbank.net';	
			$products[$i][clean]="<p><a href='".$products[$i]['link']."'>Click Here To Learn More...</a></p>";
			
		}
		return $products;
	}
	

	/***** NETWORKS ***********************************************************/
	/***** *  *     ***********************************************************/
	/***** *  *     ***********************************************************/


	/***** YOUTUBE  ***********************************************************/
	/***** *  *     ***********************************************************/
	/***** *  *     ***********************************************************/		

	
	// get videos by profile, returns videos!
	private function CMyoutube_profile($search_query, $start_index = 1, $max_results = 50)
	{
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/users/" . urlencode($search_query) . "/uploads?start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}
	
	// get most viewed videos
	private function CMyoutube_most_viewed($search_query, $start_index = 1, $max_results = 50)
	{
		// set video query
		$q = (!empty($search_query)) ? "q=" . urlencode($search_query) . "&" : null;
		
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/standardfeeds/most_viewed?{$q}start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}
	
	// get top rated videos
	private function CMyoutube_top_rated($search_query, $start_index = 1, $max_results = 50)
	{
		// set video query
		$q = (!empty($search_query)) ? "q=" . urlencode($search_query) . "&" : null;
		
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/standardfeeds/top_rated?{$q}start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}
	
	// get recently featured videos
	private function CMyoutube_recently_featured($search_query, $start_index = 1, $max_results = 50)
	{
		// set video query
		$q = (!empty($search_query)) ? "q=" . urlencode($search_query) . "&" : null;
		
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/standardfeeds/recently_featured?{$q}start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}
	
	// get videos for categories, keywords
	private function CMyoutube_categories_keywords($search_query, $start_index = 1, $max_results = 50)
	{
		/*
		Category start from an upper case letter "Category"
		and keywords from lower case letter "keyword keyword"
		*/
		
		// get words out of search query
		preg_match_all("/(\S+)/i", $search_query, $matches);
		
		// urlencode each word
		foreach ($matches[0] as $key => $value)
		{
			$matches[0][$key] = urlencode($value);
		}
		
		// create search query url
		$search_query_url = implode("/", $matches[0]);
		
		
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/videos/-/$search_query_url?start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}


    // just search for videos
	public function CMyoutube_videos($search_query, $start_index = 1, $max_results = 50)
	{
		/*
		format=5 - is for getting only embeddable videos
		*/
		
		$q = (!empty($search_query)) ? "q=" . urlencode($search_query) . "&" : null;
		
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/videos?{$q}&start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}
	

	// get videos by profile, returns videos!
	private function youtube_profile($search_query, $start_index = 1, $max_results = 50)
	{
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/users/" . urlencode($search_query) . "/uploads?start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}
	
	// get most viewed videos
	private function youtube_most_viewed($search_query, $start_index = 1, $max_results = 50)
	{
		// set video query
		$q = (!empty($search_query)) ? "q=" . urlencode($search_query) . "&" : null;
		
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/standardfeeds/most_viewed?{$q}start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}
	
	// get top rated videos
	private function youtube_top_rated($search_query, $start_index = 1, $max_results = 50)
	{
		// set video query
		$q = (!empty($search_query)) ? "q=" . urlencode($search_query) . "&" : null;
		
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/standardfeeds/top_rated?{$q}start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}
	
	// get recently featured videos
	private function youtube_recently_featured($search_query, $start_index = 1, $max_results = 50)
	{
		// set video query
		$q = (!empty($search_query)) ? "q=" . urlencode($search_query) . "&" : null;
		
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/standardfeeds/recently_featured?{$q}start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}
	
	// get videos for categories, keywords
	private function youtube_categories_keywords($search_query, $start_index = 1, $max_results = 50)
	{
		/*
		Category start from an upper case letter "Category"
		and keywords from lower case letter "keyword keyword"
		*/
		
		// get words out of search query
		preg_match_all("/(\S+)/i", $search_query, $matches);
		
		// urlencode each word
		foreach ($matches[0] as $key => $value)
		{
			$matches[0][$key] = urlencode($value);
		}
		
		// create search query url
		$search_query_url = implode("/", $matches[0]);
		
		
		if (false !== ($videos = $this->simpleXML("http://gdata.youtube.com/feeds/api/videos/-/$search_query_url?start-index=$start_index&max-results=$max_results&v=2&format=5")))
		{
			return $videos;
		}
		else
		{
			return false;
		}
	}


	/***** EBAY     ***********************************************************/
	/***** *  *     ***********************************************************/
	/***** *  *     ***********************************************************/	


	function CMebay_output($resp){
	$x = 1;
		// If the response was loaded, parse it and build links
		if ($resp->searchResult->item){
		foreach($resp->searchResult->item as $item) {
			$link  = $item->viewItemURL;
			$title = $item->title;
			$image = $item->galleryURL;
			$current_price= $item->sellingStatus->convertedCurrentPrice;
			$current_price=@number_format($current_price, 2, '.', '');	
		   foreach($item->sellingStatus->convertedCurrentPrice->attributes() as $a => $b)
		   {
		   $currency = $b;
		   }		
		$endtime = $this->CMebay_end_time($item->sellingStatus->timeLeft);
		$results[$x][title]=$title;
		$results[$x][link]=$link;
		$results[$x][image]=$image;
		$results[$x][price]=$current_price;
		$results[$x][currency]=$currency;
		$results[$x][endtime]=$endtime;
		//Ricky Clean Item
		$results[$x][clean]="<h2><a href='$link'>$title</a></h2><p><a href='$link'><img src='$image' /></a></p>"; //<br>(Hurry $endtime Left!)
		$x++;
		}
	return $results;
	}
	}
	
	function CMebay_url($keyword){
	$ebay_campaign = get_option('CMebaylist_campaignid');
	$ebay_country = get_option('CMebaylist_country');
	if($ebay_country==""){
	$ebay_country = "EBAY-US";
	}
	
	$ebay_max_results = 20;
	$ebay_min_price = get_option('CMebaylist_minprice');
	if($ebay_min_price==""){
	$ebay_min_price = 1;
	}
	$ebay_max_price = get_option('CMebaylist_maxprice');
	if($ebay_max_price==""){
	$ebay_max_price = 99999;
	}
	$ebay_itemsort = get_option('CMebaylist_itemsort');
	if($ebay_itemsort==""){
	$ebay_itemsort = "BestMatch";
	}
	$ebay_itemsortorder = get_option('CMebaylist_itemsortorder');
	if($ebay_itemsortorder==""){
	$ebay_itemsortorder = "Descending";
	}
	
	$query = str_replace(" ","%20",$keyword);
	$feedURL ="http://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsByKeywords&SERVICE-VERSION=1.9.0&SECURITY-APPNAME=EdwinBoi-afd2-4ef1-880d-927ea6833f3e&GLOBAL-ID=". $ebay_country ."&RESPONSE-DATA-FORMAT=XML&REST-PAYLOAD&affiliate.networkId=9&affiliate.trackingId=". $ebay_campaign ."&PriceMax.Value=". $ebay_max_price ."&PriceMin.Value=". $ebay_min_price ."&ItemSort=". $ebay_itemsort. "&sortOrder=". $ebay_itemsortorder ."&paginationInput.entriesPerPage=".$ebay_max_results."&keywords=". $query;
	return $feedURL;
	}
	
	function CMebay_end_time($endtime){
		$endtime=str_replace("PT","P",$endtime);
		$endtime=str_replace("P","",$endtime);
		$endtime=str_replace("DT","D",$endtime);	
		$endtime=str_replace("D"," Days ",$endtime);		
		$endtime=str_replace("H"," Hours ",$endtime);	
		$endtime=str_replace("M"," Minutes ",$endtime);
		$endtime=str_replace("S"," Seconds ",$endtime);
		$endtime=str_replace("0 Days","",$endtime);
		$endtime=str_replace("1 Days","1 Day",$endtime);
		$endtime=str_replace("0 Hours","",$endtime);
		$endtime=str_replace("1 Hours","1 Hour",$endtime);
		$endtime=str_replace("0 Minutes","",$endtime);
		$endtime=str_replace("1 Minutes","1 Minute",$endtime);
		$endtime=str_replace("0 Seconds","",$endtime);
		$endtime=str_replace("1 Seconds","1 Second",$endtime);
		return $endtime;
	}
	

	/***** LINK SHARE *********************************************************/
	/***** *  *       *********************************************************/
	/***** *  *       *********************************************************/	

	function CMget_linkshare_products($keyword,$min_price){
	$appid=get_option('CMlinkshare_api');
	$num=20;
	$start=1;
	//&cat="'.urlencode($cat).'"&sort='.$sort.'&sorttype=asc&merchant='.urlencode($merchant);	
	$request = 'http://productsearch.linksynergy.com/productsearch?token='.$appid.'&keyword="'.urlencode('sony cameras').'"&MaxResults='.$num.'&pagenumber='.$start; // The request URL used by the API service
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $request);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	$response = curl_exec($ch);
	$result['EXE'] = curl_exec($ch);
	$result['INF'] = curl_getinfo($ch);
	$result['ERR'] = curl_error($ch);	
	curl_close($ch);
	//print_r($result);
	$i = 0;
	$xml= @simplexml_load_string($result['EXE']);
	  if($xml->item){
		foreach ($xml->item as $item){
		$adid= $item->linkid;
		$merchant  = $item->merchantname;
		$upc  = $item->upccode;
		$sku = $item->sku;
		$link= $item->linkurl;
		$title = $item->productname;
		$description  = $item->description->long;
		$short_description  = $item->description->short;
		$mid  = $item->mid;
		$merchant_logo = "http://merchant.linksynergy.com/fs/logo/lg_". $mid;
		$merchant_logo1 =$merchant_logo;
		$merchant_logo1 .=".gif";
		$merchant_logo2 =$merchant_logo;
		$merchant_logo2 .=".jpg";
			if (@fopen($merchant_logo1, "r")) {
			$merchant_lg = $merchant_logo1;
			} else {
			$merchant_lg = $merchant_logo2;
			}
		$price= $item->price;
			if($min_price!=""){		  
				if($price<$min_price){break; }
			}
					
		$image = $item->imageurl;
		  foreach($item->price->attributes() as $a => $b)
		  {
		  $currency = $b;
		  }
			  if($adid!=""){
								$product[$i][title]=$title;
								$product[$i][description]=$description;
								$product[$i][image]=$image;
								$product[$i][link]=$link;
								$product[$i][currency]=$currency;
								$product[$i][price]=$price;
								$product[$i][advertiser_id]=$mid;
								$product[$i][advertiser_name]=$merchant;
								$product[$i][advertiser_logo]=$merchant_lg;
								$product[$i][clean]="<h2><a href='$link'>$title</a></h2><p><a href='$link'><img src='$image' /><br>$description</a><br></p>";
		
								
								
				  $i++;
				  if($i>24){break;}
				  }//end if adid
	  }//end for each
	  unset($xml);
	  }//end if xml
	return $product;
	}//end function



	/***** CJ       ***********************************************************/
	/***** *  *     ***********************************************************/
	/***** *  *     ***********************************************************/	

	function CMget_cj_products($keyword,$min_price){
	$cj_pid=get_option('CMcj_site'); 
	$cj_dev=get_option('CMcj_api'); 
	$cj_keyword = "%2B". urlencode($keyword);
	$cj_keyword = str_replace(" "," %2B", $cj_keyword);
	//BUG
	//$search = urlencode($keyword);
	$cj_dev = trim($cj_dev);
	$cj_pid = trim($cj_pid);
	if($min_price>0){$min_price_url="&low-price=". $min_price;}
	if ($cj_dev!="")
	{
	//$advertiser_ids_url="&advertiser-ids=joined";
	//$page_number=1;
	//$currency_url . $min_price_url . $max_price_url .
	$server ='https://producm-search.api.cj.com/v2/producm-search?website-id='.$cj_pid.'&keywords='.$cj_keyword.'&low-price='.$min_price.'&sort-order=Price&records-per-page=20';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $server);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Host: link-search.api.cj.com',
		'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8',
		'Authorization: '.$cj_dev,
		'Content-Type: application/xml'
		));	
	
	$result['EXE'] = curl_exec($ch);
	$result['INF'] = curl_getinfo($ch);
	$result['ERR'] = curl_error($ch);	
	curl_close($ch);
	
			if ($result['EXE']=="" || $result['EXE']=='You must specify a developer key'){
				//You must specify a developer key
				//also gets thrown for invalid keywords!! Ricky
				$cj_content=  "Error at connecting to CJ.com";
				}else{
				$i = 0; // set counter to handle display amounts
				$xml = @simplexml_load_string($result['EXE']);
					 if($xml->products->product){
					 
						foreach ($xml->products->product as $item){
						$advertiser_id = $item->{'advertiser-id'};
						$advertiser_name = $item->{'advertiser-name'};
						$link = $item->{'buy-url'}; 
						$currency = $item->currency;
						$description = $item->description;
						$image = $item->{'image-url'};
						$manufacturer = $item->manufacturer;
						$title = $item->name;
						$price = $item->price;
						$list_price = $item->{'retail-price'};
						$advertiser_name = str_replace("Affiliate Program", "",$advertiser_name);
						  if ($salePrice==""){
						  $salePrice=$price;
						  }
						  
						  if ($salePrice=="0.0"){
						  $salePrice=$price;
						  }
						  if ($salePrice=="0"){
						  $salePrice=$price;
						  }
						  
						  if ($salePrice==""){
						  $salePrice=$list_price;
						  }
							$product[$i][title]=$title;
							$product[$i][description]=$description;
							$product[$i][image]=$image;
							$product[$i][link]=$link;
							$product[$i][currency]=$currency;
							$product[$i][price]=$price;
							$product[$i][advertiser_id]=$advertiser_id;
							$product[$i][advertiser_name]=$advertiser_name;
							$product[$i][clean]="<h2><a href='$link'>$title</a></h2><p><a href='$link'><img src='$image' /><br>$description</a><br></p>";
						  $i++;
						  if($i>24){break;}
					}//foreach
				}//end if xml-products	
			}//end if results
		}//end if cj dev
		return $product;
	}//end function
	

	/***** AMAZON   ***********************************************************/
	/***** *  *     ***********************************************************/
	/***** *  *     ***********************************************************/	
	
	function CMamazon_signed_request($region, $params){
	$amazon_secret= get_option('CMamazon_api_secret');
	$amazon_public= get_option('CMamazon_api_public');
	$amazon_secret = trim($amazon_secret);
	$amazon_public = trim($amazon_public);
	   $method = "GET";
	   if($region=="it"){
	   $host = "webservices.amazon.it";
	   }else{
		 if($region=="es"){
		 $host = "webservices.amazon.es";
		 }else{
		 $host = "ecs.amazonaws.".$region;
		 }
	   }
		$uri = "/onca/xml";
		$params["Service"] = "AWSECommerceService";
		$params["AWSAccessKeyId"] = $amazon_public;
		$params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
		$params["Version"] = "2011-08-01";
		ksort($params);
		$canonicalized_query = array();
		foreach ($params as $param=>$value)
		{
			$param = str_replace("%7E", "~", rawurlencode($param));
			$value = str_replace("%7E", "~", rawurlencode($value));
			$canonicalized_query[] = $param."=".$value;
		}
		$canonicalized_query = implode("&", $canonicalized_query);
		$string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
		$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $amazon_secret, True));
		$signature = str_replace("%7E", "~", rawurlencode($signature));
		$request = "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
		return $request;
	}
	
	function CMamazon_tld_details($amazon_country){
	switch ($amazon_country)
	{
		case "USA":
		$searchIndex="All";
		$ama_tld = "com";
		$currency_sign = "$";
		break;
		
		case "UK":
		$searchIndex="All";
		$ama_tld="co.uk";
		$currency_sign="&#163;";
		break;
		
		case "Canada":
		$searchIndex="Blended";
		$ama_tld = "ca";
		$currency_sign="$";
		break;
		
		case "Germany":
		$searchIndex="Blended";
		$ama_tld="de";
		$currency_sign = "&#8364;";
		break;
		
		case "France":
		$searchIndex="Blended";
		$ama_tld ="fr";
		$currency_sign = "&#8364;";
		break;
		
		case "Italy":
		$searchIndex="All";
		$ama_tld ="it";
		$currency_sign = "&#8364;";
		break;
		
		case "Spain":
		$searchIndex="Blended";
		$ama_tld ="es";
		$currency_sign = "&#8364;";
		break;
		
		case "Japan":
		$searchIndex="Blended";
		$ama_tld="jp";
		$currency_sign="&#165;";
		break;
		}
		
		if ($ama_tld==""){
		$searchIndex="All";
		$ama_tld = "com";
		$currency_sign="&#165;";
		}
		
		$ama_details['ama_tld']=$ama_tld;
		$ama_details['searchIndex']=$searchIndex;
		$ama_details['currency_sign']=$currency_sign;
		return $ama_details;
	}
	
	function CMamazon_search($feedURL){	
	
	error_reporting(0);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $feedURL);
	$data = curl_exec($ch);
	curl_close($ch);
	
	$this->_log('Amazon Response');
	$this->_log($data);
	
	$xml  = @simplexml_load_string($data);
	$mark   = $xml->TotalPages;
	$error  = $xml->ErrorMsg;
	$product = array();
	$i=0;
		if($xml->Items){	
		foreach ($xml->Items->Item as $item){
			$link = $item->DetailPageURL;
			$medium_image = $item->MediumImage->URL;
			$large_image = $item->LargeImage->URL;
			$image = $medium_image;
			if($image==""){
			$image = $large_image;
			}
			$title = $item->ItemAttributes->Title;
			$title =substr($title, 0,120);
			$brand = $item->ItemAttributes->Brand;
			$amz_price= $item->Offers->Offer->OfferListing->Price->Amount;
			$currency= $item->OfferSummary->LowestNewPrice->CurrencyCode;				
			$description= $item->EditorialReviews->EditorialReview->Content;
			if ($amz_price==""){
			$amz_price= $item->ItemAttributes->ListPrice->Amount;
			$currency= $item->ItemAttributes->ListPrice->CurrencyCode;
			}
			
			if ($amz_price!=""){
			$amz1_price=substr($amz_price, 0, -2);
			$amz2_price=substr($amz_price, -2);
			$amazon_price =$amz1_price .".". $amz2_price;
					}else{
					$amazon_price="";
					}
			$list_price = $item->ItemAttributes->ListPrice->Amount;
			$amz1_list_price=substr($list_price, 0, -2);
			$amz2_list_price=substr($list_price, -2);
			$list_price =$amz1_list_price .".". $amz2_list_price;
			if($item->VariationSummary->LowestPrice){
					$currency = $item->VariationSummary->LowestPrice->CurrencyCode;
					$low_price = $item->VariationSummary->LowestPrice->Amount;
			$lowprice1=substr($low_price, 0, -2);
			$lowprice2=substr($low_price, -2);
			$low_price =$lowprice1 .".". $lowprice2;
			$amazon_price = $low_price;
			}
			if($item->VariationSummary->HighestPrice){
					$high_price = $item->VariationSummary->HighestPrice->Amount;
			$highprice1=substr($high_price, 0, -2);
			$highprice2=substr($high_price, -2);
			$high_price =$highprice1 .".". $highprice2;
			}
			//in case there is a variation called lowest saleprice
			if($item->VariationSummary->LowestSalePrice){	
			$currency = $item->VariationSummary->LowestSalePrice->CurrencyCode;
			$low_price = $item->VariationSummary->LowestSalePrice->Amount;
			$lowprice1=substr($low_price, 0, -2);
			$lowprice2=substr($low_price, -2);
			$low_price =$lowprice1 .".". $lowprice2;
			$amazon_price = $low_price;
			}
			if($item->VariationSummary->HighestSalePrice){
					$high_price = $item->VariationSummary->HighestSalePrice->Amount;
			$highprice1=substr($high_price, 0, -2);
			$highprice2=substr($high_price, -2);
			$high_price =$highprice1 .".". $highprice2;
			}	
			if($item->OfferSummary->LowestNewPrice){
			$lowest_price = $item->OfferSummary->LowestNewPrice->Amount;
						  if ($lowest_price==""){
						  $amazon_price="Too Low to Display";
						  }else{
						  $lowest_price1=substr($lowest_price, 0, -2);
						  $lowest_price2=substr($lowest_price, -2);
						  $lowest_price=$lowest_price1 .".". $lowest_price2;
						  }
			}
			if($item->OfferSummary->LowestUsedPrice){
			$lowest_usedprice = $item->OfferSummary->LowestUsedPrice->Amount;
						  if ($lowest_usedprice!=""){
						  $lowest_usedprice1=substr($lowest_usedprice, 0, -2);
						  $lowest_usedprice2=substr($lowest_usedprice, -2);
						  $lowest_usedprice=$lowest_usedprice1 .".". $lowest_usedprice2;
						  }
			}
			if($item->OfferSummary->LowestRefurbishedPrice){
			$lowest_refprice = $item->OfferSummary->LowestRefurbishedPrice->Amount;
						  if ($lowest_refprice!=""){
						  $lowest_refprice1=substr($lowest_refprice, 0, -2);
						  $lowest_refprice2=substr($lowest_refprice, -2);
						  $lowest_refprice=$lowest_refprice1 .".". $lowest_refprice2;
						  }
			if($lowest_usedprice==""){$lowest_usedprice=$lowest_refprice; }
			}
			if($amazon_price!=""){
			
			$product[$i][title]=$title;
			$product[$i][description]=$description;
			$product[$i][image]=$image;
			$product[$i][link]=$link;
			$product[$i][currency]=$currency;
			$product[$i][price]=$amazon_price;
			$product[$i][usedprice]=$lowest_usedprice;
			$product[$i][clean]="<h2><a href='$link'>$title</a></h2><p><a href='$link'><img src='$image' /><br>$description</a><br></p>";
			$i++;
			}
		  }
		}
		unset($xml);
		return $product;
	}

	/*****       ***********************************************************/
	/***** HELPER FUNCTIONS ************************************************/
	
	function CMsecondsToTime($lastTime,$firstTime)
	{
			$seconds = $firstTime - $lastTime;
			$hours = floor($seconds / (60 * 60));
			$divisor_for_minutes = $seconds % (60 * 60);
			$minutes = floor($divisor_for_minutes / 60);
			$divisor_for_seconds = $divisor_for_minutes % 60;
			$seconds = ceil($divisor_for_seconds);
			$obj = array(
				"h" => (int) $hours,
				"m" => (int) $minutes,
				"s" => (int) $seconds,
			);
			return $obj;
	}
	
	function CMcurrency_sign($currency){
	$currency_sign = $currency;
	$currency = strtolower($currency );
		switch($currency){
			case "usd":
			$currency_sign = "$";
			break;
			case "cdn":
			$currency_sign = "$";
			break;
			case "eur":
			$currency_sign = "&#8364;";
			break;
			case "euro":
			$currency_sign = "&#8364;";
			break;
			case "gbp":
			$currency_sign = "&#163;";
			break;
			case "yen":
			$currency_sign = "&#165;";
			break;
		}
		return $currency_sign;
	}

	function CMget_content_keywords_from_post($thispostid){
	$postdata = $this->CMget_post_data($thispostid);
	$this_content = $postdata[0]->post_content;
	$this_content = strip_tags($this_content);
	$this_content = str_replace("\"","",$this_content);
	$this_content =urlencode($this_content);
	//lets do some magic :)
	$url = 'http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20contentanalysis.analyze%20where%20text%3D%22'. $this_content .'%22';
	$data = CMget_page($url,'');
	$xml_response = $data[EXE];
	$xml= @simplexml_load_string($xml_response);
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

	function CMget_post_data($thispostid) {
		global $wpdb;
		return $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID=$thispostid");
	}	
	

	function CMget_page($url,$referer){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$result['EXE'] = curl_exec($ch);
		$result['INF'] = curl_getinfo($ch);
		$result['ERR'] = curl_error($ch);
		curl_close($ch);
		return $result;
	}
	

	public function simpleXML($url, $post_method = 0, $post_data = array())
	{
		$this->_log('Calling '.$url);		
		$response = $this->getCurl($url, $post_method = 0, $post_data = array());
		$this->_log('Response');
		$this->_log($response);		
		if (false !== $response)
		{
			$xml = simplexml_load_string($response);
			return $xml;
		}
		else
		{
			return false;
		}
	}
	
	// use only curl to make requests
	public function getCurl($url, $post_method = 0, $post_data = array())
	{
		$ch = curl_init($url);
		
		// GET is the default http method
		curl_setopt($ch, CURLOPT_HEADER, 0); // don't include header in the output
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // to return output as a string rather than echoin it
		
		
		// swich to POST
		if (1 == $post_method && is_array($post_data) && 0 < count($post_data))
		{
			// post_data - should be an array with the field name as key and field data as value
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}
		
		$output = curl_exec($ch);
		//error_log($url); error_log($output);
		curl_close($ch);
		
		return $output;
	}
		
	
}// END NETWORK CLASS