<?php

/*
This class contains messages that the plugin uses
*/

class CMMessages
{
	/* Status messages */
	
	const st_search_term_added = "Search term added."; // search term added
	
	const st_search_term_updated = "Search term updated."; // search term updated
	
	const st_search_terms_deleted = "Search term(s) deleted."; // search terms deleted
	
	const st_search_term_paused = "Search term paused."; // search term paused
	
	const st_search_term_activated = "Search term activated."; // search term activated
	
	const st_post_code_edited = "Post code edited."; // post code edited
	
	const st_post_code_reset = "Post code reset to default."; // reset to default post code
	
	const st_error = "Something strange happened."; // error message
	
	/* General messages */
	
	const description = "This plugin allows you to post videos automatically from YouTube based on keywords you setup.<br>You can edit video post template <a href=\"%s\">here</a>."; // link to edit post code page
	
	const no_search_terms = "You have no search terms added yet! <a href='%s'>Add one</a> to start working with the plugin."; // an achor to add new search term form
	
	const adding_new_video = "A new Video Or Article is being added right now. <a href=\"%s\">Reload the page</a> to see the results."; // link to reload this page
	
	const edit_post_code = "Here you can change post code of posts that this plugin makes. You can use variables: {video_url}, {video_description}, {search_query}.";
	
	// light version
	const light_version = "You can use up to %u more search term(s). Upgrade to the <a href=\"%s\">full version</a> to have unlimited search terms."; // link to upgrade plugin page
}
?>