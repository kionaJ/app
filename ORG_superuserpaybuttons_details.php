<?php 
// Report all PHP errors (see changelog)
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
//do not require login
include('login/includes/api.php');
include('functions.php');

//////////////////////////////////////////////////////////////////
//SHOW THE PAYBUTTON PAGE
//////////////////////////////////////////////////////////////////

// Include and instantiate the class.
require_once 'plugins/Mobile_Detect/Mobile_Detect.php';
$detect = new Mobile_Detect;

$button_id = 0;
$type = 0;
$adult_id = 0;
$kid_id = 0;
$manual_id = 0;
$superuser_id = 0;
$superuser = "club";

if(isset($_GET['button_id']) && is_numeric($_GET['button_id']) && !empty($_GET['button_id']) && ($_GET['button_id']) <> 0){  
	$button_id=mres($_GET['button_id']);
}
if(isset($_GET['type']) && is_numeric($_GET['type']) && !empty($_GET['type']) && ($_GET['type']) <> 0){  
	$type=mres($_GET['type']);
}
if(isset($_GET['club_id']) && is_numeric($_GET['club_id']) && !empty($_GET['club_id']) && ($_GET['club_id']) <> 0){  
	$superuser = "club";
	$superuser_id=mres($_GET['club_id']);
}
if(isset($_GET['adult_id']) && is_numeric($_GET['adult_id']) && !empty($_GET['adult_id']) && ($_GET['adult_id']) <> 0){  
	$adult_id=mres($_GET['adult_id']);
}
if(isset($_GET['kid_id']) && is_numeric($_GET['kid_id']) && !empty($_GET['kid_id']) && ($_GET['kid_id']) <> 0){  
	$kid_id=mres($_GET['kid_id']);
}
if(isset($_GET['manual_id']) && is_numeric($_GET['manual_id']) && !empty($_GET['manual_id']) && ($_GET['manual_id']) <> 0){  
	$manual_id=mres($_GET['manual_id']);
}


$for = "";
$test = 0;

//loop through url: start with adult_id
if($adult_id <> 0){
		
		//kid_id = 0
		if($kid_id == 0){
		//check if adult is member
		$check = mysqli_query($con, "SELECT parent_id FROM parents_" . $superuser . "s WHERE parent_id = $adult_id AND " . $superuser . "_id = $superuser_id");
		if(mysqli_num_rows($check) > 0){
			$for = userValue($adult_id , "username");
		}
		
		//check if joined parent is member
		else { 
			$adult_list = JoinedParentsFromParentId($adult_id);
			$check = mysqli_query($con, "SELECT parent_id FROM parents_" . $superuser . "s WHERE parent_id IN($adult_list) AND " . $superuser . "_id = $superuser_id ORDER BY parent_id ASC");
			if(mysqli_num_rows($check) > 0){
				$row_check = mysqli_fetch_array($check);
				$for = userValue($row_check['parent_id'] , "username");
			} 
			else {
				//adult or joined adult is not member: give first child
				$kids_list = KidsFromParentId($adult_id);
				$check = mysqli_query($con, "SELECT kid_id FROM kids_" . $superuser . "s WHERE kid_id IN($kids_list) AND " . $superuser . "_id = $superuser_id ORDER BY kid_id ASC");
				if(mysqli_num_rows($check) > 0){
					$row_check = mysqli_fetch_array($check);
					$token = generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, 0, $row_check['kid_id'], 0);
					header("location: superuser_paybuttons_details.php?" . $superuser . "_id=$superuser_id&type=$type&button_id=$button_id&adult_id=0&kid_id=" . $row_check['kid_id'] . "&manual_id=0&token=" . $token);
				}
				else {
					header("location: page_not_permitted.php");
				}
			}
		}
		}
		//kid_id is not 0 in url
		else {
			//check if kid is from parent
			$kids_list = explode(',', KidsFromParentId($adult_id));
			if (in_array($kid_id, $kids_list)) {
				$check = mysqli_query($con, "SELECT id FROM kids_" . $superuser . "s WHERE " . $superuser . "_id = $superuser_id AND kid_id  = $kid_id");
				  if(mysqli_num_rows($check) > 0){
					  	$token = generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, 0, $kid_id, 0);
						header("location: superuser_paybuttons_details.php?" . $superuser . "_id=$superuser_id&type=$type&button_id=$button_id&adult_id=0&kid_id=" . $kid_id . "&manual_id=0&token=" . $token);
					} 
				   else {
						header("location: page_not_permitted.php");
				  }
			}
			else {
				header("location: page_not_permitted.php");
			}
		}
}

//second, adult_id = 0, so check kid_id
elseif($kid_id <> 0){
	  
	  //check if kid is member
	  $check = mysqli_query($con, "SELECT id FROM kids_" . $superuser . "s WHERE " . $superuser . "_id = $superuser_id AND kid_id  = $kid_id");
	  if(mysqli_num_rows($check) > 0){
			$for = userValueKid($kid_id , "name") . " " . userValueKid($kid_id , "surname");
		} 
	   else {
			header("location: page_not_permitted.php");
	  }
	  
}
elseif($manual_id <> 0){
	$check = mysqli_query($con, "SELECT id FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id = $superuser_id AND id = $manual_id");
	  if(mysqli_num_rows($check) > 0){
			$for = userValueManual($manual_id, $superuser, $superuser_id, "name") . " " . userValueManual($manual_id, $superuser, $superuser_id, "surname");
		} 
	   else {
			header("location: page_not_permitted.php");
	  }
	
}
else {
	$test = "1";
}

//generate token
if($test == 0){
$newtoken_encrypted = generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, $adult_id, $kid_id, $manual_id);

if(($superuser_id == "1471") && !isset($_GET['token'])){
	header("location: superuser_paybuttons_details.php?" . $superuser . "_id=$superuser_id&type=$type&button_id=$button_id&adult_id=$adult_id&kid_id=$kid_id&manual_id=$manual_id&token=".$newtoken_encrypted);
}


if(isset($_GET['token']) && !empty($_GET['token'])){
$gettoken=mres($_GET['token']);

if($gettoken <> $newtoken_encrypted){
	 //echo "<small>nm</small>";
	 //error_log("<br>new: " . $newtoken_encrypted . " decrypted: " . base64_url_decode($newtoken_encrypted) . "<br>get: " . $gettoken . " decrypted: " . base64_url_decode($gettoken));
	 header("location: page_not_permitted.php");
}
}
else {
	//echo "<small>nt</small>";
	header("location: page_not_permitted.php");	

}
}

//create preliminary order id
$prem_order_id = mt_rand() . "_" . time();


//check payed
//check if user has already payed
$check_payed = mysqli_query($con, "SELECT * FROM paybuttons_results WHERE button_id = $button_id AND adult_id = $adult_id AND kid_id = $kid_id AND manual_id = $manual_id AND " . $superuser . "_id = $superuser_id AND status_id = 1");
if(mysqli_num_rows($check_payed) > 0){
	$row_check_payed = mysqli_fetch_array($check_payed);
	$time_payed = $row_check_payed['timestamp'];
	$payed = "1";
	}
	else {
		$payed = "0";
	}

?>
<!DOCTYPE html>
<html>
<head>
<?php include("header.php") ?>
<style>
.product-details {
  float: left;
  width: 66%;
}
 
.product-price {
  float: left;
  width: 12%;
}
 
.product-quantity {
  float: left;
  width: 10%;
}
 
.product-line-price {
  float: left;
  width: 12%;
  text-align: right;
}
 
/* This is used as the traditional .clearfix class */
.group:before, .shopping-cart:before, .column-labels:before, .product:before, .totals-item:before,
.group:after,
.shopping-cart:after,
.column-labels:after,
.product:after,
.totals-item:after {
  content: '';
  display: table;
}
 
.group:after, .shopping-cart:after, .column-labels:after, .product:after, .totals-item:after {
  clear: both;
}
 
.group, .shopping-cart, .column-labels, .product, .totals-item {
  zoom: 1;
}
 
/* Apply clearfix in a few places */
/* Apply dollar signs */
.product .product-price:before, .product .product-line-price:before, .totals-value:before {
  content: '€ ';
}

 
.shopping-cart {
  margin-top: 20px;
}
 
/* Column headers */
.column-labels label {
  padding-bottom: 15px;
  margin-bottom: 15px;
  border-bottom: 1px solid #eee;
}
.column-labels .product-image, .column-labels .product-details, .column-labels .product-removal {
  text-indent: -9999px;
}
 
/* Product entries */
.product {
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}

.product .product-details .product-title {
  margin-right: 20px;
}
.product .product-details .product-description {
  margin: 5px 20px 5px 0;
  line-height: 1.4em;
}
.product .product-quantity input {
  width: 40px;
}


/* Totals section */
.totals .totals-item {
  float: right;
  clear: both;
  width: 100%;
  margin-bottom: 10px;
}
.totals .totals-item label {
  float: left;
  clear: both;
  width: 79%;
  text-align: right;
}
.totals .totals-item .totals-value {
  float: right;
  width: 21%;
  text-align: right;
}
.totals .totals-item-total {
  
}
 
.checkout {
  float: right;
  border: 0;
  margin-top: 20px;
  padding: 6px 25px;
  background-color: #6b6;
  color: #fff;
  font-size: 25px;
  border-radius: 3px;
}
 
.checkout:hover {
  background-color: #494;
}
 
/* Make adjustments for tablet */
@media screen and (max-width: 650px) {
  .shopping-cart {
    margin: 0;
    padding-top: 20px;
    border-top: 1px solid #eee;
  }
 
  .column-labels {
    display: none;
  }

  .product-details {
    float: none;
    margin-bottom: 10px;
    width: auto;
  }
 
  .product-price {
    clear: both;
    width: 50px;
  }
 
  .product-quantity {
    width: 100px;
  }
  .product-quantity input {
	margin-top: -22px;
    margin-left: 60px;
  }
 
  .product-quantity:before {
    content: 'x';
  }
 
  
  .product-line-price {
    float: right;
    width: 70px;
  }
}

</style>
</head>
<body>
<?php

//show success or fail/cancel page
if(isset($_GET['action'])) $action=$_GET['action'];
if(isset($_POST['action'])) $action=$_POST['action'];

//check order (MOLLIE/PAYPAL)
if(isset($_GET['order_id'])){ 
$order_id=mres($_GET['order_id']);
$sql_status = mysqli_query($con,"SELECT * FROM paybuttons_results WHERE order_id = '$order_id' AND " . $superuser . "_id = $superuser_id AND button_id = $button_id AND adult_id = $adult_id AND kid_id = $kid_id AND manual_id = $manual_id");
if(mysqli_num_rows($sql_status) > 0){
	$row_status = mysqli_fetch_array($sql_status);
	$status_id = $row_status['status_id'];
	if($status_id == "1"){
		$action = "success";
	}
	if($status_id == "2"){
		$action = "cancel";
		//archive order if exists
		mysqli_query($con, "UPDATE paybuttons_form_answers SET archive = 1 WHERE " . $superuser . "_id = $superuser_id AND button_id = $button_id AND adult_id = $adult_id AND kid_id = $kid_id AND manual_id = $manual_id AND order_id = '$order_id'");
	}
}
else {
	$action = "cancel";
	//archive order if exists
	mysqli_query($con, "UPDATE paybuttons_form_answers SET archive = 1 WHERE " . $superuser . "_id = $superuser_id AND button_id = $button_id AND adult_id = $adult_id AND kid_id = $kid_id AND manual_id = $manual_id AND order_id = '$order_id'");
}
}

?>
<br>
<?php
	
//QUERY PAYBUTTON
$sql = mysqli_query($con,"SELECT * FROM paybuttons WHERE " . $superuser . "_id = $superuser_id AND id = $button_id AND enddate >= CURDATE()");
if(mysqli_num_rows($sql) > 0){ 
$row_button = mysqli_fetch_array($sql);
$enddate_format = "" . formatDate($row_button['enddate'], "EEEE dd MMMM");

?>
<div class="container" style="max-width: 800px">	
<div id="rcorners_flat">
		
		<ul class="media-list">
				  <li class="media">
				    <div class="media-left">
					 <?php showAvatarSuper($superuser_id, $superuser); ?>
					 
				    </div>
				    <div class="media-body">
				      <h4 class="media-heading" style="border-bottom: 1px #cccccc solid;"><strong><?php echo userValueSuper($superuser_id,$superuser,"username") ?></strong></h4>
				       <i><font color=gray>Betaalpagina '<?php echo $row_button['title'] ?>'.</font></i><br>
				      </div>
				  </li>
				</ul>
			<br><br>
			<?php if($row_button['picture'] <> ""){ ?>
			<div align="center">
			<img width="80%" src="/data/<?php echo $superuser ?>s_webshop/<?php echo $superuser_id ?>/<?php echo $row_button['picture'] ?>" style="margin-top: -25px; border-radius: 5px; margin-bottom: 10px">
			</div>
			<?php } ?>
			<div style="background-color: transparent">
			<?php 
			if(isset ($action) && $action == "success"){
			?>
			<div align="center">
				<img src="img/animat-checkmark-color.gif" width="250px">
				<h4>Bedankt voor je betaling!</h4>
				<strong>We hebben de betaling voor <?php echo $for ?> ontvangen!</strong><br><br>
				
				<?php $token = generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, $adult_id, $kid_id, $manual_id); ?>
				<a href="<?php echo $full_path ?>/superuser_paybuttons_details.php?<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&type=<?php echo $type ?>&button_id=<?php echo $button_id ?>&adult_id=<?php echo $adult_id ?>&kid_id=<?php echo $kid_id ?>&manual_id=<?php echo $manual_id ?>&token=<?php echo $token ?>" class="btn btn-sm btn-block btn-primary"><span class="fa fa-credit-card"></span> Open betaalpagina opnieuw</a>
				<a href="<?php echo $full_path . "/openlink.php" ?>" class="btn btn-sm btn-block btn-success"><span class="fa fa-sign-out"></span> Terug naar <?php echo $appname ?></a>

			</div>
			<?php
			}
			elseif(isset ($action) && $action == "cancel"){
			?>
			<div align="center">
				<img src="img/animat-x-color.gif" width="250px">
				<h4>Je hebt je betaling geannuleerd...</h4>
				Je mag dit venster nu sluiten
			</div>
			<?php
			}
			else {
			echo $row_button['message'] ?><br><br>
			<table width="100%" class="table-calendar">
			<?php
			if($adult_id <> "0"){
					?>
					<tr><td style="vertical-align: middle"><span class="fa fa-user-circle fa-fw"></span></td><td> Je doet deze betaling voor <select class="changebuyer" style="display: inline">
					<?php
					//get list of family members who are member of a club, starting with parent_id
					$kids_list = explode(",", KidsFromParentId($adult_id));
					foreach($kids_list AS $kid){
						//check if member from club
						$check = mysqli_query($con, "SELECT * FROM kids_" . $superuser . "s WHERE kid_id = $kid AND " . $superuser . "_id = $superuser_id");
						if(mysqli_num_rows($check) > 0){
								echo "<option value=\"" . $full_path . "/superuser_paybuttons_details.php?" . $superuser ."_id=" . $superuser_id . "&type=" . $type . "&button_id=" . $button_id . "&adult_id=0&kid_id=" . $kid . "&manual_id=0&token=" . generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, 0, $kid, 0) . "\">" . userValueKid($kid, "name") . " " . userValueKid($kid, "surname")  . "</option>";
						} 
					} 
					$parents_list = explode(",", JoinedParentsFromParentId($adult_id));
					foreach($parents_list AS $parent_id){
						$check = mysqli_query($con, "SELECT * FROM parents_" . $superuser . "s WHERE parent_id = $parent_id AND " . $superuser . "_id = $superuser_id");
						if(mysqli_num_rows($check) > 0){
							if($adult_id == $parent_id) { 
								$selected = "selected";	
							}
							else {
								$selected = "";
							}
								echo "<option value=\"" . $full_path . "/superuser_paybuttons_details.php?" . $superuser ."_id=" . $superuser_id . "&type=" . $type . "&button_id=" . $button_id . "&adult_id=" . $parent_id . "&kid_id=0&manual_id=0&token=" . generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, $parent_id, 0, 0) . "\" $selected>" . userValue($parent_id, "username") . "</option>";
						}
					} 
					?>
					</select></td></tr>
				<?php
				}
				elseif($kid_id <> "0") {
					?>
					<tr><td style="vertical-align: middle"><span class="fa fa-user-circle fa-fw"></span></td><td> Je doet deze betaling voor <select class="changebuyer" style="display: inline">
					<?php
					$parent_id = userValueKid($kid_id, "parent");
					//get list of family members who are member of a club, starting with parent_id
					$kids_list = explode(",", KidsFromParentId($parent_id));
					foreach($kids_list AS $kid){
						//check if member from club
						$check = mysqli_query($con, "SELECT * FROM kids_" . $superuser . "s WHERE kid_id = $kid AND " . $superuser . "_id = $superuser_id");
						if(mysqli_num_rows($check) > 0){
							if($kid == $kid_id) { 
								$selected = "selected";	
							}
							else {
								$selected = "";
							}
								echo "<option value=\"" . $full_path . "/superuser_paybuttons_details.php?" . $superuser ."_id=" . $superuser_id . "&type=" . $type . "&button_id=" . $button_id . "&adult_id=0&kid_id=" . $kid . "&manual_id=0&token=" . generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, 0, $kid, 0) . "\" $selected>" . userValueKid($kid, "name") . " " . userValueKid($kid, "surname")  . "</option>";
							
						} 
					} 
					$parents_list = explode(",", JoinedParentsFromParentId($parent_id));
					foreach($parents_list AS $parent_id){
						$check = mysqli_query($con, "SELECT * FROM parents_" . $superuser . "s WHERE parent_id = $parent_id AND " . $superuser . "_id = $superuser_id");
						if(mysqli_num_rows($check) > 0){
								echo "<option value=\"" . $full_path . "/superuser_paybuttons_details.php?" . $superuser ."_id=" . $superuser_id . "&type=" . $type . "&button_id=" . $button_id . "&adult_id=" . $parent_id . "&kid_id=0&manual_id=0&token=" . generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, $parent_id, 0, 0) . "\">" . userValue($parent_id, "username") . "</option>";
						}
					} 
					?>
					</select></td></tr>
					<?php
				}
				elseif($manual_id <> "0") {
					echo "<strong><span class=\"fa fa-user-circle fa-fw\"></span> Je doet deze betaling voor <u>" .  userValueManual($manual_id, $superuser, $superuser_id, "name") . " " . userValueManual($manual_id, $superuser, $superuser_id, "surname") . "</u>.";
				}
				else {
					echo "";
				}
			if($row_button['paydynamic'] <> "2"){ 
			?>
			<tr><td><span class="fa fa-shopping-cart fa-fw"></span></td><td> Kostprijs: <?php 
				if($row_button['paydynamic'] == "0"){ 
				echo $row_button['amount'] . "&euro;";
				}
				if($row_button['paydynamic'] == "1"){ 
					echo "variabel";
				}
				if($row_button['paydynamic'] == "3"){ 
					echo "selecteer zelf uit de lijst";
				}
			}
				?>
			</td></tr>
			<tr><td><span class="far fa-sticky-note fa-fw"></span></td><td> Referentie: <?php echo $row_button['invoice'] ?></td></tr>
			<tr><td><span class="fa fa-calendar fa-fw"></span></td><td> Einddatum: Gelieve de betaling te verrichten voor <u><?php echo $enddate_format ?></u></td></tr>
			<tr><td><span class="fa fa-info-circle fa-fw"></span></td><td> Disclaimer: Na klikken op de betaalknop word je verwezen naar een externe betaalpagina, waar je de betaling kan voldoen.</td></tr>
			</table>
			
			<?php
			//show order form if paydynamic = 2	
			if($row_button['paydynamic'] == 2){
				$sql_options = mysqli_query($con, "SELECT * FROM paybuttons_form WHERE " . $superuser . "_id = $superuser_id AND button_id = $button_id AND archive = 0");
				if(mysqli_num_rows($sql_options) > 0){
				?>
				<form id="OrderFormForm">
					<input type="hidden" name="button_id" value="<?php echo $button_id ?>">
					<input type="hidden" name="<?php echo $superuser ?>_id" value="<?php echo $superuser_id ?>">
					<input type="hidden" name="adult_id" value="<?php echo $adult_id ?>">
					<input type="hidden" name="kid_id" value="<?php echo $kid_id ?>">
					<input type="hidden" name="manual_id" value="<?php echo $manual_id ?>">
					<input type="hidden" name="order_id" value="<?php echo $prem_order_id ?>">
				<div class="shopping-cart">
				  <div class="column-labels">
				    <label class="product-details">Omschrijving</label>
				    <label class="product-price">Prijs</label>
				    <label class="product-quantity">Aantal</label>
				    <label class="product-line-price">Totaal</label>
				  </div>
				<?php
					while($row_options = mysqli_fetch_array($sql_options)){ ?>
				 
				  <input type="hidden" name="id[]" value="<?php echo $row_options['id'] ?>">
				  <input type="hidden" name="amount[]" value="<?php echo $row_options['amount'] ?>">
				  <div class="product">
				    <div class="product-details">
				      <div class="product-title"><?php echo $row_options['description'] ?></div>
				      
				    </div>
				    <div class="product-price"><?php echo $row_options['amount'] ?></div>
				    <div class="product-quantity">
				      <input type="number" name="quantity[]" value="0" min="0">
				    </div>
				    <div class="product-line-price">0</div>
				  </div>
				
				<?php } ?>
				  
				 
				  <div class="totals">
				    <div class="totals-item totals-item-total">
				      <label>Subtotaal</label>
				      <div class="totals-value" id="cart-subtotal">0</div>
				    </div>
				    <?php if($row_button['activatediscount'] == 1){ ?>
				    <div class="totals-item">
				      <label>Eventuele korting in € <?php echo helpPopup("whatsdiscountpaybutton", "black", "question-circle", $superuser, "") ?></label>
				      	 <div style="float: right; display: inline" class="amount_discount">
					     <input type="text" id="amount_discount" name="amount_discount" size="4" value="0" style="text-align:right;"> 
				      	 </div>
				    </div>
				    <?php } else { ?>
				    <input type="hidden" id="amount_discount" name="amount_discount" size="4" value="0" style="text-align:right;"> 
				    <?php } ?>
				    
				    <div class="totals-item totals-item-total">
				      <label>Totaal te betalen</label>
				      <div class="totals-value" id="cart-total">0</div>
				    </div>
				  </div>
				</form>

				</div>
				<?php
				}
			}
			} ?>
			</div>
			
			<?php 
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//PAYPAL
if(!isset($action)){ 
if($type == 0){ 
//show testpayment if 11513
if(($superuser == "club" && $superuser_id == "11513")){ ?>
<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" id="paypal<?php echo $button_id ?>">
<?php } else { ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="paypal<?php echo $button_id ?>">
<?php } 

if($row_button['paydynamic'] == 0){
	?>
<input type="hidden" name="amount" value="<?php echo $row_button['amount'] ?>">
	<?php
}
if($row_button['paydynamic'] == 1){
	?>
<br>
<div class="control-group">
    <label class="control-label">Geef het te betalen bedrag in:</label>
    <div class="input-group">
	  <span class="input-group-addon" id="basic-addon3">&euro;</span>
	  <input type="text" id="amount_dynamic" name="amount" class="form-control" aria-describedby="basic-addon3">
	</div>

</div>
	<?php
}
if($row_button['paydynamic'] == 2){
	?>
<input type="hidden" id="orderformtotal" name="amount">
	<?php
}
if($row_button['paydynamic'] == 3){
	?>
<label class="control-label">Prijs:</label>
<select name="amount" id="amountoptions" class="form-control">
	<option value="0">Selecteer:</option>
	<?php $sql_op = mysqli_query($con, "SELECT * FROM paybuttons_form WHERE " . $superuser . "_id = $superuser_id AND button_id = $button_id AND archive = 0 AND exclusive = 1");
		if(mysqli_num_rows($sql_op) > 0){
			while($row_op = mysqli_fetch_array($sql_op)){
				echo "<option value=\"$row_op[amount]\"> " . $row_op['description'] . " (". $row_op['amount'] . " euro)</option>";
			}
		}
		?>
</select>
<br>
	<?php
}
?>
<input type="hidden" id="paydynamic" name="paydynamic" value="<?php echo $row_button['paydynamic'] ?>">
<br>
<div class="control-group">
    <label class="control-label"><input type="hidden" name="on1" value="Opmerkingen" maxlength="200">Geef eventuele opmerkingen door aan <?php echo userValueSuper($superuser_id,$superuser,"username") ?>:</label>
    <div class="controls">
	  <input type="text" name="os1" class="form-control">
    </div>
</div>
<br>

<!-- Identify your business so that you can collect the payments. -->
<input type="hidden" name="business" value="<?php echo userValueSuper($superuser_id,$superuser,"paypal_id") ?>">

<!-- Specify a Buy Now button. -->
<input type="hidden" name="cmd" value="_xclick">

<!-- Specify details about the item that buyers will purchase. -->
<input type="hidden" name="item_name" value="<?php echo $row_button['title'] ?>">


<input type="hidden" name="notify_url" value="<?php echo $full_path ?>/plugins/includes/PayPal/paypal_notify_url.php">

<!-- In notify_url this is necessary to update database -->
<input type="hidden" name="item_number" value="<?php echo $button_id ?>">
<input type="hidden" name="invoice" value="<?php echo $row_button['invoice'] ?> - <?php echo $for ?>">
<input type="hidden" name="custom" value="<?php echo $superuser_id ?>&<?php echo $superuser ?>&<?php echo $adult_id ?>&<?php echo $kid_id ?>&<?php echo $manual_id ?>&<?php echo $prem_order_id ?>">

<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="charset" value="UTF-8">

<input type="hidden" name="cancel_return" value="<?php echo $full_path ?>/superuser_paybuttons_details.php?<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&type=0&button_id=<?php echo $button_id ?>&adult_id=<?php echo $adult_id ?>&kid_id=<?php echo $kid_id ?>&manual_id=<?php echo $manual_id ?>&order_id=<?php echo $prem_order_id ?>&token=<?php echo generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, $adult_id, $kid_id, $manual_id); ?>">
<input type="hidden" name="return" value="<?php echo $full_path ?>/superuser_paybuttons_details.php?<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&type=0&button_id=<?php echo $button_id ?>&adult_id=<?php echo $adult_id ?>&kid_id=<?php echo $kid_id ?>&manual_id=<?php echo $manual_id ?>&action=success&token=<?php echo generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, $adult_id, $kid_id, $manual_id); ?>">

<?php if($test == "1"){ ?>
<button class="btn btn-success btn-block" disabled><span class="fab fa-paypal"></span> Betaal</button>
<div style="color:red; font-size: smaller" align="center">
  deze betaalknop is inactief (dit is een testpagina).
</div>
<?php } else { ?> 
<button type="submit" class="btn btn-success btn-block TriggerForm" <?php if(($row_button['paydynamic'] == 2) || ($row_button['paydynamic'] == 3)) { echo "disabled"; } ?>><span class="fab fa-paypal"></span> Betaal</button>
<?php } 
if($payed == "1"){ ?>
<br>
<div class="alert alert-success" style="font-size: smaller" align="center">
  <span class="fa fa-info-circle fa-fw"></span> Opmerking: Je hebt al betaald voor <?php echo $for ?> op 
  <?php echo formatDate($time_payed); ?>
</div>
  <?php }
if(($payed == "0") && ($test == "0")){ ?>
<div style="color:black; font-size: smaller" align="center">
  Opmerking: Je gaat betalen voor <?php echo $for ?>
</div>
  <?php } ?>


</form>
<?php }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
//MOLLIE
if($type == 1){
 ?>
 <form action="<?php echo $full_path ?>/plugins/mollie2/examples/payments/create-payment-oauth.php" method="post" id="<?php echo $button_id ?>">

<?php
if($row_button['paydynamic'] == 0){
	?>
<input type="hidden" name="amount" value="<?php echo $row_button['amount'] ?>">
	<?php
}
if($row_button['paydynamic'] == 1){
	?>
<br>
<div class="control-group">
    <label class="control-label">Geef het te betalen bedrag in:</label>    
    <div class="input-group">
	  <span class="input-group-addon" id="basic-addon3">&euro;</span>
	  <input type="text" id="amount_dynamic" name="amount" class="form-control" aria-describedby="basic-addon3">
	</div>
</div>
	<?php
}
if($row_button['paydynamic'] == 2){
	?>
<input type="hidden" id="orderformtotal" name="amount">
	<?php
}
if($row_button['paydynamic'] == 3){
	?>
<label class="control-label">Prijs:</label>
<select name="amount" id="amountoptions" class="form-control">
	<option value="0">Selecteer:</option>
	<?php $sql_op = mysqli_query($con, "SELECT * FROM paybuttons_form WHERE " . $superuser . "_id = $superuser_id AND button_id = $button_id AND archive = 0 AND exclusive = 1");
		if(mysqli_num_rows($sql_op) > 0){
			while($row_op = mysqli_fetch_array($sql_op)){
				echo "<option value=\"$row_op[amount]\"> " . $row_op['description'] . " (". $row_op['amount'] . " euro)</option>";
			}
		}
		?>
</select>
<br>
	<?php
}
?>
<br>
<input type="hidden" id="paydynamic" name="paydynamic" value="<?php echo $row_button['paydynamic'] ?>">
<div class="control-group">
    <label class="control-label">Geef eventuele opmerkingen door aan <?php echo userValueSuper($superuser_id,$superuser,"username") ?>:</label>
    <div class="controls">
	  <input type="text" name="comments" class="form-control">
    </div>
    
</div>
<br>
<input type="hidden" name="button_id" value="<?php echo $button_id ?>">
    <input type="hidden" name="<?php echo $superuser ?>_id" value="<?php echo $superuser_id ?>">
    <input type="hidden" name="adult_id" value="<?php echo $adult_id ?>">
    <input type="hidden" name="kid_id" value="<?php echo $kid_id ?>">
    <input type="hidden" name="manual_id" value="<?php echo $manual_id ?>">
    <input type="hidden" name="order_id" value="<?php echo $prem_order_id ?>">
    <input type="hidden" name="invoice" value="<?php echo $row_button['invoice'] ?> - <?php echo $for ?>">
    
<?php if($test == "1"){ ?>
<button class="btn btn-success btn-block" disabled><span class="fa fa-credit-card fa-fw"></span> Betaal</button>
<div style="color:red; font-size: smaller" align="center">
    deze betaalknop is inactief (dit is een testpagina).
</div>
<?php } else { ?> 
<button type="submit" class="btn btn-success btn-block TriggerForm" <?php if(($row_button['paydynamic'] == 2) || ($row_button['paydynamic'] == 3)) { echo "disabled"; } ?>><span class="fa fa-credit-card fa-fw"></span>  Betaal</button>
<?php }
if($payed == "1"){ ?>
<br>
<div class="alert alert-success" style="font-size: smaller" align="center">
  <span class="fa fa-info-circle fa-fw"></span> Opmerking: Je hebt al betaald voor <?php echo $for ?> op 
  <?php echo formatDate($time_payed); ?>
</div>
<?php }
if(($payed == "0") && ($test == "0")){ ?>
<div style="color:black; font-size: smaller" align="center">
Opmerking: Je gaat betalen voor <?php echo $for ?>
</div>
<?php } ?>
</form>
	<?php
	}
	}
?>

<br>
</div>
<br>
<div align="middle">
<a href="<?php echo $home_path ?>" target="_blank" style="color: black"><img src="<?php echo $logo_path ?>" class="avatar-x-small" style="display: inline"><font style="font-size: 12px">&nbsp;&nbsp;Powered by </font><font class="font" style="font-size: 12px"><?php echo $appname ?></font></a>	
</div>
		
<?php
	}
	else {
		?>
		<ul class="media-list alert alert-danger" style="padding-left: 10px">
	  <li class="media">
	    <div class="media-left"><span class="fa fa-exclamation-triangle fa-lg bg-icons bg-color-3 icon-circle"></span></div>
	    <div class="media-body">
	      Deze betaalknop is niet meer geldig
	      </div>
	</div>
	  </li>
</ul>
		<?php
	}
	?>

<script type="text/javascript">
	
	$(document).ready(function(){
    
	   function isNumber(evt) {
	   var theEvent = evt || window.event;
	   var key = theEvent.keyCode || theEvent.which;            
	   var keyCode = key;
	   key = String.fromCharCode(key);          
	   if (key.length == 0) return;
	   var regex = /^[0-9.,\b]+$/;            
	   if(keyCode == 188 || keyCode == 190){
	      return;
	   }else{
	      if (!regex.test(key)) {
	         theEvent.returnValue = false;                
	         if (theEvent.preventDefault) theEvent.preventDefault();
	      }
	    }    
	 }
    
    //change comma for dot
	$('#amount_dynamic').on('keyup', function (e) {
	  $('#amount_dynamic').val($(this).val().replace(/,/g, '.'));
	  var val = $(this).val();
	    if(isNaN(val)){
	         val = val.replace(/[^0-9\.]/g,'');
	         if(val.split('.').length>2) 
	             val =val.replace(/\.+$/,"");
	    }
	    $(this).val(val); 
		});

    //only allow numbers in number field
    $('#amount_dynamic').keypress(isNumber);
    
    /*
     $("#amount_dynamic").on("keypress keyup blur",function (event) {
	 $(this).val($(this).val().replace(/,/g, '.'));
     $(this).val($(this).val().replace(/[^0-9\.]/g,''));
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
    */

    
    $('#amountoptions').on('change', function () {
    $('.TriggerForm').prop( "disabled", false );
    });
    
      // bind change event to select
      $('.changebuyer').on('change', function () {
          var url = $(this).val(); // get selected value
          if (url) { // require a URL
              window.location = url; // redirect
          }
          return false;
      });

	
    
    //save button
	$('.TriggerForm').on('click', function(){
		
	var paydynamic = $("#paydynamic").val();

	if(paydynamic === "2"){
	
	var submiturl = "superuser_paybuttons_form_action.php?action=InsertOrderForm"; // the script where you handle the form input.
	
	$.ajax({
           type: "POST",
           url: submiturl,
           data: $("#OrderFormForm").serialize(), // serializes the form's elements.
           success: function(result)
           {
		   $('form<?php $button_id ?>').submit();  
           },
           error: function(error){
	           alert("probleem");
	           console.log(error)
	           },
         });	
	
	return false;
	}
	
	if(paydynamic === "3"){
	if ($('#amountoptions').val() == "0") 
	{
        NotifError("fillallfields"); //Show error
		return false;
    }
	}
	if(paydynamic === "1"){
    if ($('#amount_dynamic').val().length  >   0) 
    {
    }
    else {
        NotifError("fillallfields"); //Show error
		return false;
    }

	var price = parseInt($("#amount_dynamic").val());
    if(price >= 1){
			 //Show error
		    }
		    else {
		        NotifError("minimumvalue"); //Show error
				return false;
		    }
    var x = Number($("#amount_dynamic").val()).toFixed(2);
	  $("#amount_dynamic").val(x).text(x);
	}
	
	$('form<?php $button_id ?>').submit(function() {
		// submit more than once return false
		$(this).submit(function() {
			return false;
		});
		// submit once return true
		return true;
	});
		    
	});
     
	var images = new Array ('img/animat-shopping-cart-color.gif', 'img/animat-credit-card-color.gif');
		var index = 1;
		 
		function rotateImage()
		{
		  $('#myImage').fadeOut('fast', function()
		  {
		    $(this).attr('src', images[index]);
		 
		    $(this).fadeIn('fast', function()
		    {
		      if (index == images.length-1)
		      {
		        index = 0;
		      }
		      else
		      {
		        index++;
		      }
		    });
		  });
		}
 
	$(document).ready(function()
	{
	  setInterval (rotateImage, 2500);
	});
	
	/* Set rates + misc */
var taxRate = 0;
var shippingRate = 0; 
var fadeTime = 200;
var discount = 0;
 
 
/* Assign actions */
$('.product-quantity input').change( function() {
  updateQuantity(this);
});

$(".amount_discount input").on('keyup', function(){
  recalculateCart();
  $('#amount_discount').val($(this).val().replace(/,/g, '.'));
});
$('#amount_discount').keypress(isNumber);
 

 
/* Recalculate cart */
function recalculateCart()
{
  var subtotal = 0;
  var discount = $('#amount_discount').val();
   
  /* Sum up row totals */
  $('.product').each(function () {
    subtotal += parseFloat($(this).children('.product-line-price').text());
  });
   
  /* Calculate totals */
  var tax = subtotal * taxRate;
  var shipping = (subtotal > 0 ? shippingRate : 0);
  var total = (subtotal + tax + shipping) - discount;
  if(total < 0){
	  total = 0;
  }
   
  /* Update totals display */
  $('.totals-value').fadeOut(fadeTime, function() {
    $('#cart-subtotal').html(subtotal.toFixed(2));
    $('#cart-tax').html(tax.toFixed(2));
    $('#cart-shipping').html(shipping.toFixed(2));
    $('#cart-total').html(total.toFixed(2));
    $('#orderformtotal').val(total.toFixed(2));
    if(total == 0){
      $('.TriggerForm').prop("disabled", true);
    }else{
	   $('.TriggerForm').prop( "disabled", false );
    }
    $('.totals-value').fadeIn(fadeTime);
  });
}
 
 
/* Update quantity */
function updateQuantity(quantityInput)
{
  /* Calculate line price */
  var productRow = $(quantityInput).parent().parent();
  var price = parseFloat(productRow.children('.product-price').text());
  var quantity = $(quantityInput).val();
  var linePrice = price * quantity;
   
  /* Update line price display and recalc cart totals */
  productRow.children('.product-line-price').each(function () {
    $(this).fadeOut(fadeTime, function() {
      $(this).text(linePrice.toFixed(2));
      recalculateCart();
      $(this).fadeIn(fadeTime);
    });
  });  
}



	});
</script>
<?php include("footer.php") ?>
</body>
</html>		
		
