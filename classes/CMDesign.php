<?php

/*
Design templates for the plugin
*/

class CMDesign
{
	public static function admin_head()
	{
		// js
		wp_enqueue_script('admin-forms');
        
		// css
		echo <<< EOT
<style type='text/css'>
/* <![CDATA[ */

.stat_row {
	background: #D1D1D1 none repeat scroll 0%;
}

.stat_row_table {
	width: 100%;
}

.stat_row_table th, .stat_row_table td {
	margin: 0;
	padding: 0;
	border: 0;
}
.sr_last_post {
	width: 70px;
}

.sr_run_now, .st_state {
	width: 100px;
}

div.CMsmall_heading {
	font-weight: bold;
	font-size: 1.1em;
	margin-bottom: 9px;
}

div.CMsmall_heading2 {
	font-weight: bold;
	font-size: 1.1em;
	color: d54e21;
	margin-bottom: 9px;
}

.CMblog_posts_t {
	width: 100%;
}

.CMblog_posts_t td {
	vertical-align: top;
	width: 33%;
	padding: 3px;
}

.CMblog_posts_t .bp_title {
	font-size: 1.1em;
	margin-bottom: 3px;
}

/* ]]> */
</style>

EOT;
	}
	
	public static function management_page($arr)
	{
		// this is, if we want to see or null up wp cron, schedules
		//update_option('cron', array()); delete_option('CMschedules');
		//print_r(get_option('cron')); echo "<br>"; print_r(wp_get_schedules());
		?>

		<div class="wrap">
			<p style="vertical-align:text-top;"><img src="<?=plugins_url().'/contentmagnet/images/op_header.jpg';?>" /></p>
            <h2><?php printf(__('Content Magnet (<a href="%s">add new</a>)', 'cm-plugin'), '#addsearchterm') ?></h2>
            <form id="posts-filter" action="?page=<?php echo $arr['plugin_page'] ?>" method="POST">
			<?php
            if (isset($arr['message']))
			{
				echo "<div id=\"message\" class=\"updated fade\"><p>" . __($arr['message'], 'cm-plugin') . "</p></div>";
			}
			?>
            <?php
			if (0 < CMLV_LIMIT)
			{
				
			}
            ?>
			<br class="clear" />
			
			<div class="tablenav">

			<div class="alignleft">
			<input type="submit" value="<?php _e('Delete', 'cm-plugin'); ?>" name="deleteit" class="button-secondary delete" />
			
			<?php wp_nonce_field('bulk-searchterms'); ?>
			</div>
			
			<br class="clear" />
			</div>

			<br class="clear" />
			
			<?php
			// amount of search terms
			$search_terms_amount = 0;
			
			// if there are no search terms yet
			if (0 == count($arr['sql_search_terms']))
			{
				// display a message
				printf("<div style='text-align: center'>" . __(CMMessages::no_search_terms, 'cm-plugin') . "</div>",
				"#addsearchterm");
			}
			// otherwise show search terms
			else {
				?>
				<table class="widefat">
				<thead>
				<tr>
					<th scope="col" class="check-column"><input type="checkbox" /></th>
					<th scope="col"><?php _e('Search', 'cm-plugin') ?></th>
                    <th scope="col"><?php _e('Network', 'cm-plugin') ?></th>
					<th scope="col"><?php _e('Run every', 'cm-plugin') ?></th>
					<th scope="col"><?php _e('Category', 'cm-plugin') ?></th>
					<th scope="col"><?php _e('Comments', 'cm-plugin') ?></th>
					<th scope="col"><?php _e('State', 'cm-plugin') ?></th>
				</tr>
				</thead>
				<tbody id='the-list' class="list:cat">
				<?php
				// display every search term
				foreach($arr['sql_search_terms'] as $searchterm)
				{
					++$search_terms_amount;
					
					/* if this is light version and there are more search terms than allowed
					if(CMLV_LIMIT>0){
						if (5 == CMLV_LIMIT && $search_terms_amount > CMLV_LIMIT)
						{
							// break out of the loop
							break;
						}
					}
					*/
					// set humanized text
					$humanized = (!empty($searchterm->humanized)) ? " humanized" : null;
					
					// create $run_every
					if ('day' == $searchterm->run_period)
					{
						$run_every = (1 == $searchterm->run_num) ? 
						"1 day" . $humanized : "$searchterm->run_num days" . $humanized;
					}
					else
					{
						$run_every = (1 == $searchterm->run_num) ? 
						"1 hour" . $humanized : "$searchterm->run_num hours" . $humanized;
					}
					?>
					<tr id='cm-searchterm-<?php echo $searchterm->id ?>' class='alternate'>
						<th scope='row' class='check-column'><input type='checkbox' name='delete[]' value='<?php echo $searchterm->id ?>' /></th>
						<td><span class='row-title'><?php echo $arr['search_type_arr'][$searchterm->search_type] ?></span> &nbsp;<a href='<?php echo "?page={$arr['plugin_page']}&amp;action=edit&amp;id=$searchterm->id" ?>' 
						class='row-title'><span style="font-style: italic"><?php echo $searchterm->search_query ?></span></a></td>
						<td><?php echo ucwords($searchterm->network) ?></td>
						<td><?php echo $run_every ?></td>
						<td><?php echo get_cat_name($searchterm->category_id) ?></td>
						<td><?php echo $searchterm->post_comments ?></td>
						<td class="st_state">
							<?php
							// if it has 'active' state
							if ("active" == $searchterm->state):?>
							<a href='<?php echo wp_nonce_url("?page={$arr['plugin_page']}&amp;action=pause&amp;id=$searchterm->id", 'pause-searchterm-' .  $searchterm->id) ?>' 
							class='edit'><?php _e('Pause', 'cm-plugin') ?></a>
							<?php 
							// if it has a 'paused' state
							else: ?>
							<a href='<?php echo wp_nonce_url("?page={$arr['plugin_page']}&amp;action=activate&amp;id=$searchterm->id", 'activate-searchterm-' .  $searchterm->id) ?>' 
							class='edit'><?php _e('Activate', 'cm-plugin') ?></a>
							<?php endif; ?>
						</td>
					</tr>
					<tr id='cm-searchterm-<?php echo $searchterm->id ?>-sr' class="stat_row">
						<td colspan="7">
							<?php CMDesign::stat_row($arr, $searchterm->id) ?>
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
				</table>
		<?php } ?>
		</form>
		
		<div class="tablenav">
		<br class="clear" />
		</div>
		<br class="clear" />
		<?php
		// latest wordpress lab posts
		//printf('<div class="wrap"><p><div class="CMsmall_heading">' . __('Latest blog posts from <a href="%s">Ricky</a>', 'cm-plugin') . '</div>', "http://www.neil-harvey.com/");
		//CMDesign::blog_posts($arr);
		?>
		</p>
		
		<?php
      
		// add search term
		CMDesign::edit_search_term_page($arr, null, $search_terms_amount);
	}
    
    
	//Ricky +
    public static function setupOption_page($arr)
    {
        ?>
		<?php
        $amazon_secret= get_option('CMamazon_api_secret');
        $amazon_public= get_option('CMamazon_api_public');
        $amazon_id= get_option('CMamazon_associate');
        $amazon_country= get_option('CMamazon_country');
        $amazon_price= get_option('CMamazon_price');
        
		$cj_id= get_option('CMcj_site');
        $cj_api= get_option('CMcj_api');
        $linkshare_id = get_option('CMlinkshare_api');
        $shareasale_id= get_option('CMshareasale_id');
        //$shareasale_ftp_id = get_option('CMshareasale_ftp_id');
        //$shareasale_ftp_pass = get_option('CMshareasale_ftp_pass');
        $clickbank_id= get_option('CMclickbank_id');
        //$overstock_id= get_option('CMoverstock_id');
        $newlink= get_option('CMnewlink');
        ?>    

        <div class="wrap">        
            <p style="vertical-align:text-top;"><img src="<?=plugins_url().'/contentmagnet/images/op_header.jpg';?>" /></p>
            <h2><?php printf(__('Setup Options', 'cm-plugin'), '#addsearchterm') ?></h2>
			<?php
            if (isset($arr['message']))
			{
				echo "<div id=\"message\" class=\"updated fade\"><p>" . __($arr['message'], 'cm-plugin') . "</p></div>";
			}
			?>
         <?php
          // setup form 
            $heading = __("Setup Option1", 'cm-plugin');
            $submit_text = __('Save Affiliate Options', 'cm-plugin');
            $form = "<form name=\"setupOption\" id=\"setupOption\" method=\"post\" action=\"?page={$arr['plugin_page']}\" class=\"add:the-list:validate\">\n";
            $action = 'saveOptions';
            $nonce_action = 'add-saveOptions';
        ?>
        <!-- <h2><?php echo $heading ?></h2> -->
        <div id="ajax-response"></div>
        <?php echo $form ?>
        <input type="hidden" name="action" value="<?php echo $action ?>" />
        <?php wp_nonce_field($nonce_action); ?>

        <table border="0" class="form-table">
          <tr>
            <td colspan="2"><h2>Amazon Details</h2></td>
          </tr>
          <tr>
            <td>Amazon Associate ID</td>
            <td><input type="text" name="amazon_id" class="form_install" value="<?php echo $amazon_id;?>" size="45" tabindex="20" />
              <br>
              Enter either your associate id or Tracking id</td>
          </tr>
          <tr>
            <td width="200px">Amazon Public API Key</td>
            <td><input type="text" name="amazon_public" class="form_install" value="<?php echo $amazon_public;?>" size="45" tabindex="10" />
              <br>
              Enter the Access Key ID</td>
          </tr>
          <tr>
            <td>Amazon Secret API</td>
            <td><input type="text" name="amazon_secret" class="form_install" value="<?php echo $amazon_secret;?>" size="45" tabindex="20" />
              <br>
              Enter the Secret Access API Key</td>
          </tr>
          <tr>
            <td>Amazon Country</td>
            <td><select class="form_install" name="amazon_country">
                <option <?php if($amazon_country =="USA"){echo "SELECTED";}?>>USA</option>
                <option <?php if($amazon_country =="UK"){echo "SELECTED";}?>>UK</option>
                <option <?php if($amazon_country =="Canada"){echo "SELECTED";}?>>Canada</option>
                <option <?php if($amazon_country =="Germany"){echo "SELECTED";}?>>Germany</option>
                <option <?php if($amazon_country =="France"){echo "SELECTED";}?>>France</option>
                <option <?php if($amazon_country =="Italy"){echo "SELECTED";}?>>Italy</option>
                <option <?php if($amazon_country =="Spain"){echo "SELECTED";}?>>Spain</option>
                <option <?php if($amazon_country =="Japan"){echo "SELECTED";}?>>Japan</option>
              </select>
              <br>
              Select the country </td>
          </tr>
          <?php if(CMLV_LIMIT==0){?>
          <tr>
            <td colspan="2"><h2>CJ.com Details</h2></td>
          </tr>
          <tr>
            <td>CJ.com API Key</td>
            <td><input type="text" name="cj_api" class="form_install" value="<?php echo $cj_api;?>"  size="40" tabindex="20" /></td>
          </tr>
          <tr>
            <td>CJ.com Site ID</td>
            <td><input type="text" name="cj_id" class="form_install" value="<?php echo $cj_id;?>"  size="40" tabindex="20" /></td>
          </tr>
          <tr>
            <td colspan="2"><h2>LinkShare Details</h2></td>
          </tr>
          <tr>
            <td>Linkshare API Token</td>
            <td><input type="text" name="linkshare_id" class="form_install" value="<?php echo $linkshare_id;?>"  size="40" tabindex="20" /></td>
          </tr>
          <tr>
            <td colspan="2"><h2>eBay Options</h2></td>
          </tr>
          <tr>
            <th width="45%" valign="top" align="right" scope="row">Campaign ID</th>
            <td valign="top"><input name="CMebaylist_campaignid" type="text" size="25" value="<?php echo get_option('CMebaylist_campaignid') ?>"/>
              <br />
              <?php echo "fill in your eBay Campaign id."; ?></td>
          </tr>
          <tr>
            <th width="45%" valign="top" align="right" scope="row">eBay Country</th>
            <td valign="top"><?php $countryselected = get_option('CMebaylist_country');  ?>
              <select name="CMebaylist_country">
                <option value="EBAY-US" <?php if($countryselected =="EBAY-US"){echo "selected";}?>>United States - USD</option>
                <option value="EBAY-AU" <?php if($countryselected =="EBAY-AU"){echo "selected";}?>>Australia - AUD</option>
                <option value="EBAY-ENCA" <?php if($countryselected =="EBAY-ENCA"){echo "selected";}?>>Canada - CAD</option>
                <option value="EBAY-DE" <?php if($countryselected =="EBAY-DE"){echo "selected";}?>>Germany - EUR</option>
                <option value="EBAY-GB" <?php if($countryselected =="EBAY-GB"){echo "selected";}?>>United Kingdom - GBP</option>
                <option value="">-------------------------</option>
                <option value="EBAY-AU" <?php if($countryselected =="EBAY-AU"){echo "selected";}?>>Australia - AUD</option>
                <option value="EBAY-AT" <?php if($countryselected =="EBAY-AT"){echo "selected";}?>>Austria - EUR</option>
                <option value="EBAY-NLBE" <?php if($countryselected =="EBAY-NLBE"){echo "selected";}?>>Belgium (Dutch) - EUR</option>
                <option value="EBAY-FRBE" <?php if($countryselected =="EBAY-FRBE"){echo "selected";}?>>Belgium (French) - EUR</option>
                <option value="EBAY-ENCA" <?php if($countryselected =="EBAY-ENCA"){echo "selected";}?>>Canada - CAD& USD</option>
                <option value="EBAY-FRCA" <?php if($countryselected =="EBAY-FRCA"){echo "selected";}?>>CanadaFrench - CAD& USD</option>
                <option value="EBAY-MOTOR" <?php if($countryselected =="EBAY-MOTOR"){echo "selected";}?>>eBay Motors - USD</option>
                <option value="EBAY-FR" <?php if($countryselected =="EBAY-FR"){echo "selected";}?>>France - EUR</option>
                <option value="EBAY-DE" <?php if($countryselected =="EBAY-DE"){echo "selected";}?>>Germany - EUR</option>
                <option value="EBAY-HK" <?php if($countryselected =="EBAY-HK"){echo "selected";}?>>Hong Kong - HKD</option>
                <option value="EBAY-IN" <?php if($countryselected =="EBAY-IN"){echo "selected";}?>>India - INR</option>
                <option value="EBAY-IE" <?php if($countryselected =="EBAY-IE"){echo "selected";}?>>Ireland - EUR</option>
                <option value="EBAY-IT" <?php if($countryselected =="EBAY-IT"){echo "selected";}?>>Italy - EUR</option>
                <option value="EBAY-MY" <?php if($countryselected =="EBAY-MY"){echo "selected";}?>>Malaysia - MYR</option>
                <option value="EBAY-NL" <?php if($countryselected =="EBAY-NL"){echo "selected";}?>>Netherlands - EUR</option>
                <option value="EBAY-PH" <?php if($countryselected =="EBAY-PH"){echo "selected";}?>>Philippines - PHP</option>
                <option value="EBAY-PL" <?php if($countryselected =="EBAY-PL"){echo "selected";}?>>Poland - PLN</option>
                <option value="EBAY-SG" <?php if($countryselected =="EBAY-SG"){echo "selected";}?>>Singapore - SGD</option>
                <option value="EBAY-ES" <?php if($countryselected =="EBAY-ES"){echo "selected";}?>>Spain - EUR</option>
                <option value="EBAY-SE" <?php if($countryselected =="EBAY-SE"){echo "selected";}?>>Sweden - SEK</option>
                <option value="EBAY-CH" <?php if($countryselected =="EBAY-CH"){echo "selected";}?>>Switzerland - CHF</option>
                <option value="EBAY-GB" <?php if($countryselected =="EBAY-GB"){echo "selected";}?>>United Kingdom - GBP</option>
                <option value="EBAY-US" <?php if($countryselected =="EBAY-US"){echo "selected";}?>>USA - USD</option>
              </select></td>
          </tr>
          <tr>
            <th width="45%" valign="top" align="right" scope="row">Minimum Price</th>
            <td valign="top"><input name="CMebaylist_minprice" type="text" size="25" value="<?php echo get_option('CMebaylist_minprice') ?>"/>
              <br /></td>
          </tr>
          <tr>
            <th width="45%" valign="top" align="right" scope="row">Maximum Price</th>
            <td valign="top"><input name="CMebaylist_maxprice" type="text" size="25" value="<?php echo get_option('CMebaylist_maxprice') ?>"/>
              <br /></td>
          </tr>
          <tr>
            <th width="45%" valign="top" align="right" scope="row">Sort Results By</th>
            <td valign="top"><?php $sort_selected = get_option('CMebaylist_itemsort');  ?>
              <select name="CMebaylist_itemsort">
                <option value="BestMatch" <?php if($sortselected =="BestMatch"){echo "selected";}?>>Best Match</option>
                <option value="BestMatchCategoryGroup" <?php if($sort_selected =="BestMatchCategoryGroup"){echo "selected";}?>>BestMatchCategoryGroup</option>
                <option value="BestMatchPlusEndTime" <?php if($sort_selected =="BestMatchPlusEndTime"){echo "selected";}?>>BestMatchPlusEndTime</option>
                <option value="BestMatchPlusPrice" <?php if($sort_selected =="BestMatchPlusPrice"){echo "selected";}?>>BestMatchPlusPrice</option>
                <option value="BidCount" <?php if($sort_selected =="BidCount"){echo "selected";}?>>BidCount</option>
                <option value="CurrentBid" <?php if($sortselected =="CurrentBid"){echo "selected";}?>>CurrentBid</option>
                <option value="EndTime" <?php if($sort_selected =="EndTime"){echo "selected";}?>>EndTime</option>
                <option value="PricePlusShipping" <?php if($sort_selected =="PricePlusShipping"){echo "selected";}?>>PricePlusShipping</option>
                <option value="StartDate" <?php if($sort_selected =="StartDate"){echo "selected";}?>>StartDate</option>
                <option value="Distance" <?php if($sort_selected =="Distance"){echo "selected";}?>>Distance</option>
              </select></td>
          </tr>
          <tr>
            <th width="45%" valign="top" align="right" scope="row">eBay Sort Order</th>
            <td valign="top"><?php $sortorder_selected = get_option('CMebaylist_itemsortorder');  ?>
              <select name="CMebaylist_itemsortorder">
                <option value="Ascending" <?php if($sortorder_selected =="Ascending"){echo "selected";}?>>Ascending</option>
                <option value="Descending" <?php if($sortorder_selected =="Descending"){echo "selected";}?>>Descending</option>
              </select></td>
          </tr>
          <tr>
            <td colspan="2"><h2>ClickBank ID</h2></td>
          </tr>
          <tr>
            <td>ClickBank ID</td>
            <td><input type="text" name="clickbank_id" class="form_install" value="<?php echo $clickbank_id;?>"  size="40" tabindex="20" /></td>
          </tr>
          <!--
            <tr><td colspan="2"><h2>ShareaSale Details</h2></td></tr>
            <tr><td>ShareaSale ID</td><td>
            <input type="text" name="shareasale_id" class="form_install" value="<?php echo $shareasale_id;?>"  size="40" tabindex="20" /></td>
            </tr>
            <tr><td>ShareaSale FTP User</td><td>
            <input type="text" name="shareasale_ftp_id" class="form_install" value="<?php echo $shareasale_ftp_id;?>"  size="40" tabindex="20" /></td>
            </tr>
            <tr><td>ShareaSale FTP Pass</td><td>
            <input type="text" name="shareasale_ftp_pass" class="form_install" value="<?php echo $shareasale_ftp_pass;?>"  size="40" tabindex="20" /></td>
            </tr>
            -->
          <?php } ?>
        </table>  
        <p class="submit"><input type="submit" class="button-primary" name="Submit" <?php echo $submit_disabled ?> value="<?php echo $submit_text ?>" /></p>
        </form>
        </div>
        <?php 
    }
	
	
	//Ricky
	//This is a test ALL networks Page Once We have the Options Stored
	function networks_page($arr)
	{
		$affiliate_ids="no";
		$amazon_id= get_option('CMamazon_associate'); 
		$cj_id= get_option('CMcj_site'); 
		$linkshare_id = get_option('CMlinkshare_api');
		$clickbank_id= get_option('CMclickbank_id');
		$ebay_id=get_option('CMebaylist_campaignid');
		if($amazon_id !=""){$amz="yes";}
		if($cj_id !=""){$cj="yes";}  
		if($linkshare_id !=""){$ls="yes";} 
		if($clickbank_id !=""){$cb="yes";} 
		if($ebay_id !=""){$ebay="yes";} 
		
		if(!empty($arr['spinContent']['org']))
		{
		?>
	     <div class="wrap">
		  <h2>Original</h2>
          <textarea style="width:400px; height:400px;" wrap="hard" readonly="readonly"><?=$arr['spinContent']['org'];?></textarea>
		  <hr />
		  <h2>Spun</h2>
          <textarea style="width:400px; height:400px;" wrap="hard" readonly="readonly"><?=$arr['spinContent']['spin'];?></textarea>
		  </div>
		<?php				
		}
		elseif(empty($arr['return'])){		
        // setup form 
            $heading = __("test all networks", 'cm-plugin');
            $submit_text = __('Test All Networks', 'cm-plugin');
            $form = "<form name=\"setupOption\" id=\"setupOption\" method=\"post\" action=\"?page={$arr['plugin_page']}\" class=\"add:the-list:validate\">\n";
            $form2 = "<form name=\"runnow\" id=\"runnow\" method=\"post\" action=\"?page={$arr['plugin_page']}\" class=\"add:the-list:validate\">\n";
			$form3 = "<form name=\"runnow\" id=\"runnow\" method=\"post\" action=\"?page={$arr['plugin_page']}\" class=\"add:the-list:validate\">\n";
			
			$action = 'testProducts';
            $action2= 'runnow';
			$action3= 'testSpiner';
			$nonce_action = 'add-networks_page';
        ?>
        <!-- Ricky TEST networks -->
        <div id="ajax-response"></div>
        <?php echo $form ?>
        <input type="hidden" name="action" value="<?php echo $action ?>" />
        <?php wp_nonce_field($nonce_action); ?><br /><br />
	    Test Keyword: <input type="text" name="keyword" class="form_install" value=""  size="40" tabindex="20" /><br />
        <p class="submit"><input type="submit" class="button-primary" name="Submit" <?php echo $submit_disabled ?> value="<?php echo $submit_text ?>" /></p>
        </form>
        <hr />
        <!-- Ricky TEST Poster -->
        <?php echo $form2 ?>
        <?php
		if(isset($arr['poster']))
		{
			$file_exists=false;
			do{
				if(file_exists(CMPATH.'/CMerror_log.txt')){
					$file_exists=true;
					echo '<a href="'.plugins_url().'/contentmagnet/CMerror_log.txt" target="_blank">'.CMPATH.'/CMerror_log.txt'.'</a>';
				}else{
					echo 'Waiting For File Generation<br>';
					sleep(15);	
				}
			}
			while($file_exists==false);
		}
		?>
        <input type="hidden" name="action" value="<?=$action2;?>" />
        <?php //echo CMDesign::network_dropdown($arr, (!empty($id)) ? attribute_escape($searchterm->network) : null) ?>
		<?php wp_nonce_field( 'run-now-0'); ?>
        <br /><p class="submit"><input type="submit" class="button-primary" name="Submit" <?php echo $submit_disabled ?> value="RUN POSTER NOW" /></p>
        </form>
        <hr />
        <!-- Ricky TEST Spinner -->
        <?php echo $form3 ?>
        <input type="hidden" name="action" value="<?=$action3;?>" />
        Test Keyword: <input type="text" name="keyword" class="form_install" value=""  size="40" tabindex="20" /><br />
        <br /><p class="submit"><input type="submit" class="button-primary" name="Submit" <?php echo $submit_disabled ?> value="RUN SPINNER NOW" /></p>
        </form>        
        </div>
        <?php 
		}else{
		?>
        <div class="wrap">
			<h2>Testing: <?=$arr['keyword'];?></h2>	
            <hr />
            <?php
			if($amz=='yes'){
			if($arr['return']['amazon']['success']==true){
			?>
				<h3>Amazon Retuned Products!!</h3>
                <textarea style="width:400px; height:400px;" wrap="hard" readonly="readonly"><?=print_r($arr['return']['amazon'],true);?></textarea>
			<?php
            }else{
			?>	
				<h3>Amazon Returned Nothing :(</h3>
            <?php	
			}}
			?>
			<hr />
            <?php
			if($ls=='yes'){
			if($arr['return']['linkshare']['success']==true){
			?>
				<h3>Linkshare Retuned Products!!</h3>
                <textarea style="width:400px; height:400px;" wrap="hard" readonly="readonly"><?=print_r($arr['return']['linkshare'],true);?></textarea>
			
			<?php
            }else{
			?>	
				<h3>Linkshare Returned Nothing :(</h3>
            <?php	
			}}
			?>
            <hr />
            <?php
			if($cj=='yes'){
			if($arr['return']['cj']['success']==true){
			?>
				<h3>CJ Retuned Products!!</h3>
                <textarea style="width:400px; height:400px;" wrap="hard" readonly="readonly"><?=print_r($arr['return']['cj'],true);?></textarea>
							
			<?php
            }else{
			?>	
				<h3>CJ Returned Nothing :(</h3>
            <?php	
			}}
			?>
			<hr />
            <?php
			if($ebay=='yes'){
			if($arr['return']['ebay']['success']==true){
			?>
				<h3>Ebay Retuned Products!!</h3>
                <textarea style="width:400px; height:400px;" wrap="hard" readonly="readonly"><?=print_r($arr['return']['ebay'],true);?></textarea>
							
			<?php
            }else{
			?>	
				<h3>EBAY Returned Nothing :(</h3>
            <?php	
			}}
			?>
            <hr />
            <?php
			if($cb=='yes'){
			if($arr['return']['clickbank']['success']==true){
			?>
				<h3>Clickbank Retuned Products!!</h3>
                <textarea style="width:400px; height:400px;" wrap="hard" readonly="readonly"><?=print_r($arr['return']['clickbank'],true);?></textarea>
							
			<?php
            }else{
			?>	
				<h3>ClickBank Returned Nothing :(</h3>
            <?php	
			}}
			?>                                                            
		</div>
		<?php
        }
    }
	
	
    
    //Antero +
    public static function gatherTrend_page($arr)
    {
        // this is, if we want to see or null up wp cron, schedules
        //update_option('cron', array()); delete_option('CMschedules');
        //print_r(get_option('cron')); echo "<br>"; print_r(wp_get_schedules());
        ?>
        <div class="wrap">
        <p style="vertical-align:text-top;"><img src="<?=plugins_url().'/contentmagnet/images/op_header.jpg';?>" /></p>
        <h2><?php printf(__('Gather Trends', 'cm-plugin'), '#addsearchterm') ?></h2>
		<?php
        if (isset($arr['message']))
        {
            echo "<div id=\"message\" class=\"updated fade\"><p>" . __($arr['message'], 'cm-plugin') . "</p></div>";
        }
        ?>    
        <br class="clear" />        
        <?php 
        // add search term
        $heading = __("Gather Trends", 'cm-plugin');
        $submit_text = __('Add Search Term', 'cm-plugin');
        $form = "<form name=\"customsetupOption\" id=\"setupOption\" class=\"add:the-list:validate\">\n";
        ?>
        <div id="ajax-response"></div>
        
        <!-- form start -->                        
        <?php echo $form ?> 
        
            <input type="hidden" name="action" value="<?php echo $action ?>" />
            <div class="tablenav">

                <div class="alignleft">
                    <input type="button" value="<?php _e('Get Keywords', 'cm-plugin'); ?>" name="getKeywords" class="button-secondary delete" onclick = "get_keywords()" />
                </div>
                <div id = "loading"> </div>
                <br class="clear" />
            </div>

            <br class="clear" />
            <!-- <div id="save_result" ></div> -->
            <table width="100%">
            <tr>
            	<td align="left">
                    <div id="keywords" style="height:auto; width:60%;" ></div>
                    <div class="alignleft" id="savekeys" style="display:none;">                
                    <p class="submit">
                    <input type="button" class="button-primary" <?php // echo $submit_disabled ?> value="Save Keywords" onclick="add_key()"/> 
                    </p> 
                    </div>
           	 	</td>
            </tr>
            <tr>
            	<td align="left">
                <h2>Your Saved Keywords</h2>
                <hr />
                 <!-- Antero+ -->
                 <div id="div_saved_keywords" name = "div_saved_keywords" style="overflow:auto; height:550px; width:25%;" >
                     <table align="left" style=" height:auto; width:auto; border:1;" >
                        <tr>
                            <td valign="top" class="saved-td">
                     <?php            
                         $saved_keys = $arr['model']->seleCMkey();
                         foreach($saved_keys as $key) 
                         {
                     ?>   
                            <ul><li><input type="checkbox" class="saved-keys" name="saved_keys[]" value = "<?php echo $key; ?>"> <?php echo $key; ?></input> </li></ul>
                     <?php
                         }
                     ?>
                            </td>
                        </tr>
                        <tr>
                        	<td>
                            <div class="alignleft">                
                            <p class="submit">
                            <input type="button" class="button-primary" style = "display:blocked; float: right;" <?php // echo $submit_disabled ?> value="Delete Keywords" onclick="delete_key()"/>     
                            </p> 
                            </div>                            
                            </td>
                        </tr>        
                     </table>
                 </div>             
                </td>
            </tr>
            </table>            
            <br class="clear" />      
        </form>
        <!-- form end -->
        
        </div>
        <script language="javascript">
        
            function add_key()
            {
                var setOptionForm = document.forms['setupOption'];
                var key_count = document.getElementsByName('keys[]').length;
                var key_list = setOptionForm.elements['keys[]'];
                
                var chk_list = new Array();
                
                for (var i = 0; i < key_count; i++) {
                    if (key_list[i].checked) {
                        chk_list.push(key_list[i].value);
                     }
                }
                
                jQuery.post(
                    ajaxurl,
                    {
                        'action':'add_key',
                        'chk_keys':chk_list,
                    },
                    function(response)
                    {
                        alert(response);
                        for (var i = 0; i < key_count; i++) 
                        {
                                    if (key_list[i].checked) 
                                    {
                                        chk_list.push(key_list[i].value);
                                        jQuery('.saved-td').append('<ul><li><input type="checkbox" class="saved-keys" name="saved_keys[]" value = "' + key_list[i].value + '">' + key_list[i].value + '</input> </li></ul>');
                                     }
                        }
                        //jQuery('.saved-td').add('<ul><li><input type="checkbox" class="saved-keys" name="saved_keys[]" value = "test">test</input> </li></ul>');
                        
                        //Delete the keys
                         jQuery('.added-keys').each(function(){
                            if (jQuery(this).attr('checked'))
                            {
                                jQuery(this).parent().parent().remove();        
                            }
                        });      
                    }
                );
            } 
             
            function delete_key()
            {
               
                var setOptionForm = document.forms['setupOption'];
                var saved_key_count = document.getElementsByName('saved_keys[]').length;
                var saved_key_list = setOptionForm.elements['saved_keys[]'];
                
                var delete_chk_list = new Array();
                
                for (var i = 0; i < saved_key_count; i++) {
                    if (saved_key_list[i].checked) {
                        delete_chk_list.push(saved_key_list[i].value);
                     }
                }
                
                jQuery.post(
                    ajaxurl,
                    {
                        'action':'delete_key',
                        'delete_chk_keys':delete_chk_list,
                    },
                    function(response)
                    {
                        alert(response);
                        
                        jQuery('.saved-keys').each(function(){
                            if (jQuery(this).attr('checked'))
                                jQuery(this).parent().parent().remove();
                        });
                        
                    }
                );
                
            } 
            function get_keywords()
            {
                jQuery('#loading').html('Loading...<br /><img src="<?=plugins_url().'/contentmagnet/images/ajax-loader.gif';?>" />')
                jQuery.post('<?=plugins_url().'/contentmagnet/classes/CMKeyword.php';?>',{parameter:"none"}, 
                function(data){
                    jQuery('#loading').remove();
                    jQuery('#keywords').html(data);
					jQuery('#savekeys').show();
                });            
            }
        </script>
        <?php
    }
    
	// stat row for every search term
	public static function stat_row($arr, $id)
	{
		$sql = "SELECT state, sr_post_id, sr_video_chosen, sr_videos_found, sr_next_runtime FROM {$arr['model']->db_table_search_terms} WHERE id = $id";
		
		$result = $arr['model']->wpdb->get_row($sql);
		
		// get posts title
		// checks whether there is such post
		if (null != ($post = get_post($result->sr_post_id)))
		{
			$post_title = CMDesign::str_s($post->post_title);
			
			$post_link = "<a href=\"$post->guid\">$post_title</a>";
		}
		else
		{
			// show this, if the post was deleted
			$post_link = __('post was deleted', 'cm-plugin');
		}
		
		// date time format (set in wordpress)
		$CMdate_format = get_option('date_format') . " " . get_option('time_format');
		$CMdate_format = (!empty($CMdate_format) && !$arr['use_this_date']) ? $CMdate_format : $arr['date_format'];
		
		$output = null;
		
		// set next run time
		$next_runtime = ('active' == $result->state) ?
		date($CMdate_format, $result->sr_next_runtime) : __('search term is paused', 'cm-plugin') ;
		
		// run now link
		$run_now_link = wp_nonce_url("?page={$arr['plugin_page']}&amp;action=run_now&amp;id=$id", 'run-now-' . $id);
		
		// video was added
		if (0 != $result->sr_post_id)
		{
			?>
	<table border="0" class="stat_row_table">
		<tr>
			<th style="text-align: center" class="sr_last_post"><?php _e('Last post', 'cm-plugin') ?></th>
			<td style="text-align: center"><?php echo $post_link ?></td>
			<th style="text-align: center"><?php _e('Video/Article chosen', 'cm-plugin') ?></th>
			<td style="text-align: center"><?php echo "$result->sr_video_chosen/$result->sr_videos_found" ?></td>
			<th style="text-align: center"><?php _e('Next run', 'cm-plugin') ?></th>
			<td style="text-align: center"><?php echo $next_runtime ?></td>
			<td style="text-align: center" class="sr_run_now"><a href="<?php echo $run_now_link ?>" class="run_now"><?php _e('Run now', 'cm-plugin') ?></a></td>
		</tr>
	</table>
			<?php
		}
		// if adding a video right now
		else if (0 == $result->sr_next_runtime)
		{
			?>
	<table border="0" class="stat_row_table">
		<tr>
			<td><?php printf(__(CMMessages::adding_new_video), "?page={$arr['plugin_page']}") ?></td>
		</tr>
	</table>
			<?php
		}
		// no video found
		else
		{
			?>
	<table border="0" class="stat_row_table">
		<tr>
			<th style="text-align: center"  class="sr_last_post"><?php _e('Last post', 'cm-plugin') ?></th>
			<td style="text-align: center"><?php _e('no video/article was found', 'cm-plugin') ?></td>
			<th style="text-align: center"><?php _e('Videos/Articles found', 'cm-plugin') ?></th>
			<td style="text-align: center"><?php echo $result->sr_videos_found ?></td>
			<th style="text-align: center"><?php _e('Next run', 'cm-plugin') ?></th>
			<td style="text-align: center"><?php echo $next_runtime ?></td>
			<td style="text-align: center" class="sr_run_now"><a href="<?php echo $run_now_link ?>" class="run_now"><?php _e('Run now', 'cm-plugin') ?></a></td>
		</tr>
	</table>
			<?php
		}
	}
	
	// edit searchterm form
	public static function edit_search_term_page($arr, $id = null, $search_terms_amount = 0)
	{
		$id = (int) $id;
		
		// edit search term
		if (!empty($id))
		{
			$heading = __("Edit Search Term", 'cm-plugin');
			$submit_text = __('Edit Search Term', 'cm-plugin');
			$form = "<form name=\"editsearchterm\" id=\"editsearchterm\" method=\"post\" action=\"?page={$arr['plugin_page']}\" class=\"validate\">\n";
			$action = 'editsearchterm';
			$nonce_action = 'update-searchterm-' . $id;
			$searchterm = $arr['model']->seleCMsearch_term($id);
			
			$submit_disabled = null;
		}
		// add search term
		else
		{
			$heading = __("Add Search Term", 'cm-plugin');
			$submit_text = __('Add Search Term', 'cm-plugin');
			$form = "<form name=\"addsearchterm\" id=\"addsearchterm\" method=\"post\" action=\"?page={$arr['plugin_page']}\" class=\"add:the-list:validate\">\n";
			$action = 'addsearchterm';
			$nonce_action = 'add-searchterm';
			/*
			if(CMLV_LIMIT>0){
				$submit_disabled = (5 == CMLV_LIMIT && $search_terms_amount >= CMLV_LIMIT) ? 
				"disabled=\"disabled\"" : null ;
			}
			*/
		}
		?>


		<?php (!empty($id)) ? screen_icon() : ''; ?>
		<h2><?php echo $heading ?></h2>
		<div id="ajax-response"></div>
        
        <?php echo $form ?>
		<input type="hidden" name="action" value="<?php echo $action ?>" />
		<input type="hidden" name="id" value="<?php echo $id ?>" />
		<?php wp_nonce_field($nonce_action); ?>
			<table style="clear: none" class="form-table">
				<tr valign="top">
					<th scope="row"><label for="search_type"><?php _e('Search', 'cm-plugin') ?></label></th>
					<td>
						<?php echo CMDesign::sf_dropdown($arr, (!empty($id)) ? attribute_escape($searchterm->search_type) : null) ?>
                        
                        <input name="search_query_2" id="search_query_2" type="text" value="<?php echo (!empty($id)) ? attribute_escape($searchterm->search_query) : null; ?>" style="width: auto" />
                        
                        - OR -
                        
                        <?php 
                             
                             $saved_keys = $arr['model']->seleCMkey();
                        ?>
                        <select name="search_query" id="search_query" style="width: auto;" >
                        <option value='none'>Select Trends</option>
                        <?php
						if(!empty($saved_keys)){
                           foreach($saved_keys as $key) 
                           {
                        ?>
                         <option value="<?php echo $key; ?>"> <?php echo $key; ?> </option>
                         <?php
                           }
						}
                         ?>
                        
                        </select><br />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="network"><?php _e('Publisher Network', 'cm-plugin') ?></label></th>
					<td>
					<?php echo CMDesign::network_dropdown($arr, (!empty($id)) ? attribute_escape($searchterm->network) : null) ?>
					</td>
				</tr>                
				<tr valign="top">
					<th scope="row"><label for="run_num"><?php _e('Run every', 'cm-plugin') ?></label></th>
					<td>
						<input name="run_num" id="run_num" type="text" value="<?php echo (!empty($id)) ? attribute_escape($searchterm->run_num) : null; ?>" class="small-text" />
						<?php echo CMDesign::rp_dropdown($arr, (!empty($id)) ? attribute_escape($searchterm->run_period) : null) ?>
						&nbsp;<label><input type="checkbox" id="humanized" name="humanized" <?php if (!empty($id) && 1 == $searchterm->humanized) { echo "checked=\"checked\""; } ?>/>
						<?php _e('humanize', 'cm-plugin') ?></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="category_id"><?php _e('Category', 'cm-plugin') ?></label></th>
					<td>
						<?php echo CMDesign::c_dropdown((!empty($id)) ? attribute_escape($searchterm->category_id) : null) ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="post_comments"><?php _e('Comments', 'cm-plugin') ?></label></th>
					<td>
						<?php echo CMDesign::pc_dropdown($arr, (!empty($id)) ? attribute_escape($searchterm->post_comments) : null) ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="post_author_id"><?php _e('Post author', 'cm-plugin') ?></label></th>
					<td>
					<?php echo CMDesign::pa_dropdown($arr, (!empty($id)) ? attribute_escape($searchterm->post_author_id) : null) ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="post_status"><?php _e('Post status', 'cm-plugin') ?></label></th>
					<td>
					<?php echo CMDesign::ps_dropdown($arr, (!empty($id)) ? attribute_escape($searchterm->post_status) : null) ?>
					</td>
				</tr>
			</table>
		<p class="submit"><input type="submit" class="button-primary" name="Submit" <?php echo $submit_disabled ?> value="<?php echo $submit_text ?>" /></p>
		</form>
        
		</div>
		<?php
	}
	
	// blog posts
	public static function blog_posts($arr)
	{
		?>
		
		<table class="CMblog_posts_t">
			<tr>
		<?php
		for ($i = 0; $i < 3; ++$i)
		{
			if (isset($arr['blog_posts'][$i]['title']))
			{
				
				?>
				<td>
					<div class="bp_title"><a href="<?php echo $arr['blog_posts'][$i]['link'] ?>" target="_blank"><?php echo $arr['blog_posts'][$i]['title'] ?></a></div>
					<div><?php echo CMDesign::str_s($arr['blog_posts'][$i]['description'], 100)?></div>
				</td>
				<?php
			}
			else
			{
				?>
				<td rowspan="2" style="vertical-align: middle; text-align: center">No post</td>
				<?php
			}
		}
		?>
			</tr>
		</table>
		
		<?php
	}
	
	// edit post code
	public static function edit_post_code($arr)
	{
		// show messages
		if (isset($arr['message']))
		{
			echo "<div id=\"message\" class=\"updated fade\"><p>" . __($arr['message'], 'cm-plugin') . "</p></div>";

		}
		?>
		<div class="wrap">
			
			<h2><?php _e('Edit Post Code', 'cm-plugin') ?></h2>

			<form method="POST" action="?page=<?php echo $arr['plugin_page'] ?>&amp;action=updatepostcode">
				<?php wp_nonce_field('edit-post-code') ?>
				<div style="margin: 0 auto; width: 70%">
					<textarea id="CMpostcode" name="CMpostcode" rows="17" style="width: 100%"><?php echo $arr['post_code'] ?></textarea>
					<br />
					<a href="?page=<?php echo $arr['plugin_page'] ?>&amp;action=editpostcode&amp;reset=true"><?php _e('Reset to default', 'cm-plugin') ?></a>
				</div>

				<p><?php _e(CMMessages::edit_post_code, 'cm-plugin') ?></p>

				<p class="submit">
					<input type="submit" class="button-primary" name="Submit" value="<?php _e('Update Post Code »', 'cm-plugin') ?>" />
				</p>
			</form>
		</div>
		<?php
	}
	
	// powered by
	public static function powered_by_wpl($arr)
	{
		// css (style) here is needed so that users won't be able to disable this link via css file
		// they can use .pb_wpl css class to customize the link
		echo "<div class=\"pb_wpl\" size=\"1\" style=\"display: block; font-size:7pt; visibility: visible\">" . __($arr['powered_by_wpl'], 'cm-plugin') . "</div>";
	}
	
	/*******************************************************/
	
	// show search for drop down
	public static function sf_dropdown($arr, $search_type = null)
	{
		$output='';
		
		foreach ($arr['search_type_arr'] as $key => $value)
		{
			if ($search_type == $key)
			{
				$output .= "<option selected=\"selected\" value=\"$key\">$value</option>\n";
			}
			else
			{
				$output .= "<option value=\"$key\">$value</option>\n";
			}
		}
		
		$output = "<select id=\"search_type\" name=\"search_type\" class=\"postform\">\n$output</select>\n";
		
		return $output;
	}
	
	// show run period drop down
	public static function rp_dropdown($arr, $run_period)
	{
		$output = null;
		
		// set default $run_period
		$run_period = (empty($run_period)) ? 'day' : $run_period ;
		
		foreach ($arr['run_period_arr'] as $key => $value)
		{
			if ($run_period == $key)
			{
				$output .= "<option selected=\"selected\" value=\"$key\">$value</option>\n";
			}
			else
			{
				$output .= "<option value=\"$key\">$value</option>\n";
			}
		}
		
		$output = "<select id=\"run_period\" name=\"run_period\" class=\"postform\">\n$output</select>\n";
		
		return $output;
	}
	
	// show drop down categories
	public static function c_dropdown($category_id)
	{
		// get id of category or of default category
		$category_id = (!empty($category_id)) ? $category_id : get_option('default_category');
		
		return wp_dropdown_categories('hide_empty=0&name=category_id&orderby=name&selected=' . $category_id . '&hierarchical=1&show_option_none=&echo=0');
	}
	
	// show drop down post comments
	public static function pc_dropdown($arr, $post_status = null)
	{
		$pc_array = array(
			"yes" => "yes",
			"no" => "no",
		);
		
		$output = null;
		
		foreach ($pc_array as $key => $value)
		{
			if ($key == $post_status)
			{
				$output .= "<option selected=\"selected\" value=\"$key\">$value</option>\n";
			}
			else
			{
				$output .= "<option value=\"$key\">$value</option>\n";
			}
		}
		
		$output = "<select id=\"post_comments\" name=\"post_comments\" class=\"postform\">\n$output</select>\n";
		
		return $output;
	}
	
	// show post author drop down
	public static function pa_dropdown($arr, $post_author_id = null)
	{
		// get authors from the table
		$sql = "SELECT ID, display_name FROM {$arr['model']->wpdb->users}";
		
		$result = $arr['model']->wpdb->get_results($sql);
		
		$output = null;
		
		// display each author and mark the required one selected
		foreach ($result as $post_author)
		{
			if ($post_author->ID == $post_author_id)
			{
				$output .= "<option selected=\"selected\" value=\"$post_author->ID\">$post_author->display_name</option>\n";
			}
			else
			{
				$output .= "<option value=\"$post_author->ID\">$post_author->display_name</option>\n";
			}
		}
		
		$output = "<select id=\"post_author_id\" name=\"post_author_id\" class=\"postform\">\n$output</select>\n";
		
		return $output;
	}

	// show post status drop down
	public static function ps_dropdown($arr, $post_status = null)
	{
		$output = null;
		
		foreach ($arr['post_status_arr'] as $key => $value)
		{
			if ($key == $post_status)
			{
				$output .= "<option selected=\"selected\" value=\"$key\">$value</option>\n";
			}
			else
			{
				$output .= "<option value=\"$key\">$value</option>\n";
			}
		}
		
		$output = "<select id=\"post_status\" name=\"post_status\" class=\"postform\">\n$output</select>\n";
		
		return $output;
	}
	
	// show network dropddown Ricky
	public static function network_dropdown($arr, $network = null)
	{
		$output = null;
		
		foreach ($arr['network_arr'] as $key => $value)
		{
			//Check if Network is activated or limted - Rickyyy
			switch($key)
			{
				case 'amazon':
				$amazon_id= get_option('CMamazon_associate');
				if(!empty($amazon_id)){$valid_nets[$key]=$value;}	
				break;
                
				case 'linkshare':
				$linkshare_id = get_option('CMlinkshare_api');
				if(!empty($linkshare_id)){$valid_nets[$key]=$value;}
				break;

				case 'cj':
				$cj_id= get_option('CMcj_site'); 
				if(!empty($cj_id)){$valid_nets[$key]=$value;}
				break;

				case 'ebay':
				$ebay_id=get_option('CMebaylist_campaignid');
				if(!empty($ebay_id)){$valid_nets[$key]=$value;}
				break;

				case 'clickbank':
				$clickbank_id= get_option('CMclickbank_id');
				if(!empty($clickbank_id)){$valid_nets[$key]=$value;}
				break;
			}
			
		}
		foreach($valid_nets as $key=>$value)
		{			
			
			if(CMLV_LIMIT>0){
				//invoke only amazon
				if($key!='amazon') continue;
			}
			
			
			if ($key == $network)
			{
				$output .= "<option selected=\"selected\" value=\"$key\">$value</option>\n";
			}
			else
			{
				$output .= "<option value=\"$key\">$value</option>\n";
			}
		}
		
		$output = "<select id=\"network\" name=\"network\" class=\"postform\">\n$output</select>\n";
		
		return $output;
	}	
	
	// change post title to ...
	public static function str_s($string, $char_num = 23)
	{
		// if we have multibyte save string functions
		if (function_exists('mb_strlen'))
		{
			// get prev encoding
			$prev_enc = mb_internal_encoding();
			
			mb_internal_encoding('UTF-8');
			
			if ($char_num < mb_strlen($string))
			{
				$string = mb_substr($string, 0, $char_num - 3) . "...";
			}
			
			// set encoding back
			mb_internal_encoding($prev_enc);
		}
		else
		{
			if ($char_num < strlen($string))
			{
				$string = substr($string, 0, $char_num - 3) . "...";
			}
		}
		
		return $string;
	}
}
?>