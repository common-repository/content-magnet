<?php
    
    function prefix_ajax_add_key()
    { 
		$sel_keys =  array();
        $sel_keys = $_REQUEST['chk_keys'];    
        
        global $wpdb;

        foreach ($sel_keys as $key)
        {
            $wpdb->insert($wpdb->prefix."CMkeyword", array('CMkeyword' => $key),array('%s'));
        }

        die ('Insert Success!');    
            
    }
    
    function prefix_ajax_delete_key()
    {
        global $wpdb;
        $del_keys = array();
        $del_keys = $_REQUEST['delete_chk_keys'];
        
        foreach ($del_keys as $key)
        {
            
            $sql = "DELETE FROM ".$wpdb->prefix."CMkeyword WHERE CMkeyword = '".$key."'";
            
            $wpdb->query($sql);
        }
        
       die('Delete Success'); 
    }
?>
