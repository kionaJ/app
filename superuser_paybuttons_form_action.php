<?php
include('functions.php');
include('login/includes/api.php');
if(isset($_GET['action'])) $action=$_GET['action'];
if(isset($_POST['action'])) $action=$_POST['action'];

//////////////////////////////////////////////////////////////////
//RECORD ORDER
//////////////////////////////////////////////////////////////////

if(isset ($action) && $action == "InsertOrderForm"){	
$i = 0;

$order_id = mres($_POST['order_id']);
//check first if entries already exist (double click form)
$check = mysqli_query($con, "SELECT * FROM paybuttons_form_answers WHERE order_id = '$order_id'");
if(mysqli_num_rows($check) == 0){ 

$button_id=mres($_POST['button_id']);
	
	$club_id = 0;
	$kid_id = 0;
	$adult_id = 0;
	$manual_id = 0;
	
	if(isset($_POST['club_id']) && is_numeric($_POST['club_id']) && !empty($_POST['club_id']) && ($_POST['club_id']) <> 0){
	$superuser = "club"; 
	$superuser_id=mres($_POST['club_id']);
	}
	if(isset($_POST['kid_id']) && is_numeric($_POST['kid_id']) && !empty($_POST['kid_id']) && ($_POST['kid_id']) <> 0){ 
		$kid_id=mres($_POST['kid_id']);
	}
	if(isset($_POST['adult_id']) && is_numeric($_POST['adult_id']) && !empty($_POST['adult_id']) && ($_POST['adult_id']) <> 0){ 
		$adult_id=mres($_POST['adult_id']);
	}
	if(isset($_POST['manual_id']) && is_numeric($_POST['manual_id']) && !empty($_POST['manual_id']) && ($_POST['manual_id']) <> 0){ 
		$manual_id=mres($_POST['manual_id']);
	}

	$date = date('Y-m-d');
		
	if(isset($_POST['amount_discount'])){
	$amount_discount=mres($_POST['amount_discount']);	
	}
	else {
		$amount_discount = 0;
	}

foreach ($_POST['id'] as $val) {
	
    $option_id = mres($_POST['id'][$i]);
    $quantity = mres($_POST['quantity'][$i]);
    $amount = mres($_POST['amount'][$i]);

    $insert = mysqli_query($con, "INSERT INTO paybuttons_form_answers (date, order_id, button_id, " . $superuser . "_id, kid_id, adult_id, manual_id, option_id, quantity, amount, discount) VALUES ('$date', '$order_id', $button_id, $superuser_id, $kid_id, $adult_id, $manual_id, $option_id, $quantity, '$amount', '$amount_discount')");
    $i++;
    
	    if(!$insert){
		error_log("Error insertorderform: " . mysqli_error($con), 0);
		}
	}
}
}

?>
