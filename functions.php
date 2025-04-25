<?php //function prevent SQL injections
function mres($var){
	global $con;
	if($var <> NULL){
    return mysqli_real_escape_string($con,trim($var));
	}
}

// Added KFJ.php
function getGenderLabel($kid_id) {
	$sex = userValueKid($kid_id, "sex");
    switch ($sex) {
        case 0:
            return "Jongen";
        case 1:
            return "Meisje";
        case 2:
            return "Neutraal";
        default:
            return "Onbekend";
    }
}

// Added KFJ paybuttonlimit
function user_has_reached_shopitem_limit($user_id, $club_id) {
    // Haal het abonnementsniveau op voor de gebruiker
    $user_level = get_user_level($user_id);

    // Stel de limieten per niveau in
    $level_limits = array(
        1 => 3, // Max 3 items voor level 1
        2 => 5,
        3 => 10
    );

    // Controleer of er een limiet is ingesteld voor dit niveau

    if (isset($level_limits[$user_level])) {
        global $wpdb;
        $paybutton_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM paybuttons WHERE user_id = %d AND club_id = %d",
            $user_id, $club_id
        ));

        // Vergelijk het aantal met de limiet
        if ($paybutton_count >= $level_limits[$user_level]) {
            return true; // Limiet bereikt
        }
    }

    return false; // Limiet niet bereikt of niet van toepassing
}
// End Added KFJ

function isExistingCustomer($registrationDate, $cutoffDate) {
    return strtotime($registrationDate) < strtotime($cutoffDate);
}

function calculatePremiumAmount($trial, $level, $isExistingCustomer, $level_1_price, $newLevel1Price, $level_2_price, $newLevel2Price, $superuser_id, $superuser, $today) {
    $amount = "GRATIS"; // Default value for trial

    if ($trial == "0") {
        if ($level == "1") {
            $amount = ($isExistingCustomer) ? $level_1_price : $newLevel1Price;
        } elseif ($level == "2") {
            $amount = ($isExistingCustomer) ? $level_2_price : $newLevel2Price;
        }

        if (checkPremiumLevel($superuser_id, $superuser, "") == "level 1" && $level == "2") {
            // Upgrade to level 2
            $amount = $newLevel2Price;
        }

        $new_enddate = date('d-m-Y', strtotime('+1 year', strtotime($today)));
    }

    return $amount;
}


function generateInvoiceNumber($currentYear) {
    // Haal het laatste gegenereerde factuurnummer op uit de database
    $last_invoice_number = getLastInvoiceNumber();
    
    if ($last_invoice_number === false) {
        // Er zijn geen eerder gegenereerde factuurnummers gevonden
        // Begin met de nieuwe nummering vanaf het huidige jaar (bijv. 2024000 voor 2024)
        return intval($currentYear . "000");
    } else {
        // Haal het jaar op van het laatste gegenereerde factuurnummer
        $last_year = substr($last_invoice_number, 0, 4);

        if ($currentYear == $last_year) {
            // Het huidige jaar is hetzelfde als het jaar van het laatste factuurnummer
            // Voeg 1 toe aan het laatste factuurnummer om het nieuwe factuurnummer te genereren
            return $last_invoice_number + 1;
        } else {
            // Het huidige jaar is anders dan het jaar van het laatste factuurnummer
            // Begin met de nieuwe nummering vanaf het huidige jaar (bijv. 2025000 voor 2025)
            return intval($currentYear . "000");
        }
    }
}

// End Added KFJ.php


//safety layer post kid_id
function kres($kid_id){
	global $con;
	$kid_id = mres($kid_id);
	$parent_id = userValue(null, "id");
	$stmt = $con->prepare("SELECT id FROM kids
		WHERE id= ? AND parent = ? 
		OR 
		id= ? AND 
		parent IN(SELECT master_id FROM users_share WHERE slave_id = ? AND confirmed = 1)");
	
	$stmt->bind_param("iiii", $kid_id, $parent_id, $kid_id, $parent_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		return $kid_id;
	}
	else {
		die(); 
	}
	$stmt->close();	
}

//safety layer post get_user_id
function pres($get_user_id){
	global $con;
	$logged_in_id = userValue(null, "id");
	
	if($logged_in_id == $get_user_id){
		return $get_user_id;
	}
	else { 
	//check if user id is master or slave
	$stmt = $con->prepare("SELECT * FROM users_share WHERE master_id = ? AND slave_id = ? AND confirmed = '1' OR master_id = ? AND slave_id = ? AND confirmed = '1'");
	$stmt->bind_param("iiii", $logged_in_id, $get_user_id, $get_user_id, $logged_in_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		return $get_user_id;
	}
	else {
		die(); 
	}
	}
	$stmt->close();		
}

//safety layer post superuser
function sres($subcategory_id, $superuser_id, $superuser){
	global $con;
	$subcategory_id = mres($subcategory_id);
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	$stmt = $con->prepare("SELECT * FROM " . $superuser . "s_categories 
		WHERE id= ? AND " . $superuser . "_id = ?");
	$stmt->bind_param("ii", $subcategory_id, $superuser_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){ 
	return $subcategory_id;
	}
	else {
		die();
	}
	}
	else {
		die();
	}	
	$stmt->close();
}

function base64_url_encode($input) {
	 return strtr(base64_encode($input), '+/=', '-._,-');
}

function base64_url_decode($input) {
 	return base64_decode(strtr($input, '-._,-', '+/='));
}

function userValueKid($kid_id, $value){
	global $con;
	global $pass;
	if(($value == "name") || ($value == "surname") || ($value == "postal") || ($value == "avatar")){
		$stmt = $con->prepare("SELECT CONVERT(AES_DECRYPT($value, SHA1('$pass')) USING utf8) AS $value FROM kids WHERE id = ?");
	}
	else {
		$stmt = $con->prepare("SELECT * FROM kids WHERE id = ?");
	}
	$stmt->bind_param("i", $kid_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){ 
		$row = $result->fetch_assoc();
		if(!empty($row[$value])) {
			return $row[$value];
		} else {
			return false;
		}		
	}
	else {
		return false;
	}
		
	$stmt->close();	
}

function userValueManual($manual_id, $superuser, $superuser_id, $value){
	global $con;
	global $pass;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	if(($value == "name") || ($value == "surname") || ($value == "avatar") || ($value == "email") || ($value == "Telefoonnummer")){
		$stmt = $con->prepare("SELECT AES_DECRYPT($value, SHA1('$pass')) AS $value FROM kids_" . $superuser . "s_manual WHERE id = ? AND " . $superuser . "_id = ?");
	}
	else {
		$stmt = $con->prepare("SELECT * FROM kids_" . $superuser . "s_manual WHERE id = ? AND " . $superuser . "_id = ?");
	}
	$stmt->bind_param("ii", $manual_id, $superuser_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){ 
		$row = $result->fetch_assoc();
		if(!empty($row[$value])) {
			return $row[$value];
		} else {
			return false;
		}	
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
	$stmt->close();
}


//userValue from superuser
function userValueSuper($superuser_id,$superuser,$value) { 
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	$stmt = $con->prepare("SELECT * FROM users WHERE " . $superuser . "_id = ? AND " . $superuser . "_id <> 0");
	$stmt->bind_param("i", $superuser_id);
	$stmt->execute();
	$result = $stmt->get_result();
		if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		if(!empty($row[$value])) {
			return $row[$value];
		} else {
			return false;
		}
		$stmt->close();
	}
	else {
		//username not found
		if($value == "username"){
		if($superuser == "club"){ 
		//club is no member in app
		$stmt = $con->prepare("SELECT club_name FROM clubs WHERE id = ?");
		$stmt->bind_param("i", $superuser_id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0){
		$row_nomember = $result->fetch_assoc();
		return $row_nomember['club_name'];
		}
		else {
			return false;
		}
		$stmt->close();
		}
		}
		else { 
		return false;
		}
	}
	}
	else {
		return false;
	}
}

function userValueSubcategory($subcategory_id, $superuser, $value) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	$stmt = $con->prepare("SELECT * FROM " . $superuser . "s_categories WHERE id = ?");
	$stmt->bind_param("i", $subcategory_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
	$row = $result->fetch_assoc();
	if(!empty($row[$value])) {
		return $row[$value];
	} else {
		return false;
	}
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
	$stmt->close();
}

//function online status user
function isOnline($user_id, $is_superuser = 0, $method = "retreive", $output = "bullet"){
global $con;
if($method == "set"){
	$sql_online = mysqli_query($con, "SELECT * FROM online_status WHERE user_id IN ($user_id)");
	if(mysqli_num_rows($sql_online) > 0){
		$row_online = mysqli_fetch_array($sql_online);
		if(strtotime($row_online['timestamp']) < strtotime("-3 minutes")){
		mysqli_query($con, "UPDATE online_status SET timestamp = CURRENT_TIMESTAMP() WHERE user_id = $user_id");
		}
	}
	else {
		mysqli_query($con, "INSERT INTO online_status (user_id, is_superuser) VALUES ($user_id, $is_superuser)");
	}
}	
if($method == "retreive"){
	$sql_online = mysqli_query($con, "SELECT * FROM online_status WHERE user_id IN($user_id) ORDER BY timestamp DESC LIMIT 1");
	if(mysqli_num_rows($sql_online) > 0){
		$row_online = mysqli_fetch_array($sql_online);
		if(strtotime($row_online['timestamp']) < strtotime("-5 minutes") && strtotime($row_online['timestamp']) > strtotime("-1 day")){
			if($output == "text"){
			return "afwezig";
			}
			if($output == "bullet"){
				return "<span class=\"isAbsent online-dot\"><span class=\"fa fa-circle\" style=\"color: orange;\"></span></span>";
			}
		}
		if(strtotime($row_online['timestamp']) < strtotime("-1 day")){
			if($output == "text"){
			return "offline";
			}
			if($output == "bullet"){
				return "<span class=\"isOffline online-dot\"><span class=\"fa fa-circle\" style=\"color: red;\"></span></span>";
			}
		}
		if(strtotime($row_online['timestamp']) >= strtotime("-5 minutes")){
			//error_log($user_id);
			if($output == "text"){
				return "online";
				}
				if($output == "bullet"){
					return "<span class=\"isOnline online-dot\"><span class=\"fa fa-circle\" style=\"color: green;\"></span></span>";
				}
		}
	}
	else {
		if($output == "text"){
			return "offline";
			}
			if($output == "bullet"){
				return "<span class=\"isOffline online-dot\"><span class=\"fa fa-circle\" style=\"color: red;\"></span></span>";
			}
	}
}
}

//function check if user is reported
function isReportedProfile($kid_id, $adult_id, $friend_id, $superuser, $superuser_id){
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$stmt = $con->prepare("SELECT * FROM kids_report WHERE kid_id= ? AND parent_id= ? AND friend_id= ? AND " . $superuser . "_id = ?");
	$stmt->bind_param("iiii", $kid_id, $adult_id, $friend_id, $superuser_id);
	}
	else {
	$stmt = $con->prepare("SELECT * FROM kids_report WHERE kid_id= ? AND parent_id= ? AND friend_id= ?");
	$stmt->bind_param("iii", $kid_id, $adult_id, $friend_id);
	}
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
	   return true;
	}
	else {
	   return false;
	}
	$stmt->close();
}

//Calculate Age Function
function birthday ($birthday) {
    list($year,$month,$day) = explode("-",$birthday);
    $year_diff  = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff   = date("d") - $day;
    if ($month_diff < 0) $year_diff--;
    elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
    return $year_diff;
    }


//display legit state of child
function showLegitFromKidId($kid_id) {
global $con;
$stmt = $con->prepare("SELECT legit, fb_ver FROM kids WHERE id = ?");
$stmt->bind_param("i", $kid_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0){
$row = $result->fetch_assoc();
 
	  if(isset($row['legit']) && $row['legit'] == "0"){
     	  $legit = "&nbsp;<span class=\"fa fa-check-circle\" style=\"color:red;\" aria-hidden=\"true\"></span>";
 	  }
 	  if(isset($row['legit']) && $row['legit'] == "1"){
     	  $legit = "&nbsp;<span class=\"falfa-check-circle\" style=\"color:orange;\" aria-hidden=\"true\"></span>";
 	  }
 	  if(isset($row['legit']) && $row['legit'] == "2"){
     	  $legit = "&nbsp;<span class=\"fa fa-check-circle\" style=\"color:green;\" aria-hidden=\"true\"></span>";
 	  }
	  /*
 	  if(isset($row['fb_ver']) && $row['fb_ver'] == "1"){
       	$legit .= "&nbsp;<span class=\"fal fa-star\" style=\"color:orange;\" aria-hidden=\"true\"></span>";
      }
	  */
      return $legit;
}
else {
	return false;
}
$stmt->close();
}

//display legit state of child
function showGDPRFace($kid_id, $adult_id, $manual_id, $superuser_id, $superuser) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	
	if($kid_id <> "0"){
		$sql = mysqli_query($con,"SELECT * FROM kids_" . $superuser . "s WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id ORDER BY ApproveFace_timestamp DESC");	
		}
	
	if($adult_id <> "0"){
		$sql = mysqli_query($con,"SELECT * FROM parents_" . $superuser . "s WHERE parent_id = $adult_id AND " . $superuser . "_id = $superuser_id ORDER BY ApproveFace_timestamp DESC");	
	}
	
	if($manual_id <> "0"){
		$sql = mysqli_query($con,"SELECT * FROM kids_" . $superuser . "s_manual WHERE id = $manual_id AND " . $superuser . "_id = $superuser_id ORDER BY ApproveFace_timestamp DESC");		
	}
	$row = mysqli_fetch_array($sql);
	
	  	if(($row['ApproveFace'] == 0) && ($row['ApproveFace_timestamp'] == 0)){
     	  	return "&nbsp;<span class=\"fa fa-camera\" style=\"color:orange;\" aria-hidden=\"true\"></span>";
 	  	}
 	  	if(($row['ApproveFace'] == 0) && ($row['ApproveFace_timestamp'] <> 0)){
     	  	return "&nbsp;<span class=\"fa fa-camera\" style=\"color:red;\" aria-hidden=\"true\"></span>";
 	  	}
 	  	if(($row['ApproveFace'] == 1) && ($row['ApproveFace_timestamp'] <> 0)){
     	  	return "&nbsp;<span class=\"fa fa-camera\" style=\"color:green;\" aria-hidden=\"true\"></span>";
 	  	}
	}
}

//display province
function showProvinceFromId($province_id) {
	  if(isset($province_id) && $province_id == "1"){
     	  return "Limburg";
      }
      if(isset($province_id) && $province_id == "2"){
     	  return "Oost-Vlaanderen";
      }
      if(isset($province_id) && $province_id == "3"){
     	  return "West-Vlaanderen";
      }
      if(isset($province_id) && $province_id == "4"){
     	  return "Brabant";
      }
      if(isset($province_id) && $province_id == "5"){
     	  return "Antwerpen";
      }
      if(isset($province_id) && $province_id == "6"){
     	  return "Brussels Hoofdstedelijk Gewest";
      }
      if(isset($province_id) && $province_id == "7"){
     	  return "Henegouwen";
      }
      if(isset($province_id) && $province_id == "8"){
     	  return "Luik";
      }
      if(isset($province_id) && $province_id == "9"){
     	  return "Luxemburg";
      }
      if(isset($province_id) && $province_id == "10"){
     	  return "Namen";
      }
      if(isset($province_id) && $province_id == "11"){
     	  return "Waals-Brabant";
      }
}


//display category club
function showCategory($row) {
	  if(isset($row['category']) && $row['category'] == "1"){
     	  return "Sportvereniging";
      }
      if(isset($row['category']) && $row['category'] == "2"){
     	  return "(Jeugd)vereniging";
      }
      if(isset($row['category']) && $row['category'] == "3"){
     	  return "Muziek- of kunstvereniging";
      }
      if(isset($row['category']) && $row['category'] == "4"){
     	  return "Speelpleinwerking";
      }
}


//display category plus
function showCategoryPlusFromCategoryID($category_id, $icon = false) {
	  if(isset($category_id) && $category_id == "1"){
		  if($icon == true){
		  return "<span class='fal fa-baby-carriage pull-right'></span> Baby & Kind";	  
		  }
		  else {
     	  return "Baby & Kind";
     	  }
      }
      if(isset($category_id) && $category_id == "2"){
	      if($icon == true){
     	  return "<span class='fal fa-desktop pull-right'></span> Beeld & Geluid";
     	  }
     	  else { 
	      return "Beeld & Geluid";
	      }
      }
      if(isset($category_id) && $category_id == "3"){
	      if($icon == true){
     	  return "<span class='fal fa-book pull-right'></span> Boeken & Magazines";
     	  }
     	  else {
	      return "Boeken & Magazines";  
     	  }
      }
      if(isset($category_id) && $category_id == "4"){
	      if($icon == true){
     	  return "<span class='fal fa-gift pull-right'></span> Cadeaus & Gadgets";
     	  }
     	  else {
	      return "Cadeaus & Gadgets";	  
     	  }
      }
      if(isset($category_id) && $category_id == "5"){
	      if($icon == true){
     	  return "<span class='fal fa-laptop pull-right'></span> Computer";
     	  }
     	  else {
	      return "Computer";  
     	  }
      }
      if(isset($category_id) && $category_id == "6"){
	      if($icon == true){
     	  return "<span class='fal fa-calendar-alt pull-right'></span> Dagaanbiedingen";
     	  }
     	  else {
	      return "Dagaanbiedingen";  
     	  }
      }
      if(isset($category_id) && $category_id == "7"){
	      if($icon == true){
     	  return "<span class='fal fa-map-marker-alt pull-right'></span> Dagje uit";
     	  }
     	  else {
	      return "Dagje uit";  
     	  }
      }
      if(isset($category_id) && $category_id == "8"){
	      if($icon == true){
     	  return "<span class='fal fa-credit-card pull-right'></span> Financieel";
     	  }
     	  else {
	      return "Financieel"; 
     	  }
      }
      if(isset($category_id) && $category_id == "9"){
	      if($icon == true){
     	  return "<span class='fal fa-camera pull-right'></span> Foto & Camera";
     	  }
     	  else {
	      return "Foto & Camera";  
     	  }
      }
      if(isset($category_id) && $category_id == "10"){
	      if($icon == true){
     	  return "<span class='fal fa-music pull-right'></span> Games, films & muziek";
     	  }
     	  else {
	      return "Games, films & muziek"; 
     	  }
      }
      if(isset($category_id) && $category_id == "11"){
	      if($icon == true){
     	  return "<span class='fal fa-home pull-right'></span> Huis & Tuin";
     	  }
     	  else {
	      return "Huis & Tuin";  
     	  }
      }
      if(isset($category_id) && $category_id == "12"){
	      if($icon == true){
     	  return "<span class='fal fa-blender pull-right'></span> Huishoudapparatuur";
     	  }
     	  else {
	      return "Huishoudapparatuur";
     	  }
      }
      if(isset($category_id) && $category_id == "13"){
	      if($icon == true){
     	  return "<span class='fal fa-tshirt pull-right'></span> Mode";
     	  }
     	  else {
	      return "Mode";  
     	  }
      }
      if(isset($category_id) && $category_id == "14"){
	      if($icon == true){
     	  return "<span class='fal fa-apple-alt pull-right'></span> Mooi & Gezond";
     	  }
     	  else {
	      return "Mooi & Gezond";  
     	  }
      }
      if(isset($category_id) && $category_id == "15"){
	      if($icon == true){
     	  return "<span class='fal fa-futbol pull-right'></span> Sport & Vrije tijd";
     	  }
     	  else {
	      return "Sport & Vrije tijd";  
     	  }
      }
      if(isset($category_id) && $category_id == "16"){
	      if($icon == true){
     	  return "<span class='fal fa-mobile-alt pull-right'></span> Telefoon & Internet";
     	  }
     	  else {
	      return "Telefoon & Internet";  
     	  }
      }
      if(isset($category_id) && $category_id == "17"){
	      if($icon == true){
     	  return "<span class='fal fa-plane pull-right'></span> Vakantie & Reizen";
     	  }
     	  else {
	      return "Vakantie & Reizen";  
     	  }
      }
      if(isset($category_id) && $category_id == "18"){
	      if($icon == true){
     	  return "<span class='fal fa-suitcase pull-right'></span> Zakelijk";
     	  }
     	  else {
	      return "Zakelijk";  
     	  }
      }
}


//display affiliate
function showAffiliate($row) {
global $con;
	$sql = mysqli_query($con,"SELECT name FROM affiliate WHERE id = $row[affiliate]");
	if(mysqli_num_rows($sql) > 0){
		$row_aff = mysqli_fetch_array($sql);
		return $row_aff['name'];
		}
		else {
			return "nog geen ingegeven";
		}
		
}

//display plugin Help
function showHelpPlugin($row) {
	  if(isset($row['plugin']) && $row['plugin'] == "1"){
     	 return "CONNECT";
      }
      if(isset($row['plugin']) && $row['plugin'] == "2"){
     	  return "CLUB";
      }
}

//display Log Type
function showLogType($row) {
	  if(isset($row['log_type']) && $row['log_type'] == "1"){
     	 return "TODO";
      }
      if(isset($row['log_type']) && $row['log_type'] == "2"){
     	  return "VERSION HISTORY";
      }
      if(isset($row['log_type']) && $row['log_type'] == "3"){
     	  return "INFO";
      }
     
}

//display fontawesome icon documentlist
function showFilterMembers($filter) {
global $con;

	  if(isset($filter) && $filter == "f1"){
     	return "Houtblazers";
      }
      if(isset($filter) && $filter == "f2"){
     	return "Koperblazers";
      }
      if(isset($filter) && $filter == "f3"){
     	return "Slagwerk";
      }
      if(isset($filter) && $filter == "f4"){
     	return "Harp/Piano";
      }
      if(isset($filter) && $filter == "f5"){
     	return "Strijkers";
      }
      if(isset($filter) && $filter == "s1"){
     	return "Aerofonen";
      }
      if(isset($filter) && $filter == "s2"){
     	return "Chordofonen";
      }
      if(isset($filter) && $filter == "s3"){
     	return "Idiofonen";
      }
      if(isset($filter) && $filter == "s4"){
     	return "Membranofonen";
      }
      if(isset($filter) && $filter == "s5"){
     	return "Elektrofonen";
      }
      
}

//display fontawesome icon documentlist
function showFilterDocuments($superuser_id, $superuser, $filetype, $tag) {
global $con;

	  if($filetype <> "99"){
	  if(isset($filetype) && $filetype == "0"){
     	return "Document";
      }
      if(isset($filetype) && $filetype == "1"){
     	return "Link/URL";
      }
      if(isset($filetype) && $filetype == "2"){
     	return "Fotoalbum";
      }
      if(isset($filetype) && $filetype == "3"){
     	return "Video";
      }
      if(isset($filetype) && $filetype == "4"){
     	return "Audio";
      }
      if(isset($filetype) && $filetype == "5"){
     	return "Catalogus";
      }
      }
      if($tag <> "0"){
	      $superusers = array("club");
		  if(in_array($superuser, $superusers)){
	      $sql_tags = mysqli_query($con,"SELECT * FROM " . $superuser . "s_tags WHERE " . $superuser . "_id = $superuser_id AND id = $tag");
		  if(mysqli_num_rows($sql_tags) > 0) {
		  $row_tag = mysqli_fetch_array($sql_tags);
		  return $row_tag['title'];
		  }
		  else {
			  return false;
		  }
	      }
	      else {
		      return false;
	      }
     }
     else {
	     return false;
     }
}

//display fontawesome icon documentlist
function showIconDocumentListIcons($row, $width = "48px", $css = "", $type = "image") {
	  //document
	  if(isset($row['filetype']) && $row['filetype'] == "0"){
		$ext = strtolower(pathinfo($row['file'], PATHINFO_EXTENSION));
		if($ext == "pdf"){
		  if($type == "image"){ 
		  $icon = "<img src=\"img/filetypes/icon_pdf.png\" width=\"$width\" class=\"media-object $css\">"; 
		  }
		  if($type == "FA"){
		  $icon = "<span class=\"fal fa-file-pdf fa-lg bg-icons bg-color-3 icon-circle\" style=\"padding-top: 11px; padding-left: 2px\"></span>";
		  } 
		}
		elseif(($ext == "doc") || ($ext == "docx")){
		  $icon = "<img src=\"img/filetypes/icon_docx.png\" width=\"$width\" class=\"media-object $css\">";  
		}
		elseif(($ext == "xls") || ($ext == "xlsx") || ($ext == "csv")){
		  if($type == "image"){   
		  $icon = "<img src=\"img/filetypes/icon_xls.png\" width=\"$width\" class=\"media-object $css\">";
		  }
		   if($type == "FA"){
		  $icon = "<span class=\"fal fa-file-excel fa-lg bg-icons bg-color-2 icon-circle\" style=\"padding-top: 11px; padding-left: 1px\"></span>";
		 }
		  
		}
		elseif(($ext == "ppt") || ($ext == "pptx")){
		  $icon = "<img src=\"img/filetypes/icon_ppt.png\" width=\"$width\" class=\"media-object $css\">";  
		}
		
		elseif(($ext == "gpx") || ($ext == "GPX")){
		  $icon = "<img src=\"img/filetypes/icon_xml.png\" width=\"$width\" class=\"media-object $css\">";  
		}

		elseif(($ext == "jpg") || ($ext == "jpeg")){
		 if($type == "image"){    
		 $icon = "<img src=\"img/filetypes/icon_jpg.png\" width=\"$width\" class=\"media-object $css\">"; 
		 }
		  if($type == "FA"){
		  $icon = "<span class=\"fal fa-image fa-lg bg-icons bg-color-2 icon-circle\" style=\"padding-top: 11px; padding-left: 1px\"></span>";
		 }  
		}
		elseif($ext == "png"){
		 if($type == "image"){  
		 $icon = "<img src=\"img/filetypes/icon_png.png\" width=\"$width\" class=\"media-object $css\">";
		 }
		  if($type == "FA"){
		  $icon = "<span class=\"fal fa-image fa-lg bg-icons bg-color-2 icon-circle\" style=\"padding-top: 11px; padding-left: 1px\"></span>";
		 }  
		}
		elseif($ext == "gif"){
		 if($type == "image"){  
		 $icon = "<img src=\"img/filetypes/icon_gif.png\" width=\"$width\" class=\"media-object $css\">"; 
		 }
		  if($type == "FA"){
		  $icon = "<span class=\"fal fa-image fa-lg bg-icons bg-color-2 icon-circle\" style=\"padding-top: 11px; padding-left: 1px\"></span>";
		 }  
		}
		
	  }
	  //url
	  if(isset($row['filetype']) && $row['filetype'] == "1"){
		if($type == "image"){ 
		$icon = "<img src=\"img/filetypes/icon_html.png\" width=\"$width\" class=\"media-object $css\">";
		}
		if($type == "FA"){
		  $icon = "<span class=\"fal fa-code fa-lg bg-icons bg-color-6 icon-circle\" style=\"padding-top: 11px; padding-left: 1px\"></span>";
		 }
	  }
	  //photoalbum
	  if(isset($row['filetype']) && $row['filetype'] == "2"){
		 if($type == "image"){  
		 $icon = "<img src=\"img/filetypes/icon_jpg.png\" width=\"$width\" class=\"media-object $css\">";
		 }
		if($type == "FA"){
		  $icon = "<span class=\"fal fa-image fa-lg bg-icons bg-color-3 icon-circle\" style=\"padding-top: 11px; padding-left: 1px\"></span>";
		 }
	  }
	  //video
	  if(isset($row['filetype']) && $row['filetype'] == "3"){
		 if($type == "image"){ 
		 $icon = "<img src=\"img/filetypes/icon_mp4.png\" width=\"$width\" class=\"media-object $css\">";
		 }
		 if($type == "FA"){
		  $icon = "<span class=\"fal fa-video fa-lg bg-icons bg-color-5 icon-circle\" style=\"padding-top: 11px; padding-left: 1px\"></span>";
		 }
  
	  }
	  //audio
	  if(isset($row['filetype']) && $row['filetype'] == "4"){
		 if($type == "image"){
		 $icon = "<img src=\"img/filetypes/icon_mp3.png\" width=\"$width\" class=\"media-object $css\">";
		 }
		 if($type == "FA"){
		  $icon = "<span class=\"fal fa-music fa-lg bg-icons bg-color-1 icon-circle\" style=\"padding-top: 11px; padding-left: 1px\"></span>";
		 }
	  }
	  //catalogue
	  if(isset($row['filetype']) && $row['filetype'] == "5"){
		 if($type == "image"){
			 if($row['file'] <> ''){
				 if($row['isURL'] <> 1){
					 if(isset($row['club_id'])){
						 $superuser = "club";
						 $superuser_id = $row['club_id'];
					 }
					$icon = "<img src=\"/data/" . $superuser . "s_catalogue/" . $superuser_id . "/" . $row['file'] . "\" width=\"$width\" class=\"media-object $css\">"; 
				 }
				 else {
					$icon = "<img src=\"" . $row['file'] . "\" width=\"$width\" class=\"media-object $css\">";  
				 }
			 }
			 else { 
				 $icon = "<img src=\"img/no_image.png\" width=\"$width\" class=\"media-object $css\">";
			 }
		 }
		 if($type == "FA"){
		  $icon = "<span class=\"fal fa-image fa-lg bg-icons bg-color-1 icon-circle\" style=\"padding-top: 11px; padding-left: 1px\"></span>";
		 }
	  }
   
   return $icon;
}

//check category club
function isCategory($club_id,$category) {
	global $con;
	$sql_club = mysqli_query($con,"SELECT * FROM clubs WHERE id = $club_id AND category = '$category'");
	if(!$sql_club){
	error_log("Error function isCategory(): " . mysqli_error($con), 0);
	}
	if(mysqli_num_rows($sql_club) > 0){
		return true;
		}
		else {
			return false;
		}  
}

//get club_id  from tradetracker reference
function getSuperuserFromAffiliate($reference) {
		//cut off the first 'c' or 's'
		$superuser_id = substr($reference, 1);
		
		if (strpos($superuser_id, 'u') !== false) {
		//cut off everything after 'u'
		$superuser_id = strstr($superuser_id, 'u', true);
		}
		if (strpos($superuser_id, '-') !== false) {
		//cut off everything after '-'
		$superuser_id = strstr($superuser_id, '-', true);
		}
		if (strpos($superuser_id, '_') !== false) {
		//cut off everything after '-'
		$superuser_id = strstr($superuser_id, '_', true);
		}
		return $superuser_id;
}

//get club_id from tradetracker reference
function getUserFromAffiliate($reference) {
	if((substr( $reference, 0, 1 ) === "c") || (substr( $reference, 0, 1 ) === "s")){ 	
	//extract user id
	if (($pos = strpos($reference, "-u")) !== FALSE) { 
    return substr($reference, $pos+1); 
	}
	//extract user id
	elseif (($pos = strpos($reference, "u")) !== FALSE) { 
    return substr($reference, $pos+1); 
	}
	}
	else {
		//no user in reference: default as Frank Janssen
		return "20";
	}
	
}

//display instrument from instrument id
function DisplayInstruments($instrument_id) {
	global $con;
	if($instrument_id <> 0){ 
	$sql_instrument = mysqli_query($con,"SELECT * FROM musical_instruments WHERE id = $instrument_id");
	if(mysqli_num_rows($sql_instrument) > 0){ 
	$row_instrument = mysqli_fetch_array($sql_instrument);
	if($instrument_id == "1") {
		return "geen instrument";
	}
	else { 
	return $row_instrument['instrument'];
	}
	}
	else {
		return "geen instrument";
	}
	}
	else {
		return "";
	}
}

//display instrument
function ShowInstrumentsClub($kid_id,$adult_id, $manual_id, $club_id) {
	global $con;
	if($kid_id <> 0){
	$sql_instrument = "SELECT instrument_id FROM clubs_instruments 
	WHERE kid_id = $kid_id AND club_id = $club_id";
	}
	if(($kid_id == 0) && ($adult_id <> 0)){
	$sql_instrument = "SELECT instrument_id FROM clubs_instruments 
	WHERE parent_id = $adult_id AND club_id = $club_id";
	}
	if(($kid_id == 0) && ($adult_id == 0) && ($manual_id <> 0)){
	$sql_instrument = "SELECT member_instrument AS instrument_id FROM kids_clubs_manual
	WHERE id = $manual_id";
	}
	
	$sql_instrument = mysqli_query($con, $sql_instrument);
	
	if(mysqli_num_rows($sql_instrument) > 0){
		$instruments=array();
		while($row_instrument = mysqli_fetch_array($sql_instrument)){
			$instruments[] = DisplayInstruments($row_instrument['instrument_id']);			
		}
		return implode(", ", $instruments);
	}
	else {
		return false;
	}
}

//display instrument new
function ShowValueInstrumentsClub($kid_id,$adult_id,$manual_id,$club_id,$subcategory_id,$value) {
	global $con;
	
	if(($kid_id <> "0") || ($adult_id <> "0")){
	//there can only be one head instrument in one subcategory
	$sql_instrument = mysqli_query($con,
	"SELECT * FROM clubs_instruments WHERE kid_id = $kid_id AND parent_id = $adult_id AND club_id = $club_id AND headinstrument IN($subcategory_id)
	OR
	kid_id = $kid_id AND parent_id = $adult_id AND club_id = $club_id AND headinstrument = 0 AND subcategory_id IN($subcategory_id)
	ORDER BY id ASC, headinstrument <> 0 ASC LIMIT 1");

	if(mysqli_num_rows($sql_instrument) >  0){
		$row_instrument = mysqli_fetch_array($sql_instrument);
			if($value == "instrument"){
				return DisplayInstruments($row_instrument['instrument_id']);
			}
			else { 
			return $row_instrument[$value];
			}
		}	
	else {
		return "";
	}
	}
	if($manual_id <> "0"){
		$sql_instrument = mysqli_query($con,"SELECT member_instrument FROM kids_clubs_manual WHERE id = $manual_id");
		if(mysqli_num_rows($sql_instrument) > 0){ 
		$row_instrument = mysqli_fetch_array($sql_instrument);
			if($value == "instrument"){ 
			return DisplayInstruments($row_instrument['member_instrument']);
			}
			else {
				return "";
			}
		}
		else {
			return "";
		}
	}
}

//check favorite shop
function CheckFavoriteShop($shop_id,$user_id,$local) {
	global $con;
	if($local == "0"){
	$check = mysqli_query($con,"SELECT * FROM playday_plus_favorite_shop WHERE user_id = $user_id AND plus_id = $shop_id");	
	}
	if($local == "1"){
	$check = mysqli_query($con,"SELECT * FROM playday_plus_favorite_shop WHERE user_id = $user_id AND plus_local_id = $shop_id");	
	}
	if(mysqli_num_rows($check) > 0) {
		return true;
	}
	else { 
	return false;
	}
}

//display avatar
function showAvatar($row, $css = "avatar-smaller") {
    if(!isset($row['avatar']) || ($row['avatar' ] == "")) {
	  if(isset($row['sex'])){ 
	  if($row['sex'] == 1) {
		  echo "<img class=\"$css\" src=\"/app/img/anonymous.png\" style=\"display: inline\"></img>";
		  
	  }
	  else {
		  echo "<img class=\"$css\" src=\"/app/img/anonymous.png\" style=\"display: inline\"></img>";
	  }
	  }
	  else {
		  echo "<img class=\"$css\" src=\"/app/img/anonymous.png\" style=\"display: inline\"></img>";
	  }
	  }
	  if(isset($row['avatar']) && ($row['avatar' ] <> "")) {
	  	  echo "<img class=\"$css\" src=\"/data/avatars/$row[avatar]\" style=\"display: inline\"></img>";
	  }
}

//display avatar from kid_id
function showAvatarFromKidId($kid_id, $css = "avatar-small", $method = "echo") {
global $con;
global $pass;
global $logo_path;
$avatar = "<img class=\"$css useravatar\" src=\"" . $logo_path . "\" id=\"item-img-output\" style=\"display: inline\" ></img>";
$avatar_sql = mysqli_query($con,"SELECT AES_DECRYPT(avatar, SHA1('$pass')) AS avatar, sex FROM kids WHERE id = $kid_id");
if(mysqli_num_rows($avatar_sql) > 0){
	$row = mysqli_fetch_array($avatar_sql);
	if($row['avatar' ] == "") {
	  if(isset($row['sex'])){ 
	  if($row['sex'] == 1) {
		  $avatar = "<img class=\"$css useravatar\" src=\"/app/img/anonymous.png\" id=\"item-img-output\" style=\"display: inline\"></img>";
	  }
	  else {
		  $avatar = "<img class=\"$css useravatar\" src=\"/app/img/anonymous.png\" id=\"item-img-output\" style=\"display: inline\"></img>";
	  }
	  }
	  }
	  else {
	  	  $avatar = "<img class=\"$css useravatar\" src=\"/data/avatars/$row[avatar]\" id=\"item-img-output\" style=\"display: inline; background-color:white\"></img>";
	  }
	
} 
if($method == "echo"){
	echo $avatar;
}
if($method == "return"){
	return $avatar;
}  
}

//display avatar from kid_id for mail
function showAvatarFromKidIdMail($kid_id) {
global $con;
global $pass;
global $full_path;
global $data_path;
$avatar_sql = mysqli_query($con,"SELECT AES_DECRYPT(avatar, SHA1('$pass')) AS avatar, sex FROM kids WHERE id = $kid_id");
if(mysqli_num_rows($avatar_sql) > 0){
	$row = mysqli_fetch_array($avatar_sql);
	if($row['avatar' ] == "") {
	  if(isset($row['sex'])){ 
	  if($row['sex'] == 1) {
		  return $full_path . "/img/anonymous.png";
	  }
	  else {
		  return $full_path . "/img/anonymous.png";
	  }
	  }
	  }
	  else {
		  return $data_path . "/avatars/$row[avatar]";
	  }
	
}   
}

function showAvatarFamily($adult_id, $css="avatar-small", $method = "echo") {
global $con;
global $pass;
global $full_path;

$avatars = "<div class=\"$css\" style=\"overflow: hidden; background-color: white\">";

if(KidsFromParentId($adult_id) <> 0){
$kids_list = explode(',', KidsFromParentId($adult_id));
foreach($kids_list AS $kid_id){
	if(userValueKid($kid_id, "avatar") <> ""){ 
		$getavatar = "/data/avatars/" . userValueKid($kid_id, "avatar");
	}
	else {
		if(userValueKid($kid_id, "sex") == 1) {
		  $getavatar = "/app/img/anonymous.png";
		  }
		  else {
			  $getavatar =  "/app/img/anonymous.png";
		  }
	}
$avatars .= "<img width='33%' height='100%' src='" . $getavatar . "' id='item-img-output' style='border: 1px solid white;object-fit: cover;'></img>";

}
}
if(JoinedParentsFromParentId($adult_id) <> 0){
	$adult_list = explode(',', JoinedParentsFromParentId($adult_id));
	foreach($adult_list AS $joined_adult){
		if(userValue($joined_adult, "avatar") <> ""){
			$getavatar = "/data/avatars/" . userValue($joined_adult, "avatar");
		}
		else {
			$getavatar = "/app/img/anonymous.png";
		}
	$avatars .= "<img width='33%' height='100%' src='" . $getavatar . "' id='item-img-output' style='border: 1px solid white; object-fit: cover;'></img>";
		}
		}
	$avatars .= "</div>";
	
	if($method == "echo"){
		echo $avatars;
	}
	if($method == "return"){
		return $avatars;
	}   
}


//display avatar parent from parent_id
function showAvatarFromParentId($parent_id, $css="avatar-small", $method = "echo") {
global $con;
global $pass;
global $full_path;
$avatar_sql = mysqli_query($con,"SELECT sid,avatar,club_id,plus_id from users WHERE id = $parent_id");
if(mysqli_num_rows($avatar_sql) > 0){
	$row = mysqli_fetch_array($avatar_sql);
    if(($row['club_id'] <> 0) || ($row['plus_id'] <> 0)){
	    if($row['avatar'] == "") {
	  $avatar = "<img class=\"$css useravatar\" src=\"/app/img/anonymous.png\" id=\"item-img-output\" style=\"display: inline\" ></img>";
	  }
	  else {
	  	  $avatar = "<img class=\"$css useravatar\" src=\"/data/uploads/$row[avatar]\" id=\"item-img-output\" style=\"display: inline;\" ></img>";
	  }
    }
    else { 
	$sid = $row['sid'];
	if($row['avatar'] == "") {
	$avatar = "<img class=\"$css useravatar\" src=\"$full_path/img/anonymous.png\" id=\"item-img-output\" style=\"display: inline\"></img>";
	  }
	  else {
	$avatar = "<img class=\"$css useravatar\" src=\"/data/avatars/$row[avatar]\" id=\"item-img-output\" style=\"display: inline; background-color:white\" ></img>";
		}
	} 

} 
else {
	$avatar = "<img class=\"$css useravatar\" src=\"img/anonymous.png\" id=\"item-img-output\" style=\"display: inline\" ></img>";
}
if($method == "echo"){
	echo $avatar;
}
if($method == "return"){
	return $avatar;
}   
}


//display avatar instrument
function showAvatarInstrument($row, $css="avatar-smaller") {
    if($row['avatar' ] == "") {
	  return "<img class=\"$css\" src=\"img/playday_uploadpic.png\" style=\"display:inline\"></img>";
	  }
	  else {
	  return "<img class=\"$css\" src=\"/data/clubs_instruments/$row[club_id]/$row[avatar]\" style=\"display:inline\">";
	  }
}


//display avatar clubs from club_id
function showAvatarSuper($superuser_id, $superuser, $css = "avatar-small", $method = "echo") {
global $con;
global $logo_path;
$avatar = "";
$superusers = array("club");
if(in_array($superuser, $superusers)){ 
$avatar_sql = mysqli_query($con,"SELECT avatar FROM users WHERE " . $superuser . "_id = $superuser_id");
if(mysqli_num_rows($avatar_sql) > 0){
	$row = mysqli_fetch_array($avatar_sql);
	if($row['avatar' ] == "") {
	  $avatar = "<img class=\"$css\" src=\"" . $logo_path . "\" style=\"display: inline\" ></img>";

	  }
	  else {
	  	  $avatar = "<img class=\"$css\" src=\"/data/uploads/$row[avatar]\" style=\"display: inline\" ></img>";
	  }
}
else {
	$avatar = "<img class=\"$css\" src=\"" . $logo_path . "\" style=\"display: inline\" ></img>";	
}
}
else {
	$avatar = "<img class=\"$css\" src=\"" . $logo_path . "\" style=\"display: inline\" ></img>";	
}
if($method == "echo"){
	echo $avatar;
}
if($method == "return"){
	return $avatar;
}
}

//display avatar clubs from subcategory
function showAvatarSubcategory($superuser_id, $superuser, $subcategory_id, $css = "avatar-small", $method = "echo") {
	global $con;
	$avatar = "";
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	$avatar_sql = mysqli_query($con,"SELECT cat_name, avatar FROM " . $superuser . "s_categories WHERE " . $superuser . "_id = $superuser_id AND id = $subcategory_id");
	if(mysqli_num_rows($avatar_sql) > 0){
		$row = mysqli_fetch_array($avatar_sql);
		if($row['avatar' ] == "") {
	  	$avatar = "<img class=\"letterpic $css\" title=\"" . preg_replace("/[^A-Za-z0-9 ]/", '', $row['cat_name']) . "\" style=\"display:inline\"></img>";
	  	}
	  	else {
	  	  	$avatar = "<img class=\"$css\" src=\"/data/avatars/$row[avatar]\" style=\"display: inline\" ></img>";
	  	}
	}
	else {
		return false;	
	}
	}
	else {
		return false;	
	}
	if($method == "echo"){
		echo $avatar;
	}
	if($method == "return"){
		return $avatar;
	}
}


//display subject event calendar
function showSubject($row) {
if($row['subject'] <> 0 && $row['subject'] <> ""){
$subject_event =  stripslashes(mres($row['subject']));
echo "<br><strong>" . $subject_event . "</strong>";
}
}

//display comments
function showComments($row, $cutoff = false) {
	$comments =  strip_tags($row['comment'], '<a>');
	$comments = str_replace('<a', '<a target="_blank"', $comments);
	$comments = nl2br($comments);
	if($comments != "") {
		if($cutoff == true){
		if(strlen($comments) > 50){ 
		$comments = substr($comments, 0, strrpos(substr($comments, 0, 50), ' ')) . "...";
		}
		}
		echo "<tr><td><i class=\"fal fa-comment fa-fw\" aria-hidden=\"true\"></i></td><td style='word-break: break-word'><div>" . $comments . "</div></td></tr>";
	}
	else {
		//echo "<br><span class=\"fas fa-comment-dots\" aria-hidden=\"true\"></span>&nbsp;<i>Geen extra opmerkingen</i>";
	}
}

function showEventPlacement($superuser_id, $superuser, $eventID, $listcategories = 0, $echo = 1){
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	
	$check = mysqli_query($con, "SELECT ForAll, ForSome, ForKids, ForAdults, ForManual, sync_id, repeat_id, subcategory FROM calendar WHERE id = $eventID AND " . $superuser . "_id = '$superuser_id' ");
	if(mysqli_num_rows($check) > 0){ 
		$row = mysqli_fetch_array($check);
		if($superuser == "club"){ 
		if($echo == 1){ 
		if($row['sync_id'] <> ""){
			if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM clubs_categories_sync WHERE repeat_id = $row[repeat_id] AND club_id = $superuser_id")) > 0){
			echo "<small>Automatische import " . helpPopup("whatsautomaticimport", "gray", "question-circle", "info", "") . "</small><br>";
			}
		}
		}
		}
		if($row['ForAll'] == 0){ 
			if($listcategories == 0){
			//do not echo anything
			}
			else {
				return $row['subcategory'];
			}
				
		}
		if($row['ForAll'] == 1){ 
			if($listcategories == 0){
			echo "<small>Staat in alle subgroepen</small>";
			}
			else {
				return getSubcategoryIdsFromSuperUser($superuser_id, $superuser);
			}
				
		}
		if ($row['ForAll'] == "2"){
			if($listcategories == 0){
			echo "<small>Staat in ";
			$list = explode(',',$row['ForSome']);
			sort($list);
			foreach($list AS $sub){ 
			$subs[] = userValueSubcategory($sub, $superuser, "cat_name");
			 }
			echo implode(", ", $subs);
			echo "</small>";
			}
			else {
				return $row['ForSome'];	
			}
		}
		if ($row['ForAll'] == "3"){
			if($listcategories == 0){
			echo "<small> Staat in de agenda van ";
			$kids = array();
			if($row['ForKids'] <> "0"){
			$list_kids = explode(',',$row['ForKids']);
			sort($list_kids);
			foreach($list_kids AS $sub_kids){
			   $kids[] = userValueKid($sub_kids, "name") . " " . userValueKid($sub_kids, "surname"); 
			}
			}
			$adults = array();
			if($row['ForAdults'] <> "0"){
			$list_adults = explode(',',$row['ForAdults']);
			sort($list_adults);
			foreach($list_adults AS $sub_adults){
			   $adults[] = userValue($sub_adults, "username"); 
			}
			}
			$manuals = array();
			if($row['ForManual'] <> "0"){
			$list_manual = explode(',',$row['ForManual']);
			sort($list_manual);
			foreach($list_manual AS $sub_manual){
			   $manuals[] = userValueManual($sub_manual, $superuser, $superuser_id, "name") . " " . userValueManual($sub_manual, $superuser, $superuser_id, "surname"); 
			}
			}
			$allnames = array_merge($kids, $adults, $manuals);
			if(count($allnames) > 5){
				$firstfive = array_slice($allnames, 0, 5);
				echo implode(", ", $firstfive) . " en " . (count($allnames) - 5) . " anderen.</small>";
			}
			else {
			echo implode(", ", $allnames) . "</small>";
			}
			}
			else {
				return 0;
				//return $row['subcategory'];
			}
		}
		
	}
	else {
		echo "";
	}
	}
	else {
		echo "";
	}
	}

function showChatDate($row_answer){
	$today = date('d-m-y');
	if($today == $row_answer['answerdate']){
		return $row_answer['time'];
	}
	else {
		return $row_answer['answerdate'] . " om " . $row_answer['time'];
	}
}

function showChatContentRespond($chat_id){
	global $con;
	global $data_path;
	$sql = mysqli_query($con, "SELECT * FROM chat WHERE id = $chat_id");
	if(mysqli_num_rows($sql) > 0){
		$row_answer = mysqli_fetch_array($sql);
		$content = "<div style='border-left: 5px solid gray; padding-left: 5px; margin-bottom: 5px'><font color=black>" . userValue($row_answer['parent_id'], "username") . "</font><br>";
		if($row_answer['pic'] <> ""){
					 //add blank=1 at the end of the url to make android app open in browser (see Android App Code in Android Studio)
					 $content .= "<a rel=\"gallery-$row_answer[event_id]\" class=\"swipebox\" title=\"afbeelding\" href=\"/data/clubs_images/$row_answer[club_id]/chat/$row_answer[pic]\" data-href=\"" . $data_path . "/clubs_images/$row_answer[club_id]/chat/$row_answer[pic]?blank=1\"><img class=\"img-rounded\" src=\"/data/clubs_images/$row_answer[club_id]/chat/thumb_$row_answer[pic]\" width=\"20%\" style=\"display:block\"></a>";
         }
		 if($row_answer['document_id'] <> 0){
			 if($row_answer['club_id'] <> 0){
				 $superuser = "club";
				 $superuser_id = $row_answer['club_id'];
			 }
			 $sql_document = mysqli_query($con, "SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = '$superuser_id' AND id ='$row_answer[document_id]'");
			 if(mysqli_num_rows($sql_document) > 0){
			  $row_document = mysqli_fetch_array($sql_document);
			  $content .= "<table class='table-calendar'><tr><td>" . showIconDocumentListIcons($row_document, "18px") . "</td><td>" . $row_document['name'] . "</td></tr></table>";
		  	}
  }
        $content .= html_entity_decode($row_answer['comment']) . "</div>";
		return $content;
	}
	else {
		return "<div style='border-left: 5px solid gray; padding-left: 5px; margin-bottom: 5px'><font color=black><font color=gray><i>[Originele bericht bestaat niet meer]</i></font></div>";
	}
	
}

function showChatRead($chat_id, $parent_id, $method = "small", $event_id = 0) {
	global $con;
	$check_read = mysqli_query($con, "SELECT * FROM chat_read WHERE chat_id = $chat_id AND user_id <> $parent_id");
	if(mysqli_num_rows($check_read) > 0){
		if($method == "small"){ 
		echo "<font style=\"font-size: 8px\" class=\"pull-right\"><a role=\"button\" data-target=\"#myModalRead$chat_id\" data-toggle=\"modal\">gezien door " . mysqli_num_rows($check_read) . "</a></font>";
		}
		if($method == "dropdown"){
		$ps = "personen...";
		if(mysqli_num_rows($check_read) == 1){
			$ps = "persoon...";
		}
		if(mysqli_num_rows($check_read) > 1){
			$ps = "personen...";
		}
		echo "<li><a role=\"button\" class=\"OpenModalUser\" chat_id_attr=\"$chat_id\" type_attr=\"showreadchat\" parent_id_attr=\"$parent_id\"><span class=\"fal fa-eye pull-right\"></span> gelezen door " . mysqli_num_rows($check_read) . " " . $ps . "</a></li>";
		
		echo "<li><a role=\"button\" class=\"AnswerThisChat\" chat_id_attr=\"$chat_id\" event_id_attr=\"$event_id\"><span class=\"fal fa-reply pull-right\"></span> Beantwoord...</a></li>"; 
		echo "</ul>";	
		}
	
		?>
		<!-- hier stond modal -->
<?php
}
else {
	if($method == "dropdown"){
		echo "<li><a role=\"button\"><span class=\"fal fa-eye pull-right\"></span> ongelezen bericht</a></li>";
		echo "<li><a role=\"button\" class=\"AnswerThisChat\" chat_id_attr=\"$chat_id\" event_id_attr=\"$event_id\"><span class=\"fal fa-reply pull-right\"></span> Beantwoord...</a></li>"; 
		echo "</ul>";
	}
}
}

function ChatExists($eventID, $subcategory_id = 0, $superuser_id = 0, $superuser = "club") {
	global $con;
	if($eventID <> 0){	
	$check = mysqli_query($con, "SELECT * FROM chat WHERE event_id IN($eventID)");
	}
	else {
	$check = mysqli_query($con, "SELECT * FROM chat WHERE (subcategory_id = $subcategory_id OR ForAll = 1 OR ForAll = 2 AND FIND_IN_SET($subcategory_id, ForSome)) AND " . $superuser . "_id = $superuser_id");	
	}
	if(mysqli_num_rows($check) > 0){
		return mysqli_num_rows($check);
	}
	else {
		return false;
	}
}

function ChatUnread($eventID, $user_id, $subcategory_id = 0, $superuser_id = 0, $superuser = "club") {
	global $con;
	if($eventID <> 0){
	$check_read = mysqli_query($con, "SELECT chat.id, chat_read.user_id FROM chat JOIN chat_read ON chat.id = chat_read.chat_id WHERE event_id IN($eventID) AND user_id = $user_id");
	}
	else {
	$check_read = mysqli_query($con, "SELECT chat.id, chat_read.user_id FROM chat JOIN chat_read ON chat.id = chat_read.chat_id WHERE " . $superuser . "_id = $superuser_id AND user_id = $user_id AND 
	(subcategory_id = $subcategory_id
		OR ForAll = 1
		OR ForAll = 2 AND FIND_IN_SET($subcategory_id, ForSome)
	)");
	}
	$unread = ChatExists($eventID, $subcategory_id, $superuser_id , $superuser) - mysqli_num_rows($check_read);
	return $unread;

}

//display mail delay_type
function showMailStatus($row) {
	global $con;
	$delay_type = $row['delay_type'];
	$oneday_before = date('Y-m-d', strtotime($row['startdate'] .' -1 day'));
	$twoday_before = date('Y-m-d', strtotime($row['startdate'] .' -2 day'));
	$threeday_before = date('Y-m-d', strtotime($row['startdate'] .' -3 day'));
	$fourday_before = date('Y-m-d', strtotime($row['startdate'] .' -4 day'));
	$fiveday_before = date('Y-m-d', strtotime($row['startdate'] .' -5 day'));
	$sixday_before = date('Y-m-d', strtotime($row['startdate'] .' -6 day'));
	/* Set locale to Dutch */
	setlocale(LC_ALL, 'nl_NL');

		if($row['mail_sent'] <> "0000-00-00"){
			echo "<tr><td><i class=\"fal fa-envelope fa-fw\" aria-hidden=\"true\"></i></td><td>Agendapunt werd gemaild op " . formatDate($row['mail_sent'], "EEEE dd LLLL") . "</td></tr>";
		}
		else {
			//check if there's a mail in queue
			$sql = mysqli_query($con, "SELECT * FROM mails WHERE event_id = $row[eventID] AND senddate = CURDATE() AND queue = 1");
			if(mysqli_num_rows($sql) > 0){
			echo "<tr><td><i class=\"fal fa-envelope fa-fw\" aria-hidden=\"true\"></i></td><td>Agendapunt staat in queue en wordt vandaag gemaild <a class=\" notifit_custom mailinque location\"><span class=\"fal fa-question-circle\"></span></a></td></tr>";
			}
		}

		if($delay_type == "0") {
			echo "";
		}
		if($delay_type == "1") {
			echo "<tr><td><i class=\"fal fa-envelope fa-fw\" aria-hidden=\"true\"></i></td><td>Wordt automatisch gemaild op " . 
			formatDate($oneday_before, "EEEE dd LLLL") . " om 7u00</td></tr>";
			if(strtotime($oneday_before . " " . "7:00") < time()){
				echo "<tr><td><i class=\"fal fa-exclamation-circle fa-fw\" aria-hidden=\"true\"></i></td><td><font color='red'>Opgelet: Je hebt aangegeven dat dit agendapunt automatisch verzonden wordt op een moment in het verleden. Gelieve je instellingen aan te passen!</font></td></tr>";
			}
			
		}
		if($delay_type == "2") {
			echo "<tr><td><i class=\"fal fa-envelope fa-fw\" aria-hidden=\"true\"></i></td><td>Wordt automatisch gemaild op " . formatDate($twoday_before, "EEEE dd LLLL") . " om 7u00</td></tr>";
			if(strtotime($twoday_before . " " . "7:00") < time()){
				echo "<tr><td><i class=\"fal fa-exclamation-circle fa-fw\" aria-hidden=\"true\"></i></td><td><font color='red'>Opgelet: Je hebt aangegeven dat dit agendapunt automatisch verzonden wordt op een moment in het verleden. Gelieve je instellingen aan te passen!</font></td></tr>";
			}
		}
		if($delay_type == "3") {
			echo "<tr><td><i class=\"fal fa-envelope fa-fw\" aria-hidden=\"true\"></i></td><td>Wordt automatisch gemaild op " . formatDate($threeday_before, "EEEE dd LLLL") . " om 7u00</td></tr>";
			if(strtotime($threeday_before . " " . "7:00") < time()){
				echo "<tr><td><i class=\"fal fa-exclamation-circle fa-fw\" aria-hidden=\"true\"></i></td><td><font color='red'>Opgelet: Je hebt aangegeven dat dit agendapunt automatisch verzonden wordt op een moment in het verleden. Gelieve je instellingen aan te passen!</font></td></tr>";
			}
		}
		if($delay_type == "4") {
			echo "<tr><td><i class=\"fal fa-envelope fa-fw\" aria-hidden=\"true\"></i></td><td>Wordt automatisch gemaild op " . formatDate($fourday_before, "EEEE dd LLLL") . " om 7u00</td></tr>";
			if(strtotime($fourday_before . " " . "7:00") < time()){
				echo "<tr><td><i class=\"fal fa-exclamation-circle fa-fw\" aria-hidden=\"true\"></i></td><td><font color='red'>Opgelet: Je hebt aangegeven dat dit agendapunt automatisch verzonden wordt op een moment in het verleden. Gelieve je instellingen aan te passen!</font></td></tr>";
			}
		}
		if($delay_type == "5") {
			echo "<tr><td><i class=\"fal fa-envelope fa-fw\" aria-hidden=\"true\"></i></td><td>Wordt automatisch gemaild op " . formatDate($fiveday_before, "EEEE dd LLLL") . " om 7u00</td></tr>";
			if(strtotime($fiveday_before . " " . "7:00") < time()){
				echo "<tr><td><i class=\"fal fa-exclamation-circle fa-fw\" aria-hidden=\"true\"></i></td><td><font color='red'>Opgelet: Je hebt aangegeven dat dit agendapunt automatisch verzonden wordt op een moment in het verleden. Gelieve je instellingen aan te passen!</font></td></tr>";
			}
		}
		if($delay_type == "6") {
			echo "<tr><td><i class=\"fal fa-envelope fa-fw\" aria-hidden=\"true\"></i></td><td>Wordt automatisch gemaild op " . formatDate($sixday_before, "EEEE dd LLLL") . " om 7u00</td></tr>";
			if(strtotime($sixday_before . " " . "7:00") < time()){
				echo "<tr><td><i class=\"fal fa-exclamation-circle fa-fw\" aria-hidden=\"true\"></i></td><td><font color='red'>Opgelet: Je hebt aangegeven dat dit agendapunt automatisch verzonden wordt op een moment in het verleden. Gelieve je instellingen aan te passen!</font></td></tr>";
			}
		}
		
		
}

//display attachments
function showAttachments($superuser_id, $superuser, $eventID) {
	global $con;
	global $full_path;
	global $data_path;
	global $home_path;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql_attachments = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE event_id = $eventID AND " . $superuser . "_id = $superuser_id");
	$numrows_attachments = mysqli_num_rows($sql_attachments);
	if($numrows_attachments > 0){
		while($row_attachments = mysqli_fetch_array($sql_attachments)){
			if($row_attachments['filetype'] == "0"){
				$icons = "";
				if(file_exists('../data/' . $superuser . 's_documents/' . $superuser_id . '/thumb_' . $row_attachments['file'])) {
					$document_name = "<img src='$data_path/" . $superuser . "s_documents/$superuser_id/thumb_$row_attachments[file]'>";
					}
					else {
					$document_name = $row_attachments['name'];
					} 
				$ext = pathinfo($row_attachments['file'] , PATHINFO_EXTENSION);
				if($ext == "gpx"){
					//add blank=1 at end of url to make android app open in browser (see Android App Code in Android Studio)
					$icons = "&nbsp;<a href=\"" . $full_path . "/gpx_viewer.php?superuser_id=" . base64_url_encode($superuser_id) . "&superuser=" . base64_url_encode($superuser) . "&document_id=" . base64_url_encode($row_attachments['id']) . "&blank=1" . "\" class=\"none pull-right\" role=\"button\" target=\"_blank\"><i class=\"fas fa-map-marked-alt fa-fw\" aria-hidden=\"true\"></i></a>";
				}
				
			echo "<tr><td><i class=\"fal fa-paperclip fa-fw\" aria-hidden=\"true\"></i></td><td><a href=\"" . $data_path . "/" . $superuser . "s_documents/$superuser_id/$row_attachments[file]?blank=1\" class=\"location\" target=\"_blank\">" . $document_name . "</a>" . $icons . "<br>" . $row_attachments['comment'] . "</td></tr>";
			}
			if($row_attachments['filetype'] == "1"){
			$url = $row_attachments['file'];
			if (false !== strpos($url, '?')) {
				$url = $url . "&blank=1";
			}else{
				$url = $url . "/?blank=1";
			}
			
			echo "<tr><td><i class=\"fal fa-paperclip fa-fw\" aria-hidden=\"true\"></i></td><td><a href=\"$url\" class=\"location\" target=\"_blank\">" . $row_attachments['name'] . "</a></td></tr>";
			}
			if($row_attachments['filetype'] == "2"){ 
				if($row_attachments['isURL'] == "0"){
				echo "<tr><td><i class=\"fal fa-paperclip fa-fw\" aria-hidden=\"true\"></i></td><td><a class=\"location OpenSwipe\" name=\"$row_attachments[file]\">" . $row_attachments['name'] . "<br>" . $row_attachments['comment'] . "</a></td></tr>";
			
					$i = 1;
						$images = glob("../data/" . $superuser . "s_images/$superuser_id/$row_attachments[file]/*");
						foreach ($images as $image) {
					    	echo "<a rel=\"gallery-$row_attachments[file]\" class=\"swipebox box-$row_attachments[file]-$i\" href=\"$image\" data-href=\"" . $home_path . "/$image?blank=1\" title=\"$row_attachments[name]\"></a>";
					    	$i++;
						}
				}
				else {
				echo "<tr><td><i class=\"fal fa-paperclip fa-fw\" aria-hidden=\"true\"></i></td><td><a href=\"$row_attachments[file]\" class=\"location\" target=\"_blank\">" . $row_attachments['name'] . "<br>" . $row_attachments['comment'] . "</a></td></tr>";	
			}
			}
			if($row_attachments['filetype'] == "3"){ 
			if($row_attachments['isURL'] == "0"){
			//add blank=1 at end to make android app open in browser (see Android App Code in Android Studio)
			echo "<tr><td><i class=\"fal fa-paperclip fa-fw\" aria-hidden=\"true\"></i></td><td><a rel=\"gallery-video-$row_attachments[id]\" class=\"swipebox location\" href=\"/data/" . $superuser . "s_videos/$superuser_id/$row_attachments[file]\" data-href=\"" . $data_path . "/" . $superuser . "s_videos/$superuser_id/$row_attachments[file]?blank=1\" title=\"$row_attachments[name]\">" . $row_attachments['name'] . "<br>" . $row_attachments['comment'] . "</a></td></tr>";
			}
			else {
			echo "<tr><td><i class=\"fal fa-paperclip fa-fw\" aria-hidden=\"true\"></i></td><td><a href=\"$row_attachments[file]\" class=\"location\" target=\"_blank\">" . $row_attachments['name'] . "</a></td></tr>";		
			}
			}
			if($row_attachments['filetype'] == "4"){ 
			if($row_attachments['isURL'] == "0"){
			echo "<tr><td><i class=\"fal fa-paperclip fa-fw\" aria-hidden=\"true\"></i></td><td><a class=\"location\" href=\"" . $data_path . "/" . $superuser . "s_audio/$superuser_id/$row_attachments[file]?blank=1\" target=\"_blank\">" . $row_attachments['name'] . "<br>" . $row_attachments['comment'] . "</a></td></tr>";
			}
			else {
			echo "<tr><td><i class=\"fal fa-paperclip fa-fw\" aria-hidden=\"true\"></i></td><td><a href=\"$row_attachments[file]\" class=\"location\" target=\"_blank\">" . $row_attachments['name'] . "</a></td></tr>";		
			}
			}
			
		}
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}		
}

function showPayButtonsSuper($eventID, $superuser_id, $superuser, $subcategory_id) {
	global $con;
	global $full_path;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql_paybuttons = mysqli_query($con,"SELECT paybuttons.id AS button_id, paybuttons.*, " . $superuser . "s_paybuttons.* FROM " . $superuser . "s_paybuttons
	JOIN paybuttons ON paybuttons.id = " . $superuser . "s_paybuttons.button_id
	WHERE " . $superuser . "s_paybuttons.event_id = $eventID AND paybuttons.archive = 0");
	$numrows_paybuttons = mysqli_num_rows($sql_paybuttons);
	if($numrows_paybuttons > 0){
		while($row_paybuttons = mysqli_fetch_array($sql_paybuttons)){
		echo "<tr><td><i class=\"fal fa-credit-card fa-fw\" aria-hidden=\"true\"></i></td><td><a href=\"#\" data-target=\"#myModalPayButtons$eventID\" data-toggle=\"modal\" id=\"$eventID\" class=\"location OpenPayButtons\" name=\"$subcategory_id\" style='display:inline'>Shop-item actief</strong>";
		
		$check_payments = mysqli_query($con, "SELECT * FROM paybuttons_results WHERE " . $superuser . "_id = $superuser_id AND button_id = $row_paybuttons[button_id] AND status_id = 1");
		echo " <span class='label label-primary pull-right'>" . mysqli_num_rows($check_payments) . "</span></a></td></tr>";
		}
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
}

//get ids of everyone who hasn't payed yet (for mail function)
function showPayButtons($eventID, $superuser_id, $superuser, $adult_id, $kid_id, $manual_id) {
	global $con;
	global $full_path;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql_paybuttons = mysqli_query($con,"SELECT paybuttons.id AS button_id, paybuttons.*, " . $superuser . "s_paybuttons.* FROM " . $superuser . "s_paybuttons
	JOIN paybuttons ON paybuttons.id = " . $superuser . "s_paybuttons.button_id
	WHERE " . $superuser . "s_paybuttons.event_id = $eventID AND paybuttons.archive = 0");
	$numrows_paybuttons = mysqli_num_rows($sql_paybuttons);
	if($numrows_paybuttons > 0){
		while($row_paybuttons = mysqli_fetch_array($sql_paybuttons)){
			//check if user has already payed
			$check_payed = mysqli_query($con, "SELECT * FROM paybuttons_results WHERE button_id = $row_paybuttons[button_id] AND adult_id = $adult_id AND kid_id = $kid_id AND manual_id = $manual_id AND status_id = 1");
			if(mysqli_num_rows($check_payed) > 0){
				$row_payed = mysqli_fetch_array($check_payed);
				echo "<a role=\"button\" class=\"btn btn-primary btn-sm btn-block disabled\"><span class=\"fal fa-credit-card\"></span> $row_paybuttons[title]</a><div align=\"center\"><small>Je hebt deze betaling voldaan op " . date('d-m-Y', $row_payed['timestamp']) . "!</small></div><br>"; 
				}
				else { 
				$token = generateTokenPaybuttons($row_paybuttons['button_id'], $row_paybuttons['type'], $superuser, $superuser_id, $adult_id, $kid_id, $manual_id);
			echo "<a href=\"$full_path/superuser_paybuttons_details.php?" . $superuser . "_id=$superuser_id&type=$row_paybuttons[type]&button_id=$row_paybuttons[button_id]&adult_id=$adult_id&kid_id=$kid_id&manual_id=$manual_id&token=$token\" target=\"_blank\" role=\"button\" class=\"btn btn-primary btn-sm btn-block\"><span class=\"fal fa-credit-card\"></span> $row_paybuttons[title]</a><br>";
			}
			
		}
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
}

function PayButtonNotPayed($superuser_id, $superuser, $button_id, $who){
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT * FROM paybuttons WHERE " . $superuser . "_id = $superuser_id AND id = $button_id");
	if(mysqli_num_rows($sql) > 0){ 
	$row_button=mysqli_fetch_array($sql);
		
	if ($row_button['ForAll'] == "0"){
		$string = "AND subcategory IN ($row_button[subcategory_id])";
	}
	if ($row_button['ForAll'] == "1"){
		$string = "AND subcategory <> 0";
	}
	if ($row_button['ForAll'] == "2"){
		$string = "AND subcategory IN ($row_button[ForSome])";
	}
	if ($row_button['ForAll'] == "3"){
		if($who == "kids"){
			$string = "AND kid_id IN ($row_button[ForKids])";
		}
		if($who == "adults"){
			$string = "AND parent_id IN ($row_button[ForAdults])";
		}
		if($who == "manual"){ 
			$string = "AND id IN ($row_button[ForManual])";
		}	
	}
	
	if($who == "kids"){ 
	//kids
	$fetch_ids = mysqli_query($con,"SELECT * FROM kids_" . $superuser . "s
		  WHERE " . $superuser . "_id = $superuser_id 
		  $string
		  AND kid_id NOT IN (SELECT kid_id FROM paybuttons_results WHERE button_id = $button_id AND " . $superuser . "_id = $superuser_id)");

	if(mysqli_num_rows($fetch_ids) > 0){
		while($row = mysqli_fetch_array($fetch_ids)){
			$list_kids_not_payed[] = $row['kid_id'];
		}
		return implode(',', $list_kids_not_payed);
		
	}
	else {
		return 0;
	}
	}
	
	if($who == "adults"){
	//adults
	$fetch_ids = mysqli_query($con,"SELECT * FROM parents_" . $superuser . "s
		  WHERE " . $superuser . "_id = $superuser_id 
		  $string
		  AND parent_id NOT IN (SELECT adult_id FROM paybuttons_results WHERE button_id = $button_id AND " . $superuser . "_id = $superuser_id)");

	if(mysqli_num_rows($fetch_ids) > 0){
		while($row = mysqli_fetch_array($fetch_ids)){
			$list_adults_not_payed[] = $row['parent_id'];
		}
		return implode(',', $list_adults_not_payed);
	}
	else {
		return 0;
	}
	}
	
	if($who == "manual"){ 
	//manual
	$fetch_ids = mysqli_query($con,"SELECT *, id AS manual_id FROM kids_" . $superuser . "s_manual
		  WHERE " . $superuser . "_id = $superuser_id 
		  $string
		  AND id NOT IN (SELECT manual_id FROM paybuttons_results WHERE button_id = $button_id AND " . $superuser . "_id = $superuser_id)");
		  
	if(mysqli_num_rows($fetch_ids) > 0){
		while($row = mysqli_fetch_array($fetch_ids)){
			$list_manual_not_payed[] = $row['manual_id'];
		}
		return implode(',', $list_manual_not_payed);

	}
	else {
		return 0;	
	}

		
		}
	}
	}
	else {
		return 0;
	}	
}

//display description from order form
function DisplayDescription($option_id) {
	global $con;
	$sql = mysqli_query($con,"SELECT * FROM paybuttons_form WHERE id = $option_id");
	if(mysqli_num_rows($sql) > 0){ 
	$row = mysqli_fetch_array($sql);
		return $row['description'];
	}
	else {
		return "[geen omschrijving]";
	}	
}

//display location
function showLocationGoogle($row) {
global $google_maps_api;

	if(($row['street_google'] <> "") OR ($row['city_google'] <> "") OR ($row['postal_google'] <> "")) { 
		echo "<tr><td><i class=\"fal fa-map-marker fa-fw\" aria-hidden=\"true\"></i></td><td><a href=\"#\" name=\"$row[eventID]\" class=\" location-google\" data-target=\"#myModalGoogle$row[eventID]\" data-toggle=\"modal\" style=\"display: inline; color:black; text-decoration: underline\">" . $row['location_google'] . "</a></td></tr>";
		}
		else {
		if($row['location_google'] <> ""){ 
		echo "<tr><td><i class=\"fal fa-map-marker fa-fw\" aria-hidden=\"true\"></i></td><td>" . $row['location_google'] . "</td></tr>";
		}
		}
	?>
	<!-- Modal Google Maps button -->
	<div id="myModalGoogle<?php echo $row['eventID'] ?>" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	
	    <!-- Modal content-->
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	        <h5 class="modal-title"><?php echo $row['location_google'] ?></h5>
	      </div>
	      <div class="modal-body">
		  
		  <iframe id="googleframe<?php echo $row['eventID'] ?>" target="_parent"
		  width="100%"
		  height="450"
		  frameborder="0" style="border:0"
		  src="about:blank"
		  data-src="https://www.google.com/maps/embed/v1/place?key=<?php echo $google_maps_api ?>&q=<?php echo $row['street_google'] . "+" . $row['number_google'] . "+" . $row['postal_google'] . "+" . $row['city_google'] . "" ?>" allowfullscreen>
		</iframe> 
	
		
		      
		   						      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-primary btn-sm btn-block" data-dismiss="modal">Sluit</button>
	        
	      </div>
	    </div>
	
	  </div>
	</div>
<!-- end Modal -->
	<?php
}

function showTextShareButton($eventID){
	global $con;
	$sql = mysqli_query($con, "SELECT *, DATE(startdate) AS startdate, DATE(enddate) AS enddate, DATE_FORMAT(startdate,'%H:%i') AS start_time, DATE_FORMAT(enddate,'%H:%i') AS end_time, location AS location_google, street AS street_google, number AS number_google, postal AS postal_google, city AS city_google FROM calendar WHERE id = $eventID");
	if(mysqli_num_rows($sql) > 0){
	$row = mysqli_fetch_array($sql);
	
	//start building text
	$text = dutchDateFromEvent($eventID) . "\n";
	
	if($row['showtimeframe'] == "1"){
		if($row['enddate'] > $row['startdate']){
		$text .= "van " . date("d-m-Y", strtotime($row['startdate'])) . " tot " . date("d-m-Y", strtotime($row['enddate'])) . "\n";	
		}
		else { 
		$text .= "de hele dag\n";
		}
	}
	if($row['showtimeframe'] == "2"){
	if($row['enddate'] > $row['startdate']){
			$text .= "van " . date("d-m-Y", strtotime($row['startdate'])) . " tot " . date("d-m-Y", strtotime($row['enddate'])) . "\n";
	}
	}
	if($row['showtimeframe'] == "0"){
	if($row['start_time'] != "00:00"){ 
		if(($row['start_time'] == "05:55") && ($row['end_time'] != "23:55")){
			if($row['enddate'] > $row['startdate']){
			$text .= "van " . date("d-m-Y", strtotime($row['startdate'])) . " tot " . date("d-m-Y", strtotime($row['enddate'])) . " om " . $row['end_time'] . "\n";
			}
			else { 
			$text .= "tot $row[end_time]\n";
			}
		}
		if(($row['start_time'] == "05:55") && ($row['end_time'] == "23:55")){
			if($row['enddate'] > $row['startdate']){
			$text .= "van " . date("d-m-Y", strtotime($row['startdate'])) . " tot " . date("d-m-Y", strtotime($row['enddate'])) . "\n";
			}
			else {
			$text .= "de hele dag\n";
			}
		}
		if(($row['start_time'] != "05:55") && ($row['end_time'] != "23:55")){
			if($row['enddate'] > $row['startdate']){
			$text .= "van " . date("d-m-Y", strtotime($row['startdate'])) . " om " . $row['start_time'] . " tot " . date("d-m-Y", strtotime($row['enddate'])) . " om " . $row['end_time'] . "\n";
			}
			else {
			$text .= "$row[start_time] - $row[end_time]\n";
			}
		}
		if(($row['start_time'] != "05:55") && ($row['end_time'] == "23:55")){
			if($row['enddate'] > $row['startdate']){
			$text .= "van " . date("d-m-Y", strtotime($row['startdate'])) . " om " . $row['start_time']  . " tot " . date("d-m-Y", strtotime($row['enddate'])) . "\n";
			}
			else {
			$text .= "vanaf $row[start_time]\n";
			}
		}
		
	}
	}

	if($row['subject'] <> ""){
	 $text .= $row['subject'] . "\n";
	 }
	
	if(($row['street_google'] <> "") OR ($row['city_google'] <> "") OR ($row['postal_google'] <> "")) { 
		$text .= "Plaats: " . $row['location_google'] . "\n";
		}
		else {
		if($row['location_google'] <> ""){ 
		$text .= "Plaats: " . $row['location_google'] . "\n";
		}
	}
	
	$text .= "Extra info: " . strip_tags($row['comment'], '<br>')  . "\n";
	return $text;
	}
	else {
		return false;
	}
}

//display timeframe
function showTimeframe($row) {
	if($row['showtimeframe'] == "1"){
		if($row['enddate'] > $row['startdate']){
		echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>van <strong>" . date("d-m-Y", strtotime($row['startdate'])) . "</strong> tot <strong>" . date("d-m-Y", strtotime($row['enddate'])) . "</strong></td></tr>";	
		}
		else { 
		echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>de hele dag</td></tr>";
		}
	}
	if($row['showtimeframe'] == "2"){
	if($row['enddate'] > $row['startdate']){
			echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>van <strong>" . date("d-m-Y", strtotime($row['startdate'])) . "</strong> tot <strong>" . date("d-m-Y", strtotime($row['enddate'])) . "</strong></td></tr>";
	}
	}
	if($row['showtimeframe'] == "0"){
	if($row['start_time'] != "00:00"){ 
		if(($row['start_time'] == "05:55") && ($row['end_time'] != "23:55")){
			if($row['enddate'] > $row['startdate']){
			echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>van <strong>" . date("d-m-Y", strtotime($row['startdate'])) . "</strong> tot <strong>" . date("d-m-Y", strtotime($row['enddate'])) . "</strong> om <strong>" . $row['end_time'] . "</strong></td></tr>";
			}
			else { 
			echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>tot $row[end_time]</td></tr>";
			}
			$starttime = "begin van de dag";
		}
		if(($row['start_time'] == "05:55") && ($row['end_time'] == "23:55")){
			if($row['enddate'] > $row['startdate']){
			echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>van <strong>" . date("d-m-Y", strtotime($row['startdate'])) . "</strong> tot <strong>" . date("d-m-Y", strtotime($row['enddate'])) . "</strong></td></tr>";
			}
			else {
			echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>de hele dag</td></tr>";
			}
			$starttime = "begin van de dag";
		}
		if(($row['start_time'] != "05:55") && ($row['end_time'] != "23:55")){
			if($row['enddate'] > $row['startdate']){
			echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>van <strong>" . date("d-m-Y", strtotime($row['startdate'])) . "</strong> om <strong>" . $row['start_time'] . "</strong> tot <strong>" . date("d-m-Y", strtotime($row['enddate'])) . "</strong> om <strong>" . $row['end_time'] . "</strong></td></tr>";
			}
			else {
			echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>$row[start_time] - $row[end_time]</td></tr>";
			}
		}
		if(($row['start_time'] != "05:55") && ($row['end_time'] == "23:55")){
			if($row['enddate'] > $row['startdate']){
			echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>van <strong>" . date("d-m-Y", strtotime($row['startdate'])) . "</strong> om <strong>" . $row['start_time']  . "</strong> tot <strong>" . date("d-m-Y", strtotime($row['enddate'])) . "</strong></td></tr>";
			}
			else {
			echo "<tr><td><i class=\"fal fa-clock fa-fw\" aria-hidden=\"true\" data-intro=\"Tijdsduur van de activiteit\"></i></td><td>vanaf $row[start_time]</td></tr>";
			}
			$starttime = "begin van de dag";
		}
		
	}
	else {
		echo "";
	}
	}
}


//check if member is present in Absence list Clubs
function AbsenceGoSuper($superuser, $kid_id,$parent_id,$manual_kid_id,$eventID,$value) {
	global $con;
	if($superuser == "club"){
	$sql = mysqli_query($con,"SELECT * FROM RSVP WHERE kid_id = $kid_id AND parent_id = $parent_id AND manual_kid_id = $manual_kid_id AND event_id = $eventID AND NoGo IN($value)");
	}
	if(mysqli_num_rows($sql) > 0){
		return "checked";
		}
		else {
			return "";
		}  
}

function displayOwnRSVPs($eventID, $kid_id, $parent_id, $NoGo) {
global $con;
	$check = mysqli_query($con,"SELECT * FROM RSVP WHERE kid_id IN($kid_id) AND parent_id IN($parent_id) AND event_id = $eventID AND NoGo IN($NoGo)");
	if(mysqli_num_rows($check) > 0){
		return true;
	}
	else {
		return false;
	} 

}

function displayRSVPs($eventID, $all, $kid_id, $NoGo) {
	global $con;
	global $pass;
	$check = mysqli_query($con,"SELECT * FROM RSVP WHERE event_id = $eventID AND NoGo IN($NoGo)");
	$num_rows_check=mysqli_num_rows($check);
	if($num_rows_check > 0){
		while($row_check = mysqli_fetch_array($check)) {
			if($row_check['car'] == 1){
				$car = " <span class=\"fal fa-car\"></span>";
			}
			else {
				$car = "";
			}
		if($NoGo == "0"){
			if($row_check['kid_id'] <> "0"){ 
			$present[] = "<a class=\"location OpenModalUser\" type_attr=\"userinfo\" name=\"$eventID\" kid_id_attr=\"$row_check[kid_id]\" parent_id_attr=\"0\">" . userValueKid($row_check['kid_id'], "name") . " " . userValueKid($row_check['kid_id'], "surname") . "" . $car . "</a>";
			}
			if($row_check['parent_id'] <> "0"){ 
			$present[] = "<a class=\"location OpenModalUser\" type_attr=\"userinfo\" name=\"$eventID\" kid_id_attr=\"0\" parent_id_attr=\"$row_check[parent_id]\" >" . userValue($row_check['parent_id'], "username") . "" . $car . "</a>";
			}
			if($row_check['manual_kid_id'] <> "0"){ 
				$present[] = "" . userValueManual($row_check['manual_kid_id'], "club", $row_check['club_id'], "name") . " " . userValueManual($row_check['manual_kid_id'], "club", $row_check['club_id'], "surname") . "" . $car . "";
			}
		
		}
		
		if($NoGo == "1,2"){
			if($row_check['kid_id'] <> "0"){ 
			$present[] = userValueKid($row_check['kid_id'], "name") . " " . userValueKid($row_check['kid_id'], "surname");
			}
			if($row_check['parent_id'] <> "0"){ 
			$present[] = userValue($row_check['parent_id'], "username");
			}
			if($row_check['manual_kid_id'] <> "0"){ 
			$present[] = userValueManual($row_check['manual_kid_id'], "club", $row_check['club_id'], "name") . " " . userValueManual($row_check['manual_kid_id'], "club", $row_check['club_id'], "surname");
			}
		}
	}
	if($NoGo == "0"){
	if($all == "0"){
	if($num_rows_check > 2) { 
	$present_kids = array_slice($present, 0, 2);
	$present_kids = implode(", ", $present_kids);
	$others = " <a class=\"ShowMoreLink location\" id=\"$eventID\" kid_id_attr=\"$kid_id\">en " . ($num_rows_check - 2) . " anderen.</a>";
	}
	else {
	$present_kids = implode(", ", $present);
	$others = "";	
	}
	echo "<tr id='ShowMore" . $kid_id . "_" . $eventID ."'><td><i class=\"fal fa-user-check fa-fw\" aria-hidden=\"true\"></i></td><td>$present_kids $others</td></tr>"; 
	}
	if($all == "1"){
	$present_kids = implode(", ", $present);
	echo "<tr id='ShowAll" . $kid_id . "_" . $eventID ."' style='display:none'><td><i class=\"fal fa-user-check fa-fw\" aria-hidden=\"true\"></i></td><td>$present_kids</td></tr>";
	}
	}
	
	if($NoGo == "1,2"){
	if($all == "0"){
	if($num_rows_check > 2) { 
	$present_kids = array_slice($present, 0, 2);
	$present_kids = implode(", ", $present_kids);
	$others = " <a class=\"ShowMoreLinkNoGo location\" id=\"$eventID\" kid_id_attr=\"$kid_id\">en " . ($num_rows_check - 2) . " anderen.</a>";
	}
	else {
	$present_kids = implode(", ", $present);
	$others = "";	
	}
	echo "<tr id='ShowMoreNoGo" . $kid_id . "_" . $eventID ."'><td><i class=\"fal fa-user-times fa-fw\" aria-hidden=\"true\"></i></td><td>$present_kids $others</td></tr>";
	}
	if($all == "1"){
	$present_kids = implode(", ", $present);
	echo "<tr id='ShowAllNoGo" . $kid_id . "_" . $eventID ."' style='display:none'><td><i class=\"fal fa-user-times fa-fw\" aria-hidden=\"true\"></i></td><td>$present_kids</td></tr>";
	}
	}
	}
}


//display RSVP public calendar (for manual members)
function showRSVPsClubsManual($manual_kid_id,$row) {
	global $con;
	global $pass;
	$check = mysqli_query($con,"SELECT * 
	FROM RSVP
	WHERE event_id = $row[eventID]
	AND manual_kid_id = $manual_kid_id");
	
	$check2 = mysqli_query($con, "SELECT club_id FROM calendar WHERE id = $row[eventID]");
		if(mysqli_num_rows($check2) > 0){
			$row_check2 = mysqli_fetch_array($check2);
			if($row_check2['club_id'] <> 0){
			$firstname = userValueManual($manual_kid_id, "club", $row_check2['club_id'], "name");
		}
		}
		else {
			$firstname = "";
		}
	
	if(mysqli_num_rows($check) > 0){
		$row_check = mysqli_fetch_array($check);
		
		if($row_check['NoGo'] == "0"){ 
	echo "<tr><td><i class=\"fas fa-user-check fa-fw\" aria-hidden=\"true\"></i></td><td>Bedankt, $firstname, je hebt je aanwezigheid bevestigd.</td></tr>"; 
		}
		if($row_check['NoGo'] != "0"){ 
	echo "<tr><td><i class=\"fal fa-user-times fa-fw\" aria-hidden=\"true\"></i></td><td>Je aangegeven dat je niet aanwezig zal zijn.</td></tr>"; 
		}
		
	}
	else {
		if(DateFromEvent($row['eventID']) >= date('Y-m-d')){
		echo "<tr><td><i class=\"fal fa-info-circle fa-fw\" aria-hidden=\"true\"></i></td><td>$firstname: Bevestig je aanwezigheid via een van de knoppen hieronder</td></tr>";
		}
	}
	}

//display max RSVP
function displayRSVPlimit($eventID, $superuser_id, $superuser, $is_superuser = 1) {
	global $con;
	global $pass;
	$max_description = "";
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	
	$check = mysqli_query($con,"SELECT RSVP.* 
	FROM RSVP
	JOIN kids ON RSVP.kid_id = kids.id
	WHERE event_id = $eventID
	AND NoGo = 0
	UNION
	SELECT RSVP.*
	FROM RSVP
	JOIN users ON RSVP.parent_id = users.id
	WHERE event_id = $eventID
	AND NoGo = 0
	UNION
	SELECT RSVP.*
	FROM RSVP
	JOIN kids_" . $superuser . "s_manual ON RSVP.manual_kid_id = kids_" . $superuser . "s_manual.id
	WHERE event_id = $eventID
	AND NoGo = 0");
	$num_rows_check=mysqli_num_rows($check);
		
		
	$check_limit = mysqli_query($con, "SELECT * FROM RSVP_settings WHERE event_id = $eventID AND " . $superuser . "_id = $superuser_id AND inactive = 0");
	if(mysqli_num_rows($check_limit) > 0){
		$row_limit = mysqli_fetch_array($check_limit);
		$available_places = $row_limit['max_limit'] - $num_rows_check;
		if($available_places > 0){
		if($row_limit['comment_limit_reason'] == ""){
		$max_description = "Er zijn maximum " . $row_limit['max_limit'] . " deelnemers toegestaan voor deze activiteit";
		}
		else {
		$max_description = $row_limit['comment_limit_reason'];
		}
		}
		if($available_places <= 0){
		if($row_limit['comment_limit_reached'] == ""){
		$max_description = "<font color=red>Sorry, het maximum aantal deelnemers is bereikt voor deze activiteit.</font>";
		}
		else {
		$max_description = "<font color=red>" . $row_limit['comment_limit_reached'] . "</font>";
		}	
		}
		$labelcolor = "warning";
		if(($available_places <= $row_limit['max_limit']) && ($available_places > $row_limit['max_limit']/2)){
			$labelcolor = "success";
		}
		if(($available_places <= $row_limit['max_limit']/2) && ($available_places > 0)){
			$labelcolor = "warning";
		}
		if($available_places <= 0){
			$labelcolor = "danger";
		}
		
		$link = "role='button' class='none'";
		if($is_superuser == 1){
			$link = "role=\"button\" id=\"$eventID\" class=\"OpenAbsences location\" name=\"takeabsences\"";
		}
		
		echo "<tr><td><i class=\"fal fa-users-cog fa-fw\" aria-hidden=\"true\"></i></td><td><a $link style='display:inline'>$max_description <span class='label label-$labelcolor pull-right'>" . $available_places . "/" . $row_limit['max_limit'] . "</span></a></td></tr>";
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
}

//display block RSVP
function displayRSVPblock($eventID, $superuser_id, $superuser, $is_superuser = 1) {
	global $con;
	$comment_block = "";
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	
	$check_block = mysqli_query($con, "SELECT * FROM RSVP_settings WHERE event_id = $eventID AND " . $superuser . "_id = $superuser_id AND inactive = 1 AND block = 1");
	if(mysqli_num_rows($check_block) > 0){
		$row_block = mysqli_fetch_array($check_block);
		if($row_block['comment_block'] == ""){
		echo "<tr><td><i class=\"fal fa-users-cog fa-fw\" aria-hidden=\"true\"></i></td><td>Inschrijvingen voor deze activiteit zijn uitgeschakeld.";
		if($is_superuser == 0){
		echo helpPopup("whatsblockRSVPusers", "black", "question-circle", "info", "");
		}
		echo "</td></tr>";
		}
		else {
		echo "<tr><td><i class=\"fal fa-users-cog fa-fw\" aria-hidden=\"true\"></i></td><td>" . $row_block['comment_block'];
		if($is_superuser == 0){
		echo helpPopup("whatsblockRSVPusers", "black", "question-circle", "info", "");
		}
		echo "</td></tr>";
		}
		
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
}

//display RSVP (for calendar items Clubs)
function displayRSVPsSuper($eventID, $superuser_id, $superuser, $all, $NoGo) {
	global $con;
	global $pass;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$check = mysqli_query($con,"SELECT RSVP.*, CONVERT(CONCAT(AES_DECRYPT(kids.name, SHA1('$pass')), ' ', AES_DECRYPT(kids.surname, SHA1('$pass')))  USING utf8) AS fullname 
	FROM RSVP
	JOIN kids ON RSVP.kid_id = kids.id
	WHERE event_id = $eventID
	AND NoGo IN ($NoGo)
	UNION
	SELECT RSVP.*, users.username AS fullname
	FROM RSVP
	JOIN users ON RSVP.parent_id = users.id
	WHERE event_id = $eventID
	AND NoGo IN ($NoGo)
	UNION
	SELECT RSVP.*, CONVERT(CONCAT(AES_DECRYPT(kids_" . $superuser . "s_manual.name, SHA1('$pass')), ' ', AES_DECRYPT(kids_" . $superuser . "s_manual.surname, SHA1('$pass')))  USING utf8) AS fullname
	FROM RSVP
	JOIN kids_" . $superuser . "s_manual ON RSVP.manual_kid_id = kids_" . $superuser . "s_manual.id
	WHERE event_id = $eventID
	AND NoGo IN ($NoGo)");
	$num_rows_check=mysqli_num_rows($check);
	if($num_rows_check > 0){
	while($row_check = mysqli_fetch_array($check)) {
		$verified = "";
		if($row_check['verified'] == 1){
			$verified = "<div style=\"font-size: 1rem; display:inline\"><span class='fal fa-qrcode' style='color: green; line-height: 9px; vertical-align: top;'></span></div>";
		}
		if($row_check['extra_info'] <> ""){ 
			$present[] = $row_check['fullname'] . "" . $verified . "<div style=\"font-size: 1rem; display:inline\"><span class='fal fa-comment-alt-dots fa-sm' style='line-height: 9px; vertical-align: top;'></span></div>";
			}
			else {
			$present[] = $row_check['fullname'] . "" . $verified;	
			}
	}
	if($NoGo == "0"){
	if($all == "0"){
	if($num_rows_check > 2) { 
	$present_kids = array_slice($present, 0, 2);
	$present_kids = implode(", ", $present_kids);
	$others = " <a class=\"ShowMoreLink location\" id=\"$eventID\">en " . ($num_rows_check - 2) . " anderen.</a>";
	}
	else {
	$present_kids = implode(", ", $present);
	$others = "";	
	}
	echo "<tr id='ShowMore$eventID'><td><i class=\"fal fa-user-check fa-fw\" aria-hidden=\"true\"></i></td><td>$present_kids $others <a role=\"button\" id=\"$eventID\" class=\"OpenAbsences location\" name=\"takeabsences\" style='display:inline'><span class='label label-primary pull-right'>$num_rows_check</span></a></td></tr>"; 
	}
	if($all == "1"){
	$present_kids = implode(", ", $present);
	echo "<tr id='ShowAll$eventID' style='display:none'><td><i class=\"fal fa-user-check fa-fw\" aria-hidden=\"true\"></i></td><td>$present_kids <a role=\"button\" id=\"$eventID\" class=\"OpenAbsences location\" name=\"takeabsences\" style='display:inline'><span class='label label-primary pull-right'>$num_rows_check</span></a></td></tr>";
	}
	}
	
	if($NoGo == "1,2"){
	if($all == "0"){
	if($num_rows_check > 2) { 
	$present_kids = array_slice($present, 0, 2);
	$present_kids = implode(", ", $present_kids);
	$others = " <a class=\"ShowMoreLinkNoGo location\" id=\"$eventID\">en " . ($num_rows_check - 2) . " anderen.</a>";
	}
	else {
	$present_kids = implode(", ", $present);
	$others = "";	
	}
	echo "<tr id='ShowMoreNoGo$eventID'><td><i class=\"fal fa-user-times fa-fw\" aria-hidden=\"true\"></i></td><td>$present_kids $others <a role=\"button\" id=\"$eventID\" class=\"OpenAbsences location\" name=\"takeabsences\" style='display:inline'><span class='label label-primary pull-right'>$num_rows_check</span></a></td></tr>"; 
	}
	if($all == "1"){
	$present_kids = implode(", ", $present);
	echo "<tr id='ShowAllNoGo$eventID' style='display:none'><td><i class=\"fal fa-user-times fa-fw\" aria-hidden=\"true\"></i></td><td>$present_kids <a role=\"button\" id=\"$eventID\" class=\"OpenAbsences location\" name=\"takeabsences\" style='display:inline'><span class='label label-primary pull-right'>$num_rows_check</span></a></td></tr>";
	}
	}
	}
	}
	else {
		return false;
	}
}

//show extra user info in RSVP function
function displayExtraInfoRSVP($kid_id, $adult_id, $manual_id, $eventID) {
	global $con;
	if($kid_id <> 0){ 
	$sql_extrainfo = mysqli_query($con,"SELECT * FROM RSVP WHERE kid_id = $kid_id AND manual_kid_id = $manual_id AND event_id = $eventID AND extra_info <> ''");
	}
	else {
	$sql_extrainfo = mysqli_query($con,"SELECT * FROM RSVP WHERE parent_id = $adult_id AND manual_kid_id = $manual_id AND event_id = $eventID AND extra_info <> ''");	
	}
	if(mysqli_num_rows($sql_extrainfo) > 0){ 
	$row_extrainfo = mysqli_fetch_array($sql_extrainfo);
	echo "<tr><td><span class='fal fa-comment-alt-dots'></span></td><td>" . $row_extrainfo['extra_info'] . "</td></tr>";
	}
	else {
		return false;
	}
}

//show verified info in RSVP function
function displayVerifiedRSVP($kid_id, $adult_id, $manual_id, $eventID) {
global $con;
$sql_verified = mysqli_query($con,"SELECT *, DATE(timestamp) AS timestamp_date, DATE_FORMAT(timestamp,'%H:%i') AS timestamp_time FROM RSVP WHERE kid_id = $kid_id AND parent_id = $adult_id AND manual_kid_id = $manual_id AND event_id = $eventID AND verified = 1");
if(mysqli_num_rows($sql_verified) > 0){ 
$row_verified = mysqli_fetch_array($sql_verified);
$verified_by = "";
if($row_verified['verified_by'] <> 0){
	$verified_by = " door " . userValue($row_verified['verified_by'], "username");
}
echo "<tr><td><span class='fal fa-qrcode' style='color:green'></span></td><td>" . date("d-m-Y", strtotime($row_verified['timestamp_date'])) . " om " . $row_verified['timestamp_time'] . "" . $verified_by . "</td></tr>";
}
else {
	return false;
}
}


//display read confirmation mails (for calendar items Clubs)
function showReadMailSuper($eventID,$superuser_id,$superuser,$mail_id,$all, $style = "icon") {
	global $con;
	global $pass;
	
	if($style == "icon"){
		$icon = "<i class=\"fal fa-envelope-open fa-fw\" aria-hidden=\"true\"></i>";
	}
	if($style == "text"){
		$icon = "<strong>Leesbevestiging:</strong>";
	}
	
	$check = mysqli_query($con,"SELECT *
	FROM mail_read
	WHERE event_id = $eventID AND mail_id = $mail_id");
	$num_rows_check=mysqli_num_rows($check);
	if($num_rows_check > 0){
	while($row_check = mysqli_fetch_array($check)) {
		if($row_check['parent_id'] <> 0){
		if(userValue($row_check['parent_id'], "username")){ 
		$present[] = userValue($row_check['parent_id'], "username");
		}
		}
		if($row_check['manual_kid_id'] <> 0){
			if(userValueManual($row_check['manual_kid_id'], $superuser, $superuser_id, "name")){
			$present[] = userValueManual($row_check['manual_kid_id'], $superuser, $superuser_id, "name") . " " . userValueManual($row_check['manual_kid_id'], $superuser, $superuser_id, "surname"); 
	   	}
		}
	}
	if($all == "0"){
	if($num_rows_check > 2) { 
	$present_kids = array_slice($present, 0, 2);
	$present_kids = implode(", ", $present_kids);
	$others = "<a class=\"ShowMoreLinkRead location\" name=\"$mail_id\" id=\"$eventID\"> en " . ($num_rows_check - 2) . " anderen.</a>";
	}
	else {
	$present_kids = implode(", ", $present);
	$others = "";	
	}
	echo "<tr id='ShowRead" . $eventID . "_" . $mail_id . "'><td>$icon</td><td>" . $present_kids . " " . $others . "</td></tr>"; 
	}
	if($all == "1"){
	$present_kids = implode(", ", $present);
	echo "<tr id='ShowAllRead" . $eventID . "_" . $mail_id . "' style='display:none'><td>$icon</td><td>" . $present_kids . "</td></tr>";
	}
	}
}

function checkReadMail($mail_id, $parent_id){
global $con;
	$check = mysqli_query($con,"SELECT * FROM mail_read WHERE mail_id = $mail_id AND parent_id = $parent_id");
	if(mysqli_num_rows($check) > 0){
		return true;
	}
	else {
		return false;
	}
}

//display format date: dutch date long
function dutchDate($row) {
	$date = date("Y-m-d",strtotime($row['startdate']));
	/* Set locale to Dutch */
	setlocale(LC_ALL, 'nl_NL');
	echo "" . formatDate($date, "EEEE dd LLLL");
}

//display dutch format date from event
function dutchDateFromEvent($event_id, $year = false) {
	global $con;
	$get_date = mysqli_query($con,"SELECT DATE(startdate) AS startdate FROM calendar WHERE id = $event_id");
	if(mysqli_num_rows($get_date) > 0){
		$row = mysqli_fetch_array($get_date);
		$date = date("Y-m-d",strtotime($row['startdate']));
		/* Set locale to Dutch */
	setlocale(LC_ALL, 'nl_NL');
	if($year == false){
	return "" . formatDate($date, "EEEE dd LLLL");
	}
	if($year == true){
	return "" . formatDate($date, "EEEE dd LLLL");
	}
	}
}

//display format date: english date from event
function DateFromEvent($event_id) {
	global $con;
	$get_date = mysqli_query($con,"SELECT DATE(startdate) AS startdate FROM calendar WHERE id = $event_id");
	if(mysqli_num_rows($get_date) > 0){
		$row = mysqli_fetch_array($get_date);
		return date("Y-m-d",strtotime($row['startdate']));
	}
}

//admin name from admin_id
function AdminNameFromAdminId($admin_id) {
	  if(isset($admin_id) && $admin_id == "2"){
     	 return "Simon";
      }
      if(isset($admin_id) && $admin_id == "3"){
     	 return "Tim";
      }
     
}

function SuperuserIDFromSuperuserAlias($alias, $superuser) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$get_alias = mysqli_query($con,"SELECT " . $superuser . "_id AS superuser_id FROM users WHERE alias = '$alias' AND " . $superuser . "_id <> 0");
	if(mysqli_num_rows($get_alias) > 0){
		$row = mysqli_fetch_array($get_alias);
		return $row['superuser_id'];
	}
	else {
		return "0";
	}
	}
	else {
		return "0";
	}
}

//display format date: dutch date short (only daynumber)
function dutchDateShort($row, $type="number") {
	$date = date("Y-m-d",strtotime($row['startdate']));
	/* Set locale to Dutch */
	setlocale(LC_ALL, 'nl_NL');
	if($type == "number"){
	echo formatDate($date, "dd");
	}
	if($type == "day"){
	echo formatDate($date, "EE");	
	}
}


//display button RSVP Clubs
function showButtonRSVPClubs($kid_id,$adult_id,$row) {
global $con;

//check for max_limit in RSVP_settings
$limit_set = 0;
$check_limit = mysqli_query($con, "SELECT * FROM RSVP_settings WHERE event_id = $row[eventID] AND inactive = 0");
if(mysqli_num_rows($check_limit) > 0){
	$limit_set = 1;
	$row_limit = mysqli_fetch_array($check_limit);
	
	$check = mysqli_query($con,"SELECT RSVP.* 
	FROM RSVP
	JOIN kids ON RSVP.kid_id = kids.id
	WHERE event_id = $row[eventID]
	AND NoGo = 0
	UNION
	SELECT RSVP.*
	FROM RSVP
	JOIN users ON RSVP.parent_id = users.id
	WHERE event_id = $row[eventID]
	AND NoGo = 0
	UNION
	SELECT RSVP.*
	FROM RSVP
	JOIN kids_clubs_manual ON RSVP.manual_kid_id = kids_clubs_manual.id
	WHERE event_id = $row[eventID]
	AND NoGo = 0");
	$num_rows_check=mysqli_num_rows($check);

	$available_places = $row_limit['max_limit'] - $num_rows_check;
	$max_limit_notif = "";
	if($available_places <= 0){
		$max_limit_notif = "Het maximum aantal deelnemers is bereikt. Je kan je aanwezigheid nu niet bevestigen.";
	}
}


if($kid_id <> 0){ 
$check = mysqli_query($con,"SELECT * FROM RSVP WHERE kid_id = $kid_id AND event_id = $row[eventID]");
}
else {
$check = mysqli_query($con,"SELECT * FROM RSVP WHERE parent_id = $adult_id AND event_id = $row[eventID]");	
}
$num_rows_check=mysqli_num_rows($check);
if($num_rows_check > 0){
$row_check = mysqli_fetch_array($check);
if($row_check['NoGo'] == "0"){ 
//show NoGo Button Clubs
echo "<a name=\"$row[eventID]\" club_id_attr=\"$row[club_id]\" subcategory_id_attr=\"$row[subcategory]\" nogo_attr=\"1\" class=\"btn btn-default RSVPButton\" kid_id_attr=\"$kid_id\" adult_id_attr=\"$adult_id\" style=\"background: transparent; color: black\" role=\"button\"><span class=\"fal fa-user-times fa-lg\" aria-hidden=\"true\"></span><br><small class='smalltext'>annuleer</small></a>";
}
if($row_check['NoGo'] != "0"){ 
//show NoGo Button Clubs
?>
<div class="btn-group" role="group" style="position: initial;">
						  <button class="btn btn-default dropdown-toggle default-button-transparent black" type="button" id="dropdownMenu<?php echo $row['club_id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						    <span class="fal fa-user-check fa-lg" aria-hidden="true"></span>
						    <span class="caret" title="Toggle dropdown menu"></span>
							<br><small class="smalltext">kies</small>
						  </button>
<ul class="dropdown-menu" style="width: 100%;" aria-labelledby="dropdownMenu<?php echo $row['club_id'] ?>">
							<?php if($limit_set == 1 && $available_places <= 0){ 
								?>
								<li><a role="button" style="color:gray"><span class="fal fa-user-check pull-right" style="color:gray"></span>
							    <?php echo $max_limit_notif ?>
								</a></li>
								<?php
							}
							else { ?>
						    <li><a name="<?php echo $row['eventID'] ?>" id="<?php echo $row['club_id'] ?>" adult_id_attr="<?php echo $adult_id ?>" kid_id_attr="<?php echo $kid_id ?>" club_id_attr="<?php echo $row['club_id'] ?>" subcategory_id_attr="<?php echo $row['subcategory'] ?>" car_attr="0" nogo_attr="0" class="RSVPButton"><span class="fal fa-user-check pull-right"></span>
							    <?php if($kid_id <> 0) {
								    echo userValueKid($kid_id, "name") . " is aanwezig";
								    }
								    else {
									echo "Ik ben aanwezig"; 
									}
									?>
							</a></li>
						    <li><a name="<?php echo $row['eventID'] ?>" id="<?php echo $row['club_id'] ?>" adult_id_attr="<?php echo $adult_id ?>" kid_id_attr="<?php echo $kid_id ?>" club_id_attr="<?php echo $row['club_id'] ?>" subcategory_id_attr="<?php echo $row['subcategory'] ?>" car_attr="1" nogo_attr="0" class="RSVPButton"><span class="fal fa-car pull-right"></span>
							    <?php if($kid_id <> 0) {
								    echo userValueKid($kid_id, "name") . " is aanwezig en kan carpoolen";
								    }
								    else {
									echo "Ik ben aanwezig en kan carpoolen"; 
									}
									?>
								</a></li>
								<?php } ?>
								
						  </ul>
</div>
<?php
}
}
else {
//show Go Button Clubs
?>
<div class="btn-group" role="group" style="position: initial;">
  <button class="btn btn-default dropdown-toggle default-button-transparent black" type="button" id="dropdownMenu<?php echo $row['club_id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
    <span class="fal fa-user-check fa-lg" aria-hidden="true"></span>
    <span class="caret" title="Toggle dropdown menu"></span>
	<br><small class="smalltext">kies</small>
  </button>
 
  <ul class="dropdown-menu" style="width: 100%; position: absolute;" aria-labelledby="dropdownMenu<?php echo $row['club_id'] ?>">
    <?php if($limit_set == 1 && $available_places <= 0){ 
		?>
		<li><a role="button" style="color:gray"><span class="fal fa-user-check pull-right" style="color:gray"></span>
	    <?php echo $max_limit_notif ?>
		</a></li>
		<?php
	}
	else { ?>
    <li><a name="<?php echo $row['eventID'] ?>" id="<?php echo $row['club_id'] ?>" adult_id_attr="<?php echo $adult_id ?>" kid_id_attr="<?php echo $kid_id ?>" club_id_attr="<?php echo $row['club_id'] ?>" subcategory_id_attr="<?php echo $row['subcategory'] ?>" car_attr="0" nogo_attr="0" class="RSVPButton"><span class="fal fa-user-check pull-right"></span>
	    <?php if($kid_id <> 0) {
		    echo userValueKid($kid_id, "name") . " is aanwezig";
		    }
		    else {
			echo "Ik ben aanwezig"; 
			}
			?></a></li>
    <li><a name="<?php echo $row['eventID'] ?>" id="<?php echo $row['club_id'] ?>" adult_id_attr="<?php echo $adult_id ?>" kid_id_attr="<?php echo $kid_id ?>" club_id_attr="<?php echo $row['club_id'] ?>" subcategory_id_attr="<?php echo $row['subcategory'] ?>" car_attr="1" nogo_attr="0" class="RSVPButton"><span class="fal fa-car pull-right"></span>
	    <?php if($kid_id <> 0) {
		    echo userValueKid($kid_id, "name") . " is aanwezig en kan carpoolen";
		    }
		    else {
			echo "Ik ben aanwezig en kan carpoolen"; 
			}
			?>
	</a></li>
    <li><a name="<?php echo $row['eventID'] ?>" id="<?php echo $row['club_id'] ?>" adult_id_attr="<?php echo $adult_id ?>" kid_id_attr="<?php echo $kid_id ?>" club_id_attr="<?php echo $row['club_id'] ?>" subcategory_id_attr="<?php echo $row['subcategory'] ?>" car_attr="0" nogo_attr="1" class="RSVPButton"><span class="fal fa-user-times pull-right"></span>
	    <?php if($kid_id <> 0) {
		    echo userValueKid($kid_id, "name") . " is niet aanwezig";
		    }
		    else {
			echo "Ik ben niet aanwezig"; 
			}
			?></a></li>
			<?php } ?>
  </ul>
</div>
<?php
}
}

//display button RSVP Clubs
function showButtonRSVPClubsManual($manual_kid_id,$row) {
global $con;

//check for max_limit in RSVP_settings
$limit_set = 0;
$check_limit = mysqli_query($con, "SELECT * FROM RSVP_settings WHERE event_id = $row[eventID] AND inactive = 0");
if(mysqli_num_rows($check_limit) > 0){
	$limit_set = 1;
	$row_limit = mysqli_fetch_array($check_limit);
	
	$check = mysqli_query($con,"SELECT RSVP.* 
	FROM RSVP
	JOIN kids ON RSVP.kid_id = kids.id
	WHERE event_id = $row[eventID]
	AND NoGo = 0
	UNION
	SELECT RSVP.*
	FROM RSVP
	JOIN users ON RSVP.parent_id = users.id
	WHERE event_id = $row[eventID]
	AND NoGo = 0
	UNION
	SELECT RSVP.*
	FROM RSVP
	JOIN kids_clubs_manual ON RSVP.manual_kid_id = kids_clubs_manual.id
	WHERE event_id = $row[eventID]
	AND NoGo = 0");
	$num_rows_check=mysqli_num_rows($check);

	$available_places = $row_limit['max_limit'] - $num_rows_check;
	$max_limit_notif = "";
	if($available_places <= 0){
		$max_limit_notif = "Inschrijven niet meer mogelijk.";
	}
}

$check = mysqli_query($con,"SELECT * FROM RSVP WHERE manual_kid_id = $manual_kid_id AND event_id = $row[eventID]");
$num_rows_check=mysqli_num_rows($check);
if($num_rows_check > 0){
	$row_check = mysqli_fetch_array($check);
	if($row_check['NoGo'] == "0") {
	//show NoGo Button Clubs
	echo "<a name=\"$row[eventID]\" id=\"$row[friend_id_output]\" class=\"btn btn-default NoGoButtonClubs btn-sm default-button-transparent\" role=\"button\"><span class=\"fal fa-user-times fa-lg\" aria-hidden=\"true\"></span> Ik kan er toch niet zijn</a>";
	}
	if($row_check['NoGo'] != "0") {
    if($limit_set == 1 && $available_places <= 0){ 
	echo "<a class=\"btn btn-default btn-sm default-button-transparent\" role=\"button\"><span class=\"fal fa-times fa-lg\" aria-hidden=\"true\"></span> " . $max_limit_notif . "</a>";
	}
	else { 
	echo "<a name=\"$row[eventID]\" id=\"$row[friend_id_output]\" class=\"btn btn-default GoButtonClubs btn-sm default-button-transparent\" role=\"button\"><span class=\"fal fa-user-check fa-lg\" aria-hidden=\"true\"></span> Ik ben toch aanwezig</a>";	
	}
	}
}
else {
//show Go Button Clubs
    if($limit_set == 1 && $available_places <= 0){ 
	echo "<a class=\"btn btn-default btn-sm default-button-transparent\" role=\"button\"><span class=\"fal fa-times fa-lg\" aria-hidden=\"true\"></span> " . $max_limit_notif . "</a>";
	}
	else { 
echo "<a name=\"$row[eventID]\" id=\"$row[friend_id_output]\" class=\"btn btn-default GoButtonClubs btn-sm default-button-transparent\" role=\"button\"><span class=\"fal fa-user-check fa-lg\" aria-hidden=\"true\"></span> Ik ben aanwezig</a>";
}
//show NoGo Button Clubs
echo "<a name=\"$row[eventID]\" id=\"$row[friend_id_output]\" class=\"btn btn-default NoGoButtonClubs btn-sm default-button-transparent\" role=\"button\"><span class=\"fal fa-user-times fa-lg\" aria-hidden=\"true\"></span> Ik ben niet aanwezig</a>";
}
}

//Function postal codes around another postal code
function postalRadius($postal,$radius) {
global $con;
//query for coordinates of provided ZIP Code
	$rs = mysqli_query($con,"SELECT postal.*, postalhead.* FROM postal JOIN postalhead ON postal.zip_code = postalhead.postcode WHERE zip_code = '$postal' ORDER BY (city LIKE hoofdgemeente) DESC LIMIT 1");
	if(mysqli_num_rows($rs) == 0) {
			//echo "<p><strong>Gelieve je postcode aan te passen</p>\n";	
		}
		else {
			//if found, set variables
			$row = mysqli_fetch_array($rs);
			$lat1 = $row['latitude'];
			$lon1 = $row['longitude'];
			$d = $radius/1.6; //distance in miles around center postal
			$r = 3959;
			
			//compute max and min latitudes / longitudes for search square
			$latN = str_replace(',', '.', rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(0))))) ;
			$latS = str_replace(',', '.', rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(180)))));
			$lonE = str_replace(',', '.', rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(90)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN)))));
			$lonW = str_replace(',', '.', rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(270)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN)))));
			
			//find all coordinates within the search square's area
			//exclude the starting point and any empty city values
			$query = mysqli_query($con, "SELECT *, (((acos(sin((".$lat1."*pi()/180)) * sin((`latitude`*pi()/180))+cos((".$lat1."*pi()/180)) * cos((`latitude`*pi()/180)) * cos(((".$lon1."- `longitude`)*pi()/180))))*180/pi())*60*1.1515) as distance FROM postal WHERE zip_code = $postal OR ((latitude <= $latN AND latitude >= $latS AND longitude <= $lonE AND longitude >= $lonW) AND (latitude != $lat1 AND longitude != $lon1)) AND city != '' GROUP BY zip_code ORDER BY distance ASC");
			

				while($data = mysqli_fetch_array($query)) {
				$zipcodes[] = $data['zip_code'];
				}
				$zipcodes_result = implode(", ", $zipcodes); 
				return $zipcodes_result;
	}
}

//Function postal codes deelgemeentes van hoofgemeente.
function postalCitySubmunicipality($postal) {
	global $con;
	$query = mysqli_query($con, "SELECT postcode FROM `postalhead` WHERE postcode_hoofdgemeente = '$postal' OR postcode = '$postal'
	UNION
	SELECT postcode_hoofdgemeente AS postcode FROM postalhead WHERE postcode = '$postal'
	GROUP BY postcode");
	if(mysqli_num_rows($query) > 0){
	while($data = mysqli_fetch_array($query)) {
	$zipcodes[] = '' . $data['postcode'] . '';
	}
	$zipcodes_result = implode(", ", $zipcodes); 
	return $zipcodes_result;
	}
	else {
		return $postal;
	}
}


//function show city associated with postal code
function postalCity($row) {
	$postal = $row['postal'];
	if($postal <> ''){ 
	global $con;
	$city = mysqli_query($con,"SELECT * FROM postalhead WHERE postcode = '$row[postal]'");
	if(mysqli_num_rows($city) > 0){ 
	$row_city = mysqli_fetch_array($city);
	return $row_city['postcode'] . " " . $row_city['hoofdgemeente'];
	}
	else {
		return "<font color=red>Je hebt geen postcode opgegeven!</font>";
	}
	}
	else {
		return "<font color=red>Je hebt geen postcode opgegeven!</font>";
	}
}

//function show relationship parent
function showRelationship($parent_id,$kid_id, $accolade = true) {
	global $con;
	if($accolade == true){
		$open = "(";
		$close = ")";
	}
	else {
		$open = "";
		$close = "";
	}
	$relation = mysqli_query($con,"SELECT relation_id FROM relations WHERE user_id = $parent_id AND kid_id = $kid_id");
	if(mysqli_num_rows($relation) > 0){ 
	$row_relation = mysqli_fetch_array($relation);
		if($row_relation['relation_id'] == 1){
			return $open . "mama" . $close;
		}
		if($row_relation['relation_id'] == 2){
			return $open . "papa" . $close;
		}
		if($row_relation['relation_id'] == 3){
			return $open . "oma" . $close;
		}
		if($row_relation['relation_id'] == 4){
			return $open . "opa" . $close;
		}
		if($row_relation['relation_id'] == 5){
			return $open . "plusmama" . $close;
		}
		if($row_relation['relation_id'] == 6){
			return $open . "pluspapa" . $close;
		}
		if($row_relation['relation_id'] == 7){
			return $open . "pleegmoeder" . $close;
		}
		if($row_relation['relation_id'] == 8){
			return $open . "pleegvader" . $close;
		}
		if($row_relation['relation_id'] == 9){
			return $open . "zus" . $close;
		}
		if($row_relation['relation_id'] == 10){
			return $open . "broer" . $close;
		}
		if($row_relation['relation_id'] == 11){
			return $open . "tante" . $close;
		}
		if($row_relation['relation_id'] == 12){
			return $open . "nonkel" . $close;
		}
		if($row_relation['relation_id'] == 13){
			return $open . "nicht" . $close;
		}
		if($row_relation['relation_id'] == 14){
			return $open . "neef" . $close;
		}
		if($row_relation['relation_id'] == 15){
			return $open . "andere relatie" . $close;
		}
	}
	else {
		return false;
	}
}

//function show relationship parent
function FetchRelationId($parent_id,$kid_id) {
	global $con;
	$relation = mysqli_query($con,"SELECT relation_id FROM relations WHERE user_id = $parent_id AND kid_id = $kid_id");
	if(mysqli_num_rows($relation) > 0){ 
	$row_relation = mysqli_fetch_array($relation);
	return $row_relation['relation_id'];
}
}

//function show parent info
function showParent($row) {
	global $con;
	if(isset($row['friend_id_output']))
	{
	$member=$row['friend_id_output'];
	}
	elseif(isset($row['member'])){
		$member = $row['member'];
	}
	elseif(isset($row['kid_id'])){
		$member = $row['kid_id'];
	}
	$parents = mysqli_query($con,"SELECT * FROM users WHERE id = $row[parent] OR id IN(SELECT slave_id FROM users_share WHERE master_id = $row[parent] AND confirmed = 1)");
	while($row_parents = mysqli_fetch_array($parents)) {
		echo "<i class=\"fal fa-user-friends\"></i>&nbsp;&nbsp;$row_parents[username] <i><font color=gray>" . showRelationship($row_parents['id'],$member) . "</font></i><br>";
	}
}

function showSuggestions($value){
	if(($value <> "") && ($value <> "no")){
		echo "<span class='fal fa-download fa-fw'></span> Lid wil graag ingedeeld worden in: ";
	$suggestions = explode(",", $value);
	foreach($suggestions AS $sug_sub){
		if($sug_sub == 0){
			$ex[] = "Andere subgroep";
		}
		else { 
			$ex[] = userValueSubcategory($sug_sub, "club", "cat_name");
		}
	}
	echo implode(", ", $ex);
	}
	else {
		return false;
	}
}


// Function to send a mail
function userSendMail6($from, $to, $subject, $message) {
	global $mailpass;
	global $mailhost;
	global $mailusername;
	global $mailfromaddress;
	global $mailfromname;
	
	require_once 'plugins/PHPMailer-master/src/Exception.php';
	require_once 'plugins/PHPMailer-master/src/PHPMailer.php';
	require_once 'plugins/PHPMailer-master/src/SMTP.php';
	
	$mail = new PHPMailer\PHPMailer\PHPMailer(true);
	//Create a new PHPMailer instance
	//Tell PHPMailer to use SMTP
	$mail->isSMTP();
	$mail->CharSet = 'UTF-8';
	$mail->SMTPOptions = array(
            	'ssl' => array(
                	'verify_peer' => false,
                	'verify_peer_name' => false,
                	'allow_self_signed' => true
            	)
        	);
	
	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;
	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';
	//Set the hostname of the mail server
	$mail->Host = $mailhost;
	//Set the SMTP port number - likely to be 25, 465 or 587
	$mail->Port = 587;
	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;
	//Username to use for SMTP authentication
	$mail->Username = $mailusername;
	//Password to use for SMTP authentication
	$mail->Password = $mailpass;
	//Set who the message is to be sent from
	$mail->setFrom($mailfromaddress,$mailfromname);
	
	try {
	//Set an alternative reply-to address
	$mail->addReplyTo($from);
	//Set who the message is to be sent to
	$mail->addAddress($to);
	//Set the subject line
	$mail->WordWrap = 50;
	$mail->isHTML(true); // Enable HTML
	
	$mail->Subject = $subject;
	$mail->MsgHTML($message);
	
	
	if (!$mail->send()) {
    	return false;
	} else {
    	return true;
	}
	
	$mail->clearAddresses();
	}
	catch (Exception $e)
			{
		   	$error_code = $e->errorMessage();
		  	
			}
			catch (\Exception $e)
			{
		   	echo $e->getMessage();
			}	
}
	
function CityName($postal) {
	global $con;
	if($postal <> ''){ 
	$sql_city_name = mysqli_query($con,"SELECT * FROM postalhead WHERE postcode = $postal");
	$count = mysqli_num_rows($sql_city_name);
	$row_city_name = mysqli_fetch_array($sql_city_name);
	if($count > 1){
		return $row_city_name['hoofdgemeente'];
	}
	else {
		return $row_city_name['gemeente'];
	}
	}
	else {
		return false;
	}
}


function CountLatestDeals($parent_id) {
	global $con;
	global $pass;
	
	//generate list of postal codes and province ids associated with this account (parents/kids)
	$get_postal_family = mysqli_query($con,"SELECT AES_DECRYPT(postal, SHA1('$pass')) AS postal, postalhead.*
	FROM kids 
	JOIN postalhead ON AES_DECRYPT(postal, SHA1('$pass')) = postalhead.postcode
	WHERE parent = $parent_id OR
	parent IN(SELECT master_id FROM users_share WHERE slave_id = $parent_id AND confirmed = 1)
	GROUP BY postalhead.postcode
	UNION
	SELECT users.postal, postalhead.* 
	FROM users 
	JOIN postalhead ON users.postal = postalhead.postcode
	WHERE users.id = $parent_id
	OR users.id IN(SELECT master_id FROM users_share WHERE slave_id = $parent_id AND confirmed = 1)
	OR users.id IN(SELECT slave_id FROM users_share WHERE master_id = $parent_id AND confirmed = 1)
	GROUP BY postalhead.postcode");
	
	//if user has kids or has an adult profile: postal is filled in: he can see local deals
	if(mysqli_num_rows($get_postal_family) > 0){
	while($row_postal = mysqli_fetch_array($get_postal_family)) {
	$postal_web_zones[] = "inactive = 0 AND FIND_IN_SET('$row_postal[postal]', zone_postal)";
	$postal_local[] = "inactive = 0 AND status = 1 AND finished = 1 AND FIND_IN_SET('$row_postal[postal]', playday_plus_local.postal)";
	$postal_local_zones[] = "FIND_IN_SET('$row_postal[postal]', zone_postal)";
	$province[] = $row_postal['provincie_id'];
	}
	$province_list = implode(", ", $province);
	$postal_list_web_zones = implode(" OR ", $postal_web_zones);
	$postal_list_local = implode(" OR ", $postal_local);
	$postal_list_local_zones = implode(" OR ", $postal_local_zones);
	
	//query
	$sql = mysqli_query($con,"SELECT 
	playday_plus.id FROM playday_plus
	JOIN playday_plus_zones ON playday_plus.zone = playday_plus_zones.zone_id 
	WHERE inactive = 0
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND playday_plus.zone = 0
	OR inactive = 0 
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND playday_plus.zone IN ($province_list)
	OR inactive = 0
	AND DATE(now()) <= DATE(sale_code_temp_date) 
	AND ($postal_list_web_zones)
	UNION
	SELECT 
	playday_plus_local.id FROM playday_plus_local
	JOIN playday_plus_zones ON playday_plus_local.zone = playday_plus_zones.zone_id 
	WHERE playday_plus_local.zone = 99
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND ($postal_list_local)
	OR 
	inactive = 0 AND status = 1 AND finished = 1
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND playday_plus_local.zone IN ($province_list)
	OR
	inactive = 0 AND status = 1 AND finished = 1
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND ($postal_list_local_zones)");
	
	if(!$sql){
		error_log("Error description CountLatestDeals: SELECT 
	playday_plus.id FROM playday_plus
	JOIN playday_plus_zones ON playday_plus.zone = playday_plus_zones.zone_id 
	WHERE inactive = 0
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND playday_plus.zone = 0
	OR inactive = 0 
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND playday_plus.zone IN ($province_list)
	OR inactive = 0
	AND DATE(now()) <= DATE(sale_code_temp_date) 
	AND ($postal_list_web_zones)
	UNION
	SELECT 
	playday_plus_local.id FROM playday_plus_local
	JOIN playday_plus_zones ON playday_plus_local.zone = playday_plus_zones.zone_id 
	WHERE playday_plus_local.zone = 99
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND ($postal_list_local)
	OR 
	inactive = 0 AND status = 1 AND finished = 1
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND playday_plus_local.zone IN ($province_list)
	OR
	inactive = 0 AND status = 1 AND finished = 1
	AND DATE(now()) <= DATE(sale_code_temp_date)
	AND ($postal_list_local_zones) " . mysqli_error($con), 0);
	}
	
	$cnt = mysqli_num_rows($sql);
	return $cnt;
	}
	else {
		return "";
	}
}

function CountUserInputsClubs() {
	global $con;
	$check = mysqli_query($con, "SELECT id FROM users_input");
	$cnt_check = mysqli_num_rows($check);
	return $cnt_check;
}

function isLinkedClub($adult_id) {
	global $con;
	$club_share = mysqli_query($con,"SELECT clubs_share.*, users.avatar AS club_default_avatar 
			FROM clubs_share
			JOIN users ON clubs_share.club_id = users.club_id
			WHERE user_id = $adult_id
			GROUP BY clubs_share.club_id");
			$num_clubs_share=mysqli_num_rows($club_share);
			if($num_clubs_share > 0){
				return true;
				}
				else {
					return false;
				}
}

function isLinkedAccount($adult_id){
	global $con;
	$linked = mysqli_query($con,"SELECT * FROM users_share WHERE (master_id = $adult_id OR slave_id = $adult_id) AND confirmed = '1'
								AND master_id IN (SELECT id FROM users) AND slave_id IN (SELECT id FROM users)");
			if(mysqli_num_rows($linked) > 0){
				return true;
				}
				else {
					return false;
				}
}

function isMemberClub($adult_id, $kid_id = 0, $classified = false){
	global $con;
	if($classified == true){
		$string = "AND subcategory <> 0";
	}
	if($classified == false){
		$string = "";
	}
	if($kid_id == 0){
	$memberclubs = mysqli_query($con,"SELECT * FROM parents_clubs WHERE parent_id IN($adult_id) $string");
			$num_memberclubs=mysqli_num_rows($memberclubs);
			if($num_memberclubs > 0){
				return true;
				}
				else {
					return false;
				}
	}
	if($kid_id <> 0){
	$memberclubs = mysqli_query($con,"SELECT * FROM kids_clubs WHERE kid_id IN ($kid_id) $string");
			$num_memberclubs=mysqli_num_rows($memberclubs);
			if($num_memberclubs > 0){
				return true;
				}
				else {
					return false;
				}
	}
	
}

function isMemberClubJoined($adult_id){
	global $con;
	$memberclubs = mysqli_query($con,"SELECT * FROM parents_clubs WHERE parent_id = $adult_id
	OR parent_id IN(SELECT master_id FROM users_share WHERE slave_id = $adult_id AND confirmed = 1)
	OR parent_id IN(SELECT slave_id FROM users_share WHERE master_id = $adult_id AND confirmed = 1)");
			$num_memberclubs=mysqli_num_rows($memberclubs);
			if($num_memberclubs > 0){
				return true;
				}
				else {
					return false;
				}
}

function hasUploadedDocument($user_id,$document_id, $superuser_id, $superuser) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	//first check to which subcategories shared user is linked to
	$share = mysqli_query($con,"SELECT * 
			FROM " . $superuser . "s_share
			WHERE user_id = $user_id
			AND " . $superuser . "_id = $superuser_id");

			$num_share=mysqli_num_rows($share);
			if($num_share > 0){
			while($row_share = mysqli_fetch_array($share)) {
			$subcategories[] = $row_share['subcategory_id'];
			}
			$linked_subcategories = implode(", ", $subcategories);
			}
			else {
			$linked_subcategories = "0";
			}
	//then check if this user has made a 'ForAll' event or a 'ForSome' event. 
	$made = mysqli_query($con,"SELECT * 
			FROM " . $superuser . "s_documents
			WHERE id = $document_id
			AND subcategory IN($linked_subcategories)
			AND " . $superuser . "_id = $superuser_id");
			
	if(mysqli_num_rows($made) > 0){
		return true;
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
	
}

function hasMadeEvent($user_id,$event_id, $superuser_id, $superuser) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	//first check to which subcategories shared user is linked to
	$share = mysqli_query($con,"SELECT * 
			FROM " . $superuser . "s_share
			WHERE user_id = $user_id
			AND " . $superuser . "_id = $superuser_id");

			$num_share=mysqli_num_rows($share);
			if($num_share > 0){
			while($row_share = mysqli_fetch_array($share)) {
			$subcategories[] = $row_share['subcategory_id'];
			}
			$linked_subcategories = implode(", ", $subcategories);
			}
			else {
			$linked_subcategories = "0";
			}
	//then check if this user has made a 'ForAll' event or a 'ForSome' event. 
	$made = mysqli_query($con,"SELECT * 
			FROM calendar
			WHERE id = $event_id
			AND subcategory IN($linked_subcategories)
			AND " . $superuser . "_id = $superuser_id");

	if(mysqli_num_rows($made) > 0){
		return true;
	}
	else {
		return false;
	}
   }
   else {
	   return false;
   }
}

function NoKidsAdded($adult_id){
	global $con;
	$check_empty = mysqli_query($con,"SELECT id FROM kids 
	WHERE parent = $adult_id 
	OR 
	parent IN(SELECT master_id FROM users_share WHERE slave_id = $adult_id AND confirmed = 1)");
			$numrows_empty = mysqli_num_rows($check_empty);
			if($numrows_empty == 0)
			{ 
				return true;
				}
				else {
					return false;
				}
}

function KidsFromParentId($parent_id){
	global $con;
	$get_ids_kids = mysqli_query($con,"SELECT id FROM kids WHERE 
		parent = $parent_id OR
		parent IN(SELECT master_id FROM users_share WHERE slave_id = $parent_id AND confirmed = 1) ORDER BY id ASC");
		if(mysqli_num_rows($get_ids_kids) > 0){ 
		  $id_kids=array();
		  while($row_kids = mysqli_fetch_array($get_ids_kids)){
			$id_kids[] = $row_kids['id'];
			}
			//output kids ids in a list
			return implode(",", $id_kids);
			}
			else { 
	    // list is empty.
     	return 0;
		}
}

function JoinedParentsFromParentId($parent_id, $superuser_id = 0, $superuser = "club", $subcategory_id = 0, $type = 0){
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$get_ids_parents = mysqli_query($con,"SELECT id FROM users WHERE 
		(id IN($parent_id) OR
		id IN(SELECT master_id FROM users_share WHERE slave_id IN($parent_id) AND confirmed = 1)
		OR
		id IN(SELECT slave_id FROM users_share WHERE master_id IN($parent_id) AND confirmed = 1))
		AND id NOT IN (SELECT user_id FROM mails_usersettings WHERE " . $superuser . "_id = $superuser_id AND subcategory_id IN($subcategory_id) AND type = $type)
		ORDER BY FIELD(id, $parent_id) DESC");
		if(mysqli_num_rows($get_ids_parents) > 0){ 
		  $id_parents=array();
		  while($row_parents = mysqli_fetch_array($get_ids_parents)){
			$id_parents[] = $row_parents['id'];
			}
			//output in a list
			return implode(",", $id_parents);
			}
			else { 
	    // list is empty.
     	return 0;
		}
	}
	else {
		return 0;
	}
}

//Get parent ids from kids
function getParentIdsFromKidIds($kids_list, $superuser_id = 0, $superuser = "club", $subcategory_id = 0, $type = 0) {
	global $con;
	//get list kids
	$fetch_kids = mysqli_query($con,"SELECT parent AS parent_id FROM kids
  	WHERE id IN ($kids_list)");
	
	if(mysqli_num_rows($fetch_kids) > 0){
	$id_parents_master=array();
	while($row_kids = mysqli_fetch_array($fetch_kids)){
	$id_parents_master[] = $row_kids['parent_id'];
	}
	$parent_list_master = implode(",", $id_parents_master);
	
	//check if kids parents are in joined accounts and if parent want to participate
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$fetch_parents = mysqli_query($con,"SELECT id FROM users WHERE (id IN (SELECT slave_id FROM users_share WHERE master_id IN ($parent_list_master) AND confirmed = '1') OR id IN ($parent_list_master)) AND id NOT IN (SELECT user_id FROM mails_usersettings WHERE " . $superuser . "_id = $superuser_id AND subcategory_id IN($subcategory_id) AND type = $type)");
	$id_parents_all=array();
	while($row_parents = mysqli_fetch_array($fetch_parents)){
	$id_parents_all[] = $row_parents['id'];
	}
	return implode(",", $id_parents_all);
	}
	else {
	return "0";
	}
	}
	else {
		return 0;
	}
}

//count members subgroup
function CountMembersSubcategory($superuser_id, $superuser,$subcategory_id) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT id FROM kids_" . $superuser . "s 
	WHERE " . $superuser . "_id = $superuser_id
	AND subcategory IN($subcategory_id)
	AND kid_id NOT IN (SELECT id FROM kids WHERE inactive = 1)
	UNION
	SELECT id FROM parents_" . $superuser . "s 
	WHERE " . $superuser . "_id = $superuser_id AND subcategory IN($subcategory_id)
	UNION
	SELECT id FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id = $superuser_id AND subcategory IN($subcategory_id)");
	$cnt = mysqli_num_rows($sql);
	return $cnt;
	}
	else {
		return 0;
	}
}

//count members
function CountMembersSuper($superuser_id, $superuser) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT kid_id AS kid_id, 0 AS manual_id, 0 AS adult_id FROM kids_" . $superuser . "s 
	WHERE " . $superuser . "_id = $superuser_id
	AND subcategory <> 0
	AND kid_id NOT IN (SELECT id FROM kids WHERE inactive = 1)
	UNION
	SELECT 0 AS kid_id, 0 AS manual_id, parent_id AS adult_id FROM parents_" . $superuser . "s 
	WHERE " . $superuser . "_id = $superuser_id 
	AND subcategory <> 0
	UNION
	SELECT 0 AS kid_id, id AS manual_id, 0 AS parent_id FROM kids_" . $superuser . "s_manual 
	WHERE " . $superuser . "_id = $superuser_id 
	AND subcategory <> 0");
	$cnt = mysqli_num_rows($sql);
	return $cnt;
	}
	else {
		return 0;
	}
}

//count answers Survey
function CountSurveyAnswers($eventID){
	global $con;
	$sql = mysqli_query($con,"SELECT survey_answers.*, survey_questions.* 
	FROM survey_answers 
	JOIN survey_questions ON survey_questions.id = survey_answers.question_id
	WHERE (survey_questions.event_id = $eventID OR FIND_IN_SET($eventID, event_id_list)) AND survey_questions.archive = 0 GROUP BY survey_answers.parent_id");
	$cnt = mysqli_num_rows($sql);
	if($cnt == 0){
		return $cnt;
	}
	else { 
return $cnt;
}
}

function CountNewSurvey($kid_id, $adult_id, $superuser_id, $superuser, $subcategory_id) {
	global $con;
	$today = date('Y-m-d');
	if($kid_id <> 0){
		$string_sql = "kid_id = $kid_id";
		$string_3 = "AND FIND_IN_SET($kid_id,ForKids)";
	}
	if($kid_id == 0 && $adult_id <> 0){
		$string_sql = "parent_id = $adult_id";
		$string_3 = "AND FIND_IN_SET($adult_id,ForAdults)";
	}
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT * FROM survey_questions
	WHERE
	ForAll = 0 
	AND " . $superuser . "_id = $superuser_id 
	AND subcategory_id = $subcategory_id
	AND enddate >= CURDATE()
	AND NOT EXISTS (SELECT * FROM survey_answers WHERE $string_sql AND survey_answers.question_id = survey_questions.id AND archive = 0)
	AND archive = 0
	AND done = 1
	OR
	ForAll = 1 
	AND " . $superuser . "_id = $superuser_id 
	AND enddate >= CURDATE()
	AND NOT EXISTS (SELECT * FROM survey_answers WHERE $string_sql AND survey_answers.question_id = survey_questions.id AND archive = 0)
	AND archive = 0
	AND done = 1
	OR
	ForAll = 2
	AND " . $superuser . "_id = $superuser_id
	AND FIND_IN_SET($subcategory_id,ForSome)
	AND enddate >= CURDATE()
	AND NOT EXISTS (SELECT * FROM survey_answers WHERE $string_sql AND survey_answers.question_id = survey_questions.id AND archive = 0)
	AND archive = 0
	AND done = 1
	OR
	ForAll = 3
	AND " . $superuser . "_id = $superuser_id
	$string_3
	AND enddate >= CURDATE()
	AND NOT EXISTS (SELECT * FROM survey_answers WHERE $string_sql AND survey_answers.question_id = survey_questions.id AND archive = 0)
	AND archive = 0
	AND done = 1");
	
	if(mysqli_num_rows($sql) > 0){	
		return true;
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
}


//show new survey
function ShowSurvey($kid_id, $adult_id, $eventID){
	global $con;
	$sql_question = mysqli_query($con,"SELECT * FROM survey_questions WHERE (event_id = $eventID OR FIND_IN_SET($eventID, event_id_list)) AND done = 1 AND archive = 0");
	//there is a survey for this eventID
	if(mysqli_num_rows($sql_question) > 0){ 
	while($row_question = mysqli_fetch_array($sql_question)){ 
	if(($row_question['enddate'] == "0000-00-00") || ($row_question['enddate'] >= date('Y-m-d'))){
	
	if($kid_id <> 0){ 
	$sql_answer = mysqli_query($con,"SELECT * FROM survey_answers 
	WHERE question_id = $row_question[id]
	AND kid_id = $kid_id LIMIT 1");
	}
	if($kid_id == 0){
	$sql_answer = mysqli_query($con,"SELECT * FROM survey_answers 
	WHERE question_id = $row_question[id]
	AND parent_id = $adult_id AND kid_id = 0 LIMIT 1");
	}
	//you already gave an answer
	if(mysqli_num_rows($sql_answer) > 0){
	$row_answer = mysqli_fetch_array($sql_answer);
	if($row_answer['parent_id'] == $adult_id){ 
		$text = "Je hebt het antwoordstrookje beantwoord. Klik om te bewerken";
	}
	else {
		$text = "Het antwoordstrookje is ingevuld door " . userValue($row_answer['parent_id'], "username") . ". Klik om te bewerken";
	}
	echo "<tr><td><i class=\"fal fa-cut fa-fw\" aria-hidden=\"true\"></i></td><td>
	<a class=\"location OpenModalUser\" type_attr=\"modalstartsurvey\" chat_id_attr=\"$row_question[id]\" name=\"$eventID\" kid_id_attr=\"$kid_id\" parent_id_attr=\"$adult_id\">
	$text</a></td></tr>";
	}
	else{ 
	//you didn't give an answer 
	echo "<tr><td><i class=\"fal fa-cut fa-fw\" aria-hidden=\"true\"></i></td><td class=\"bg-color-bloodred\" style=\"border-radius: 5px\">
	<a class=\"none OpenModalUser\" type_attr=\"modalstartsurvey\" chat_id_attr=\"$row_question[id]\" name=\"$eventID\" kid_id_attr=\"$kid_id\" parent_id_attr=\"$adult_id\">
	<font color=white>Er is een antwoordstrookje om te beantwoorden. Klik om te starten</font></a></td></tr>";	
	
	}
	}
	else {
		echo "<tr><td><i class=\"fal fa-cut fa-fw\" aria-hidden=\"true\"></i></td><td>De einddatum van het antwoordstrookje is verstreken.</td></tr>";
	}
	//there isn't a survey for this eventID
	}
	}
	else {
		return false;
	}
}


//get facebook page superuser
function GetSocialWebMail($superuser_id,$superuser,$type,$subcategory_id) {
	global $con;
	global $full_path;
	$value = "id";
	$types = array("cat_facebook", "cat_website");
	if(in_array($type, $types)){
		$value = $type;
	}
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$get_social = mysqli_query($con,"SELECT $value FROM " . $superuser . "s_categories WHERE id IN($subcategory_id) AND " . $superuser . "_id = $superuser_id AND $value <> '' LIMIT 1");
	$num_get_social = mysqli_num_rows($get_social);
	if($num_get_social > 0){
	$row_social = mysqli_fetch_array($get_social); 
	if($type == "cat_facebook"){ 
	return "<a href=\"" . $row_social['cat_facebook'] . "\" target=\"_blank\"><img width=\"30\" src=\"$full_path/img/color-facebook-48.png\" alt=\"facebook\"/></a>";
	}
	if($type == "cat_website"){ 
	return "<a href=\"" . $row_social['cat_website'] . "\" target=\"_blank\"><img width=\"30\" src=\"$full_path/img/color-link-48.png\" alt=\"facebook\"/></a>";
	}
	}
	else {
	if($type == "cat_facebook"){ 
		if(userValueSuper($superuser_id,$superuser,"facebook") != ""){
		return "<a href=\"" . userValueSuper($superuser_id,$superuser,"facebook") . "\" target=\"_blank\"><img width=\"30\" src=\"$full_path/img/color-facebook-48.png\" alt=\"facebook\"/></a>";	
		}
		else {
			return "";
		}
	}
	if($type == "cat_website"){
		if(userValueSuper($superuser_id,$superuser,"website") != ""){
		return "<a href=\"" . userValueSuper($superuser_id,$superuser,"website") . "\" target=\"_blank\"><img width=\"30\" src=\"$full_path/img/color-link-48.png\" alt=\"facebook\"/></a>";	
		}
		else {
			return "";
		}
	}
	}
	}
	else {
		return "";
	}
}

function showTagsPlus($row) {
global $con;
$sql_tags = mysqli_query($con,"SELECT playday_plus_tags.*, playday_plus_tags_config.* 
							  FROM playday_plus_tags
							  JOIN playday_plus_tags_config ON playday_plus_tags.tag_id = playday_plus_tags_config.id
							  WHERE playday_plus_tags.plus_id = $row[product_id]");
							  $cnt_tags = mysqli_num_rows($sql_tags);
							  if($cnt_tags > 0){
							  while($row_tags = mysqli_fetch_array($sql_tags)){
								  $tags[] = $row_tags['tag_name'];  
								  }
							  
							  $tags_list = implode(", ", $tags);	
							  return $tags_list; 
							  }

}

//NEW VERSION 10/10/2020 Apple API Push sending_notification_requests_to_apns
function send_mobile_notification_request($user_mobile_info, $payload_info, $showresponse = 0){
	global $pem_file_location;
	global $pem_secret_key;
	global $apns_topic_name;
	global $api_key_android;
	global $logo_path;
	global $appname;
	
    //Default result
    $result = -1;
    //Change depending on where to send notifications - either production or development
    $pem_preference = "production";
    $user_device_type = $user_mobile_info['device'];
    $user_device_key = $user_mobile_info['token'];

	//device 1 = iOS
    if (($user_device_type == "1") && ($user_device_key != "")  && ($user_device_key != "null")) {

    $device_token   = $user_device_key;
    $pem_file       = $pem_file_location;
    $pem_secret     = $pem_secret_key;
    $apns_topic     = $apns_topic_name;

    $url = "https://api.push.apple.com/3/device/$device_token";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_info);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("apns-topic: $apns_topic"));
    curl_setopt($ch, CURLOPT_SSLCERT, $pem_file);
    curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pem_secret);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	if($showresponse == 1){
	
    //On successful response you should get true in the response and a status code of 200
    //A list of responses and status codes is available at 
    //https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/TheNotificationPayload.html#//apple_ref/doc/uid/TP40008194-CH107-SW1

    var_dump($response);
    var_dump($httpcode);
    }

    }
    //device type = Android
    else if (($user_device_type == "2") && ($user_device_key != "")  && ($user_device_key != "null")) {
		
		//IMPORTANT: BEFORE JUNE 2024 THIS MUST ME UPDATED TO https://firebase.google.com/docs/cloud-messaging/migrate-v1

        // API access key from Google API's Console
        if (!defined('API_ACCESS_KEY')) define('API_ACCESS_KEY', $api_key_android);
       
     
       $fields = array("to" => $user_device_key,
                    	"data" => array(
                                            "message"=>json_decode($payload_info)->aps->alert,
                                            "title"=>$appname,
                                            "pushUrl"=>json_decode($payload_info)->aps->pushUrl,
                                            "timestamp"=>date('Y-m-d G:i:s'),
                                            "image-url"=> $logo_path
							),

        );

        $headers = array
        (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch);
        curl_close($ch);
    }
    return $result > 0;
}


//Create json file to send to Apple/Google Servers with notification request and body
function create_payload_json($message, $pushUrl = null) {
	global $full_path;
	
	if($pushUrl == null){
		$pushUrl = $full_path;
	}
	else {
		$pushUrl = $full_path . "/" . $pushUrl;
	}
	
    //Badge icon to show at users ios app icon after receiving notification
    $badge = "1";
    $sound = 'default';

    $payload = array();
    $payload['aps'] = array('alert' => $message, 'badge' => intval($badge), 'sound' => $sound, 'pushUrl' => $pushUrl);
    return json_encode($payload);
    
$response = curl_exec($ch);
if ($response === false) {
    $error_msg = curl_error($ch);
    error_log("cURL Error: " . $error_msg); // Log de cURL-fout
}

$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($httpcode !== 200) {
    error_log("Error sending notification: HTTP Code " . $httpcode . " Response: " . $response);
}
    
    
}




//clubs get kid ids from 1 subcategory or whole club
function getKidIdsFromSuper($superuser_id, $superuser, $subcategory_id, $eventID = 0) {
	global $con;
	if($subcategory_id <> 0){
		$string = "AND subcategory IN ($subcategory_id)";
	}
	if($subcategory_id == 0){
		$string = "AND subcategory <> 0";
	}
	if($eventID <> 0){
		$string_RSVP = "AND kid_id NOT IN (SELECT kid_id FROM RSVP WHERE event_id = $eventID AND " . $superuser . "_id = $superuser_id AND NoGo = 1)";
	}
	else {
		$string_RSVP = "";
	}
	
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	//get list kid ids
	$fetch_ids = mysqli_query($con,"SELECT kid_id FROM kids_" . $superuser . "s
  	WHERE " . $superuser . "_id = $superuser_id 
  	AND kid_id NOT IN (SELECT id FROM kids WHERE inactive = 1)
  	$string
  	$string_RSVP
  	GROUP BY kid_id");
	
	if(mysqli_num_rows($fetch_ids) > 0){
	$id_kids=array();
	while($row_kids = mysqli_fetch_array($fetch_ids)){
	$id_kids[] = $row_kids['kid_id'];
	}
	return implode(",", $id_kids);
	}
	else {
	return "0";
	}
	}
	else {
		return "0";
	}
}

//clubs get adult ids from 1 subcategory
function getAdultIdsFromSuper($superuser_id, $superuser, $subcategory_id, $eventID = 0) {
	global $con;
	
	if($subcategory_id <> 0){
		$string = "AND subcategory IN ($subcategory_id)";
	}
	if($subcategory_id == 0){
		$string = "AND subcategory <> 0";
	}
	if($eventID <> 0){
		$string_RSVP = "AND parent_id NOT IN (SELECT parent_id FROM RSVP WHERE event_id = $eventID AND " . $superuser . "_id = $superuser_id AND NoGo = 1)";
	}
	else {
		$string_RSVP = "";
	}
	
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	
	//get list adult ids
	$fetch_ids = mysqli_query($con,"SELECT parent_id FROM parents_" . $superuser . "s
  	WHERE " . $superuser . "_id = $superuser_id 
  	$string
  	$string_RSVP
  	GROUP BY parent_id");
	
	if(mysqli_num_rows($fetch_ids) > 0){
	$id_adults=array();
	while($row_adults = mysqli_fetch_array($fetch_ids)){
	$id_adults[] = $row_adults['parent_id'];
	}
	return implode(",", $id_adults);
	}
	else {
	return "0";
	}
	}
	else {
		return "0";
	}
}

//clubs get adult ids from 1 subcategory
function getManualIdsFromSuper($superuser_id, $superuser, $subcategory_id) {
	global $con;
	
	if($subcategory_id <> 0){
		$string = "AND subcategory IN ($subcategory_id)";
	}
	if($subcategory_id == 0){
		$string = "AND subcategory <> 0";
	}
	
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	//get list manual ids
	$fetch_ids = mysqli_query($con,"SELECT id AS manual_kid_id FROM kids_" . $superuser . "s_manual
  	WHERE " . $superuser . "_id = $superuser_id 
  	$string
  	GROUP BY id");
	
	if(mysqli_num_rows($fetch_ids) > 0){
	$id_manual=array();
	while($row_manual = mysqli_fetch_array($fetch_ids)){
	$id_manual[] = $row_manual['manual_kid_id'];
	}
	return implode(",", $id_manual);
	}
	else {
	return "0";
	}
	}
	else {
		return "0";
	}
}


function getSubcategoryIdsFromSuperUser($superuser_id, $superuser) {
	global $con;
	//get list subcategories
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$fetch_ids = mysqli_query($con,"SELECT id FROM " . $superuser . "s_categories WHERE " . $superuser . "_id = $superuser_id");
	
	if(mysqli_num_rows($fetch_ids) > 0){
	$id_subcategories=array();
	while($row_subcategories = mysqli_fetch_array($fetch_ids)){
	$id_subcategories[] = $row_subcategories['id'];
	}
	return implode(",", $id_subcategories);
	}
	else {
	return "0";
	}
	}
	else {
		return false;
	}
}

//superuser get greeting mail from calendar
function getHeaderFooterMailSuper($superuser_id, $superuser, $subcategory_id, $type) {
	global $con;
	global $pass;
	$logged_in_user = userValue(null, "id");
	$value = "";
	if($type == "cat_contact_name"){
		$value = "AES_DECRYPT(" . $superuser . "s_contact.cat_contact_name_x, SHA1('$pass')) AS cat_contact_name";
	}
	if($type == "cat_contact_email"){
		$value = "AES_DECRYPT(" . $superuser . "s_contact.cat_contact_email_x, SHA1('$pass')) AS cat_contact_email";
	}
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT " . $superuser . "s_share.*, $value 
	FROM " . $superuser . "s_contact
	JOIN " . $superuser . "s_share ON " . $superuser . "s_share." . $superuser . "s_contact_id = " . $superuser . "s_contact.id
	WHERE " . $superuser . "s_contact.subcategory_id = $subcategory_id AND " . $superuser . "s_contact." . $superuser . "_id = $superuser_id 
	AND " . $superuser . "s_share.user_id = $logged_in_user
	AND " . $superuser . "s_share." . $superuser . "_user_id <> $logged_in_user
	AND AES_DECRYPT(" . $superuser . "s_contact.cat_contact_name_x, SHA1('$pass')) <> '' AND AES_DECRYPT(" . $superuser . "s_contact.cat_contact_email_x, SHA1('$pass')) <> ''");
	$numrows = mysqli_num_rows($sql);
	if($numrows > 0){ 
	$row = mysqli_fetch_array($sql);
	return $row[$type];
	}
	else {
		if($type == "cat_contact_name"){
		return userValueSuper($superuser_id,$superuser,"username"); //return main  name
	}
	if($type == "cat_contact_email"){
		return userValueSuper($superuser_id,$superuser,"email"); //return main  email
	}
	}
	}
	else {
		return false;
	}
}

//clubs get ids contact persons subcategory or whole club
function getIdsContactSuper($superuser_id, $superuser, $subcategory_id) {
	global $con;
	global $pass;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	//one or more subgroups, not whole club
	if($subcategory_id <> 0){
	//first check if a contact is joined to a user account
	$sql = mysqli_query($con,"SELECT users.id AS userId, " . $superuser . "s_share.* 
	FROM " . $superuser . "s_share
	JOIN users ON users.id = " . $superuser . "s_share.user_id
	WHERE " . $superuser . "s_share." . $superuser . "_id = $superuser_id AND " . $superuser . "s_share.subcategory_id IN($subcategory_id)");
	
	//no contact is entered or joined to a user account
	if(mysqli_num_rows($sql) < 1){
		$sql = mysqli_query($con, "SELECT id AS userId FROM users WHERE " . $superuser . "_id = $superuser_id");
	}
	}
	//$subcategory = 0 in function, so whole club
	else {
	//first check if a contact is joined to a user account
	$sql = mysqli_query($con,"SELECT users.id AS userId, " . $superuser . "s_share.* 
	FROM " . $superuser . "s_share
	JOIN users ON users.id = " . $superuser . "s_share.user_id
	WHERE " . $superuser . "s_share." . $superuser . "_id = $superuser_id");	
	
	//no contact is entered or joined to a user account
	if(mysqli_num_rows($sql) < 1){
		$sql = mysqli_query($con, "SELECT id AS userId FROM users WHERE " . $superuser . "_id = $superuser_id");
	}
	}
	$numrows = mysqli_num_rows($sql);
	if($numrows > 0){ 
	while($row_info = mysqli_fetch_array($sql)){
	$id_contact[] = $row_info['userId'];
	}
	return implode(",", $id_contact);
	}
	else {
		return "0";
	}
	}
	else {
		return "0";
	}
}

//clubs get attachments mail
function getAttachmentsSuper($superuser_id, $superuser,$event_id) {
	global $con;
	global $full_path;
	global $data_path;
	global $appname;
	
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$attach = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents WHERE event_id = $event_id AND " . $superuser . "_id = $superuser_id");
	if(mysqli_num_rows($attach) > 0){
	$attachments=array();
	while($row_document = mysqli_fetch_array($attach)){
	if($row_document['filetype'] == "0"){
		$ext = pathinfo($row_document['file'] , PATHINFO_EXTENSION);
				if($ext == "gpx"){
			$attachment[] = "<a href=\"$full_path/gpx_viewer.php?superuser_id=" . base64_url_encode($superuser_id) . "&superuser=" . base64_url_encode($superuser) . "&document_id=" . base64_url_encode($row_document['id']) . "\" target=\"_blank\" class=\"link-button\">" . $row_document['name'] . "</a>";
				}
			else { 
	$attachment[] = "<a href=\"$data_path/" . $superuser . "s_documents/" . $superuser_id . "/" . $row_document['file'] . "\" target=\"_blank\" class=\"link-button\">" . $row_document['name'] . "</a>";
			}
	}
	if($row_document['filetype'] == "1"){
	$attachment[] = "<a href=\"" . $row_document['file'] . "\" target=\"_blank\" class=\"link-button\">" . $row_document['name'] . "</a>";
	}
	if($row_document['filetype'] == "2"){
	$attachment[] = "<a href=\"$full_path\" target=\"_blank\" class=\"link-button\">" . $row_document['name'] . "<br>(fotoalbum, te bekijken via de " . $appname . "-app)</a>";
	}
	if($row_document['filetype'] == "3"){
	$attachment[] = "<a href=\"$full_path\" target=\"_blank\" class=\"link-button\">" . $row_document['name'] . "<br>(video, te bekijken via de " . $appname . "-app)</a>";
	}
	if($row_document['filetype'] == "4"){
	$attachment[] = "<a href=\"$full_path\" target=\"_blank\" class=\"link-button\">" . $row_document['name'] . "<br>(audio file, te beluisteren via de " . $appname . "-app)</a>";
	}
	}
	//output all kids invited in list
	$attachments = implode("<br> ", $attachment);
	return $attachments;
	}
	else {
		return "";
	}
	}
	else {
		return "";
	}
}

//put entry in notifications
function NotifEntry($kid_id, $user_id, $code, $delete = false, $date = null) {
	global $con;
	$today = date('Y-m-d');
	if ($date != null) {
		$today = $date;
	}
	$check_notif = mysqli_query($con,"SELECT * FROM notifications WHERE kid_id = $kid_id AND user_id = $user_id AND code = $code");	
	if(mysqli_num_rows($check_notif) > 0)
	{
		if($delete == true){
			mysqli_query($con,"DELETE FROM notifications WHERE kid_id = $kid_id AND user_id = $user_id AND code = $code");
		}
		//do nothing
	}
	else {
		//insert notif
		if($delete == false){
			mysqli_query($con,"INSERT INTO notifications (date,kid_id,user_id,code) VALUES ('$today', $kid_id, $user_id, $code)");
		}
	}
}

//count notifications notifications-table: count all without bell click (for badge)
function CountNotif($kid_id,$user_id, $notifications = "", $badge = false, $css = "") {
	global $con;
	$string = "";
	if($notifications == ""){
		$string = "";
	}
	else {
		$string = "AND code IN ($notifications)";
	}
	$check_notif = mysqli_query($con,"SELECT * FROM notifications WHERE kid_id IN($kid_id) AND user_id = $user_id $string  AND bell_clicked = 0");
	if($badge == false){
	return mysqli_num_rows($check_notif);
	}
	if($badge == true){
		if( mysqli_num_rows($check_notif) > 0){
			return "<span class=\"badge $css\" style=\"background-color:red\">" . mysqli_num_rows($check_notif) . "</span>";
		}
		else {
			return "";
		}
	}
}

function LogEntryConnect($eventID, $kid_id, $friend_id, $club_id, $subcategory_id, $action) {
	global $con;
	$today = date('Y-m-d');
	$check_log = mysqli_query($con,"SELECT * FROM kids_logs WHERE calendar_id = $eventID AND club_id = $club_id AND kid_id = $kid_id AND date = '$today' AND friend_id = $friend_id AND action = $action");
	$numrows_check_log = mysqli_num_rows($check_log);	
	if($numrows_check_log > 0)
	{ 
	//do nothing
	}
	else {
	//insert log
	$insert_log = mysqli_query($con,"INSERT INTO kids_logs(date, calendar_id, kid_id, friend_id, club_id, subcategory, action) VALUES('$today','$eventID', '$kid_id','$friend_id', '$club_id', '$subcategory_id', '$action')");
	}
}

function LogEntrySuper($event_id, $superuser_id, $superuser, $kid_id, $parent_id, $subcategory, $ForAll, $action) {
	global $con;
	$today = date('Y-m-d');
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$check_log = mysqli_query($con,"SELECT * FROM kids_logs WHERE calendar_id = $event_id AND " . $superuser . "_id = $superuser_id AND date = '$today' AND subcategory=$subcategory AND ForAll = $ForAll AND action = '$action'");
	if(!$check_log){ 
	error_log("SELECT * FROM kids_logs WHERE calendar_id = $event_id AND " . $superuser . "_id = $superuser_id AND date = '$today' AND subcategory=$subcategory AND ForAll = $ForAll AND action = '$action'" . mysqli_error($con), 0);
	}
	$numrows_check_log = mysqli_num_rows($check_log);	
	if($numrows_check_log > 0)
	{ 
	//do nothing
	}
	else {
	//insert log
	mysqli_query($con,"INSERT INTO kids_logs(date, calendar_id, " . $superuser . "_id, kid_id, parent_id, subcategory, ForAll, action) VALUES('$today','$event_id','$superuser_id', '$kid_id', '$parent_id', '$subcategory','$ForAll','$action')");
	
	//insert log superuser_logs to monitor action in app
	mysqli_query($con,"INSERT INTO superuser_logs(date, calendar_id, " . $superuser . "_id, kid_id, parent_id, subcategory, ForAll, action) VALUES('$today','$event_id','$superuser_id', '$kid_id', '$parent_id', '$subcategory','$ForAll','$action')");
	}
	}
}

//cash affiliate
function AffiliateCashSuper($superuser_id, $superuser, $status) {
	global $con;
	global $CommPercent;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT *  FROM affiliate_overview WHERE " . $superuser . "_id = $superuser_id");	
	if(mysqli_num_rows($sql) > 0){
	$plus = mysqli_query($con,"SELECT SUM(affiliate_commission) AS total_cash FROM affiliate_overview WHERE " . $superuser . "_id = $superuser_id AND status_id IN($status)");
	$row_plus = mysqli_fetch_array($plus);
		return round($row_plus['total_cash']*$CommPercent, 1);
	}
	else {
		return "0";
	}
	}
	else {
		return "0";
	}
}

function AffiliateCashUser($users, $superuser_id, $superuser, $status) {
	global $con;
	global $CommPercent;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT *  FROM affiliate_overview WHERE " . $superuser . "_id = $superuser_id");	
	if(mysqli_num_rows($sql) > 0){
	$plus = mysqli_query($con,"SELECT SUM(affiliate_commission) AS total_cash FROM affiliate_overview WHERE " . $superuser . "_id = $superuser_id AND user_id IN ($users) AND status_id IN($status)");
	$row_plus = mysqli_fetch_array($plus);
		return round($row_plus['total_cash']*$CommPercent, 1);
	}
	else {
		return "0";
	}
	}
	else {
		return "0";
	}
}

//calculate what's already used in saldo Plus
function AffiliateMinusSuper($superuser_id, $superuser) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	//check if club used any plus-money for premium version
	$premium = mysqli_query($con,"SELECT SUM(reduction_plus) AS total_reduction_plus FROM premium WHERE " . $superuser . "_id = $superuser_id AND status_id = 1");
	if(mysqli_num_rows($premium) > 0){
	$row_premium = mysqli_fetch_array($premium);
		$reduction_premium = $row_premium['total_reduction_plus'];
	}
	else {
		$reduction_premium = "0";
	}	
	//check if there was already a pay-out for affiliate program
	$plus = mysqli_query($con,"SELECT SUM(plus_topay) AS total_payed_out_plus FROM credit WHERE " . $superuser . "_id = $superuser_id AND payed = 1");
	if(mysqli_num_rows($plus) > 0){
	$row_plus = mysqli_fetch_array($plus);
		$reduction_plus = $row_plus['total_payed_out_plus'];
	}
	else {
		$reduction_plus = "0";
	}
	return round($reduction_premium + $reduction_plus,1);
	}
	else {
		return 0;
	}
}

//check if user has superadmin rights
function SuperAdminRights($user_id) {
	global $con;
	$superuser_sql = mysqli_query($con,"SELECT id FROM users WHERE $user_id IN(2,3,20)");	
	if(mysqli_num_rows($superuser_sql) > 0){
		return true;
	}
	else {
		return false;
	}
}

//check if id can see error reports (ids are Simon, Perebak)
function ShowErrors($user_id) {
	global $con;
	$error_sql = mysqli_query($con,"SELECT id FROM users WHERE $user_id IN(2,20,83,446)");	
	if(mysqli_num_rows($error_sql) > 0){
		return true;
	}
	else {
		return false;
	}
}

//modal remove link shared account parents
function showModalRemoveShare($share_id, $other_id) {
global $con;
?>
<!-- Modal Remove  -->
<div id="myModalRemoveShare<?php echo $share_id ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">Verbreek je koppeling met <?php echo userValue($other_id, "username") ?></strong></h5>
      </div>
      <div class="modal-body">
	      <ul class="media-list" style="padding-left: 10px">
			  <li class="media">
			    <div class="media-left"><span class="fal fa-info fa-lg bg-icons bg-color-1 icon-circle"></span></div>
			    <div class="media-body">
			      <div class="media-heading">
				      Weet je zeker dat je de koppeling van je account met <strong><u><?php echo userValue($other_id, "username") ?></u></strong> wil verbreken?<br>
				      Dit kan niet ongedaan worden.<br>						      									     
			      </div>
					</div>
			  </li>
			</ul><br>
      
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary btn-sm btn-block" data-dismiss="modal"><span class="fal fa-times fa-fw"></span> Sluit</button>
        <button type="submit" id="<?php echo $share_id ?>" class="btn btn-danger btn-sm btn-block RejectShare"><span class="fas fa-trash-alt fa-fw"></span> Ja, ik ben zeker</button>
      </div>
    </div>
  </div>
  </div>
<!-- end Modal -->
<?php
}

function CountNewFilesSuper($superuser_id, $superuser, $subcategory_id, $filetype) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT * FROM " . $superuser . "s_documents 
	WHERE " . $superuser . "_id = $superuser_id 
	AND subcategory = $subcategory_id
	AND permanent = 1 
	AND filetype IN ($filetype)
	AND hide = 0
	AND date >= DATE(NOW()) - INTERVAL 1 DAY
	OR 
	" . $superuser . "_id = $superuser_id
	AND forall = 1 
	AND permanent = 1
	AND filetype IN ($filetype)
	AND hide = 0
	AND date >= DATE(NOW()) - INTERVAL 1 DAY
	OR 
	" . $superuser . "_id = $superuser_id
	AND forall = 2
	AND FIND_IN_SET($subcategory_id,forsome)
	AND permanent = 1
	AND filetype IN ($filetype)
	AND hide = 0
	AND date >= DATE(NOW()) - INTERVAL 1 DAY
	OR
	event_id IN (SELECT id FROM calendar WHERE " . $superuser . "_id = $superuser_id AND ForAll = 1)
	AND permanent = 1
	AND filetype IN ($filetype)
	AND hide = 0
	AND date >= DATE(NOW()) - INTERVAL 1 DAY");
	
	if(mysqli_num_rows($sql) > 0){
		return true;
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
}

function CountNewUnPayedPaybuttons($kid_id, $adult_id, $superuser_id, $superuser, $subcategory_id) {
	global $con;
	$today = date('Y-m-d');
	if($kid_id <> 0 && $adult_id <> 0){
		$adult_id = 0;
	}
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT * FROM paybuttons
	WHERE
	ForAll = 0 
	AND " . $superuser . "_id = $superuser_id 
	AND subcategory_id = $subcategory_id
	AND '$today' BETWEEN startdate AND enddate
	AND NOT EXISTS (SELECT * FROM paybuttons_results WHERE kid_id = $kid_id AND adult_id = $adult_id AND paybuttons_results.button_id = paybuttons.id AND status_id = 1)
	AND archive = 0
	OR
	ForAll = 1 
	AND " . $superuser . "_id = $superuser_id 
	AND '$today' BETWEEN startdate AND enddate
	AND NOT EXISTS (SELECT * FROM paybuttons_results WHERE kid_id = $kid_id AND adult_id = $adult_id AND paybuttons_results.button_id = paybuttons.id AND status_id = 1)
	AND archive = 0
	OR
	ForAll = 2
	AND " . $superuser . "_id = $superuser_id
	AND FIND_IN_SET($subcategory_id,ForSome)
	AND '$today' BETWEEN startdate AND enddate
	AND NOT EXISTS (SELECT * FROM paybuttons_results WHERE kid_id = $kid_id AND adult_id = $adult_id AND paybuttons_results.button_id = paybuttons.id AND status_id = 1)
	AND archive = 0
	OR
	ForAll = 3
	AND " . $superuser . "_id = $superuser_id
	AND (FIND_IN_SET($kid_id,ForKids) OR FIND_IN_SET($adult_id,ForAdults))
	AND '$today' BETWEEN startdate AND enddate
	AND NOT EXISTS (SELECT * FROM paybuttons_results WHERE kid_id = $kid_id AND adult_id = $adult_id AND paybuttons_results.button_id = paybuttons.id AND status_id = 1)
	AND archive = 0");
	
	if(mysqli_num_rows($sql) > 0){
		return true;
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
}

function CountNewMails($superuser_id, $superuser, $subcategory_id, $kid_id, $parent_id) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	if($kid_id <> "0"){
		$string = "FIND_IN_SET($kid_id, id_list_kids)";
	}
	else {
		$string = "FIND_IN_SET($parent_id, id_list_adults)";
	}
	$sql = mysqli_query($con,"SELECT id FROM mails 
	WHERE 
	$string 
	AND trash = 0
	AND " . $superuser . "_id = $superuser_id
	AND event_id = 0
	AND document_id = 0
	AND subcategory_id = $subcategory_id
	AND (laterdate IN (0,1) AND senddate <= CURDATE()
	OR laterdate = 2 AND DATE(sendmoment) <= CURDATE()) 
	AND id NOT IN (SELECT mail_id FROM mail_read WHERE parent_id = $parent_id)");
	if(mysqli_num_rows($sql)){
		return mysqli_num_rows($sql);
	}
	else {
		return false;
	}
	}
	else {
		return false;
	}
}

function CountNewLogsMenu($plugin) {
	global $con;
	if($plugin == "1"){
		$string = "connect = 1";
	}
	if($plugin == "2"){
		$string = "club = 1";
	}
	$sql = mysqli_query($con,"SELECT logs_subtitles.*, logs_titles.* FROM logs_subtitles
	JOIN logs_titles ON logs_subtitles.title_id = logs_titles.id
	WHERE $string AND log_type = 2 AND public = 1 AND date >= DATE(NOW()) - INTERVAL 1 DAY");
	if(mysqli_num_rows($sql) > 0){
		return true;
	}
	else {
		return false;
	}
}


function CountNewLogs($plugin,$title_id) {
	global $con;
	if($plugin == "1"){
		$string = "connect = 1";
	}
	if($plugin == 2){
		$string = "club = 1";
	}
	$sql = mysqli_query($con,"SELECT logs_subtitles.*, logs_titles.* FROM logs_subtitles
	JOIN logs_titles ON logs_subtitles.title_id = logs_titles.id
	WHERE $string AND log_type = 2 AND public = 1 AND title_id = $title_id
	AND date >= DATE(NOW()) - INTERVAL 1 DAY");
	
	if(mysqli_num_rows($sql) > 0){
		return true;
	}
	else {
		return false;
	}
}

function CountNewLogsSubtitle($plugin,$subtitle_id) {
	global $con;
	if($plugin == 1){
		$string = "connect = 1";
	}
	if($plugin == 2){
		$string = "club = 1";
	}
	$sql = mysqli_query($con,"SELECT logs_subtitles.*, logs_titles.* FROM logs_subtitles
	JOIN logs_titles ON logs_subtitles.title_id = logs_titles.id
	WHERE $string AND public = 1 AND log_type = 2 AND logs_subtitles.id = $subtitle_id
	AND date >= DATE(NOW()) - INTERVAL 1 DAY");
	
	if(mysqli_num_rows($sql) > 0){
		return true;
	}
	else {
		return false;
	}
}

function createThumbnail($filepath, $thumbpath, $thumbnail_width, $thumbnail_height, $background="transparent") {
    list($original_width, $original_height, $original_type) = getimagesize($filepath);
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width) / 2);
    $dest_y = intval(($thumbnail_height - $new_height) / 2);

    if ($original_type === 1) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    } else if ($original_type === 2) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    } else if ($original_type === 3) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    } else {
        return false;
    }

    $old_image = $imgcreatefrom($filepath);
    $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height); // creates new image, but with a black background

    // figuring out the color for the background
    if(is_array($background) && count($background) === 3) {
      list($red, $green, $blue) = $background;
      $color = imagecolorallocate($new_image, $red, $green, $blue);
      imagefill($new_image, 0, 0, $color);
    // apply transparent background only if is a png image
    } else if($background === 'transparent' && $original_type === 3) {
      imagesavealpha($new_image, TRUE);
      $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
      imagefill($new_image, 0, 0, $color);
    }

    imagecopyresampled($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
    $imgt($new_image, $thumbpath);
    return file_exists($thumbpath);
}


//check if an answer is already given on a survey when exclusive = 2
function CheckAvailability($question_id, $answer_id, $kid_id, $parent_id) {
	global $con;
	if($kid_id <> "0"){ 
	$sql = mysqli_query($con,"SELECT * FROM survey_answers WHERE question_id = $question_id AND answer_id = $answer_id AND kid_id <> $kid_id");
	}
	else {
	//parents
	$sql = mysqli_query($con,"SELECT * FROM survey_answers WHERE question_id = $question_id AND answer_id = $answer_id AND kid_id <> 0
	UNION SELECT * FROM survey_answers WHERE question_id = $question_id AND answer_id = $answer_id AND kid_id = 0 AND parent_id <> $parent_id");	
	}
	if(mysqli_num_rows($sql) > 0){
		return true;
	}
	else {
		return false;
	}	
}

//function to remove photoalbum folder on the server
function recursiveRemoveDirectory($directory)
{
    foreach(glob("{$directory}/*") as $file)
    {
        if(is_dir($file)) { 
            recursiveRemoveDirectory($file);
        } else {
            unlink($file);
        }
    }
    rmdir($directory);
}	

function helpPopup($class, $color, $icon, $type, $position){
	return "<a href=\"#\" class=\"notifit_custom $class $position\" id=\"$type\" style=\"color:$color; background-color:transparent; display:inline\">&nbsp;<span class=\"fa fa-$icon fa-fw\"></span></a>";
}

function showLabels($kid_id, $adult_id, $manual_id, $superuser_id, $superuser, $subcategory_id, $showpaybuttons = "true"){
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
		if($kid_id <> "0"){
			$sql_default = mysqli_query($con, "SELECT * FROM kids_" . $superuser . "s WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id AND mark = 1");
		}
		if($adult_id <> "0"){
			$sql_default = mysqli_query($con, "SELECT * FROM parents_" . $superuser . "s WHERE parent_id = $adult_id AND " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id AND mark = 1");
		}
		if($manual_id <> "0"){
			$sql_default = mysqli_query($con, "SELECT * FROM kids_" . $superuser . "s_manual WHERE id = $manual_id AND " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id AND mark = 1");
		}
	
	
	//show default labels
	if(mysqli_num_rows($sql_default) > 0){
		$row_default = mysqli_fetch_array($sql_default);
		?>
		<a role="button" id="fa-flag" name="Standaard" class="notifit_custom labelinfo btn btn-default btn-xs" style="display: inline;"><i class="fal fa-flag fa-fw fa-lg" aria-hidden="true"></i></a>&nbsp;
		<?php
	}
	
	//show custom labels
	$sql = mysqli_query($con, "SELECT * FROM labels WHERE kid_id = $kid_id AND adult_id = $adult_id AND manual_id = $manual_id AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id");
	if(mysqli_num_rows($sql) > 0){
		while($row = mysqli_fetch_array($sql)){
			$get_icon = mysqli_query($con, "SELECT * FROM " . $superuser . "s_labels WHERE id = $row[label_id]");
			$icon = mysqli_fetch_array($get_icon);
			?>
			<a role="button" id="<?php echo $icon['icon'] ?>" name="<?php echo $icon['title'] ?>" class="notifit_custom labelinfo btn btn-default btn-xs" style="display: inline;"><i class="fal <?php echo $icon['icon'] ?> fa-fw fa-lg" aria-hidden="true"></i></a>&nbsp;
			<?php
		}
	}
	
	if($showpaybuttons == "true"){
	//show paybutton labels
	$sql_paybuttons = mysqli_query($con,"SELECT * FROM paybuttons 
	WHERE " . $superuser . "_id = $superuser_id 
	AND CURDATE() BETWEEN startdate AND enddate 
	AND archive = 0
	AND
	(
	ForAll = 0 
	AND subcategory_id = $subcategory_id
	OR
	ForAll = 1
	OR
	ForAll = 2 AND FIND_IN_SET($subcategory_id, ForSome)
	OR
	ForAll = 3 AND 
	(FIND_IN_SET($kid_id, ForKids) OR FIND_IN_SET($adult_id, ForAdults) OR FIND_IN_SET($manual_id, ForManual)
	)
	)");
	if(mysqli_num_rows($sql_paybuttons) > 0){
		if($kid_id <> 0){
			$string = "AND kid_id = $kid_id";
		}
		if($adult_id <> 0){
			$string = "AND adult_id = $adult_id";
		}
		if($manual_id <> 0){
			$string = "AND manual_id = $manual_id";
		}
		while($row_paybuttons = mysqli_fetch_array($sql_paybuttons)){
			$check_status = mysqli_query($con,"SELECT * FROM paybuttons_results WHERE button_id = $row_paybuttons[id] $string");
			if(mysqli_num_rows($check_status) > 0){
				while($row_check_status = mysqli_fetch_array($check_status)){
				if($row_check_status['status_id'] == 1){
					$background_pay = "green";
					$additional = $row_check_status['amount'] . "  &euro; betaald op " . date('d-m-Y', $row_check_status['timestamp']) . "<br><strong>Referentie:</strong> '" . $row_check_status['invoice'] . "'";
					$method = $row_check_status['method'];
					if($row_check_status['method'] == "manueel"){
					$url = "<a role='button' id='$row_paybuttons[id]' name='remove' class='manualpayed btn btn-warning btn-sm btn-block' kid_id_attr='" . $kid_id . "' manual_id_attr='" . $manual_id . "' adult_id_attr='" . $adult_id . "'><span class='fal fa-credit-card'></span> Markeer als 'onbetaald'</a>";	
					}
					else { 
					$url = "";
					}
				break;
				}
				if($row_check_status['status_id'] == 2){
					$background_pay = "red";
					$additional = "Er is een probleem opgetreden met de betaling of betaling werd geannuleerd";
					$url = "<a role='button' id='$row_paybuttons[id]' name='add' class='manualpayed btn btn-success btn-sm btn-block' kid_id_attr='" . $kid_id . "' manual_id_attr='" . $manual_id . "' adult_id_attr='" . $adult_id . "'><span class='fal fa-credit-card'></span> Markeer als 'betaald'</a>";
					$method = $row_check_status['method'];
				}
				if($row_check_status['status_id'] == 0){
					$background_pay = "orange";
					$additional = "Lid heeft nog niet betaald";
					$method = $row_check_status['method'];
					$url = "<a role='button' id='$row_paybuttons[id]' name='add' class='manualpayed btn btn-success btn-sm btn-block' kid_id_attr='" . $kid_id . "' manual_id_attr='" . $manual_id . "' adult_id_attr='" . $adult_id . "'><span class='fal fa-credit-card'></span> Markeer als 'betaald'</a>";
				}
				
			}
			}
			else {
				$background_pay = "orange";
				$additional = "Lid heeft nog niet betaald";
				$url = "<a role='button' id='$row_paybuttons[id]' name='add' class='manualpayed btn btn-success btn-sm btn-block' kid_id_attr='" . $kid_id . "' manual_id_attr='" . $manual_id . "' adult_id_attr='" . $adult_id . "'><span class='fal fa-credit-card'></span> Markeer als 'betaald'</a>";
				$method = "nvt";
				}
			?>
			<a role="button" url_attr="<?php echo $url ?>" id="fa-euro-sign" name="<div align='left'><strong>Titel:</strong> '<?php echo $row_paybuttons['title'] ?>'<br><strong>Status:</strong> <?php echo $additional ?><br><strong>Betaalmethode:</strong> <?php echo $method ?><br><strong>Opm:</strong> Dit label verdwijnt automatisch na <?php echo date("d-m-Y", strtotime($row_paybuttons['enddate'])) ?></div>" class="paybuttoninfo"  style="display:inline; border: 0px solid lightgray; box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; color:white; background-color: <?php echo $background_pay ?>"><span class="fal fa-euro-sign fa-fw"></span></a>&nbsp;
			<?php
			}
		}
	}
	}
	else {
		return false;
	}	
}

function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80) {
    $imgsize = getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];
 
    switch ($mime) {
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;
 
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 7;
            break;
 
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 80;
            break;
 
        default:
            return false;
            break;
    }
 
    $dst_img = imagecreatetruecolor($max_width, $max_height);
    ///////////////
 
    imagealphablending($dst_img, false);
    imagesavealpha($dst_img, true);
    $transparent = imagecolorallocatealpha($dst_img, 255, 255, 255, 127);
    imagefilledrectangle($dst_img, 0, 0, $max_width, $max_height, $transparent);
 
 
    /////////////
    $src_img = $image_create($source_file);
 
    $width_new = $height * $max_width / $max_height;
    $height_new = $width * $max_height / $max_width;
    //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
    if ($width_new > $width) {
        //cut point by height
        $h_point = (($height - $height_new) / 2);
        //copy image
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
    } else {
        //cut point by width
        $w_point = (($width - $width_new) / 2);
        imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
    }
 
    $image($dst_img, $dst_dir, $quality);
 
    if ($dst_img)
        imagedestroy($dst_img);
    if ($src_img)
        imagedestroy($src_img);
}
 
function image_fix_orientation($filename) {
    $exif = exif_read_data($filename);
    if (!empty($exif['Orientation'])) {
        $image = imagecreatefromjpeg($filename);
        switch ($exif['Orientation']) {
        case 3:
            $image = imagerotate($image, -180, 0);
            break;

        case 6:
            $image = imagerotate($image, -90, 0);
            break;

        case 8:
            $image = imagerotate($image, 90, 0);
            break;
    }
        imagejpeg($image, $filename, 90);
    }
}

//shorten description landingpage!
function shorten_string($string, $wordsreturned)
{
  $retval = $string;
  $string = preg_replace('/(?<=\S,)(?=\S)/', ' ', $string);
  $string = str_replace("\n", " ", $string);
  $array = explode(" ", $string);
  if (count($array)<=$wordsreturned)
  {
    $retval = $string;
  }
  else
  {
    array_splice($array, $wordsreturned);
    $retval = implode(" ", $array)." ...";
  }
  return $retval;
}


function showtags($superuser_id, $superuser, $subcategory_id, $filetype) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$tagslist_sql = mysqli_query($con, "SELECT tags FROM " . $superuser . "s_documents 
	WHERE " . $superuser . "_id = $superuser_id 
	AND subcategory = $subcategory_id 
	AND permanent = 1 
	AND filetype IN ($filetype)
	AND tags <> ''
	OR 
	" . $superuser . "_id = $superuser_id 
	AND forall = 1 
	AND permanent = 1
	AND filetype IN ($filetype)
	AND tags <> ''
	OR 
	" . $superuser . "_id = $superuser_id 
	AND forall = 2 
	AND FIND_IN_SET($subcategory_id,forsome)
	AND permanent = 1
	AND filetype IN ($filetype)
	AND tags <> ''
	OR
	event_id IN (SELECT id FROM calendar WHERE " . $superuser . "_id = $superuser_id AND ForAll = 1)
	AND permanent = 1
	AND filetype IN ($filetype)
	AND tags <> ''");
	if(mysqli_num_rows($tagslist_sql) > 0){
		while($row_tagslist = mysqli_fetch_array($tagslist_sql)) {
		$tags[] = $row_tagslist['tags'];
	}
	return implode(",", $tags);
	}
	else {
		return "0";
	}
	}
	else {
		return "0";
	}
}

function hasPermission($superuser_id, $superuser, $permission_id) {
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	$sql_permission = mysqli_query($con,"SELECT * FROM permissions_shared WHERE " . $superuser . "_id = $superuser_id AND permission_id IN($permission_id)");
	if(mysqli_num_rows($sql_permission) > 0) {
		return false;
	}
	else {
		return true;
	}
	}
	else {
		return false;
	}
}

function allowNotification($user_id, $code) {
	global $con;
	$sql = mysqli_query($con,"SELECT * FROM notifications_disable WHERE user_id = $user_id AND code IN($code)");
	if(mysqli_num_rows($sql) > 0) {
		return false;
	}
	else {
		return true;
	}
}

function allowMails($user_id, $club_id, $subcategory_id, $type) {
	global $con;
	$sql = mysqli_query($con,"SELECT * FROM mails_usersettings WHERE user_id = $user_id AND club_id = $club_id AND subcategory_id = $subcategory_id AND type = $type");
	if(mysqli_num_rows($sql) > 0) {
		return false;
	}
	else {
		return true;
	}
}

function allowCalendarsSync($user_id, $club_id, $subcategory_id, $private, $kid_id, $type) {
	global $con;
	$sql = mysqli_query($con,"SELECT * FROM sync_usersettings WHERE user_id = $user_id AND club_id = $club_id AND subcategory_id = $subcategory_id AND private = $private AND kid_id = $kid_id AND type = $type");
	if(mysqli_num_rows($sql) > 0) {
		return false;
	}
	else {
		return true;
	}
}

function allowCalendars($parent_id, $kid_id, $superuser_id, $superuser, $subcategory_id) {
	global $con;
	$user_id = userValue(null, "id");
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con,"SELECT * FROM calendar_usersettings WHERE user_id = $user_id AND parent_id = $parent_id AND kid_id = $kid_id AND " . $superuser . "_id = $superuser_id AND subcategory_id = $subcategory_id");
	if(mysqli_num_rows($sql) > 0) {
		return false;
	}
	else {
		return true;
	}
	}
	else {
		return false;
	}
}

//get list of clubs family is member of
function WhosMember($adult_id) {
	global $con;
	$kid_id = KidsFromParentId($adult_id);  
	$parent_id = JoinedParentsFromParentId($adult_id);
	$check_clubs = mysqli_query($con,"SELECT *
	FROM 
	(SELECT * FROM kids_clubs
	WHERE kid_id IN ($kid_id)
	GROUP BY club_id
	UNION
	SELECT *
	FROM parents_clubs
	WHERE parent_id IN ($parent_id)
	GROUP BY club_id) t
	GROUP BY club_id
	ORDER BY club_id ASC");
	if(mysqli_num_rows($check_clubs) > 0){ 
	while($row_clubs = mysqli_fetch_array($check_clubs)){
	$club_ids[] = $row_clubs['club_id'];  
	}
 	$clubs_list = implode(", ", $club_ids);	
 	return $clubs_list; 
	}
	else {
		return "0";
	}
}

function showHomeAddress($kid_id, $adult_id, $key_code_club){
	global $con;
	global $pass;
	
	if(($kid_id == 0) && ($adult_id == 0) && ($key_code_club == 0)){
		return false;
	}
	
	if($kid_id <> "0"){
		$string = "kid_id = $kid_id";
	}
	if(($kid_id == 0) && ($adult_id <> "0")){
		$string = "adult_id = $adult_id";
	}
	if($key_code_club <> "0"){
		$string = "key_code_club = $key_code_club";
	}
	
	$sql_address = mysqli_query($con,"SELECT AES_DECRYPT(street, SHA1('$pass')) AS street, AES_DECRYPT(number, SHA1('$pass')) AS number, AES_DECRYPT(postal, SHA1('$pass')) AS postal, AES_DECRYPT(city, SHA1('$pass')) AS city, AES_DECRYPT(street2, SHA1('$pass')) AS street2, AES_DECRYPT(number2, SHA1('$pass')) AS number2, AES_DECRYPT(postal2, SHA1('$pass')) AS postal2, AES_DECRYPT(city2, SHA1('$pass')) AS city2 FROM address WHERE $string");
	if(mysqli_num_rows($sql_address) > 0){ 
	$row_address = mysqli_fetch_array($sql_address);
	if(($row_address['street'] == "") && ($row_address['street2'] == "")){
		return "geen";
	}
	if(($row_address['street'] <> "") && ($row_address['street2'] <> "")){
		return $row_address['street'] . " " . $row_address['number'] . ", " . $row_address['postal'] . " " . $row_address['city'] . "<br>" . $row_address['street2'] . " " . $row_address['number2'] . ", " . $row_address['postal2'] . " " . $row_address['city2'];
	}
	if(($row_address['street'] == "") && ($row_address['street2'] <> "")){
		return $row_address['street2'] . " " . $row_address['number2'] . ", " . $row_address['postal2'] . " " . $row_address['city2'];
	}
	if(($row_address['street'] <> "") && ($row_address['street2'] == "")){
		return $row_address['street'] . " " . $row_address['number'] . ", " . $row_address['postal'] . " " . $row_address['city'];
	}
	}
	else {
		return "geen adres opgegeven";
	}
}

function folderSize ($dir)
{
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : folderSize($each);
    }
    return $size;
}

function isa_convert_bytes_to_specified($bytes, $to, $decimal_places = 1) {
    $formulas = array(
        'K' => number_format($bytes / 1024, $decimal_places),
        'M' => number_format($bytes / 1048576, $decimal_places),
        'G' => number_format($bytes / 1073741824, $decimal_places)
    );
    return isset($formulas[$to]) ? $formulas[$to] : 0;
}

function calculateServerSpace($superuser_id, $superuser){
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	$bytes_videos = folderSize ("../data/" . $superuser . "s_videos/" . $superuser_id);
	$bytes_audio = folderSize ("../data/" . $superuser . "s_audio/" . $superuser_id);
	$bytes_images = folderSize ("../data/" . $superuser . "s_images/" . $superuser_id);
	$bytes_documents = folderSize ("../data/" . $superuser . "s_documents/" . $superuser_id);

	$bytes = $bytes_audio + $bytes_documents + $bytes_images + $bytes_videos;
	return isa_convert_bytes_to_specified($bytes, 'G', 2);
	}
	else {
		return false;
	}
}


// edited KFJ
function checkPremiumLevel($superuser_id, $superuser, $value){
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	$check = mysqli_query($con,"SELECT * FROM premium WHERE " . $superuser . "_id = $superuser_id AND status_id = 1 AND end_date > CURDATE() ORDER BY id DESC LIMIT 1");
	if(mysqli_num_rows($check) > 0){
		$row = mysqli_fetch_array($check);
		$level = $row['level'];
		$end_date = $row['end_date'];
		if($level == "1"){
			if($value == "serverspace"){
				return 2;
			}
			if($value == ""){
				return "level 1";
			}
			if($value == "end_date"){
				return $end_date;
			}
			if($value == "contacts"){
				return 10000;
			}
			if($value == "default_blocks"){
				return 10000;
			}
			if($value == "upload_pictures"){
				return 40;
			}
			if($value == "size_attachments"){
				return 10;
			}
		}
		if($level == "2"){
			if($value == "serverspace"){
				return 10;
			}
			if($value == ""){
				return "level 2";
			}
			if($value == "end_date"){
				return $end_date;
			}
			if($value == "contacts"){
				return 10000;
			}
			if($value == "default_blocks"){
				return 10000;
			}
			if($value == "upload_pictures"){
				return 50;
			}
			if($value == "size_attachments"){
				return 15;
			}
		}
	}
	else {
		if($value == "serverspace"){
				return 0.25;
			}
		if($value == ""){
				return "level 0";
			}
		if($value == "end_date"){
				return "";
			}
		if($value == "contacts"){
				return 2;
			}
		if($value == "default_blocks"){
				return 0;
			}
		if($value == "upload_pictures"){
				return 20;
			}
		if($value == "size_attachments"){
				return 5;
			}
	}
	}
	else {
		return false;
	}
} 

function displayAdvertising($superuser_id, $superuser, $is_admin){
	global $con;
	global $data_path;
	global $full_path;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$check = mysqli_query($con,"SELECT * FROM advertising WHERE " . $superuser . "_id = $superuser_id AND is_admin = $is_admin ORDER BY row_order DESC");
	if(mysqli_num_rows($check) > 0){
		while($row = mysqli_fetch_array($check)){
			if($superuser_id <> "0"){  
			$adverts[] = "<a href=\"" . $full_path . "/advert_click.php?" . $superuser . "_id=" . $superuser_id . "&keycode=$row[keycode]&url=$row[url]\" target=\"_blank\"><img src=\"$data_path/" . $superuser . "s_sponsors/$superuser_id/$row[image]\" alt=\"$row[sponsor]\" width=\"100px\"></a>";
			}
			else {  
			$adverts[] = "<a href=\"" . $full_path . "/advert_click.php?keycode=$row[keycode]&url=$row[url]\" target=\"_blank\"><img src=\"$data_path/img_sponsors/$row[image]\" alt=\"$row[sponsor]\" width=\"100px\"></a>";
			}
		}
		$advertisements = implode("", $adverts);	
		return $advertisements;
		}
		else {
			return "";
		}
	}
	else {
		return "";
	}
} 

function colorBlock($superuser_id, $superuser, $eventID){
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	$sql = mysqli_query($con, "SELECT title, subcategory FROM calendar WHERE id = $eventID AND " . $superuser . "_id = $superuser_id AND kid_id = 0");
	if(mysqli_num_rows($sql) > 0){
		$row = mysqli_fetch_array($sql);
		
		if ((strpos($row['title'], strtoupper($superuser)) !== false) ||  (strpos($row['title'], strtolower($superuser)) !== false)){
		$block_id = preg_replace("/[^0-9\.]/", '', $row['title']);
    	if($block_id == 1){
	    	$get_background = mysqli_query($con, "SELECT hex_color_default FROM " . $superuser . "s_categories WHERE id = $row[subcategory]");
			if(mysqli_num_rows($get_background) > 0){
			$row_background = mysqli_fetch_array($get_background);
			if($row_background['hex_color_default'] <> ""){ 
			return $row_background['hex_color_default'];
			}
			else { 
	    	return "663300";
			}
			}
			else {
			return "663300";	
			}
    	}
    	elseif($block_id == 2){
	    	$get_background = mysqli_query($con, "SELECT hex_color_default2 FROM " . $superuser . "s_categories WHERE id = $row[subcategory]");
			if(mysqli_num_rows($get_background) > 0){
			$row_background = mysqli_fetch_array($get_background);
			if($row_background['hex_color_default2'] <> ""){ 
			return $row_background['hex_color_default2'];
			}
			else { 
	    	return "ec971f";
			}
			}
			else {
			return "ec971f";	
			}
    	}
    	else {
	    	return "663300";
    	}
		}
	
		//user blocks
		if (strpos($row['title'], 'user') !== false) {
		$block_id = preg_replace("/[^0-9\.]/", '', $row['title']);
		$sql_block = mysqli_query($con, "SELECT hex_color FROM " . $superuser . "s_default_blocks WHERE id = $block_id AND " . $superuser . "_id = $superuser_id");
		if(mysqli_num_rows($sql_block) > 0){
			$row_block = mysqli_fetch_array($sql_block);
			return $row_block['hex_color'];
		}
		else {
			return "663300";
		}
		}
	}
	else {
		return "663300";
	}
	}
	else {
		return "663300";
	}
}

function getCalendarItemsFromKidId($kid_id,$all, $start = "2019-08-01", $end = "2019-08-31"){
	global $con;
	global $pass; 
	$logged_in_user = userValue(null, "id");
	$events = array();
	
	//removed because performance issues 15/01/23 from line 5064
	//OR DATE(calendar.enddate) > DATE(calendar.startdate) AND (DATE(calendar.startdate) < DATE('$start') OR DATE(calendar.enddate) > DATE('$end'))
	
	$sql = mysqli_query($con, "SELECT calendar.id, calendar.subject, calendar.comment, calendar.startdate, calendar.enddate, calendar.club_id, calendar.invite, showtimeframe, canceled, allDay
	FROM calendar
	WHERE
	(
	DATE(calendar.startdate) BETWEEN DATE('$start') AND DATE('$end')
	OR
	DATE(calendar.enddate) BETWEEN DATE('$start') AND DATE('$end')
	OR
	DATE(calendar.startdate) < DATE('$start') AND DATE(calendar.enddate) > DATE('$end')
	)
	AND
	(
	club_id <> 0 AND
	NOT EXISTS (SELECT id FROM users WHERE club_id = calendar.club_id AND permission = 2 AND testmode = 1)
	AND
		(
		ForAll = 0
		AND club_id IN (SELECT club_id FROM kids_clubs WHERE kid_id = $kid_id AND kids_clubs.subcategory=calendar.subcategory AND club_id <> 0)
		AND NOT EXISTS (SELECT id FROM calendar_usersettings WHERE user_id = $logged_in_user AND kid_id = $kid_id AND club_id = calendar.club_id AND subcategory_id = calendar.subcategory)
		OR ForAll = 1
		AND club_id IN (SELECT club_id FROM kids_clubs WHERE kid_id = $kid_id AND kids_clubs.subcategory <> 0 AND club_id <> 0) 
		AND EXISTS (SELECT subcategory FROM kids_clubs WHERE club_id = calendar.club_id AND kid_id = $kid_id AND subcategory NOT IN (SELECT subcategory_id FROM calendar_usersettings WHERE user_id = $logged_in_user AND kid_id = $kid_id AND club_id = calendar.club_id))
		OR ForAll = 2
		AND club_id IN (SELECT club_id FROM kids_clubs WHERE kid_id = $kid_id AND FIND_IN_SET(kids_clubs.subcategory,calendar.ForSome)) 
		AND EXISTS (SELECT subcategory FROM kids_clubs WHERE club_id = calendar.club_id AND kid_id = $kid_id AND FIND_IN_SET(subcategory,calendar.ForSome) AND subcategory NOT IN (SELECT subcategory_id FROM calendar_usersettings WHERE user_id = $logged_in_user AND kid_id = $kid_id AND club_id = calendar.club_id))
		OR ForAll = 3
		AND club_id IN (SELECT club_id FROM kids_clubs WHERE kid_id = $kid_id AND FIND_IN_SET(kid_id,calendar.ForKids))
		AND NOT EXISTS (SELECT id FROM calendar_usersettings WHERE user_id = $logged_in_user AND kid_id = $kid_id AND club_id = calendar.club_id AND subcategory_id = calendar.subcategory)
		)
	)");
	
		
	while($fetch = mysqli_fetch_array($sql))
	{
	$e = array();
	$e['id'] = $fetch['id'];
	$e['start'] = $fetch['startdate'];
	$e['end'] = $fetch['enddate'];
	$e['canceled'] = $fetch['canceled'];
	$e['clubname'] = userValueSuper($fetch['club_id'], "club", "username");
	$e['kidname'] = userValueKid($kid_id, "name");
	$e['invite'] = $fetch['invite'];
	$e['club_id'] = $fetch['club_id']; 
	$e['comment'] = $fetch['comment'];
	$e['subject'] = $fetch['subject'];
	$e['title'] = "PLAYDAYS";
	$e['showtimeframe'] = $fetch['showtimeframe'];
	
	$e['colorblock_club'] = colorBlock($fetch['club_id'], "club", $fetch['id']);
	$e['font_colorblock_club'] = getContrastColor("#" . colorBlock($fetch['club_id'], "club", $fetch['id']));
	
	if(displayOwnRSVPs($fetch['id'], $kid_id, "0", "0")){
	$e['RSVP'] = "GO";
	}
	elseif(displayOwnRSVPs($fetch['id'], $kid_id, "0", "1,2")){
	$e['RSVP'] = "NOGO";
	}
	else {
	$e['RSVP'] = "NA";
	}
	
	if($fetch['club_id'] <> 0){
		$superuser = "club";
		$superuser_id = $fetch['club_id'];
	}
	
	$sql_survey = mysqli_query($con,"SELECT * FROM survey_questions WHERE (event_id = $fetch[id] OR FIND_IN_SET($fetch[id], event_id_list)) AND ". $superuser . "_id = $superuser_id AND archive = 0");
	if(mysqli_num_rows($sql_survey) > 0){ 
	$e['survey'] = "1";
	}
	else {
	$e['survey'] = "0";    
	}
	
	$sql_paybuttons = mysqli_query($con,"SELECT paybuttons.id AS button_id, paybuttons.*, ". $superuser . "s_paybuttons.* FROM ". $superuser . "s_paybuttons
	JOIN paybuttons ON paybuttons.id = ". $superuser . "s_paybuttons.button_id
	WHERE ". $superuser . "s_paybuttons.event_id = $fetch[id] AND paybuttons.archive = 0");
	if(mysqli_num_rows($sql_paybuttons) > 0){ 
	$e['paybutton'] = "1";
	}
	else {
	$e['paybutton'] = "0";    
	}
	
	$sql_attachments = mysqli_query($con,"SELECT * FROM ". $superuser . "s_documents WHERE event_id = $fetch[id]");
	if(mysqli_num_rows($sql_attachments) > 0){ 
	$e['attachment'] = "1";
	}
	else {
	$e['attachment'] = "0";    
	}
	
	
	$allday = ($fetch['allDay'] == "true") ? true : false;
	$e['allDay'] = $allday;
	array_push($events, $e);
 	
		}
	return $events;
	}
	
function getCalendarItemsFromParentId($adult_id, $start = "2019-08-01", $end = "2019-08-31"){
	global $con;
	global $pass; 
	$logged_in_user = userValue(null, "id");
	$events = array();
	
	//calendar slow 15/01/2023: removed this line from line 5169
	//	OR DATE(calendar.enddate) > DATE(calendar.startdate) AND (DATE(calendar.startdate) < DATE('$start') OR DATE(calendar.enddate) > DATE('$end'))
	
	$sql = mysqli_query($con, "SELECT id, subject, comment, startdate, enddate, club_id, invite, showtimeframe, canceled, allDay
	FROM calendar
	WHERE
	(
	DATE(calendar.startdate) BETWEEN DATE('$start') AND DATE('$end')
	OR
	DATE(calendar.enddate) BETWEEN DATE('$start') AND DATE('$end')
	OR 
	DATE(calendar.startdate) < DATE('$start') AND DATE(calendar.enddate) > DATE('$end')
	)
	AND
	(
	club_id <> 0 AND
	NOT EXISTS (SELECT id FROM users WHERE club_id = calendar.club_id AND permission = 2 AND testmode = 1)
	AND
	(ForAll = 0
	AND club_id IN (SELECT club_id FROM parents_clubs WHERE parent_id IN($adult_id) AND parents_clubs.subcategory=calendar.subcategory AND club_id <> 0)
	AND NOT EXISTS (SELECT id FROM calendar_usersettings WHERE user_id = $logged_in_user AND parent_id = $adult_id AND club_id = calendar.club_id AND subcategory_id = calendar.subcategory)
	OR
	ForAll = 1
	AND club_id IN (SELECT club_id FROM parents_clubs WHERE parent_id IN($adult_id) AND parents_clubs.subcategory <> 0 AND club_id <> 0) 
	AND EXISTS (SELECT subcategory FROM parents_clubs WHERE club_id = calendar.club_id AND parent_id IN($adult_id) AND subcategory NOT IN (SELECT subcategory_id FROM calendar_usersettings WHERE user_id = $logged_in_user AND parent_id IN($adult_id) AND club_id = calendar.club_id))
	OR
	ForAll = 2
	AND club_id IN (SELECT club_id FROM parents_clubs WHERE parent_id IN($adult_id) AND FIND_IN_SET(parents_clubs.subcategory,calendar.ForSome)) 
	AND EXISTS (SELECT subcategory FROM parents_clubs WHERE club_id = calendar.club_id AND parent_id IN($adult_id) AND FIND_IN_SET(subcategory,calendar.ForSome) AND subcategory NOT IN (SELECT subcategory_id FROM calendar_usersettings WHERE user_id = $logged_in_user AND parent_id IN($adult_id) AND club_id = calendar.club_id))
	OR
	ForAll = 3
	AND club_id IN (SELECT club_id FROM parents_clubs WHERE parent_id IN($adult_id) AND FIND_IN_SET(parent_id,calendar.ForAdults)) 
	AND NOT EXISTS (SELECT id FROM calendar_usersettings WHERE user_id = $logged_in_user AND parent_id = $adult_id AND club_id = calendar.club_id AND subcategory_id = calendar.subcategory)
	)
	)");
		
	while($fetch = mysqli_fetch_array($sql))
	{
	$e = array();
	$e['id'] = $fetch['id'];
	$e['start'] = $fetch['startdate'];
	$e['end'] = $fetch['enddate'];
	$e['canceled'] = $fetch['canceled'];
	$e['clubname'] = userValueSuper($fetch['club_id'], "club", "username");
	$e['kidname'] = userValue($adult_id,"username");
	$e['invite'] = $fetch['invite'];
	$e['club_id'] = $fetch['club_id']; 
	$e['subject'] = $fetch['subject'];
	$e['comment'] = $fetch['comment'];
	$e['title'] = "PLAYDAYS";
	$e['showtimeframe'] = $fetch['showtimeframe'];
	
	$e['colorblock_club'] = colorBlock($fetch['club_id'], "club", $fetch['id']);
	$e['font_colorblock_club'] = getContrastColor("#" . colorBlock($fetch['club_id'], "club", $fetch['id']));
	
	if(displayOwnRSVPs($fetch['id'], "0", $adult_id, "0")){
	$e['RSVP'] = "GO";
	}
	elseif(displayOwnRSVPs($fetch['id'], "0", $adult_id, "1,2")){
	$e['RSVP'] = "NOGO";
	}
	else {
	$e['RSVP'] = "NA";
	}
	
	if($fetch['club_id'] <> 0){
		$superuser = "club";
		$superuser_id = $fetch['club_id'];
	}
	
	$sql_survey = mysqli_query($con,"SELECT * FROM survey_questions WHERE (event_id = $fetch[id] OR FIND_IN_SET($fetch[id], event_id_list)) AND ". $superuser . "_id = $superuser_id AND archive = 0");
	if(mysqli_num_rows($sql_survey) > 0){ 
	$e['survey'] = "1";
	}
	else {
	$e['survey'] = "0";    
	}
	
	$sql_paybuttons = mysqli_query($con,"SELECT paybuttons.id AS button_id, paybuttons.*, ". $superuser . "s_paybuttons.* FROM ". $superuser . "s_paybuttons
	JOIN paybuttons ON paybuttons.id = ". $superuser . "s_paybuttons.button_id
	WHERE ". $superuser . "s_paybuttons.event_id = $fetch[id] AND paybuttons.archive = 0");
	if(mysqli_num_rows($sql_paybuttons) > 0){ 
	$e['paybutton'] = "1";
	}
	else {
	$e['paybutton'] = "0";    
	}
	
	$sql_attachments = mysqli_query($con,"SELECT * FROM ". $superuser . "s_documents WHERE event_id = $fetch[id]");
	if(mysqli_num_rows($sql_attachments) > 0){ 
	$e['attachment'] = "1";
	}
	else {
	$e['attachment'] = "0";    
	}
	
	
	$allday = ($fetch['allDay'] == "true") ? true : false;
	$e['allDay'] = $allday;
	array_push($events, $e);
 	
		}
	return $events;
}


function showAwinMerchant($program, $affiliate){
	global $con;
	if($affiliate == "7"){ 
	$sql = mysqli_query($con, "SELECT merchant_name FROM awin_merchants WHERE id = '$program'");
	if(mysqli_num_rows($sql) > 0){
		$row = mysqli_fetch_array($sql);
		return $row['merchant_name'];
	}
	else {
		return "Andere webshop";
	}
	}
	else {
		return $program;
	}
}

function ChatAllowedNew($eventID, $kid_id, $parent_id){ 
	global $con;
	
	//list target of event
	$sql_target = mysqli_query($con, "SELECT * FROM calendar WHERE id = $eventID");
	if(mysqli_num_rows($sql_target) > 0){
		$row_target = mysqli_fetch_array($sql_target);
		
		if(($row_target['ForAll'] == 0) || ($row_target['ForAll'] == 3)){
			$target = $row_target['subcategory'];
			if($row_target['club_id'] <> "0"){
				$sql = mysqli_query($con, "SELECT chat FROM clubs_categories WHERE club_id = $row_target[club_id] AND id = $target");
				}
				if(mysqli_num_rows($sql) > 0){
					$row = mysqli_fetch_array($sql);
					if($row['chat'] == 1){
						return true;
					}
					else {
						return false;
					}
				}
				else {
					return false;
				}
		}
		
		if($row_target['ForAll'] == 1){
			if($row_target['club_id'] <> "0"){
			$target = explode(',', getSubcategoryIdsFromSuperUser($row_target['club_id'], "club"));
			}
		}
		if($row_target['ForAll'] == 2){
			$target = explode(',', $row_target['ForSome']);	
		}
	}
	
	if($kid_id <> "0"){
	if($row_target['club_id'] <> "0"){
	//list subcategories where kid_id is member from
	$sql = mysqli_query($con, "SELECT * FROM kids_clubs WHERE club_id = $row_target[club_id] AND kid_id = $kid_id AND subcategory <> 0");
	}
	
	}
	
	if($kid_id == 0 && $parent_id <> "0"){
	if($row_target['club_id'] <> "0"){
	//list subcategories where parent is member from
	$sql = mysqli_query($con, "SELECT * FROM parents_clubs WHERE club_id = $row_target[club_id] AND parent_id = $parent_id AND subcategory <> 0");
	}
	
	}
	
	$id_list_subcategories[] = "";
	if(mysqli_num_rows($sql) > 0){
		while($row = mysqli_fetch_array($sql)){
			$id_list_subcategories[] = $row['subcategory'];
		}
	}
	
	
	$cross_subcategories=array_intersect($id_list_subcategories,$target);
	$cross_subcategories = implode(',', $cross_subcategories);
	
	if($row_target['club_id'] <> "0"){
	$sql = mysqli_query($con, "SELECT SUM(chat) AS total_chat FROM clubs_categories WHERE club_id = $row_target[club_id] AND id IN($cross_subcategories)");
	}
	
	if(mysqli_num_rows($sql) > 0){
		$row = mysqli_fetch_array($sql);
		if($row['total_chat'] > 0){
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
	}

function dirToOptions($path = __DIR__, $level = 0) {
    $items = scandir($path);
    foreach($items as $item) {
        // ignore items strating with a dot (= hidden or nav)
        if (strpos($item, '.') === 0) {
            continue;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $item;
        //cut off first 2 points in relative path (Edit Simon 271118)
        if(substr( $fullPath, 0, 2 ) === ".."){ 
        $value = substr($fullPath, 2);
        }
        else{
	        $value = $fullPath;
        }
        
        // add some whitespace to better mimic the file structure
        $item = str_repeat('&nbsp;', $level * 3) . $item;
        // file
        if (is_file($fullPath)) {
            echo "<option value='$value'>$item</option>";
        }
        // dir
        else if (is_dir($fullPath)) {
            // immediatly close the optgroup to prevent (invalid) nested optgroups
            echo "<optgroup label='$item'></optgroup>";
            // recursive call to self to add the subitems
            dirToOptions($fullPath, $level + 1);
        }
    }

}


function MemberData($kid_id,$parent_id,$manual_id,$superuser_id,$superuser,$subcategory_id)
{
	global $con;
	global $pass;
	
	$key_code_club = 0;
	
	if($kid_id <> 0){ 
		$name = userValueKid($kid_id, "name") . " " . userValueKid($kid_id, "surname");
		$firstname = userValueKid($kid_id, "name");
		$lastname = userValueKid($kid_id, "surname");
		$birthdate = userValueKid($kid_id, "birthdate");
		
		if(userValueKid($kid_id, "avatar") <> ""){
		$avatar = userValueKid($kid_id, "avatar");
		}
		else {
		$avatar = "";
		}
		
		$parent = explode(',', JoinedParentsFromParentId(userValueKid($kid_id, "parent")));
		$contactinfo = array();
		foreach($parent AS $parents){ 
		$contactinfo[] = array("contact_id" => $parents, "contact_name" => userValue($parents, "username"), "contact_phone" => userValue($parents, "Telefoonnummer"), "contact_email" => userValue($parents, "email"));
		}
		
		$contact_name = userValue(userValueKid($kid_id, "parent"), "username");
		$contact_phone = userValue(userValueKid($kid_id, "parent"), "Telefoonnummer");
		$contact_phone2 = "nvt";
		$contact_mail = userValue(userValueKid($kid_id, "parent"), "email");

		$string1 = "kid_id = $kid_id";
		
		$superusers = array("club");
		if(in_array($superuser, $superusers)){
		$table1 = "kids_" . $superuser . "s";
		
		//added as contact person
		$subcategory_contact_list = "0";
		$subcategory_contact_id_list = "0";
		
		//added in subgroups
		$check_added = mysqli_query($con,"SELECT * FROM kids_" . $superuser . "s WHERE " . $superuser . "_id = $superuser_id AND kid_id = $kid_id AND subcategory <> 0 AND subcategory IN (SELECT id FROM " . $superuser . "s_categories)");
		if(mysqli_num_rows($check_added) > 0){
			while($row_added = mysqli_fetch_array($check_added)){
				$subcategories[] = userValueSubcategory($row_added['subcategory'], $superuser, "cat_name");
				$subcategories_id[] = $row_added['subcategory'];
			}
			$subcategory_list = implode(", ", $subcategories);
			$subcategory_id_list = implode(",", $subcategories_id);	
		}
		else {
			$subcategory_list = "0";
			$subcategory_id_list = "0";
		}
		
		//approved GDPR?
		$GDPR = 0;
		$sql_GDPR = mysqli_query($con,"SELECT * FROM kids_" . $superuser . "s WHERE kid_id = $kid_id AND " . $superuser . "_id = $superuser_id ORDER BY ApproveFace_timestamp DESC");
		if(mysqli_num_rows($sql_GDPR) > 0){
			$row_GDPR = mysqli_fetch_array($sql_GDPR);
			if(($row_GDPR['ApproveFace'] == 0) && ($row_GDPR['ApproveFace_timestamp'] == 0)){
			 $GDPR = "nog geen goedkeuring gegeven";
		 	  }
		 	  if(($row_GDPR['ApproveFace'] == 0) && ($row_GDPR['ApproveFace_timestamp'] <> 0)){
		     	  	 $GDPR = "<u>geen</u> goedkeuring gegeven op " . date('d-m-Y', $row_GDPR['ApproveFace_timestamp']);
		 	  }
		 	  if(($row_GDPR['ApproveFace'] == 1) && ($row_GDPR['ApproveFace_timestamp'] <> 0)){
		     	  	  $GDPR = "goedkeuring gegeven op " . date('d-m-Y', $row_GDPR['ApproveFace_timestamp']);
		 	  }
		}	
		}

	}
	
	if($parent_id <> 0){ 
		$name = userValue($parent_id, "username");
		
		$fullname = userValue($parent_id, "username");
		$fullname = trim($fullname); // remove double space
		if(strpos($fullname, ' ') !== FALSE){
		$firstname = substr($fullname, 0, strpos($fullname, ' '));
		$lastname = substr($fullname, strpos($fullname, ' '), strlen($fullname));
		}
		else {
		$firstname = $fullname;
		$lastname = "";
		}

		$avatar = userValue($parent_id, "avatar");
		$birthdate = "n/a";
	
		$parent = explode(',', JoinedParentsFromParentId($parent_id));
		$contactinfo = array();
		foreach($parent AS $parents){ 
		$contactinfo[] = array("contact_id" => $parents, "contact_name" => userValue($parents, "username"), "contact_phone" => userValue($parents, "Telefoonnummer"), "contact_email" => userValue($parents, "email"));
		}
		
		$contact_name = userValue($parent_id, "username");
		$contact_phone = userValue($parent_id, "Telefoonnummer");
		$contact_phone2 = "nvt";
		$contact_mail = userValue($parent_id, "email");
		
		$string1 = "parent_id = $parent_id";
		
		$superusers = array("club");
		if(in_array($superuser, $superusers)){ 
		$table1 = "parents_" . $superuser . "s";
		
		//added as contact person
		$check_link = mysqli_query($con,"SELECT * FROM " . $superuser . "s_share 
		WHERE " . $superuser . "_id = $superuser_id AND user_id = $parent_id
		AND subcategory_id IN (SELECT id FROM " . $superuser . "s_categories)");
		$numrows_check_link = mysqli_num_rows($check_link);
		if($numrows_check_link > 0){
			while($row_link = mysqli_fetch_array($check_link)){
				$subcategories_contact[] = userValueSubcategory($row_link['subcategory_id'], $superuser, "cat_name");
				$subcategories_contact_id[] = $row_link['subcategory_id'];
			}
			$subcategory_contact_list = implode(", ", $subcategories_contact);
			$subcategory_contact_id_list = implode(", ", $subcategories_contact_id);		
		}
		else {
			$subcategory_contact_list = "0";
			$subcategory_contact_id_list = "0";
		}
		
		//added in subgroups
		$check_added = mysqli_query($con,"SELECT * FROM parents_" . $superuser . "s WHERE " . $superuser . "_id = $superuser_id AND parent_id = $parent_id AND subcategory <> 0 AND subcategory IN (SELECT id FROM " . $superuser . "s_categories)");
		if(mysqli_num_rows($check_added) > 0){
			while($row_added = mysqli_fetch_array($check_added)){
				$subcategories[] = userValueSubcategory($row_added['subcategory'], $superuser, "cat_name");
				$subcategories_id[] = $row_added['subcategory'];
			}
			$subcategory_list = implode(", ", $subcategories);
			$subcategory_id_list = implode(",", $subcategories_id);		
		}
		else {
			$subcategory_list = "0";
			$subcategory_id_list = "0";
		}
		
		//approved GDPR?
		$GDPR = 0;
		$sql_GDPR = mysqli_query($con,"SELECT * FROM parents_" . $superuser . "s WHERE parent_id = $parent_id AND " . $superuser . "_id = $superuser_id ORDER BY ApproveFace_timestamp DESC");
		if(mysqli_num_rows($sql_GDPR) > 0){
			$row_GDPR = mysqli_fetch_array($sql_GDPR);
			if(($row_GDPR['ApproveFace'] == 0) && ($row_GDPR['ApproveFace_timestamp'] == 0)){
			 $GDPR = "nog geen goedkeuring gegeven";
		 	  }
		 	  if(($row_GDPR['ApproveFace'] == 0) && ($row_GDPR['ApproveFace_timestamp'] <> 0)){
		     	  	 $GDPR = "<u>geen</u> goedkeuring gegeven op " . date('d-m-Y', $row_GDPR['ApproveFace_timestamp']);
		 	  }
		 	  if(($row_GDPR['ApproveFace'] == 1) && ($row_GDPR['ApproveFace_timestamp'] <> 0)){
		     	  	  $GDPR = "goedkeuring gegeven op " . date('d-m-Y', $row_GDPR['ApproveFace_timestamp']);
		 	  }
		
		}
		}	
	}
	
	if($manual_id <> 0){
		
		$avatar = "";
		$birthdate = "n/a";
		$superusers = array("club");
		if(in_array($superuser, $superusers)){
		if($superuser == "club"){
			$key_code_club = 0;
			if(userValueManual($manual_id, $superuser, $superuser_id, "key_code")){
			$key_code_club = userValueManual($manual_id, $superuser, $superuser_id, "key_code");
			}
		}
		$name = userValueManual($manual_id, $superuser, $superuser_id, "name") . " " . userValueManual($manual_id, $superuser, $superuser_id, "surname");
		$firstname = userValueManual($manual_id, $superuser, $superuser_id, "name");
		$lastname = userValueManual($manual_id, $superuser, $superuser_id, "surname");
		$get_contact = mysqli_query($con,"SELECT AES_DECRYPT(username, SHA1('$pass')) AS username, AES_DECRYPT(email, SHA1('$pass')) AS email, AES_DECRYPT(Telefoonnummer, SHA1('$pass')) AS Telefoonnummer, AES_DECRYPT(Telefoonnummer2, SHA1('$pass')) AS Telefoonnummer2, AES_DECRYPT(member_medical_x, SHA1('$pass')) AS member_medical, AES_DECRYPT(member_emergency_x, SHA1('$pass')) AS member_emergency, AES_DECRYPT(member_notes_x, SHA1('$pass')) AS member_notes, member_instrument, member_number, member_category, ApproveFace, ApproveFace_timestamp FROM kids_" . $superuser . "s_manual WHERE id = $manual_id");
		}
		$row_contact = mysqli_fetch_array($get_contact);
		
		$contactinfo = array("contact_id" => $manual_id, "contact_name" => $row_contact['username'], "contact_phone" => $row_contact['Telefoonnummer'], "contact_phone2" => $row_contact['Telefoonnummer2'], "contact_email" => $row_contact['email']);
		
		$contact_name = $row_contact['username'];
		$contact_phone = $row_contact['Telefoonnummer'];
		$contact_phone2 = $row_contact['Telefoonnummer2'];
		$contact_mail = $row_contact['email'];
		//member number and category
		$member_number = $row_contact['member_number'];
		$member_category = $row_contact['member_category'];
		$member_medical = $row_contact['member_medical'];
		$member_emergency = $row_contact['member_emergency'];
		$member_notes = $row_contact['member_notes'];
		
		//added as contact person
		$check_link = mysqli_query($con,"SELECT * FROM " . $superuser . "s_contact 
		WHERE " . $superuser . "_id = $superuser_id AND AES_DECRYPT(cat_contact_email_x, SHA1('$pass')) = '$contact_mail'");
		$numrows_check_link = mysqli_num_rows($check_link);
		if($numrows_check_link > 0){
			while($row_link = mysqli_fetch_array($check_link)){
				$subcategories_contact[] = userValueSubcategory($row_link['subcategory_id'], $superuser, "cat_name");
				$subcategories_contact_id[] = $row_link['subcategory_id'];
			}
			$subcategory_contact_list = implode(", ", $subcategories_contact);
			$subcategory_contact_id_list = implode(", ", $subcategories_contact_id);		
		}
		else {
			$subcategory_contact_list = "0";
			$subcategory_contact_id_list = "0";
		}

		
		//added in subgroups
		$key_code = userValueManual($manual_id, $superuser, $superuser_id, "key_code");
		$check_added = mysqli_query($con,"SELECT * FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id = $superuser_id AND key_code = '$key_code' AND subcategory <> 0");
		if(mysqli_num_rows($check_added) > 0){
			while($row_added = mysqli_fetch_array($check_added)){
				$subcategories[] = userValueSubcategory($row_added['subcategory'], $superuser, "cat_name");
				$subcategories_id[] = $row_added['subcategory'];
			}
			$subcategory_list = implode(", ", $subcategories);
			$subcategory_id_list = implode(",", $subcategories_id); 		
		}
		else {
			$subcategory_list = "0";
			$subcategory_id_list = "0";
		}

		
		if(($row_contact['ApproveFace'] == 0) && ($row_contact['ApproveFace_timestamp'] == 0)){
		 $GDPR = "nog geen goedkeuring gegeven";
	 	  }
	 	  if(($row_contact['ApproveFace'] == 0) && ($row_contact['ApproveFace_timestamp'] <> 0)){
	     	  	 $GDPR = "<u>geen</u> goedkeuring gegeven op " . date('d-m-Y', $row_contact['ApproveFace_timestamp']);
	 	  }
	 	  if(($row_contact['ApproveFace'] == 1) && ($row_contact['ApproveFace_timestamp'] <> 0)){
	     	  	  $GDPR = "goedkeuring gegeven op " . date('d-m-Y', $row_contact['ApproveFace_timestamp']);
	 	  }


	} 	
		
	if(($kid_id <> 0) || ($parent_id <> 0)){
	//member number and category
	$member_number = "geen";
	$member_category = "geen";
	$member_medical = "geen";
	$member_emergency = "geen opgegeven!";
	$member_notes = "geen";
	$sql_nr = mysqli_query($con, "SELECT member_number, member_category, AES_DECRYPT(member_medical_x, SHA1('$pass')) AS member_medical, AES_DECRYPT(member_emergency_x, SHA1('$pass')) AS member_emergency, AES_DECRYPT(member_notes_x, SHA1('$pass')) AS member_notes FROM $table1 WHERE $string1 AND " . $superuser . "_id = $superuser_id");


	if(mysqli_num_rows($sql_nr) > 0){ 
	$row_nr = mysqli_fetch_array($sql_nr);
	if($row_nr['member_number'] <> ""){
		$member_number = $row_nr['member_number'];
	}
	if($row_nr['member_category'] <> ""){
		$member_category = $row_nr['member_category'];
	}
	if($row_nr['member_medical'] <> ""){
		$member_medical = $row_nr['member_medical'];
	}
	if($row_nr['member_emergency'] <> ""){
		$member_emergency = $row_nr['member_emergency'];
	}
	if($row_nr['member_notes'] <> ""){
		$member_notes = $row_nr['member_notes'];
	}
	}
	}
	
	if($superuser == "club"){
	//instrument owner
	$owner = ShowValueInstrumentsClub($kid_id,$parent_id,$manual_id,$superuser_id,$subcategory_id,"owner");
     if($owner == "1"){
         $owner = "Geen eigenaar";
     }
     if($owner == "0"){
         $owner = "Eigenaar";
     }
     $instrument = ShowValueInstrumentsClub($kid_id,$parent_id,$manual_id,$superuser_id,$subcategory_id,"instrument");
     $brand = ShowValueInstrumentsClub($kid_id,$parent_id,$manual_id,$superuser_id,$subcategory_id,"brand");
     $serial = ShowValueInstrumentsClub($kid_id,$parent_id,$manual_id,$superuser_id,$subcategory_id,"serial");
     $value = ShowValueInstrumentsClub($kid_id,$parent_id,$manual_id,$superuser_id,$subcategory_id,"value");
    }
    else {
	    $owner = "";
	    $instrument = "";
	    $brand = "";
	    $serial = "";
	    $value = "";
	    
    } 
	
    return [ 
    'name' => $name,
	'firstname' => $firstname,
	'lastname' => $lastname,
    'birthdate' => $birthdate,
    'avatar' => $avatar,
    'contactinfo' => $contactinfo, 
    'contact_name' => $contact_name,
    'contact_phone' => $contact_phone,
    'contact_phone2' => $contact_phone2,
    'contact_mail' => $contact_mail,
    'added_as_contact' => $subcategory_contact_list,
    'added_as_contact_id' => $subcategory_contact_id_list,
    'added_as_member' => $subcategory_list,
    'added_as_member_id' => $subcategory_id_list,
    'GDPR' => $GDPR,
    'address' => showHomeAddress($kid_id, $parent_id, $key_code_club),
    'member_number' => $member_number,
    'member_category' => $member_category,
    'member_medical' => $member_medical,
    'member_emergency' => $member_emergency,
    'member_notes' => $member_notes,
    'instrument' => $instrument,
    'brand' => $brand,
    'serial' => $serial,
	'value' => $value,
	'owner' => $owner
    ];

    
}

function Shortcut($user_id)
{
	global $con;
	$check = mysqli_query($con, "SELECT * FROM users_shortcut WHERE user_id = $user_id AND activated = 0");
	if(mysqli_num_rows($check) > 0){
		$row_shortcut = mysqli_fetch_array($check);
		
	return [ 
    'shortcut_club' => $row_shortcut['club_id'],
    'subcategory_suggestion' => $row_shortcut['subcategory_id'],    
    ];

	}
	else {
		return false;
	}

}

function addtoString($str, $item) {
    $parts = explode(',', $str);
    $parts[] = $item;
    return implode(',', $parts);
}

function removeFromString($str, $item, $deliminator = ',') {
    $parts = explode($deliminator, $str);
    while(($i = array_search($item, $parts)) !== false) {
        unset($parts[$i]);
    }
    return implode($deliminator, $parts);
}

function addhttp($url) { 
    //if no http(s) added, add https
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "https://" . $url;
    }
	//if http added, convert to https
	$url = str_replace("http://", "https://", $url);
	//add blank=1 to make android app open in browser (see Android App Code in Android Studio)
	if (false !== strpos($url, '?')) {
		$url = $url . "&blank=1";
	}else{
		$path = parse_url($url, PHP_URL_PATH);       // get path from url
		$extension = pathinfo($path, PATHINFO_EXTENSION);
		if($extension == null){
		$url = $url . "/?blank=1";
		}
		else {
		$url = $url . "?blank=1";	
		}
	}
    return $url;
}

function SwitchAccounts($uid, $token_rememberme, $mac){			
global $con;
global $full_path;

	//step 1: logout
	// Unset and destroy the session
	unset($_SESSION['uid']);
	unset($_SESSION['ip']);
	session_destroy();

	// Unset and destroy the last remembered page
	unset($_COOKIE['last_url']);
	setcookie("last_url", null, time()-3600, '/');
	
	unset($_COOKIE['rememberme']);
	setcookie("rememberme", null, time()-3600, '/');

	//step 2: switch to another account (login)			
	$ip = $_SERVER['REMOTE_ADDR'];
	
	$bancheck = mysqli_query($con,"SELECT * FROM bans WHERE uid='$uid'");
	// Check if the user is banned or if the user isn't active
	if(mysqli_num_rows($bancheck) > 0) {
		echo "<h5 class='text-center red'>". $m['you_are_banned'] ."</h5>";
	} else {

		if (hash_equals(hash_hmac('sha256', $uid . ':' . $token_rememberme, '_P1aYd8y_'), $mac)) {	
		$last_login = time();
		
		// Add needed session data
		$_SESSION['uid'] = $uid;
		$_SESSION['ip'] = $ip;
		
		mysqli_query($con,"UPDATE users SET last_login='$last_login' WHERE id='$uid'"); // Update last login	
		$getuser = mysqli_query($con,"SELECT * FROM users WHERE id='$uid' AND rememberme = AES_ENCRYPT('$token_rememberme', SHA1('_P1aYd8y_'))");
		$gu = mysqli_fetch_array($getuser);
		
		$token_rememberme = $gu['email']; // generate a token, should be 128 - 256 bit
		$storeTokenForUser = mysqli_query($con,"UPDATE users SET rememberme = AES_ENCRYPT('$token_rememberme', SHA1('_P1aYd8y_')) WHERE id='$uid'");
		$cookie = $uid . ':' . $token_rememberme;
		$mac = hash_hmac('sha256', $cookie, '_P1aYd8y_');
		$cookie .= ':' . $mac;
		setcookie('rememberme', $cookie, time()+15552000, '/');
		
		$numrows = mysqli_num_rows($getuser);	
		if($numrows > 0)
		{ 		
		header('Location: ' . $full_path);
		}
		}
		else {
			echo "fout";
		}
		
		if(isset($_COOKIE['last_url'])){ 
		$last_url = $_COOKIE['last_url'];
		setcookie("last_url", "", time() - 3600);
		unset($_COOKIE['last_url']); // Delete last URL cookie to avoid infinite redirections if the user is not allowed to visit the URL
		}
		
		// Check if log successful logins is enabled, if so, log this login try
		if(getSetting("log_successful_logins", "text") == "true") {
			addLog("1", $_SERVER['REMOTE_ADDR'], $uid, $token_rememberme, "website");
		}
	}
}

function SyncResultsTrue($sync_url, $sync_method){
	require_once('plugins/iCalReader/class.iCalReader.php');
	$sync_url = str_replace( 'webcal://', 'https://', $sync_url);
	if (!preg_match("~^(?:f|ht)tps?://~i", $sync_url)) {
		$sync_url = "https://" . $sync_url;
	}
	//if http added, convert to https
	$sync_url = str_replace("http://", "https://", $sync_url);
	$ical   = new ICal($sync_url);
	
	$now = date('Y-m-d H:i:s');//current date and time
	$today = date('Y-m-d');
	
	$contents = @file($sync_url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if (stristr($contents[0], 'BEGIN:VCALENDAR') === false) {
		return 0;
	}
	else { 
	if($ical->hasEvents()){ 
	$events = $ical->eventsFromRange(true, true);
	if($ical->event_count <> 0){
		return 1;
	}
	else {
		return 2;
	}
	}
	else {
		return 2;
	}
	}
}

function SyncResults($sync_url, $sync_method){
	require_once('plugins/iCalReader/class.iCalReader.php');
	$sync_url = str_replace( 'webcal://', 'https://', $sync_url);
	if (!preg_match("~^(?:f|ht)tps?://~i", $sync_url)) {
		$sync_url = "https://" . $sync_url;
	}
	//if http added, convert to https
	$sync_url = str_replace("http://", "https://", $sync_url);
	
	$now = date('Y-m-d H:i:s');//current date and time
	$today = date('Y-m-d');
	
	$ical   = new ICal($sync_url);
	
	if($ical->hasEvents()){ 
	//show only events from now to January the 29th, 2038 (UNIX timestamp)
	$events = $ical->eventsFromRange(true, true);
	
	if($ical->event_count <> 0){
		echo "<table class='table table-options'>";
		if(($sync_method == "Google")){
		echo "<tr><td style='background-color: black; color: white'>Startdatum</td><td style='background-color: black; color: white'>Einddatum</td><td style='background-color: black; color: white'>Omschrijving</td><td style='background-color: black; color: white'>Locatie</td>";	
		}
	foreach ($events as $event) {
		
		if(($sync_method == "KBVB") || ($sync_method == "foot24") || ($sync_method == "badmintonvlaanderen") || ($sync_method == "hockeybelgium")){
		
		$matchdatestart = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTSTART']));
    	$matchdateend = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTEND']));
    	
    	
		$tablerow = "<tr><td>$matchdatestart</td><td>$matchdateend</td>"; 
		if(isset($event['LOCATION'])){
		$tablerow .= "<td>" . mres($event['LOCATION']) . "</td>";
    	}
		$tablerow .= "<td>$event[SUMMARY]</td>";
		
		echo $tablerow;
		}
		
		
		
		if($sync_method == "volleyscores"){
		
		//add 2 hours (zie mail kevin degryse van volleyscores)
		$matchdatestart = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTSTART']));
    	$matchdateend = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTEND']));
    		
		echo "<tr><td>$matchdatestart</td><td>$matchdateend</td><td>" . mres($event['LOCATION']) . "</td><td>" . mres($event['SUMMARY']) . "</td>";
	
	}
		
		if(($sync_method == "Google")){
		if(isset($event['DTSTART']) && (isset($event['DTEND']))){ 
		$startdate = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTSTART']));
		$enddate = date('Y-m-d H:i:s', $ical->iCalDateToUnixTimestamp($event['DTEND']));
	
    	$summary = $event['SUMMARY'];
    	$summary = str_replace('\\', '', $summary);
    	
    	if(isset($event['LOCATION'])){
    	$location = $event['LOCATION'];
    	$location = str_replace('\\', '', $location); 
    	}
    	else {
	    	$location = "";
    	}
	
		echo "<tr><td>$startdate</td><td>$enddate</td><td>$summary</td><td>$location</td>";
	
		}
		}
	
	}
 		echo "</table>";
	}	
	}	
}

function CountPlannedMails($superuser_id, $superuser, $subcategory_id, $board = "0"){
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){ 
	if($board == "0"){
		$string1 = "AND subcategory_id = $subcategory_id AND board = 0";
		$string2 = "AND (ForAll IN(0,3) AND subcategory = $subcategory_id OR ForAll = 1 OR ForAll = 2 AND FIND_IN_SET($subcategory_id, ForSome))";
	}
	if($board == "1"){
		if(is_allowedLevel("2")){
		$string1 = "";
		$string2 = "";
		}
		else {
		$string1 = "AND subcategory_id = $subcategory_id AND board = 0";
		$string2 = "AND (ForAll IN(0,3) AND subcategory = $subcategory_id OR ForAll = 1 OR ForAll = 2 AND FIND_IN_SET($subcategory_id, ForSome))";	
		}
		}

	//count manual mails
	$check_planned_manual = mysqli_query($con, "SELECT id FROM mails WHERE " . $superuser . "_id = $superuser_id $string1 AND senddate > CURDATE()");
	$num_planned_manual = mysqli_num_rows($check_planned_manual);
	
	$check_planned_calendar = mysqli_query($con, "SELECT id FROM calendar WHERE " . $superuser . "_id = $superuser_id AND delay_type <> 0 AND DATE(startdate) - INTERVAL delay_type DAY > CURDATE() $string2");
	$num_planned_calendar = mysqli_num_rows($check_planned_calendar);
	
	return $num_planned_manual + $num_planned_calendar;
	}
	else {
		return false;
	}
}
  

function getContrastColor($hexColor) {
		
		if($hexColor == ''){
			return '#000000'; 
		}
		else {
        // hexColor RGB
        $R1 = hexdec(substr($hexColor, 1, 2));
        $G1 = hexdec(substr($hexColor, 3, 2));
        $B1 = hexdec(substr($hexColor, 5, 2));

        // Black RGB
        $blackColor = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

         // Calc contrast ratio
         $L1 = 0.2126 * pow($R1 / 255, 2.2) +
               0.7152 * pow($G1 / 255, 2.2) +
               0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
              0.7152 * pow($G2BlackColor / 255, 2.2) +
              0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
        }

        // If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000000';
        } else { 
            // if not, return white color.
            return '#FFFFFF';
        }
	}
}

function listTitles($superuser_id, $superuser, $subcategory_id, $sync_url = "", $sync_method = "", $action = "changetitle") {
	global $con;
	
	$superusers = array("club");
	if(in_array($superuser, $superusers)){						    
    	//hex colors default blocks
    	$sql = mysqli_query($con, "SELECT * FROM " . $superuser . "s_categories WHERE " . $superuser . "_id = $superuser_id AND id IN ($subcategory_id)");
		
    	if(mysqli_num_rows($sql) > 0){
		while ($row = mysqli_fetch_array($sql)){
		if($row['hex_color_default'] <> ""){
		$hex_color_default = "#" . $row['hex_color_default'];
		}
		else {
			$hex_color_default = "#663300";
		}
		
		if($row['hex_color_default2'] <> ""){
		$hex_color_default2 = "#" . $row['hex_color_default2'];
		}
		else {
			$hex_color_default2 = "#ec971f";
		}
     	?>
    	<li><a role="button" class="<?php echo $action ?>" subcategory_attr="<?php echo $row['id'] ?>" sync_url_attr="<?php echo $sync_url ?>" name="<?php echo $sync_method ?>" id="<?php echo strtoupper($superuser) ?>1" color_attr_back="<?php echo $hex_color_default ?>" color_attr_front="<?php echo getContrastColor($hex_color_default) ?>"><span class="fa fa-square-full" style="color: <?php echo $hex_color_default ?>"></span> <?php echo $row['title_default'] ?></a></li>
    	<li><a role="button" class="<?php echo $action ?>" subcategory_attr="<?php echo $row['id'] ?>" sync_url_attr="<?php echo $sync_url ?>" name="<?php echo $sync_method ?>" id="<?php echo strtoupper($superuser) ?>2" color_attr_back="<?php echo $hex_color_default2 ?>" color_attr_front="<?php echo getContrastColor($hex_color_default2) ?>"><span class="fa fa-square-full" style="color: <?php echo $hex_color_default2 ?>"></span> <?php echo $row['title_default2'] ?></a></li>
    	<?php }
		}
		//check for extra blocks
		$extra_sql = mysqli_query($con, "SELECT * FROM " . $superuser . "s_default_blocks WHERE " . $superuser . "_id = $superuser_id AND subcategory_id IN ($subcategory_id) ORDER BY id ASC");
		if(mysqli_num_rows($extra_sql) > 0){
		while($row_extra = mysqli_fetch_array($extra_sql)){ ?>
			<li><a role="button" class="<?php echo $action ?>" subcategory_attr="<?php echo $row_extra['subcategory_id'] ?>" sync_url_attr="<?php echo $sync_url ?>" name="<?php echo $sync_method ?>" id="user<?php echo $row_extra['id'] ?>" color_attr_back="#<?php echo $row_extra['hex_color'] ?>" color_attr_front="<?php echo getContrastColor("#" . $row_extra['hex_color']) ?>"><span class="fa fa-square-full" style="color: #<?php echo $row_extra['hex_color'] ?>"></span> <?php echo $row_extra['title'] ?></a></li>
	    	<?php 
		}
    	}
    	}
    	else {
	    	return false;
    	} 
}

function checkRemoteFile($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    // don't download content
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);
    curl_close($ch);
    if($result !== FALSE)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function array_flatten($array) {

   $return = array();
   foreach ($array as $key => $value) {
       if (is_array($value)){ $return = array_merge($return, array_flatten($value));}
       else {$return[$key] = $value;}
   }
   return $return;

}

function trim_all( $str , $what = NULL , $with = ' ' )
{
    if( $what === NULL )
    {
        //  Character      Decimal      Use
        //  "\0"            0           Null Character
        //  "\t"            9           Tab
        //  "\n"           10           New line
        //  "\x0B"         11           Vertical Tab
        //  "\r"           13           New Line in Mac
        //  " "            32           Space
       
        $what   = "\\x00-\\x20";    //all white-spaces and control chars
    }
   
    return trim( preg_replace( "/[".$what."]+/" , $with , $str ) , $what );
}

function ListPostals($country){
	global $con;
	if($country == "BE"){
		$query_array = mysqli_query($con, "SELECT zip_code FROM postal");
		while($row_array=mysqli_fetch_array($query_array)){
		$codes[] = $row_array['zip_code'];
	}
	}
	return json_encode($codes);
}

function FileList($row_document, $superuser, $superuser_id, $edit = false, $icon = true){
	global $con;
	global $full_path;
	global $data_path;
	global $home_path;
	
	require_once 'plugins/Mobile_Detect/Mobile_Detect.php';
	$detect = new Mobile_Detect;
	/* Set locale to Dutch */
	setlocale(LC_ALL, 'nl_NL');
	
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
		
		$name = $row_document['name'];
		$filetype = $row_document['filetype'];
		$tags = $row_document['tags'];
		$audioplayer = "";
		$images = "";
		$link = "";
		$link_body = "";
		$comments = "";
		$filesattachments = "";
		$showtags = "";
		$name_type = "";
		$placement = "";
		$date_published = "";
		
		//name filetype
		if($filetype == "0"){
			$author = "Auteur:";
			$name_date_published = "Datum opmaak:";
			$name_type = "Document";
		}
		if($filetype == "1"){
			$author = "Auteur:";
			$name_date_published = "Datum opmaak:";
			$name_type = "Link/URL";
		}
		if($filetype == "2"){
			$author = "Fotograaf:";
			$name_date_published = "Datum foto's:";
			$name_type = "Fotoalbum";
		}
		if($filetype == "3"){
			$author = "Uitvoerder:";
			$name_date_published = "Datum opname:";
			$name_type = "Video";
		}
		if($filetype == "4"){
			$author = "Uitvoerder:";
			$name_date_published = "Datum opname:";
			$name_type = "Audio";
		}
		if($filetype == "5"){
			$author = "Auteur/Componist/Uitvoerder:";
			$name_date_published = "Datum gepubliceerd:";
			$name_type = "Catalogus";
		}
		
		//date added
		$date_added = date("Y-m-d",strtotime($row_document['date']));
		$date_added = formatDate($date_added, "dd LLLL yyyy");
		
		//date published
		if($row_document['date_published'] <> '' && $row_document['date_published'] <> '0000-00-00'){ 
		$date_published = date("Y-m-d",strtotime($row_document['date_published']));
		$date_published = formatDate($date_published, "dd LLLL yyyy");
		}
		
		
		
		//show tags
		if($tags <> ""){ 
		$sql_tags = mysqli_query($con,"SELECT title FROM " . $superuser . "s_tags WHERE id IN($tags) AND " . $superuser . "_id = $superuser_id AND title <> '' ORDER BY title ASC");
		if(!$sql_tags){
				error_log("Error description: SELECT title FROM " . $superuser . "s_tags WHERE id IN($tags) AND " . $superuser . "_id = $superuser_id AND title <> '' ORDER BY title ASC" . mysqli_error($con), 0);
			}
		if(mysqli_num_rows($sql_tags) > 0) {
			if($icon == true){ 
			$showtags = "<span class='fal fa-tags fa-fw'></span>&nbsp;";
			}
			while($row_tags = mysqli_fetch_array($sql_tags)){ 
			$tgs[] = $row_tags['title'];
			}
			$showtags .= implode(' | ', $tgs);
			}
		}
		
		//name file
		if ($detect->isMobile() ) {
		if (strlen($name) > 25){
			$maxLength = 25;
		$name = substr($name, 0, $maxLength) . "...";
		}
		}
		
		//show comments
		if($row_document['comment'] <> ""){ 
		  $comments =  strip_tags($row_document['comment'], '<a>');
		  $comments = str_replace('<a', '<a target="_blank"', $comments);
		  $comments = nl2br($comments) . "<br>";
		}
		
		//placement
		if ($row_document['forall'] == "1"){
		$placement = "alle subgroepen";
		}
		if ($row_document['forall'] == "2"){
			$list = explode(',',$row_document['forsome']);
			sort($list);
			foreach($list AS $sub){
			if($sub <> 0){ 
			$placement .= userValueSubcategory($sub, $superuser, "cat_name") . " | ";
			}
			}
	  
		}
		if ($row_document['forall'] == "0"){
			$placement = "deze subgroep";
			}
	
		//link						
		$document_id = $row_document['id'];
		$link_body = "<h5>$name</h5></a>";
		$image_files = "";
		//url
		if($row_document['filetype'] == "1"){
		$link = "<a href=\"" . addhttp($row_document['file']) . "\" class=\"location CountClick\" file_id_attr=\"$document_id\" super_attr=\"$superuser\" super_id_attr=\"$superuser_id\" target=\"_blank\">";
		}
		//file
		if($row_document['filetype'] == "0"){
			$ext = pathinfo($row_document['file'] , PATHINFO_EXTENSION);
				if($ext == "gpx"){
				//add blank=1 to end of url to make android app open in browser (see Android App Code in Android Studio)
				$link = "<a href=\"" . $full_path . "/gpx_viewer.php?superuser_id=" . base64_url_encode($superuser_id) . "&superuser=" . base64_url_encode($superuser) . "&document_id=" . base64_url_encode($row_document['id']) . "&blank=1\" class=\"location CountClick\" file_id_attr=\"$document_id\" super_attr=\"$superuser\" super_id_attr=\"$superuser_id\" target=\"_blank\">";
				}
				else { 
		$link = "<a href=\"" . $data_path . "/" . $superuser . "s_documents/$superuser_id/$row_document[file]?blank=1\" class=\"location CountClick\" file_id_attr=\"$document_id\" super_attr=\"$superuser\" super_id_attr=\"$superuser_id\" target=\"_blank\">";
				}
		}
		//image
		if($row_document['filetype'] == "2"){
		if($row_document['isURL'] == "0"){
		$link = "<a class=\"OpenSwipe location CountClick\" file_id_attr=\"$document_id\" super_attr=\"$superuser\" super_id_attr=\"$superuser_id\" id=\"$superuser_id\" name=\"$row_document[file]\">";
		$i = 1;
		
		$images = glob("../data/" . $superuser . "s_images/$superuser_id/$row_document[file]/*");
		foreach ($images as $image) {
			$image_files .= "<a rel=\"gallery-$row_document[file]\" class=\"swipebox location box-$row_document[file]-$i\" href=\"$image\" data-href=\"" . $home_path . "/$image\" title=\"$row_document[name]\"></a>";
			$i++;
		}
		}
		else {
		$link = "<a class=\"location CountClick\" file_id_attr=\"$document_id\" href=\"" . addhttp($row_document['file']) . "\" super_attr=\"$superuser\" super_id_attr=\"$superuser_id\" target=\"_blank\">";
		}
		}					
		//video
		if($row_document['filetype'] == "3"){
		if($row_document['isURL'] == "0"){
		$link = "<a rel=\"gallery-video-$row_document[id]\" class=\"swipebox location CountClick\" file_id_attr=\"$document_id\" super_attr=\"$superuser\" super_id_attr=\"$superuser_id\" href=\"/data/" . $superuser . "s_videos/$superuser_id/$row_document[file]\" data-href=\"" . $data_path . "/" . $superuser . "s_videos/$superuser_id/$row_document[file]?blank=1\" title=\"$row_document[name]\">";
		}
		else {
		$link = "<a href=\"" . addhttp($row_document['file']) . "\" class=\"location CountClick\" file_id_attr=\"$document_id\" super_attr=\"$superuser\" super_id_attr=\"$superuser_id\" target=\"_blank\">";
			}
			}
		//audio
		if($row_document['filetype'] == "4"){
		if($row_document['isURL'] == "0"){
		$link = "<a href=\"" . $data_path . "/" . $superuser . "s_audio/$superuser_id/$row_document[file]?blank=1\" class=\"location CountClick\" file_id_attr=\"$document_id\" super_attr=\"$superuser\" super_id_attr=\"$superuser_id\" target=\"_blank\">";
		}			
		else {
		$link = "<a href=\"" . addhttp($row_document['file']) . "\" class=\"location CountClick\" file_id_attr=\"$document_id\" super_attr=\"$superuser\" super_id_attr=\"$superuser_id\" target=\"_blank\">";
		}
		}
		
		//catalogue
		if($row_document['filetype'] == "5"){
		$link = "<a role='button' class='none'>";
		}
		
		if($row_document['filetype'] == "4"){
		if($row_document['isURL'] == "0"){
			$source =  $data_path . "/" . $superuser . "s_audio/$superuser_id/$row_document[file]?blank=1";
		}
		else {
			$source = addhttp($row_document['file']);
		}
		
		
		//add audioplayer
		$audioplayer = "<audio id=\"$row_document[id]\" class=\"audioplayer\" preload=\"metadata\" preload=\"none\"><source src=\"$source\" type=\"audio/mpeg\"></audio>";
		//$audioplayer .= <button class=\"btn btn-default btn-xs Backward\" id=\"$row_document[id]\"><span class=\"fal fa-backward\" style=\"color:gray\"></span></button>";
		$audioplayer .= "<button class=\"btn btn-default btn-xs Preload\" id=\"$row_document[id]\"><span class=\"fal fa-play\" style=\"color:gray\"></span></button>";
		$audioplayer .= "<button class=\"btn btn-default btn-xs Play\" id=\"$row_document[id]\" style=\"display:none\"><span class=\"fal fa-play\" style=\"color:gray\"></span></button>";
		$audioplayer .= "<button class=\"btn btn-default btn-xs Pause\" id=\"$row_document[id]\" style=\"display:none\"><span class=\"fal fa-pause\" style=\"color:gray\"></span></button>";
		$audioplayer .= "<button class=\"btn btn-default btn-xs Stop\" id=\"$row_document[id]\" style=\"display:none\"><span class=\"fal fa-stop\" style=\"color:gray\"></span></button>";
		//$audioplayer .= "<button class=\"btn btn-default btn-xs Forward\" id=\"$row_document[id]\"><span class=\"fal fa-forward\" style=\"color:gray\"></span></button>";
		}
		
	
		//show extra attachments
		 $sql_share = mysqli_query($con, "SELECT document_slave_id FROM " . $superuser ."s_documents_share WHERE " . $superuser . "_id = $superuser_id AND document_master_id = $row_document[id]");
		 if(mysqli_num_rows($sql_share) > 0){
			 while($row_share = mysqli_fetch_array($sql_share)){
				 $sql_shared_document = mysqli_query($con, "SELECT * FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND id = $row_share[document_slave_id]");
				 if(mysqli_num_rows($sql_shared_document) > 0){
					 //$filesattachments = "<br><strong>Extra bijlages:</strong><br>";
					 $filesattachments .= "<table class='table-calendar'>";
					 while($row_shared_document = mysqli_fetch_array($sql_shared_document)){ 
					 $Sfiles = FileList($row_shared_document, $superuser, $superuser_id, false, false);
					 $filesattachments .= "<tr><td>";
					 $filesattachments .= "" . $Sfiles['link'] . "" . showIconDocumentListIcons($row_shared_document, "30px") . "</a></td>";
					 $filesattachments .= "<td>&nbsp;&nbsp;";
					 if($edit == true){ 
					 $filesattachments .= "<div class=\"dropdown\" style=\"display: inline-block;\">
					 <button class=\"btn btn-default btn-xs dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\"><span class=\"fas fa-times\" aria-hidden=\"true\"></span></button>
					 <ul class=\"dropdown-menu\">
					 <li><a class=\"EditDocument\" id=\"$row_shared_document[id]\"><span class='fal fa-edit pull-right'></span> Bewerk bestand</a></li>
					 <li class='divider'></li>
					 <li><a class=\"FileUpdateAction\" shared_attr=\"$row_share[document_slave_id]\" id=\"$row_document[id]\" type_attr=\"unlinkdocument\"><span class='fal fa-unlink pull-right'></span> Koppel los</a></li>
					 <li><a class=\"FileUpdateAction\" id=\"$row_shared_document[id]\" type_attr=\"deletedocument\"><span class='fal fa-trash-alt pull-right'></span> Verwijder bestand</a></li>
					 </ul></div>";
					 }
					 $filesattachments .= $Sfiles['link'] . "" . $Sfiles['link_body']; 
					 $filesattachments .= $Sfiles['image_files'];
					 if($row_shared_document['filetype'] == "4"){
					 $filesattachments .= $Sfiles['audioplayer'];
					 }
					 $filesattachments .= "<br>" . $Sfiles['comments'] . "</td>";
					 $filesattachments .= "</tr>";
	
	
					 }
					 $filesattachments .= "</table>";
				 }
						  } 
		 }
	
	}
	else {
		return false;
	}	
		return [
		'link' => $link,
		'link_body' => $link_body,
		'image_files' => $image_files,
		'audioplayer' => $audioplayer,
		'author' => $author,
		'name_date_published' => $name_date_published,
		'date_added' => $date_added,
		'date_published' => $date_published,
		'name_type' => $name_type,
		'comments' => $comments,
		'showtags' => $showtags,
		'placement' => $placement,
		'filesattachments' => $filesattachments
		];
}


function isPlacedIn($eventID, $superuser, $superuser_id){
	global $con;
	$sql = mysqli_query($con, "SELECT * FROM calendar WHERE id = $eventID AND " . $superuser . "_id = $superuser_id");
	if(mysqli_num_rows($sql) > 0){
	$row = mysqli_fetch_array($sql);
		if ($row['ForAll'] == "1"){
		return getSubcategoryIdsFromSuperUser($superuser_id, $superuser);
		}
		if ($row['ForAll'] == "2"){
		return $row['ForSome'];
		}
		if(($row['ForAll'] == "0") || ($row['ForAll'] == "3")){
		return $row['subcategory'];
		}
	}
	else {
    	return "0";
	}
}

function CountRecipients($id_list_kids, $id_list_adults, $id_list_manual, $superuser_id, $superuser, $subcategory_id){
	global $con;
	global $pass;
	$parent_list = getParentIdsFromKidIds($id_list_kids, $superuser_id, $superuser, $subcategory_id);
	$adult_list = JoinedParentsFromParentId($id_list_adults, $superuser_id, $superuser, $subcategory_id);
	//BCC are not considered here, because superuser decides this right before sending mail
	
	$result = mysqli_query($con,"SELECT 0 AS manual, id AS adult_id, 0 AS manual_id, username, email, activate_code AS key_code FROM users WHERE id IN ($parent_list,$adult_list)
	UNION
	SELECT 1 AS manual, 0 AS adult_id, id AS manual_id, AES_DECRYPT(username, SHA1('$pass')) AS username, AES_DECRYPT(email, SHA1('$pass')) AS email, key_code FROM kids_" . $superuser . "s_manual WHERE id IN($id_list_manual) AND AES_DECRYPT(email, SHA1('$pass')) <> ''");
	
	return mysqli_num_rows($result);
}

function SendBulkMail($cron, $crontype = 0){
	//$crontype = 0 = no cron, mail sent immediately
	//$crontype = 1 = cronjob day hourly
	
	global $full_path;
	global $con;
	global $pass;
	global $mailpass;
	global $appname;
	global $home_path;
	global $logo_path;
	global $mailhost;
	global $mailfromname;
	global $mailfromaddress;
	global $mailusername;
	global $server_path;
	global $data_path; 
	global $mailtemplate_superuser;
	
	require_once 'plugins/PHPMailer-master/src/Exception.php';
	require_once 'plugins/PHPMailer-master/src/PHPMailer.php';
	require_once 'plugins/PHPMailer-master/src/SMTP.php';

	if ($cron['club_id'] <> 0) {
		$superuser = "club";
		$superuser_id = $cron['club_id'];
	}

	if ($cron['id_list_kids'] == "") {
		$id_list_kids = "0";
	} else {
		$id_list_kids = $cron['id_list_kids'];
	}

	if ($cron['id_list_adults'] == "") {
		$id_list_adults = "0";
	} else {
		$id_list_adults = $cron['id_list_adults'];
	}

	if ($cron['id_list_manual'] == "") {
		$manual_list = "0";
	} else {
		$manual_list = $cron['id_list_manual'];
	}

	$ListBCC = $cron['BCC'];
	$subcategory_id = $cron['subcategory_id'];
	$mail_id = $cron['id'];
	$superuser_name = userValueSuper($superuser_id, $superuser, "username");
	$superuser_avatar = userValueSuper($superuser_id, $superuser, "avatar");
	$timestamp = $cron['timestamp'];
	$mailfrom = $cron['mailfrom'];
	if ($mailfrom == "0") {
		$reply_granted = 0;
	} else {
		$reply_granted = 1;
	}
	$subject = stripslashes($cron['subject']);
	$message_title = stripslashes($subject);
	$message = stripslashes($cron['message']);
	$button_id = $cron['paybutton'];
	$link = "";
	$link2 = "";
	$link3 = "";
	$tracker = "";
	$plus = "";

	$reply_yes = "<small>Deze e-mail werd verstuurd vanuit <a href=\"" . $home_path . "\" target=\"_blank\">" . $appname . "</a>.<br>Klik hieronder om een antwoord te sturen naar de afzender van dit bericht.</small><br><a class=\"answer-button\" href=\"mailto:%reply_to%\"><font color=\"white\"><img src=\"" . $full_path . "/img/reply_white_192x192.png\" style=\"float:left;\" width=\"20px\" alt=''> Beantwoord deze mail</font></a>";
	$reply_no = "<small>Deze e-mail werd verstuurd vanuit <a href=\"" . $home_path . "\" target=\"_blank\">" . $appname . "</a>.<br>Je kan niet reageren op deze mail.</small>";
	$disclaimer = "";

	if (checkPremiumLevel($superuser_id, $superuser, "") == "level 2") {
		$advertising = displayAdvertising($superuser_id, $superuser, "0");
	} else {
		//PUT HERE APP ADVERTISING IN MAIL
		//$advertising = displayAdvertising("0", "0", "1");
		$advertising = "";
	}

	//print details event
	$return_message = "mailID: " . $mail_id . "\r\n " . $superuser . ": " . $superuser_id . "(" . userValueSuper($superuser_id,$superuser,"username") . ")";
	$return_message .= "\r\n Onderwerp: " . $subject . "\r\n ";

	//Get all ids to mail to (joined parents from kids_list and joined parents from adult list)
	$parent_list = getParentIdsFromKidIds($id_list_kids, $superuser_id, $superuser, $subcategory_id, "0");
	$adult_list = JoinedParentsFromParentId($id_list_adults, $superuser_id, $superuser, $subcategory_id, "0");


	$result = mysqli_query($con, "SELECT 0 AS manual, id AS adult_id, 0 AS manual_id, username, email, activate_code AS key_code FROM users WHERE id IN($parent_list,$adult_list)
UNION SELECT 1 AS manual, 0 AS adult_id, id AS manual_id, AES_DECRYPT(username, SHA1('$pass')) AS username, AES_DECRYPT(email, SHA1('$pass')) AS email, key_code FROM kids_" . $superuser . "s_manual WHERE id IN ($manual_list) AND AES_DECRYPT(email, SHA1('$pass')) <> ''");


	//paybutton if button_id is set
	if ((isset($button_id)) && ($button_id <> "0")) {
		//get more information paybutton
		$check_paybuttons = mysqli_query($con, "SELECT * FROM paybuttons
WHERE id = $button_id AND " . $superuser . "_id = $superuser_id");
		if (mysqli_num_rows($check_paybuttons) > 0) {
			$row_paybuttons = mysqli_fetch_array($check_paybuttons);
			$paybuttons = 1;
			$title = $row_paybuttons['title'];
			$type = $row_paybuttons['type'];
		} else {
			$paybuttons = 0;
		}
	} else {
		$paybuttons = 0;
	}


	//Create a new PHPMailer instance
	$mail = new PHPMailer\PHPMailer\PHPMailer(true);
	//Tell PHPMailer to use SMTP
	$mail->isSMTP();
	$mail->CharSet = 'UTF-8';

	$mail->SMTPOptions = array(
		'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true
		)
	);

	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;
	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';
	//Set the hostname of the mail server
	$mail->Host = $mailhost;
	//Set the SMTP port number - likely to be 25, 465 or 587
	$mail->Port = 25;
	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;
	//Username to use for SMTP authentication
	$mail->Username = $mailusername;
	//Password to use for SMTP authentication
	$mail->Password = $mailpass;
	$mail->WordWrap = 50;
	$mail->isHTML(true); // Enable HTML

	//Set who the message is to be sent from
	$mail->setFrom($mailfromaddress, $superuser_name);
	//Set an alternative reply-to address
	$mail->addReplyTo($mailfromaddress, $mailfromname);
	$mail->Subject = $subject;

	//check if attachments in manual exist: if so, send in mail
	//attachments in mail from calendar are already included in 'message'
	if($cron['event_id'] == 0 && $cron['document_id'] == 0){ 
	$attach = mysqli_query($con, "SELECT *, " . $superuser . "_id AS superuser_id FROM " . $superuser . "s_documents WHERE " . $superuser . "_id = $superuser_id AND mail_id = $cron[id]");
	if (mysqli_num_rows($attach) > 0) {
		
		if($crontype == 0){
			$path = "../data/";
		}
		if($crontype == 1){
			$path = dirname(__DIR__, 1). "/data/";
		}
		
		foreach ($attach as $row_attach) {
			if (file_exists($path . $superuser . "s_documents/" . $row_attach['superuser_id'] . "/" . $row_attach['file'])) {
				$mail->AddAttachment($path . $superuser . "s_documents/" . $row_attach['superuser_id'] . "/" . $row_attach['file']);
			}
		}
	}
	}

	foreach ($result as $row) {
		try {
			$mail->addAddress($row['email'], $row['username']);

			//add paybuttons if exist
			if ($paybuttons == "1") {
				
				if ($row['manual'] == 1) {
					$token = generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, 0, 0, $row['manual_id']);
					$link = "<a href=\"" . $full_path . "/superuser_paybuttons_details.php?" . $superuser . "_id=$superuser_id&type=$type&button_id=$button_id&adult_id=0&kid_id=0&manual_id=$row[manual_id]&token=$token\" class=\"link-button\" target=\"_blank\">$title</a>";
				} else {
					$token = generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, $row['adult_id'], 0, 0);
					$link = "<a href=\"" . $full_path . "/superuser_paybuttons_details.php?" . $superuser . "_id=$superuser_id&type=$type&button_id=$button_id&adult_id=$row[adult_id]&kid_id=0&manual_id=0&token=$token\" class=\"link-button\" target=\"_blank\">$title</a>";
				}
			} else {
				$link = "";
			}

			//check for invite on calendar mails
			if ($cron['event_id'] <> 0) {
				$check_invite = mysqli_query($con, "SELECT * FROM calendar WHERE id = $cron[event_id] AND invite = 1 AND canceled = 0 AND " . $superuser . "_id = $superuser_id");
				if (mysqli_num_rows($check_invite) > 0) {
					if ($row['manual'] == 0) {
						$link3 = "<a href=\"" . $full_path . "/openlink.php?deeplink=1&goto_event_id=$cron[event_id]\" class=\"link-button\" target=\"_blank\">Bevestig je aanwezigheid<br>via de " . $appname . "-app</a>";
					} else {
						$link3 = "<a href=\"" . $full_path . "/clubs_calendar_public.php?subcategory_id=" . base64_url_encode($subcategory_id) . "&key_code=$row[key_code]&" . $superuser . "_id=" . base64_url_encode($superuser_id) . "&event_id=$cron[event_id]&startdate=" . DateFromEvent($cron['event_id']) . "&parent_mail=$row[email]\" class=\"link-button\" target=\"_blank\">Bevestig je<br>aanwezigheid</a>";
					}
				}

				//check for survey from calendar (survey will always be 1)
				if ($cron['survey'] == "1") {
					$check_survey = mysqli_query($con, "SELECT * FROM survey_questions WHERE (event_id = $cron[event_id] OR FIND_IN_SET($cron[event_id], event_id_list)) AND " . $superuser . "_id = $superuser_id AND archive = 0");
					if (mysqli_num_rows($check_survey) > 0) {
						if ($row['manual'] == 1) {
							$link2 = "";
						} else {
							$link2 = "<a href=\"" . $full_path . "/openlink.php?deeplink=1&goto_event_id=$cron[event_id]\" class=\"link-button\" target=\"_blank\">Er is een antwoordstrookje<br>bij deze activiteit<br>Bekijk dit via de " . $appname . "-app!</a>";
						}
					}
				}	
			}
			
			//check for survey in manual mail (will always be > 1)
			if ($cron['survey'] > "1") {
				$check_survey = mysqli_query($con, "SELECT * FROM survey_questions WHERE id = $cron[survey] AND " . $superuser . "_id = $superuser_id AND done = 1 AND archive = 0");
				
				if (mysqli_num_rows($check_survey) > 0) {
					if ($row['manual'] == 1) {
						$link2 = "";
					} else {
						$link2 = "<a href=\"" . $full_path . "/openlink.php?deeplink=1&survey_id=$cron[survey]\" class=\"link-button\" target=\"_blank\">Er is een antwoordstrookje<br>te beantwoorden.<br>Bekijk dit via de " . $appname . "-app!</a>";
					}
				}
			}


			//Since the tracking URL is a bit long, I usually put it in a variable of it's own
			if($cron['event_id'] <> 0){
			$tracker = $full_path . "/superuser_mail_record.php?log=true&subcategory_id=$subcategory_id&key_code=$row[key_code]&" . $superuser . "_id=$superuser_id&calendar_id=$cron[event_id]&parent_mail=$row[email]";
			}
			else {
			$tracker = $full_path . "/superuser_mail_record.php?log=true&subcategory_id=$subcategory_id&key_code=$row[key_code]&" . $superuser . "_id=$superuser_id&mail_id=$mail_id&parent_mail=$row[email]";	
			}

			if ($row['manual'] == 0) {
				$disclaimer = "<small>Wilt u deze meldingen niet langer ontvangen? Klik dan <a href=\"" . $full_path . "/openlink.php?deeplink=1&profile=1&goto=BtnEditMails\">hier</a> om uw voorkeuren te bepalen.</small>";
			}


			$message_body = file_get_contents($server_path . '/mail_templates/' . $mailtemplate_superuser);
			$message_body = str_replace('%message_title%', $subject, $message_body);
			$message_body = str_replace('%message%', $message, $message_body);
			$message_body = str_replace('%tracker%', $tracker, $message_body);
			$message_body = str_replace('%plus%', $plus, $message_body);
			$message_body = str_replace('%disclaimer%', $disclaimer, $message_body);
			$message_body = str_replace('%superusername%', $superuser_name, $message_body);
			$message_body = str_replace('%advertising%', $advertising, $message_body);
			$message_body = str_replace('%link%', $link, $message_body);
			$message_body = str_replace('%link2%', $link2, $message_body);
			$message_body = str_replace('%link3%', $link3, $message_body);
			$message_body = str_replace('%full_path%', $full_path, $message_body);		  
			$message_body = str_replace('%appname%', $appname, $message_body);	
			//check if premium level (personalize communication)
			if (checkPremiumLevel($superuser_id, $superuser, "") != "level 0") {
				$message_body = str_replace('%socialfacebook%', GetSocialWebMail($superuser_id, $superuser, "cat_facebook", $subcategory_id), $message_body);
				$message_body = str_replace('%socialwebsite%', GetSocialWebMail($superuser_id, $superuser, "cat_website", $subcategory_id), $message_body);
				if ($superuser_avatar == "") {
					$message_body = str_replace('%avatar%', $logo_path, $message_body);
				} else {
					$message_body = str_replace('%avatar%', $data_path . "/uploads/" . $superuser_avatar, $message_body);
				}
			} //not premium
			else {
				$message_body = str_replace('%socialfacebook%', "", $message_body);
				$message_body = str_replace('%socialwebsite%', "", $message_body);
				$message_body = str_replace('%avatar%', $logo_path, $message_body);
			}
			if ($reply_granted == "1") {
				$message_body = str_replace('%reply%', $reply_yes, $message_body);
				$message_body = str_replace('%reply_to%', $mailfrom, $message_body);
			}
			if ($reply_granted == "0") {
				$message_body = str_replace('%reply%', $reply_no, $message_body);
			}

			$mail->MsgHTML($message_body);

			if (!$mail->send()) {
				$return_message .= "* FOUT: mail niet verstuurd naar " . $row['email'] . " (" . $row['username'] . ")\r\n\r\n";
			} else {
				$return_message .= "* SUCCESS: mail verstuurd naar " . $row['email'] . " (" . $row['username'] . ")\r\n\r\n";
			}
			$mail->clearAddresses();
		} catch (Exception $e) {
			$error_code = $e->errorMessage();
			mysqli_query($con, "INSERT INTO mails_errors (event_id, mail_id, " . $superuser . "_id, email, username, error_code) VALUES ($cron[event_id], $mail_id, $superuser_id, AES_ENCRYPT('$row[email]', SHA1('$pass')),AES_ENCRYPT('$row[username]', SHA1('$pass')), '$error_code')");
			error_log("Error description: " . $error_code . " - " . mysqli_error($con), 0);
		} catch (\Exception $e) {
			$return_message .= $e->getMessage();
		}
	}

	$count_BCC = 0;
	//send copy (BCC)
	if (!empty($ListBCC)) {
		//first send notification to linked contacts - don't do deeplinking: contacts can be contacts without being in a subgroup
		$payload = create_payload_json("Je hebt een e-mail ontvangen van " . $superuser_name . ". Open je e-mailprogramma om deze mail te lezen.");
		$fetchtokens = mysqli_query($con, "SELECT users.id, users.device, users.device_token, " . $superuser . "s_share.*
FROM " . $superuser . "s_share
JOIN users ON users.id = " . $superuser . "s_share.user_id
WHERE " . $superuser . "s_share." . $superuser . "_id = $superuser_id AND " . $superuser . "s_share." . $superuser . "s_contact_id IN($ListBCC)
AND users.device <> 0 AND users.device_token <> '' AND users.device_token <> 'null'");
		if (mysqli_num_rows($fetchtokens) > 0) {
			foreach ($fetchtokens as $row) {
				$user_mobile_info = ['device'=>$row['device'], 'token'=>$row['device_token']];
				send_mobile_notification_request($user_mobile_info, $payload);
			}
		}

		//now send mail, even to those who aren't linked to club (unlinked contacts)
		$get_contacts = mysqli_query($con, "SELECT id, AES_DECRYPT(cat_contact_name_x, SHA1('$pass')) AS cat_contact_name, AES_DECRYPT(cat_contact_email_x, SHA1('$pass')) AS cat_contact_email
FROM " . $superuser . "s_contact
WHERE " . $superuser . "_id = $superuser_id AND id IN($ListBCC)");

		$count_BCC = mysqli_num_rows($get_contacts);

		foreach ($get_contacts as $row_contact) {
			{
				try {
					$mail->addAddress($row_contact['cat_contact_email'], $row_contact['cat_contact_name']);

					$link = ""; //paybuttons not included: n/a
					$tracker = ""; //tracker not included: copy can be sent to non-app-user (contactperson without account)

					$message_body = file_get_contents($server_path . '/mail_templates/' . $mailtemplate_superuser);
					$message_body = str_replace('%message_title%', $subject, $message_body);
					$message_body = str_replace('%message%', $message, $message_body);
					$message_body = str_replace('%tracker%', $tracker, $message_body);
					$message_body = str_replace('%plus%', $plus, $message_body);
					$message_body = str_replace('%disclaimer%', $disclaimer, $message_body);
					$message_body = str_replace('%advertising%', $advertising, $message_body);
					$message_body = str_replace('%superusername%', $superuser_name, $message_body);
					$message_body = str_replace('%link%', $link, $message_body);
					$message_body = str_replace('%link2%', $link2, $message_body);
					$message_body = str_replace('%link3%', $link3, $message_body);
					$message_body = str_replace('%full_path%', $full_path, $message_body);		  
					$message_body = str_replace('%appname%', $appname, $message_body);	

					//check if premium level (personalize communication)
					if (checkPremiumLevel($superuser_id, $superuser, "") != "level 0") {
						$message_body = str_replace('%socialfacebook%', GetSocialWebMail($superuser_id, $superuser, "cat_facebook", $subcategory_id), $message_body);
						$message_body = str_replace('%socialwebsite%', GetSocialWebMail($superuser_id, $superuser, "cat_website", $subcategory_id), $message_body);
						if ($superuser_avatar == "") {
							$message_body = str_replace('%avatar%', $logo_path, $message_body);
						} else {
							$message_body = str_replace('%avatar%', $data_path . "/uploads/" . $superuser_avatar, $message_body);
						}
					} //not premium
					else {
						$message_body = str_replace('%socialfacebook%', "", $message_body);
						$message_body = str_replace('%socialwebsite%', "", $message_body);
						$message_body = str_replace('%avatar%', $logo_path, $message_body);
					}

					if ($reply_granted == "1") {
						$message_body = str_replace('%reply%', $reply_yes, $message_body);
						$message_body = str_replace('%reply_to%', $mailfrom, $message_body);
					}
					if ($reply_granted == "0") {
						$message_body = str_replace('%reply%', $reply_no, $message_body);
					}

					$mail->MsgHTML($message_body);


					if (!$mail->send()) {
						$return_message .= "* FOUT: BCC mail niet verstuurd naar " . $row_contact['cat_contact_email'] . " (" . $row_contact['cat_contact_name'] . ")\r\n\r\n";
					} else {
						$return_message .= "* SUCCESS: BCC mail verstuurd naar " . $row_contact['cat_contact_email'] . " (" . $row_contact['cat_contact_name'] . ")\r\n\r\n";
					}
					$mail->clearAddresses();
				} catch (Exception $e) {
					$error_code = $e->errorMessage();
					mysqli_query($con, "INSERT INTO mails_errors (mail_id, " . $superuser . "_id, email, username, error_code) VALUES ($mail_id, $superuser_id, AES_ENCRYPT('$row_contact[cat_contact_email]', SHA1('$pass')),AES_ENCRYPT('$row_contact[cat_contact_name]', SHA1('$pass')), '$error_code')");
					error_log("Error description: " . mysqli_error($con), 0);
				} catch (\Exception $e) {
					$return_message .= $e->getMessage();
				}
			}
		}
	}

	$count_recipients = mysqli_num_rows($result) + $count_BCC;
	//update is_sent, update queue, update recipients
	mysqli_query($con, "UPDATE mails SET is_sent = 1, rec_1 = $count_recipients, queue = 0, crontype = '$crontype' WHERE id = $mail_id");
	if($cron['event_id'] <> 0){
		$mail_sent = date('Y-m-d');
		mysqli_query($con,"UPDATE calendar SET mail_sent = '$mail_sent' WHERE id = $cron[event_id] AND " . $superuser . "_id = $superuser_id");
	}



	//default send push notifications to Parents too
	if($cron['event_id'] <> 0){
	$payload = create_payload_json("Je hebt een mail ontvangen van " . $superuser_name . " in verband met de activiteit van " . dutchDateFromEvent($cron['event_id']), "index.php?deeplink=1&goto_event_id=" . $cron['event_id']);
	
	//log mail sent	
	$getinfo = mysqli_query($con, "SELECT * FROM calendar WHERE id = $cron[event_id] AND " . $superuser . "_id = $superuser_id");
	$rowinfo = mysqli_fetch_array($getinfo);
	LogEntrySuper($cron['event_id'], $superuser_id, $superuser, "0", "0", $rowinfo['subcategory'], $rowinfo['ForAll'], "7");
	
	}
	if(($cron['event_id'] == 0) && ($cron['document_id'] == 0)){
	$payload = create_payload_json($superuser_name . " heeft je een mail gestuurd met als onderwerp: " . $subject . ". Bekijk deze mail in je e-mailprogramma of in " . $appname . ".", "index.php?deeplink=1&mail_id=$mail_id");
	}
	if($cron['document_id'] <> 0){
	$payload = create_payload_json($cron['subject'], "index.php?deeplink=1&" . $superuser . "_document_id=" . $cron['document_id']);
	}
	
	$fetchtokens = mysqli_query($con, "SELECT device, device_token FROM users WHERE id IN($parent_list,$adult_list) AND device <> 0 AND device_token <> '' AND device_token <> 'null'");
	if (mysqli_num_rows($fetchtokens) > 0) {
		foreach ($fetchtokens as $row) {
			$user_mobile_info = ['device'=>$row['device'], 'token'=>$row['device_token']];
			send_mobile_notification_request($user_mobile_info, $payload);
		}
	}
	
	if ($crontype <> 0){
	echo $return_message;
	}
}

function showStatusMail($row, $superuser_id, $superuser){
	if($row['queue'] == 1){
		return "<a class='notifit_custom mailinque none'><span class='fas fa-pause-circle' style='color:orange'></span></a>";
	}
	if($row['queue'] == 2){
		return "<a class='notifit_custom mailinprogress none'><span class='fas fa-location-circle' style='color:blue'></span></a>";
	}
	if($row['queue'] == 0){
		if($row['is_sent'] == 0){ 
		if($row['laterdate'] <> 1){ 
		return "<a class='notifit_custom mailerror none'><span class='fas fa-times-circle' style='color:red'></span></a>";
		}
		}
		else {
		return "<a class='notifit_custom mailsuccess none' id='" . date("d-m-Y H:i", strtotime($row['sendmoment'])) . "'><span class='fas fa-check-circle' style='color:green'></span></a>";	
		}
	}
}

function QueryUserFilePage($superuser, $superuser_id, $list_subcategories, $filetype, $order_by){
	$string_forsome = "";
	$array_subcategories = explode(',',$list_subcategories);
	foreach($array_subcategories AS $subcategory){
		$string_forsome .= " OR forall = 2
					   		AND FIND_IN_SET($subcategory,forsome) ";

}

return "SELECT * FROM " . $superuser . "s_documents 
		WHERE " . $superuser . "_id = $superuser_id
		AND hide = 0
		AND permanent = 1
		AND filetype IN ($filetype)
		AND id NOT IN (SELECT document_slave_id FROM " . $superuser . "s_documents_share WHERE " . $superuser . "_id = $superuser_id)
		AND 
		(
		forall = 0
		AND subcategory IN($list_subcategories) 							
		OR 
		forall = 1 
		$string_forsome	
		OR					
		event_id IN (SELECT id FROM calendar WHERE " . $superuser . "_id = $superuser_id AND ForAll = 1)
		)
		ORDER BY $order_by";

}

function showWeather($eventID){
	global $con;
	global $api_weather;
	
	$sevendays = date('c', strtotime('TODAY + 7 DAYS'));
	$eventdate = DateFromEvent($eventID);
	if($eventdate <= $sevendays) {
	$sql = mysqli_query($con, "SELECT postal,city, DATE(startdate) AS startdate, DATE(enddate) AS enddate FROM calendar WHERE id = $eventID AND weather = 1");
	if(mysqli_num_rows($sql) > 0){
		$row = mysqli_fetch_array($sql);
		
		//check if one-day event or multi-day event
		if($row['enddate'] > $row['startdate']){
		$string_multiday = "AND date BETWEEN '$row[startdate]' AND '$row[enddate]'";
		$multiday = 1;
		}
		else {
		$string_multiday = "AND date = '$eventdate'";
		$multiday = 0;
		}
		
		
		$city = $row['city'];
		
		if($row['postal'] <> ''){
			$sql2 = mysqli_query($con, "SELECT latitude,longitude,zip_code FROM postal WHERE zip_code = $row[postal]");
		}
		elseif($row['city'] <> ''){
			$sql2 = mysqli_query($con, "SELECT latitude,longitude,zip_code FROM postal WHERE city LIKE '%$city%'");
		}
		else {
			return false;
		}
		if(mysqli_num_rows($sql2) > 0){
			$row2 = mysqli_fetch_array($sql2);
			
			//check if already in table weather
			$perimeter = postalRadius($row2['zip_code'],"15"); //weather is the same in 15km radius
			$check = mysqli_query($con, "SELECT * FROM weather WHERE postal IN ($perimeter) $string_multiday GROUP BY date");
			if(mysqli_num_rows($check) > 0){ //twice a day cron job
				while($row_weather = mysqli_fetch_array($check)){
				$date = date_create($row_weather['date']);
				$date_format = date_format($date, "d/m");
				if($multiday == 1){
					$show_date = $date_format . ": ";
				}
				else {
					$show_date = "";
				}
				echo "<tr><td><img src=\"https://openweathermap.org/img/w/" . $row_weather['icon'] . ".png\" height=\"25px\"></td><td>" . $show_date . $row_weather['description'] . " - " . round($row_weather['temperature'], 0)  . " ºC - " . round($row_weather['wind_speed'],1) . " km/u wind vanuit het " . wind_cardinal($row_weather['wind_degrees']) . ".</td></tr>";
			}
			}
			else {
	
			$api_weather = "ec707b7d393dac548305017f807de6d3";
			$googleApiUrl = "https://api.openweathermap.org/data/3.0/onecall?lat=" . $row2['latitude'] . "&lon=" . $row2['longitude'] . "&exclude={part}&units=metric&lang=nl&appid=" . $api_weather;
	
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			
			curl_close($ch);
			$data = json_decode($response, JSON_PRETTY_PRINT);
			$result = json_decode($response);
			$currentTime = time();
			
			foreach ($result->hourly as $obj) {
    $date_weather = date('Y-m-d', $obj->dt);
    $icon_weather = $obj->weather[0]->icon;
    $description_weather = $obj->weather[0]->description;
    $temperature_weather = round($obj->temp, 0);
    $wind_speed_weather = round(mps_to_kmph($obj->wind_speed), 1);
    $wind_degrees_weather = $obj->wind_deg;
				
				if(mysqli_num_rows(mysqli_query($con, "SELECT id FROM weather WHERE postal = $row2[zip_code] AND date = '$date_weather'")) > 0){ 
					mysqli_query($con, "UPDATE weather SET
					description = '$description_weather',
					temperature = '$temperature_weather',
					wind_speed = '$wind_speed_weather',
					wind_degrees = '$wind_degrees_weather',
					icon = '$icon_weather'
					WHERE postal = $row2[zip_code] AND date = '$date_weather'");
				}
				else { 
				//import into table weather
				mysqli_query($con, "INSERT INTO weather (postal, description, temperature, wind_speed, wind_degrees, icon, date)
				VALUES
				($row2[zip_code], '$description_weather', '$temperature_weather', '$wind_speed_weather', '$wind_degrees_weather', '$icon_weather', '$date_weather')");
				}
				
				if(date('Y-m-d', $obj->dt) == DateFromEvent($eventID)){
				echo "<tr><td><img src=\"https://openweathermap.org/img/w/" . $obj->weather[0]->icon . ".png\" height=\"25px\"></td><td>" . $obj->weather[0]->description . " - " . round($obj->temp->day, 0)  . "��C - " . round(mps_to_kmph($obj->wind_speed),1) . " km/u wind vanuit het " . wind_cardinal($obj->wind_deg) . "</td></tr>";
	
				}
			}
			}
			
			
	}
	}
	}
	else {
		return false; //too late
	}
}

function mps_to_kmph( $mps) 
    { 
        return (3.6 * $mps); 
    } 

function wind_cardinal( $degree ) {

            switch( $degree ) {

                case ( $degree >= 348.75 && $degree <= 360 ):
                    $cardinal = "N";
                break;

                case ( $degree >= 0 && $degree <= 11.249 ):
                    $cardinal = "N";
                break;

                case ( $degree >= 11.25 && $degree <= 33.749 ):
                    $cardinal = "NNO";
                break;

                case ( $degree >= 33.75 && $degree <= 56.249 ):
                    $cardinal = "NO";
                break;

                case ( $degree >= 56.25 && $degree <= 78.749 ):
                    $cardinal = "ONO";
                break;

                case ( $degree >= 78.75 && $degree <= 101.249 ):
                    $cardinal = "O";
                break;

                case ( $degree >= 101.25 && $degree <= 123.749 ):
                    $cardinal = "OZO";
                break;

                case ( $degree >= 123.75 && $degree <= 146.249 ):
                    $cardinal = "ZO";
                break;

                case ( $degree >= 146.25 && $degree <= 168.749 ):
                    $cardinal = "N";
                break;

                case ( $degree >= 168.75 && $degree <= 191.249 ):
                    $cardinal = "Z";
                break;

                case ( $degree >= 191.25 && $degree <= 213.749 ):
                    $cardinal = "ZZW";
                break;

                case ( $degree >= 213.75 && $degree <= 236.249 ):
                    $cardinal = "ZW";
                break;

                case ( $degree >= 236.25 && $degree <= 258.749 ):
                    $cardinal = "WZW";
                break;

                case ( $degree >= 258.75 && $degree <= 281.249 ):
                    $cardinal = "W";
                break;

                case ( $degree >= 281.25 && $degree <= 303.749 ):
                    $cardinal = "WNW";
                break;

                case ( $degree >= 303.75 && $degree <= 326.249 ):
                    $cardinal = "NW";
                break;

                case ( $degree >= 326.25 && $degree <= 348.749 ):
                    $cardinal = "NNW";
                break;

                default:
                    $cardinal = null;

            }

           return $cardinal;

       }

function roundToNextHour($dateString) {
    $date = new DateTime($dateString);
    $minutes = $date->format('i');
    if ($minutes > 0) {
        $date->modify("+1 hour");
        $date->modify('-'.$minutes.' minutes');
    }
    return $date;
}

function testmode($testmode, $notification_type){
if($testmode == 1){	
	if($notification_type = "alert"){
		return "<div class='alert alert-warning'><span class='fal fa-info-circle'></span> Melding: Je gebruikt de app momenteel in testmodus" . helpPopup("whatstestmode", "black", "question-circle", "info", "") . ".<br>Klik <a href='/app/login/profile.php?goto=BtnEditAdmin'>hier</a> om de app terug te activeren in je accountpagina.</div>";
}
}
}

function LogAction($eventID, $superuser, $superuser_id, $subcategory_id, $kid_id, $adult_id, $action, $line, $variables, $error, $type = 1){
	global $con;
	global $pass;
	$logged_user = userValue(null, "id");
	$variables = mres($variables);
	
		//log action superuser_actions
		if($type == 1){
			$superusers = array("club");
			if(in_array($superuser, $superusers)){
				$insert = mysqli_query($con, "INSERT INTO log_action (event_id, " . $superuser . "_id, subcategory_id, logged_user, action, line, variables, error) VALUES ($eventID, $superuser_id, $subcategory_id, $logged_user, '$action', '$line', AES_ENCRYPT('$variables', SHA1('$pass')), '$error')");
				if(!$insert){
					error_log("Error description LogAction: " . mysqli_error($con), 0);
				}
			} 
			
		}
		//log action user_actions
		if($type == 0){
			$colum_superuser = "club_id";
			if($superuser == "club"){
			$colum_superuser = "club_id";	
			}
			$insert = mysqli_query($con, "INSERT INTO log_action (event_id, logged_user, $colum_superuser, kid_id, adult_id, action, line, variables, error) VALUES ($eventID, $logged_user, $superuser_id, $kid_id, $adult_id, '$action', '$line', AES_ENCRYPT('$variables', SHA1('$pass')), '$error')");
			if(!$insert){
					error_log("Error description LogAction: " . mysqli_error($con), 0);
				}
		
		}	
}

function showCountClick($document_id, $superuser, $superuser_id){
	global $con;
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
		$sql = mysqli_query($con, "SELECT id FROM CountClick WHERE file_id = $document_id AND " . $superuser . "_id = $superuser_id");
		if(mysqli_num_rows($sql) > 0){ 
			echo "<li><span class='fal fa-stopwatch fa-fw'></span>&nbsp;Clicks: <a class=\"location OpenModalUser\" type_attr=\"showcountclick\" name=\"$document_id\">" . mysqli_num_rows($sql) . "</li></a>";
			}
			else {
			echo "<li><span class='fal fa-stopwatch fa-fw'></span>&nbsp;Clicks: " . mysqli_num_rows($sql) . "</li></a>";
			}
	}
}

function generateTokenPaybuttons($button_id, $type, $superuser, $superuser_id, $adult_id, $kid_id, $manual_id){
	$superusers = array("club");
	if(in_array($superuser, $superusers)){
	return base64_url_encode("b" . $button_id . "t" . $type . $superuser . $superuser_id . "a" . $adult_id . "k" . $kid_id . "m" . $manual_id);
	}
	else {
		return false;
	}
}

/**
 * Increases or decreases the brightness of a color by a percentage of the current brightness.
 *
 * @param   string  $hexCode        Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`
 * @param   float   $adjustPercent  A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
 *
 * @return  string
 *
 * @author  maliayas
 */
function adjustBrightness($hexCode, $adjustPercent) {
    $hexCode = ltrim($hexCode, '#');

    if (strlen($hexCode) == 3) {
        $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
    }

    $hexCode = array_map('hexdec', str_split($hexCode, 2));

    foreach ($hexCode as & $color) {
        $adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
        $adjustAmount = ceil($adjustableLimit * $adjustPercent);

        $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
    }

    return '#' . implode($hexCode);
}

function extractDateAndTime($string, $type){
	if($type == "date"){
		$results = array();
		if(preg_match_all('#\d{2}[-/]\d{2}[-/]\d{4}#', $string, $results)){
		if(isset($results)){
		$date = $results[0][0];
		$date = str_replace('/', '-', $date);
		$timestamp = strtotime($date);
		return date("Y-m-d", $timestamp);
		}
		else {
			return false;
		}
	}
	}
	if($type == "time"){
		$results = array();
		if(preg_match_all("#\d{2}:\d{2}#", $string, $results)){
		$time = $results[0][0];
		return $time;
		}
		else {
			return false;
		}
}
}

function formatDate($startdate, $pattern = "EEEE dd LLLL yyyy"){
	if($startdate <> NULL){
	$format_date = new IntlDateFormatter('NL',
		IntlDateFormatter::FULL, 
		IntlDateFormatter::FULL
	);
	$format_date->setPattern($pattern);
	$format_date->format(strtotime($startdate));
	return $format_date->format(strtotime($startdate));
	}
	else {
		return false;
	}
}

 
?>
