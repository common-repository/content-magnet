<?php
define( 'DEBUG_SPIN', FALSE );
define( 'LOCAL_SPIN', TRUE );
//error_reporting(E_ALL & ~E_NOTICE);
class SpinMe
{
    public $exarr = array(),
           $hideKeys = array();

    public function spinText( $text, $method, $maxsyns = 1, $quality = 1, $excluded = FALSE, $session = FALSE, $showone = TRUE ) 
    {
        if( $excluded ) $text = $this->exText( $text, $excluded );
        
        $data = array( 'session' => true,
                       'format' => 'php',
                       'text' => $this->smartyTextCleaner($this->hideText($this->delTags($this->cleanText($text)))),
                       'action' => $method );

        switch( $method ) {
            case "replaceEveryonesFavorites":
                $data['maxsyns'] = $maxsyns;
                $data['quality'] = $quality;
                break;
            case "identifySynonyms":
                $data['maxsyns'] = $maxsyns;
                break;
            default:
                return '';
        }
        
        // LOCAL SPIN
        $return = $this->localSpinTextFile( $data['text'], $data['maxsyns'] );
		if(!empty($return)){
			$this->_log2( "LocalSpin:\n".$return );
			return $return;
		}else{
			 return '';
		}
		     
    }

    private function exText( $text, $excluded ) 
    {
        $words = explode( "\n", $excluded );
        foreach( $words as $word ) {
            $word = trim( $word );
            $md5 = md5($word);
            $md5_word = $md5[1].$md5[2].$md5[3].$md5[4].$md5[5].$md5[6].$md5[7];
            $this->exarr[$md5_word] = $word;
            $text = str_replace( $word, $md5_word, $text );
        }
        return $text;
    }

    private function unText( $text ) 
    {
        foreach( $this->exarr as $md5=>$word ) $text = str_replace( $md5, $word, $text );
        return $text;
    }
    
    private function hideText( $text ) 
    {
        preg_match_all("|{(.*)}|U",$text,$out);
        if( count($out[1]) ) {
            foreach( $out[1] as $i=>$synonyms ) {
                $this->hideKeys[$i] = $synonyms;
                $text = str_replace( $synonyms, '{%hk'.$i.'%}', $text );
            }
        }
        return $text;
    }

    private function unHideText( $text ) 
    {
        foreach( $this->hideKeys as $i=>$synonyms ) $text = str_replace( '%hk'.+$i.'%', $synonyms, $text );
        //$this->hideKeys = array();
        $text = str_replace( '{{', '{', str_replace( '}}', '}', $text ) );
        return $text;
    }
    
    private function cleanText( $text ) 
    {
        $text = str_replace( '%u2018', '\'', $text );
        $text = str_replace( "%u201D", '"', $text );
        $text = str_replace( "%u201C", '"', $text );
        $text = str_replace( "&nbsp;", '', $text );
        //$text = preg_replace('!\s+!', ' ', $text);
        
        return $text;
    }
    
    private function localSpinTextFile( $textbody, $maxsyns = 1 ) 
    {
        if( DEBUG_SPIN ) $this->_log2( "Local [1] Text:\n$textbody\nMax: $maxsyns" );
        
        $showOne = $maxsyns;
        
        // loading synonyms
        $synonyms = file(dirname(__FILE__).'/synonyms.txt');
        
        //$textbody = str_replace( '<p>', '', $textbody );
        //$textbody = str_replace( '</p>', '', $textbody );
        $textbody = str_replace( '<span class="selected_tinymce_content">', '^@@#@@^', $textbody );        
        $hashArray = array();

        foreach( $synonyms as $group ) {
            $group = trim( $group );
            $synonym = explode( '|', $group );
            $synonym = array_unique( $synonym );
            foreach( $synonym as $word ) {
                $word = trim( $word );
                if( $word ) {
                    //preg_match("/[\s|\W]{0,1}".$word."[\W|_]{0,1}/i", $textbody, $matches, PREG_OFFSET_CAPTURE);
                    //Fixer
                    $word = str_replace('/','\/',$word);
                    preg_match("/[\W|\s]".$word."[\W|\s]/i", $textbody, $matches, PREG_OFFSET_CAPTURE);
                    $match = $matches[0][1];
                    if ( isset($match) ) {
                        if ($match != 0) $match++;
                        $found = trim( substr( $textbody, $match, (strlen($word)) ) );
                        $hash = $this->generateHash();
                        //$group = str_ireplace( $found, "<b>$found</b>", $group );
                    
                        //if( $maxsyns == 1 ) $maxsyns++;
                        
                        if( $maxsyns == 1 ) $group = $synonym[rand(0,count($synonym)-1)];
                        else {
                            $group = '{';
                        
                            if( $maxsyns > count($synonym) ) $max = count($synonym);
                            else $max = $maxsyns;
                        
                            for( $i=0; $i<$max; $i++ ) $group.= $synonym[$i].'|';
                            $group = preg_replace('/\|$/', '}', $group);
                        }
                    
                        $hashArray[$hash] = $group;
                        $textbody = str_replace( $found, " $hash ", $textbody ); 
                    }
                }
            }
        }
    
        foreach( $hashArray as $hash=>$var ) $textbody = str_replace( " $hash ", $var, $textbody );
        $textbody = str_replace( '^@@#@@^', '<span class="selected_tinymce_content">', $textbody );
        $textbody = str_replace( '}/span>', '}</span> ', $textbody );
        
        if( DEBUG_SPIN ) $this->_log2( "Local [2] Text:\n$textbody\n" );
        
        return $textbody;
    }

    private function generateHash() 
    {
        $str = '!@#()^&()&@(#&';
        return '#'.$str[rand(1,15)].$str[rand(1,15)].$str[rand(1,15)].$str[rand(1,15)].$str[rand(1,15)].$str[rand(1,15)].'^';
    }
        
    private function delTags( $text ) 
    {
        $text = preg_replace( "/(<p>|<br>)Tags:(<\/p>|)(\s+)<div style='clear:both'><\/div>/", '', $text );
        $text = preg_replace( "/<p>(.*)(<|&lt;)A hre<\/a>(<\/p>|)/", '', $text );
        if( !preg_match_all( '|<[a\|A].* href=(.*)rel=[\'\|\"]tag[\'\|\"]>(.*)</[a\|A]>|U', $text, $out, PREG_OFFSET_CAPTURE ) ) {
            if( !preg_match_all( '|<[a\|A] rel=[\'\|\"]tag[\'\|\"].* href=(.*)>(.*)</[a\|A]>|U', $text, $out, PREG_OFFSET_CAPTURE ) ) {
                if( DEBUG_SPIN ) $this->_log2( "Del tags: [0]\n$text" );
                    return $text;
            }
        }
                
        $text = preg_replace( "/<p>Tags:(<\/p>|)(\s+)<div style='clear:both'><\/div>/", '', $text );
        
        $countTags = count( $out[0] );
        $randCount = rand( 1,$countTags );
        if( $countTags > 6 AND $randCount > 6 ) $randCount = 6;
        
        $myTags = '';
        for( $i = 0; $i < $randCount; $i++ ) {
            $randTag = rand(0,($countTags-1));
            $myTags.= $out[0][$randTag][0].", ";
        }
        $myTags = preg_replace('/, $/', '', $myTags);
        $text = str_replace( "Tags: ", '', $text );
        
        $text = substr_replace( $text, "</a><div style='clear:both'></div><br>Tags: $myTags", $out[0][0][1], (strlen( $text )-1));
        $text = str_replace( "<div style='clear:both'></div>", '', $text );
        
        if( DEBUG_SPIN ) $this->_log2( "Del tags: [1]\n$text" );

        return $text;
    }
    
    private function smartyTextCleaner( $text ) 
    {
        preg_match_all('/([a-zA-Zа-яА-Я]+)/', htmlspecialchars( $text ), $out, PREG_OFFSET_CAPTURE);

        foreach( $out[0] as $words ) {
            $word = trim( $words[0] );
            if( empty( $word ) ) continue;
    
            $position = $words[1];
    
            if( $lastOne == $word ) {
                $text = substr_replace( $text, $this->genTrash(strlen($word)), $position, strlen($word) );
            }
            if( "$lastThree $lastTwo" == "$lastOne $word" ) {
                $text = substr_replace( $text, $this->genTrash(strlen($lastOne)+strlen($word)+2), ($position-strlen($lastOne)-1), (strlen($lastOne)+strlen($word)+2) );
            }
    
            if( !empty( $lastOne ) ) {
                if( !empty( $lastTwo ) ) {
                    $lastThree = $lastTwo;
                    $lastTwo = $lastOne;
                } else {
                    $lastThree = '';
                    $lastTwo = $lastOne;
                }
            }
            $lastOne = $word;
        }
        $text = str_replace( '`', '', htmlspecialchars_decode( $text ) );
        if( DEBUG_SPIN ) $this->_log2( "Smarty: [1]\n$text" );
        
        return $text;
    }

    private function genTrash( $count ) 
    {
        $trash = '';
        for( $i=0; $i<$count; $i++ ) $trash.= '`';
        return $trash;
    }
	
	public function _log2( $message ) {
		if(DEBUG_SPIN == 1){
		$fp=fopen(CMPATH.'/CMerror_log.txt','a');
		  if( is_array( $message ) || is_object( $message ) ){
		   $m=print_r( $message, true );
		   fwrite($fp,$m);
		  } else {
			$m=print_r( $message, true );
			fwrite( $m);
		  }
	    fclose($fp);
		} else { return false; }
	  }

	
}
?>