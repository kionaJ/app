<?php 
include('login/includes/api.php');
logged_in();
include('functions.php');
include('superuser_security.php');

//////////////////////////////////////////////////////////////////
//SUPERUSER (CLUB) HOMEPAGE
//////////////////////////////////////////////////////////////////
//set timezone
date_default_timezone_set('Europe/Amsterdam'); 

if(is_allowedLevel("2")) {
//pt_sendinblue_subscribe(array(6));
}
if(!is_allowedLevel("2")) {
//pt_sendinblue_addtolist("6");
}

$goto_subcategory = 0;
$BigNotif = 0;
$OpenMembers = 0;
$order_id = 0;

if(isset($_GET['goto_subcategory']) && is_numeric($_GET['goto_subcategory']) && !empty($_GET['goto_subcategory']) && $_GET['goto_subcategory'] <> 0){
$goto_subcategory=mres($_GET['goto_subcategory']);

if(isset($_GET['BigNotif']) && is_numeric($_GET['BigNotif']) && !empty($_GET['BigNotif']) && $_GET['BigNotif'] <> 0){ 
$BigNotif=mres($_GET['BigNotif']);
}
if(isset($_GET['OpenMembers']) && is_numeric($_GET['OpenMembers']) && !empty($_GET['OpenMembers']) && $_GET['OpenMembers'] <> 0){ 
$OpenMembers=mres($_GET['OpenMembers']);
}
}

//return from mollie page (premium payment)
if(isset($_GET['order_id'])){
$order_id = mres($_GET["order_id"]);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="description" content="Playday. An application for various clubs to communicate and organise all your admin.">
<meta name="<?php echo $meta_name ?>" content="<?php echo $meta_content ?>">
<?php include("header.php") ?>
<!-- loading robot script -->
<script type="text/javascript">
	// Wait for window load
	$(window).on("load", function(){
		// Animate loader off screen
		$(".se-pre-con").fadeOut("slow");;
	});   
</script>

</head>
<body ontouchstart="">
<div class="se-pre-con"></div>
		
<div class="container" id="content_superuser" align="center">
<?php if(is_allowedLevel("2")) {
	include("accept_terms.php");
	} 
	
	
	//start page
 	//query: check if club has added subcategories
	$sql = mysqli_query($con,"SELECT * FROM " . $superuser . "s_categories WHERE " . $superuser . "_id = $superuser_id $string1 ORDER BY row_order, start_year, cat_name");
		$numrows = mysqli_num_rows($sql);
	
	//check if subcategories are added, if not, give notification to add subcategory
	if($numrows == 0)
		{ 
		
		if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM referral WHERE " . $superuser . "_id = $superuser_id")) == 0){ 
		include("referral.php");
		}
		
		//no categories are added
		?><br>
		<div id="rcorners_flat" style="padding: 15px">
		<br>
		<div align="center">
			<img src="<?php echo $logo_path ?>" alt="category image" width="60px">

		<h3>Welkom bij <?php echo $appname ?>,<br><?php echo $superuser_name ?>!</h3>
		<br>
		Laten we eraan beginnen!<br><br>
		<a href="superuser_subcategory_new.php" class="btn btn-success btn-block btn-sm" style="max-width: 400px" role="button"><span class="fa fa-plus"></span> Maak een subgroep</a>
		<br>
		</div>
		</div>

		<?php
		if(is_allowedLevel("2")){
		$today = date('Y-m-d');
		//update table mails_automatic to send a welcome mail (first login)
		$AM_select0 = mysqli_query($con, "SELECT * FROM mails_automatic WHERE " . $superuser . "_id = $superuser_id AND mail = 0");
		if(mysqli_num_rows($AM_select0) == 0){
			mysqli_query($con, "INSERT INTO mails_automatic (date," . $superuser . "_id,mail,sent) VALUES ('$today', $superuser_id, 0, 1)");
			
		//now send welcome mail
		$message_body = file_get_contents('mail_templates/mail0.html');
		userSendMail6($mailfromaddress, userValueSuper($superuser_id,$superuser,"email"), $appname . ": maximaal profiteren doe je zo!", $message_body); //Send Mail
		}
		
		//welcome mail is already sent: next step
		else {
			$row_AM_select0 = mysqli_fetch_array($AM_select0);
			$date_welcome = $row_AM_select0['date'];
			if(strtotime($date_welcome) < strtotime('-2 day')){
				//check if next mail is already sent
				$AM_select0 = mysqli_query($con, "SELECT * FROM mails_automatic WHERE " . $superuser . "_id = $superuser_id AND mail = 1");
				if(mysqli_num_rows($AM_select0) == 0){
					mysqli_query($con, "INSERT INTO mails_automatic date," . $superuser . "_id,mail,sent) VALUES ('$today', $superuser_id, 1, 1)");
					
				//now send welcome mail
				$message_body = file_get_contents('mail_templates/mail1.html');
				userSendMail6($mailfromaddress, userValueSuper($superuser_id,$superuser,"email"), $appname . ": hulp nodig?", $message_body); //Send Mail
				}	
				
			}	
		}
	 }


	}
	else {
	?>
	<br>
	<div class="SearchBox" style="display: none"></div>
	<div id="LoadSubgroup"></div>
	<?php
		}
	$showtop = true;
	include("top.php");
	?>
<br>
</div>
<div class="modal fade userinfo" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="UserInfoDiv"></div>
		</div>
	</div>
</div>
<br>
<?php include("footer.php"); ?>
<!-- Slider Javascript file -->
<script type="text/javascript">
	var superuser = "<?php echo $superuser ?>";
	var superuser_id = "<?php echo $superuser_id ?>";
	var order_id = "<?php echo $order_id ?>";
	var goto_subcategory = "<?php echo $goto_subcategory ?>";
	var BigNotif = "<?php echo $BigNotif ?>";
	var OpenMembers = "<?php echo $OpenMembers ?>";
</script>
<script src="js/link_changer.js" type="text/javascript"></script>
<script src="js/superuser_js.js?time=<?php echo time() ?>" type="text/javascript"></script>

<script type="text/javascript">
$(document).ready(function() {
    $('.nav-swipe').slideAndSwipe();
	
$(document).off('click','.LoadSubgroup').on("click",".LoadSubgroup",function(e) {
    <?php if ($detect->isMobile()) { ?>
    setTimeout(function() {
		$("#mySidenav").css("width", '0');
	}, 800);
    <?php } ?>
	var subcategory_id = this.id;
	$('#LoadSubgroup').load("superuser_subcategory.php?get_"+superuser+"_id="+superuser_id+"&subcategory_id="+subcategory_id+"&order_id="+order_id);
});
    
if(goto_subcategory != 0){ 
	$('#LoadSubgroup').load("superuser_subcategory.php?get_"+superuser+"_id="+superuser_id+"&subcategory_id="+goto_subcategory+"&BigNotif="+BigNotif+"&OpenMembers="+OpenMembers);
}
else { 
    $(".LoadSubgroup:first").trigger( "click" );
}	
});

</script>
<div class="loadingmodal"><!-- Place at bottom of page --></div>
</body>
</html>
