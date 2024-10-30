<?php
function articlex($keyword,$lang="en",$num=10) {
    global $wpdb,$CMtable_templates;
    
    $keyword2 = $keyword;    
    $keyword = str_replace( " ","+",$keyword );    
    $keyword = urlencode($keyword);
    
    $blist[] = "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)";
    $blist[] = "Mozilla/5.0 (compatible; Konqueror/3.92; Microsoft Windows) KHTML/3.92.0 (like Gecko)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; .NET CLR 1.1.4322; Windows-Media-Player/10.00.00.3990; InfoPath.2";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; InfoPath.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; Dealio Deskball 3.0)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; NeosBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
    $ua = $blist[array_rand($blist)];    

    $start=rand(1,10);
    
    $page = $start / 15;
    $page = (string) $page; 
    $page = explode(".", $page);    
    $page=(int)$page[0];    
    $page++;    

    if($page == 0) {$page = 1;}
    $prep = floor($start / 15);
    $numb = $start - $prep * 15;
    $lang = "en";

        if($lang == "en") {
            $search_url = "http://www.articlesbase.com/find-articles.php?q=$keyword&page=$page";
            $search_url_refer = "http://www.google.com/url?sa=t&rct=j&q=find-articles.php%20site%3Aarticlesbase.com&source=web&cd=1&ved=0CB0QFjAA&url=http%3A%2F%2Fwww.articlesbase.com%2Ffind-articles.php?q=$keyword&ei=wT2qTs6sGa2eiAfD0qC9Dw&usg=AFQjCNGTs5kw9wglWWyLUM93cxhTKvvvxg&cad=rja";
        } 
    /*
    elseif($lang == "fr") {
        $search_url = "http://fr.articlesbase.com/find-articles.php?q=$keyword&page=$page";    
    } elseif($lang == "es") {
        $search_url = "http://www.articuloz.com/find-articles.php?q=$keyword&page=$page";
    } elseif($lang == "pg") {
        $search_url = "http://www.artigonal.com/find-articles.php?q=$keyword&page=$page";
    } elseif($lang == "ru") {
        $search_url = "http://www.rusarticles.com/find-articles.php?q=$keyword&page=$page";
    }
    */

    // make the cURL request to $search_url
    if ( function_exists('curl_init') ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_URL,$search_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        //curl_setopt($ch, CURLOPT_REFERER, 'http://page2rss.com/0cb48809b4a879af8cb3dac48d2f8a7c/5115987_5150928');
        //curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/');
        curl_setopt($ch,CURLOPT_REFERER,$search_url_refer);        
        
        
        $html = curl_exec($ch);
        if (!$html) {
            die(curl_errno($ch).": ".curl_error($ch));
        }        
        curl_close($ch);
    } else {                 
        $html = @file_get_contents($search_url);
        if (!$html) {
            die('cURL is not installed on this server!');
        }
    }    

        // parse the html into a DOMDocument  
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
    
        // Grab Product Links  
        $xpath = new DOMXPath($dom);
        $paras = $xpath->query("//div//h3/a");
        
        $x = 0;
        $end = $numb + $num;
        
            if($paras->length == 0) {
                return $posts;        
            }    
        
        if($end > $paras->length) { $end = $paras->length;}
        for ($i = $numb;  $i < $end; $i++ ) {
        
            $para = $paras->item($i);
        
            if(empty($para)) {
                print_r($posts);
                return $posts;        
            } else {
            
                $target_url = $para->getAttribute('href'); // $target_url = "http://www.articlesbase.com" . $para->getAttribute('href');        
                
                // make the cURL request to $search_url
                if ( function_exists('curl_init') ) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Firefox (WindowsXP) - Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
                    curl_setopt($ch, CURLOPT_URL,$target_url);
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);
                    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
                    $html = curl_exec($ch);
                    if (!$html) {
                        die(curl_errno($ch).": ".curl_error($ch));    
                    }        
                    curl_close($ch);
                } else {                 
                    $html = @file_get_contents($target_url);
                    if (!$html) {
                        die('cURL is not installed on this server!');    
                    }
                }
    
                // parse the html into a DOMDocument  
    
                $dom = new DOMDocument();
                @$dom->loadHTML($html);
                    
                // Grab Article Title             
                $xpath1 = new DOMXPath($dom);
                $paras1 = $xpath1->query("//div/h1");
                $para1 = $paras1->item(0);
                $title = $para1->textContent;    
    
                // Grab Article    Syndicate Box
                //$xpath2 = new DOMXPath($dom);            
                //$paras2 = $xpath2->query("//textarea[@id='texttocopy']"); 
                //$para2 = $paras2->item(0);    
                //echo strip_tags($dom->saveXml($para2),'<p><strong><b><a><br>');            
                
                // Grab Article    
                $xpath2 = new DOMXPath($dom);
                //$paras2 = $xpath2->query("//div[@class='article_cnt KonaBody']"); 
                #//div[@class='article_cnt']/  <--- this broke it
                $paras2 = $xpath2->query("//div[@class='inner_body']"); 
                $para2 = $paras2->item(0);        
                $string = $dom->saveXml($para2);    
            
                $string = strip_tags($string,'<p><strong><b><a><br>');
                $string = str_replace('<div class="inner_body">', "", $string);    
                $string = str_replace("</div>", "", $string);
                $string = str_replace("&nbsp;", "", $string);                    
                
                
                
    
                //if($lang == "es") {$string = utf8_decode  (  $string  );    }
                $string = CMstrip_selected_tags($string, array('a','iframe','script'));
                $articlebody .= $string . ' ';
                $articlebody=str_replace(']]>','',$articlebody);            
                
                // Grab Ressource Box    
                $xpath3 = new DOMXPath($dom);
                $paras3 = $xpath3->query("//div[@class='author_details']/p");        //$para = $paras->item(0);        
                
                $ressourcetext = "";
                for ($y = 0;  $y < $paras3->length; $y++ ) {  //$paras->length
                    $para3 = $paras3->item($y);
                    $ressourcetext .= $dom->saveXml($para3);    
                }    
                
                $ressourcetext .= '<br/>Article from <a href="'.$target_url.'">articlesbase.com</a>';
                
                $title = utf8_decode($title);
            
            // Split into Pages
            //if($options['CMeza_split'] == "yes") {
                //$articlebody = wordwrap($articlebody,100, "<!--nextpage-->");
            //}
            
            /*
            $post = CMrandom_tags($articlebody);
            $post = str_replace("{article}", $articlebody, $post);            
            $post = str_replace("{authortext}", $ressourcetext, $post);    
            $noqkeyword = str_replace('"', '', $keyword2);
            $post = str_replace("{keyword}", $noqkeyword, $post);
            $post = str_replace("{Keyword}", ucwords($noqkeyword), $post);                
            $post = str_replace("{title}", $title, $post);    
            $post = str_replace("{url}", $target_url, $post);
            */
                            
            $posts[$x]["unique"] = $target_url;
            $posts[$x]["title"] = $title;
            $posts[$x]["content"] = $articlebody;    
            $articlebody='';            
            $x++;
        }    
    }    


    
    $posts += CMpressreleasepost($keyword,$num,1);
    $posts += CMezinemarkpost($keyword,$num,1);
    $posts += CMbukisapost($keyword,$num,1);
    $posts += CMgoarticlepost($keyword,$num,1);      
    $posts += CMarticlepost($keyword,$num,1);      
    
    
    return $posts;
}

function CMpressreleasepost($keyword,$num,$start,$optional="",$comments="") {
    global $wpdb,$CMtable_templates;
    
    if($keyword == "") {
        
        $return["error"]["module"] = "Press Release";
        $return["error"]["reason"] = "No keyword";
        $return["error"]["message"] = __("No keyword specified.","ctbot");
        return $return;    
    }    
    
    
    
    /*$template = $wpdb->get_var("SELECT content FROM " . $CMtable_templates . " WHERE type = 'pressrelease'");
    if($template == false || empty($template)) {
        $return["error"]["module"] = "Press Release";
        $return["error"]["reason"] =  "No template";
        $return["error"]["message"] = __("Module Template does not exist or could not be loaded.","ctbot");
        return $return;    
    }*/        
    //$options = unserialize(get_option("CMoptions"));
     $posts = array();
    
    $keyword2 = $keyword;    
    $keyword = str_replace( " ","+",$keyword );    
    $keyword = urlencode($keyword);
    
    $blist[] = "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)";
    $blist[] = "Mozilla/5.0 (compatible; Konqueror/3.92; Microsoft Windows) KHTML/3.92.0 (like Gecko)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; .NET CLR 1.1.4322; Windows-Media-Player/10.00.00.3990; InfoPath.2";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; InfoPath.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; Dealio Deskball 3.0)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; NeosBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
    $ua = $blist[array_rand($blist)];    

    $page = $start / 25;
    $page = (string) $page; 
    $page = explode(".", $page);    
    $page=(int)$page[0];    
    $page++;    

    if($page == 0) {$page = 1;}
    $prep = floor($start / 25);
    $numb = $start - $prep * 25;    
    
    $search_url = "http://www.prweb.com/Search.aspx?Search-releases=$keyword&start=$page";

    // make the cURL request to $search_url
    if ( function_exists('curl_init') ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'Firefox (WindowsXP) - Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
        curl_setopt($ch, CURLOPT_URL,$search_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        $html = curl_exec($ch);
        if (!$html) {
            $return["error"]["module"] = "Press Release";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = __("cURL Error Number ","ctbot").curl_errno($ch).": ".curl_error($ch);    
            return $return;
        }        
        curl_close($ch);
    } else {                 
        $html = @file_get_contents($search_url);
        if (!$html) {
            $return["error"]["module"] = "Press Release";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = __("cURL is not installed on this server!","ctbot");    
            return $return;        
        }
    }    

    // parse the html into a DOMDocument  

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Grab Product Links  

    $xpath = new DOMXPath($dom);
    $paras = $xpath->query("//div[@id='releases']//h3//a");
    
    $x = 0;
    $end = $numb + $num;
    if($end > $paras->length) { $end = $paras->length;}    
    
    if($end == 0 || $end == $numb) {
        $posts["error"]["module"] = "Press Release";
        $posts["error"]["reason"] = "No content";
        $posts["error"]["message"] = "No (more) pressreleases found.";    
        return $posts;        
    }    
    
    for ($i = $numb;  $i < $end; $i++ ) {
        $para = $paras->item($i);

        if($para == '' | $para == null) {
            $posts["error"]["module"] = "Press Release";
            $posts["error"]["reason"] = "No content";
            $posts["error"]["message"] = __("No (more) pressreleases found.","ctbot");    
            return $posts;        
        } else {
        
            $target_url = $para->getAttribute('href');
            
            // make the cURL request to $search_url
            if ( function_exists('curl_init') ) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERAGENT, 'Firefox (WindowsXP) - Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
                curl_setopt($ch, CURLOPT_URL, $target_url);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 45);
                $html = curl_exec($ch);
                if (!$html) {
                    $return["error"]["module"] = "Press Release";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] = __("cURL Error Number ","ctbot").curl_errno($ch).": ".curl_error($ch);    
                    return $return;
                }        
                curl_close($ch);
            } else {                 
                $html = @file_get_contents($target_url);
                if (!$html) {
                    $return["error"]["module"] = "Press Release";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] = __("cURL is not installed on this server!","ctbot");    
                    return $return;        
                }
            }

            // parse the html into a DOMDocument  

            $dom = new DOMDocument();
            @$dom->loadHTML($html);
                
            // Grab Press Release Title             
            $xpath1 = new DOMXPath($dom);
            $paras1 = $xpath1->query("//h1[@class='title']");
            $para1 = $paras1->item(0);
            $title = $para1->textContent;    

            // Grab Press Release Summary             
            $xpath1 = new DOMXPath($dom);
            $paras1 = $xpath1->query("//h2[@class='subtitle']");
            $para1 = $paras1->item(0);
            $summary = $para1->textContent;            

            // Grab Press Release Thumbnail             
            $xpath1 = new DOMXPath($dom);
            $paras1 = $xpath1->query("//div/div[@class='mediaBox']/div/img[@class='newsImage']"); 
            $para1 = $paras1->item(0);
            if(isset($para1)) {
                $imgurl = $para1->getAttribute('src');
            }
            if(!empty($imgurl)) {$thumbnail = '<img style="float:left;margin: 0 20px 10px 0;" src="'.$imgurl.'" />';} else {$thumbnail = "";}
            
            // Grab Press Release    
            $xpath2 = new DOMXPath($dom);
            //$paras2 = $xpath2->query("//div/div[@class='fullWidth floatLeft dottedTop']"); 
            $paras2 = $xpath2->query("//div/div[@class='one content']/div[@class='fullWidth floatLeft dottedTop']"); 
            $para2 = $paras2->item(0);        
            $string = $dom->saveXml($para2);


        //    $string = preg_replace('#\#\#\#(.*)#smiU', '', $string);
        //    $string = preg_replace('Share: (.*)#smiU', '', $string);    
            
        //    $string = preg_replace('#PRWeb News Center(.*)#smiU', '', $string);    
        //    $string = preg_replace('#Create Account(.*)#smiU', '', $string);    
            
        //    $string = preg_replace('#(.*)Printer Friendly Version#smiU', '', $string);    
        //    $string = preg_replace('#<h1 class="h1">(.*)</h1>#smiU', '', $string);
            $string = preg_replace('#<div(.*)<p class="releaseDateline">#smiU', '', $string);
            $string = preg_replace('#<div(.*)</div>#smiU', '', $string);
            $string = preg_replace('#<p style=\"text-align: center; font-weight: bold; clear: both;\">(.*)</p>#smiU', '', $string);
            $string = preg_replace('#<p style=\"text-align: center;  font-weight: bold;clear:both\">(.*)</p>#smiU', '', $string);
            $string = str_replace("clear:both", "", $string);
            $string = str_replace("clear: both", "", $string);                
            $string = str_replace("]]>", '', $string);            
            $string = str_replace('xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"&gt;', '', $string);            
            
            $string = strip_tags($string,'<p><strong><b><br><i><img>');        
            $string = str_replace("$", "$ ", $string);
            //$string = utf8_decode($string);
            $string = iconv('UTF-8','ISO-8859-1//IGNORE', $string);
            $pressreleasebody = $string;
            $pos2 = strpos($string, "301 Moved");    

                if ($pos2 !== false) {
                    $return["error"]["module"] = "Press Release";
                    $return["error"]["reason"] = "IncNum";
                    $return["error"]["message"] = __("Press release has been deleted or moved and was skipped.","ctbot");    
                    return $return;        
                }
            
                if (empty($pressreleasebody)) {
                    $return["error"]["module"] = "Press Release";
                    $return["error"]["reason"] = "No Content";
                    $return["error"]["message"] = __("No press release found.","ctbot");    
                    return $return;        
                }
                
            //$title = utf8_decode($title);
            $summary = iconv('UTF-8','ISO-8859-1//IGNORE', $summary);
            $title = iconv('UTF-8','ISO-8859-1//IGNORE', $title);
            
            /*$post = $template;
            $post = CMrandom_tags($post);*/
            $post = "";
            $post = str_replace("{pressrelease}", $pressreleasebody, $post);    
            $post = str_replace("{summary}", $summary, $post);    
            $post = str_replace("{thumbnail}", $thumbnail, $post);                
            $noqkeyword = str_replace('"', '', $keyword2);
            $post = str_replace("{keyword}", $noqkeyword, $post);
            $post = str_replace("{Keyword}", ucwords($noqkeyword), $post);                    
            
            $post = str_replace("{title}", $title, $post);    
            $post = str_replace("{url}", $target_url, $post);                
                    if(function_exists("CMtranslate_partial")) {
                        $post = CMtranslate_partial($post);
                    }
                    if(function_exists("CMrewrite_partial")) {
                        $post = CMrewrite_partial($post,$options);
                    }                    
            $posts[$x]["unique"] = $target_url;
            $posts[$x]["title"] = $title;
            $posts[$x]["content"] = $post;                
            $x++;
        }    
    }    
    return $posts;
}

function CMpressrelease_options_default() {
    $options = array(
    );
    return $options;
}

function CMpressrelease_options($options) {
    ?>
    <h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Press Release Options","ctbot") ?></h3>
        <table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform">     
            <tr valign="top"> 
                <td width="40%" scope="row"><?php _e("No options are available for this module.","ctbot") ?></td> 
            </tr>                        
        </table>        
    <?php
}
?>
<?php
// =============================== EZINEMARK.com ================================ //

function CMezinemarkpost($keyword,$num,$start,$optional="",$comments="") {
    global $wpdb,$CMtable_templates;

    $page = $start / 20;
    $page = (string) $page; 
    $page = explode(".", $page);    
    $page=(int)$page[0];    
    $page++;    

    if($page == 0) {$page = 1;}
    $prep = floor($start / 20);
    $numb = $start - $prep * 20;
    
    $keyword = str_replace( "+","-",$keyword );    
    $search_url = "http://ezinemark.com/a/$keyword/p-$page/";

    // make the cURL request to $search_url
    if ( function_exists('curl_init') ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
            if($proxy != "") {
                //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
                if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
                if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
            }            
        curl_setopt($ch, CURLOPT_URL,$search_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        $html = curl_exec($ch);
        if (!$html) {
            $return["error"]["module"] = "Article";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = "cURL Error Number ";
            return $return;
        }        
        curl_close($ch);
    } else {                 
        $html = @file_get_contents($search_url);
        if (!$html) {
            $return["error"]["module"] = "Article";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = "cURL is not installed on this server!";
            return $return;        
        }
    }    

    // parse the html into a DOMDocument  

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Grab Product Links  

    $xpath = new DOMXPath($dom);
    $paras = $xpath->query("//div[@class='scontent clearfix']//h3/a");
    
    $x = 0;
    $end = $numb + $num;
    
        if($paras->length == 0) {
            $posts["error"]["module"] = "Article";
            $posts["error"]["reason"] = "No content";
            $posts["error"]["message"] = __("No (more) articles found.","ctbot");    
            return $posts;        
        }    
    
    if($end > $paras->length) { $end = $paras->length;}
    for ($i = $numb;  $i < $end; $i++ ) {
    
        $para = $paras->item($i);
    
        if(empty($para)) {
            $posts["error"]["module"] = "Article";
            $posts["error"]["reason"] = "No content";
            $posts["error"]["message"] = __("No (more) articles found.","ctbot");    
            //print_r($posts);
            return $posts;        
        } else {
        
            $target_url = $para->getAttribute('href'); // $target_url = "http://www.articlesbase.com" . $para->getAttribute('href');        
            
            // make the cURL request to $search_url
            if ( function_exists('curl_init') ) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERAGENT, $ua);
                if($proxy != "") {
                    //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
                    curl_setopt($ch, CURLOPT_PROXY, $proxy);
                    if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
                    if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
                }                    
                curl_setopt($ch, CURLOPT_URL,$target_url);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 45);
                $html = curl_exec($ch);
                if (!$html) {
                    $return["error"]["module"] = "Article";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] = "cURL Error Number ";
                    return $return;
                }        
                curl_close($ch);
            } else {                 
                $html = @file_get_contents($target_url);
                if (!$html) {
                    $return["error"]["module"] = "Article";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] ="cURL is not installed on this server!";
                    return $return;        
                }
            }

            // parse the html into a DOMDocument  

            $dom = new DOMDocument();
            @$dom->loadHTML($html);
                
            // Grab Article Title             
            $xpath1 = new DOMXPath($dom);
            $paras1 = $xpath1->query("//div[@class='d_ctitle']/h2");
            $para1 = $paras1->item(0);
            $title = $para1->textContent;        
            
                if (empty($title)) {
                    $return["error"]["module"] = "Article";
                    $return["error"]["reason"] = "IncNum";
                    $return["error"]["message"] = "Video content skipped. ";
                    return $return;
                }                
            
            // Grab Article    
            $xpath2 = new DOMXPath($dom);
            $paras2 = $xpath2->query("//div[@id='art_content']"); 
            $para2 = $paras2->item(0);        
            $string = $dom->saveXml($para2);
        

            $string = str_replace('<div class="KonaBody">', "", $string);    
            $string = str_replace("]]>", "", $string);
            $string = str_replace("]]&gt;", "", $string);
            $string = str_replace("&nbsp;", "", $string);    
            //$string = preg_replace('#<ul>(.*)</ul>#smiU', '', $string);            
            $string = preg_replace('#<div class="related_links">(.*)</ul></div>#smiU', '', $string);
            $string = strip_tags($string,'<p><strong><b><a><br>');            
            $string = str_replace("</div>", "", $string);    
            $string = str_replace("$", "$ ", $string); 
            $string = str_replace("<div>", "", $string);        
            if ($options['CMeza_striplinks']=='yes') {$string = CMstrip_selected_tags($string, array('a','iframe','script'));}    
            $articlebody .= $string . ' ';    

            

            // Grab Ressource Box    

            $xpath3 = new DOMXPath($dom);
            $paras3 = $xpath3->query("//div[@class='authorbox']/div[@class='rightbl']//p");        //$para = $paras->item(0);        
            
            $ressourcetext = "";
            for ($y = 0;  $y < $paras3->length; $y++ ) {  //$paras->length
                $para3 = $paras3->item($y);
                $ressourcetext .= $dom->saveXml($para3);    
            }    
            
            $title = utf8_decode($title);
            
            // Split into Pages
            if($options['CMeza_split'] == "yes") {
                $articlebody = wordwrap($articlebody, $options['CMeza_splitlength'], "<!--nextpage-->");
            }
            
            $post = $template;
            $post = CMrandom_tags($post);
            $post = str_replace("{article}", $articlebody, $post);            
            $post = str_replace("{authortext}", $ressourcetext, $post);    
            $noqkeyword = str_replace('"', '', $keyword2);
            $post = str_replace("{keyword}", $noqkeyword, $post);
            $post = str_replace("{Keyword}", ucwords($noqkeyword), $post);                
            $post = str_replace("{title}", $title, $post);    
            $post = str_replace("{url}", $target_url, $post);
                    if(function_exists("CMrewrite_partial")) {
                        $post = CMrewrite_partial($post,$options);
                    }            
                    if(function_exists("CMtranslate_partial")) {
                        $post = CMtranslate_partial($post);
                    }    
                    
            $posts[$x]["unique"] = $target_url;
            $posts[$x]["title"] = $title;
            $posts[$x]["content"] = $post;                
            $x++;
        }    
    }    
    return $posts;
}

// =============================== BUKISA.com ================================ //

function CMbukisapost($keyword,$num,$start,$optional="",$comments="") {
    global $wpdb,$CMtable_templates;

    $page = $start / 10;
    $page = (string) $page; 
    $page = explode(".", $page);    
    $page=(int)$page[0];    
    $page++;    

    if($page == 0) {$page = 1;}
    $prep = floor($start / 10);
    $numb = $start - $prep * 10;

    $search_url = "http://www.bukisa.com/search?q=$keyword&where=0&page=$page";

    // make the cURL request to $search_url
    if ( function_exists('curl_init') ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
            if($proxy != "") {
                //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
                if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
                if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
            }            
        curl_setopt($ch, CURLOPT_URL,$search_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        $html = curl_exec($ch);
        if (!$html) {
            $return["error"]["module"] = "Article";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = "cURL Error Number ";
            return $return;
        }        
        curl_close($ch);
    } else {                 
        $html = @file_get_contents($search_url);
        if (!$html) {
            $return["error"]["module"] = "Article";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = "cURL is not installed on this server!";
            return $return;        
        }
    }    

    // parse the html into a DOMDocument  

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Grab Product Links  

    $xpath = new DOMXPath($dom);
    $paras = $xpath->query("//h3[@class='entry_title']/a");
    
    $x = 0;
    $end = $numb + $num;
    
        if($paras->length == 0) {
            $posts["error"]["module"] = "Article";
            $posts["error"]["reason"] = "No content";
            $posts["error"]["message"] = "No (more) articles found.";
            return $posts;        
        }    
    
    if($end > $paras->length) { $end = $paras->length;}
    for ($i = $numb;  $i < $end; $i++ ) {
    
        $para = $paras->item($i);
    
        if(empty($para)) {
            $posts["error"]["module"] = "Article";
            $posts["error"]["reason"] = "No content";
            $posts["error"]["message"] = "No (more) articles found.";
            //print_r($posts);
            return $posts;        
        } else {
        
            $target_url = $para->getAttribute('href'); // $target_url = "http://www.articlesbase.com" . $para->getAttribute('href');        
            
            // make the cURL request to $search_url
            if ( function_exists('curl_init') ) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERAGENT, $ua);
                if($proxy != "") {
                    //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
                    curl_setopt($ch, CURLOPT_PROXY, $proxy);
                    if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
                    if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
                }                    
                curl_setopt($ch, CURLOPT_URL,$target_url);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 45);
                $html = curl_exec($ch);
                if (!$html) {
                    $return["error"]["module"] = "Article";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] = "cURL Error Number ";
                    return $return;
                }        
                curl_close($ch);
            } else {                 
                $html = @file_get_contents($target_url);
                if (!$html) {
                    $return["error"]["module"] = "Article";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] = "cURL is not installed on this server!";
                    return $return;        
                }
            }

            // parse the html into a DOMDocument  

            $dom = new DOMDocument();
            @$dom->loadHTML($html);
                
            // Grab Article Title             
            $xpath1 = new DOMXPath($dom);
            $paras1 = $xpath1->query("//div/h1");
            $para1 = $paras1->item(0);
            $title = $para1->textContent;        
            
            // Grab Article    
            $xpath2 = new DOMXPath($dom);
            $paras2 = $xpath2->query("//div[@id='article_section']/div[@class='KonaBody']"); 
            $para2 = $paras2->item(0);        
            $string = $dom->saveXml($para2);
        
            $string = strip_tags($string,'<p><strong><b><a><br>');
            $string = str_replace('<div class="KonaBody">', "", $string);    
            $string = str_replace("</div>", "", $string);
            $string = str_replace("]]>", "", $string);
            $string = str_replace("]]&gt;", "", $string);
            $string = str_replace("$", "$ ", $string); 
            $string = str_replace("&nbsp;", "", $string);    
            $string = preg_replace('#<strong>RELATED CONTENT(.*)#smiU', '', $string);
            if ($options['CMeza_striplinks']=='yes') {$string = CMstrip_selected_tags($string, array('a','iframe','script'));}    
            $articlebody .= $string . ' ';            

            // Grab Ressource Box    

            $xpath3 = new DOMXPath($dom);
            $paras3 = $xpath3->query("//div[@id='bio_section']/p");        //$para = $paras->item(0);        
            
            $ressourcetext = "";
            for ($y = 0;  $y < $paras3->length; $y++ ) {  //$paras->length
                $para3 = $paras3->item($y);
                $ressourcetext .= $dom->saveXml($para3);    
            }    
            
            $title = utf8_decode($title);
            
            // Split into Pages
            if($options['CMeza_split'] == "yes") {
                $articlebody = wordwrap($articlebody, $options['CMeza_splitlength'], "<!--nextpage-->");
            }
            
            $post = $template;
            $post = CMrandom_tags($post);
            $post = str_replace("{article}", $articlebody, $post);            
            $post = str_replace("{authortext}", $ressourcetext, $post);    
            $noqkeyword = str_replace('"', '', $keyword2);
            $post = str_replace("{keyword}", $noqkeyword, $post);
            $post = str_replace("{Keyword}", ucwords($noqkeyword), $post);                
            $post = str_replace("{title}", $title, $post);    
            $post = str_replace("{url}", $target_url, $post);
                    if(function_exists("CMrewrite_partial")) {
                        $post = CMrewrite_partial($post,$options);
                    }            
                    if(function_exists("CMtranslate_partial")) {
                        $post = CMtranslate_partial($post);
                    }    
                    
            $posts[$x]["unique"] = $target_url;
            $posts[$x]["title"] = $title;
            $posts[$x]["content"] = $post;                
            $x++;
        }    
    }    
    return $posts;
}

// =============================== GOARTICLES.com ================================ //

function CMgoarticlepost($keyword,$num,$start,$optional="",$comments="") {
    global $wpdb,$CMtable_templates;

    $search_url = "http://goarticles.com/search/?q=$keyword&start=$start&limit=$num";
    
    $proxy == "";
    if($options["CMtrans_use_proxies"] == "yes") {
        $proxies = str_replace("\r", "", $options["CMtrans_proxies"]);
        $proxies = explode("\n", $proxies);  
        $rand = array_rand($proxies);    
        list($proxy,$proxytype,$proxyuser)=explode("|",$proxies[$rand]);
    }    
    
//echo $search_url. " <br/>";
    // make the cURL request to $search_url
    if ( function_exists('curl_init') ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);        
            if($proxy != "") {
                //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
                if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
                if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
            }            
        curl_setopt($ch, CURLOPT_URL,$search_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        $html = curl_exec($ch);
        if (!$html) {
            //echo "CURL NO 1 <br/>";
            $return["error"]["module"] = "Article";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = "cURL Error Number ";    
            return $return;
        }        
        curl_close($ch);
    } else {                 
        $html = @file_get_contents($search_url);
        if (!$html) {
            $return["error"]["module"] = "Article";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = "cURL is not installed on this server!";
            return $return;        
        }
    }    

    // parse the html into a DOMDocument  

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Grab Product Links  

    $xpath = new DOMXPath($dom);
    $paras = $xpath->query("//div//a[@class='article_title_link']");
    
    $x = 0;
    
        if($paras->length == 0) {
            $posts["error"]["module"] = "Article";
            $posts["error"]["reason"] = "IncNum";
            $posts["error"]["message"] = "No (more) articles found.";
            return $posts;        
        }    
    
    $end = $paras->length;
    for ($i = 0;  $i < $end; $i++ ) {

        $para = $paras->item($i);
    
        if(empty($para)) {
            $posts["error"]["module"] = "Article";
            $posts["error"]["reason"] = "IncNum";
            $posts["error"]["message"] = "No (more) articles found.";
            //print_r($posts);
            return $posts;        
        } else {
        
            $target_url = "http://goarticles.com".$para->getAttribute('href');    

            // make the cURL request to $search_url
            if ( function_exists('curl_init') ) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERAGENT, $ua);
                    if($proxy != "") {
                        //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
                        curl_setopt($ch, CURLOPT_PROXY, $proxy);
                        if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
                        if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
                    }                    
                curl_setopt($ch, CURLOPT_URL,$target_url);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 45);
                $html = curl_exec($ch);
                if (!$html) {
            //echo $html. " <br/>";    
            //echo "CURL NO 2 <br/>";                
                    $return["error"]["module"] = "Article";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] = "cURL Error Number ";
                    return $return;
                }        
                curl_close($ch);
            } else {                 
                $html = @file_get_contents($target_url);                
                if (!$html) {
                    $return["error"]["module"] = "Article";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] = "cURL is not installed on this server!";
                    return $return;        
                }
            }

            $dom = new DOMDocument();
            @$dom->loadHTML($html);
                
            // Grab Article Author             
            $xpath1 = new DOMXPath($dom);
            $paras1 = $xpath1->query("//div/h1[@class='art_head']/em");
            $para1 = $paras1->item(0);
            $author = $para1->textContent;                    
                
            // Grab Article Title             
            $xpath1 = new DOMXPath($dom);
            $paras1 = $xpath1->query("//div/h1[@class='art_head']");
            $para1 = $paras1->item(0);
            //$title = $dom->saveXml($para1);    
            //$title = preg_replace('#<em(.*)</em>#smiU', '', $title);
            //$title = strip_tags($title,'<p><strong><b><a><br>');
            $title = $para1->textContent;
            $title = str_replace($author, "", $title);
            $title = str_replace("by", "", $title);
            $title = trim($title);
            $title = substr($title,0,-4); 

            // Grab Article    
            $xpath2 = new DOMXPath($dom);
            $paras2 = $xpath2->query("//div[@id='main-col']/div[@class='article']/div[@class='KonaBody']"); 
            $para2 = $paras2->item(0);        
            $string = $dom->saveXml($para2);
            $string = str_replace("&#13;", '', $string);
            $string = trim($string);
            $string = preg_replace('#<h1(.*)</h1>#smiU', '', $string);
            $string = preg_replace('#<h2(.*)</h2>#smiU', '', $string);
            $string = strip_tags($string,'<p><strong><b><a><br>');
            $string = str_replace('<div class="KonaBody">', "", $string);    
            $string = str_replace("</div>", "", $string);
            $string = str_replace("&nbsp;", "", $string);    
            $string = str_replace("$", "$ ", $string); 
            $string = str_replace(chr(13), '', $string);
            if ($options['CMeza_striplinks']=='yes') {$string = CMstrip_selected_tags($string, array('a','iframe','script'));}        
            $articlebody = "<p>Article $author</p>".$string . ' ';            

            // Grab Ressource Box    
            
            $xpath2 = new DOMXPath($dom);
            $paras2 = $xpath2->query("//div[@id='main-col']/div[@class='article']"); 
            $para2 = $paras2->item(0);        
            $string = $dom->saveXml($para2);    
            $string = preg_replace('#(.*)</h3>#smiU', '', $string);
            $string = str_replace('<div class="KonaBody">', "", $string);    
            $string = str_replace("</div>", "", $string);    
            $string = str_replace("<div>", "", $string);    
            $string = strip_tags($string,'<p><strong><b><a><br>');            
            $ressourcetext = ''.$string;
            
            // Split into Pages
            if($options['CMeza_split'] == "yes") {
                $articlebody = wordwrap($articlebody, $options['CMeza_splitlength'], "<!--nextpage-->");
            }
            /*
            $post = $template;
            $post = CMrandom_tags($post);
            */
            $post="";
            $post = str_replace("{article}", $articlebody, $post);            
            $post = str_replace("{authortext}", $ressourcetext, $post);    
            $noqkeyword = str_replace('"', '', $keyword2);
            $post = str_replace("{keyword}", $noqkeyword, $post);
            $post = str_replace("{Keyword}", ucwords($noqkeyword), $post);                
            $post = str_replace("{title}", $title, $post);    
            $post = str_replace("{url}", $target_url, $post);
            
                    if(function_exists("CMtranslate_partial")) {
                        $post = CMtranslate_partial($post);
                    }    
                    if(function_exists("CMrewrite_partial")) {
                        $post = CMrewrite_partial($post,$options);
                    }
                    
            $posts[$x]["unique"] = $target_url;
            $posts[$x]["title"] = $title;
            $posts[$x]["content"] = $post;                
            $x++;
        }    
    }    
    return $posts;
}

// =============================== ARTICLESBASE.com ================================ //

function CMarticlepost($keyword,$num,$start,$optional="",$comments="") {
    global $wpdb,$CMtable_templates;

    if($keyword == "") {
        $return["error"]["module"] = "Article";
        $return["error"]["reason"] = "No keyword";
        $return["error"]["message"] = __("No keyword specified.","ctbot");
        return $return;    
    }    
    /*
    $template = $wpdb->get_var("SELECT content FROM " . $CMtable_templates . " WHERE type = 'article'");
    if($template == false || empty($template)) {
        $return["error"]["module"] = "Article";
        $return["error"]["reason"] = "No template";
        $return["error"]["message"] = __("Module Template does not exist or could not be loaded.","ctbot");
        return $return;    
    }        
    */
     $posts = array();
    //$options = unserialize(get_option("CMoptions"));    
    
    $proxy == "";
    if($options["CMtrans_use_proxies"] == "yes") {
        $proxies = str_replace("\r", "", $options["CMtrans_proxies"]);
        $proxies = explode("\n", $proxies);  
        $rand = array_rand($proxies);    
        list($proxy,$proxytype,$proxyuser)=explode("|",$proxies[$rand]);
    }    

    $keyword2 = $keyword;    
    $keyword = str_replace( " ","+",$keyword );    
    $keyword = urlencode($keyword);
    
    $blist[] = "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)";
    $blist[] = "Mozilla/5.0 (compatible; Konqueror/3.92; Microsoft Windows) KHTML/3.92.0 (like Gecko)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; .NET CLR 1.1.4322; Windows-Media-Player/10.00.00.3990; InfoPath.2";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; InfoPath.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; Dealio Deskball 3.0)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; NeosBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
    $ua = $blist[array_rand($blist)];        
    

    if($options["CMeza_source"] == "rand") {
        $rand = rand(0,3);
        if($rand == 0) {$options["CMeza_source"] = "buk";
        } elseif($rand == 1) {$options["CMeza_source"] = "goa";
        } elseif($rand == 2) {$options["CMeza_source"] = "ezm";        
        } else {$options["CMeza_source"] = "ab";}    
    }

    if($options["CMeza_source"] == "buk") {
        return CMbukisapost($keyword,$num,$start,$optional,$comments,$options,$template,$ua,$proxy,$proxytype,$proxyuser);
    } elseif($options["CMeza_source"] == "goa") {
        return CMgoarticlepost($keyword,$num,$start,$optional,$comments,$options,$template,$ua,$proxy,$proxytype,$proxyuser);
    } elseif($options["CMeza_source"] == "ezm") {
        return CMezinemarkpost($keyword,$num,$start,$optional,$comments,$options,$template,$ua,$proxy,$proxytype,$proxyuser);        
    }

    $page = $start / 15;
    $page = (string) $page; 
    $page = explode(".", $page);    
    $page=(int)$page[0];    
    $page++;    

    if($page == 0) {$page = 1;}
    $prep = floor($start / 15);
    $numb = $start - $prep * 15;

    //$lang = $options['CMeza_lang'];
    $lang ="en";
    
    if($lang == "en") {
        $search_url = "http://www.articlesbase.com/find-articles.php?q=$keyword&page=$page";
    } elseif($lang == "fr") {
        $search_url = "http://www.articlonet.fr/find-articles.php?q=$keyword&page=$page";    
    } elseif($lang == "es") {
        $search_url = "http://www.articuloz.com/find-articles.php?q=$keyword&page=$page";
    } elseif($lang == "pg") {
        $search_url = "http://www.artigonal.com/find-articles.php?q=$keyword&page=$page";
    } elseif($lang == "ru") {
        $search_url = "http://www.rusarticles.com/find-articles.php?q=$keyword&page=$page";
    }

    // make the cURL request to $search_url
    if ( function_exists('curl_init') ) {
        $ch = curl_init();    
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
            if($proxy != "") {            
                //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
                if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
                if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
            }            
        curl_setopt($ch, CURLOPT_URL,$search_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        $html = curl_exec($ch);
        if (!$html) {
            $return["error"]["module"] = "Article";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = "cURL Error Number ";    
            return $return;
        }        
        curl_close($ch);
    } else {     
        $html = @file_get_contents($search_url);
        if (!$html) {
            $return["error"]["module"] = "Article";
            $return["error"]["reason"] = "cURL Error";
            $return["error"]["message"] = "cURL is not installed on this server!";    
            return $return;        
        }
    }    
    // parse the html into a DOMDocument  


    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Grab Product Links  

    $xpath = new DOMXPath($dom);
    $paras = $xpath->query("//div//h3/a");
    
    $x = 0;
    $end = $numb + $num;
    
        if($paras->length == 0) {
            $posts["error"]["module"] = "Article";
            $posts["error"]["reason"] = "No content";
            $posts["error"]["message"] = "No (more) articles found.";    
            return $posts;        
        }    
    
    if($end > $paras->length) { $end = $paras->length;}
    for ($i = $numb;  $i < $end; $i++ ) {
    
        $para = $paras->item($i);
    
        if(empty($para)) {
            $posts["error"]["module"] = "Article";
            $posts["error"]["reason"] = "No content";
            $posts["error"]["message"] = "No (more) articles found.";    
            //print_r($posts);
            return $posts;        
        } else {
        
            $target_url = $para->getAttribute('href'); // $target_url = "http://www.articlesbase.com" . $para->getAttribute('href');        
            
            // make the cURL request to $search_url
            if ( function_exists('curl_init') ) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERAGENT, $ua);
                if($proxy != "") {
                    //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
                    curl_setopt($ch, CURLOPT_PROXY, $proxy);
                    if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
                    if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
                }                    
                curl_setopt($ch, CURLOPT_URL,$target_url);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 45);
                $html = curl_exec($ch);
                if (!$html) {
                    $return["error"]["module"] = "Article";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] = "cURL Error Number ";    
                    return $return;
                }        
                curl_close($ch);
            } else {                 
                $html = @file_get_contents($target_url);
                if (!$html) {
                    $return["error"]["module"] = "Article";
                    $return["error"]["reason"] = "cURL Error";
                    $return["error"]["message"] = "cURL is not installed on this server!";    
                    return $return;        
                }
            }

            // parse the html into a DOMDocument  

            $dom = new DOMDocument();
            @$dom->loadHTML($html);
                
            // Grab Article Title             
            $xpath1 = new DOMXPath($dom);
            $paras1 = $xpath1->query("//div/h1");
            $para1 = $paras1->item(0);
            $title = $para1->textContent;        
            
            // Grab Article    
            $xpath2 = new DOMXPath($dom);
            //$paras2 = $xpath2->query("//div[@class='article_cnt KonaBody']"); 

            
            /*if(empty($string)) {}
            $paras2 = $xpath2->query("//div[@id='nw_content']/div[@class='KonaBody']"); 
            $para2 = $paras2->item(0);        
            $string = $dom->saveXml($para2);*/    

            $paras2 = $xpath2->query("//div[@class='article_cnt']/div[@class='KonaBody']"); 
            $para2 = $paras2->item(0);        
            $string = $dom->saveXml($para2);
            
            $string = preg_replace('#<div class="articles">(.*)</div>#smiU', '', $string);        
            $string = preg_replace('#<a title="(.*)" href="/authors/(.*)</a>#smiU', '', $string);
            $string = preg_replace('#<strong>(.*)</strong>#smiU', '', $string);
            
            $string = strip_tags($string,'<p><strong><b><a><br>');
            $string = str_replace('<div class="KonaBody">', "", $string);    
            $string = str_replace("</div>", "", $string);
            $string = str_replace("&nbsp;", "", $string);    
            $string = str_replace("]]>", "", $string);    
            $string = str_replace("$", "$ ", $string); 
            if ($options['CMeza_striplinks']=='yes') {$string = CMstrip_selected_tags($string, array('a','iframe','script'));}    
            //if($lang == "es") {$string = utf8_decode  (  $string  );    }        
            $articlebody .= $string . ' ';            

            // Grab Ressource Box    

            $xpath3 = new DOMXPath($dom);
            $paras3 = $xpath3->query("//div[@class='author_details']/p");        //$para = $paras->item(0);        
            
            $ressourcetext = "";
            for ($y = 0;  $y < $paras3->length; $y++ ) {  //$paras->length
                $para3 = $paras3->item($y);
                $ressourcetext .= $dom->saveXml($para3);    
            }    
            
            $title = utf8_decode($title);
            
            // Split into Pages
            if($options['CMeza_split'] == "yes") {
                $articlebody = wordwrap($articlebody, $options['CMeza_splitlength'], "<!--nextpage-->");
            }
            /*
            $post = $template;
            $post = CMrandom_tags($post);
            */
            $post = str_replace("{article}", $articlebody, $post);            
            $post = str_replace("{authortext}", $ressourcetext, $post);    
            $noqkeyword = str_replace('"', '', $keyword2);
            $post = str_replace("{keyword}", $noqkeyword, $post);
            $post = str_replace("{Keyword}", ucwords($noqkeyword), $post);                
            $post = str_replace("{title}", $title, $post);    
            $post = str_replace("{url}", $target_url, $post);
                    if(function_exists("CMrewrite_partial")) {
                        $post = CMrewrite_partial($post,$options);
                    }            
                    if(function_exists("CMtranslate_partial")) {
                        $post = CMtranslate_partial($post);
                    }    
                    
            $posts[$x]["unique"] = $target_url;
            $posts[$x]["title"] = $title;
            $posts[$x]["content"] = $post;                
            $x++;
        }    
    }    
    return $posts;
}


function CMstrip_selected_tags($text, $tags = array()) {
    $args = func_get_args();
    $text = array_shift($args);
    $tags = func_num_args() > 2 ? array_diff($args,array($text))  : (array)$tags;
    foreach ($tags as $tag){
        while(preg_match('/<'.$tag.'(|\W[^>]*)>(.*)<\/'. $tag .'>/iusU', $text, $found)){
            $text = str_replace($found[0],$found[2],$text);
        }
    }
    return preg_replace('/(<('.join('|',$tags).')(|\W.*)\/>)/iusU', '', $text);
}


function CMrandom_tags($content) {

    preg_match_all('#\[select(.*)\]#smiU', $content, $matches, PREG_SET_ORDER);
    if ($matches) {
        foreach($matches as $match) {
            $match[1] = substr($match[1], 1);
            $paras = explode("|",$match[1]);
            $randp = array_rand($paras);
            
            $content = str_replace($match[0], $paras[$randp], $content);            
        }
    }    
    //preg_match_all('#\[random(.*)](.*)[\/random]\]#smiU', $content, $matches, PREG_SET_ORDER);
    preg_match_all('#\[random(.*)\](.*)\[/random\]#smiU', $content, $matches, PREG_SET_ORDER);
    if ($matches) {
        foreach($matches as $match) {
            $match[1] = substr($match[1], 1);
            if($match[1] >= rand(1,100)) {
                //$match[2] = str_replace("[/rando", "", $match[2]);    
                $content = str_replace($match[0], $match[2], $content);    
            } else {
                $content = str_replace($match[0], "", $content);                
            }
        }
    }
    
    return $content;
}
?>