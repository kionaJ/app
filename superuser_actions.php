<?php
include('login/includes/api.php');
logged_in();
include('functions.php');
include('superuser_security.php');

if(isset($_GET['action'])) $action=$_GET['action'];
if(isset($_POST['action'])) $action=$_POST['action'];

//variables needed for mailing to users
$link = "";
$link2 = "";
$link3 = "";
$tracker = "";
 
//$plus = "<a href=\"" . $full_path . "/plus_index.php?" . $superuser . "_id=" . $superuser_id . "\" target=\"_blank\"><img src=\"" . $full_path . "/img/plus_" . $superuser . ".jpg\" alt=\"" . $affiliatename . "\" width=\"100%\"></a>";
$plus = "";
$reply_yes = "<small>Deze e-mail werd verstuurd vanuit <a href=\"" . $home_path . "\" target=\"_blank\">" . $appname . "</a>.<br><a href=\"mailto:%reply_to%\">Klik hier om een antwoord te sturen naar de afzender van dit bericht.</a></small><br><a href=\"mailto:%reply_to%\"><img height=\"30\" src=\"" . $full_path . "/img/button_reply.png\" alt=\"beantwoord deze mail\"/></a>";
$reply_no = "<small>Deze e-mail werd verstuurd vanuit <a href=\"" . $home_path . "\" target=\"_blank\">" . $appname . "</a>.<br>Je kan niet reageren op deze mail.</small>";
$disclaimer = "";

//add advertising to mails
if(checkPremiumLevel($superuser_id, $superuser, "") == "level 2"){
	$advertising = displayAdvertising($superuser_id, $superuser, "0");
}
else {
	//PUT HERE APP ADVERTISING IN MAILING
	//$advertising = displayAdvertising("0", "0", "1");
	$advertising = "";
}

//DELETE COMPLETE SUBCATEGORY
	if(isset ($action) && $action == "delete"){
	
	$subcategory_id=sres($_POST['id'], $superuser_id, $superuser);
	
	//delete subcategory from table categories
	mysqli_query($con,"DELETE FROM " . $superuser . "s_categories WHERE " . $superuser . "_id = $superuser_id AND id=$subcategory_id");
	
	//delete from categories_sync
	mysqli_query($con,"DELETE FROM " . $superuser . "s_categories_sync WHERE " . $superuser . "_id=$superuser_id AND subcategory_id=$subcategory_id");
	
	//delete contacts from table contact
	mysqli_query($con,"DELETE FROM " . $superuser . "s_contact WHERE " . $superuser . "_id = $superuser_id AND subcategory_id=$subcategory_id");
	
	//delete contacts from table default_blocks
	mysqli_query($con,"DELETE FROM " . $superuser . "s_default_blocks WHERE " . $superuser . "_id=$superuser_id AND subcategory_id=$subcategory_id");
	
	//delete documents not for all from table documents
	mysqli_query($con,"DELETE FROM " . $superuser . "s_documents WHERE subcategory=$subcategory_id AND " . $superuser . "_id=$superuser_id AND forall = 0");
		
	//delete subcategory from table calendar
	mysqli_query($con,"DELETE FROM calendar WHERE " . $superuser . "_id=$superuser_id AND subcategory = $subcategory_id AND (ForAll = 0 OR ForAll = 3)");
	
	//delete subcategory from table mail_read (leesbevestiging)
	mysqli_query($con,"DELETE FROM mail_read WHERE " . $superuser . "_id=$superuser_id AND subcategory_id = $subcategory_id");

	//delete all shares from table share
	mysqli_query($con,"DELETE FROM " . $superuser . "s_share WHERE " . $superuser . "_id=$superuser_id AND subcategory_id = $subcategory_id");
	 
	//delete custom labels
	mysqli_query($con,"DELETE FROM " . $superuser . "s_labels WHERE " . $superuser . "_id=$superuser_id AND subcategory_id = $subcategory_id AND ForAll = 0");
	mysqli_query($con,"DELETE FROM labels WHERE " . $superuser . "_id=$superuser_id AND subcategory_id = $subcategory_id");
	
	//delete manual members
	mysqli_query($con, "DELETE FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id=$superuser_id AND subcategory = $subcategory_id");
	 
	//update kids: set category back to zero
	$sql_kids = mysqli_query($con, "SELECT * FROM kids_" . $superuser . "s WHERE " . $superuser . "_id=$superuser_id AND subcategory = $subcategory_id");
	if(mysqli_num_rows($sql_kids) > 0){
		while($row_kids = mysqli_fetch_array($sql_kids)){
			if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM kids_" . $superuser . "s WHERE " . $superuser . "_id=$superuser_id AND kid_id = $row_kids[kid_id] AND subcategory <> 0")) > 1){
				mysqli_query($con,"DELETE FROM kids_" . $superuser . "s WHERE " . $superuser . "_id=$superuser_id AND kid_id = $row_kids[kid_id] AND subcategory = $subcategory_id");
			}
			else {
				mysqli_query($con,"UPDATE kids_" . $superuser . "s SET subcategory = '0' WHERE " . $superuser . "_id=$superuser_id AND kid_id = $row_kids[kid_id] AND subcategory = $subcategory_id");
			}
	
	}
	}
		
	//update parents: set category back to zero
	$sql_parents = mysqli_query($con, "SELECT * FROM parents_" . $superuser . "s WHERE " . $superuser . "_id=$superuser_id AND subcategory = $subcategory_id");
	if(mysqli_num_rows($sql_parents) > 0){
		while($row_parents = mysqli_fetch_array($sql_parents)){
			if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM parents_" . $superuser . "s WHERE " . $superuser . "_id=$superuser_id AND parent_id = $row_parents[parent_id] AND subcategory <> 0")) > 1){
				mysqli_query($con,"DELETE FROM parents_" . $superuser . "s WHERE " . $superuser . "_id=$superuser_id AND parent_id = $row_parents[parent_id] AND subcategory = $subcategory_id");
			}
			else {
				mysqli_query($con,"UPDATE parents_" . $superuser . "s SET subcategory = '0' WHERE " . $superuser . "_id=$superuser_id AND parent_id = $row_parents[parent_id] AND subcategory = $subcategory_id");
			}
	
	}
	}
   
	
	//remove avatar
	if(userValueSubcategory($subcategory_id, $superuser, "avatar") <> ""){
	$filename = "../data/avatars/$row[avatar]";
	unlink($filename);
	}
	
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "delete", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//CANCEL AN ACTIVITY (not delete)
if(isset ($action) && $action == "CancelActivity"){
	
	$event_id = 0;
	$repeat_id = 0;
	$all = 0;
	$cancelalert = 0;

	if(isset($_POST['event_id']) && is_numeric($_POST['event_id']) && !empty($_POST['event_id']) && $_POST['event_id'] <> 0) $event_id = mres($_POST['event_id']);
	if(isset($_POST['repeat_id']) && is_numeric($_POST['repeat_id']) && !empty($_POST['repeat_id'])) $repeat_id = mres($_POST['repeat_id']);
	if(isset($_POST['all']) && is_numeric($_POST['all']) && !empty($_POST['all'])) $all = mres($_POST['all']);
	$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	
	if(isset($_POST['cancelalert']) && is_numeric($_POST['cancelalert']) && !empty($_POST['cancelalert'])) $cancelalert = mres($_POST['cancelalert']);
	
	$check = mysqli_query($con, "SELECT * FROM calendar WHERE id = $event_id AND " . $superuser ."_id = $superuser_id");
	if(mysqli_num_rows($check) > 0){
	$row_check = mysqli_fetch_array($check);
	
	//make same vars as in action "changecomment"
	$ForAll = $row_check['ForAll'];
	$subcategory = $row_check['subcategory'];
	$eventid = $event_id;
	$CopyToSubcategory = $row_check['ForSome'];
	$SomeStudentKid = $row_check['ForKids'];
	$SomeStudentAdult = $row_check['ForAdults'];
	$SomeStudentManual = $row_check['ForManual'];
	$canceled = $row_check['canceled'];
	
	if($canceled == 1){
		$update_canceled = 0;
	}
	if($canceled == 0){
		$update_canceled = 1;
	}

	
	if($all == 0){
		mysqli_query($con,"UPDATE calendar SET canceled = $update_canceled WHERE id = $event_id AND " . $superuser ."_id = $superuser_id");
	}
	
	if($repeat_id <> 0 && $all == 1){
		mysqli_query($con,"UPDATE calendar SET canceled = $update_canceled WHERE repeat_id = $repeat_id AND " . $superuser ."_id = $superuser_id");
	}
	
	//send mail to all parents if user wants (MailToParents checked)
	if($cancelalert == 1){ 
	
	//fetch sender and greeting 
	//get greeting
	$greetings = getHeaderFooterMailSuper($superuser_id, $superuser, $subcategory_id, "cat_contact_name");
	//get sender
	$user_email = getHeaderFooterMailSuper($superuser_id, $superuser, $subcategory_id, "cat_contact_email");

	
	//print all ids where mail must be sent to in case this event is not ForAll
	  if($ForAll == 0){
	  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, $subcategory, $eventid);
	  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $subcategory);
	  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, $subcategory, $eventid);
	  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
	  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, $subcategory);
	  }
	  
	  if($ForAll == 1){
	  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, 0, $eventid);
	  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $subcategory);
	  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, 0, $eventid);
	  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
	  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, 0);
	  }
	  
	  if($ForAll == 2){
	  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory, $eventid);
	  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $CopyToSubcategory);
	  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory, $eventid);
	  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
	  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory);
	  }
	  
	  if($ForAll == 3){
	  $kids_list = $SomeStudentKid;
	  $parent_list = getParentIdsFromKidIds($SomeStudentKid, $superuser_id, $superuser, $subcategory);
	  $adult_list = $SomeStudentAdult;
	  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
	  $manual_list = $SomeStudentManual;
	  }
	  
	  $rec_1 = CountRecipients($kids_list, $adult_list, $manual_list, $superuser_id, $superuser, $subcategory);
		$queue = 0;
		if($rec_1 > $max_mails_direct){
			$queue = 1;
		}
   
	  //Build message 
		if($update_canceled == 1){
		if($all == 0){
		$subject = $superuser_name . " heeft de activiteit van " . dutchDateFromEvent($event_id) . " geannuleerd op " . $appname;
		$message_title = "Annulatie activiteit";
		$message = "Beste,<br><br>
		De activiteit van " . dutchDateFromEvent($event_id) . " werd geannuleerd.<br>Open " . $appname . " voor meer informatie.<br><br>
		
		Vriendelijke groet,<br>
		" . $greetings . "";
		}
		if($repeat_id <> 0 && $all == 1){
		$subject = $superuser_name . " heeft meerdere activiteiten geannuleerd op " . $appname;
		$message_title = "Annulatie activiteiten";
		$message = "Beste,<br><br>
		Er werden meerdere activiteiten geannuleerd op " . $appname . ".<br>Open " . $appname . " voor meer informatie.<br><br>
		
		Vriendelijke groet,<br>
		" . $greetings . "";
		}		
		}
		if($update_canceled == 0){
		if($all == 0){
		$subject = $superuser_name . " heeft de activiteit van " . dutchDateFromEvent($event_id) . " terug geactiveerd op " . $appname;
		$message_title = "Geannuleerde activiteit terug actief gemaakt";
		$message = "Beste,<br><br>
		De geannuleerde activiteit van " . dutchDateFromEvent($event_id) . " werd terug actief gemaakt.<br>Open " . $appname . " voor meer informatie.<br><br>
		
		Vriendelijke groet,<br>
		" . $greetings . "";
		}
		if($repeat_id <> 0 && $all == 1){
		$subject = $superuser_name . " heeft meerdere activiteiten terug geactiveerd op " . $appname;
		$message_title = "Meerdere geannuleerde activiteiten terug actief gemaakt.";
		$message = "Beste,<br><br>
		Er werden meerdere activiteiten terug actief gemaakt op " . $appname . ".<br>Open " . $appname . " voor meer informatie.<br><br>
		
		Vriendelijke groet,<br>
		" . $greetings . "";
		}
		}
		
		if($all == 0){
		$message .= "<br><br><a href=\"" . $full_path . "/openlink.php?deeplink=1&goto_event_id=$event_id\" class=\"link-button\" target=\"_blank\">Bekijk activiteit<br>op " . $appname . "</a>";
		}
	
	$logged_in_user = userValue(null, "id");
	$senddate = date("Y-m-d");
	
	require_once 'plugins/HTMLPurifier/HTMLPurifier.auto.php';
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	$dirty_html = $message;
	$message = $purifier->purify($dirty_html);
	$message_database = mres($message);
	
	$insert = mysqli_query($con,"INSERT INTO mails (event_id,id_list_subgroups,id_list_kids,id_list_adults,id_list_manual,subcategory_id," . $superuser . "_id,laterdate,senddate,mailfrom,BCC,from_id,subject,message,paybutton,survey,board,rec_1,is_sent,queue) VALUES ($event_id,'0','$kids_list','$adult_list','$manual_list',$subcategory_id,$superuser_id,'0','$senddate','0','', '$logged_in_user','$subject','$message_database','0','0','0','$rec_1','0','$queue')");
	$mail_id = mysqli_insert_id($con);
	if(!$insert){
	error_log("Error description: " . mysqli_error($con), 0);
	}
	
	if($queue <> 1){
		$sql_mail = mysqli_query($con, "SELECT * FROM mails WHERE id = $mail_id AND " . $superuser . "_id = $superuser_id");
		$row_mail = mysqli_fetch_array($sql_mail);
		SendBulkMail($row_mail, 0); //second argument 0 = no cronjob
	}	
	//end if isset MailToParents
	}
	}

LogAction($event_id, $superuser, $superuser_id, $subcategory_id, 0, 0, "CancelActivity", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));	
}

//ADD NEW SUBCATEGORY
if(isset ($action) && $action == "insert"){
  
  $cat_name=mres($_POST['cat_name']);
  $sex=mres($_POST['sex']);
  $start_year=mres($_POST['start_year']);
  $end_year=mres($_POST['end_year']);
  $cat_facebook=mres($_POST['cat_facebook']);
  $cat_website=mres($_POST['cat_website']);
  
  if(isset($_POST['board'])){
	$board = '1';
	}
	else {
	$board = '0';
	}
  
    if(isset($_POST['chat'])){
	$chat = '1';
	}
	else {
	$chat = '0';
	}

  $insert = mysqli_query($con,"INSERT INTO " . $superuser . "s_categories 
  (" . $superuser . "_id,
  cat_name,
  sex,
  start_year, 
  end_year,
  cat_facebook,
  cat_website,
  number_defaults,
  board,
  chat) VALUES 
  ('$superuser_id','$cat_name','$sex','$start_year','$end_year','$cat_facebook','$cat_website','2','$board','$chat')");
    
  if($insert == true) {
	$last_id = mysqli_insert_id($con); //belangrijk dat deze lijn hier staat!
  	
  	//check if this is the first added subgroup -> alert app-team
  	$check = mysqli_query($con,"SELECT id FROM " . $superuser . "s_categories WHERE " . $superuser . "_id = $superuser_id");
  	if(mysqli_num_rows($check) == 1){
	  	 //send mail to app team
		  $user_email = $mailusername;
		  $to      = $mailinfoaddress;
		  $subject = 'ADMIN: Melding van ' . $appname . ': nieuwe ' . $superuser . ' heeft een eerste subgroep toegevoegd.';
		  $message_title = "ADMIN: " . userValueSuper($superuser_id,$superuser,"username") . " heeft een eerste subgroep toegevoegd en is dus actief op " . $appname . " als " . $superuser . ".";
		  $message = "Details:<br>
		  <strong>Naam " . $superuser . ":</strong> " . userValueSuper($superuser_id,$superuser,"username") . "<br>
		  <strong>Email " . $superuser . ":</strong> " . userValueSuper($superuser_id,$superuser,"email") . "<br>
		  <strong>id " . $superuser . ":</strong> " . $superuser_id . "<br>";
		  
		    $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
			$message_body = str_replace('%message_title%', $message_title, $message_body); 
			$message_body = str_replace('%message%', $message, $message_body);
			$message_body = str_replace('%link%', "", $message_body);
			$message_body = str_replace('%link2%', "", $message_body);
			$message_body = str_replace('%link3%', "", $message_body);
			$message_body = str_replace('%reply%', "", $message_body);
			$message_body = str_replace('%avatar%', $logo_path, $message_body);	
			$message_body = str_replace('%full_path%', $full_path, $message_body);		  
			$message_body = str_replace('%appname%', $appname, $message_body);	
				  
		  userSendMail6($user_email, $to, $subject, $message_body); //Send Mail

  	} 
  	
  	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "insert", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
  	
	
	$data['last_insert_id'] = $last_id;
	echo json_encode($data);
	exit;

	}
}

//EDIT A SUBCATEGORY
	if(isset ($action) && $action == "update"){
	  
	  $subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	  $cat_name=mres($_POST['cat_name']);
	  $sex=mres($_POST['sex']);
	  $start_year=mres($_POST['start_year']);
	  $end_year=mres($_POST['end_year']);
	  
	  $cat_facebook=mres($_POST['cat_facebook']);
	  $cat_website=mres($_POST['cat_website']);
	  
	if(isset($_POST['board'])){
	$board = '1';
	}
	else {
	$board = '0';
	}
	
	  if(isset($_POST['chat'])){
	$chat = '1';
	}
	else {
	$chat = '0';
	}

	$update = mysqli_query($con, "UPDATE " . $superuser . "s_categories SET 
	cat_name = '$cat_name',
	sex = '$sex',
	start_year = '$start_year',
	end_year = '$end_year',
	cat_facebook = '$cat_facebook',
	cat_website = '$cat_website',
	board = '$board',
	chat = '$chat'
	WHERE " . $superuser . "_id = $superuser_id
	AND id='$subcategory_id'");
	
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "update", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	
}

//UPDATE A CUSTOM CALENDAR BLOCK
	if(isset ($action) && $action == "UpdateBlock"){
	  
	$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	$block_id = mres($_POST['block_id']);
	$block_type = mres($_POST['block_type']);
	
	$title=mres($_POST['title']);  
	
	$subject=mres($_POST['subject']);  
	
	require_once 'plugins/HTMLPurifier/HTMLPurifier.auto.php';
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	$comment = $_POST["comment"]; //mres hier niet doen!
	$comment = $purifier->purify($comment);
	$comment = mres($comment);
		
	$start_time = mres($_POST['start_time']);
	$end_time = mres($_POST['end_time']);
	$showtimeframe = mres($_POST['showtimeframe']);
	$location=mres($_POST['location']);
	$street = mres($_POST['street']);
	$number = mres($_POST['number']);
	$postal = mres($_POST['postal']);
	$city = mres($_POST['city']);
	$ForAll = mres($_POST['ForAll']);
	
	//check for weather
    $weather = 0;
    if(isset($_POST['weather'])){
	    $weather = 1;
	}
	
	//check for weather
    $invite = 0;
    if(isset($_POST['Invite'])){
	    $invite = 1;
	}
	  
	if(isset($_POST['hex_color'])){
	$hex_color = mres($_POST['hex_color']);
	}
	else {
		$hex_color = "";
	}

	if(isset($ForAll) && $ForAll == "2"){
	if(isset($_POST['CopyToSubcategory'])){
	$CopyToSubcategory = implode(',', $_POST['CopyToSubcategory']);
	}
	}
	else {
	$CopyToSubcategory = "";
	}
	
	if(isset($ForAll) && $ForAll == "3"){
	if(isset($_POST['SomeStudentKid'])){
	$SomeStudentKid = implode(',', $_POST['SomeStudentKid']);
	}
	else {
		$SomeStudentKid = "";
	}
	if(isset($_POST['SomeStudentAdult'])){
	$SomeStudentAdult = implode(',', $_POST['SomeStudentAdult']);
	}
	else {
		$SomeStudentAdult = "";
	}
	if(isset($_POST['SomeStudentManual'])){
	$SomeStudentManual = implode(',', $_POST['SomeStudentManual']);
	}
	else {
		$SomeStudentManual = "";
	}
	}
	else {
	$SomeStudentKid = "";
	$SomeStudentAdult = "";
	$SomeStudentManual = "";
	}
	  
	if($block_type == "default"){
		  if($block_id == "1"){
			$update = mysqli_query($con,"UPDATE " . $superuser . "s_categories SET 
			hex_color_default = '$hex_color',
			title_default = '$title',
			subject_default = '$subject',
			comment_default = '$comment',
			start_time_default = '$start_time',
			end_time_default = '$end_time',
			showtimeframe_default = '$showtimeframe',
			location_default = '$location',
			street_default = '$street',
			number_default = '$number',
			postal_default = '$postal',
			city_default = '$city',
			invite_default = '$invite',
			weather_default = '$weather',
			ForAll_default = '$ForAll',
			ForSome_default = '$CopyToSubcategory',
			ForKids_default = '$SomeStudentKid',
			ForAdults_default = '$SomeStudentAdult',
			ForManual_default = '$SomeStudentManual'
			WHERE " . $superuser . "_id = $superuser_id AND
			id='$subcategory_id'");
			  
		  }
		  if($block_id == "2"){
			  	
			$update = mysqli_query($con,"UPDATE " . $superuser . "s_categories SET
			hex_color_default2 = '$hex_color',
			title_default2 = '$title', 
			subject_default2 = '$subject',
			comment_default2 = '$comment',
			start_time_default2 = '$start_time',
			end_time_default2 = '$end_time',
			showtimeframe_default2 = '$showtimeframe',
			location_default2 = '$location',
			street_default2 = '$street',
			number_default2 = '$number',
			postal_default2 = '$postal',
			city_default2 = '$city',
			invite_default2 = '$invite',
			weather_default2 = '$weather',
			ForAll_default2 = '$ForAll',
			ForSome_default2 = '$CopyToSubcategory',
			ForKids_default2 = '$SomeStudentKid',
			ForAdults_default2 = '$SomeStudentAdult',
			ForManual_default2 = '$SomeStudentManual'
			WHERE " . $superuser . "_id = $superuser_id AND
			id='$subcategory_id'");
		}
	}
	
	if($block_type == "user"){
			$update = mysqli_query($con,"UPDATE " . $superuser . "s_default_blocks SET 
			title = '$title',
			hex_color = '$hex_color',
			subject = '$subject',
			comment = '$comment',
			start_time = '$start_time',
			end_time = '$end_time',
			showtimeframe = '$showtimeframe',
			location = '$location',
			street = '$street',
			number = '$number',
			postal = '$postal',
			city = '$city',
			invite = '$invite',
			weather = '$weather',
			ForAll = '$ForAll',
			ForSome = '$CopyToSubcategory',
			ForKids = '$SomeStudentKid',
			ForAdults = '$SomeStudentAdult',
			ForManual = '$SomeStudentManual'
			WHERE " . $superuser . "_id = $superuser_id AND
			id='$block_id'");
			  
	}
	
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "UpdateBlock", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

		
}

//ADD NEW LABEL (IN MEMBERS_LIST)
	if(isset ($action) && $action == "NewLabel"){
	  
	$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	
	$title=mres($_POST['title']);  
	$icon=mres($_POST['icon']);
	$ForAll = mres($_POST['ForAll']);
	  
	if(isset($ForAll) && $ForAll == "2"){
	if(isset($_POST['CopyToSubcategory'])){
	$CopyToSubcategory = implode(',', $_POST['CopyToSubcategory']);
	}
	}
	else {
	$CopyToSubcategory = "";
	}

	mysqli_query($con,"INSERT INTO " . $superuser . "s_labels
	(" . $superuser . "_id, subcategory_id, title, icon, ForAll, ForSome)
	VALUES
	('$superuser_id', '$subcategory_id', '$title', '$icon', '$ForAll', '$CopyToSubcategory')");	
	
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "NewLabel", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//DELETE LABEL
	if(isset ($action) && $action == "DeleteLabel"){
	
	$label_id=mres($_POST['label_id']);
	
	//delete from table default_blocks
	mysqli_query($con,"DELETE FROM " . $superuser . "s_labels WHERE " . $superuser . "_id = $superuser_id AND id = $label_id");
	mysqli_query($con,"DELETE FROM labels WHERE " . $superuser . "_id = $superuser_id AND label_id = $label_id");
	
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "DeleteLabel", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//ADD CUSTOM CALENDAR BLOCK
	if(isset ($action) && $action == "NewBlock"){
	  
	$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	
	$title=mres($_POST['title']);  
	$subject=mres($_POST['subject']);
	
	require_once 'plugins/HTMLPurifier/HTMLPurifier.auto.php';
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	$comment = $_POST["comment"]; //mres niet nodig
	$comment = $purifier->purify($comment);
	$comment = mres($comment);
		
	$start_time = mres($_POST['start_time']);
	$end_time = mres($_POST['end_time']);
	$showtimeframe = mres($_POST['showtimeframe']);
	$location=mres($_POST['location']);
	$street = mres($_POST['street']);
	$number = mres($_POST['number']);
	$postal = mres($_POST['postal']);
	$city = mres($_POST['city']);
	$ForAll = mres($_POST['ForAll']);
	  
	if(isset($_POST['hex_color'])){
	$hex_color = mres($_POST['hex_color']);
	}
	else {
		$hex_color = "";
	}

	if(isset($ForAll) && $ForAll == "2"){
	if(isset($_POST['CopyToSubcategory'])){
	$CopyToSubcategory = implode(',', $_POST['CopyToSubcategory']);
	}
	}
	else {
	$CopyToSubcategory = "";
	}
	
	if(isset($ForAll) && $ForAll == "3"){
	if(isset($_POST['SomeStudentKid'])){
	$SomeStudentKid = implode(',', $_POST['SomeStudentKid']);
	}
	else {
		$SomeStudentKid = "";
	}
	if(isset($_POST['SomeStudentAdult'])){
	$SomeStudentAdult = implode(',', $_POST['SomeStudentAdult']);
	}
	else {
		$SomeStudentAdult = "";
	}
	if(isset($_POST['SomeStudentManual'])){
	$SomeStudentManual = implode(',', $_POST['SomeStudentManual']);
	}
	else {
		$SomeStudentManual = "";
	}
	}
	else {
	$SomeStudentKid = "";
	$SomeStudentAdult = "";
	$SomeStudentManual = "";
	}  
	
	//check for weather
    $weather = 0;
    if(isset($_POST['weather'])){
	    $weather = 1;
	}
	
	//check for weather
    $invite = 0;
    if(isset($_POST['Invite'])){
	    $invite = 1;
	}

	
	mysqli_query($con,"INSERT INTO " . $superuser . "s_default_blocks
	(" . $superuser . "_id, subcategory_id, title, hex_color, subject, comment, start_time, end_time, showtimeframe, location, street, number, postal, city, invite, weather, ForAll, ForSome, ForKids, ForAdults, ForManual)
	VALUES
	('$superuser_id', '$subcategory_id', '$title', '$hex_color', '$subject', '$comment', '$start_time', '$end_time', '$showtimeframe', '$location', '$street', '$number', '$postal', '$city', '$invite', '$weather', '$ForAll', '$CopyToSubcategory', '$SomeStudentKid', '$SomeStudentAdult', '$SomeStudentManual')");
	
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "NewBlock", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
		
}

//DELETE CUSTOM CALENDAR BLOCK
	if(isset ($action) && $action == "DeleteBlock"){
	
	$block_id=mres($_POST['block_id']);
	$title = "user" . $block_id;
	
	//check if block is used in calendar
	$select_query = mysqli_query($con, "SELECT id FROM calendar WHERE title = '$title'");
	if(mysqli_num_rows($select_query) > 0){
		mysqli_query($con, "UPDATE calendar SET title = '". strtoupper($superuser) . "1' WHERE title = '$title' AND " . $superuser . "_id = $superuser_id");
	}
		
	//delete from table default_blocks
	mysqli_query($con,"DELETE FROM " . $superuser . "s_default_blocks WHERE " . $superuser . "_id = $superuser_id AND id = $block_id");
	
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "DeleteBlock", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	
}


//ADD A CONTACT PERSON TO CONTACT LIST SUBCATEGORY - FROM USER DATABASE
if(isset ($action) && $action == "AddContactSuper"){
  
  $subcategory_id = mres($_POST['subcategory_id']);
  
  $subs_list = explode(',', $subcategory_id);
		  foreach($subs_list AS $sub){ 

  $subcategory_id=sres($sub, $superuser_id, $superuser);
  
  $parent_id = 0;
  $manual_id = 0;
  
  if(isset($_POST['parent_id'])){
	  $parent_id = mres($_POST['parent_id']);
  }
  if(isset($_POST['manual_id'])){
	  $manual_id = mres($_POST['manual_id']);
  }
  
  if($parent_id <> 0){ 
  $cat_contact_name = userValue($parent_id, "username");
  $cat_contact_email = userValue($parent_id, "email");
  $cat_contact_phone = userValue($parent_id, "Telefoonnummer");
  }
  if($manual_id <> 0){
  $cat_contact_name = userValueManual($manual_id, $superuser, $superuser_id, "name") . " " . userValueManual($manual_id, $superuser, $superuser_id, "surname");
  $cat_contact_email = userValueManual($manual_id, $superuser, $superuser_id, "email");
  $cat_contact_phone = userValueManual($manual_id, $superuser, $superuser_id, "Telefoonnummer");  
  } 
  
  //check if exists:
  if($parent_id <> 0){
  $check = mysqli_query($con, "SELECT * FROM " . $superuser . "s_share WHERE " . $superuser . "_id = $superuser_id AND user_id = $parent_id AND subcategory_id = $subcategory_id");
  }
  if($manual_id <> 0){ 
  $check = mysqli_query($con, "SELECT * FROM " . $superuser . "s_contact WHERE AES_DECRYPT(cat_contact_email_x, SHA1('$pass')) = '$cat_contact_email' AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id");
  }
  
  if(mysqli_num_rows($check) == 0){
  $insert = mysqli_query($con,"INSERT INTO " . $superuser . "s_contact 
  (" . $superuser . "_id,
  subcategory_id,
  cat_contact_name_x,
  cat_contact_email_x, 
  cat_contact_phone_x,head) VALUES 
  ('$superuser_id','$subcategory_id',AES_ENCRYPT('$cat_contact_name', SHA1('$pass')),AES_ENCRYPT('$cat_contact_email', SHA1('$pass')),AES_ENCRYPT('$cat_contact_phone', SHA1('$pass')),'0')");
  
  $last_id = mysqli_insert_id($con);
  
  //if user on app (not manually added), automatically link person to superuser subcategory
  if($parent_id <> 0){
  $superuser_user_id = $id;
  $user_id=$parent_id;
  $superuser_contact_id=$last_id;

  mysqli_query($con,"INSERT INTO " . $superuser . "s_share (" . $superuser . "_user_id,user_id," . $superuser . "_id," . $superuser . "s_contact_id,subcategory_id) VALUES ($id, $user_id,$superuser_id,$superuser_contact_id,$subcategory_id)"); 
  }
  } 
  }
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "AddContactSuper", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
   
}


//ADD MANUAL CONTACT PERSON SUBCATEGORY
if(isset ($action) && $action == "insert_contact"){
  
  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  
  if(isset($_POST['head'])){
  $head=1;
  
  //if second person is called 'head', remove 'head' status from another already inserted
  $check = mysqli_query($con,"SELECT id FROM " . $superuser . "s_contact WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND head = 1");
  $numrows_check = mysqli_num_rows($check);
  if($numrows_check > 0){
	  $check = mysqli_query($con,"UPDATE " . $superuser . "s_contact SET head = 0 WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND head = 1");
  }
  }
  else {
	 $head = 0; 
  }

  $cat_contact_name=mres($_POST['cat_contact_name']);
  $cat_contact_email=mres($_POST['cat_contact_email']);
  $cat_contact_phone=mres($_POST['cat_contact_phone']);
    
  
  $insert = mysqli_query($con,"INSERT INTO " . $superuser . "s_contact 
  (" . $superuser . "_id,
  subcategory_id,
  cat_contact_name_x,
  cat_contact_email_x, 
  cat_contact_phone_x,head) VALUES 
  ('$superuser_id','$subcategory_id',AES_ENCRYPT('$cat_contact_name', SHA1('$pass')),AES_ENCRYPT('$cat_contact_email', SHA1('$pass')),AES_ENCRYPT('$cat_contact_phone', SHA1('$pass')),'$head')");
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "insert_contact", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
    
}


//UPDATE CONTACT PERSON SUBCATEGORY
if(isset ($action) && $action == "update_contact"){
  
  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  $contact_id=mres($_POST['contact_id']);
  
  if(isset($_POST['head'])){
  $head=1;
  
  //if second person is called 'head', remove 'head' status from another already inserted
  $check = mysqli_query($con,"SELECT id FROM " . $superuser . "s_contact WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND head = 1");
  $numrows_check = mysqli_num_rows($check);
  if($numrows_check > 0){
	  $check = mysqli_query($con,"UPDATE " . $superuser . "s_contact SET head = 0 WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND head = 1");
  }
  }
  else {
	 $head = 0; 
  }

  $cat_contact_name=mres($_POST['cat_contact_name']);
  $cat_contact_email=mres($_POST['cat_contact_email']);
  $cat_contact_phone=mres($_POST['cat_contact_phone']);
    
  
  $insert = mysqli_query($con,"UPDATE " . $superuser . "s_contact 
  SET 
  cat_contact_name_x = AES_ENCRYPT('$cat_contact_name', SHA1('$pass')),
  cat_contact_email_x = AES_ENCRYPT('$cat_contact_email', SHA1('$pass')),
  cat_contact_phone_x = AES_ENCRYPT('$cat_contact_phone', SHA1('$pass')),
  head = '$head'
  WHERE " . $superuser . "_id = $superuser_id AND
  id = $contact_id");
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "update_contact", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
    
}


//REMOVE CONTACT PERSON FROM SUBCATEGORY
if(isset ($action) && $action == "remove_contact"){

  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  $id=mres($_POST['id']);

  mysqli_query($con,"DELETE FROM " . $superuser . "s_contact WHERE id = $id AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id");
  mysqli_query($con,"DELETE FROM " . $superuser . "s_share WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND " . $superuser . "s_contact_id = $id");  
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "remove_contact", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	 
}

//LINK AN MANUEL ADDED CONTACT PERSON TO HIS/HERS PERSONAL APP ACCOUNT
if(isset ($action) && $action == "link_contact"){

  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  $superuser_user_id=mres($_POST['superuser_user_id']);
  $user_id=mres($_POST['user_id']);
  $superuser_contact_id=mres($_POST['superuser_contact_id']);

  mysqli_query($con,"INSERT INTO " . $superuser . "s_share (" . $superuser . "_user_id,user_id," . $superuser . "_id," . $superuser . "s_contact_id,subcategory_id) VALUES ($superuser_user_id, $user_id,$superuser_id,$superuser_contact_id,$subcategory_id)");   
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "link_contact", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	 
}

//UNLINK A CONTACT PERSON WITH HIS/HERS PERSONAL APP ACCOUNT
if(isset ($action) && $action == "unlink_contact"){

  $subcategory_id=mres($_POST['subcategory_id']);
  $superuser_contact_id=mres($_POST['superuser_contact_id']);

  mysqli_query($con,"DELETE FROM " . $superuser . "s_share WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND " . $superuser . "s_contact_id = $superuser_contact_id");   
	 
	 LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "unlink_contact", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}


//MAKE CONTACT PERSON HEAD CONTACT PERSON
if(isset ($action) && $action == "contact_head"){

  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  $id = mres($_POST['id']);
  
  //if second person is called 'head', remove 'head' status from another already inserted
  $check = mysqli_query($con,"SELECT * FROM " . $superuser . "s_contact WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND head = 1");
  $numrows_check = mysqli_num_rows($check);
  if($numrows_check > 0){
	  $check = mysqli_query($con,"UPDATE " . $superuser . "s_contact SET head = 0 WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND head = 1");
  }
  
   mysqli_query($con,"UPDATE " . $superuser . "s_contact SET head = 1 WHERE " . $superuser . "_id = $superuser_id AND id=$id AND subcategory_id = $subcategory_id");
   
   LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "contact_head", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));  	 
}


//REMOVE A USER FROM YOUR SUBCATEGORY OR CLUB
if(isset ($action) && $action == "RemoveUser"){

  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  
  $kid_id = 0;
  $parent_id = 0;
  $manual_id = 0;
  
  if(isset($_POST['kid_id'])){
	  $kid_id = mres($_POST['kid_id']);
  }
  if(isset($_POST['parent_id'])){
	  $parent_id = mres($_POST['parent_id']);
  }
  if(isset($_POST['manual_id'])){
	  $manual_id = mres($_POST['manual_id']);
  }
  
  $type=mres($_POST['type']);
  
  if($type == "RemoveFromSubcategoryMono"){  
  if($kid_id <> 0){ 
  mysqli_query($con,"UPDATE kids_" . $superuser . "s SET subcategory = 0 WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id");
  mysqli_query($con,"DELETE FROM labels WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id");    
  }
  if($parent_id <> 0){  
  mysqli_query($con,"UPDATE parents_" . $superuser . "s SET subcategory = 0 WHERE parent_id = $parent_id AND " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id");
  mysqli_query($con,"DELETE FROM labels WHERE adult_id = $parent_id AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id");    
  }
  if($manual_id <> 0){ 
  mysqli_query($con,"DELETE FROM kids_" . $superuser . "s_manual WHERE id = $manual_id AND " . $superuser . "_id = $superuser_id");
  mysqli_query($con,"DELETE FROM labels WHERE manual_id = $manual_id AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id"); 
  }  
  }
  
  if($type == "RemoveFromOneSubcategoryMulti"){  
  if($kid_id <> 0){ 
  mysqli_query($con,"DELETE FROM kids_" . $superuser . "s WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id");
  mysqli_query($con,"DELETE FROM labels WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id");
  }
  if($parent_id <> 0){ 
  mysqli_query($con,"DELETE FROM parents_" . $superuser . "s WHERE parent_id = $parent_id AND " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id");
  mysqli_query($con,"DELETE FROM labels WHERE adult_id = $parent_id AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id");   
  }
  if($manual_id <> 0){ 
  mysqli_query($con,"DELETE FROM kids_" . $superuser . "s_manual WHERE id = $manual_id AND " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id");
  mysqli_query($con,"DELETE FROM labels WHERE manual_id = $manual_id AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id");  
  }  
  }
  
  if($type == "RemoveTotal"){  
  if($kid_id <> 0){ 
  mysqli_query($con,"DELETE FROM kids_" . $superuser . "s WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id");
  mysqli_query($con,"DELETE FROM labels WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id");   
  }
  if($parent_id <> 0){  
  mysqli_query($con,"DELETE FROM parents_" . $superuser . "s WHERE parent_id = $parent_id AND " . $superuser . "_id = $superuser_id"); 
  mysqli_query($con,"DELETE FROM labels WHERE adult_id = $parent_id AND " . $superuser . "_id = $superuser_id"); 
  }
  if($manual_id <> 0){ 
  $key_code = userValueManual($manual_id, $superuser, $superuser_id, "key_code");
  mysqli_query($con,"DELETE FROM address WHERE key_code_" . $superuser . " = $key_code"); 
  
  //get all ids with this key_code
  $sql = mysqli_query($con, "SELECT id FROM kids_" . $superuser . "s_manual WHERE key_code = '$key_code' AND " . $superuser . "_id = $superuser_id");
  foreach($sql AS $row){
	  mysqli_query($con,"DELETE FROM kids_" . $superuser . "s_manual WHERE id = $row[id] AND " . $superuser . "_id = $superuser_id");
	  mysqli_query($con,"DELETE FROM labels WHERE manual_id = $row[id] AND " . $superuser . "_id = $superuser_id"); 
  }
  }  
  }
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "RemoveUser", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
   
}


//LABEL A MEMBER IN MEMBERS_LIST
if(isset ($action) && $action == "label_member"){

  $label_id = mres($_POST['label_id']);
  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  
  if(isset($_POST['id_list_kids']) && !empty($_POST['id_list_kids'])){
	  $id_list_kids = mres($_POST['id_list_kids']);
	  
	if($label_id == 0){ 
	  mysqli_query($con,"UPDATE kids_" . $superuser . "s SET mark = '1' WHERE kid_id IN ($id_list_kids) AND subcategory = $subcategory_id AND " . $superuser . "_id = $superuser_id");
	}
	else {
	  $id_list_kids = explode(",", $id_list_kids);
	  foreach($id_list_kids as $checked_kid){
	  mysqli_query($con,"INSERT INTO labels (" . $superuser . "_id,subcategory_id,label_id,kid_id) 
	SELECT $superuser_id,$subcategory_id,$label_id,$checked_kid
	  FROM dual 
	  WHERE NOT EXISTS 
	    (SELECT * FROM labels WHERE kid_id=$checked_kid AND " . $superuser . "_id=$superuser_id AND subcategory_id = $subcategory_id AND label_id = $label_id)");
	  }   
	  }
  }
  if(isset($_POST['id_list_parents']) && !empty($_POST['id_list_parents'])){
	  $id_list_parents = mres($_POST['id_list_parents']);
	  if($label_id == 0){ 
	   mysqli_query($con,"UPDATE parents_" . $superuser . "s SET mark = '1' WHERE parent_id IN ($id_list_parents) AND subcategory = $subcategory_id AND " . $superuser . "_id = $superuser_id");
	}
	else {
	  $id_list_parents = explode(",", $id_list_parents);
	  foreach($id_list_parents as $checked_parent){
	  mysqli_query($con,"INSERT INTO labels(" . $superuser . "_id,subcategory_id,label_id,adult_id) 
	SELECT $superuser_id,$subcategory_id,$label_id,$checked_parent
	  FROM dual 
	  WHERE NOT EXISTS 
	    (SELECT * FROM labels WHERE adult_id=$checked_parent AND " . $superuser . "_id=$superuser_id AND subcategory_id = $subcategory_id AND label_id = $label_id)");
	  }   
	  }

  }
  if(isset($_POST['id_list_manual']) && !empty($_POST['id_list_manual'])){
	  $id_list_manual = mres($_POST['id_list_manual']);
	  if($label_id == 0){ 
	   mysqli_query($con,"UPDATE kids_" . $superuser . "s_manual SET mark = '1' WHERE id IN ($id_list_manual) AND subcategory = $subcategory_id AND " . $superuser . "_id = $superuser_id");
	}
	else {
	  $id_list_manual = explode(",", $id_list_manual);
	  foreach($id_list_manual as $checked_manual){
	  mysqli_query($con,"INSERT INTO labels(" . $superuser . "_id,subcategory_id,label_id,manual_id) 
	SELECT $superuser_id,$subcategory_id,$label_id,$checked_manual
	  FROM dual 
	  WHERE NOT EXISTS 
	    (SELECT * FROM labels WHERE manual_id=$checked_manual AND " . $superuser . "_id=$superuser_id AND subcategory_id = $subcategory_id AND label_id = $label_id)");
	  }   
	  }  
  }
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "label_member", __LINE__, print_r($_POST, true), mres(mysqli_error($con))); 	 
}

//UNLABEL A MEMBER IN MEMBERS_LIST
if(isset ($action) && $action == "unlabel_member"){

  $label_id = mres($_POST['label_id']);
  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  
  if(isset($_POST['id_list_kids']) && !empty($_POST['id_list_kids'])){
	  $id_list_kids = mres($_POST['id_list_kids']);
	  
	if($label_id == 0){ 
	  mysqli_query($con,"UPDATE kids_" . $superuser . "s SET mark = '0' WHERE kid_id IN ($id_list_kids) AND subcategory = $subcategory_id AND " . $superuser . "_id = $superuser_id");
	}
	else {
	  $id_list_kids = explode(",", $id_list_kids);
	  foreach($id_list_kids as $checked_kid){
	  mysqli_query($con,"DELETE FROM labels WHERE kid_id = $checked_kid AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND label_id = $label_id");
	  }   
	  }
  }
  if(isset($_POST['id_list_parents']) && !empty($_POST['id_list_parents'])){
	  $id_list_parents = mres($_POST['id_list_parents']);
	  if($label_id == 0){ 
	   mysqli_query($con,"UPDATE parents_" . $superuser . "s SET mark = '0' WHERE parent_id IN ($id_list_parents) AND subcategory = $subcategory_id AND " . $superuser . "_id = $superuser_id");
	}
	else {
	  $id_list_parents = explode(",", $id_list_parents);
	  foreach($id_list_parents as $checked_parent){
	   mysqli_query($con,"DELETE FROM labels WHERE adult_id = $checked_parent AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND label_id = $label_id");
	  }   
	  }

  }
  if(isset($_POST['id_list_manual']) && !empty($_POST['id_list_manual'])){
	  $id_list_manual = mres($_POST['id_list_manual']);
	  if($label_id == 0){ 
	   mysqli_query($con,"UPDATE kids_" . $superuser . "s_manual SET mark = '0' WHERE id IN ($id_list_manual) AND subcategory = $subcategory_id AND " . $superuser . "_id = $superuser_id");
	}
	else {
	  $id_list_manual = explode(",", $id_list_manual);
	  foreach($id_list_manual as $checked_manual){
	   mysqli_query($con,"DELETE FROM labels WHERE manual_id = $checked_manual AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND label_id = $label_id");	  
	   }   
	  }	  
  }
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "unlabel_member", __LINE__, print_r($_POST, true), mres(mysqli_error($con))); 	 
}

//SETTINGS ABSENCES
if(isset ($action) && $action == "AbsencesSettings"){
	
  $event_id=mres($_POST['event_id']);
  $comment_limit_reason = "";
  $comment_limit_reached = "";
  $comment_block = "";
  $max_limit = 0;
  $block_active = 0;
  $limit_inactive = mres($_POST['limit_inactive']);

  if(isset($_POST['comment_limit_reason']) && !empty($_POST['comment_limit_reason'])){
	   $comment_limit_reason = mres($_POST['comment_limit_reason']);
  }
  if(isset($_POST['comment_limit_reached']) && !empty($_POST['comment_limit_reached'])){
	   $comment_limit_reached = mres($_POST['comment_limit_reached']);
  }
  if(isset($_POST['comment_block']) && !empty($_POST['comment_block'])){
	   $comment_block = mres($_POST['comment_block']);
  }
  if(isset($_POST['max_limit']) && !empty($_POST['max_limit'])){
	   $max_limit = mres($_POST['max_limit']);
  }
  if(isset($_POST['block_active']) && !empty($_POST['block_active'])){
	   $block_active = mres($_POST['block_active']);
  }
  

	   if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP_settings WHERE event_id = $event_id AND " . $superuser . "_id = $superuser_id")) == 0){ 
	   mysqli_query($con,"INSERT INTO RSVP_settings (event_id," . $superuser . "_id,max_limit,comment_limit_reason,comment_limit_reached,comment_block,inactive,block) VALUES ($event_id, $superuser_id, $max_limit, '$comment_limit_reason', '$comment_limit_reached','$comment_block',$limit_inactive,$block_active)");
	   }
	   else {
	   mysqli_query($con,"UPDATE RSVP_settings SET 
	   max_limit = $max_limit,
	   comment_limit_reason = '$comment_limit_reason',
	   comment_limit_reached = '$comment_limit_reached',
	   comment_block = '$comment_block',
	   inactive = $limit_inactive,
	   block = $block_active
	   WHERE event_id = $event_id AND " . $superuser . "_id = $superuser_id");  
	   }
  
  LogAction($event_id, $superuser, $superuser_id, 0, 0, 0, "AbsencesSettings", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}


//TAKE ABSENCES
if(isset ($action) && $action == "TakeAbsences"){

  $event_id=mres($_POST['event_id']);
  $now = date("Y-m-d H:i:s");
  
  if(isset($_POST['comment']) && !empty($_POST['comment'])){
	   $comment = mres($_POST['comment']);
	   mysqli_query($con,"DELETE FROM RSVP_comments WHERE event_id = $event_id AND " . $superuser . "_id = $superuser_id");
	   mysqli_query($con,"INSERT INTO RSVP_comments (date,event_id," . $superuser . "_id,comment) VALUES ('$now',$event_id, $superuser_id, '$comment')");
	  }
  if(isset($_POST['comment']) && empty($_POST['comment'])){
	  mysqli_query($con,"DELETE FROM RSVP_comments WHERE event_id = $event_id AND " . $superuser . "_id = $superuser_id");
	 }
  
  if(isset($_POST['id_list_kids_go']) && !empty($_POST['id_list_kids_go'])){
	  $id_list_kids_go = mres($_POST['id_list_kids_go']);
	  $id_list_kids_go = explode(",", $id_list_kids_go);
	  foreach($id_list_kids_go as $checked_kids_go){
      if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP WHERE kid_id = $checked_kids_go AND event_id = $event_id")) > 0){
	      mysqli_query($con,"UPDATE RSVP SET NoGo = 0 WHERE kid_id = $checked_kids_go AND event_id = $event_id"); 
      }
      else { 
	  mysqli_query($con,"INSERT INTO RSVP (kid_id,event_id," . $superuser . "_id,NoGo) VALUES ($checked_kids_go, $event_id, $superuser_id,'0')");
	  }
	  }
  }
  if(isset($_POST['id_list_kids_nogo']) && !empty($_POST['id_list_kids_nogo'])){
	  $id_list_kids_nogo = mres($_POST['id_list_kids_nogo']);
	  $id_list_kids_nogo = explode(",", $id_list_kids_nogo);
	  foreach($id_list_kids_nogo as $checked_kids_nogo){
      if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP WHERE kid_id = $checked_kids_nogo AND event_id = $event_id")) > 0){
	      mysqli_query($con,"UPDATE RSVP SET NoGo = 1, verified = 0 WHERE kid_id = $checked_kids_nogo AND event_id = $event_id"); 
      }
      else {
	  mysqli_query($con,"INSERT INTO RSVP (kid_id,event_id," . $superuser . "_id,NoGo) VALUES ($checked_kids_nogo, $event_id, $superuser_id,'1')");
	  }
	  }
  }
  
  if(isset($_POST['id_list_kids_nogo_reason']) && !empty($_POST['id_list_kids_nogo_reason'])){
	  $id_list_kids_nogo_reason = mres($_POST['id_list_kids_nogo_reason']);
	  $id_list_kids_nogo_reason = explode(",", $id_list_kids_nogo_reason);
	  foreach($id_list_kids_nogo_reason as $checked_kids_nogo_reason){
	  if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP WHERE kid_id = $checked_kids_nogo_reason AND event_id = $event_id")) > 0){
	      mysqli_query($con,"UPDATE RSVP SET NoGo = 2, verified = 0 WHERE kid_id = $checked_kids_nogo_reason AND event_id = $event_id"); 
      }
      else {
	  mysqli_query($con,"INSERT INTO RSVP (kid_id,event_id," . $superuser . "_id,NoGo) VALUES ($checked_kids_nogo_reason, $event_id, $superuser_id,'2')");
	  }
	  }
  }
  
  if(isset($_POST['id_list_parents_go']) && !empty($_POST['id_list_parents_go'])){
	  $id_list_parents_go = mres($_POST['id_list_parents_go']);
	  $id_list_parents_go = explode(",", $id_list_parents_go);
	  foreach($id_list_parents_go as $checked_parents_go){
	  if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP WHERE parent_id = $checked_parents_go AND event_id = $event_id")) > 0){
	      mysqli_query($con,"UPDATE RSVP SET NoGo = 0 WHERE parent_id = $checked_parents_go AND event_id = $event_id"); 
      }
      else {
	  mysqli_query($con,"INSERT INTO RSVP (parent_id,event_id," . $superuser . "_id,NoGo) VALUES ($checked_parents_go, $event_id, $superuser_id,'0')");
	  }
	  }
  }
  if(isset($_POST['id_list_parents_nogo']) && !empty($_POST['id_list_parents_nogo'])){
	  $id_list_parents_nogo = mres($_POST['id_list_parents_nogo']);
	  $id_list_parents_nogo = explode(",", $id_list_parents_nogo);
	  foreach($id_list_parents_nogo as $checked_parents_nogo){
	  if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP WHERE parent_id = $checked_parents_nogo AND event_id = $event_id")) > 0){
	      mysqli_query($con,"UPDATE RSVP SET NoGo = 1, verified = 0 WHERE parent_id = $checked_parents_nogo AND event_id = $event_id"); 
      }
      else {
	  mysqli_query($con,"INSERT INTO RSVP (parent_id,event_id," . $superuser . "_id,NoGo) VALUES ($checked_parents_nogo, $event_id, $superuser_id,'1')");
	  }
	  }
  }
  if(isset($_POST['id_list_parents_nogo_reason']) && !empty($_POST['id_list_parents_nogo_reason'])){
	  $id_list_parents_nogo_reason = mres($_POST['id_list_parents_nogo_reason']);
	  $id_list_parents_nogo_reason = explode(",", $id_list_parents_nogo_reason);
	  foreach($id_list_parents_nogo_reason as $checked_parents_nogo_reason){
	  if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP WHERE parent_id = $checked_parents_nogo_reason AND event_id = $event_id")) > 0){
	      mysqli_query($con,"UPDATE RSVP SET NoGo = 2, verified = 0 WHERE parent_id = $checked_parents_nogo_reason AND event_id = $event_id"); 
      }
      else {
	  mysqli_query($con,"INSERT INTO RSVP (parent_id,event_id," . $superuser . "_id,NoGo) VALUES ($checked_parents_nogo_reason, $event_id, $superuser_id,'2')");
	  }
	  }
  }

  if(isset($_POST['id_list_manual_go']) && !empty($_POST['id_list_manual_go'])){
	  $id_list_manual_go = mres($_POST['id_list_manual_go']);
	  $id_list_manual_go = explode(",", $id_list_manual_go);
	  foreach($id_list_manual_go as $checked_manual_go){
	  if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP WHERE manual_kid_id = $checked_manual_go AND event_id = $event_id")) > 0){
	      mysqli_query($con,"UPDATE RSVP SET NoGo = 0 WHERE manual_kid_id = $checked_manual_go AND event_id = $event_id"); 
      }
      else {
	  mysqli_query($con,"INSERT INTO RSVP (manual_kid_id,event_id," . $superuser . "_id,NoGo) VALUES ($checked_manual_go, $event_id, $superuser_id,'0')");
	  }
	  }
  }
  if(isset($_POST['id_list_manual_nogo']) && !empty($_POST['id_list_manual_nogo'])){
	  $id_list_manual_nogo = mres($_POST['id_list_manual_nogo']);
	  $id_list_manual_nogo = explode(",", $id_list_manual_nogo);
	  foreach($id_list_manual_nogo as $checked_manual_nogo){
	  if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP WHERE manual_kid_id = $checked_manual_nogo AND event_id = $event_id")) > 0){
	      mysqli_query($con,"UPDATE RSVP SET NoGo = 1, verified = 0 WHERE manual_kid_id = $checked_manual_nogo AND event_id = $event_id"); 
      }
      else {
	  mysqli_query($con,"INSERT INTO RSVP (manual_kid_id,event_id," . $superuser . "_id,NoGo) VALUES ($checked_manual_nogo, $event_id, $superuser_id,'1')");
	  }
	  }
  }	
  if(isset($_POST['id_list_manual_nogo_reason']) && !empty($_POST['id_list_manual_nogo_reason'])){
	  $id_list_manual_nogo_reason = mres($_POST['id_list_manual_nogo_reason']);
	  $id_list_manual_nogo_reason = explode(",", $id_list_manual_nogo_reason);
	  foreach($id_list_manual_nogo_reason as $checked_manual_nogo_reason){
	  if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM RSVP WHERE manual_kid_id = $checked_manual_nogo_reason AND event_id = $event_id")) > 0){
	      mysqli_query($con,"UPDATE RSVP SET NoGo = 2, verified = 0 WHERE manual_kid_id = $checked_manual_nogo_reason AND event_id = $event_id"); 
      }
      else {
	  mysqli_query($con,"INSERT INTO RSVP (manual_kid_id,event_id," . $superuser . "_id,NoGo) VALUES ($checked_manual_nogo_reason, $event_id, $superuser_id,'2')");
	  }
	  }
  }
  LogAction($event_id, $superuser, $superuser_id, 0, 0, 0, "TakeAbsences", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));	 
}



//MOVE OR COPY MEMBER(S) TO ANOTHER SUBCATEGORY
if(isset ($action) && $action == "MoveCopyMembers"){

  $new_subcategory_id=mres($_POST['new_subcategory_id']);
  $old_subcategory_id=mres($_POST['old_subcategory_id']);
  $type=mres($_POST['type']);
  
  if(isset($_POST['sendmail'])) {
  $sendmail=mres($_POST['sendmail']);
  }
  else {
	  $sendmail = 0;
  }
  
  //put new members in multiple subcategories
  if($type == "newmembers"){

	  if(isset($_POST['id_list_kids']) && !empty($_POST['id_list_kids'])){
	  $id_list_kids = mres($_POST['id_list_kids']);
	  $list_kids = explode(',', $id_list_kids);
	  foreach($list_kids AS $kid_id){
		  //first delete the first entry
		  mysqli_query($con,"DELETE FROM kids_" . $superuser . "s WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id AND subcategory = 0");
		  
		  $subs_list = explode(',', $new_subcategory_id);
		  foreach($subs_list AS $sub){ 
		  //then insert
		  mysqli_query($con,"INSERT INTO kids_" . $superuser . "s (subcategory, kid_id, " . $superuser . "_id) VALUES ($sub, $kid_id, $superuser_id)");
		  LogEntrySuper("0", $superuser_id, $superuser, $kid_id, "0", $sub, "0", "8");
		  } 
	  }
	  
	  
	  if($sendmail == "1"){
	  if(userValueSuper($superuser_id,$superuser,"testmode") == 0){
		  $list_kids = explode(',', $id_list_kids);
		  foreach($list_kids AS $kid_id){ 
		  //check if parent has joined accounts
		  $parent_id = userValueKid($kid_id, "parent");
		  $result = mysqli_query($con,"SELECT id, username, email FROM users WHERE id IN(SELECT slave_id FROM users_share WHERE master_id = $parent_id AND confirmed = '1') OR id = $parent_id");
		  
		  //first send notification 
			$payload = create_payload_json($superuser_name . " heeft " . userValueKid($kid_id, "name") . " ingedeeld op " . $appname . ".", "index.php?goto_kid=$kid_id");
			  $fetchtokens = mysqli_query($con,"SELECT device, device_token FROM users WHERE id IN(SELECT slave_id FROM users_share WHERE master_id = $parent_id AND confirmed = '1') AND device <> 0 AND device_token <> '' AND device_token <> 'null' OR id = $parent_id AND device <> 0 AND device_token <> '' AND device_token <> 'null'");
				  if(mysqli_num_rows($fetchtokens) > 0){
				  foreach($fetchtokens AS $row){
				  $user_mobile_info = ['device'=>$row['device'], 'token'=>$row['device_token']];
				  send_mobile_notification_request($user_mobile_info, $payload);
				  }
				  }
		  
		  
		  
		  //send mail to parents to tell them 'da good news'  
		  $subject = "Bericht van " . $appname . ": " . $superuser_name . " heeft " . userValueKid($kid_id, "name") . " ingedeeld";
		  $message_title = "" . userValueKid($kid_id, "name") . " is ingedeeld op " . $appname . ".";
		  $message = "Beste ouder,<br><br>
		  " . $superuser_name . " gebruikt de " . $appname . "-app en dat is goed nieuws! Al hun activiteiten zullen automatisch zichtbaar worden in de " . $appname . "-kalender van " . userValueKid($kid_id, "name") . ". Je kan  via de app met je vereniging communiceren.  Contactgegevens, handige documenten, leuke foto's, ... kan je er eenvoudig terugvinden.<br><br>
		  Veel plezier!<br>
		  Team " . $appname;
		  
		  $link = "<a href=\"" . $full_path . "/openlink.php?goto_kid=$kid_id\" class=\"link-button\" target=\"_blank\">Open " . $appname . "</a>";


			$message_body = file_get_contents('mail_templates/' . $mailtemplate_superuser);
			
			$message_body = str_replace('%message_title%', $message_title, $message_body); 
			$message_body = str_replace('%message%', $message, $message_body);
			$message_body = str_replace('%tracker%', $tracker, $message_body);
			$message_body = str_replace('%plus%', $plus, $message_body);
			$message_body = str_replace('%disclaimer%', $disclaimer, $message_body);
			$message_body = str_replace('%advertising%', $advertising, $message_body);
			$message_body = str_replace('%superusername%', $superuser_name, $message_body);
			$message_body = str_replace('%link%', $link, $message_body);
			$message_body = str_replace('%link2%', $link2, $message_body);
			$message_body = str_replace('%link3%', $link3, $message_body);
			$message_body = str_replace('%reply%', $reply_no, $message_body);
			$message_body = str_replace('%full_path%', $full_path, $message_body);		  
			$message_body = str_replace('%appname%', $appname, $message_body);	
			
			//check if premium level (personalize communication)
			if(checkPremiumLevel($superuser_id, $superuser, "") != "level 0"){
			$message_body = str_replace('%socialfacebook%', GetSocialWebMail($superuser_id,$superuser,"cat_facebook",$new_subcategory_id), $message_body);
			$message_body = str_replace('%socialwebsite%', GetSocialWebMail($superuser_id,$superuser,"cat_website",$new_subcategory_id), $message_body);
			if($superuser_avatar == ""){ 
			$message_body = str_replace('%avatar%', $logo_path, $message_body);
			}
			else {
			$message_body = str_replace('%avatar%', $data_path . "/uploads/" . $superuser_avatar, $message_body);
			}
			}
			//not premium
			else {
				$message_body = str_replace('%socialfacebook%', "", $message_body);
				$message_body = str_replace('%socialwebsite%', "", $message_body);
				$message_body = str_replace('%avatar%', $logo_path, $message_body);
			}
		  
		  
		  include("PHPmailer_include.php");
		   
			$mail->setFrom($mailfromaddress,$superuser_name);
			//Set an alternative reply-to address
			$mail->addReplyTo($mailfromaddress, $mailfromname);
			//Set who the message is to be sent to
			
			$mail->Subject = $subject;
			$mail->MsgHTML($message_body);
			
			foreach ($result as $row) {
			$mail->addAddress($row['email'], $row['username']);
			// If the mail is send, it returns true, else it will return false
			
			if(!$mail->send()) {
				echo "fout";
			} else {
				echo "gelukt";
			}
			$mail->clearAddresses();
			}
		
				  
			}	  
			}
			}
  }
  if(isset($_POST['id_list_parents']) && !empty($_POST['id_list_parents'])){
	  
	  $id_list_parents = mres($_POST['id_list_parents']);
	  $list_parents = explode(',', $id_list_parents);
	  foreach($list_parents AS $parent_id){
		  //first delete the first entry
		  mysqli_query($con,"DELETE FROM parents_" . $superuser . "s WHERE parent_id = $parent_id AND " . $superuser . "_id = $superuser_id AND subcategory = 0");
		  
		  $subs_list = explode(',', $new_subcategory_id);
		  foreach($subs_list AS $sub){ 
		  //then insert
		  mysqli_query($con,"INSERT INTO parents_" . $superuser . "s (subcategory, parent_id, " . $superuser . "_id) VALUES ($sub, $parent_id, $superuser_id)");
		  LogEntrySuper("0", $superuser_id, $superuser, "0", $parent_id, $sub, "0", "9");
		  } 
	  }


	  if($sendmail == "1"){
	  if(userValueSuper($superuser_id,$superuser,"testmode") == 0){
		  $parent_list = explode(',', $id_list_parents);
		  foreach($parent_list AS $parent_id){ 
		  
		   //first send notification 
	$payload = create_payload_json($superuser_name . " heeft jou ingedeeld op " . $appname . ".", "index.php?goto_parent=1");
	  $fetchtokens = mysqli_query($con,"SELECT device, device_token FROM users WHERE id = $parent_id AND device <> 0 AND device_token <> '' AND device_token <> 'null'");
		  if(mysqli_num_rows($fetchtokens) > 0){
		  foreach($fetchtokens AS $row){
		  $user_mobile_info = ['device'=>$row['device'], 'token'=>$row['device_token']];
		  send_mobile_notification_request($user_mobile_info, $payload);
		  }
		  }

	  	  
	
	  //send mail to parents to tell them 'da good news'  
	  $subject = "Bericht van " . $appname . ": " . $superuser_name . " heeft " . userValue($parent_id, "username") . " ingedeeld"; 
	  $to = userValue($parent_id, "email");
	  $message_title = "" . userValue($parent_id, "username") . " is ingedeeld op " . $appname . ".";
	  $message = "Hallo " . userValue($parent_id, "username") . ",<br><br>
	  " . $superuser_name . " gebruikt  de " . $appname . " app en dat is goed nieuws!  Al hun activiteiten zullen automatisch zichtbaar worden in je " . $appname . "-kalender.  Je kan via de app met je vereniging communiceren.  Contactgegevens, handige documenten, leuke foto's, ... kan je er eenvoudig terugvinden.<br><br>
	  Veel plezier!<br>
	  Team " . $appname;
	  $link = "<a href=\"" . $full_path . "/openlink.php?goto_parent=1\" class=\"link-button\" target=\"_blank\">Open " . $appname . "</a>";
	  
	  $message_body = file_get_contents('mail_templates/' . $mailtemplate_superuser);

	$message_body = str_replace('%message_title%', $message_title, $message_body); 
	$message_body = str_replace('%message%', $message, $message_body);
	$message_body = str_replace('%tracker%', $tracker, $message_body);
	$message_body = str_replace('%plus%', $plus, $message_body);
	$message_body = str_replace('%disclaimer%', $disclaimer, $message_body);
	$message_body = str_replace('%advertising%', $advertising, $message_body);
	$message_body = str_replace('%superusername%', $superuser_name, $message_body);
	$message_body = str_replace('%link%', $link, $message_body);
	$message_body = str_replace('%link2%', $link2, $message_body);
	$message_body = str_replace('%link3%', $link3, $message_body);
	$message_body = str_replace('%reply%', $reply_no, $message_body);
	$message_body = str_replace('%full_path%', $full_path, $message_body);		  
	$message_body = str_replace('%appname%', $appname, $message_body);	
	
	//check if premium level (personalize communication)
	if(checkPremiumLevel($superuser_id, $superuser, "") != "level 0"){
	$message_body = str_replace('%socialfacebook%', GetSocialWebMail($superuser_id,$superuser,"cat_facebook",$new_subcategory_id), $message_body);
	$message_body = str_replace('%socialwebsite%', GetSocialWebMail($superuser_id,$superuser,"cat_website",$new_subcategory_id), $message_body);
	if($superuser_avatar == ""){ 
	$message_body = str_replace('%avatar%', $logo_path, $message_body);
	}
	else {
	$message_body = str_replace('%avatar%', $data_path . "/uploads/" . $superuser_avatar, $message_body);
	}
	}
	//not premium
	else {
		$message_body = str_replace('%socialfacebook%', "", $message_body);
		$message_body = str_replace('%socialwebsite%', "", $message_body);
		$message_body = str_replace('%avatar%', $logo_path, $message_body);
	}

	userSendMail6($mailfromaddress, $to, $subject, $message_body); //Send Mail
	 }
  }
  }
  }  
  }
  
  //move existing members or put a new member in one category
  if($type == "movemembers"){ 
  
  if(isset($_POST['id_list_kids']) && !empty($_POST['id_list_kids'])){
	  $id_list_kids = mres($_POST['id_list_kids']);
	  //first delete if already exists in the other subgroup so there aren't duplicates (concept cut/paste)
	  mysqli_query($con,"DELETE FROM kids_" . $superuser . "s WHERE kid_id IN($id_list_kids) AND " . $superuser . "_id = $superuser_id AND subcategory = $new_subcategory_id");
	  
	  //then delete any existing labels put on users in the old subcategory
	  mysqli_query($con,"DELETE FROM labels WHERE kid_id IN($id_list_kids) AND " . $superuser . "_id = $superuser_id AND subcategory_id = $old_subcategory_id");
	  
	  //then update
	  mysqli_query($con,"UPDATE kids_" . $superuser . "s SET subcategory = $new_subcategory_id, mark= 0 WHERE kid_id IN($id_list_kids) AND " . $superuser . "_id = $superuser_id AND subcategory = $old_subcategory_id"); 
	  
	  
	  if($sendmail == "1"){
	  if(userValueSuper($superuser_id,$superuser,"testmode") == 0){
		  $list_kids = explode(',', $id_list_kids);
		  foreach($list_kids AS $kid_id){ 
		  //check if parent has joined accounts
		  $parent_id = userValueKid($kid_id, "parent");
		  $result = mysqli_query($con,"SELECT id, username, email FROM users WHERE id IN(SELECT slave_id FROM users_share WHERE master_id = $parent_id AND confirmed = '1') OR id = $parent_id");
		  
		  //first send notification 
			$payload = create_payload_json($superuser_name . " heeft " . userValueKid($kid_id, "name") . " ingedeeld in " . userValueSubcategory($new_subcategory_id, $superuser, "cat_name") . " op " . $appname . ".", "index.php?goto_kid=$kid_id");
			  $fetchtokens = mysqli_query($con,"SELECT device, device_token FROM users WHERE id IN(SELECT slave_id FROM users_share WHERE master_id = $parent_id AND confirmed = '1') AND device <> 0 AND device_token <> '' AND device_token <> 'null' OR id = $parent_id AND device <> 0 AND device_token <> '' AND device_token <> 'null'");
				  if(mysqli_num_rows($fetchtokens) > 0){
				  foreach($fetchtokens AS $row){
				  $user_mobile_info = ['device'=>$row['device'], 'token'=>$row['device_token']];
				  send_mobile_notification_request($user_mobile_info, $payload);
				  }
				  }
			
		  LogEntrySuper("0", $superuser_id, $superuser, $kid_id, "0", $new_subcategory_id, "0", "8");
		  
		  //send mail to parents to tell them 'da good news'  
		  $subject = "Bericht van " . $appname . ": " . $superuser_name . " heeft " . userValueKid($kid_id, "name") . " ingedeeld bij " . userValueSubcategory($new_subcategory_id, $superuser, "cat_name");
		  $message_title = "" . userValueKid($kid_id, "name") . " is ingedeeld bij " . userValueSubcategory($new_subcategory_id, $superuser, "cat_name") . " op " . $appname . ".";
		  $message = "Beste ouder,<br><br>
		  " . $superuser_name . " gebruikt de " . $appname . "-app en dat is goed nieuws! Al hun activiteiten zullen automatisch zichtbaar worden in de " . $appname . "-kalender van " . userValueKid($kid_id, "name") . ". Je kan  via de app met je vereniging communiceren.  Contactgegevens, handige documenten, leuke foto's, ... kan je er eenvoudig terugvinden.<br><br>
		  Veel plezier!<br>
		  Team " . $appname;
		  
		  $link = "<a href=\"" . $full_path . "/openlink.php?goto_kid=$kid_id\" class=\"link-button\" target=\"_blank\">Open " . $appname . "</a>";


			$message_body = file_get_contents('mail_templates/' . $mailtemplate_superuser);
			
			$message_body = str_replace('%message_title%', $message_title, $message_body); 
			$message_body = str_replace('%message%', $message, $message_body);
			$message_body = str_replace('%tracker%', $tracker, $message_body);
			$message_body = str_replace('%plus%', $plus, $message_body);
			$message_body = str_replace('%disclaimer%', $disclaimer, $message_body);
			$message_body = str_replace('%advertising%', $advertising, $message_body);
			$message_body = str_replace('%superusername%', $superuser_name, $message_body);
			$message_body = str_replace('%link%', $link, $message_body);
			$message_body = str_replace('%link2%', $link2, $message_body);
			$message_body = str_replace('%link3%', $link3, $message_body);
			$message_body = str_replace('%reply%', $reply_no, $message_body);
			$message_body = str_replace('%full_path%', $full_path, $message_body);		  
			$message_body = str_replace('%appname%', $appname, $message_body);	
			
			//check if premium level (personalize communication)
			if(checkPremiumLevel($superuser_id, $superuser, "") != "level 0"){
			$message_body = str_replace('%socialfacebook%', GetSocialWebMail($superuser_id,$superuser,"cat_facebook",$new_subcategory_id), $message_body);
			$message_body = str_replace('%socialwebsite%', GetSocialWebMail($superuser_id,$superuser,"cat_website",$new_subcategory_id), $message_body);
			if($superuser_avatar == ""){ 
			$message_body = str_replace('%avatar%', $logo_path, $message_body);
			}
			else {
			$message_body = str_replace('%avatar%', $data_path . "/uploads/" . $superuser_avatar, $message_body);
			}
			}
			//not premium
			else {
				$message_body = str_replace('%socialfacebook%', "", $message_body);
				$message_body = str_replace('%socialwebsite%', "", $message_body);
				$message_body = str_replace('%avatar%', $logo_path, $message_body);
			}
		  
		  
		  include("PHPmailer_include.php");
		   
			$mail->setFrom($mailfromaddress,$superuser_name);
			//Set an alternative reply-to address
			$mail->addReplyTo($mailfromaddress, $mailfromname);
			//Set who the message is to be sent to
			
			$mail->Subject = $subject;
			$mail->MsgHTML($message_body);
			
			foreach ($result as $row) {
			$mail->addAddress($row['email'], $row['username']);
			// If the mail is send, it returns true, else it will return false
			
			if(!$mail->send()) {
				echo "fout";
			} else {
				echo "gelukt";
			}
			$mail->clearAddresses();
			}
		
				  
			}	  
			}
			}
	   

  }
  if(isset($_POST['id_list_parents']) && !empty($_POST['id_list_parents'])){
	  $id_list_parents = mres($_POST['id_list_parents']);
	  //first delete if already exists in the other subgroup so there aren't duplicates (concept cut/paste)
	  mysqli_query($con,"DELETE FROM parents_" . $superuser . "s WHERE parent_id IN($id_list_parents) AND " . $superuser . "_id = $superuser_id AND subcategory = $new_subcategory_id");
	  
	  //then delete any existing labels put on users in the old subcategory
	  mysqli_query($con,"DELETE FROM labels WHERE adult_id IN($id_list_parents) AND " . $superuser . "_id = $superuser_id AND subcategory_id = $old_subcategory_id");
	  
	  //then update
	  mysqli_query($con,"UPDATE parents_" . $superuser . "s SET subcategory = $new_subcategory_id, mark= 0 WHERE parent_id IN($id_list_parents) AND " . $superuser . "_id = $superuser_id AND subcategory = $old_subcategory_id");
	  
	  if($sendmail == "1"){
	  if(userValueSuper($superuser_id,$superuser,"testmode") == 0){
		  $parent_list = explode(',', $id_list_parents);
		  foreach($parent_list AS $parent_id){ 
		  
		   //first send notification 
	$payload = create_payload_json($superuser_name . " heeft jou ingedeeld in " . userValueSubcategory($new_subcategory_id, $superuser, "cat_name") . " op " . $appname . ".", "index.php?goto_parent=1");
	  $fetchtokens = mysqli_query($con,"SELECT device, device_token FROM users WHERE id = $parent_id AND device <> 0 AND device_token <> '' AND device_token <> 'null'");
		  if(mysqli_num_rows($fetchtokens) > 0){
		  foreach($fetchtokens AS $row){
		  $user_mobile_info = ['device'=>$row['device'], 'token'=>$row['device_token']];
		  send_mobile_notification_request($user_mobile_info, $payload);
		  }
		  }
	  
	  LogEntrySuper("0", $superuser_id, $superuser, "0", $parent_id, $new_subcategory_id, "0", "8");		  
	
	  //send mail to parents to tell them 'da good news'  
	  $subject = "Bericht van " . $appname . ": " . $superuser_name . " heeft " . userValue($parent_id, "username") . " ingedeeld bij " . userValueSubcategory($new_subcategory_id, $superuser, "cat_name"); 
	  $to = userValue($parent_id, "email");
	  $message_title = "" . userValue($parent_id, "username") . " is ingedeeld bij " . userValueSubcategory($new_subcategory_id, $superuser, "cat_name") . " op " . $appname . ".";
	  $message = "Hallo " . userValue($parent_id, "username") . ",<br><br>
	  " . $superuser_name . " gebruikt de " . $appname . "-app en dat is goed nieuws! Al hun activiteiten zullen automatisch zichtbaar worden in je " . $appname . "-kalender.  Je kan via de app met je vereniging communiceren.  Contactgegevens, handige documenten, leuke foto's, ... kan je er eenvoudig terugvinden.<br><br>
	  Veel plezier!<br>
	  Team " . $appname;
	  $link = "<a href=\"" . $full_path . "/openlink.php?goto_parent=1\" class=\"link-button\" target=\"_blank\">Open " . $appname . "</a>";
	  
	  $message_body = file_get_contents('mail_templates/' . $mailtemplate_superuser);

	$message_body = str_replace('%message_title%', $message_title, $message_body); 
	$message_body = str_replace('%message%', $message, $message_body);
	$message_body = str_replace('%tracker%', $tracker, $message_body);
	$message_body = str_replace('%plus%', $plus, $message_body);
	$message_body = str_replace('%disclaimer%', $disclaimer, $message_body);
	$message_body = str_replace('%advertising%', $advertising, $message_body);
	$message_body = str_replace('%superusername%', $superuser_name, $message_body);
	$message_body = str_replace('%link%', $link, $message_body);
	$message_body = str_replace('%link2%', $link2, $message_body);
	$message_body = str_replace('%link3%', $link3, $message_body);
	$message_body = str_replace('%reply%', $reply_no, $message_body);
	$message_body = str_replace('%full_path%', $full_path, $message_body);		  
	$message_body = str_replace('%appname%', $appname, $message_body);	
	
	//check if premium level (personalize communication)
	if(checkPremiumLevel($superuser_id, $superuser, "") != "level 0"){
	$message_body = str_replace('%socialfacebook%', GetSocialWebMail($superuser_id,$superuser,"cat_facebook",$new_subcategory_id), $message_body);
	$message_body = str_replace('%socialwebsite%', GetSocialWebMail($superuser_id,$superuser,"cat_website",$new_subcategory_id), $message_body);
	if($superuser_avatar == ""){ 
	$message_body = str_replace('%avatar%', $logo_path, $message_body);
	}
	else {
	$message_body = str_replace('%avatar%', $data_path . "/uploads/" . $superuser_avatar, $message_body);
	}
	}
	//not premium
	else {
		$message_body = str_replace('%socialfacebook%', "", $message_body);
		$message_body = str_replace('%socialwebsite%', "", $message_body);
		$message_body = str_replace('%avatar%', $logo_path, $message_body);
	}

  
	userSendMail6($mailfromaddress, $to, $subject, $message_body); //Send Mail
		  
		  
	  }

  }
  }
  }
  
  if(isset($_POST['id_list_manual']) && !empty($_POST['id_list_manual'])){
	  $id_list_manual = mres($_POST['id_list_manual']);
	  $manualids = explode(',',$id_list_manual);
	foreach($manualids as $kid_id_manual) {
		//get information of manual kid
		$sql_manual = mysqli_query($con,"SELECT key_code FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id = $superuser_id AND id = $kid_id_manual");
		$row_members_manual = mysqli_fetch_array($sql_manual);
		
		//check if user not already exists in this subcategory
		$check_manual = mysqli_query($con,"SELECT * FROM kids_" . $superuser . "s_manual WHERE key_code = $row_members_manual[key_code] AND " . $superuser . "_id = $superuser_id AND subcategory = $new_subcategory_id");
		$numrows_check_manual = mysqli_num_rows($check_manual);
		
		if($numrows_check_manual == 0){

		//then insert a new row
		mysqli_query($con,"INSERT INTO kids_" . $superuser . "s_manual
		(key_code,
		name,
		  surname,
		  username, 
		  email,
		  Telefoonnummer,
		  Telefoonnummer2,
		  member_number,
		  member_category,
		  member_medical_x,
		  member_emergency_x,
		  member_instrument,
		  " . $superuser . "_id,
		  subcategory)
SELECT key_code,name, surname, username, email, Telefoonnummer, Telefoonnummer2, member_number, member_category, member_medical_x, member_emergency_x, member_instrument, " . $superuser . "_id, $new_subcategory_id
FROM kids_" . $superuser . "s_manual A
WHERE A.id = $kid_id_manual");

		//then delete so there aren't duplicates (concept cut/paste)
		mysqli_query($con,"DELETE FROM kids_" . $superuser . "s_manual WHERE id = $kid_id_manual");
		
		//then delete any existing labels put on users in the old subcategory
	    mysqli_query($con,"DELETE FROM labels WHERE manual_id = $kid_id_manual AND " . $superuser . "_id = $superuser_id AND subcategory_id = $old_subcategory_id");
		
		  }
		  else {
			  //user already exists in that subcategory -> delete here
			mysqli_query($con,"DELETE FROM kids_" . $superuser . "s_manual WHERE id = $kid_id_manual");  
		  }
	}

  }
  }
  if($type == "copymembers"){
	
	if(isset($_POST['id_list_kids']) && !empty($_POST['id_list_kids'])){
	  $id_list_kids = mres($_POST['id_list_kids']);
	  $ids = explode(',',$id_list_kids);
	  foreach($ids as $kid_id) {
		/*
		//insert if not already exists
		mysqli_query($con,"INSERT INTO kids_" . $superuser . "s(kid_id, " . $superuser . "_id,subcategory) 
	SELECT $kid_id,$superuser_id,$new_subcategory_id
	  FROM dual 
	  WHERE NOT EXISTS 
	    (SELECT * FROM kids_" . $superuser . "s WHERE kid_id=$kid_id AND " . $superuser . "_id=$superuser_id AND subcategory = $new_subcategory_id)");
	    */
	    
	    //first check if not already exists in new subcategory
		if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM kids_" . $superuser . "s WHERE kid_id=$kid_id AND " . $superuser . "_id=$superuser_id AND subcategory = $new_subcategory_id")) == 0){
			$sql_old_subcategory = mysqli_query($con, "SELECT member_number,member_category,AES_DECRYPT(member_medical_x, SHA1('$pass')) AS member_medical,AES_DECRYPT(member_emergency_x, SHA1('$pass')) AS member_emergency, ApproveFace, ApproveFace_timestamp FROM kids_" . $superuser . "s WHERE kid_id=$kid_id AND " . $superuser . "_id=$superuser_id AND subcategory = $old_subcategory_id");
			$row_duplicate = mysqli_fetch_array($sql_old_subcategory);
			
		    mysqli_query($con,"INSERT INTO kids_" . $superuser . "s 
		    (kid_id, " . $superuser . "_id,subcategory,member_number,member_category,member_medical_x,member_emergency_x,ApproveFace,ApproveFace_timestamp) 
		    VALUES
		    ($kid_id,$superuser_id,$new_subcategory_id,'$row_duplicate[member_number]','$row_duplicate[member_category]',AES_ENCRYPT('$row_duplicate[member_medical]', SHA1('$pass')),AES_ENCRYPT('$row_duplicate[member_emergency]', SHA1('$pass')),'$row_duplicate[ApproveFace]', '$row_duplicate[ApproveFace_timestamp]')");  		    
	    }
	    
	    
	      
	}
  }
  if(isset($_POST['id_list_parents']) && !empty($_POST['id_list_parents'])){
	  $id_list_parents = mres($_POST['id_list_parents']);
	  $ids = explode(',',$id_list_parents);
	  foreach($ids as $parent_id) {
		
		/*
		//insert if not already exists
		mysqli_query($con,"INSERT INTO parents_" . $superuser . "s(parent_id, " . $superuser . "_id,subcategory) 
	SELECT $parent_id,$superuser_id,$new_subcategory_id
	  FROM dual 
	  WHERE NOT EXISTS 
	    (SELECT * FROM parents_" . $superuser . "s WHERE parent_id=$parent_id AND " . $superuser . "_id=$superuser_id AND subcategory = $new_subcategory_id)"); 
	    */
	    
	     //first check if not already exists in new subcategory
		if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM parents_" . $superuser . "s WHERE parent_id=$parent_id AND " . $superuser . "_id=$superuser_id AND subcategory = $new_subcategory_id")) == 0){
			$sql_old_subcategory = mysqli_query($con, "SELECT member_number,member_category,AES_DECRYPT(member_medical_x, SHA1('$pass')) AS member_medical,AES_DECRYPT(member_emergency_x, SHA1('$pass')) AS member_emergency, ApproveFace, ApproveFace_timestamp FROM parents_" . $superuser . "s WHERE parent_id=$parent_id AND " . $superuser . "_id=$superuser_id AND subcategory = $old_subcategory_id");
			$row_duplicate = mysqli_fetch_array($sql_old_subcategory);
			
		    mysqli_query($con,"INSERT INTO parents_" . $superuser . "s 
		    (parent_id, " . $superuser . "_id,subcategory,member_number,member_category,member_medical_x,member_emergency_x,ApproveFace,ApproveFace_timestamp) 
		    VALUES
		    ($parent_id,$superuser_id,$new_subcategory_id,'$row_duplicate[member_number]','$row_duplicate[member_category]',AES_ENCRYPT('$row_duplicate[member_medical]', SHA1('$pass')),AES_ENCRYPT('$row_duplicate[member_emergency]', SHA1('$pass')),'$row_duplicate[ApproveFace]', '$row_duplicate[ApproveFace_timestamp]')");  		    
	    }
	     
	}
  }

 if(isset($_POST['id_list_manual']) && !empty($_POST['id_list_manual'])){
	  $id_list_manual = mres($_POST['id_list_manual']);
	  $manualids = explode(',',$id_list_manual);
	  foreach($manualids as $kid_id_manual) {
		//get information of manual kid
		$sql_manual = mysqli_query($con,"SELECT key_code FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id = $superuser_id AND id = $kid_id_manual");
		$row_members_manual = mysqli_fetch_array($sql_manual);
		
		//check if user not already exists in this subcategory
		$check_manual = mysqli_query($con,"SELECT * FROM kids_" . $superuser . "s_manual WHERE key_code = $row_members_manual[key_code] AND " . $superuser . "_id = $superuser_id AND subcategory = $new_subcategory_id");
		$numrows_check_manual = mysqli_num_rows($check_manual);
		
		if($numrows_check_manual == 0){
		
		//then insert a new row
		mysqli_query($con,"INSERT INTO kids_" . $superuser . "s_manual
		(key_code,
		  name,
		  surname,
		  username, 
		  email,
		  Telefoonnummer,
		  Telefoonnummer2,
		  " . $superuser . "_id,
		  subcategory,
		  member_number,
		  member_category,
		  member_instrument,
		  member_medical_x,
		  member_emergency_x,
		  ApproveFace,
		  ApproveFace_timestamp,
		  adult)
SELECT key_code, name, surname, username, email, Telefoonnummer, Telefoonnummer2, " . $superuser . "_id, $new_subcategory_id, member_number, member_category, member_instrument, member_medical_x, member_emergency_x, ApproveFace, ApproveFace_timestamp, adult
FROM kids_" . $superuser . "s_manual A
WHERE A.id = $kid_id_manual");
		  }
	}
	}  
  }
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "MoveCopyMembers", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
   
}

//SUPERUSER REPORT A USER PROFILE
if(isset ($action) && $action == "ReportUser"){

  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  
  $kid_id = 0;
  $parent_id = 0;
  
  if(isset($_POST['kid_id'])){
	  $kid_id = mres($_POST['kid_id']);
  }
  if(isset($_POST['parent_id'])){
	  $parent_id = mres($_POST['parent_id']);
  }
   
  mysqli_query($con,"INSERT INTO kids_report (" . $superuser . "_id,friend_id,parent_id) VALUES ($superuser_id, $kid_id,$parent_id)");
  
  if($kid_id <> 0){

  //send mail to app team
  $date = date('d-m-Y H:i:s');
  $subject = 'ADMIN: Kind (' . userValueKid($kid_id, "name") . ' ' . userValueKid($kid_id, "surname") .  ' - id = ' . $kid_id .  ') gerapporteerd op ' . $appname;
  $message_title = "Er werd een kind gerapporteerd op " . $appname . " (Door een superuser)";
  $message = "Details:<br>
   <strong>- naam profiel (kind):</strong> " . userValueKid($kid_id, "name") . ' ' . userValueKid($kid_id, "surname") . "<br>
   <strong>- id kind (table kids in database):</strong> " . $kid_id . "<br><br>
  Gerapporteerd door account:<br>
   <strong>- " . $superuser . ":</strong> " . $superuser_name . "<br>
   <strong>- id " . $superuser . " (table users in database):</strong> "  . $id . "<br>
   <strong>- id " . $superuser . " (table " . $superuser . "s):</strong> " . $superuser_id . "<br>
   <strong>- e-mail persoon die rapporteerde:</strong> " . userValue(null, "email") . "<br>
   <strong>- e-mail " . $superuser . " (algemeen):</strong> " . $superuser_mail . "<br><br>
  Rapport op: " . $date;
  
  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
  $message_body = str_replace('%message_title%', $message_title, $message_body); 
  $message_body = str_replace('%message%', $message, $message_body);
  $message_body = str_replace('%link%', "", $message_body);
  $message_body = str_replace('%link2%', "", $message_body);
  $message_body = str_replace('%link3%', "", $message_body);
  $message_body = str_replace('%reply%', "", $message_body);
  $message_body = str_replace('%avatar%', $logo_path, $message_body);	
  $message_body = str_replace('%full_path%', $full_path, $message_body);		  
  $message_body = str_replace('%appname%', $appname, $message_body);
  
  userSendMail6($mailfromaddress, $mailinfoaddress, $subject, $message_body); //Send Mail

  //send mail to superuser who reported child
  $date = date('d-m-Y H:i:s');
  $subject = 'Je rapporteerde een kind op ' . $appname;
  $message_title = "Bevestiging rapportage profiel op " . $appname;
  $message = "Beste " . userValue(null, "username") . ",<br><br>
  We hebben je mail goed ontvangen!<br><br> 
  Je rapporteerde het volgende profiel: <br>
  <div align=\"center\">
<img class=\"avatar\" src=\"" . showAvatarFromKidIdMail($kid_id) . "\" alt=\"". userValueKid($kid_id, "name") . ' ' . userValueKid($kid_id, "surname") . "\" /><br>@" . userValueKid($kid_id, "name") . ' ' . userValueKid($kid_id, "surname") . "
</div><br>
  Datum rapportage:<strong> " . $date . "</strong><br> 
  Wij onderzoeken de zaak en houden je verder op de hoogte!<br><br>
  Indien je nog verdere informatie hierover wil verschaffen, of indien je de rapportage ongedaan wil maken, kan je op deze mail reply-en (gebruik de knop hierboven).<br>
  Je kan de rapportage ook in " . $appname . " ongedaan maken.<br><br>
  Vriendelijke groet,<br><br>
  Het " . $appname . "-team";
  
    $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
	$message_body = str_replace('%message_title%', $message_title, $message_body); 
	$message_body = str_replace('%message%', $message, $message_body);
	$message_body = str_replace('%link%', "", $message_body);
	$message_body = str_replace('%link2%', "", $message_body);
	$message_body = str_replace('%link3%', "", $message_body);
	$message_body = str_replace('%reply%', $reply_yes, $message_body);
	$message_body = str_replace('%reply_to%', $mailinfoaddress, $message_body);
	$message_body = str_replace('%avatar%', $logo_path, $message_body);		
	$message_body = str_replace('%full_path%', $full_path, $message_body);		  
	$message_body = str_replace('%appname%', $appname, $message_body);	  

  userSendMail6($mailfromaddress, userValue(null, "email"), $subject, $message_body); //Send Mail
  
  }
  
  if($parent_id <> 0){
  //send mail to app team
  $date = date('d-m-Y H:i:s');
  $subject = 'ADMIN: Volwassen ' . $appname . '-lid (' . userValue($parent_id, "username") . ' - id = ' . $parent_id .  ') gerapporteerd op ' . $appname;
  $message_title = "Er werd een volwassen " . $appname . "-lid gerapporteerd op " . $appname . " (Superuser)";
  $message = "Details:<br>
   <strong>- naam profiel:</strong> " . userValue($parent_id, "username") . "<br>
   <strong>- id profiel (table users in database):</strong> " . $parent_id . "<br><br>
  Gerapporteerd door account:<br>
   <strong>- " . $superuser . ":</strong> " . $superuser_name . "<br>
   <strong>- id " . $superuser . " (table users in database):</strong> "  . $id . "<br>
   <strong>- id " . $superuser . " (table " . $superuser . "s):</strong> " . $superuser_id . "<br>
   <strong>- e-mail persoon die rapporteerde:</strong> " . userValue(null, "email") . "<br>
   <strong>- email " . $superuser . " (algemeen):</strong> " . $superuser_mail . "<br><br>
  Rapport op: " . $date;
  
  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
  $message_body = str_replace('%message_title%', $message_title, $message_body); 
  $message_body = str_replace('%message%', $message, $message_body);
  $message_body = str_replace('%link%', "", $message_body);
  $message_body = str_replace('%link2%', "", $message_body);
  $message_body = str_replace('%link3%', "", $message_body);
  $message_body = str_replace('%reply%', "", $message_body);
  $message_body = str_replace('%avatar%', $logo_path, $message_body);	
  $message_body = str_replace('%full_path%', $full_path, $message_body);		  
  $message_body = str_replace('%appname%', $appname, $message_body);
  
  userSendMail6($mailfromaddress, $mailinfoaddress, $subject, $message_body); //Send Mail

  //send mail to superuser who reported child
  $date = date('d-m-Y H:i:s');
  $subject = 'Je rapporteerde een profiel op ' . $appname;
  $message_title = "Bevestiging rapportage profiel op " . $appname;
  $message = "Beste " . userValue(null, "username") . ",<br><br>
  We hebben je mail goed ontvangen!<br><br> 
  Je rapporteerde het volgende profiel: <br><strong>" . userValue($parent_id, "username") . "</strong><br>
  Datum rapportage:<strong> " . $date . "</strong><br> 
  Wij onderzoeken de zaak en houden je verder op de hoogte!<br><br>
  Indien je nog verdere informatie hierover wil verschaffen, of indien je de rapportage ongedaan wil maken, kan je op deze mail replyen (via de knop hierboven).<br>
  Je kan de rapportage ook in " . $appname . " ongedaan maken.<br><br>
  Vriendelijke groet,<br><br>
  Het " . $appname . "-team";
  
  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
	$message_body = str_replace('%message_title%', $message_title, $message_body); 
	$message_body = str_replace('%message%', $message, $message_body);
	$message_body = str_replace('%link%', "", $message_body);
	$message_body = str_replace('%link2%', "", $message_body);
	$message_body = str_replace('%link3%', "", $message_body);
	$message_body = str_replace('%reply%', $reply_yes, $message_body);
	$message_body = str_replace('%reply_to%', $mailinfoaddress, $message_body);
	$message_body = str_replace('%avatar%', $logo_path, $message_body);	
	$message_body = str_replace('%full_path%', $full_path, $message_body);		  
	$message_body = str_replace('%appname%', $appname, $message_body);		  

  userSendMail6($mailfromaddress, userValue(null, "email"), $subject, $message_body); //Send Mail  
	  
  }
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "ReportUser", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//SUPERUSER CANCEL REPORT OF USER PROFILE
if(isset ($action) && $action == "ReportUserUndo"){

  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  
  $kid_id = 0;
  $parent_id = 0;
  
  if(isset($_POST['kid_id'])){
	  $kid_id = mres($_POST['kid_id']);
  }
  if(isset($_POST['parent_id'])){
	  $parent_id = mres($_POST['parent_id']);
  }

	  
  //mysqli_query($con,"INSERT INTO kids_report (" . $superuser . "_id,friend_id,parent_id) VALUES ($superuser_id, $kid_id,$parent_id)");
  
  if($kid_id <> 0){

   mysqli_query($con,"DELETE FROM kids_report WHERE friend_id=$kid_id AND " . $superuser . "_id=$superuser_id");
  
  //send mail to app team
  $date = date('d-m-Y H:i:s');
  $subject = 'ADMIN: ANNULATIE RAPPORT Kind (' . userValueKid($kid_id, "name") . ' ' . userValueKid($kid_id, "surname") . ' - id = ' . $kid_id .  ')';
  $message_title = "Rapportage ongedaan gemaakt op " . $appname . " (Superuser)";
  $message = "Details:<br>
   <strong>- naam profiel (kind):</strong> " . userValueKid($kid_id, "name") . ' ' . userValueKid($kid_id, "surname") . "<br>
   <strong>- id kind (table kids in database):</strong> " . $kid_id . "<br><br>
  Geannuleerd door account:<br>
   <strong>- " . $superuser . ":</strong> " . $superuser_name . "<br>
   <strong>- id " . $superuser . " (table users in database):</strong> "  . $id . "<br>
   <strong>- id " . $superuser . " (table " . $superuser . "s):</strong> " . $superuser_id . "<br>
   <strong>- e-mail persoon die de rapportage annuleerde:</strong> " . userValue(null, "email") . "<br>
   <strong>- email " . $superuser . " (algemeen):</strong> " . $superuser_mail . "<br><br>
  Rapport is geannuleerd op: " . $date;
  
  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
  $message_body = str_replace('%message_title%', $message_title, $message_body); 
  $message_body = str_replace('%message%', $message, $message_body);
  $message_body = str_replace('%link%', "", $message_body);
  $message_body = str_replace('%link2%', "", $message_body);
  $message_body = str_replace('%link3%', "", $message_body);
  $message_body = str_replace('%reply%', "", $message_body);
  $message_body = str_replace('%avatar%', $logo_path, $message_body);	
  $message_body = str_replace('%full_path%', $full_path, $message_body);		  
  $message_body = str_replace('%appname%', $appname, $message_body);
  
  userSendMail6($mailfromaddress, $mailinfoaddress, $subject, $message_body); //Send Mail

  //send mail to superuser who reported child
  $date = date('d-m-Y H:i:s');
  $subject = 'Je annuleerde een rapport op ' . $appname;
  $message_title = "Bevestiging annulatie rapportage op " . $appname;
  $message = "Beste " . userValue(null, "username") . ",<br><br>
  We hebben je mail goed ontvangen!<br><br> 
  Je annuleerde het gerapporteerde profiel:
  <div align=\"center\">
<img class=\"avatar\" src=\"" . showAvatarFromKidIdMail($kid_id) . "\" alt=\"". userValueKid($kid_id, "name") . ' ' . userValueKid($kid_id, "surname") . "\" /><br>@" . userValueKid($kid_id, "name") . ' ' . userValueKid($kid_id, "surname") . "
</div><br>
  Datum annulatie:<strong> " . $date . "</strong><br><br>
  Vriendelijke groet,<br><br>
  Het " . $appname . "-team";
  	
  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
	$message_body = str_replace('%message_title%', $message_title, $message_body); 
	$message_body = str_replace('%message%', $message, $message_body);
	$message_body = str_replace('%link%', "", $message_body);
	$message_body = str_replace('%link2%', "", $message_body);
	$message_body = str_replace('%link3%', "", $message_body);
	$message_body = str_replace('%reply%', $reply_no, $message_body);
	$message_body = str_replace('%avatar%', $logo_path, $message_body);	
	$message_body = str_replace('%full_path%', $full_path, $message_body);		  
	$message_body = str_replace('%appname%', $appname, $message_body);		  

  userSendMail6($mailfromaddress, userValue(null, "email"), $subject, $message_body); //Send Mail
  
  }
  
  if($parent_id <> 0){
  mysqli_query($con,"DELETE FROM kids_report WHERE parent_id=$parent_id AND " . $superuser . "_id=$superuser_id");
  
  //send mail to app team
  $date = date('d-m-Y H:i:s');
  $subject = 'ADMIN: ANNULATIE RAPPORT Volwassene (' . userValue($parent_id, "username") . ' - id = ' . $parent_id .  ')';
  $message_title = "Rapportage ongedaan gemaakt op " . $appname . " (Superuser)";
  $message = "Details:<br>
   <strong>- naam profiel:</strong> " . userValue($parent_id, "username") . "<br>
   <strong>- id profiel (table users in database):</strong> " . $parent_id . "<br><br>
  Geannuleerd door account:<br>
   <strong>- " . $superuser . ":</strong> " . $superuser_name . "<br>
   <strong>- id " . $superuser . " (table users in database):</strong> "  . $id . "<br>
   <strong>- id " . $superuser . " (table " . $superuser . "s):</strong> " . $superuser_id . "<br>
   <strong>- e-mail persoon die de rapportage annuleerde:</strong> " . userValue(null, "email") . "<br>
   <strong>- email " . $superuser . " (algemeen):</strong> " . $superuser_mail . "<br><br>
  Rapport is geannuleerd op: " . $date;
  
  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
  $message_body = str_replace('%message_title%', $message_title, $message_body); 
  $message_body = str_replace('%message%', $message, $message_body);
  $message_body = str_replace('%link%', "", $message_body);
  $message_body = str_replace('%link2%', "", $message_body);
  $message_body = str_replace('%link3%', "", $message_body);
  $message_body = str_replace('%reply%', "", $message_body);
  $message_body = str_replace('%avatar%', $logo_path, $message_body);	
  $message_body = str_replace('%full_path%', $full_path, $message_body);		  
  $message_body = str_replace('%appname%', $appname, $message_body);
  
  userSendMail6($mailfromaddress, $mailinfoaddress, $subject, $message_body); //Send Mail

  //send mail to superuser who reported adult
  $date = date('d-m-Y H:i:s');
  $subject = 'Je annuleerde een rapport op ' . $appname;
  $message_title = "Bevestiging annulatie rapportage op " . $appname;
  $message = "Beste " . userValue(null, "username") . ",<br><br>
  We hebben je mail goed ontvangen!<br><br> 
  Je annuleerde het gerapporteerde profiel: <strong>" . userValue($parent_id, "username") . "</strong><br>
  Datum annulatie:<strong> " . $date . "</strong><br><br>
  Vriendelijke groet,<br><br>
  Het " . $appname . "-team";
  
  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
	$message_body = str_replace('%message_title%', $message_title, $message_body); 
	$message_body = str_replace('%message%', $message, $message_body);
	$message_body = str_replace('%link%', "", $message_body);
	$message_body = str_replace('%link2%', "", $message_body);
	$message_body = str_replace('%link3%', "", $message_body);
	$message_body = str_replace('%reply%', $reply_no, $message_body);
	$message_body = str_replace('%avatar%', $logo_path, $message_body);	
	$message_body = str_replace('%full_path%', $full_path, $message_body);		  
	$message_body = str_replace('%appname%', $appname, $message_body);		  

  userSendMail6($mailfromaddress, userValue(null, "email"), $subject, $message_body); //Send Mail	   
	  
  }

  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "ReportUserUndo", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}


//UPDATE CALENDAR ITEM
if(isset ($action) && $action == "changecomment"){
	
	//get variables
	$subcategory = sres($_POST['id'], $superuser_id, $superuser); 
	$eventid = mres($_POST['eventid']);
	$start_time = mres($_POST['start_time']);
	$end_time = mres($_POST['end_time']);
	$showtimeframe = mres($_POST['showtimeframe']);
	$subject_event = mres($_POST['subject_event']);
	$title = mres($_POST['title']);
	
	//start- and enddate
	$startdate = DateFromEvent($eventid);
	if(isset($_POST['startdate']) && !empty($_POST['startdate'])) {
		$startdate = mres($_POST['startdate']);
	}
	if(isset($_POST['enddate']) && ($_POST['enddate'] <> $startdate) && ($_POST['enddate'] > $startdate)) { 
		$enddate = mres($_POST['enddate']);
	}
	else {
		$enddate = $startdate;
	}
	
	require_once 'plugins/HTMLPurifier/HTMLPurifier.auto.php';
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	$comment = $_POST["comment"]; //hier geen mres
	$comment = $purifier->purify($comment);
	$comment = strip_tags($comment);
	
	include('plugins/Autolink/lib_autolink.php');
	
	# By default if the display url is truncated, a title attribute is added to the link, if you don't want this, add a 4th parameter of false
	$comment = autolink($comment, 100, ' target="_blank" class="location"', false);
	
	# link up email address
	$comment = autolink_email($comment);
	
	$comment = mres($comment);
	

	if(isset($_POST['location'])) { 
	$location = mres($_POST['location']);
	}
	else {
		$location = "";
	}
	
	if(isset($_POST['street'])) {  
	$street = mres($_POST['street']);
	}
	else {
		$street = "";
	}
	
	if(isset($_POST['number'])) { 
	 $number = mres($_POST['number']);
	 }
	else {
		$number = "";
	}
	
	if(isset($_POST['postal'])) { 
	 $postal = mres($_POST['postal']);
	 }
	else {
		$postal = "";
	}
	
	if(isset($_POST['city'])) {
	$city = mres($_POST['city']);
	}
	else {
		$city = "";
	}
	
	//check for weather
    $weather = 0;
    if(isset($_POST['weather'])){
	    $weather = 1;
	}

	
	//check if entry is for all subgroups, 1 subgroup or multiple subgroups
	if(isset($_POST['ForAll'])){
	$ForAll = mres($_POST['ForAll']);
	}
	if(isset($ForAll) && $ForAll == "2"){
	if(isset($_POST['CopyToSubcategory'])){
	$CopyToSubcategory = implode(',', $_POST['CopyToSubcategory']);
	}
	}
	else {
	$CopyToSubcategory = "";
	}
	
	if(isset($ForAll) && $ForAll == "3"){
	if(isset($_POST['SomeStudentKid'])){
	$SomeStudentKid = implode(',', $_POST['SomeStudentKid']);
	}
	else {
		$SomeStudentKid = "0";
	}
	if(isset($_POST['SomeStudentAdult'])){
	$SomeStudentAdult = implode(',', $_POST['SomeStudentAdult']);
	}
	else {
		$SomeStudentAdult = "0";
	}
	if(isset($_POST['SomeStudentManual'])){
	$SomeStudentManual = implode(',', $_POST['SomeStudentManual']);
	}
	else {
		$SomeStudentManual = "0";
	}
	}
	else {
	$SomeStudentKid = "";
	$SomeStudentAdult = "";
	$SomeStudentManual = "";
	}
	
	//check for delayed mailing
	$delay_type = 0;
	if(isset($_POST['DelayedMail'])){
	$delay_type = $_POST['delay_type'];
    }
    
    //check for invite (button subscribe)
    $invite = 0;
    if(isset($_POST['Invite'])){
	    $invite = 1;
	}

	
	//update database
	//if repeated event: update all if user wants (checkbox checked)
	if(isset($_POST['UpdateAll'])){
		$repeat_id = $_POST['UpdateAll'];
		$update = mysqli_query($con,"UPDATE calendar SET
	title='$title',
	subject='$subject_event',
	comment='$comment',
	subcategory='$subcategory',
	ForAll ='$ForAll',
	ForSome = '$CopyToSubcategory',
	ForSome = '$CopyToSubcategory',
	ForKids = '$SomeStudentKid',
	ForAdults = '$SomeStudentAdult',
	ForManual = '$SomeStudentManual',
	invite ='$invite',
	delay_type = '$delay_type',
	startdate=CONCAT(DATE(startdate), ' ', '$start_time'),
	enddate=CONCAT(DATE(enddate), ' ', '$end_time'),
	showtimeframe='$showtimeframe',
	location='$location',
	street='$street',
	number='$number',
	postal='$postal',
	city='$city',
	weather='$weather'
	WHERE repeat_id='$repeat_id'
	AND " . $superuser . "_id = $superuser_id");
	}		
	else {
	//update only this day  
	$update = mysqli_query($con,"UPDATE calendar SET 
	title='$title',
	subject='$subject_event',
	comment='$comment',
	subcategory='$subcategory',
	ForAll ='$ForAll',
	ForSome = '$CopyToSubcategory',
	ForSome = '$CopyToSubcategory',
	ForKids = '$SomeStudentKid',
	ForAdults = '$SomeStudentAdult',
	ForManual = '$SomeStudentManual',
	invite ='$invite',
	delay_type = '$delay_type',
	startdate=CONCAT('$startdate', ' ', '$start_time'),
	enddate=CONCAT('$enddate', ' ', '$end_time'),
	showtimeframe='$showtimeframe',
	location='$location',
	street='$street',
	number='$number',
	postal='$postal',
	city='$city',
	weather='$weather'
	WHERE id='$eventid'
	AND " . $superuser . "_id = $superuser_id");
	}
	
	//repeat date if repeat isset
  if(isset($_POST['RepeatEvent'])){ 
	$repeat_type = $_POST['repeat_type'];
	$repeat_times = $_POST['repeat_times'];
	$repeat_id = $eventid;

	  
	//get date (and all information) of activity
	$get_date = mysqli_query($con,"SELECT *, DATE(startdate) AS startdate, DATE(enddate) AS enddate, DATE_FORMAT(startdate,'%H:%i') AS start_time, DATE_FORMAT(enddate,'%H:%i') AS end_time FROM calendar WHERE id = $eventid AND " . $superuser . "_id = $superuser_id");
	$row_get_date = mysqli_fetch_array($get_date);
	$comment = mres($row_get_date['comment']);
	$subject_event = mres($row_get_date['subject']);
	
	$freq = "day";
	$x = 1;
	//daily
	if($repeat_type == 1){
		$freq = "day";
		$x = 1;
	}
	//weekly
	if($repeat_type == 2){
		$freq = "week";
		$x = 1;
	}
	//2-weekly
	if($repeat_type == 3){
		$freq = "weeks";
		$x = 2;
	}
	//monthly
	if($repeat_type == 4){
		$freq = "month";
		$x = 1;
	}
	//yearly
	if($repeat_type == 5){
		$freq = "year";
		$x = 1;
	}
	//3-weekly
	if($repeat_type == 6){
		$freq = "weeks";
		$x = 3;
	}
	//4-weekly
	if($repeat_type == 7){
		$freq = "weeks";
		$x = 4;
	}
			
	if($repeat_times <= '30')
	{
		for($i = $x; $i <= $repeat_times*$x; $i+=$x)
		{
			
			$start = date('Y-m-d', strtotime("+$i " . $freq, strtotime($row_get_date['startdate'])));
			$end = date('Y-m-d', strtotime("+$i " . $freq, strtotime($row_get_date['enddate'])));
			//update 'mother'-event
			$update_event = mysqli_query($con,"UPDATE calendar SET repeat_type = 1, repeat_times = '$repeat_times', repeat_id = '$repeat_id' WHERE id='$eventid' AND " . $superuser . "_id = $superuser_id");

			//insert into calendar
			$insert = mysqli_query($con,"INSERT INTO calendar
			(`title`, `" . $superuser . "_id`, `subcategory`, `ForAll`, `ForSome`, `ForKids`, `ForAdults`, `ForManual`, `startdate`, `enddate`, `showtimeframe`, `subject`, `comment`, `location`, `street`, `number`, `postal`, `city`, `weather`, `invite`, `allDay`, `repeat_type`, `repeat_times`, `repeat_id`, `delay_type`) 
			VALUES
			('$row_get_date[title]','$superuser_id','$row_get_date[subcategory]','$row_get_date[ForAll]','$row_get_date[ForSome]','$row_get_date[ForKids]','$row_get_date[ForAdults]','$row_get_date[ForManual]',CONCAT('$start', ' ','$row_get_date[start_time]'),CONCAT('$end', ' ','$row_get_date[end_time]'),'$row_get_date[showtimeframe]','$subject_event','$comment','$row_get_date[location]','$row_get_date[street]','$row_get_date[number]','$row_get_date[postal]','$row_get_date[city]','$row_get_date[weather]','$row_get_date[invite]','false', 1, '$repeat_times','$repeat_id','$delay_type')");
			}
		}	

	  //end repeatevent
	  }

  if(userValueSuper($superuser_id,$superuser,"testmode") == 0){
  //log entry changed calendar
  LogEntrySuper($eventid, $superuser_id, $superuser, "0", "0", $subcategory, $ForAll, "1");


  //send mail to all parents if user wants (MailToParents checked)
  if(isset($_POST['MailToParents'])){
 
  //print all ids where mail must be sent to in case this event is not ForAll
  if($ForAll == 0){
  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, $subcategory, $eventid);
  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $subcategory);
  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, $subcategory, $eventid);
  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, $subcategory);
  }
  
  if($ForAll == 1){
  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, 0, $eventid);
  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $subcategory);
  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, 0, $eventid);
  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, 0);
  }
  
  if($ForAll == 2){
  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory, $eventid);
  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $CopyToSubcategory);
  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory, $eventid);
  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory);
  }
  
  if($ForAll == 3){
  $kids_list = $SomeStudentKid;
  $parent_list = getParentIdsFromKidIds($SomeStudentKid, $superuser_id, $superuser, $subcategory);
  $adult_list = $SomeStudentAdult;
  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
  $manual_list = $SomeStudentManual;
  }
  
  $rec_1 = CountRecipients($kids_list, $adult_list, $manual_list, $superuser_id, $superuser, $subcategory);
	$queue = 0;
	if($rec_1 > $max_mails_direct){
		$queue = 1;
	}
    
  //Build message
  $date_activity = dutchDateFromEvent($eventid);	  
  
  //fetch sender and greeting 
  //get greeting
  $greetings = getHeaderFooterMailSuper($superuser_id, $superuser, $subcategory, "cat_contact_name");
  //get sender
  $user_email = getHeaderFooterMailSuper($superuser_id, $superuser, $subcategory, "cat_contact_email");

  if($showtimeframe == "0"){
	  
	  if($start_time != "00:00"){ 
		if(($start_time == "05:55") && ($end_time != "23:55")){
			if($enddate > $startdate){
			$timeframe= "van <strong>" . date("d-m-Y", strtotime($startdate)) . "</strong> tot <strong>" . date("d-m-Y", strtotime($enddate)) . "</strong> om <strong>" . $end_time . "</strong>";
			}
			else { 
			$timeframe = "tot $end_time<br>";
			}
		}
		if(($start_time == "05:55") && ($end_time == "23:55")){
			if($enddate > $startdate){
			$timeframe= "van <strong>" . date("d-m-Y", strtotime($startdate)) . "</strong> tot <strong>" . date("d-m-Y", strtotime($enddate)) . "</strong>";
			}
			else {
			$timeframe = "de hele dag<br>";
			}
		}
		if(($start_time != "05:55") && ($end_time != "23:55")){
			if($enddate > $startdate){
			$timeframe= "van <strong>" . date("d-m-Y", strtotime($startdate)) . "</strong> om <strong>" . $start_time . "</strong> tot <strong>" . date("d-m-Y", strtotime($enddate)) . "</strong> om <strong>" . $end_time . "</strong>";
			}
			else {
			$timeframe = "van $start_time tot $end_time<br>";
			}
		}
		if(($start_time != "05:55") && ($end_time == "23:55")){
			if($enddate > $startdate){
			$timeframe= "van <strong>" . date("d-m-Y", strtotime($startdate)) . "</strong> om <strong>" . $start_time . "</strong> tot <strong>" . date("d-m-Y", strtotime($enddate)) . "</strong>";
			}
			else {
			$timeframe = "vanaf $start_time<br>";
			}
		}	
	}
	else {
		$timeframe = "";
	}
  }

  if($showtimeframe == "1"){ 
  if($enddate > $startdate){
	$timeframe= "van <strong>" . date("d-m-Y", strtotime($startdate)) . "</strong> tot <strong>" . date("d-m-Y", strtotime($enddate)) . "</strong>";
  }
	else {
	$timeframe = "de hele dag<br>";
	}
  }
  if($showtimeframe == "2"){ 
  if($startdate > $enddate){
	$timeframe= "van <strong>" . date("d-m-Y", strtotime($startdate)) . "</strong> tot <strong>" . date("d-m-Y", strtotime($enddate)) . "</strong>";
  }
  else {
	  $timeframe = "";
  }
  }
  
  if($subject_event <> ""){
	  $subject = $subject_event;
  }
  else {
	  $subject = "Bericht van " . $superuser_name . " via " . $appname . ": informatie over de activiteit van " . $date_activity . ".";
  }

//message title
$message_title = $subject;

//message: comment
if($comment <> ""){
$comment = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"<br/>",$comment);
$comment = stripslashes($comment);
$message = "<div align='left'>" . $comment . "<br><br>";
}
else {
$message = "<div align='left'><br><br>";	
}

//message details: start table
$message .= "<div style='border-top: 1px solid lightgray;border-bottom: 1px solid lightgray;'><br><table border='0' width='100%'>";

//message date
$message .= "<tr>
	<td width='30%' style='text-align: left; font-size: 18px; vertical-align: top'><img src='$data_path/mails_images/icons/calendar.png' width='16px' alt='icon'></td>
	<td width='70%' style='text-align: left; font-size: 18px; vertical-align: top'>" . $date_activity . "</td>
	</tr>";

//message timeframe
if($timeframe <> ""){
$message .= "<tr>
  <td width='30p%' style='text-align: left; font-size: 18px; vertical-align: top'><img src='$data_path/mails_images/icons/clock.png' width='16px' alt='icon'></td>
  <td width='70%' style='text-align: left; font-size: 18px; vertical-align: top'>" . $timeframe . "</td>
  </tr>";
}

//message location
if($location <> ""){
$message .= "<tr>
  <td width='30%' style='text-align: left; font-size: 18px; vertical-align: top'><img src='$data_path/mails_images/icons/location.png' width='16px' alt='icon'></td>
  <td width='70%' style='text-align: left; font-size: 18px; vertical-align: top'>" . $location . "</td>
  </tr>";
}

if(getAttachmentsSuper($superuser_id, $superuser,$eventid) <> "") {
$attachment_all = getAttachmentsSuper($superuser_id, $superuser,$eventid);
$message .= "<tr>
  <td width='30%' style='text-align: left; font-size: 18px; vertical-align: top'><img src='$data_path/mails_images/icons/paperclip.png' width='16px' alt='icon'></td>
  <td width='70%' style='text-align: left; font-size: 18px; vertical-align: top'>" . $attachment_all . "</td>
  </tr>";
}

//message details: end table
$message .= "</table><br></div>";

//message attachments
$message .= "<br><br>Vriendelijke groet,<br>
  " . $greetings . "</div><br>";
  
  //check if activity is canceled
  $check_canceled = mysqli_query($con, "SELECT id FROM calendar WHERE id = $eventid AND canceled = 1");
	if(mysqli_num_rows($check_canceled) > 0){
		$message .= "<br><font color=\"red\">Opgelet: deze activiteit is geannuleerd!</font>";
	}

  //check for survey
  $check_survey = mysqli_query($con, "SELECT * FROM survey_questions WHERE (event_id = $eventid OR FIND_IN_SET($eventid, event_id_list)) AND " . $superuser . "_id = $superuser_id AND archive = 0");
  if(mysqli_num_rows($check_survey) > 0){
	  $survey = 1;
  }
  else {
	  $survey = 0;
  }
  
  //check for paybuttons
  $check_paybuttons = mysqli_query($con, "SELECT " . $superuser . "s_paybuttons.*, paybuttons.* FROM " . $superuser . "s_paybuttons
  JOIN paybuttons ON " . $superuser . "s_paybuttons.button_id = paybuttons.id 
  WHERE " . $superuser . "s_paybuttons.event_id = $eventid AND " . $superuser . "s_paybuttons." . $superuser . "_id = $superuser_id");
  if(mysqli_num_rows($check_paybuttons) > 0){
	  $row_paybuttons = mysqli_fetch_array($check_paybuttons);
	  $paybuttons = 1;
	  $title = $row_paybuttons['title'];
	  $type = $row_paybuttons['type'];
	  $button_id = $row_paybuttons['button_id'];
  }
  else {
	  $paybuttons = 0;
	  $button_id = 0;
  }

  if($delay_type <> "0"){
	  $laterdate = "2";
	  $senddate = date('Y-m-d');
  }
  else {
	  $laterdate = "0";
	  $senddate = date('Y-m-d');
  }
  $logged_in_user = userValue(null, "id");
  
  require_once 'plugins/HTMLPurifier/HTMLPurifier.auto.php';
  $config = HTMLPurifier_Config::createDefault();
  $purifier = new HTMLPurifier($config);
  $dirty_html = $message;
  $message = $purifier->purify($dirty_html);
  $message_database = mres($message);
  
  $insert = mysqli_query($con,"INSERT INTO mails (event_id,id_list_subgroups,id_list_kids,id_list_adults,id_list_manual,subcategory_id," . $superuser . "_id,laterdate,senddate,mailfrom,BCC,from_id,subject,message,paybutton,survey,board,invite,is_sent,rec_1,queue) VALUES ($eventid,'0','$kids_list','$adult_list','$manual_list',$subcategory,$superuser_id,'$laterdate','$senddate','$user_email','', '$logged_in_user','$subject','$message_database','$button_id','$survey','0', '$invite', '0', '$rec_1', '$queue')");
$mail_id = mysqli_insert_id($con);
if(!$insert){
	error_log("Error description: " . mysqli_error($con), 0);
}

  
if($queue <> 1){
	$sql_mail = mysqli_query($con, "SELECT * FROM mails WHERE id = $mail_id AND " . $superuser . "_id = $superuser_id");
	$row_mail = mysqli_fetch_array($sql_mail);
	SendBulkMail($row_mail, 0); //second argument 0 = no cronjob
}
//end if isset MailToParents
}
}

LogAction($eventid, $superuser, $superuser_id, $subcategory, 0, 0, "changecomment", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));	
}


//UPLOAD AVATAR SUBCATEGORY
if(isset ($action) && $action == "uploadavatar"){
	$subcategory_id = $_POST['subcategory_id'];

	$target_dir = "../data/avatars/";
	
	//check first if other image is used (so delete the old one)
	$check_avatar_sql = mysqli_query($con,"SELECT avatar FROM " . $superuser . "s_categories WHERE id = '$subcategory_id' AND " . $superuser . "_id = $superuser_id");
	$check_avatar = mysqli_fetch_array($check_avatar_sql);
	$old_avatar = $check_avatar['avatar'];
	
	if ($old_avatar != "") {
		if (@unlink($target_dir .  $old_avatar)) {
		}
		else {
		}
	}
	
	
	$encoded = $_POST['image_data'];
    //explode at ',' - the last part should be the encoded image now
    $exp = explode(',', $encoded);
    //decode the image and finally save it
    $data = base64_decode($exp[1]);
    
    //add name and random number to uploaded avatar
	$rand = rand(0000,9999);
	$target_file = $target_dir . "" . $superuser . "s_" . $subcategory_id . "_" . $rand . ".png";
	$avatar = "" . $superuser . "s_" . $subcategory_id . "_" . $rand . ".png";
    //make sure you are the owner and have the rights to write content
    file_put_contents($target_file, $data);
    
    //update database:
	$update_avatar = mysqli_query($con,"UPDATE " . $superuser . "s_categories SET avatar = '$avatar' WHERE id='$subcategory_id' AND " . $superuser . "_id = $superuser_id");
	
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "uploadavatar", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));	 
}

//FETCH ALL GSM NUMBERS TO SEND A SMS (TEXT MESSAGE)
if(isset ($action) && $action == "fetch_sms"){
	
	$subcategory = sres($_POST['id'], $superuser_id, $superuser);
	$eventid = mres($_POST['eventid']);
	$comment = mres($_POST['comment']);
	$start_time = $_POST['start_time'];
	$end_time = $_POST['end_time'];
	$location = $_POST['location'];
	$street = $_POST['street'];
	$number = $_POST['number'];
	$postal = $_POST['postal'];
	$city = $_POST['city'];
	
	//check if entry is for all subgroups, 1 subgroup or multiple subgroups
	if(isset($_POST['ForAll'])){
	$ForAll = mres($_POST['ForAll']);
	}
	if(isset($ForAll) && $ForAll == "2"){
	if(isset($_POST['CopyToSubcategory'])){
	$CopyToSubcategory = implode(',', $_POST['CopyToSubcategory']);
	}
	}
	else {
	$CopyToSubcategory = "";
	}
	if(isset($ForAll) && $ForAll == "3"){
	if(isset($_POST['SomeStudentKid'])){
	$SomeStudentKid = implode(',', $_POST['SomeStudentKid']);
	}
	else {
		$SomeStudentKid = "0";
	}
	if(isset($_POST['SomeStudentAdult'])){
	$SomeStudentAdult = implode(',', $_POST['SomeStudentAdult']);
	}
	else {
		$SomeStudentAdult = "0";
	}
	if(isset($_POST['SomeStudentManual'])){
	$SomeStudentManual = implode(',', $_POST['SomeStudentManual']);
	}
	else {
		$SomeStudentManual = "0";
	}
	}
	else {
	$SomeStudentKid = "";
	$SomeStudentAdult = "";
	$SomeStudentManual = "";
	}

	
	//print all ids where mail must be sent to in case this event is not ForAll
	  if($ForAll == 0){
	  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, $subcategory);
	  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $subcategory);
	  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, $subcategory);
	  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
	  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, $subcategory);
	  }
	  
	  if($ForAll == 1){
	  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, 0);
	  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $subcategory);
	  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, 0);
	  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
	  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, 0);
	  }
	  
	  if($ForAll == 2){
	  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory);
	  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $CopyToSubcategory);
	  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory);
	  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
	  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory);
	  }
	  
	  if($ForAll == 3){
	  $kids_list = $SomeStudentKid;
	  $parent_list = getParentIdsFromKidIds($SomeStudentKid, $superuser_id, $superuser, $subcategory);
	  $adult_list = $SomeStudentAdult;
	  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory);
	  $manual_list = $SomeStudentManual;
	  }


	$result = mysqli_query($con,"SELECT 0 AS manual, username, Telefoonnummer FROM users WHERE id IN($parent_list,$adult_list) AND Telefoonnummer <> '' 
  UNION SELECT 1 AS manual, AES_DECRYPT(username, SHA1('$pass')) AS username, AES_DECRYPT(Telefoonnummer, SHA1('$pass')) AS Telefoonnummer FROM kids_" . $superuser . "s_manual WHERE id IN ($manual_list) AND Telefoonnummer <> ''
  UNION SELECT 1 AS manual, AES_DECRYPT(username, SHA1('$pass')) AS username, AES_DECRYPT(Telefoonnummer2, SHA1('$pass')) AS Telefoonnummer FROM kids_" . $superuser . "s_manual WHERE id IN ($manual_list) AND Telefoonnummer <> ''");
	
	 
 
  $message = "Beste,<br><br>
  " . $superuser_name . " wil je graag attent maken op volgend agendapunt:<br>
  - Datum: " . dutchDateFromEvent($eventid) . "<br>
  - Tijd: Van " . $start_time . " tot " . $end_time . "<br>
  - Locatie: " . $location . "<br>
  - Extra opmerkingen: " . $comment . "<br><br>
  Vriendelijke groet,<br>
  " . $superuser_name . "";
  
  $message = str_replace('<br>', '%0D%0A', $message);
  
  $phones = array();
  while($row_phone = mysqli_fetch_array($result)){
	 
	$phones[] = $row_phone['Telefoonnummer'];
	}
	//output parent_id in a list, to check if this id is a shared account
	$phone_list_iOS = implode(",", $phones);
	$phone_list_iOS = rtrim($phone_list_iOS,',');
	
	$phone_list_Android = implode(";", $phones);
	$phone_list_Android = rtrim($phone_list_Android,';');

	$data['phone_list_iOS'] = $phone_list_iOS;
	$data['phone_list_Android'] = $phone_list_Android;
	$data['message'] = $message;
	echo json_encode($data);
	exit;
}

//SMS (TEXT) MEMBER(S)
if(isset ($action) && $action == "sms_member"){

  $subcategory=sres($_POST['subcategory'], $superuser_id, $superuser);
  if(isset($_POST['id_list_kids']) && !empty($_POST['id_list_kids'])){
	  $id_list_kids = mres($_POST['id_list_kids']);
  }
  else {
	  $id_list_kids = 0;
  }
  if(isset($_POST['id_list_parents']) && !empty($_POST['id_list_parents'])){
	  $id_list_parents = mres($_POST['id_list_parents']);
  }
  else {
	  $id_list_parents = 0;
  }
  if(isset($_POST['id_list_manual']) && !empty($_POST['id_list_manual'])){
	  $id_list_manual = mres($_POST['id_list_manual']);
  }
  else {
	  $id_list_manual = 0;
  }
	
   $fetch_kids = mysqli_query($con,"SELECT kids_" . $superuser . "s.*, kids.id, kids.parent AS parent_id FROM kids
  JOIN kids_" . $superuser . "s ON kids_" . $superuser . "s.kid_id = kids.id
  WHERE kids_" . $superuser . "s." . $superuser . "_id = $superuser_id
  AND kids.id IN($id_list_kids)");
  
  $id_parents=array();
  if(mysqli_num_rows($fetch_kids) > 0){ 
  while($row_kids = mysqli_fetch_array($fetch_kids)){
	$id_parents[] = $row_kids['parent_id'];
	}
	//output parent_id in a list, to check if this id is a shared account
	$parent_list = implode(", ", $id_parents);
	
	if(empty($parent_list)){
	 $parent_list = "0";
	}
  }
  else {
	  $parent_list = "0";
  }
	
	
	$result = mysqli_query($con,"SELECT id, username, Telefoonnummer 
	FROM users 
	WHERE id IN(SELECT slave_id FROM users_share WHERE master_id IN ($parent_list))
	AND Telefoonnummer <> ''
	OR id IN ($parent_list)
	AND Telefoonnummer <> ''
	UNION
	SELECT id, username, Telefoonnummer
	FROM users
	WHERE id IN(SELECT parent_id FROM parents_" . $superuser . "s WHERE parent_id IN ($id_list_parents) AND " . $superuser . "_id = $superuser_id)
	AND Telefoonnummer <> '' 
	UNION
	SELECT id, AES_DECRYPT(username, SHA1('$pass')) AS username, AES_DECRYPT(Telefoonnummer, SHA1('$pass')) AS Telefoonnummer FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id = $superuser_id
	AND id IN($id_list_manual)
	AND Telefoonnummer <> ''");
  
  $phones = array();
  while($row_phone = mysqli_fetch_array($result)){
	 
	$phones[] = $row_phone['Telefoonnummer'];
	}
	//output parent_id in a list, to check if this id is a shared account
	$phone_list_iOS = implode(",", $phones);
	$phone_list_iOS = rtrim($phone_list_iOS,',');
	
	$phone_list_Android = implode(";", $phones);
	$phone_list_Android = rtrim($phone_list_Android,';');

	$data['phone_list_iOS'] = $phone_list_iOS;
	$data['phone_list_Android'] = $phone_list_Android;
	
	echo json_encode($data);
	exit;
  	 
}

//HIDE/SHOW FILE
	if(isset ($action) && $action == "hide_document"){
	
	$document_id=mres($_POST['document_id']);
	$hide = mres($_POST['hide']);
	mysqli_query($con,"UPDATE " . $superuser . "s_documents SET hide = $hide WHERE " . $superuser . "_id = $superuser_id AND id = $document_id");
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "hide_document", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}

//LINK FILE TOGETHER
	if(isset ($action) && $action == "add_document_link"){
	
	$document_id=mres($_POST['document_id']);
	$shared_document_id=mres($_POST['shared_document_id']);

	mysqli_query($con, "INSERT INTO " . $superuser . "s_documents_share (document_master_id, document_slave_id, " . $superuser . "_id) VALUES
	($document_id, $shared_document_id, $superuser_id)");	
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "add_document_link", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}

//UNLINK LINKED FILES
	if(isset ($action) && $action == "delete_document_link"){
	
	$document_id=mres($_POST['document_id']);
	$shared_document_id=mres($_POST['shared_document_id']);

	mysqli_query($con, "DELETE FROM " . $superuser . "s_documents_share WHERE " . $superuser . "_id = $superuser_id AND document_master_id = $document_id AND document_slave_id = $shared_document_id");	
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "delete_document_link", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}

//DELETE FILE
	if(isset ($action) && $action == "delete_document"){
	
	$document_id=mres($_POST['document_id']);
	
	//get document information
	$select_query = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $document_id");
	if(mysqli_num_rows($select_query) > 0){ 
	$row = mysqli_fetch_array($select_query);
	
	//delete from table documents
	mysqli_query($con,"DELETE FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $document_id");
	
	//check if link with another file
	mysqli_query($con, "DELETE FROM " . $superuser . "s_documents_share WHERE " . $superuser . "_id = $superuser_id AND document_slave_id = $document_id OR document_master_id = $document_id");
	
	//delete owners from catalogue table
	mysqli_query($con, "DELETE FROM catalogue WHERE " . $superuser . "_id = $superuser_id AND document_id = $document_id");
	
	//check if reused
	$select_duplicate = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE file = '$row[file]' AND " . $superuser . "_id = $superuser_id");	
	if(mysqli_num_rows($select_duplicate) == 0){
	
	//document
	if($row['filetype'] == 0){
	$filename = "../data/" . $superuser . "s_documents/$superuser_id/$row[file]";
	$thumb = "../data/" . $superuser . "s_documents/" . $superuser_id . "/thumb_" . $row['file'];
	if(file_exists($filename)){ 
	unlink($filename);
	}
	if(file_exists($thumb)){ 
	unlink($thumb);
	}
	}
	
	//document
	if($row['filetype'] == 5){

	$filename = "../data/" . $superuser . "s_catalogue/$superuser_id/$row[file]";
	if(file_exists($filename)){ 
	unlink($filename);
	}
	}
	
	//url -> geen upload dus geen unlink
	
	//fotoalbum
	if(($row['filetype'] == 2) && ($row['isURL'] == 0)){ 
	recursiveRemoveDirectory("../data/" . $superuser . "s_images/$superuser_id/$row[file]/");
	}
	
	//video
	if(($row['filetype'] == 3) && ($row['isURL'] == 0)){ 
	$filename = "../data/" . $superuser . "s_videos/$superuser_id/$row[file]";
	if(file_exists($filename)){ 
	unlink($filename);
	}
	}	
	}
	}
	
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "delete_document", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}


//ADD AN EXISTING FILE FROM FILES_LIST TO A CALENDAR ITEM
	if(isset ($action) && $action == "add_old_attachment"){
	
	$document_id = 0;
	$event_id = 0;
	$subcategory_id = 0;
	$recurrent = 0;
	
	
	if(isset($_POST['document_id']) && is_numeric($_POST['document_id']) && !empty($_POST['document_id']) && ($_POST['document_id']) <> 0){ 
		$document_id = mres($_POST['document_id']);
	}
	if(isset($_POST['event_id']) && is_numeric($_POST['event_id']) && !empty($_POST['event_id']) && ($_POST['event_id']) <> 0){ 
		$event_id = mres($_POST['event_id']);
	}
	
	if(isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) && ($_POST['subcategory_id']) <> 0){ 
		$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	}
	
	if(isset($_POST['recurrent']) && is_numeric($_POST['recurrent']) && !empty($_POST['recurrent']) && ($_POST['recurrent']) <> 0){ 
		$recurrent = mres($_POST['recurrent']);
	}
	
	if(isset($_POST['chatbox'])){
		$chatbox =  mres($_POST['chatbox']);
	}

	$date = date('Y-m-d');
	
	//get document information
	$select_query = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $document_id");
	$row = mysqli_fetch_array($select_query);
	$name = mres($row['name']);
	$doc = $row['file'];
	$filetype = $row['filetype'];
	$isURL = $row['isURL'];
	$comment = mres($row['comment']);
	
	if($recurrent == 0){
	mysqli_query($con,"INSERT INTO " . $superuser . "s_documents (date,name,file," . $superuser . "_id,comment,subcategory,event_id,permanent,reused,filetype,isURL) VALUES ('$date','$name','$doc','$superuser_id','$comment', '$subcategory_id','$event_id','0','1','$filetype','$isURL')");
	
	//check if linked to other documents
	$check_link = mysqli_query($con, "SELECT document_slave_id FROM " . $superuser . "s_documents_share WHERE " . $superuser . "_id = $superuser_id AND document_master_id = $document_id");
	if(mysqli_num_rows($check_link) > 0){
		foreach($check_link AS $row_link){
			$select_query = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $row_link[document_slave_id]");
			$row = mysqli_fetch_array($select_query);
			$name = mres($row['name']);
			$doc = $row['file'];
			$filetype = $row['filetype'];
			$isURL = $row['isURL'];

			mysqli_query($con,"INSERT INTO " . $superuser . "s_documents (date,name,file," . $superuser . "_id,subcategory,event_id,permanent,reused,filetype,isURL) VALUES ('$date','$name','$doc','$superuser_id','$subcategory_id','$event_id','0','1','$filetype','$isURL')");
			
		}
	}
	
	}
	if($recurrent == 1){ 
	//get all events in recurrent activity
	$sql_rec = mysqli_query($con, "SELECT repeat_id FROM calendar WHERE id = $event_id AND repeat_id <> 0 AND " . $superuser . "_id = $superuser_id");
	if(mysqli_num_rows($sql_rec) > 0){
		$row_rec = mysqli_fetch_array($sql_rec);
		$sql_events = mysqli_query($con, "SELECT id FROM calendar WHERE repeat_id = $row_rec[repeat_id] AND " . $superuser . "_id = $superuser_id");
		if(mysqli_num_rows($sql_events) > 0){
			while($row_event = mysqli_fetch_array($sql_events)){
			mysqli_query($con,"INSERT INTO " . $superuser . "s_documents (date,name,file," . $superuser . "_id,subcategory,event_id,permanent,reused,filetype,isURL) VALUES ('$date','$name','$doc','$superuser_id','$subcategory_id','$row_event[id]','0','1','$filetype','$isURL')");
			
			//check if linked to other documents
			$check_link = mysqli_query($con, "SELECT document_slave_id FROM " . $superuser . "s_documents_share WHERE " . $superuser . "_id = $superuser_id AND document_master_id = $document_id");
			if(mysqli_num_rows($check_link) > 0){
				foreach($check_link AS $row_link){
					$select_query = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $row_link[document_slave_id]");
					$row = mysqli_fetch_array($select_query);
					$name = mres($row['name']);
					$doc = $row['file'];
					$filetype = $row['filetype'];
					$isURL = $row['isURL'];
		
					mysqli_query($con,"INSERT INTO " . $superuser . "s_documents (date,name,file," . $superuser . "_id,subcategory,event_id,permanent,reused,filetype,isURL) VALUES ('$date','$name','$doc','$superuser_id','$subcategory_id','$row_event[id]','0','1','$filetype','$isURL')");
			
		}
	}
			
				
			}
		}
	}
	}	
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "add_old_attachment", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}

//DELETE CHAT ENTRY
if(isset ($action) && $action == "DeleteAnswer"){

  $answer_id=mres($_POST['answer_id']);
  
  //check for picture or document
  $check = mysqli_query($con, "SELECT * FROM chat WHERE id = $answer_id");
  if(mysqli_num_rows($check) > 0){
  $row = mysqli_fetch_array($check);
  
  //check if a picture exists
  if($row['pic'] <> ""){
	  if($row['club_id'] <> "0"){ 
	  $picture = "../data/clubs_images/$row[club_id]/chat/$row[pic]";
	  unlink($picture);
	  $thumb = "../data/clubs_images/$row[club_id]/chat/thumb_$row[pic]";
	  unlink($thumb);
	  }
  }
  
  //check if document exists and is not permanent
  if($row['document_id'] <> 0){
  	$sql_document = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $row[document_id] AND permanent = 0");
	  if(mysqli_num_rows($sql_document) > 0){ 
	  $row_document = mysqli_fetch_array($sql_document);
	  mysqli_query($con,"DELETE FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $row[document_id] AND permanent = 0");
	  
	  //check if reused
	  $select_duplicate = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE file = '$row_document[file]' AND " . $superuser . "_id = $superuser_id");	
	  if(mysqli_num_rows($select_duplicate) == 0){
	  //document
	  if($row_document['filetype'] == 0){ 
	  $filename = "../data/" . $superuser . "s_documents/$superuser_id/$row_document[file]";
	  $thumb = "../data/" . $superuser . "s_documents/" . $superuser_id . "/thumb_" . $row_document['file'];
	  if(file_exists($filename)){ 
	  unlink($filename);
	  }
	  if(file_exists($thumb)){ 
	  unlink($thumb);
	  }
	  }
	  }
	  }
	  }
  }

  mysqli_query($con,"DELETE FROM chat WHERE id = $answer_id AND " . $superuser . "_id = $superuser_id");
  mysqli_query($con,"DELETE FROM chat_read WHERE chat_id = $answer_id");
  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "DeleteAnswer", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	 
}

//ADD A MEMBER MANUALLY
if(isset ($action) && $action == "insertMembers"){
	
  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  $name=mres($_POST['name']);
  $surname=mres($_POST['surname']);
  
  if(isset($_POST['member_instrument'])){
	  $member_instrument=mres($_POST['member_instrument']);
  }
  else {
	  $member_instrument="0";
  }
  
  if(isset($_POST['member_number'])) {
  $member_number=mres($_POST['member_number']);
  }
  else {
  $member_number="";
  }

  if(isset($_POST['member_category'])) {
  $member_category=mres($_POST['member_category']);
  }
  else {
  $member_category="";
  }
  
  if(isset($_POST['member_medical'])) {
  $member_medical=mres($_POST['member_medical']);
  }
  else {
  $member_medical="";
  }
  
  if(isset($_POST['member_notes'])) {
  $member_notes=mres($_POST['member_notes']);
  }
  else {
  $member_notes="";
  }
  
  if(isset($_POST['member_emergency'])) {
  $member_emergency=mres($_POST['member_emergency']);
  }
  else {
  $member_emergency="";
  }


  $adult = 0;
  if(isset($_POST['adult'])) {
	  $adult = 1;
	  }
  
  $parent=$_POST['parent'];
  if($parent == ""){
	  $parent = $name . " " . $surname;
  }
  else {
	  $parent=$_POST['parent'];
  }
  
  //home address
	if(isset($_POST['street'])){ 
	$street = mres($_POST['street']);
	}
	else {
	$street = "";
	}
	
	if(isset($_POST['number'])){ 
	$number = mres($_POST['number']);
	}
	else {
	$number = "";
	}
	
	if(isset($_POST['postal'])){ 
	$postal = mres($_POST['postal']);
	}
	else {
	$postal = "";
	}
	
	if(isset($_POST['city'])){ 
	$city = mres($_POST['city']);
	}
	else {
	$city = "";
	}
	
	if(isset($_POST['street2'])){ 
	$street2 = mres($_POST['street2']);
	}
	else {
	$street2 = "";
	}
	
	if(isset($_POST['number2'])){ 
	$number2 = mres($_POST['number2']);
	}
	else {
	$number2 = "";
	}
	
	if(isset($_POST['postal2'])){ 
	$postal2 = mres($_POST['postal2']);
	}
	else {
	$postal2 = "";
	}
	
	if(isset($_POST['city2'])){ 
	$city2 = mres($_POST['city2']);
	}
	else {
	$city2 = "";
	}
	
	if(isset($_POST['phone'])){ 
	$phone = mres($_POST['phone']);
	}
	else {
	$phone = "";
	}
	
	if(isset($_POST['phone2'])){ 
	$phone2 = mres($_POST['phone2']);
	}
	else {
	$phone2 = "";
	}

  
  $email=mres($_POST['email']);
  $key_code = mt_rand();
  
if(isset($_POST['ApproveFace'])) {
  $approveface = 1;
  $timestamp = time();
  }
elseif(isset($_POST['NoApproveFace'])) {
	  $approveface = 0;
	  $timestamp = time();
	  }
else {
$approveface = 0;
$timestamp = 0;
}

  $insert = mysqli_query($con,"INSERT INTO kids_" . $superuser . "s_manual 
  (key_code,
  name,
  surname,
  username, 
  email,
  Telefoonnummer,
  Telefoonnummer2,
  " . $superuser . "_id,
  subcategory,
  member_number,
  member_category,
  member_medical_x,
  member_emergency_x,
  member_notes_x,
  member_instrument,
  adult,
  ApproveFace,
  ApproveFace_timestamp,
  mark) VALUES 
  ('$key_code', AES_ENCRYPT('$name', SHA1('$pass')),AES_ENCRYPT('$surname', SHA1('$pass')),AES_ENCRYPT('$parent', SHA1('$pass')),AES_ENCRYPT('$email', SHA1('$pass')),AES_ENCRYPT('$phone', SHA1('$pass')),AES_ENCRYPT('$phone2', SHA1('$pass')),'$superuser_id','$subcategory_id', '$member_number','$member_category',AES_ENCRYPT('$member_medical', SHA1('$pass')),AES_ENCRYPT('$member_emergency', SHA1('$pass')),AES_ENCRYPT('$member_notes', SHA1('$pass')),'$member_instrument','$adult','$approveface','$timestamp','0')");
  
  if(!$insert){
	  //echo "fout: " . mres(mysqli_error($con));
  }
  
  $last_id = mysqli_insert_id($con);
  
  if($last_id <> 0){
  
  //insert home address in table address
  mysqli_query($con,"INSERT INTO address (manual_id_" . $superuser . ", key_code_" . $superuser . ", street, number, postal, city, street2, number2, postal2, city2) VALUES 
		($last_id, $key_code, AES_ENCRYPT('$street', SHA1('$pass')),AES_ENCRYPT('$number', SHA1('$pass')),AES_ENCRYPT('$postal', SHA1('$pass')),AES_ENCRYPT('$city', SHA1('$pass')), AES_ENCRYPT('$street2', SHA1('$pass')),AES_ENCRYPT('$number2', SHA1('$pass')),AES_ENCRYPT('$postal2', SHA1('$pass')),AES_ENCRYPT('$city2', SHA1('$pass')))");
	
  }
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "insertMembers", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}


//UPDATE A MANUALLY ADDED MEMBER
if(isset ($action) && $action == "updateMembers"){
	
  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  $kid_id=mres($_POST['kid_id']);
  $parent_id=mres($_POST['parent_id']);	
  $manual_id=mres($_POST['manual_id']);	
  
  $name = "";
  $surname = "";
  $member_number="";
  $member_category = "";
  $member_instrument = "";
  $member_medical = "";
  $member_notes = "";
  $street = "";
  $number = "";
  $postal = "";
  $city = "";
  $street2 = "";
  $number2 = "";
  $postal2 = "";
  $city2 = "";
  $phone = "";
  $phone2 = "";
  
  if(isset($_POST['name'])) $name=mres($_POST['name']);
  if(isset($_POST['surname'])) $surname=mres($_POST['surname']);
  if(isset($_POST['member_number'])) $member_number=mres($_POST['member_number']);
  if(isset($_POST['member_category'])) $member_category=mres($_POST['member_category']);
  if(isset($_POST['member_instrument'])) $member_instrument=mres($_POST['member_instrument']);
  if(isset($_POST['member_medical'])) $member_medical=mres($_POST['member_medical']);
  if(isset($_POST['member_notes'])) $member_notes=mres($_POST['member_notes']);
  
  if($manual_id <> 0){
  $parent=mres($_POST['parent']);
  if($parent == ""){
	  $parent = $name . " " . $surname;
  }
  else {
	  $parent=mres($_POST['parent']);
  }
  
  
  //home address
  	if(isset($_POST['street'])) $street = mres($_POST['street']);
  	if(isset($_POST['number'])) $number = mres($_POST['number']);
  	if(isset($_POST['postal'])) $postal = mres($_POST['postal']);
	if(isset($_POST['city'])) $city = mres($_POST['city']);
	if(isset($_POST['street2'])) $street2 = mres($_POST['street2']);
	if(isset($_POST['number2'])) $number2 = mres($_POST['number2']);
	if(isset($_POST['postal2'])) $postal2 = mres($_POST['postal2']);
	if(isset($_POST['city2'])) $city2 = mres($_POST['city2']);
	if(isset($_POST['phone'])) $phone = mres($_POST['phone']);
	if(isset($_POST['phone2'])) $phone2 = mres($_POST['phone2']);
	
  $email=mres($_POST['email']); //obligated
  $key_code = $_POST['key_code'];
  }
  
  
  if(isset($_POST['adult'])) {
	  $adult = 1;
	  }
	  else {
		  $adult = 0;
	  }

if(isset($_POST['ApproveFace'])) {
	  $approveface = 1;
	  $timestamp = time();
	  }
elseif(isset($_POST['NoApproveFace'])) {
	  $approveface = 0;
	  $timestamp = time();
	  }
else {
$approveface = 0;
$timestamp = 0;
}

  if($manual_id <> 0){
  $update = mysqli_query($con,"UPDATE kids_" . $superuser . "s_manual SET 
  name = AES_ENCRYPT('$name', SHA1('$pass')),
  surname = AES_ENCRYPT('$surname', SHA1('$pass')),
  username = AES_ENCRYPT('$parent', SHA1('$pass')),
  email = AES_ENCRYPT('$email', SHA1('$pass')),
  Telefoonnummer = AES_ENCRYPT('$phone', SHA1('$pass')),
  Telefoonnummer2 = AES_ENCRYPT('$phone2', SHA1('$pass')),
  member_instrument = '$member_instrument',
  member_number = '$member_number',
  member_category = '$member_category',
  member_medical_x = AES_ENCRYPT('$member_medical', SHA1('$pass')),
  member_notes_x = AES_ENCRYPT('$member_notes', SHA1('$pass')),
  adult = '$adult',
  ApproveFace = '$approveface',
  ApproveFace_timestamp = '$timestamp'
  WHERE key_code = $key_code AND " . $superuser . "_id = $superuser_id
  ");
  
  //check if exists
  $key_code = userValueManual($manual_id, $superuser, $superuser_id, "key_code");
  $sql = mysqli_query($con, "SELECT * FROM address WHERE key_code_" . $superuser . " = $key_code");
  if(mysqli_num_rows($sql) > 0){ 
  //update database address
  mysqli_query($con,"UPDATE address SET 
		street = AES_ENCRYPT('$street', SHA1('$pass')),
		number = AES_ENCRYPT('$number', SHA1('$pass')),
		postal = AES_ENCRYPT('$postal', SHA1('$pass')),
		city = AES_ENCRYPT('$city', SHA1('$pass')),
		street2 = AES_ENCRYPT('$street2', SHA1('$pass')),
		number2 = AES_ENCRYPT('$number2', SHA1('$pass')),
		postal2 = AES_ENCRYPT('$postal2', SHA1('$pass')),
		city2 = AES_ENCRYPT('$city2', SHA1('$pass'))
		WHERE manual_id_" . $superuser . " = $manual_id");
  }
  else {
	mysqli_query($con,"INSERT INTO address (manual_id_" . $superuser . ", key_code_" . $superuser . ", street, number, postal, city, street2, number2, postal2, city2) VALUES 
		($manual_id, $key_code, AES_ENCRYPT('$street', SHA1('$pass')),AES_ENCRYPT('$number', SHA1('$pass')),AES_ENCRYPT('$postal', SHA1('$pass')),AES_ENCRYPT('$city', SHA1('$pass')), AES_ENCRYPT('$street2', SHA1('$pass')),AES_ENCRYPT('$number2', SHA1('$pass')),AES_ENCRYPT('$postal2', SHA1('$pass')),AES_ENCRYPT('$city2', SHA1('$pass')))");  
  }
  }
  if($kid_id <> 0){
  //update table
  $update = mysqli_query($con,"UPDATE kids_" . $superuser . "s SET 
  member_number = '$member_number',
  member_category = '$member_category',
  member_notes_x = AES_ENCRYPT('$member_notes', SHA1('$pass'))
  WHERE kid_id = $kid_id AND
  " . $superuser . "_id = $superuser_id
  ");
  
   $update_kidsname = mysqli_query($con,"UPDATE kids SET 
	name = AES_ENCRYPT('$name', SHA1('$pass')),
	surname = AES_ENCRYPT('$surname', SHA1('$pass'))
	WHERE id='$kid_id'");
  }
  if($parent_id <> 0){
  //update database
  $update = mysqli_query($con,"UPDATE parents_" . $superuser . "s SET 
  member_number = '$member_number',
  member_category = '$member_category',
  member_notes_x = AES_ENCRYPT('$member_notes', SHA1('$pass'))
  WHERE parent_id = $parent_id AND
  " . $superuser . "_id = $superuser_id
  ");

  }
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "updateMembers", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
 }
 

//SEND MAIL TO MEMBERS (INCL QUEUE SYSTEM)
if(isset ($action) && $action == "MailToMembers"){

	//1. obtain variables
	//from
	if(isset($_POST["mailfrom"])){
		$from = mres($_POST["mailfrom"]);
	}
	else {
		$from = "0";
	}
	if($from == "0"){
		$reply_granted = 0;
	}
	else {
		$reply_granted = 1;
	}

	//to
	$subcategory_id = sres($_POST["subcategory_id"], $superuser_id, $superuser);

	//check if mail to all (board) or not
	if((isset($_POST['board'])) && ($_POST['board'] == "1")){
		$id_list_subgroups = $_POST["id_list_subgroups"];
		$board = "1";
		$ForAll = "2";
	}
	else {
		$id_list_subgroups = "0";
		$board = "0";
		$ForAll = "0";
	}

	if(isset($_POST["id_list_manual"]) && !empty($_POST["id_list_manual"])){
		$id_list_manual = $_POST["id_list_manual"];
	}
	else {
		$id_list_manual = 0;
	}
	if(isset($_POST["id_list_kids"]) && !empty($_POST["id_list_kids"])){
		$id_list_kids = $_POST["id_list_kids"];
	}
	else {
		$id_list_kids = 0;
	}
	if(isset($_POST["id_list_adults"]) && !empty($_POST["id_list_adults"])){
		$id_list_adults = $_POST["id_list_adults"];
	}
	else {
		$id_list_adults = 0;
	}

	//who created the mail
	$logged_in_user = userValue(null, "id");

	//permanently save attachments?
	$Perm = $_POST["MakePerm"];

	//subjects
	if((isset($_POST['subject'])) && ($_POST['subject'] != "")){
		$subject = mres($_POST['subject']);
	}
	else {
		$subject = "Bericht van " . $superuser_name . " via " . $appname;
	}

	//send mail on a later date? 0 = now, 1 = later, 2 = now and later
	$laterdate = $_POST["laterdate"];

	//now or later
	$senddate = $_POST["senddate"];
	
	//what if user sets laterdate = 1 or 2 and puts future senddate to today (11052021)
	if($laterdate <> 0 && $senddate == date('Y-m-d')){
		$laterdate = 0;
	}

	//title in mailbody
	$message_title = "Bericht van " . $superuser_name . " via " . $appname;

	//send copy?
	if(isset($_POST['AddBCC'])){
		$ListBCC = $_POST['AddBCC'];
	}


	//2. update database mails
	//variables
	
	//check if queue or not
	$rec_1 = CountRecipients($id_list_kids, $id_list_adults, $id_list_manual, $superuser_id, $superuser, $subcategory_id);
	$count_BCC = 0;
	if(!empty($ListBCC)){
		$list_BCC = explode(',', $ListBCC);
		$count_BCC = count($list_BCC);
	}
	$queue = 0;
	if($rec_1 + $count_BCC > $max_mails_direct){
		$queue = 1;
	}
	
	require_once 'plugins/HTMLPurifier/HTMLPurifier.auto.php';
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	$dirty_html = $_POST["message"]; //mres niet nodig
	$message = $purifier->purify($dirty_html);
	$message_database = mres($message);

	if((isset($_POST['button_id'])) && ($_POST['button_id'] != "")){
		$button_id = mres($_POST['button_id']);
		//get more information paybutton
		$check_paybuttons = mysqli_query($con, "SELECT * FROM paybuttons
  WHERE id = $button_id AND " . $superuser . "_id = $superuser_id");
		if(mysqli_num_rows($check_paybuttons) > 0){
			$row_paybuttons = mysqli_fetch_array($check_paybuttons);
			$paybuttons = 1;
			$title = $row_paybuttons['title'];
			$type = $row_paybuttons['type'];
		}
		else {
			$paybuttons = 0;
			$button_id = 0;
		}
	}
	else {
		$paybuttons = 0;
		$button_id = 0;
	}
	
	if((isset($_POST['survey_id'])) && ($_POST['survey_id'] != "")){
		$survey_id = mres($_POST['survey_id']);
		//get more information paybutton
		$check_survey = mysqli_query($con, "SELECT * FROM survey_questions
  WHERE id = $survey_id AND " . $superuser . "_id = $superuser_id");
		if(mysqli_num_rows($check_survey) > 0){
			$row_survey = mysqli_fetch_array($check_survey);
			$survey = 1;
			$question = $row_survey['question'];
		}
		else {
			$survey = 0;
			$survey_id = 0;
		}
	}
	else {
		$survey = 0;
		$survey_id = 0;
	}


	//if edit mail
	if(isset($_POST['mail_id'])){
		$mail_id = $_POST['mail_id'];
		//edited mail: update database mails
		$update = mysqli_query($con,"UPDATE mails SET id_list_subgroups = '$id_list_subgroups', id_list_kids = '$id_list_kids', id_list_adults = '$id_list_adults', id_list_manual = '$id_list_manual', laterdate = '$laterdate', senddate = '$senddate', mailfrom = '$from', BCC = '$ListBCC', from_id = '$logged_in_user', subject = '$subject', message = '$message_database', paybutton = '$button_id', survey = '$survey_id', board = '$board', queue = '$queue', rec_1 = '$rec_1' WHERE id = $mail_id AND " . $superuser . "_id = $superuser_id");
		if(!$update){
			error_log("Error description: " . mysqli_error($con), 0);
		}
	}
	else {
		//new mail: import into database mails
		$insert = mysqli_query($con,"INSERT INTO mails (id_list_subgroups,id_list_kids,id_list_adults,id_list_manual,subcategory_id," . $superuser . "_id,laterdate,senddate,mailfrom,BCC,from_id,subject,message,paybutton,survey,board,queue, rec_1) VALUES ('$id_list_subgroups','$id_list_kids','$id_list_adults','$id_list_manual',$subcategory_id,$superuser_id,'$laterdate','$senddate','$from','$ListBCC', '$logged_in_user','$subject','$message_database','$button_id','$survey_id','$board', '$queue', '$rec_1')");
		$mail_id = mysqli_insert_id($con);
		if(!$insert){
			error_log("Error description: " . mysqli_error($con), 0);
		}
	}

	//upload or reuse attachments
	$date = date("Y-m-d");

	//3.1.1 Check if a planned mail is edited
	if(isset($_POST['edited']) && ($_POST['edited'] == 1)){
		//user kept at least one attachment checked
		if(isset($_POST['ReuseAttachments']) && !empty($_POST['ReuseAttachments'])){
			$ReuseAttachments = mres($_POST['ReuseAttachments']);
			$string_attach = "AND id NOT IN ($ReuseAttachments)";
		}
		if(empty($_POST['ReuseAttachments'])){
			$string_attach = "";
		}
		//get unchecked attachment
		$sql_attach = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND mail_id = $mail_id $string_attach");
		$numrows = mysqli_num_rows($sql_attach);
		if($numrows > 0)
		{
			while($row_attach = mysqli_fetch_array($sql_attach)){
				//delete from table documents
				mysqli_query($con,"DELETE FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $row_attach[id] AND mail_id = $mail_id");

				//check if file is used more than once in database: i.e. stil found after delete
				$select_duplicates = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND name='$row_attach[name]' AND file = '$row_attach[file]'");
				if(mysqli_num_rows($select_duplicates) == 0){
					//document
					if($row_attach['filetype'] == 0){
						$filename = "../data/" . $superuser . "s_documents/$superuser_id/$row_attach[file]";
						if(file_exists($filename)){
							unlink($filename);
						}
					}
				}
			}
		}
	}
	else {
		//3.1.2 Check for reused attachments (mail_reuse)
		if(isset($_POST['ReuseAttachments']) && !empty($_POST['ReuseAttachments'])){
			$ReuseAttachments = mres($_POST['ReuseAttachments']);
			$sql_attach = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id IN ($ReuseAttachments)");
			$numrows = mysqli_num_rows($sql_attach);
			if($numrows > 0)
			{
				while($row_attach = mysqli_fetch_array($sql_attach)){
					mysqli_query($con,
						"INSERT INTO " . $superuser . "s_documents (date, name, file, " . $superuser . "_id, mail_id, subcategory)
		VALUES ('$date', '$row_attach[name]', '$row_attach[file]', '$superuser_id', '$mail_id', '$subcategory_id')");
				}
			}
		}
	}


	//3.2 Upload optional new attachments to server
	$timestamp = time();

	//upload if not empty
	if (!empty($_FILES['images'])) {
		//echo json_encode(['error'=>'No files found for upload.']);
		// or you can throw an exception
		//return; // terminate

		// get the files posted
		$images = $_FILES['images'];

		// a flag to see if everything is ok
		$success = null;

		// file paths to store
		$paths= [];

		// get file names
		$filenames = $images['name'];

		// loop and process files
		for($i=0; $i < count($filenames); $i++){
			$ext = pathinfo($filenames[$i], PATHINFO_EXTENSION);
			$target = str_replace(' ', '_', "../data/" . $superuser . "s_documents/" . $superuser_id . "/" . $timestamp . "_" . $superuser_id . "_" . $subcategory_id .  "_" . $filenames[$i]);
			$doc = str_replace(' ', '_',  $timestamp . "_" . $superuser_id . "_" . $subcategory_id . "_" . $filenames[$i]);
			if(move_uploaded_file($images['tmp_name'][$i], $target)) {
				$success = true;
				$paths[] = $target;

				mysqli_query($con,"INSERT INTO " . $superuser . "s_documents (date,name,file," . $superuser . "_id,subcategory,mail_id,forall,forsome,permanent) VALUES ('$date','$filenames[$i]','$doc','$superuser_id','$subcategory_id','$mail_id','$ForAll', '$id_list_subgroups', '$Perm')");

			} else {
				$success = false;
				break;
			}
		}

		// check and process based on successful status
		if ($success === true) {

			$output = [];
			// for example you can get the list of files uploaded this way
			// $output = ['uploaded' => $paths];
			//check if new mail or edited mail
			if(isset($_POST['mail_id'])){
				mysqli_query($con,"UPDATE " . $superuser . "s_documents SET permanent = '$Perm' WHERE mail_id = '$mail_id' AND " . $superuser . "_id = '$superuser_id'");
			}

		} elseif ($success === false) {
			$output = ['error'=>'Error while uploading images. Contact the system administrator'];
			// delete any uploaded files
			foreach ($paths as $file) {
				unlink($file);
			}
		} else {
			$output = ['error'=>'No files were processed.'];
		}
	}
	else {
		//check if new mail or edited mail
		if(isset($_POST['mail_id'])){
			mysqli_query($con,"UPDATE " . $superuser . "s_documents SET permanent = '$Perm' WHERE mail_id = '$mail_id' AND " . $superuser . "_id = '$superuser_id'");
		}
		$output = [];
	}
	// return a json encoded response for plugin to process successfully
	echo json_encode($output);


	//4. send mail now if laterdate is 0 or 2 AND queue is 0
	if($laterdate <> 1 && $queue <> 1){
		
		$sql_mail = mysqli_query($con, "SELECT * FROM mails WHERE id = $mail_id AND " . $superuser . "_id = $superuser_id");
		$row_mail = mysqli_fetch_array($sql_mail);
		
		SendBulkMail($row_mail, 0); //second argument 0 = no cronjob
		
	}
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "MailtoMembers", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}


//DELETE MAIL ATTACHMENT
	if(isset ($action) && $action == "delete_attachment"){
	
	$attach_id=mres($_POST['attach_id']);
	$mail_id=mres($_POST['mail_id']);
	
	//get document information
	$select_query = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $attach_id AND mail_id = $mail_id");
	$row = mysqli_fetch_array($select_query);
		
	//delete from table documents
	mysqli_query($con,"DELETE FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $attach_id AND mail_id = $mail_id");
	
	//check if file is used more than once in database: i.e. stil found after delete
	$select_duplicates = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND name='$row[name]' AND file = '$row[file]'");
	if(mysqli_num_rows($select_duplicates) == 0){ 
	//document
	if($row['filetype'] == 0){ 
	$filename = "../data/" . $superuser . "s_documents/$superuser_id/$row[file]";
	if(file_exists($filename)){
	unlink($filename);	
	}
	}
	}
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "delete_attachment", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}


//DELETE A MAIL
	if(isset ($action) && $action == "remove_mail"){
	
	$mail_id=mres($_POST['mail_id']);
	$mailtype=mres($_POST['mailtype']); 
	
	//cancel planned mail calendar
	if($mailtype == 1){
		mysqli_query($con, "UPDATE calendar SET delay_type = 0 WHERE id = $mail_id AND " . $superuser . "_id = $superuser_id");
	}
	
	//manual mails
	if($mailtype == 0){
	//if user clicks 'annuleren' on reuse
	if(isset($_POST['annul'])){
		$annul = mres($_POST['annul']);
	}
	else {
		$annul = 0;
	}
	
	$check_attachments = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND mail_id = $mail_id AND permanent = 0");
	if(mysqli_num_rows($check_attachments) > 0){
	foreach($check_attachments AS $row){
	$attach_id=$row['id'];
	$name = $row['name'];
	$file = $row['file'];
		
	//delete from table documents
	mysqli_query($con,"DELETE FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $attach_id AND mail_id = $mail_id");
	
	//check if file is used more than once in database: i.e. stil found after delete
	$select_duplicates = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND name='$name' AND file = '$file'");
	if(mysqli_num_rows($select_duplicates) == 0){ 
	//document
	if($row['filetype'] == 0){ 
	$filename = "../data/" . $superuser . "s_documents/$superuser_id/$row[file]";
	if(file_exists($filename)){
	unlink($filename);
	}	
	}
	}
	}	
	}
	
	if($annul == 0){ 
	//delete mail - 250619: put 'trash' to 1 to remain in statistics
	mysqli_query($con,"UPDATE mails SET trash = 1 WHERE id = $mail_id AND " . $superuser . "_id = $superuser_id");
	}
	else {
	mysqli_query($con,"DELETE FROM mails WHERE id = $mail_id AND " . $superuser . "_id = $superuser_id");
	}
	//delete mail read
	//mysqli_query($con,"DELETE FROM mail_read WHERE mail_id = $mail_id AND " . $superuser . "_id = $superuser_id");
	//delete mail errors
	//mysqli_query($con,"DELETE FROM mails_errors WHERE mail_id = $mail_id AND " . $superuser . "_id = $superuser_id");
}
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "remove_mail", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}



//IMPORT ICAL
if(isset ($action) && $action == "ImportActivities"){
require 'plugins/iCalReader/class.iCalReader.php';

$sync_url = mres($_POST['sync_url']);
$sync_url = str_replace( 'webcal://', 'http://', $sync_url );
$sync_url = addhttp($sync_url);

$sync_method = mres($_POST['sync_method']);
$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
$repeat_id = mt_rand();
$now = date('Y-m-d H:i:s');//current date and time
$today = date('Y-m-d');
$title = mres($_POST['title']);

$stmt_insert = $con->prepare("INSERT INTO calendar (" . $superuser . "_id, subcategory, title, startdate, enddate, subject, location, repeat_id, sync_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$ical   = new ICal($sync_url);
//show only events from now to January the 29th, 2038 (UNIX timestamp)
$events = $ical->eventsFromRange(true, true);

if($ical->event_count <> 0){
foreach ($events as $event) {
	
	if(($sync_method == "Google")){
	if(isset($event['DTSTART']) && (isset($event['DTEND']))){ 
	$startdate = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTSTART']));
	$enddate = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTEND']));

    $summary = mres($event['SUMMARY']);
    $summary = str_replace('\\', '', $summary); 
    $summary = mres($summary);
    $uid = mres($event['UID']);
    
    if(isset($event['LOCATION'])){
    $location = $event['LOCATION'];
    $location = str_replace('\\', '', $location); 
    $location = mres($location);
    }
    else {
	    $location = "";
    }
        
    $stmt_insert->bind_param("iisssssis", $superuser_id, $subcategory_id, $title, $startdate, $enddate, $summary, $location, $repeat_id, $uid);
$stmt_insert->execute();

	}
	}

}
}
LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "ImportActivities", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//SYNC APP CALENDAR WITH A WEBCAL CALENDAR
if(isset ($action) && $action == "AddSync"){

require 'plugins/iCalReader/class.iCalReader.php';

$sync_url = mres($_POST['sync_url']);
$sync_url = str_replace( 'webcal://', 'http://', $sync_url);

if (!preg_match("~^(?:f|ht)tps?://~i", $sync_url)) {
	$sync_url = "https://" . $sync_url;
}
//if http added, convert to https
$sync_url = str_replace("http://", "https://", $sync_url);

$sync_method = mres($_POST['sync_method']);
$subcategory_id = mres($_POST['subcategory_id']);
$repeat_id = mt_rand();
$now = date('Y-m-d H:i:s');//current date and time
$today = date('Y-m-d');

if(isset($_POST['title'])){ 
$title = mres($_POST['title']);
}
else {
	$title = strtoupper($superuser) . "1";
}


//update constants (table categories_sync)
$check_constants = mysqli_query($con,"SELECT * FROM " . $superuser . "s_categories_sync WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND sync_method = '$sync_method' AND sync_url = '$sync_url'");

//manual sync
if(mysqli_num_rows($check_constants) > 0){
mysqli_query($con,"UPDATE " . $superuser . "s_categories_sync SET timestamp ='$now', repeat_id = '$repeat_id' WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND sync_method = '$sync_method' AND sync_url = '$sync_url'");
$row_constants = mysqli_fetch_array($check_constants);
$title = $row_constants['title'];
}
else {
mysqli_query($con,"INSERT INTO " . $superuser . "s_categories_sync (timestamp, " . $superuser . "_id, subcategory_id, title, sync_method, sync_url, repeat_id) VALUES
('$now', $superuser_id, $subcategory_id, '$title', '$sync_method', '$sync_url', $repeat_id)");

}

$ical   = new ICal($sync_url);
//show only events from now to January the 29th, 2038 (UNIX timestamp)
$events = $ical->eventsFromRange(true, true);

if($ical->event_count <> 0){
foreach ($events as $event) {
	
	if(($sync_method == "KBVB") || ($sync_method == "badmintonvlaanderen") || ($sync_method == "hockeybelgium")){
	
	$matchdatestart = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTSTART']));
    $matchdateend = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTEND']));
    
	if(isset($event['LOCATION'])){
	$location = mres($event['LOCATION']);
    }
	else {
		$location = "";
	}

    $summary = mres($event['SUMMARY']);
    
    if($sync_method == "hockeybelgium"){ //ics in hockey belgium changes (not constant)
    $uid = str_replace(' ', '', $event['SUMMARY']);
    $uid = mres($uid);
    }
    else {
	$uid = mres($event['UID']); 
    }

	//check if entries in calendar already exist
	$check_duplicate = mysqli_query($con,"SELECT id FROM calendar WHERE " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id AND sync_id = '$uid' AND DATE(startdate) >= '$today'");
	 
	 if(mysqli_num_rows($check_duplicate) > 0){
	 $duplicate_row = mysqli_fetch_array($check_duplicate);
	 mysqli_query($con,"UPDATE calendar SET startdate = '$matchdatestart', enddate = '$matchdateend', location = '$location', repeat_id = $repeat_id WHERE id = $duplicate_row[id]");	
	 }
	 else {
	 //not existing: insert
	 mysqli_query($con,"INSERT INTO calendar (" . $superuser . "_id, subcategory, title, startdate, enddate, location, comment, repeat_id, sync_id) VALUES
	($superuser_id, $subcategory_id, '$title', '$matchdatestart', '$matchdateend', '$location', '$summary', $repeat_id, '$uid')");
	}
	}
	
	if($sync_method == "volleyscores"){
	
	//add 2 hours (zie mail kevin degryse van volleyscores): nope, had met timezone te maken (Z) achter tijd
	$matchdatestart = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTSTART']));
    $matchdateend = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTEND']));
    
    $summary = mres($event['SUMMARY']);
    $uid = mres($event['UID']);
    $location = mres($event['LOCATION']);

	//check if entries in calendar already exist
	$check_duplicate = mysqli_query($con,"SELECT id FROM calendar WHERE " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id AND sync_id = '$uid' AND DATE(startdate) >= '$today'");
	 
	 if(mysqli_num_rows($check_duplicate) > 0){
	 $duplicate_row = mysqli_fetch_array($check_duplicate);
	 mysqli_query($con,"UPDATE calendar SET startdate = '$matchdatestart', enddate = '$matchdateend', location = '$location', repeat_id = $repeat_id WHERE id = $duplicate_row[id]");	
	 }
	 else {
	 //not existing: insert
	 mysqli_query($con,"INSERT INTO calendar (" . $superuser . "_id, subcategory, title, startdate, enddate, location, comment, repeat_id, sync_id) VALUES
	($superuser_id, $subcategory_id, '$title', '$matchdatestart', '$matchdateend', '$location', '$summary', $repeat_id, '$uid')");
	}
	}
}
}

LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "AddSync", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}

//REMOVE SYNC BETWEEN APP CALENDAR AND A WEBCAL
if(isset ($action) && $action == "RemoveSync"){

$sync_id = mres($_POST['sync_id']);
$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
$repeat_id = mres($_POST['repeat_id']);

//first delete all calendar items with same repeat_id
mysqli_query($con,"DELETE FROM calendar WHERE " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id AND  DATE(startdate) > CURDATE() AND repeat_id = $repeat_id");

//then delete sync in table categories_sync
mysqli_query($con,"DELETE FROM " . $superuser . "s_categories_sync WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND id = $sync_id");
LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "RemoveSync", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}


//ADD A SURVEY QUESTION
	if(isset ($action) && $action == "AddQuestion"){
	
	$event_id=mres($_POST['event_id']);
	$subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
	$question=mres($_POST['question']);
	$description=mres($_POST['description']);
	$enddate = mres($_POST['enddate']);
	$exclusive = mres($_POST['exclusive']);
	
	$ForAll = 0;
	if(isset($_POST['ForAll'])){
	$ForAll = mres($_POST['ForAll']);
	}
	if(isset($ForAll) && $ForAll == "2"){
	if(isset($_POST['CopyToSubcategory'])){
	$CopyToSubcategory = implode(',', $_POST['CopyToSubcategory']);
	}
	}
	else {
	$CopyToSubcategory = "";
	}
	
	if(isset($ForAll) && $ForAll == "3"){
	if(isset($_POST['SomeStudentKid'])){
	$SomeStudentKid = implode(',', $_POST['SomeStudentKid']);
	}
	else {
		$SomeStudentKid = "";
	}
	if(isset($_POST['SomeStudentAdult'])){
	$SomeStudentAdult = implode(',', $_POST['SomeStudentAdult']);
	}
	else {
		$SomeStudentAdult = "";
	}
	if(isset($_POST['SomeStudentManual'])){
	$SomeStudentManual = implode(',', $_POST['SomeStudentManual']);
	}
	else {
		$SomeStudentManual = "";
	}
	}
	else {
	$SomeStudentKid = "";
	$SomeStudentAdult = "";
	$SomeStudentManual = "";
	}

	mysqli_query($con,"INSERT INTO survey_questions (event_id," . $superuser . "_id,subcategory_id,ForAll,ForSome,ForKids,ForAdults,ForManual,question,description,enddate,exclusive) VALUES ('$event_id',$superuser_id,$subcategory_id,$ForAll,'$CopyToSubcategory','$SomeStudentKid','$SomeStudentAdult','$SomeStudentManual','$question','$description','$enddate','$exclusive')");
	
	$last_id = mysqli_insert_id($con);
	$data['last_insert_id'] = $last_id;
	echo json_encode($data);
	
	LogAction($event_id, $superuser, $superuser_id, $subcategory_id, 0, 0, "AddQuestion", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

	}

//ADD AN OPTION TO A SURVEY QUESTION
	if(isset ($action) && $action == "AddOption"){
	
	$ready = mres($_GET['ready']); //get!!

	//case: date/time option
	if((isset($_POST['answer_option_date']) && !empty($_POST['answer_option_date'])) || (isset($_POST['start_time']) && ($_POST['start_time'] <> "00:00") && (!empty($_POST['start_time']))) || (isset($_POST['end_time']) && ($_POST['end_time'] <> "00:00") && (!empty($_POST['end_time'])))){ 
		//1. variables
		$answer_option_date = "";
		if(isset($_POST['answer_option_date']) && !empty($_POST['answer_option_date'])){ 
		$answer_option_date=mres($_POST['answer_option_date']);
		}

		$start_time = "00:00";
		$end_time = "00:00";
		
		if(isset($_POST['start_time']) && ($_POST['start_time'] <> "00:00") && (!empty($_POST['start_time']))){
			$start_time = mres($_POST['start_time']);
		}
		if(isset($_POST['end_time']) && ($_POST['end_time'] <> "00:00") && (!empty($_POST['end_time']))){
			$end_time = mres($_POST['end_time']);
		}
		
		//2. build option
		$answer_option = $answer_option_date;
		if(($start_time <> "00:00") && ($end_time <> "00:00") && ($start_time < $end_time)){
			$answer_option .= " van " . $start_time . " tot " . $end_time;
		}
		if(($start_time <> "00:00") && ($end_time <> "00:00") && ($start_time == $end_time)){
			$answer_option .= " om " . $start_time;
		}
		if(($start_time <> "00:00") && ($end_time == "00:00")){
			$answer_option .= " om " . $start_time;
		}
	}
	else {
	//case: normal option
	if(isset($_POST['answer_option'])){ 
	$answer_option=mres($_POST['answer_option']);
	}
	}
	
	$event_id=mres($_POST['event_id']);
	$question_id=mres($_POST['question_id']);
	$subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
		
	$question=mres($_POST['question']);
	$description = mres($_POST['description']);
	$enddate = mres($_POST['enddate']);
	$exclusive = mres($_POST['exclusive']);
	
	$ForAll = 0;
	if(isset($_POST['ForAll'])){
	$ForAll = mres($_POST['ForAll']);
	}
	if(isset($ForAll) && $ForAll == "2"){
	if(isset($_POST['CopyToSubcategory'])){
	$CopyToSubcategory = implode(',', $_POST['CopyToSubcategory']);
	}
	}
	else {
	$CopyToSubcategory = "";
	}
	
	if(isset($ForAll) && $ForAll == "3"){
		if(isset($_POST['SomeStudentKid'])){
		$SomeStudentKid = implode(',', $_POST['SomeStudentKid']);
		}
		else {
			$SomeStudentKid = "";
		}
		if(isset($_POST['SomeStudentAdult'])){
		$SomeStudentAdult = implode(',', $_POST['SomeStudentAdult']);
		}
		else {
			$SomeStudentAdult = "";
		}
		if(isset($_POST['SomeStudentManual'])){
		$SomeStudentManual = implode(',', $_POST['SomeStudentManual']);
		}
		else {
			$SomeStudentManual = "";
		}
		}
		else {
		$SomeStudentKid = "";
		$SomeStudentAdult = "";
		$SomeStudentManual = "";
		}
	
	if($ready == "0"){
	mysqli_query($con,"INSERT INTO survey_answer_options (event_id,question_id,answer_option) VALUES ('$event_id','$question_id','$answer_option')");
	}
	mysqli_query($con,"UPDATE survey_questions SET done = '$ready', enddate = '$enddate', subcategory_id='$subcategory_id', question='$question', description='$description', exclusive = '$exclusive', ForAll = '$ForAll', ForSome = '$CopyToSubcategory', ForKids = '$SomeStudentKid', ForAdults = '$SomeStudentAdult', ForManual = '$SomeStudentManual' WHERE id = $question_id");
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "AddOption", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	
}

//DELETE A SURVEY QUESTION
	if(isset ($action) && $action == "DeleteQuestion"){
	
	$question_id=mres($_POST['question_id']);
	$event_id=mres($_POST['event_id']);
	if(isset($_POST['type'])){
		$type = mres($_POST['type']);
	}
	else {
		$type = "archive";
	}
	
	//archive survey
	if($type == "archive"){ 			
	mysqli_query($con,"UPDATE survey_questions SET archive = 1 WHERE id = $question_id AND " . $superuser . "_id = $superuser_id");
	mysqli_query($con,"UPDATE survey_answers SET archive = 1 WHERE question_id = $question_id");
	mysqli_query($con,"UPDATE survey_answer_options SET archive = 1 WHERE question_id = $question_id");
	}
	
	//remove survey completely
	if($type == "trash"){ 			
	mysqli_query($con,"DELETE FROM survey_questions WHERE id = $question_id AND " . $superuser . "_id = $superuser_id");
	mysqli_query($con,"DELETE FROM survey_answers WHERE question_id = $question_id");
	mysqli_query($con,"DELETE FROM survey_answer_options WHERE question_id = $question_id");
	}
	
	//disconnect survey from calendar item
	if($type == "disconnect"){
	$get_active_survey = mysqli_query($con,"SELECT * FROM survey_questions WHERE id = $question_id");
	$row_active_survey = mysqli_fetch_array($get_active_survey);
	$event_id_list = $row_active_survey['event_id_list'];
	$newString = removeFromString($event_id_list, $event_id);
	mysqli_query($con, "UPDATE survey_questions SET event_id_list = '$newString' WHERE id = $question_id");
	
	}
	LogAction($event_id, $superuser, $superuser_id, 0, 0, 0, "DeleteQuestion", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	}

//ACTIVATE THE SURVEY
if(isset ($action) && $action == "ActivateSurvey"){
	
	$question_id=mres($_POST['question_id']);
 			
	mysqli_query($con,"UPDATE survey_questions SET archive = 0 WHERE id = $question_id AND " . $superuser . "_id = $superuser_id");
	mysqli_query($con,"UPDATE survey_answers SET archive = 0 WHERE question_id = $question_id");
	mysqli_query($con,"UPDATE survey_answer_options SET archive = 0 WHERE question_id = $question_id");
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "ActivateSurvey", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
		
	}


//DELETE AN OPTION FROM SURVEY QUESTION
	if(isset ($action) && $action == "DeleteOption"){
	
	$id=mres($_POST['id']);
	$question_id=mres($_POST['question_id']);
	
	mysqli_query($con,"DELETE FROM survey_answer_options WHERE id = $id AND question_id = $question_id");
	mysqli_query($con,"DELETE FROM survey_answers WHERE answer_id = $id AND question_id = $question_id");
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "DeleteOption", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

	
}

//EDIT OPTION FROM SURVEY QUESTION
	if(isset ($action) && $action == "EditOneOption"){
	
	$option_id=mres($_POST['option_id']);
	$option_name=mres($_POST['option_name']);
	$question_id=mres($_POST['question_id']);

	mysqli_query($con,"UPDATE survey_answer_options SET answer_option = '$option_name' WHERE id = $option_id AND question_id = $question_id");	
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "EditOneOption", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));	
	
}

//MAKE OPTIONS EDITABLE 
	if(isset ($action) && $action == "EditOptions"){
	
	$question_id=mres($_POST['question_id']);

	mysqli_query($con,"UPDATE survey_questions SET done = '0' WHERE id = $question_id");	
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "EditOptions", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));	
	
}

//REUSE A SURVEY QUESTION (DUPLICATE)
	if(isset ($action) && $action == "ReUseSurvey"){
	
	$question_id=mres($_POST['question_id']);
	$event_id=mres($_POST['event_id']);
	$subcategory_id=mres($_POST['subcategory_id']);
	if(isset($_POST['active'])){
		$active = mres($_POST['active']);
	}
	else {
		$active = "0";
	}
	
	//make new survey from old survey
	if($active == "0"){ 
	mysqli_query($con,"INSERT INTO survey_questions
	(event_id, " . $superuser . "_id, subcategory_id, question, description, exclusive, enddate)
	SELECT $event_id, " . $superuser . "_id, subcategory_id, question, description, exclusive, enddate
	FROM survey_questions
	WHERE id = $question_id");	
	
	$last_id = mysqli_insert_id($con);
	
	mysqli_query($con,"INSERT INTO survey_answer_options
	(event_id, question_id, answer_option)
	SELECT $event_id, $last_id, answer_option
	FROM survey_answer_options
	WHERE question_id = $question_id");
	}
	
	//add event to event_list of active survey
	if($active == "1"){
	$get_active_survey = mysqli_query($con,"SELECT * FROM survey_questions WHERE id = $question_id");
	$row_active_survey = mysqli_fetch_array($get_active_survey);
	$event_id_list = $row_active_survey['event_id_list'];
	if($event_id_list == ""){
	$newString = $event_id;	
	}
	else { 
	$newString = addtoString($event_id_list, $event_id);
	}
	
	mysqli_query($con, "UPDATE survey_questions SET event_id_list = '$newString' WHERE id = $question_id");
		
	}
	LogAction($event_id, $superuser, $superuser_id, $subcategory_id, 0, 0, "ReuseSurvey", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	
}


//ADD TAG (categories for files)
	if(isset ($action) && $action == "AddTag"){
	
	$tag_title = mres($_POST['tag_title']);
	$subcategory_id=mres($_POST['subcategory_id']);
	
	mysqli_query($con,"INSERT INTO " . $superuser . "s_tags (" . $superuser . "_id,subcategory_id,title,ForAll) VALUES ('$superuser_id','$subcategory_id','$tag_title','1')");
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "AddTag", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	
	
}

//DELETE TAG in tagslist (categories for documents)
	if(isset ($action) && $action == "DeleteTag"){
	
	$tag_id=mres($_POST['tag_id']);
	
	//delete from table tags
	mysqli_query($con,"DELETE FROM " . $superuser . "s_tags WHERE " . $superuser . "_id = $superuser_id AND id = $tag_id");
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "DeleteTag", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}


//UPLOAD AN ATTACHMENT TO A CALENDAR ITEM
if(isset ($action) && $action == "UploadAttachment"){

if (empty($_FILES['images'])) {
    echo json_encode(['error'=>'No files found for upload.']); 
    // or you can throw an exception 
    return; // terminate
}

// get the files posted
$images = $_FILES['images'];

//get other variables
$date=date('Y-m-d');
$name=mres($_POST['name']);
$newname = preg_replace("/[^A-Za-z0-9 ]/", '', $name);
$newname = str_replace(" ", '_', $newname);
$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
$permanent = mres($_POST['permanent']);

$eventID = 0;
if(isset($_POST['event_id']) && is_numeric($_POST['event_id']) && !empty($_POST['event_id']) && $_POST['event_id'] <> 0){ 
$eventID = mres($_POST['event_id']);
}
if(isset($_POST['recurrent'])){
	$recurrent = mres($_POST['recurrent']);
		}
	else {
		$recurrent = 0;
	}
$timestamp = time();

// a flag to see if everything is ok
$success = null;

// file paths to store
$paths= [];

// get file names
$filenames = $images['name'];

// loop and process files
for($i=0; $i < count($filenames); $i++){
	$ext = strtolower(pathinfo($filenames[$i], PATHINFO_EXTENSION));
	
	$allowedExts = array("pdf", "doc", "docx", "jpg", "jpeg", "png", "JPG", "JPEG", "PNG", "gif", "GIF", "gpx");
	if (in_array($ext, $allowedExts)){
	
    $target = "../data/" . $superuser . "s_documents/" . $superuser_id . "/" . $newname . "_" . $timestamp . "_" . $superuser_id . "_" . $subcategory_id .  "." . $ext;
    $target_thumb = "../data/" . $superuser . "s_documents/" . $superuser_id . "/thumb_" . $newname . "_" . $timestamp . "_" . $superuser_id . "_" . $subcategory_id .  "." . $ext;
    $doc = $newname . "_" . $timestamp . "_" . $superuser_id . "_" . $subcategory_id . "." . $ext;
    if(move_uploaded_file($images['tmp_name'][$i], $target)) {
        $success = true;
        $paths[] = $target;
    } else {
        $success = false;
        break;
    }
    }
    else {
	    $success = false;
    }
}


// check and process based on successful status 
if ($success === true) {
	
	if($recurrent == 0){
	if($eventID == 0){ //add attachment to chatbox
	mysqli_query($con,"INSERT INTO " . $superuser . "s_documents (date,name,file," . $superuser . "_id,subcategory,permanent,filetype) VALUES ('$date','$name','$doc','$superuser_id','$subcategory_id','$permanent','0')");
	$last_id = mysqli_insert_id($con);
	$user_id = userValue(null, "id");
	$now = date("Y-m-d H:i:s");
	
	mysqli_query($con,"INSERT INTO chat 
	(date, parent_id, document_id, subcategory_id, " . $superuser . "_id, is_superuser, pic, comment, RespondToId) VALUES 
	('$now', $user_id, $last_id, $subcategory_id, $superuser_id, 1, '', '', '')");
	
	$kids_list = getKidIdsFromSuper($superuser_id, $superuser, $subcategory_id);
	$parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $subcategory_id);
	$adult_list = getAdultIdsFromSuper($superuser_id, $superuser, $subcategory_id);
	$adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory_id);
	$contact_list = getIdsContactSuper($superuser_id, $superuser, $subcategory_id);
	
	$chatbox_token = base64_url_encode($superuser . "_" . $superuser_id . "_" . $subcategory_id);
	
	$notification_message = userValue($user_id, "username") . " heeft een bestand gedeeld in de chatbox van " . userValueSubcategory($subcategory_id, $superuser, "cat_name");

	//first send notification that a user responded to your event
	$payload = create_payload_json($notification_message, "index.php?deeplink=1&chatbox_token=" . $chatbox_token);
	  $fetchtokens = mysqli_query($con,"SELECT device, device_token FROM users WHERE 
	  id IN ($parent_list,$adult_list,$contact_list) AND id <> $user_id AND device <> 0 AND device_token <> '' AND device_token <> 'null'");
		  if(mysqli_num_rows($fetchtokens) > 0){
		  foreach($fetchtokens AS $row){
		  $user_mobile_info = ['device'=>$row['device'], 'token'=>$row['device_token']];
		  send_mobile_notification_request($user_mobile_info, $payload);
		  }
		  }
		
	}
	else { //add attachment to calendar item
	mysqli_query($con,"INSERT INTO " . $superuser . "s_documents (date,name,file," . $superuser . "_id,subcategory,event_id,permanent,filetype) VALUES ('$date','$name','$doc','$superuser_id','$subcategory_id','$eventID','$permanent','0')");
	}
	}
	if($recurrent == 1){ 
	//get all events in recurrent activity
	$sql_rec = mysqli_query($con, "SELECT repeat_id FROM calendar WHERE id = $eventID AND repeat_id <> 0");
	if(mysqli_num_rows($sql_rec) > 0){
		$row_rec = mysqli_fetch_array($sql_rec);
		$sql_events = mysqli_query($con, "SELECT id FROM calendar WHERE repeat_id = $row_rec[repeat_id]");
		if(mysqli_num_rows($sql_events) > 0){
			while($row_event = mysqli_fetch_array($sql_events)){
			mysqli_query($con,"INSERT INTO " . $superuser . "s_documents (date,name,file," . $superuser . "_id,subcategory,event_id,permanent,filetype,reused) VALUES ('$date','$name','$doc','$superuser_id','$subcategory_id','$row_event[id]','$permanent','0', '1')");	
			}
		}
	}
	}
	//make thumb from uploaded image file
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mimetype = finfo_file($finfo, $target);
	if ($mimetype == 'image/jpg' || $mimetype == 'image/jpeg' || $mimetype == 'image/gif' || $mimetype == 'image/png') {
	createThumbnail($target, $target_thumb, 60, 60, array(255,255,255)); // creates a thumbnail with 60x60 in size and with
	}

    $output = [];
    // for example you can get the list of files uploaded this way
    // $output = ['uploaded' => $paths];
} elseif ($success === false) {
    $output = ['error'=>'Error while uploading images. Contact the system administrator'];
    // delete any uploaded files
    foreach ($paths as $file) {
        unlink($file);
    }
} else {
    $output = ['error'=>'No files were processed.'];
}

LogAction($eventID, $superuser, $superuser_id, $subcategory_id, 0, 0, "UploadAttachment", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
// return a json encoded response for plugin to process successfully
echo json_encode($output);
}


//ADD PAYBUTTON
if(isset ($action) && $action == "CreatePayButtonNew"){
  
  $timestamp=time();
  $title=mres($_POST['title']);
  $invoice=mres($_POST['invoice']);
  $subcategory_id=mres($_POST['subcategory_id']);
  
  //if paybutton edit, then this is not 0
  $button_id=mres($_POST['button_id']);
  
  //added from list or from calendar
  $addedfrom = mres($_POST['addedfrom']);
  
  //clean up description
  $dirty_html=$_POST['message']; //geen mres nodig
  require_once 'plugins/HTMLPurifier/HTMLPurifier.auto.php';
  $config = HTMLPurifier_Config::createDefault();
  $purifier = new HTMLPurifier($config);
  $message = $purifier->purify($dirty_html);
  $message = mres($message);
  
  $amount=mres($_POST['amount']);
  $amount = str_replace(',', '.', $amount);
  $startdate=mres($_POST['startdate']);
  $enddate=mres($_POST['enddate']);
  
  $paydynamic=mres($_POST['paydynamic']);
  $type=mres($_POST['type']);
  
  $CopyToSubcategory = "";
  $ForKids = "0";
  $ForAdults = "0";
  $ForManual = "0";
  
  //check if entry is for all subgroups, 1 subgroup or multiple subgroups
	if(isset($_POST['ForAll'])){
	$ForAll = mres($_POST['ForAll']);
	}
	if(isset($ForAll) && $ForAll == "2"){
	if(isset($_POST['CopyToSubcategory'])){
	$CopyToSubcategory = $_POST['CopyToSubcategory'];
	}
	}
	if(isset($ForAll) && $ForAll == "3"){
	if(isset($_POST['id_list_kids']) && !empty($_POST['id_list_kids'])){
	$ForKids = $_POST['id_list_kids'];
	}
	if(isset($_POST['id_list_adults']) && !empty($_POST['id_list_adults'])){
	$ForAdults = $_POST['id_list_adults'];
	}
	if(isset($_POST['id_list_manual']) && !empty($_POST['id_list_manual'])){
	$ForManual = $_POST['id_list_manual'];
	}
	}
	
  
  if($button_id == 0){ 
  $insert = mysqli_query($con,"INSERT INTO paybuttons 
  (startdate,
  enddate,
  timestamp,
  " . $superuser . "_id,
  subcategory_id,
  title,
  message,
  invoice, 
  amount,
  paydynamic,
  ForAll,
  ForSome,
  ForKids,
  ForAdults,
  ForManual,
  type) VALUES 
  ('$startdate','$enddate','$timestamp','$superuser_id','$subcategory_id','$title','$message','$invoice','$amount','$paydynamic','$ForAll','$CopyToSubcategory','$ForKids','$ForAdults','$ForManual','$type')");
  
  $last_id = mysqli_insert_id($con);
  
  }
  if($button_id <> 0){
   $insert = mysqli_query($con,"UPDATE paybuttons SET 
	  startdate='$startdate',
	  enddate='$enddate',
	  timestamp='$timestamp',
	  " . $superuser . "_id='$superuser_id',
	  subcategory_id='$subcategory_id',
	  title='$title',
	  invoice='$invoice',
	  message='$message', 
	  amount='$amount',
	  paydynamic='$paydynamic',
	  ForAll='$ForAll',
	  ForSome='$CopyToSubcategory',
	  ForKids='$ForKids',
	  ForAdults='$ForAdults',
	  ForManual='$ForManual',
	  type='$type' WHERE id = '$button_id'");
   
   $last_id = $button_id;
   
  }
  
  LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "CreatePayButtonNew", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
  
  if($insert == true) {
  	
	
	//Upload optional pics to server
	$timestamp = time();

	//upload if not empty
	if (!empty($_FILES['images'])) {
		//echo json_encode(['error'=>'No files found for upload.']);
		// or you can throw an exception
		//return; // terminate
		
		if($button_id <> 0){
		//first delete old picture if exists
		  $select_query = mysqli_query($con,"SELECT picture FROM paybuttons WHERE id = $button_id AND picture <> '' AND " . $superuser . "_id = $superuser_id");
		  if(mysqli_num_rows($select_query) > 0){
		  $row_pic = mysqli_fetch_array($select_query);
		  $pic = $row_pic['picture'];
		  $filename = "../data/" . $superuser . "s_webshop/$superuser_id/$pic";
		  if(file_exists($filename)){
		  unlink($filename);
		  }
		  }
		  }

		// get the files posted
		$images = $_FILES['images'];

		// a flag to see if everything is ok
		$success = null;

		// file paths to store
		$paths= [];

		// get file names
		$filenames = $images['name'];

		// loop and process files
		for($i=0; $i < count($filenames); $i++){
			$ext = pathinfo($filenames[$i], PATHINFO_EXTENSION);
			$target = str_replace(' ', '_', "../data/" . $superuser . "s_webshop/" . $superuser_id . "/" . $timestamp . "_" . $superuser_id . "_" . $subcategory_id .  "_" . $filenames[$i]);
			$doc = str_replace(' ', '_',  $timestamp . "_" . $superuser_id . "_" . $subcategory_id . "_" . $filenames[$i]);
			if(move_uploaded_file($images['tmp_name'][$i], $target)) {
				$success = true;
				$paths[] = $target;

				mysqli_query($con,"UPDATE paybuttons SET picture = '$doc' WHERE id = $last_id AND " . $superuser . "_id = $superuser_id");

			} else {
				$success = false;
				break;
			}
		}

		// check and process based on successful status
		if ($success === true) {

			$output = ['last_id'=>$last_id];
			// for example you can get the list of files uploaded this way
			// $output = ['uploaded' => $paths];

		} elseif ($success === false) {
			$output = ['error'=>'Error while uploading images in paybuttons. Contact the system administrator'];
			// delete any uploaded files
			foreach ($paths as $file) {
				unlink($file);
			}
		} else {
			$output = ['error'=>'No files were processed in paybuttons.'];
		}
	}
	else {
		$output = ['last_id'=>$last_id];
	}
	// return a json encoded response for plugin to process successfully
	echo json_encode($output);
	//$data['last_id'] = $last_id;
	//echo json_encode($data);

  }

  
  //automatically add paybutton to calendar item
  if($addedfrom == "calendar"){ 
  $event_id=mres($_POST['event_id']);
  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);
  
  mysqli_query($con,"INSERT INTO " . $superuser . "s_paybuttons 
  (button_id,
  event_id,
  " . $superuser . "_id,
  subcategory_id) VALUES 
  ('$last_id','$event_id','$superuser_id','$subcategory_id')");  
  }
  
  
}

//ARCHIVE A PAYBUTTON
if(isset ($action) && $action == "ArchivePayButton"){
  
  $button_id=mres($_POST['button_id']);
    
  mysqli_query($con,"UPDATE paybuttons SET archive = 1 WHERE id = $button_id AND " . $superuser . "_id = $superuser_id");  
  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "ArchivePayButton", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//ACTIVATE A PAYBUTTON
if(isset ($action) && $action == "ActivatePayButton"){
  
  $button_id=mres($_POST['button_id']);
    
  mysqli_query($con,"UPDATE paybuttons SET archive = 0 WHERE id = $button_id AND " . $superuser . "_id = $superuser_id");  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "ActivatePayButton", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//DELETE PAYBUTTON
if(isset ($action) && $action == "DeletePayButton"){
  
  $button_id=mres($_POST['button_id']);
    
  //delete paybutton from table paybuttons
  mysqli_query($con,"DELETE FROM paybuttons WHERE " . $superuser . "_id = $superuser_id AND id = $button_id"); 
  
  //delete payment results form table paybuttons_results
  mysqli_query($con,"DELETE FROM paybuttons_results WHERE " . $superuser . "_id = $superuser_id AND button_id = $button_id"); 
  
  //delete paybutton from paybuttons
  mysqli_query($con,"DELETE FROM " . $superuser . "s_paybuttons WHERE " . $superuser . "_id = $superuser_id AND button_id = $button_id");   
  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "DeletePayButton", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}


//ADD PAYBUTTON TO AN EVENT IN CALENDAR
if(isset ($action) && $action == "AddPayButtonEvent"){
  
  $button_id=mres($_POST['button_id']);
  $event_id=mres($_POST['event_id']);
  $subcategory_id=sres($_POST['subcategory_id'], $superuser_id, $superuser);

  
  mysqli_query($con,"INSERT INTO " . $superuser . "s_paybuttons 
  (button_id,
  event_id,
  " . $superuser . "_id,
  subcategory_id) VALUES 
  ('$button_id','$event_id','$superuser_id','$subcategory_id')");  
  
  
  LogAction($event_id, $superuser, $superuser_id, $subcategory_id, 0, 0, "AddPayButtonEvent", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//REMOVE LINK BETWEEN PAYBUTTON AND CALENDAR ITEM
if(isset ($action) && $action == "UnlinkPayButtonEvent"){
  
  $button_id=mres($_POST['button_id']);
  $event_id=mres($_POST['event_id']);
    
  mysqli_query($con,"DELETE FROM " . $superuser . "s_paybuttons WHERE event_id = $event_id AND " . $superuser . "_id = $superuser_id AND button_id = $button_id");
  
  LogAction($event_id, $superuser, $superuser_id, $subcategory_id, 0, 0, "UnlinkPayButtonEvent", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));  
}

//ADD A SPONSOR (login/profile.php)
if(isset ($action) && $action == "AddSponsor"){
	$name=mres($_POST['name']);
	$url=mres($_POST['url']);
	
	//make personal dir
	if (!is_dir('../data/' . $superuser . 's_sponsors/'.$superuser_id)) {
	    mkdir('../data/' . $superuser . 's_sponsors/'.$superuser_id, 0777, true);
	}
	
	$target_dir = "../data/" . $superuser . "s_sponsors/" . $superuser_id . "/";

	$encoded = $_POST['image_data'];
    //explode at ',' - the last part should be the encoded image now
    $exp = explode(',', $encoded);
    //decode the image and finally save it
    $data = base64_decode($exp[1]);
    
    //add name and random number to uploaded avatar
	$rand = mt_rand();
	$keycode = mt_rand(); //use other random number for keycode
	
	$target_file = $target_dir . $superuser_id . "_" . $rand . ".png";
	$banner = $superuser_id . "_" . $rand . ".png";

    //make sure you are the owner and have the rights to write content
    file_put_contents($target_file, $data);

  mysqli_query($con,"INSERT INTO advertising 
  (keycode, " . $superuser . "_id, sponsor, url, image)
  VALUES
  ($keycode, $superuser_id, '$name', '$url', '$banner')");
  
  $last_id = mysqli_insert_id($con);
  
  mysqli_query($con, "UPDATE advertising SET row_order = '$last_id' WHERE id = '$last_id' AND " . $superuser . "_id = $superuser_id");  
  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "AddSponsor", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}

//MANUALLY SET STATUS 'PAYED' FOR PAYBUTTON
if(isset ($action) && $action == "ManualPay"){
  
  $button_id=mres($_POST['button_id']);
  $kid_id=mres($_POST['kid_id']);
  $manual_id=mres($_POST['manual_id']);
  $adult_id=mres($_POST['adult_id']);
  $now = time();
  $method=mres($_POST['method']);
  
  if($method == "add"){ 
  //get more information paybutton
  $check = mysqli_query($con, "SELECT * FROM paybuttons WHERE id = $button_id AND " . $superuser . "_id = $superuser_id");
  if(mysqli_num_rows($check) > 0){ 
  $row = mysqli_fetch_array($check);
  
  
  mysqli_query($con, "INSERT INTO paybuttons_results
(button_id, order_id, external_id, title, invoice, timestamp, " . $superuser . "_id, adult_id, kid_id, manual_id, amount, fee, method, status, status_id, remarks, type)
VALUES
($button_id, 'manual', 'manual', '$row[title]', '$row[invoice]', $now, '$superuser_id', '$adult_id', '$kid_id', '$manual_id', '$row[amount]', '0', 'manueel', 'manual', '1', 'manueel betaald', 0)");
  }
  }
  if($method == "remove"){ 
  mysqli_query($con, "DELETE FROM paybuttons_results WHERE " . $superuser . "_id = $superuser_id AND kid_id = $kid_id AND manual_id = $manual_id AND adult_id = $adult_id AND button_id = $button_id AND order_id = 'manual'");
  }
  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "ManualPay", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//SAVE ORDER OF PAYBUTTON FORM
	if(isset ($action) && $action == "SaveOrderForm"){
	
	$activatediscount=mres($_POST['activatediscount']);
	$button_id=mres($_POST['button_id']);

	
	mysqli_query($con,"UPDATE paybuttons SET activatediscount = $activatediscount WHERE id = $button_id AND " . $superuser . "_id = $superuser_id");
	
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "SaveOrderForm", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	
}

$('.SubmitFormOption').on('click', function(e) {
    console.log(e);
    e.preventDefault();

    var formData = new FormData($("#AddOptionForm")[0]); // Verzamel formulierdata inclusief bestand
    formData.append("action", "AddFormOption");

    var submiturl = "superuser_actions.php?get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>";

    $.ajax({
        type: "POST",
        url: submiturl,
        data: formData,
        processData: false,
        contentType: false,
        success: function(data) {
            $('<?php echo $divtarget ?>').load("superuser_paybuttons_form.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&button_id=<?php echo $button_id ?>");
        },
        error: function() {
            alert("Er is een probleem opgetreden tijdens het uploaden van de afbeelding.");
        }
    });
});

//DELETE FORM OPTION PAYBUTTON
	if(isset ($action) && $action == "DeleteFormOption"){
	
	$id=mres($_POST['id']);
	$button_id=mres($_POST['button_id']);
	
	mysqli_query($con,"DELETE FROM paybuttons_form WHERE id = $id AND button_id = $button_id AND " . $superuser . "_id = $superuser_id");
	
	//don't delete answer options to be sure, but archive them
	mysqli_query($con,"UPDATE paybuttons_form_answers SET archive = 1 WHERE option_id = $id AND button_id = $button_id AND " . $superuser . "_id = $superuser_id");
	
	LogAction(0, $superuser, $superuser_id, 0, 0, 0, "DeleteFormOption", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	
}

//SET STATUS 'READ' FOR NOTIFICATION AFTER PREMIUM MESSAGE
if(isset ($action) && $action == "PremiumRead"){

  $order_id=mres($_POST['order_id']);
  mysqli_query($con,"UPDATE premium SET message_read = '1' WHERE order_id = '$order_id' AND " . $superuser . "_id = $superuser_id"); 
  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "PremiumRead", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}

//ACTIVATE TRIAL
if(isset ($action) && $action == "PremiumTrial"){

  $level=mres($_POST['level']);
  $date = date('Y-m-d');
  $end_date = date('Y-m-d', strtotime('+' . $trial_duration . ' month', strtotime($date))); 
  $order_id = mres($_POST['order_id']);
  
  //triple check if trial isn't already used:
  $check = mysqli_query($con,"SELECT * FROM premium WHERE " . $superuser . "_id = $superuser_id AND level = $level AND status = 'trial'"); 
  if(mysqli_num_rows($check) == 0){
	mysqli_query($con,"INSERT INTO premium (order_id, " . $superuser . "_id, level, amount, pay_date, end_date, status, status_id) VALUES ('$order_id', $superuser_id, '$level', '0', '$date', '$end_date', 'trial', '1')");
	
	//send mail to app-team
 	  $user_email =  $mailinfoaddress;
	  $to      = $mailinfoaddress;
	  $subject = 'ADMIN: ' . userValueSuper($superuser_id,$superuser,"username") . ' heeft premium trial level ' . $level . ' geactiveerd';
	  $message_title = $subject;
	  $message = "Details:<br>
	  <strong>Startdatum:</strong> " . $date . "<br>
	  <strong>Einddatum:</strong> " . $end_date . "<br>
	  <strong>Bestellingsnummer:</strong> " . $order_id . "<br>";
	  
	  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
	  $message_body = str_replace('%message_title%', $message_title, $message_body); 
	  $message_body = str_replace('%message%', $message, $message_body);
	  $message_body = str_replace('%link%', "", $message_body);
	  $message_body = str_replace('%link2%', "", $message_body);
	  $message_body = str_replace('%link3%', "", $message_body);
	  $message_body = str_replace('%reply%', "", $message_body);
	  $message_body = str_replace('%avatar%', $logo_path, $message_body);	
	  $message_body = str_replace('%full_path%', $full_path, $message_body);		  
	  $message_body = str_replace('%appname%', $appname, $message_body);

	  userSendMail6($user_email, $to, $subject, $message_body); //Send Mail	
	  
	  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "PremiumTrial", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	  
  }
}

//Get premium but use all affiliate savings so it's free
if(isset ($action) && $action == "BuyFree"){

    if(isset($_POST['level'])) $level=mres($_POST['level']);
	if(isset($_POST['amount'])) $amount=mres($_POST['amount']);
	if(isset($_POST['reduction_plus'])) $reduction_plus=mres($_POST['reduction_plus']);
	if(isset($_POST['price_left'])) $price_left=mres($_POST['price_left']);

	//payment date
	$pay_date = date('Y-m-d');
	if(isset($_POST['end_date'])) $end_date=$_POST['end_date']; 
	$order_id = mres($_POST['order_id']);
  
	mysqli_query($con,"INSERT INTO premium 
	(order_id, " . $superuser . "_id, level, amount, reduction_plus, reduction_upgrade, amount_total, pay_date, end_date, status, status_id) 
	VALUES 
	('$order_id', $superuser_id, '$level', '$amount', '$reduction_plus', '$price_left', '0', '$pay_date', '$end_date', 'paid' , '1')");
	
	//send mail to app-team
 	  $user_email =  $mailinfoaddress;
	  $to      = $mailinfoaddress;
	  $subject = 'ADMIN: ' . userValueSuper($superuser_id,$superuser,"username") . ' heeft premium account level ' . $level . ' aangeschaft';
	  $message_title = $subject;
	  $message = "Details:<br>
	  <strong>Betaaldatum:</strong> " . $pay_date . "<br>
	  <strong>Einddatum:</strong> " . $end_date . "<br>
	  <strong>Bestellingsnummer:</strong> " . $order_id . "<br>
	  <strong>Prijs:</strong> " . $amount . "&euro;<br>
	  <strong>Reductie via Plus-inkomsten:</strong> " . $reduction_plus . "&euro;<br>
	  <strong>Reductie bij upgrade: </strong> " . $price_left . "&euro;<br>
	  <strong>Totaal betaald: </strong> 0&euro;<br>";
	  
	  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
	  $message_body = str_replace('%message_title%', $message_title, $message_body); 
	  $message_body = str_replace('%message%', $message, $message_body);
	  $message_body = str_replace('%link%', "", $message_body);
	  $message_body = str_replace('%link2%', "", $message_body);
	  $message_body = str_replace('%link3%', "", $message_body);
	  $message_body = str_replace('%reply%', "", $message_body);
	  $message_body = str_replace('%avatar%', $logo_path, $message_body);	
	  $message_body = str_replace('%full_path%', $full_path, $message_body);		  
	  $message_body = str_replace('%appname%', $appname, $message_body);

	  userSendMail6($user_email, $to, $subject, $message_body); //Send Mail	

	  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "BuyFree", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}

//LINK WITH MOLLIE: in login/profile.php remove link Mollie
if(isset ($action) && $action == "RemoveLinkMollie"){
  
  mysqli_query($con,"UPDATE users SET mollie_key = '' WHERE " . $superuser . "_id = $superuser_id");
  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "RemoveLinkMolly", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
 
}

//PREMIUM: Activate coupon code
if(isset ($action) && $action == "ActivateCoupon"){

  $coupon=mres($_POST['coupon']);
  $date = date('Y-m-d');
  $order_id = mres($_POST['order_id']);

  if($superuser == "club"){
  $decrypted = base_convert($coupon, 32, 10);
  $coupon_level = substr($decrypted,0,1);
  $coupon_enddate = substr($decrypted,2,4) . "-" . substr($decrypted,6,2) . "-" . substr($decrypted,8,2);
  }
  
	mysqli_query($con,"INSERT INTO premium (order_id, " . $superuser . "_id, level, amount, reduction_plus, amount_total, pay_date, end_date, coupon, status, status_id) VALUES ('$order_id', $superuser_id, '$coupon_level', '0', '0', '0', '$date', '$coupon_enddate', '$coupon', 'coupon', '1')"); 
	
	//send mail to app-team
 	  $user_email =  $mailinfoaddress;
	  $to      = $mailinfoaddress;
	  $subject = 'ADMIN: ' . userValueSuper($superuser_id,$superuser,"username") . ' heeft coupon voor level ' . $coupon_level . ' geactiveerd';
	  $message_title = $subject;
	  $message = "Details:<br>
	  <strong>Coupon: </strong> " . $coupon . "<br>
	  <strong>Startdatum:</strong> " . $date . "<br>
	  <strong>Einddatum coupon:</strong> " . $coupon_enddate . "<br>
	  <strong>Bestellingsnummer:</strong> " . $order_id . "<br>
	  <strong>Prijs:</strong> 0&euro;<br>";
	  
	  $message_body = file_get_contents('mail_templates/' . $mailtemplate_default);
	  $message_body = str_replace('%message_title%', $message_title, $message_body); 
	  $message_body = str_replace('%message%', $message, $message_body);
	  $message_body = str_replace('%link%', "", $message_body);
	  $message_body = str_replace('%link2%', "", $message_body);
	  $message_body = str_replace('%link3%', "", $message_body);
	  $message_body = str_replace('%reply%', "", $message_body);
	  $message_body = str_replace('%avatar%', $logo_path, $message_body);	
	  $message_body = str_replace('%full_path%', $full_path, $message_body);		  
	  $message_body = str_replace('%appname%', $appname, $message_body);

	  userSendMail6($user_email, $to, $subject, $message_body); //Send Mail	
  
	  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "ActivateCoupon", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//MEMBERS LIST: filter in board members list
if(isset ($action) && $action == "filter"){
  
  $label_id=mres($_POST['label_id']);
  
  $board=mres($_POST['board']);
  if($board == 0){
  $subcategory_id=mres($_POST['subcategory_id']);
  $string = "AND subcategory = $subcategory_id";
  }
  else {
	  $string = "";
  }
  
  $name=mres($_POST['name']);

  $id_kids = array();
  $id_parents = array();
  $id_manuals = array();
  
  if($name == "label"){
  if($label_id == "0"){
  $label_sql = mysqli_query($con, "SELECT kid_id, NULL AS adult_id, NULL AS manual_id FROM kids_" . $superuser . "s WHERE " . $superuser . "_id = $superuser_id AND mark = 1 $string
  UNION
  SELECT NULL AS kid_id, parent_id AS adult_id, NULL AS manual_id FROM parents_" . $superuser . "s WHERE " . $superuser . "_id = $superuser_id AND mark = 1 $string
  UNION
  SELECT NULL AS kid_id, NULL AS adult_id, id AS manual_id FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id = $superuser_id AND mark = 1 $string");
  }
  else { 
  $label_sql = mysqli_query($con,"SELECT * FROM labels WHERE " . $superuser . "_id = $superuser_id AND label_id = $label_id");
  }
  }
  
  if($name == "paybutton"){
  $label_sql = mysqli_query($con,"SELECT * FROM paybuttons_results WHERE " . $superuser . "_id = $superuser_id AND button_id = $label_id");
  }
  
  if(mysqli_num_rows($label_sql) > 0){
	  while($row_label = mysqli_fetch_array($label_sql)){
		  if($row_label['kid_id'] <> "0"){ 
		  $id_kids[] = $row_label['kid_id'];
		  }
		  if($row_label['adult_id'] <> "0"){ 
		  $id_parents[] = $row_label['adult_id'];
		  }
		  if($row_label['manual_id'] <> "0"){ 
		  $id_manuals[] = $row_label['manual_id']; 
		  } 
	  }
	  
	  
		$data['id_list_kids'] = $id_kids;
		$data['id_list_parents'] = $id_parents;
		$data['id_list_manual'] = $id_manuals;
		echo json_encode($data);
		exit;
	  
  }
  else {
	  	$data['id_list_kids'] = 0;
		$data['id_list_parents'] = 0;
		$data['id_list_manual'] = 0;
		echo json_encode($data);
		exit;
	  
  }  
  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "filter", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//SORT THE SUBGROUPS
if(isset ($action) && $action == "SortSubgroups"){
$id_ary = explode(",",$_POST["row_order"]);

for($i=0;$i<count($id_ary);$i++) {
mysqli_query($con, "UPDATE " . $superuser . "s_categories SET row_order='" . $i . "' WHERE id=". $id_ary[$i]);
}

LogAction(0, $superuser, $superuser_id, 0, 0, 0, "SortSubgroups", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//IMPORT USERS IN BULK
if(isset ($action) && $action == "BulkImport"){

$timestamp = time();
$AddToSubcategory = mres($_POST['AddtoSubcategory']);
	
$stmt_insert = $con->prepare("INSERT INTO kids_" . $superuser . "s_manual (key_code, name, surname, username, email, Telefoonnummer, Telefoonnummer2, " . $superuser . "_id, subcategory, bulk, timestamp) VALUES (?, AES_ENCRYPT(?,SHA1(?)), AES_ENCRYPT(?,SHA1(?)), AES_ENCRYPT(?,SHA1(?)), AES_ENCRYPT(?,SHA1(?)), AES_ENCRYPT(?,SHA1(?)), AES_ENCRYPT(?,SHA1(?)), ?, ?, ?, ?)");

foreach($_POST['name'] as $key => $val) {
    $name  = $val;
    $surname = $_POST['surname'][$key];
    $username = $_POST['username'][$key];
    if($username == ""){
	    $username = $name . " " . $surname;
    }
    $phone = $_POST['phone'][$key];
    $phone2 = $_POST['phone2'][$key];
    $email = $_POST['email'][$key];
    $key_code = mt_rand();
    $bulk = 1;

	$stmt_insert->bind_param("issssssssssssiiii", $key_code, $name, $pass, $surname, $pass, $username, $pass, $email, $pass, $phone, $pass, $phone2, $pass, $superuser_id, $AddToSubcategory, $bulk, $timestamp);
$stmt_insert->execute();
}
LogAction(0, $superuser, $superuser_id, $AddToSubcategory, 0, 0, "BulkImport", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//UPLOAD FILE IN FILES_LIST
if(isset ($action) && $action == "UploadFile"){

//edit the file or upload the file
if(isset($_POST['editfile'])){
	$editfile = "1";
}
else {
	$editfile = "0";
}

//get other variables
$date=date('Y-m-d');
$name=mres($_POST['name']);
$newname = preg_replace("/[^A-Za-z0-9 ]/", '', $name);
$newname = str_replace(" ", '_', $newname);

require_once 'plugins/HTMLPurifier/HTMLPurifier.auto.php';
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
$comment = $_POST["comment"]; //mres moet hier niet
$comment = $purifier->purify($comment);
$comment = mres($comment);

include('plugins/Autolink/lib_autolink.php');

# By default if the display url is truncated, a title attribute is added to the link, if you don't want this, add a 4th parameter of false
$comment = autolink($comment, 100, ' target="_blank" class="location"', false);

# link up email address
$comment = autolink_email($comment);

//advanced metadata
$log_user_id = userValue(null, "id"); //log user who uploads for legal reasons
$author = mres($_POST['author']);
$date_published = mres($_POST['date_published']);
$serial = mres($_POST['serial']);

$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);

//first get filetype
$filetype = mres($_POST['filetype']);

if(isset($_POST['MailToParents'])){
	$MailToParents = mres($_POST['MailToParents']);
	}
	else {
		$MailToParents = 0;
	}
$timestamp = time();

//check if entry is for all subgroups, 1 subgroup or multiple subgroups
if(isset($_POST['ForAll'])){
$ForAll = mres($_POST['ForAll']);
}
if(isset($ForAll) && $ForAll == "2"){
if(isset($_POST['CopyToSubcategory'])){
$CopyToSubcategory = $_POST['CopyToSubcategory'];
}
}
else {
$CopyToSubcategory = "";
}

//add tags
if(isset($_POST['tags'])){
$tags = $_POST['tags'];
}
else {
	$tags = "";
}
if(isset($_POST['Sticky'])){
$Sticky = mres($_POST['Sticky']);
}
else {
	$Sticky = 0;
}
if(isset($_POST['isURL'])){
$isURL = mres($_POST['isURL']);
}


//share URL
if($filetype == "1"){
$url = mres($_POST['url']);
//replace https with http: android won't open it otherwise in browser
$doc = str_replace( 'https://', 'http://', $url );
//$url = str_replace( '?dl=0', '?dl=1', $url );
$file_description = "link";
$success = true; //add this to proceed to mail
$output = [];
echo json_encode($output);
}

//upload document or other type (video/pic/audio)
if($filetype <> "1"){
if($isURL == 1){
if($filetype == "0"){
	$filetype = "1"; 
}
$url = mres($_POST['url']);
//replace https with http: android won't open it otherwise in browser
$doc = str_replace( 'https://', 'http://', $url );
//$url = str_replace( '?dl=0', '?dl=1', $url );
$file_description = "link";
$success = true; //add this to proceed to mail
$output = [];
echo json_encode($output);
}
else {
if (!empty($_FILES['images'])) {
	
	
if($editfile == 1){
	//first delete old files
	//get document information
	$document_id = mres($_POST['document_id']);	
	$select_query = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $document_id");
	if(mysqli_num_rows($select_query) > 0){ 
	$row = mysqli_fetch_array($select_query);
	
	//check if reused
	$select_duplicate = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE file = '$row[file]' AND " . $superuser . "_id = $superuser_id");	
	if(mysqli_num_rows($select_duplicate) == 0){
	
	//document
	if($row['filetype'] == 0){
	$filename = "../data/" . $superuser . "s_documents/$superuser_id/$row[file]";
	$thumb = "../data/" . $superuser . "s_documents/" . $superuser_id . "/thumb_" . $row['file'];
	if(file_exists($filename)){ 
	unlink($filename);
	}
	if(file_exists($thumb)){ 
	unlink($thumb);
	}
	}
	
	//document
	if($row['filetype'] == 5){

	$filename = "../data/" . $superuser . "s_catalogue/$superuser_id/$row[file]";
	if(file_exists($filename)){ 
	unlink($filename);
	}
	}
	
	//video
	if(($row['filetype'] == 3) && ($row['isURL'] == 0)){ 
	$filename = "../data/" . $superuser . "s_videos/$superuser_id/$row[file]";
	if(file_exists($filename)){ 
	unlink($filename);
	}
	}	
	}
	}	
}

// get the files posted
$images = $_FILES['images'];

$file_description = "bestand";

//upload document
if($filetype == "0"){
$allowedExts = array("pdf", "doc", "docx", "xls", "xlsx", "csv", "ppt", "pptx", "gpx");
$file_description = "document";
$target_subdir = "../data/" . $superuser . "s_documents";
}
//photoalbum
if($filetype == "2"){
$allowedExts = array("jpg", "jpeg", "JPG","JPEG", "png", "PNG", "gif", "GIF");
$file_description = "fotoalbum";
//make personal dir inside images folder
if (!is_dir('../data/' . $superuser . 's_images/'.$superuser_id . '/' . $timestamp)) {
    mkdir('../data/' . $superuser . 's_images/'.$superuser_id . '/' . $timestamp, 0777, true);
}
$target_subdir = "../data/" . $superuser . "s_images";
$doc = $timestamp;
}
//video
if($filetype == "3"){
$allowedExts = array("mp4", "m4v", "mov", "avi","wmv", "flv");
$file_description = "video";
$target_subdir = "../data/" . $superuser . "s_videos";	
}
//audio
if($filetype == "4"){
$allowedExts = array("mp3");
$file_description = "mp3-file";
$target_subdir = "../data/" . $superuser . "s_audio";
}
//audio
if($filetype == "5"){
$allowedExts = array("jpg", "jpeg", "JPG","JPEG", "png", "PNG", "gif", "GIF");
$file_description = "catalogus-item";
$target_subdir = "../data/" . $superuser . "s_catalogue";
}


// a flag to see if everything is ok
$success = null;

// file paths to store
$paths= [];

// get file names
$filenames = $images['name'];

if($filetype == "2"){
$randumnumber = rand();
// loop and process files
for($i=0; $i < count($filenames); $i++){
    $ext = explode('.', basename($filenames[$i]));
    
	    $info = getimagesize($images['tmp_name'][$i]);
		if ($info === FALSE) {
		   $success = false;
		   die();
		}
		if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
		   $success = false;
		   die();
		}    
	
	$ordernumber = sprintf("%02d", $i);
    $target = $target_subdir . "/" . $superuser_id . "/" . $timestamp . "/" . $timestamp . "_" . $superuser_id . "_" . $subcategory_id . "_" . $randumnumber . "_" . $ordernumber . "." . array_pop($ext);
    if(move_uploaded_file($images['tmp_name'][$i], $target)) {
        $success = true;
        $paths[] = $target;
    } else {
        $success = false;
        break;
    }
}
}
else { 
// loop and process files
for($i=0; $i < count($filenames); $i++){
	$ext = pathinfo($filenames[$i], PATHINFO_EXTENSION);
	if (in_array($ext, $allowedExts)){
    $target = $target_subdir . "/" . $superuser_id . "/" . $newname . "_" . $timestamp . "_" . $superuser_id . "_" . $subcategory_id .  "." . $ext;
    $doc = $newname . "_" . $timestamp . "_" . $superuser_id . "_" . $subcategory_id . "." . $ext;
    if(move_uploaded_file($images['tmp_name'][$i], $target)) {
        $success = true;
        $paths[] = $target;
    } else {
        $success = false;
        break;
    }
    }
    else {
	    $success = false;
    }
}
}

// check and process based on successful status 
if ($success === true) {
    
    $output = [];
    // for example you can get the list of files uploaded this way
    // $output = ['uploaded' => $paths];
} elseif ($success === false) {
    $output = ['error'=>'Er is iets foutgelopen bij het uploaden. Als deze fout blijft bestaan, gelieve ' . $appname . ' te contacteren op ' . $mailinfoaddress];
    // delete any uploaded files
    foreach ($paths as $file) {
        unlink($file);
    }
} else {
    $output = ['error'=>'No files were processed.'];
}

// return a json encoded response for plugin to process successfully
echo json_encode($output);
}
else {
	$success = true;
	$output = [];
	
	$file_description = "bestand";
	//upload document
	if($filetype == "0"){
	$file_description = "document";
	}
	//photoalbum
	if($filetype == "2"){
	$file_description = "fotoalbum";
	}
	//video
	if($filetype == "3"){
	$file_description = "video";
	}
	//audio
	if($filetype == "4"){
	$file_description = "mp3-file";
	}
	//audio
	if($filetype == "5"){
	$file_description = "catalogus-item";
	}
	
	echo json_encode($output);
	
	if($editfile == 0){
	$doc = '';
	}
	if($editfile == 1){
	$document_id = mres($_POST['document_id']);
	//keep file
	$sql = mysqli_query($con, "SELECT file FROM " . $superuser . "s_documents WHERE id = $document_id AND " . $superuser . "_id = $superuser_id");
	$row = mysqli_fetch_array($sql);
	$doc = $row['file'];
	}
	
}	
}
}

if($success === true){
if($editfile == 0){
	mysqli_query($con,"INSERT INTO " . $superuser . "s_documents (date,log_user_id,name,file,comment,author,serial,date_published," . $superuser . "_id,subcategory,tags,forall,forsome,permanent,filetype, isURL, sticky) VALUES ('$date','$log_user_id','$name','$doc','$comment','$author','$serial','$date_published','$superuser_id','$subcategory_id','$tags','$ForAll','$CopyToSubcategory','1','$filetype', '$isURL', '$Sticky')");
	$document_id = mysqli_insert_id($con);
}
if($editfile == 1){
	$document_id = mres($_POST['document_id']);
	$update = mysqli_query($con,"UPDATE " . $superuser . "s_documents SET
	date = '$date',
	log_user_id = '$log_user_id',
	name = '$name',
	author = '$author',
	serial = '$serial',
	date_published = '$date_published',
	comment = '$comment',
	file = '$doc',
	subcategory = '$subcategory_id',
	tags = '$tags',
	forall = '$ForAll',
	forsome = '$CopyToSubcategory',
	sticky = '$Sticky'
	WHERE id = $document_id AND " . $superuser . "_id = $superuser_id");
}

//add owners to table 'catalogue': filetype 5
if($filetype == "5"){
	
	$owner_ids = explode(",", mres($_POST['owner_ids']));
	$owner_names = explode(",", mres($_POST['owner_names']));
	
	if($editfile == 1){
		//edit: first delete owners from catalogue table
		mysqli_query($con, "DELETE FROM catalogue WHERE " . $superuser . "_id = $superuser_id AND document_id = $document_id");
	}
	
	foreach($owner_ids as $key => $val) {
    $owner_id  = $val;
    $kid_id_attr = 0;
    $parent_id_attr = 0;
    $manual_id_attr = 0;
    
    $owner_id_type = substr($owner_id, 0, 1);					    
    if($owner_id_type == "k"){
	    $kid_id_attr = substr($owner_id, 1); 
    }
    if($owner_id_type == "p"){
	    $parent_id_attr = substr($owner_id, 1); 
    }
    if($owner_id_type == "m"){
	    $manual_id_attr = substr($owner_id, 1); 
    }
    
	$owner_name = "";
    if(($kid_id_attr == 0) && ($parent_id_attr == 0) && ($manual_id_attr == 0)){
	     $owner_name = $owner_names[$key];
    }
    
    mysqli_query($con, "INSERT INTO catalogue (document_id, " . $superuser . "_id, kid_id, parent_id, manual_id, owner_name) VALUES
    ($document_id, $superuser_id, $kid_id_attr, $parent_id_attr, $manual_id_attr, '$owner_name')");
    
}
}

//link file to another file?
if($editfile == 0){ 
$link_to_document = 0;
if(isset($_POST['document_id']) && $_POST['document_id'] <> 0){
$link_to_document = mres($_POST['document_id']);
mysqli_query($con,"INSERT INTO " . $superuser . "s_documents_share (document_master_id, document_slave_id, " . $superuser . "_id) VALUES ($link_to_document, $document_id, $superuser_id)");
}
}
}


if($success === true){ 
if(userValueSuper($superuser_id,$superuser,"testmode") == 0){
//changelog if not exist
LogEntrySuper("0", $superuser_id, $superuser, "0", "0", $subcategory_id, $ForAll, "5");

//send mail to all parents if user wants (MailToParents checked)
if($MailToParents == 1){ 

//fetch sender and greeting 
//get greeting
$greetings = getHeaderFooterMailSuper($superuser_id, $superuser, $subcategory_id, "cat_contact_name");
//get sender
$user_email = getHeaderFooterMailSuper($superuser_id, $superuser, $subcategory_id, "cat_contact_email");
  
  
//print all ids where mail must be sent to in case this event is not ForAll
  if($ForAll == 0){
  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, $subcategory_id);
  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $subcategory_id);
  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, $subcategory_id);
  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory_id);
  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, $subcategory_id);
  }
  
  if($ForAll == 1){
  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, 0);
  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $subcategory_id);
  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, 0);
  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory_id);
  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, 0);
  }
  
  if($ForAll == 2){
  $kids_list = getKidIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory);
  $parent_list = getParentIdsFromKidIds($kids_list, $superuser_id, $superuser, $CopyToSubcategory);
  $adult_list = getAdultIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory);
  $adult_list = JoinedParentsFromParentId($adult_list, $superuser_id, $superuser, $subcategory_id);
  $manual_list = getManualIdsFromSuper($superuser_id, $superuser, $CopyToSubcategory);
  }
  
  $rec_1 = CountRecipients($kids_list, $parent_list, $manual_list, $superuser_id, $superuser, $subcategory_id);
	$queue = 0;
	if($rec_1 > $max_mails_direct){
		$queue = 1;
	}
    
  //Build message 
	$subject = $superuser_name . " heeft een " . $file_description . " toegevoegd op " . $appname;
	$message_title = $superuser_name . " heeft een " . $file_description  . " toegevoegd op " . $appname;
	$message = "Beste,<br><br>
	Een " . $file_description . " <i>" . $name . "</i> werd door " . $superuser_name . " toegevoegd op " . $appname . ".<br>
	Je vindt dit terug in de documentenpagina op " . $appname . ".<br><br>
	
	Vriendelijke groet,<br>
	" . $greetings . "";
	
	$message .= "<br><br><a href=\"" . $full_path . "/openlink.php?deeplink=1&" . $superuser . "_document_id=$document_id\" class=\"link-button\" target=\"_blank\">Open documentenpagina<br>op " . $appname . "</a>";

$logged_in_user = userValue(null, "id");
$senddate = date("Y-m-d");

require_once 'plugins/HTMLPurifier/HTMLPurifier.auto.php';
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
$dirty_html = $message;
$message = $purifier->purify($dirty_html);
$message_database = mres($message);

$insert = mysqli_query($con,"INSERT INTO mails (document_id,id_list_subgroups,id_list_kids,id_list_adults,id_list_manual,subcategory_id," . $superuser . "_id,laterdate,senddate,mailfrom,BCC,from_id,subject,message,paybutton,survey,board,rec_1,is_sent,queue) VALUES ($document_id,'0','$kids_list','$adult_list','$manual_list',$subcategory_id,$superuser_id,'0','$senddate','0','', '$logged_in_user','$subject','$message_database','0','0','0','$rec_1','0','$queue')");
$mail_id = mysqli_insert_id($con);
if(!$insert){
error_log("Error description: " . mysqli_error($con), 0);
}

if($queue <> 1){
	$sql_mail = mysqli_query($con, "SELECT * FROM mails WHERE id = $mail_id AND " . $superuser . "_id = $superuser_id");
	$row_mail = mysqli_fetch_array($sql_mail);
	SendBulkMail($row_mail, 0); //second argument 0 = no cronjob
}	
//end if isset MailToParents
}
}	
}
LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "UploadFile", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//ADD CALENDAR ITEM
if(isset ($action) && $action == "new"){

	$subcategory_id = 0;
	if(isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) && ($_POST['subcategory_id']) <> 0){ 
		$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	}
	
	$startdate = mres($_POST['startdate']);
	$title = mres($_POST['title']);
	$event_type = mres($_POST['event_type']);
	$event_id = mres($_POST['event_id']);
	
	if($event_type == "default"){ 
	//check if default comment is set:
	if($event_id == "1"){ 
	$check_default = mysqli_query($con,"SELECT * FROM " . $superuser . "s_categories WHERE " . $superuser . "_id = $superuser_id AND id=$subcategory_id");
	$numrows_check_default = mysqli_num_rows($check_default);	
	if($numrows_check_default > 0) {
		$row_check_default = mysqli_fetch_array($check_default);
		$subject = mres($row_check_default['subject_default']);
		$comment = mres($row_check_default['comment_default']);
		$location = mres($row_check_default['location_default']);
		$street = mres($row_check_default['street_default']);
		$number = mres($row_check_default['number_default']);
		$postal = mres($row_check_default['postal_default']);
		$city = mres($row_check_default['city_default']);
		$invite = mres($row_check_default['invite_default']);
		$weather = mres($row_check_default['weather_default']);
		$start_time = $row_check_default['start_time_default'];
		$end_time = $row_check_default['end_time_default'];
		$showtimeframe = $row_check_default['showtimeframe_default'];
		$ForAll = $row_check_default['ForAll_default'];
		$ForSome = $row_check_default['ForSome_default'];
		$ForKids = $row_check_default['ForKids_default'];
		$ForAdults = $row_check_default['ForAdults_default'];
		$ForManual = $row_check_default['ForManual_default'];
		
		if($ForKids == ""){
			$ForKids = "0";
		}
		if($ForAdults == ""){
			$ForAdults = "0";
		}
		if($ForManual == ""){
			$ForManual = "0";
		}
		}
	
	}
	//check if default comment is set:
	if($event_id == "2"){ 
	$check_default = mysqli_query($con,"SELECT * FROM " . $superuser . "s_categories WHERE " . $superuser . "_id = $superuser_id AND id=$subcategory_id");
	$numrows_check_default = mysqli_num_rows($check_default);	
	if($numrows_check_default > 0) {
		$row_check_default = mysqli_fetch_array($check_default);
		$subject = mres($row_check_default['subject_default2']);
		$comment = mres($row_check_default['comment_default2']);
		$location = mres($row_check_default['location_default2']);
		$street = mres($row_check_default['street_default2']);
		$number = mres($row_check_default['number_default2']);
		$postal = mres($row_check_default['postal_default2']);
		$city = mres($row_check_default['city_default2']);
		$invite = mres($row_check_default['invite_default2']);
		$weather = mres($row_check_default['weather_default2']);
		$start_time = $row_check_default['start_time_default2'];
		$end_time = $row_check_default['end_time_default2'];
		$showtimeframe = $row_check_default['showtimeframe_default2'];
		$ForAll = $row_check_default['ForAll_default2'];
		$ForSome = $row_check_default['ForSome_default2'];
		$ForKids = $row_check_default['ForKids_default2'];
		$ForAdults = $row_check_default['ForAdults_default2'];
		$ForManual = $row_check_default['ForManual_default2'];
		
		if($ForKids == ""){
			$ForKids = "0";
		}
		if($ForAdults == ""){
			$ForAdults = "0";
		}
		if($ForManual == ""){
			$ForManual = "0";
		}
		}
	}
	
	}
	
	if($event_type == "user"){ 
		$check_default = mysqli_query($con,"SELECT * FROM " . $superuser . "s_default_blocks WHERE " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id AND id = $event_id");
		if(mysqli_num_rows($check_default) > 0){
			$row_check_default = mysqli_fetch_array($check_default);
			$subject = mres($row_check_default['subject']);
			$comment = mres($row_check_default['comment']);
			$location = mres($row_check_default['location']);
			$street = mres($row_check_default['street']);
			$number = mres($row_check_default['number']);
			$postal = mres($row_check_default['postal']);
			$city = mres($row_check_default['city']);
			$invite = mres($row_check_default['invite']);
			$weather = mres($row_check_default['weather']);
			$start_time = $row_check_default['start_time'];
			$end_time = $row_check_default['end_time'];
			$showtimeframe = $row_check_default['showtimeframe'];
			$ForAll = $row_check_default['ForAll'];
			$ForSome = $row_check_default['ForSome'];
			$ForKids = $row_check_default['ForKids'];
			$ForAdults = $row_check_default['ForAdults'];
			$ForManual = $row_check_default['ForManual'];
			
			if($ForKids == ""){
			$ForKids = "0";
			}
			if($ForAdults == ""){
				$ForAdults = "0";
			}
			if($ForManual == ""){
				$ForManual = "0";
			}
		}
	}
	
	//insert into calendar
	$insert = mysqli_query($con,"INSERT INTO calendar(`title`, `" . $superuser . "_id`, `subcategory`, `ForAll`, `ForSome`, `ForKids`, `ForAdults`, `ForManual`, `startdate`, `enddate`, `showtimeframe`,`subject`,`comment`, `location`, `street`, `number`, `postal`, `city`,`Invite`,`weather`, `allDay`) VALUES('$title','$superuser_id','$subcategory_id','$ForAll', '$ForSome', '$ForKids', '$ForAdults', '$ForManual', CONCAT('$startdate ', '$start_time'),CONCAT('$startdate ', '$end_time'),'$showtimeframe','$subject','$comment','$location','$street','$number','$postal','$city','$invite','$weather','false')");	
	
	if($insert = true) { 
	$lastid = mysqli_insert_id($con);
	echo json_encode(array('status'=>'success','eventid'=>$lastid));
	}
	else { 
		echo json_encode(array('status'=>'failed'));
		}
	
	//changelog:
	LogEntrySuper($event_id, $superuser_id, $superuser, "0", "0", $subcategory_id, $ForAll, "1");
	LogAction($lastid, $superuser, $superuser_id, $subcategory_id, 0, 0, "new", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	
}

//MOVE CALENDAR ITEM
if(isset ($action) && $action == "resetdate"){
	
	$subcategory_id = 0;
	if(isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) && ($_POST['subcategory_id']) <> 0){ 
		$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	}

	$startdate = mres($_POST['start']);
	$enddate = mres($_POST['end']);
	$eventid = mres($_POST['eventid']);
	
	//check if ForAll is set
	$check_ForAll = mysqli_query($con,"SELECT ForAll FROM calendar WHERE id='$eventid' AND " . $superuser . "_id = $superuser_id");
	$row_ForAll = mysqli_fetch_array($check_ForAll);
	$ForAll = $row_ForAll['ForAll'];
	
	
	$update = mysqli_query($con,"UPDATE calendar SET 
	startdate=CONCAT('$startdate ', TIME(startdate)),
	enddate=CONCAT('$enddate ', TIME(enddate)) 
	where id='$eventid' AND " . $superuser . "_id = $superuser_id");
	
	//delete all RSVPs when change of date
	$delete_RSVPs = mysqli_query($con,"DELETE FROM RSVP WHERE event_id='$eventid'");
		
	if($update)
		echo json_encode(array('status'=>'success'));
	else
		echo json_encode(array('status'=>'failed'));
	
    LogEntrySuper($eventid, $superuser_id, $superuser, "0", "0", $subcategory_id, $ForAll, "1");
    LogAction($eventid, $superuser, $superuser_id, $subcategory_id, 0, 0, "resetdate", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//COPY OR MOVE A CALENDAR ITEM
if(isset ($action) && $action == "movecopyevent"){
	
	$date = date('Y-m-d');
	$subcategory_id = 0;
	if(isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) && ($_POST['subcategory_id']) <> 0){ 
		$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	}

	$startdate = mres($_POST['start']);
	$enddate = mres($_POST['end']);
	$eventid = mres($_POST['eventid']);
	
	$type = mres($_POST['type']);
	
	$types = array("move", "copy", "cancel");
	if(in_array($type, $types)){
		
	//check if ForAll is set
	$check_ForAll = mysqli_query($con,"SELECT ForAll FROM calendar WHERE id='$eventid' AND " . $superuser . "_id = $superuser_id");
	$row_ForAll = mysqli_fetch_array($check_ForAll);
	$ForAll = $row_ForAll['ForAll'];
		
	if(isset($type) && $type == "move"){
	$execute = mysqli_query($con,"UPDATE calendar SET 
	startdate=CONCAT('$startdate ', TIME(startdate)),
	enddate=CONCAT('$enddate ', TIME(enddate)) 
	where id='$eventid' AND " . $superuser . "_id = '$superuser_id'");
	
	//delete all RSVPs when change of date
	$delete_RSVPs = mysqli_query($con,"DELETE FROM RSVP WHERE event_id='$eventid'");
	
	if($execute)
	echo json_encode(array('status'=>'success'));
	else
	echo json_encode(array('status'=>'failed'));
	
	}
	
	if(isset($type) && $type == "copy"){
		$execute = mysqli_query($con, "INSERT INTO calendar
			(club_id, subcategory, ForAll, ForSome, ForKids, ForAdults, ForManual, invite, title, startdate, enddate, showtimeframe, subject, comment, location, street, number, postal, city, weather, allDay, repeat_type, repeat_times, repeat_id, sync_id)
			SELECT club_id, subcategory, ForAll, ForSome, ForKids, ForAdults, ForManual, invite, title, CONCAT('$startdate ', TIME(startdate)), CONCAT('$enddate ', TIME(enddate)), showtimeframe, subject, comment, location, street, number, postal, city, weather, allDay, repeat_type, repeat_times, repeat_id, sync_id
			FROM calendar WHERE id = '$eventid' AND " . $superuser . "_id = '$superuser_id'");
			
			$new_event_id = mysqli_insert_id($con);
		
		//check for extra's
		//attachments
		$check_attachment = mysqli_query($con, "SELECT id FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = '$superuser_id' AND event_id = '$eventid'");
		if(mysqli_num_rows($check_attachment) > 0){
			while($row_attachment = mysqli_fetch_array($check_attachment)){
			mysqli_query($con, "INSERT INTO " . $superuser . "s_documents 
			(log_user_id, date, name, file, author, serial, date_published, comment, " . $superuser . "_id, event_id, subcategory, tags, forall, forsome, permanent, reused, timestamp, filetype, isURL)
			SELECT log_user_id, '$date', name, file, author, serial, date_published, comment, '$superuser_id', '$new_event_id', subcategory, tags, forall, forsome, permanent, reused, timestamp, filetype, isURL
			FROM 
			" . $superuser . "s_documents
			WHERE " . $superuser . "_id = '$superuser_id' AND event_id = '$eventid'");
			}
		}
		
		//paybuttons
		$check_paybuttons = mysqli_query($con, "SELECT " . $superuser . "s_paybuttons.*, paybuttons.* FROM " . $superuser . "s_paybuttons
		  JOIN paybuttons ON " . $superuser . "s_paybuttons.button_id = paybuttons.id 
		  WHERE " . $superuser . "s_paybuttons.event_id = $eventid AND " . $superuser . "s_paybuttons." . $superuser . "_id = $superuser_id");
		  
		  if(mysqli_num_rows($check_paybuttons) > 0){
		  $row_paybuttons = mysqli_fetch_array($check_paybuttons);
		  $button_id = $row_paybuttons['button_id'];
		  
		  mysqli_query($con,"INSERT INTO " . $superuser . "s_paybuttons 
		  (button_id,
		  event_id,
		  " . $superuser . "_id,
		  subcategory_id) VALUES 
		  ('$button_id','$new_event_id','$superuser_id','$subcategory_id')");  
		}
		
		//survey
		$check_survey = mysqli_query($con, "SELECT * FROM survey_questions WHERE (event_id = '$eventid' OR FIND_IN_SET($eventid, event_id_list)) AND " . $superuser . "_id = '$superuser_id' AND archive = 0");
		  if(mysqli_num_rows($check_survey) > 0){
			  $row_survey = mysqli_fetch_array($check_survey);
			  $question_id = $row_survey['id'];
			  $event_id_list = $row_survey['event_id_list'];
				if($event_id_list == ""){
				$newString = $new_event_id;	
				}
				else { 
				$newString = addtoString($event_id_list, $new_event_id);
				}
			  mysqli_query($con, "UPDATE survey_questions SET event_id_list = '$newString' WHERE id = $question_id"); 
		  }

		if($execute)
		echo json_encode(array('status'=>'success'));
		else
		echo json_encode(array('status'=>'failed'));

	}
	
	LogEntrySuper($eventid, $superuser_id, $superuser, "0", "0", $subcategory_id, $ForAll, "1");

	LogAction($eventid, $superuser, $superuser_id, $subcategory_id, 0, 0, "movecopydate", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
	
	}
}

//REMOVE A CALENDAR ITEM
if(isset ($action) && $action == "remove"){

	$subcategory_id = 0;

	if(isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) && ($_POST['subcategory_id']) <> 0){ 
		$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	}
	
	$eventid = mres($_POST['eventid']);

	//First Delete attachments if not permanent
	$select_query = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND event_id = $eventid AND permanent = 0");
	if(mysqli_num_rows($select_query) > 0){ 
	while($row = mysqli_fetch_array($select_query)){
	
	mysqli_query($con,"DELETE FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND event_id = $eventid AND permanent = 0");
	
	//check if reused
	$select_duplicate = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE file = '$row[file]' AND " . $superuser . "_id = $superuser_id");	
	if(mysqli_num_rows($select_duplicate) == 0){
	
	//document
	if($row['filetype'] == 0){ 
	$filename = "../data/" . $superuser . "s_documents/$superuser_id/$row[file]";
	$thumb = "../data/" . $superuser . "s_documents/" . $superuser_id . "/thumb_" . $row['file'];
	if(file_exists($filename)){ 
	unlink($filename);
	}
	if(file_exists($thumb)){ 
	unlink($thumb);
	}
	}
	
	//url -> geen upload dus geen unlink
	
	//fotoalbum
	if(($row['filetype'] == 2) && ($row['isURL'] == 0)){ 
	recursiveRemoveDirectory("../data/" . $superuser . "s_images/$superuser_id/$row[file]/");
	}
	//video
	if(($row['filetype'] == 3) && ($row['isURL'] == 0)){ 
	$filename = "../data/" . $superuser . "s_videos/$superuser_id/$row[file]";
	if(file_exists($filename)){ 
	unlink($filename);
	}
	}		
	}
	
	}
	}

	//delete FROM table survey - survey answer options - survey answers
	//mysqli_query($con,"DELETE FROM survey_questions WHERE event_id = $eventid  AND " . $superuser . "_id = $superuser_id");
	//mysqli_query($con,"DELETE FROM survey_answer_options WHERE event_id = $eventid  AND " . $superuser . "_id = $superuser_id");
	//mysqli_query($con,"DELETE FROM survey_answers WHERE event_id = $eventid  AND " . $superuser . "_id = $superuser_id");
	
	//delete from table RSVP
	mysqli_query($con,"DELETE FROM RSVP WHERE event_id = $eventid");
	
	//delete from table RSVP
	mysqli_query($con,"DELETE FROM RSVP_comments WHERE event_id = $eventid");
	
	//delete from table mail_read
	mysqli_query($con,"DELETE FROM mail_read WHERE event_id = $eventid");
	
	//delete from table chat
	$select_chat = mysqli_query($con,"SELECT * FROM chat WHERE event_id = $eventid AND pic <> ''");
	if(mysqli_num_rows($select_chat) > 0){ 
	while($row_chat = mysqli_fetch_array($select_chat)){
	$filename = "../data/" . $superuser . "s_images/$superuser_id/chat/$row_chat[pic]";
	unlink($filename);
	}
	}
	mysqli_query($con,"DELETE FROM chat WHERE event_id = $eventid");

	//now delete event from calendar
	$delete = mysqli_query($con,"DELETE FROM calendar where id='$eventid' AND " . $superuser . "_id = $superuser_id");
	
	if($delete)
		echo json_encode(array('status'=>'success'));
	else
		echo json_encode(array('status'=>'failed'));
	
	LogAction($eventid, $superuser, $superuser_id, $subcategory_id, 0, 0, "delete", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//REMOVE MULTIPLE CALENDAR ITEMS
if(isset ($action) && $action == "removeAll"){
	
	$repeat_id = mres($_POST['repeat_id']);
	
	$subcategory_id = 0;
	if(isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) && ($_POST['subcategory_id']) <> 0){ 
		$subcategory_id = sres($_POST['subcategory_id'], $superuser_id, $superuser);
	}


	//get all eventIDs of repeated Item
	$select_events = mysqli_query($con,"SELECT id FROM calendar WHERE repeat_id='$repeat_id' AND " . $superuser . "_id = '$superuser_id'");
	if(mysqli_num_rows($select_events) > 0){ 
	while($row_event = mysqli_fetch_array($select_events)){
	
	//First Delete attachments if not permanent
	$select_query = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE event_id = $row_event[id] AND permanent = 0 AND " . $superuser . "_id = $superuser_id");
	if(mysqli_num_rows($select_query) > 0){ 
	while($row = mysqli_fetch_array($select_query)){
	mysqli_query($con,"DELETE FROM " . $superuser . "s_documents WHERE event_id = $row_event[id] AND permanent = 0 AND " . $superuser . "_id = $superuser_id");
	
	//check if reused
	$select_duplicate = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE file = '$row[file]' AND " . $superuser . "_id = $superuser_id");	
	if(mysqli_num_rows($select_duplicate) == 0){
	
	//document
	if($row['filetype'] == 0){ 
	$filename = "../data/" . $superuser . "s_documents/$superuser_id/$row[file]";
	$thumb = "../data/" . $superuser . "s_documents/" . $superuser_id . "/thumb_" . $row['file'];
	if(file_exists($filename)){ 
	unlink($filename);
	}
	if(file_exists($thumb)){ 
	unlink($thumb);
	}
	}
	
	//url -> geen upload dus geen unlink
	
	//fotoalbum
	if(($row['filetype'] == 2) && ($row['isURL'] == 0)){ 
	recursiveRemoveDirectory("../data/" . $superuser . "s_images/$superuser_id/$row[file]/");
	}
	//video
	if(($row['filetype'] == 3) && ($row['isURL'] == 0)){ 
	$filename = "../data/" . $superuser . "s_videos/$superuser_id/$row[file]";
	if(file_exists($filename)){ 
	unlink($filename);
	}
	}		
	}
	
	}
	}
	
	//delete FROM table survey - survey answer options - survey answers
	mysqli_query($con,"DELETE FROM survey_questions WHERE event_id = $row_event[id]  AND " . $superuser . "_id = $superuser_id");
	mysqli_query($con,"DELETE FROM survey_answer_options WHERE event_id = $row_event[id]  AND " . $superuser . "_id = $superuser_id");
	mysqli_query($con,"DELETE FROM survey_answers WHERE event_id = $row_event[id]  AND " . $superuser . "_id = $superuser_id");
	
	//delete from table RSVP
	mysqli_query($con,"DELETE FROM RSVP WHERE event_id = $row_event[id]");
	
	//delete from table RSVP
	mysqli_query($con,"DELETE FROM RSVP_comments WHERE event_id = $row_event[id]");
	
	//delete from table mail_read
	mysqli_query($con,"DELETE FROM mail_read WHERE event_id = $row_event[id]");
	
	//delete from table chat
	$select_chat = mysqli_query($con,"SELECT * FROM chat WHERE event_id = $row_event[id] AND pic <> ''");
	if(mysqli_num_rows($select_chat) > 0){ 
	while($row_chat = mysqli_fetch_array($select_chat)){
	$filename = "../data/" . $superuser . "s_images/$superuser_id/chat/$row_chat[pic]";
	unlink($filename);
	}
	}
	mysqli_query($con,"DELETE FROM chat WHERE event_id = $row_event[id]");
	
	}
	}
	
	//delete from calendar
	$delete = mysqli_query($con,"DELETE FROM calendar where repeat_id='$repeat_id' AND " . $superuser . "_id = $superuser_id");

	
	if($delete)
		echo json_encode(array('status'=>'success'));
	else
		echo json_encode(array('status'=>'failed'));
	
	LogAction(0, $superuser, $superuser_id, $subcategory_id, 0, 0, "removeAll", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));
}

//READ DATABASE IMPORT FROM KBVB
if(isset ($action) && $action == "databaseFootballClubs"){

// strip tags may not be the best method for your project to apply extra layer of security but fits needs for this tutorial 
$search = strip_tags(trim($_GET['q'])); 
$table = strip_tags(trim($_GET['table'])); 

// Do Prepared Query 
$query = mysqli_query($con, "SELECT HOME,REGNUMBERHOME,DIVISION FROM $table WHERE HOME LIKE '%$search%' OR REGNUMBERHOME LIKE '%$search%' GROUP BY DIVISION");

$json = [];
while($row = mysqli_fetch_array($query,MYSQLI_ASSOC)){
     $json[] = ['id'=>$row['REGNUMBERHOME'], 'text'=>$row['HOME'] . " - divisie " . $row['DIVISION'], 'division'=>$row['DIVISION']];
}

echo json_encode($json);
}


//COUNT MAIL RECIPIENTS (FOR STATISTICS)
if(isset ($action) && $action == "CountRecipients"){
  	
  	$subcategory_id = sres($_POST["subcategory_id"], $superuser_id, $superuser);
  	
  	if(isset($_POST["id_list_manual"]) && !empty($_POST["id_list_manual"])){
	$id_list_manual	= $_POST["id_list_manual"];
	}
	else {
	$id_list_manual = 0;
	}
	if(isset($_POST["id_list_kids"]) && !empty($_POST["id_list_kids"])){
	$id_list_kids	= $_POST["id_list_kids"];
	}
	else {
	$id_list_kids = 0;
	}
	if(isset($_POST["id_list_adults"]) && !empty($_POST["id_list_adults"])){
	$id_list_adults	= $_POST["id_list_adults"];
	}
	else {
	$id_list_adults = 0;
	}
	
	$parent_list = getParentIdsFromKidIds($id_list_kids, $superuser_id, $superuser, $subcategory_id);
	$adult_list = JoinedParentsFromParentId($id_list_adults, $superuser_id, $superuser, $subcategory_id);

	$result = mysqli_query($con,"SELECT 0 AS manual, id AS adult_id, 0 AS manual_id, username, email, activate_code AS key_code FROM users WHERE id IN ($parent_list,$adult_list)
UNION
SELECT 1 AS manual, 0 AS adult_id, id AS manual_id, AES_DECRYPT(username, SHA1('$pass')) AS username, AES_DECRYPT(email, SHA1('$pass')) AS email, key_code FROM kids_" . $superuser . "s_manual WHERE id IN($id_list_manual) AND AES_DECRYPT(email, SHA1('$pass')) <> ''");
    
    $data['total_recipients'] = mysqli_num_rows($result);
	echo json_encode($data);
	exit;
  
}

//MOVE SPONSOR (in login/profile.php)
if(isset ($action) && $action == "MoveSponsor"){

  $row_order=mres($_POST['row_order']);
  $sponsor_id=mres($_POST['sponsor_id']);
  $move_direction = mres($_POST['move_direction']);
  
  if($move_direction == "down"){
	  //select previous entry and switch row_order numbers
	  $previous_sql = mysqli_query($con, "SELECT * FROM advertising WHERE row_order < $row_order AND " . $superuser . "_id = $superuser_id ORDER BY id DESC LIMIT 1");
	  if(mysqli_num_rows($previous_sql) > 0){
		  $row_previous = mysqli_fetch_array($previous_sql);
		  
		  mysqli_query($con, "UPDATE advertising SET row_order = $row_previous[row_order] WHERE " . $superuser . "_id = $superuser_id AND id = $sponsor_id");
		  mysqli_query($con, "UPDATE advertising SET row_order = $row_order WHERE " . $superuser . "_id = $superuser_id AND id = $row_previous[id]");
		  
		  
	  } 
  }
  if($move_direction == "up"){
	  //select previous entry and switch row_order numbers
	  $previous_sql = mysqli_query($con, "SELECT * FROM advertising WHERE row_order > $row_order AND " . $superuser . "_id = $superuser_id ORDER BY id ASC LIMIT 1");
	  if(mysqli_num_rows($previous_sql) > 0){
		  $row_previous = mysqli_fetch_array($previous_sql);
		  
		  mysqli_query($con, "UPDATE advertising SET row_order = $row_previous[row_order] WHERE " . $superuser . "_id = $superuser_id AND id = $sponsor_id");
		  mysqli_query($con, "UPDATE advertising SET row_order = $row_order WHERE " . $superuser . "_id = $superuser_id AND id = $row_previous[id]");
		  
		  
	  } 
  }
  
  LogAction(0, $superuser, $superuser_id, 0, 0, 0, "MoveSponsor", __LINE__, print_r($_POST, true), mres(mysqli_error($con)));

}
?>
