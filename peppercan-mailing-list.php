<?php
/*
Plugin Name: Peppercan Mailing List Manager
Plugin URI: http://www.safecoms.com
Description: Create submission forms to Peppercan Mailing list
Author: Guillaume B
Version: 1.0
Author URI: http://www.safecoms.com
*/




/* 
 Add a submenu in the 'Settings' admin menu. The links goes to the settings page.
*/
function peppercan_mailing_menu()
{
	add_submenu_page('options-general.php', 'Mailing list Peppercan', 'Mailing list Peppercan', 'manage_options', 'peppercan-mailing-list', 'peppercan_mailing_settings');
}

/*
 We register the peppercan_mailing_menu function to the admin_menu hook
 That means that this function will be called every time Wordpress build the admin menu
*/
add_action('admin_menu', 'peppercan_mailing_menu');






/*
 Replace the shortcode [peppercan_submission_form] by a registration form.
 You can put this shortcode in pages, posts.
 To use this shortcode in the sidebar, you should install a plugin like "Sidebar Shortcodes" to enable shortcode in the sidebar.
*/
function ppc_mailing_shortcode()
{
	$formCode = '';
	
	/* Begining of the form */
	$formCode = $formCode . '
<form action="" method="post" name="peppercanform"  target=_blank>
<p class="ppc_mailing_input_p"><label for="external_subscriber_first_name">First Name</label><input id="external_subscriber_first_name" name="external_subscriber[first_name]" type="text"></p>

<p class="ppc_mailing_input_p"><label for="external_subscriber_last_name">Last Name</label><input id="external_subscriber_last_name" name="external_subscriber[last_name]" type="text"></p>

<p class="ppc_mailing_input_p"><label for="external_subscriber_email">Email</label><input id="external_subscriber_email" name="external_subscriber[email]" type="text"></p>

<p class="ppc_mailing_select_p">' . get_option('peppercan_mailing_label_1') . ' :';
	
	/* Foreach mailing list registered in the wp_options database, we create a checkbox */
	foreach(get_option('peppercan_mailing_list') as $listId => $listName)
	{
		$formCode = $formCode . '<br /><input type="checkbox" id="ppc_checkbox_mailing_' . $listId . '" name="mailing_' . $listId . '" /> <label for="ppc_checkbox_mailing_' . $listId . '">' . $listName . '</label>';
	}
	
	$formCode = $formCode . '</p>

<p class="ppc_mailing_submit_p"><input name="commit" id="ppc_mailing_submit_button" value="' . get_option('peppercan_mailing_label_2') . '" type="submit" onclick="submit_form(this.form);"></p>

</form>';
	
	/* We return the HTML code of the form */
	return $formCode;
}
/*
 We register the shortcode (with add_shortcode function)
*/
add_shortcode('peppercan_submission_form', 'ppc_mailing_shortcode' );



/*
 Javascript who will be include in the header.
 For each mailing list, we check if the checkbox associated is checked.
 If yes, we submit the form to the right URL.
*/
function ppc_mailing_script()
{
	echo '
	<script type="text/javascript">
	
	function submit_form(form)
	{
	
	';
	
	/* We create an 'if' statement for each mailing list */
	foreach(get_option('peppercan_mailing_list') as $listId => $listName)
	{
		echo '
		if(document.getElementById(\'ppc_checkbox_mailing_' . $listId . '\').checked == true)
		{
			document.peppercanform.action = "http://' . get_option('peppercan_site_url') . '.peppercan.com/mailing/external_subscription?add_to_list=' . $listId . '&ext=1";
			form.submit();
		}';
	}
	
	echo '
	}
	</script>';
}

add_action('wp_head', 'ppc_mailing_script');




/*
 This is the settings page (for the Wordpress admin section)
 Users can set up the form (number and names of mailing list, peppercan ULR...)
*/
function peppercan_mailing_settings()
{
	/*
	Settings are registered in the Wordpress options table in the database.
	We can get them using the function get_option($option_name)
	
	Here, we use 2 options :
		- peppercan_site_url : the URL of Peppercan (without http:// and .peppercan.com)
		- peppercan_mailing_list : an array with the ID of the mailing list (key) and the name of the mailing list (value)
		- peppercan_mailing_label_1 : the label to display in the form ("Choose your list" by default)
		- peppercan_mailing_label_2 : the label of the submit buttom ("Register" by default)
	*/

	
	
	/* We check if these setting already exist. If no, we create them */
	if(get_option('peppercan_site_url') == false)
	{
		add_option('peppercan_site_url', 'safecoms', '', 'no');
	}
	
	if(get_option('peppercan_mailing_list') == false)
	{
		add_option('peppercan_mailing_list', array('1' => 'Name of the first mailing list'), '', 'no');
	}
	if(get_option('peppercan_mailing_label_1') == false)
	{
		add_option('peppercan_mailing_label_1', 'Choose your list', '', 'no');
	}
	if(get_option('peppercan_mailing_label_2') == false)
	{
		add_option('peppercan_mailing_label_2', 'Register', '', 'no');
	}
	
	
	/* Has the form been submitted ? */
	if(isset($_POST['site_url']))
	{
		/* We update the Peppecan site ULR */
		update_option('peppercan_site_url', $_POST['site_url']);
		
		/* We update the labels */
		update_option('peppercan_mailing_label_1', $_POST['label_1']);
		update_option('peppercan_mailing_label_2', $_POST['label_2']);
		
		/* We update mailing lists */
		if(intval($_POST['number'] != 0))
		{
			$i = 0;
			
			/* We create an array to put the mailing list data */
			$mailingList = array();
			
			/*
			Foreach mailng list, we insert an element in the array
				- key : mailing list ID
				- value : mailing list name
			*/
			for($i = 0; $i < $_POST['number']; $i++)
			{
				if(isset($_POST['list_id_' . $i]))
				{
					$mailingList[$_POST['list_id_' . $i]] = $_POST['mailing_name_' . $i];
				}
				else
				{
					$mailingList[] = 'Type the name here';
				}
			}
			
			/* We update the mailing list data */
			update_option('peppercan_mailing_list', $mailingList);
		}
	}
	
	
	$mailingList = get_option('peppercan_mailing_list');
	
	?>
	
	<h1>Peppercan Mailing List Settings</h1>
	
	<p style="margin-top: 40px;">
	You can set up your mailing lists on this page. Please follow these 3 steps :
	</p>
	
	<ul style="list-style-type: disc; margin-left: 30px; margin-bottom: 30px;">
		<li><b>Set up the general settings</b> : enter your Peppercan URL and the number of mailing list you want to use</li>
		<li><b>Set up mailing lists settings</b> : type the name and ID of your mailing lists. Look at the help section at the bottom of this page.</li>
		<li><b>Insert the form in one of your Wordpress page</b>. You just have to put the shortcode <code>[peppercan_submission_form]</code> in the content of a page or post. This will be replaced automatically by a form, who will contain some fileds (First Name, Last Name, Email) and one checkbox for each mailing list you have registered bellow. Visitors can select which mailing list they want to suscribe.<br /><em>You can also insert this form in the sidebar. To do that, you should install a plugin like "Sidebar Shortcodes" to enable shortcode in the sidebar.</em></li>
	</ul>
	
	
	<form method="post" action="<?php echo site_url();?>/wp-admin/options-general.php?page=peppercan-mailing-list">
	
		<h2>General Settings</h2>
		
		<div class="peppercan_general_settings_div">
	
		<label for="site_url" style="width: 200px; display: block; float: left;">Peppercan URL</label>
		http://<input type="text" style="width: 80px" id="site_url" name="site_url" value="<?php echo get_option('peppercan_site_url');?>" />.peppercan.com
		
		<br />
		<br />
		
		<label for="number"  style="width: 200px; display: block; float: left;">Number of mailing list</label>
		<input type="text" style="width: 30px" id="number" name="number" value="<?php echo count($mailingList);?>" />
		<br />
		<em>Type the number, then save the settings. It will update the number of fields bellow.</em>
		
		<br />
		<br />
		
		<label for="label_1"  style="width: 200px; display: block; float: left;">Label before the list of Mailing lists</label>
		<input type="text" style="width: 150px" id="label_1" name="label_1" value="<?php echo get_option('peppercan_mailing_label_1');?>" />
		<br />
		<em>Text who will appear just before the checkbox</em>
		
		<br />
		<br />
		
		<label for="label_2"  style="width: 200px; display: block; float: left;">Label for the submit button</label>
		<input type="text" style="width: 150px" id="label_2" name="label_2" value="<?php echo get_option('peppercan_mailing_label_2');?>" />
		
		<br />
		<br />
		
		</div>
		
		<input type="submit" value="Save settings" />
		
		
		<br />
		
		<h2>Mailing list settings</h2>
		
<?php 

	$i = 0;

	foreach($mailingList as $listId => $listName)
	{
?>

	<div class="peppercan_mailing_list_div">

	<label for="mailing_name_<?php echo $i;?>" style="width: 150px; display: block; float: left;">Name of the mailing list</label>
	<input type="text" style="width: 200px" id="mailing_name_<?php echo $i;?>" name="mailing_name_<?php echo $i;?>" value="<?php echo $listName;?>" />
	
	<br />
	
	<label for="list_id_<?php echo $i;?>" style="width: 150px; display: block; float: left;">ID of the mailing list</label>
	<input type="text" style="width: 30px" id="list_id_<?php echo $i;?>" name="list_id_<?php echo $i;?>" value="<?php echo $listId;?>" />
	
	</div>

<?php 

	$i++;

	}
	
?>
		
		
		<input type="submit" value="Save mailing lists" />
		
	</form>
	
	<p style="margin-top: 30px; width: 700px;">
	<img src="<?php echo site_url();?>/wp-content/plugins/peppercan-mailing-list/help.png" style="float: left; margin: 25px 10px;" />
	The <b>name of the mailing list</b> is the name who will appear in the form. You can choose another name than the real name of the Peppercan mailing list.
	<br />
	<br />
	The <b>ID of the mailing list</b> is a number who permit to identify the mailing list. To have it, go on Peppercan, on the <em>Manage Mailing List</em> page and look at the URL. It should be like <b>http://<?php echo get_option('peppercan_site_url');?>.peppercan.com/mailing/list_details/<span style="color: red">4</span></b>. Here, <b>4</b> is the mailing list ID.
	</p>
	
	<br />
	<br />
	<br />
	
	<hr />
	
	<br />
	<br />
	
	<h2>What your form look like ?</h2>
	
	<p>Here is the registration form as he will appear in pages (without CSS style). You can design it using CSS :</p>
	
	<?php echo ppc_mailing_shortcode();?>
	
	
<?php 

}


function ppc_mailing_css_admin()
{
	echo '
<style type="text/css">

.peppercan_mailing_list_div {
-webkit-border-radius: 7px;
-moz-border-radius: 7px;
border-radius: 7px;
background-color: #dbe1ed;
border : 1px solid #394c71;
padding: 10px;
margin: 20px;
width: 400px;
}

.peppercan_general_settings_div {
-webkit-border-radius: 7px;
-moz-border-radius: 7px;
border-radius: 7px;
background-color: #e7f5ee;
border : 1px solid #2e664a;
padding: 10px;
margin: 20px;
width: 600px;
}

</style>';
}

add_action('admin_head', 'ppc_mailing_css_admin');

?>