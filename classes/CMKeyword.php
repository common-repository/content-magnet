    <?php
   function fetch_page($url,$timeout=60,$return=1)
   {
        if ( function_exists('curl_init') ) 
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $response = curl_exec($ch); 
            curl_close($ch);
        }
        else 
        {
            $response = @file_get_contents($request);
        } 
        if($return==1)
        {
            return $response;
        }
        else
        {
            $response=''; 
        }
   }
   
     function fetch_alexa_hot()
     {
          $contents=fetch_page('http://www.alexa.com/whatshot');
          
          preg_match_all('/<div  id="hottopics">([^`]*?)<\/div>/', $contents, $links);
          $hot_topics=$links[0][0];
         
          preg_match_all("#title='([^`]*?)'#",$hot_topics,$links2);
          $result = array();
          $result = $links2[1];
          
          return $result;
     }
     
     //Ricky Fix 
     function fetch_alexa_product()
     {
         $hot_products=array();
		 $contents=fetch_page('http://www.alexa.com/whatshot');
          //Hot Products
          preg_match_all('/<ol start=\'1\' class="hoturls">([^`]*?)<\/ol>/', $contents, $links3);
		  //GET ALL LINKS
		  if(preg_match_all("/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU",$links3[0][0], $matches)) 
		  { 
		  	//print_r($matches);
			  foreach($matches[3] as $keys)
			  {
				$hot_products[]=$keys;
			  } 			
		  }		  
          return $hot_products;
     }     
         
     function fetch_google_hot()
     {
         $contents=fetch_page('http://www.google.com/trends/hottrends?sa=X');
         preg_match_all('#<table class="Z2_list">([^`]*?)<\/table>#', $contents, $links);

         $hot_topics=$links[1];
         $hotkeys = array();
          foreach ( $hot_topics as $hotnews ){
            preg_match_all('#sa=X">([^`]*?)<\/a>#', $hotnews, $links2);
            $hotkeys=array_merge($hotkeys, $links2[1]);
          }
          array_filter($hotkeys);
          return $hotkeys;
     }
     
     function fetch_ebay_hot()
     {
         
          $contents=fetch_page('http://pulse.ebay.com');

          preg_match_all('#<td width="99%"><[^`]*?>([^`]*?)<#', $contents, $links);
          $hot_topics=$links[1];
          
		  // preg_match_all("#<td width=\"99%\"><a[^`]*? >([^`]*?)<\/a>#", $hot_topics, $links2);
          //re-catch  
          //print_r( $hot_topics); 
		  
		 if ( 1==2){
          $hotkeys = array();
          foreach ( $hot_topics as $hotnews ){
            
            $hotkeys=array_merge($hotkeys, $links2[1]);
          }
		  if ( array_filter($hot_topics) ){
			preg_match_all('#<table<?:(satitle=.*?>)<[^`]*?>([^`]*?)<#', $contents, $links3);
		  }
		  
		  $hotkeys = $hot_topics;
          array_filter($hotkeys);
          
		  preg_replace("#<td align=\"100%\".*? >([0-9\w\W]*?>)<\/a>#", $link3, $link4);
		  }else{
			  $hotkeys = $hot_topics;
			  array_filter($hotkeys);
              /*
			  echo "<ul>";
			  foreach ( $hotkeys as $key ){
				echo "<li> $key </li>";
			  }
			  echo "</ul>";
              */
          }
          return $hotkeys;    //Antero +
         // print_r($hot_products);
          
          //exit();
          //return array('hot_topics'=>$hot_topics);
     }
     
     $google_result = fetch_google_hot();                 
     $alexa_result = fetch_alexa_hot();
     $ebay_result = fetch_ebay_hot();
	 $alexa_products = fetch_alexa_product();
     
     echo "<table width='1000'>";
     echo "<tr> 
	 	<td valign='top' style='padding:5px;width:15%;'>";
     foreach ($google_result as $google_hot)
     {
         echo '<ul><li><input type="checkbox" class="added-keys" name="keys[]" value = "'.$google_hot.'">   '.$google_hot.'</input> </li></ul>';
     } 
     echo "</td>";
     echo "<td valign='top' style='padding:5px;width:15%;'>";
     foreach ($alexa_result as $alexa_hot)
     {
         echo '<ul><li><input type="checkbox" class="added-keys" name="keys[]" value = "'.$alexa_hot.'">   '.$alexa_hot.'</input> </li></ul>';
     } 
     echo "</td>" ;
     
     echo "<td valign='top' style='padding:5px;width:15%;'>";
     foreach ($ebay_result as $ebay_hot)
     {
         echo '<ul><li><input type="checkbox" class="added-keys" name="keys[]" value = "'.$ebay_hot.'">   '.$ebay_hot.'</input> </li></ul>';
     } 
     echo "</td>" ;		
     echo "<td valign='top' style='padding:5px;width:500px;'>";
     foreach ($alexa_products as $alexap_hot)
     {
         echo '<ul><li><input type="checkbox" class="added-keys" name="keys[]" value = "'.$alexap_hot.'">   '.$alexap_hot.'</input> </li></ul>';
     } 
     echo "</td>" ;
     echo "</tr>";
     echo "</table>";     
?>