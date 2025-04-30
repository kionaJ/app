<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('login/includes/api.php');
logged_in();
include('functions.php');
include('superuser_security.php'); 

//////////////////////////////////////////////////////////////////
//LIST MEMBERS OF A SUBGROUP/SUBCATEGORY
//////////////////////////////////////////////////////////////////

$string_kids = "";
$string_parents = "";
$string_manual = "";
$filter_id = 0;

//variables needed if use for duplicate mail
if(isset($_GET['mail_id'])) { 
	$mail_id=mres($_GET['mail_id']);
	}
	else {
		$mail_id = 0;
	}
if(isset($_GET['all'])) {
	$all=mres($_GET['all']);
	}
	else {
		$all = 0;
	}
if(isset($_GET['archive'])) {
	$archive=mres($_GET['archive']);
	}
	else {
		$archive = 0;
	}

$subcategory_id = isset($_GET['subcategory_id']) ? (int) $_GET['subcategory_id'] : 0;

$where_parts = [];

$where_parts[] = "(ForAll = 0 AND {$superuser}_id = $superuser_id AND subcategory_id = $subcategory_id)";
$where_parts[] = "(ForAll = 1 AND {$superuser}_id = $superuser_id)";
$where_parts[] = "(ForAll = 2 AND {$superuser}_id = $superuser_id AND FIND_IN_SET($subcategory_id, ForSome))";

$where_clause = implode(" OR ", $where_parts);

$sql = "SELECT * FROM {$superuser}s_labels WHERE $where_clause";

$labels = mysqli_query($con, $sql);



//display mails in queue
	$sql_queue = mysqli_query($con,"SELECT id AS mail_id, mailfrom, event_id, laterdate, is_sent, sendmoment, subject, DATE(sendmoment) AS date_made, senddate AS future_date, document_id, queue FROM mails WHERE " . $superuser . "_id = $superuser_id AND trash = 0 AND queue = 1 AND is_sent = 0 AND subcategory_id = $subcategory_id AND laterdate = 0");

if(isset($_GET['filter_id']) && $superuser == "club") {
$filter_id=mres($_GET['filter_id']);
if (strpos($filter_id, 'f') !== false) {
	$filter_id_F = substr($filter_id, 1);
	$string_kids = " AND kid_id IN (SELECT kid_id FROM clubs_instruments WHERE club_id = $superuser_id AND instrument_id IN (SELECT code FROM musical_instruments WHERE family = $filter_id_F)) ";
	$string_parents = " AND parent_id IN (SELECT parent_id FROM clubs_instruments WHERE club_id = $superuser_id AND instrument_id IN (SELECT code FROM musical_instruments WHERE family = $filter_id_F) )";
	$string_manual = " AND kids_clubs_manual.member_instrument IN (SELECT code FROM musical_instruments WHERE family = $filter_id_F) ";
}
if (strpos($filter_id, 's') !== false) {
	$filter_id_SH = substr($filter_id, 1);
	$string_kids = " AND kid_id IN (SELECT kid_id FROM clubs_instruments WHERE club_id = $superuser_id AND instrument_id IN (SELECT code FROM musical_instruments WHERE musicology = $filter_id_SH)) ";
	$string_parents = " AND parent_id IN (SELECT parent_id FROM clubs_instruments WHERE club_id = $superuser_id AND instrument_id IN (SELECT code FROM musical_instruments WHERE musicology = $filter_id_SH)) ";
	$string_manual = " AND kids_clubs_manual.member_instrument IN (SELECT code FROM musical_instruments WHERE musicology = $filter_id_SH) ";
}
}
$subcategory_id = isset($_GET['subcategory_id']) ? (int) $_GET['subcategory_id'] : 0;

$result_row = mysqli_query($con, "SELECT cat_name, board FROM clubs_categories WHERE id = $subcategory_id LIMIT 1");
$row = mysqli_fetch_assoc($result_row);

// Fallback voor als er geen resultaat is
if (!$row) {
    $row = ['cat_name' => 'Onbekend', 'board' => 0];
}


?>

<div align="center" class="titleunderbuttons">Ledenlijst van <?php echo $row['cat_name'] ?></div><br>
<?php
if($mail_id <> 0){
?>

<div class="alert alert-info">
	<span class="fal fa-info-circle"></span>&nbsp;Je gaat een mail hergebruiken of doorsturen. Gelieve eerst de leden te selecteren die je wil mailen.
</div>
</div>
<?php } ?>	
	
<div name="buttons" class="btn-group btn-group-justified" role="group" aria-label="...">
<?php if($mail_id <> 0){ ?>
<a id="MailSome" class="btn btn-success btn-sm btn-block" role="button"><span class="fal fa-chevron-right" aria-hidden="true"></span> Ga verder</a>
<a id="CloseButtonReUseMail" class="btn btn-primary btn-sm btn-block" role="button"><span class="fal fa-times"></span> Ga terug</a>

<?php
	}
if($mail_id == 0){ ?>
<div class="btn-group" role="group" style="position: initial;">
  <button class="btn btn-success btn-sm dropdown-toggle Mail" type="button" sid="dropdownMenu0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
    <span class="fal fa-envelope fa-lg"></span> <?php if(!$detect->isMobile()) { echo "Mail..."; } ?><span class="caret"></span>
  </button>
  <ul class="dropdown-menu full-width" style="width: 100%"  aria-labelledby="dropdownMenu0">
	  <li><a id="MailSome" role="button"><span class="fal fa-edit pull-right" aria-hidden="true"></span> Verstuur of plan een e-mail</a></li>
	  <li><a class="MailList <?php if(checkPremiumLevel($superuser_id, $superuser, "") == "level 0"){ echo "premium_disabled"; } ?>" <?php if($row['board'] == 1){ echo "id='1'"; } else { echo "id='0'"; } ?> name="0" role="button"><span class="fal fa-list-alt pull-right" aria-hidden="true"></span> Geplande e-mails
		  <?php if(CountPlannedMails($superuser_id, $superuser, $subcategory_id, "0", $row['board']) <> 0){
			  echo "<span class=\"badge\">". CountPlannedMails($superuser_id, $superuser, $subcategory_id, $row['board']) . "</span>";
		  }
			?>
	  </a></li>
	  <li><a class="MailList <?php if(checkPremiumLevel($superuser_id, $superuser, "") == "level 0"){ echo "premium_disabled"; } ?>" <?php if($row['board'] == 1){ echo "id='1'"; } else { echo "id='0'"; } ?> name="1" role="button"><span class="fal fa-archive pull-right" aria-hidden="true"></span> Verzonden e-mails 
		  <?php if(mysqli_num_rows($sql_queue) == 1){ 
			  	   echo "<span class='fas fa-pause-circle' style='color:orange'></span>";
			  	}
		  ?>
	  </a></li>

  </ul>

</div>

	<?php if($detect->isMobile()) {
	     ?>
      <a class="btn btn-success btn-sm TextSome" target="_blank" role="button"><span class="fal fa-sms fa-lg" aria-hidden="true"></span></a>
      <?php
	      }
?>

<div class="btn-group" role="group" style="position: initial;">
  <button class="btn btn-success btn-sm dropdown-toggle Labels" type="button" sid="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
    <span class="fal fa-flag fa-lg"></span> <?php if(!$detect->isMobile()) { echo "Labels..."; } ?><span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right full-width" style="width: 100%;"  aria-labelledby="dropdownMenu1">
	  <li class="dropdown-header">Voeg label toe aan leden...</li>
        
    
    <!-- Custom labels -->
    <?php
	
	?>
	<li><a class="CheckSome" name="check" id="0" role="button">Standaard<span class="fal fa-flag pull-right" aria-hidden="true" style="color:green"></span></a></li>
	<?php
	if(mysqli_num_rows($labels) > 0){
		foreach($labels AS $row_label){
			?>
	<li><a class="CheckSome" name="check" id="<?php echo $row_label['id'] ?>" role="button"><?php echo $row_label['title'] ?><span class="fal <?php echo $row_label['icon'] ?> pull-right" aria-hidden="true" style="color:green"></span></a></li>
    <?php
	}
	}
	?>
	<li class="divider"></li>
	<li class="dropdown-header">Verwijder label...</li>
	<li><a class="CheckSome" name="uncheck" id="0" role="button">Standaard<span class="fal fa-flag pull-right" aria-hidden="true" style="color:gray;"></span></a></li>
	<?php
	if(mysqli_num_rows($labels) > 0){
		foreach($labels AS $row_label){
			?>
	<li><a class="CheckSome" name="uncheck" id="<?php echo $row_label['id'] ?>" role="button"><?php echo $row_label['title'] ?><span class="fal <?php echo $row_label['icon'] ?> pull-right" aria-hidden="true" style="color:gray"></span></a></li>
    <?php
	}
	}
	?>
    <li class="divider"></li>
    <li><a class="EditMarks <?php if(checkPremiumLevel($superuser_id, $superuser, "") == "level 0"){ echo "premium_disabled"; } ?>" role="button">Beheer labels... <span class="fal fa-pencil pull-right" aria-hidden="true"></span></a></li>
    </ul>

</div>

<div class="btn-group" role="group" style="position: initial;">
  <button class="btn btn-success btn-sm dropdown-toggle Other" type="button" sid="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
    <span class="fal fa-cog fa-lg"></span> <?php if(!$detect->isMobile()) { echo "Andere..."; } ?> <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right full-width" style="width: 100%;"  aria-labelledby="dropdownMenu1">
    <li><a class="OpenExportFromMembers <?php if(!is_allowedLevel("2") && !hasPermission($superuser_id, $superuser, "2")){ echo "permission_disabled"; } ?> <?php if($detect->isMobile()) { echo "mobile_disabled"; } ?>" id="0" name="<?php echo $subcategory_id ?>"><span class="fal fa-file-export pull-right"></span>Exporteer ledengegevens...</a></li>
	<li class="divider"></li>
	<li><a role="button" class="OpenModalBulk" type_attr="movemembers"><span class="fal fa-sync-alt pull-right"></span>Verplaats leden...</a></li>
    <li><a role="button" class="OpenModalBulk" type_attr="copymembers"><span class="fal fa-copy pull-right"></span>Kopieer leden...</a></li>
    <li class="divider"></li>  	
    <li><a role="button" class="notifit_custom whatsdeletemultiple">Verwijder gebruikers...<span class="fal fa-trash-alt pull-right" aria-hidden="true"></span></a></li>
  	<li class="divider"></li>
  	<li><a class="NewMember" role="button"><span class="fal fa-user-plus pull-right" aria-hidden="true"></span> Voeg manueel lid toe</a></li>
  	<?php if($superuser == "club" && $superuser_id == "11513"){ ?>
  	<li><a href="superuser_bulk_upload.php?subcategory_id=<?php echo $subcategory_id ?>" role="button" class="<?php if(!is_allowedLevel("2")){ echo "permission_disabled"; } ?>"><span class="fal fa-upload pull-right" aria-hidden="true"></span> Importeer ledenbestand</a></li>
  	<?php } ?>
  </ul>

</div>
<?php 
	} ?>
</div>
<br>
<div class="smsnotification"></div> <!-- workaround bug iOS -->

<?php
	//alert if mails in queue
	if(mysqli_num_rows($sql_queue) > 0){
		if(mysqli_num_rows($sql_queue) == 1){
			$queuenumber = "Er staat 1 mail";
		}
		if(mysqli_num_rows($sql_queue) > 1){
			$queuenumber = "Er staan " . mysqli_num_rows($sql_queue) . " mails";
		}
		echo "<div class='alert alert-warning'><span class='fal fa-info-circle'></span> " . $queuenumber . " in de wachtrij van " .$appname . " om vandaag verstuurd te worden. Volgend verzendmoment: " . date('H', strtotime('next hour')) . " uur.</div>";
	}	
	
	
//QUERY KIDS
$sql = mysqli_query($con,"SELECT *, kid_id AS member FROM kids_" . $superuser . "s WHERE " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id
$string_kids
AND kid_id NOT IN (SELECT id FROM kids WHERE inactive = 1)");
$cnt = mysqli_num_rows($sql);

// Aanpassing van je bestaande PHP-code om geslachtsopties op te halen
$query = "SELECT DISTINCT sex FROM kids";
$result = mysqli_query($con, $query);

// Array om geslachtsopties op te slaan
$genders = array();
while ($row = mysqli_fetch_assoc($result)) {
    $genders[] = $row['sex'];
}

//QUERY ADULTS
$sql_parents = mysqli_query($con,"SELECT DISTINCT parent_id AS member FROM parents_" . $superuser . "s WHERE " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id
$string_parents");
$cnt_parents = mysqli_num_rows($sql_parents);

//QUERY MANUALLY ADDED KIDS
$sql_manual = mysqli_query($con,"SELECT member_number, member_category, AES_DECRYPT(member_medical_x, SHA1('$pass')) AS member_medical, member_instrument, id AS member,AES_DECRYPT(name, SHA1('$pass')) AS name, AES_DECRYPT(surname, SHA1('$pass')) AS surname, AES_DECRYPT(username, SHA1('$pass')) AS username, AES_DECRYPT(email, SHA1('$pass')) AS email, AES_DECRYPT(Telefoonnummer, SHA1('$pass')) AS Telefoonnummer, AES_DECRYPT(Telefoonnummer2, SHA1('$pass')) AS Telefoonnummer2, " . $superuser . "_id, subcategory, mark FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id $string_manual");
$cnt_manual = mysqli_num_rows($sql_manual);
?>

<div class="row">
  <div class="col-lg-12">
    <div class="input-group">
      <input class="form-control-custom" placeholder="Zoek..." type="text" id="customSearchBox">
	  <span class="input-group-btn">
				        <button class="btn btn-default ResetSearch RemoveFilter" style="background: transparent" type="submit"><span class="fal fa-times fa-xlg"></span></button>
				</span>
	  <span class="input-group-btn">
	  <div class="<?php if ($detect->isMobile()) { echo "dropup"; } else { echo "dropdown" ; } ?>">
		        <button type="button" class="btn btn-default" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background-color: white"><span class="fal fa-filter fa-lg"></span><span class="caret"></span>
		        </button>
	  
				<ul class="dropdown-menu pull-right <?php if ($detect->isMobile()) { echo "dropupmobile"; } ?>">
						 <li><a class="RemoveFilter">Verwijder filters <span class="fal fa-times-circle pull-right" aria-hidden="true"></span></a></li>
						 <li class="divider"></li>
						 <li class="dropdown-header">Selecteer op gebruikers <u>met</u> label...</li>
								<!-- Custom labels -->
								<li><a class="FilterSome" filter_attr="positive" title_attr="Standaard" id="0" name="label" role="button">Standaard<span class="fal fa-flag pull-right" aria-hidden="true"></span></a></li>
								<?php
								if(mysqli_num_rows($labels) > 0){
								foreach($labels AS $row_label){
										?>
								<li><a class="FilterSome" filter_attr="positive" id="<?php echo $row_label['id'] ?>" title_attr="<?php echo $row_label['title'] ?>" name="label" role="button"><?php echo $row_label['title'] ?><span class="fal <?php echo $row_label['icon'] ?> pull-right" aria-hidden="true"></span></a></li>
							    <?php
								}
								}
								?>
								<li class="divider"></li>
								<li class="dropdown-header">Selecteer op gebruikers <u>zonder</u> label...</li>
								<!-- Custom labels -->
								<li><a class="FilterSome" filter_attr="negative" title_attr="Standaard" id="0" name="label" role="button">Standaard<span class="fal fa-flag pull-right" aria-hidden="true"></span></a></li>
								<?php
								if(mysqli_num_rows($labels) > 0){
								foreach($labels AS $row_label){
										?>
								<li><a class="FilterSome" filter_attr="negative" id="<?php echo $row_label['id'] ?>" title_attr="<?php echo $row_label['title'] ?>" name="label" role="button"><?php echo $row_label['title'] ?><span class="fal <?php echo $row_label['icon'] ?> pull-right" aria-hidden="true"></span></a></li>
							    <?php
								}
								}
								?>
								<li class="divider"></li>
                                <li class="dropdown-header">Selecteer op Gender</li>
                                <li><a class="FilterSome" filter_attr="gender" id="male" title_attr="Man" name="gender" role="button">Man<span class="fas fa-mars pull-right" aria-hidden="true"></span></a></li>
                                <li><a class="FilterSome" filter_attr="gender" id="female" title_attr="Vrouw" name="gender" role="button">Vrouw<span class="fas fa-venus pull-right" aria-hidden="true"></span></a></li>
                                <li><a class="FilterSome" filter_attr="gender" id="none" title_attr="Neutraal" name="gender" role="button">Neutraal<span class="fas fa-transgender pull-right" aria-hidden="true"></span></a></li>
						
								
						 <?php if($superuser == "club" && isCategory($superuser_id,"3")){ ?>
                         <li class="divider"></li>
						 <li class="dropdown-header">Filter op Familie</li>
						 <li><a class="filtermembers" id="f1">Houtblazers</a></li>
						 <li><a class="filtermembers" id="f2">Koperblazers</a></li>
						 <li><a class="filtermembers" id="f3">Slagwerk</a></li>
						 <li><a class="filtermembers" id="f4">Harp/Piano</a></li>
						 <li><a class="filtermembers" id="f5">Strijkers</a></li>
						 <li class="divider"></li>
						 <li class="dropdown-header">Filter op Sachs-Hornbostel</li>
						 <li><a class="filtermembers" id="s1">Aerofonen</a></li>
						 <li><a class="filtermembers" id="s2">Chordofonen</a></li>
						 <li><a class="filtermembers" id="s3">Idiofonen</a></li>
						 <li><a class="filtermembers" id="s4">Membranofonen</a></li>
						 <li><a class="filtermembers" id="s5">Elektrofonen</a></li>
						 <?php } ?>
				</ul>
		</div>
	  </span>
	 	
    </div><!-- /input-group -->
	<div class="showfilters" style="margin-top: -20px; margin-left: 10px"></div><br>
  </div><!-- /.col-lg-6 -->	  
</div><!-- /.row -->


			<table class='table table-members compact' id='MembersList'>
						<thead>
							<tr style="vertical-align: bottom">
								<td width="5px">
									<label class="btn">
							          <input type="checkbox" id="select_all" style="display:none"><i class="far fa-square fa-lg"></i><i class="fa fa-check-square fa-lg"></i><span></span>
							        </label>
									
									</td>
								<td>Achternaam</td>
								<td>Mark</td>
								<td valign="bottom">Selecteer alle
</td>
							</tr>
						</thead>
			<tbody>
				
			<?php
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//FIRST OUTPUT KIDS
if($cnt > 0) {
while($row_members=mysqli_fetch_array($sql)){ 
    
    
?>
<tr>
			<td>
				<!-- checkboxes -->
			    <label class="btn">
		          <input type="checkbox" class="checkbox checkbox_kids" style="display:none" value="<?php echo $row_members['member'] ?>"><i class="far fa-square fa-lg"></i><i class="fa fa-check-square fa-lg"></i><span></span>
		        </label>					
			</td>
			<td><?php echo userValueKid($row_members['member'], "surname") ?><input type="hidden" value="<?php echo userValueKid($row_members['member'], "surname") ?>">
			</td>
			<td><?php echo $row_members['member'] ?></td>
			<td>
				<!-- information members -->
				<ul class="media-list">
				  <li class="media" style="overflow: visible !important">
				  <div class="media-left">
						<!-- avatar -->
							<?php
							if(!isReportedProfile("0", "0", $row_members['member'], $superuser_id, $superuser)) { 
							echo isOnline(JoinedParentsFromParentId(userValueKid($row_members['member'], "parent")), 0, "retreive", "bullet");
							showAvatarFromKidId($row_members['member'], "avatar-smaller"); 
							}
							else {
								echo "<img class=\"avatar-smaller\" src=\"img/reported.png\"></img>";
							}		
						?>
					</div>
				    <div class="media-body">
				      <div class="media-heading">
                        <strong>
                            <?php 
                            echo userValueKid($row_members['member'], "name") . " " .  
                            userValueKid($row_members['member'], "surname");
                        
                            // Haal de waarde van 'sex' op
                            $sex = userValueKid($row_members['member'], "sex");
                        
                            // Vertaal de waarde van 'sex' naar een FontAwesome icoontje
                            if ($sex == 0) {
                                $sexIcon = "<i class='fas fa-mars icon-large'></i>";
                            } elseif ($sex == 1) {
                                $sexIcon = "<i class='fas fa-venus icon-large'></i>";
                            } elseif ($sex == 2) {
                                $sexIcon = "<i class='fas fa-transgender icon-large'></i>";
                            } else {
                                $sexIcon = "<i class='fas fa-question icon-large'></i>";
                            }
                            // Toon het FontAwesome icoontje
                            $genderLabel = getGenderLabel($sex);
                            echo " " . $sexIcon;
                            ?>

                                <script>
                                $(document).ready(function() {
                                    $('.FilterSome').click(function() {
                                        var selectedGender = $(this).attr('id'); // krijg de ID van het geselecteerde geslacht (male, female, none)
                                        
                                        // Schakel alle selecties uit
                                        $('.FilterSome').removeClass('active');
                                        $(this).addClass('active'); // Markeer de geselecteerde optie als actief
                                        
                                        // Toon of verberg rijen op basis van het geselecteerde geslacht
                                        if (selectedGender === 'male') {
                                            $('tr').each(function() {
                                                if ($(this).find('.fa-mars').length > 0) {
                                                    $(this).find('.checkbox_kids').prop('checked', true);
                                                } else {
                                                    $(this).find('.checkbox_kids').prop('checked', false);
                                                }
                                            });
                                        } else if (selectedGender === 'female') {
                                            $('tr').each(function() {
                                                if ($(this).find('.fa-venus').length > 0) {
                                                    $(this).find('.checkbox_kids').prop('checked', true);
                                                } else {
                                                    $(this).find('.checkbox_kids').prop('checked', false);
                                                }
                                            });
                                        } else if (selectedGender === 'none') {
                                            $('tr').each(function() {
                                                if ($(this).find('.fa-transgender').length > 0) {
                                                    $(this).find('.checkbox_kids').prop('checked', true);
                                                } else {
                                                    $(this).find('.checkbox_kids').prop('checked', false);
                                                }
                                            });
                                        }
                                    });
                                });
                                </script>
                     </strong>

					      <?php if (!$detect->isMobile() ) { ?>
					      <div class="pull-right" style="margin-top: 10px">
						 <?php showLabels($row_members['member'], "0", "0", $superuser_id, $superuser, $subcategory_id) ?>
					       </div>
					      <?php } ?>
						  <br/>
						  <font color="a8b42d" style="line-height: 1.8em;">
						  <?php if($superuser == "club" && isCategory($superuser_id,"3") && (ShowInstrumentsClub($row_members['member'], "0", "0", $superuser_id))){
							  echo "<span class=\"fal fa-music fa-fw\"></span> " . ShowInstrumentsClub($row_members['member'], "0", "0", $superuser_id) . "<br>";
						  }
						  if ($detect->isMobile() ) {
						  showLabels($row_members['member'], "0", "0", $superuser_id, $superuser, $subcategory_id) ?>
						
						  <?php } ?>
						  </font>
						  
						   <?php if(isReportedProfile("0", "0", $row_members['member'], $superuser_id, $superuser)) {
						        echo "<font color=red><strong>Je hebt deze gebruiker gerapporteerd. Wij houden je op de hoogte!<br>
						        Je kan dit ongedaan maken.</strong></font>";  
						        }
	        
	        ?>
					   </div>  
					   
					</div>
					
					
					<div class="media-right" style="vertical-align: middle">
							<div class="dropdown">
							  <button class="btn btn-default btn-xs dropdown-toggle pull-right" type="button" id="dropdownMenu<?php echo $row_members['member'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="background-color: transparent; color: #bdbdbd">
							    <span class="fa fa-ellipsis-v fa-2x"></span>
							  </button>

							  <ul class="dropdown-menu pull-right" style="width: 280px;" aria-labelledby="dropdownMenu<?php echo $row_members['member'] ?>">
							    <?php if(!isReportedProfile("0", "0", $row_members['member'], $superuser_id, $superuser)) {
								?>
							    <li><a role="button" class="OpenModalUser" type_attr="userinfo" kid_id_attr="<?php echo $row_members['member'] ?>" parent_id_attr="0" manual_id_attr="0" name="<?php echo $subcategory_id ?>"><span class="fal fa-info-circle pull-right" aria-hidden="true"></span>Informatiefiche <?php echo userValueKid($row_members['member'], "name") ?></a></li>
							    <li><a id="<?php echo $row_members['member'] ?>" name="<?php echo $subcategory_id ?>" kid_id_attr="<?php echo $row_members['member'] ?>" parent_id_attr="0" manual_id_attr="0" all_attr="0" class="EditMember"><span class="fal fa-pencil pull-right" aria-hidden="true"></span>Bewerk <?php echo userValueKid($row_members['member'], "name") ?></a></li>
							    <li class="divider"></li>
							    <li><a role="button" class="OpenModalBulkSolo" kid_id_attr="<?php echo $row_members['member'] ?>" parent_id_attr="0" manual_id_attr="0" type_attr="movemembers"><span class="fal fa-sync-alt pull-right"></span>Verplaats <?php echo userValueKid($row_members['member'], "name") ?>...</a></li>
								<li><a role="button" class="OpenModalBulkSolo" kid_id_attr="<?php echo $row_members['member'] ?>" parent_id_attr="0" manual_id_attr="0" type_attr="copymembers"><span class="fal fa-copy pull-right"></span>Kopieer <?php echo userValueKid($row_members['member'], "name") ?>...</a></li>
							    <li class="divider"></li>
							    <li><a role="button" class="OpenModalUser" type_attr="userreport" kid_id_attr="<?php echo $row_members['member'] ?>" parent_id_attr="0" manual_id_attr="0" name="<?php echo $subcategory_id ?>"><span class="fal fa-exclamation-triangle pull-right" aria-hidden="true"></span>Rapporteer <?php echo userValueKid($row_members['member'], "name") ?>...</a></li>
							    <li class="divider"></li>
							    <li><a role="button" class="OpenModalUser" type_attr="userremove" kid_id_attr="<?php echo $row_members['member'] ?>" parent_id_attr="0" manual_id_attr="0" name="<?php echo $subcategory_id ?>"><span class="fal fa-trash pull-right" aria-hidden="true"></span>Verwijder <?php echo userValueKid($row_members['member'], "name") ?>...</a></li>
							   <?php } else { ?>
							 <li><a role="button" type_id_attr="report_undo" name="<?php echo $subcategory_id ?>" class="SubmitReportUser" kid_id_attr="<?php echo $row_members['member'] ?>" parent_id_attr="0"><span class="fal fa-exclamation-triangle pull-right" aria-hidden="true"></span> Maak rapportage ongedaan</a></li>  													
							
							<?php
								}
							?>	   
							  </ul>
							</div>
					</div>
				  </li>
				</ul>
	        </td>
			</tr>	
		<?php
		}
		}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//SECOND OUTPUT ADULTS
if($cnt_parents > 0) {
while($row_parents=mysqli_fetch_array($sql_parents)){
?>
<tr>
			<td>
				<!-- checkboxes -->
			    <label class="btn">
		          <input type="checkbox" class="checkbox checkbox_parents" style="display:none" value="<?php echo $row_parents['member'] ?>"><i class="far fa-square fa-lg"></i><i class="fa fa-check-square fa-lg"></i><span></span>
		        </label>					
			</td>
			<td><?php if (($pos = strpos(userValue($row_parents['member'], "username"), " ")) !== FALSE) { 
    	$surname_probably = substr(userValue($row_parents['member'], "username"), $pos+1); 
		}
		else {
			$surname_probably = userValue($row_parents['member'], "username");
		}

		echo $surname_probably ?><input type="hidden" value="<?php echo userValue($row_parents['member'], "username") ?>"></td>
			<td><?php echo $row_parents['member'] ?></td>
			<td>
				<!-- information members -->
				<ul class="media-list" >
				  <li class="media" style="overflow: visible">
				  <div class="media-left">
						<!-- avatar -->
							<?php 
							if(!isReportedProfile("0", $row_parents['member'], "0", $superuser_id, $superuser)) {	
							echo isOnline($row_parents['member'], 0, "retreive", "bullet");
							showAvatarFromParentId($row_parents['member'], "avatar-smaller");
							}
							else {
								echo "<img class=\"avatar-smaller\" src=\"img/reported.png\"></img>";
							}		
						?>

					</div>
				    
				    <div class="media-body">
				      <div class="media-heading">
					      <strong><?php echo userValue($row_parents['member'], "username")  ?>
					      </strong>
					      
					       <?php if (!$detect->isMobile() ) { ?>
					      <div class="pull-right" style="margin-top: 10px">
						 <?php showLabels("0", $row_parents['member'], "0", $superuser_id, $superuser, $subcategory_id) ?>
					       </div>
					      <?php } ?>
						  <br/>
					      <font color="#a8b42d" style="line-height: 1.8em;">
					      <?php if($superuser == "club" && isCategory($superuser_id,"3") && (ShowInstrumentsClub("0",$row_parents['member'], "0", $superuser_id) == true)){
							  echo "<span class=\"fal fa-music fa-fw\"></span> " . ShowInstrumentsClub("0",$row_parents['member'], "0", $superuser_id) . "<br>";
						  }
						  if ($detect->isMobile() ) {
						   showLabels("0", $row_parents['member'], "0", $superuser_id, $superuser, $subcategory_id) ?>
						   						<?php } ?>
					      </font>
					      
					      <?php if(isReportedProfile("0", $row_parents['member'], "0", $superuser_id, $superuser)) {	
					        echo "<font color=red><strong>Je hebt deze gebruiker gerapporteerd. Wij houden je op de hoogte!<br>
					        Je kan dit ongedaan maken.</strong></font>";  
					        }
					        ?>
					      
					   </div>  
					</div>
					
					
					<div class="media-right" style="vertical-align: middle">
							<div class="dropdown">
							  <button class="btn btn-default btn-xs dropdown-toggle pull-right" type="button" id="dropdownMenu<?php echo $row_parents['member'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="background-color: transparent; color: #bdbdbd">
							    <span class="fa fa-ellipsis-v fa-2x"></span>
							  </button>
							  
							  <ul class="dropdown-menu pull-right" style="width: 280px;" aria-labelledby="dropdownMenu<?php echo $row_parents['member'] ?>">
							    
							    <?php if(!isReportedProfile("0", $row_parents['member'], "0", $superuser_id, $superuser)) {	
								?>
							    
							    <li><a role="button" class="OpenModalUser" type_attr="userinfo" kid_id_attr="0" parent_id_attr="<?php echo $row_parents['member'] ?>" manual_id_attr="0" name="<?php echo $subcategory_id ?>"><span class="fal fa-info-circle pull-right" aria-hidden="true"></span>Informatiefiche <?php echo userValue($row_parents['member'], "username") ?></a></li>
							    <li><a id="<?php echo $row_parents['member'] ?>" name="<?php echo $subcategory_id ?>" parent_id_attr="<?php echo $row_parents['member'] ?>" kid_id_attr="0" manual_id_attr="0" all_attr="0" class="EditMember"><span class="fal fa-pencil pull-right" aria-hidden="true"></span>Bewerk <?php echo userValue($row_parents['member'], "username") ?></a></li>
								<li class="divider"></li>
							    <li><a role="button" class="OpenModalBulkSolo" parent_id_attr="<?php echo $row_parents['member'] ?>" kid_id_attr="0" manual_id_attr="0" type_attr="movemembers"><span class="fal fa-sync-alt pull-right"></span>Verplaats <?php echo userValue($row_parents['member'], "username") ?>...</a></li>
								<li><a role="button" class="OpenModalBulkSolo" parent_id_attr="<?php echo $row_parents['member'] ?>" kid_id_attr="0" manual_id_attr="0" type_attr="copymembers"><span class="fal fa-copy pull-right"></span>Kopieer <?php echo userValue($row_parents['member'], "username") ?>...</a></li>
							    <li class="divider"></li>
								<li><a role="button" class="OpenModalUser" type_attr="userreport" kid_id_attr="0" parent_id_attr="<?php echo $row_parents['member'] ?>" manual_id_attr="0" name="<?php echo $subcategory_id ?>"><span class="fal fa-exclamation-triangle pull-right" aria-hidden="true"></span>Rapporteer <?php echo userValue($row_parents['member'], "username") ?>...</a></li>
								<li class="divider"></li>
								<li><a role="button" class="OpenModalUser" type_attr="userremove" kid_id_attr="0" parent_id_attr="<?php echo $row_parents['member'] ?>" manual_id_attr="0" name="<?php echo $subcategory_id ?>"><span class="fal fa-trash pull-right" aria-hidden="true"></span>Verwijder <?php echo userValue($row_parents['member'], "username") ?>...</a></li>
								<?php } else { ?>						
							<li><a role="button" type_id_attr="report_undo" name="<?php echo $subcategory_id ?>" class="SubmitReportUser" kid_id_attr="0" parent_id_attr="<?php echo $row_parents['member'] ?>"><span class="fal fa-exclamation-triangle pull-right" aria-hidden="true"></span> Maak rapportage ongedaan</a></li>
							
							<?php
								}
							?>	    
							  </ul>
							</div>
					</div>
				  </li>
				</ul>
	        </td>
			</tr>
	<?php
		}
		}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//THIRD OUTPUT MANUALLY ADDED KIDS
if($cnt_manual > 0) {
while($row_members_manual=mysqli_fetch_array($sql_manual)){
	?>
	<tr>
			<td>
				<!-- checkboxes -->
				<label class="btn">
		          <input type="checkbox" class="checkbox checkbox_manual" style="display:none" value="<?php echo $row_members_manual['member'] ?>"><i class="far fa-square fa-lg"></i><i class="fa fa-check-square fa-lg"></i><span></span>
		        </label>
			</td>
			<td><?php echo $row_members_manual['surname'] ?><input type="hidden" value="<?php echo $row_members_manual['surname'] ?>"></td>
			<td><?php echo $row_members_manual['member'] ?></td>
			<td>
				<ul class="media-list">
				  <li class="media" style="overflow: visible">
				  <div class="media-left">
						<!-- avatar -->
					    <?php
						echo "<img class=\"letterpic avatar-smaller\" title=\"" . preg_replace("/[^A-Za-z0-9 ]/", '', $row_members_manual['name'] . " " .  $row_members_manual['surname']) . "\" style=\"display:inline\"></img>";
						//echo "<img class=\"avatar-smaller\" src=\"img/anonymous.png\"></img>";			
						?>
					</div>
				    
				    <div class="media-body">
				      <div class="media-heading">
					     					      
					      <strong><?php echo $row_members_manual['name'] . " " .  $row_members_manual['surname'] ?> <sup><a class="none whatsmanualmember">(m)</a></sup>
					      </strong>
					      
					      <?php if (!$detect->isMobile() ) { ?>
					      <div class="pull-right" style="margin-top: 10px">
						 <?php showLabels("0", "0", $row_members_manual['member'], $superuser_id, $superuser, $subcategory_id) ?>
					       </div>
					      <?php } ?>
					      <br>
						  <font color="gray" style="line-height: 1.8em">
						  <?php if($superuser == "club" && isCategory($superuser_id,"3") && $row_members_manual['member_instrument'] != "0"){
							  echo "<span class=\"fal fa-music fa-fw\"></span> " . DisplayInstruments($row_members_manual['member_instrument']) . "<br>";
						  }
						  if ($detect->isMobile() ) {
						  showLabels("0", "0", $row_members_manual['member'], $superuser_id, $superuser, $subcategory_id) ?>
						  <?php } ?>
						  </font>
						  
						  						  
					   </div>  
					</div>
					<div class="media-right" style="vertical-align: middle">
							<div class="dropdown">
							  <button class="btn btn-default btn-xs dropdown-toggle pull-right" type="button" id="dropdownMenu<?php echo $row_members_manual['member'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="background-color: transparent; color: #bdbdbd">
							    <span class="fa fa-ellipsis-v fa-2x"></span>
							  </button>
					
							 
							  <ul class="dropdown-menu pull-right" style="width: 280px;" aria-labelledby="dropdownMenu<?php echo $row_members_manual['member'] ?>">
							    <li><a role="button" class="OpenModalUser" type_attr="userinfo" kid_id_attr="0" parent_id_attr="0" manual_id_attr="<?php echo $row_members_manual['member'] ?>" name="<?php echo $subcategory_id ?>"><span class="fal fa-info-circle pull-right" aria-hidden="true"></span>Informatiefiche <?php echo $row_members_manual['name'] ?></a></li>
							    <li><a id="<?php echo $row_members_manual['member'] ?>" name="<?php echo $subcategory_id ?>" manual_id_attr="<?php echo $row_members_manual['member'] ?>" kid_id_attr="0" parent_id_attr="0" all_attr="0" class="EditMember"><span class="fal fa-pencil pull-right" aria-hidden="true"></span>Bewerk <?php echo $row_members_manual['name'] ?></a></li>
							    <li class="divider"></li>
							    <li><a role="button" class="OpenModalBulkSolo" manual_id_attr="<?php echo $row_members_manual['member'] ?>" kid_id_attr="0" parent_id_attr="0" type_attr="movemembers"><span class="fal fa-sync-alt pull-right"></span>Verplaats <?php echo $row_members_manual['name'] ?>...</a></li>
								<li><a role="button" class="OpenModalBulkSolo" manual_id_attr="<?php echo $row_members_manual['member'] ?>" kid_id_attr="0" parent_id_attr="0" type_attr="copymembers"><span class="fal fa-copy pull-right"></span>Kopieer <?php echo $row_members_manual['name'] ?>...</a></li>
							    <li class="divider"></li>
							    <li><a role="button" class="OpenModalUser" type_attr="userremove" kid_id_attr="0" parent_id_attr="0" manual_id_attr="<?php echo $row_members_manual['member'] ?>" name="<?php echo $subcategory_id ?>"><span class="fal fa-trash pull-right" aria-hidden="true"></span>Verwijder <?php echo $row_members_manual['name'] ?>...</a></li>
							  </ul>
							</div>
					</div>
				  </li>
				</ul>
				</td>
			</tr>
				<?php
				}
				}
		?>
		</tbody>
				</table>
		

<!-- Modal Member Functions (export, copy, move, ...) -->
<div class="modal fade memberinfo" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="MemberInfoDiv"></div>
		</div>
	</div>
</div>	
<script type="text/javascript">
	var superuser = "<?php echo $superuser ?>";
	var superuser_id = "<?php echo $superuser_id ?>";
	var subcategory_id = "<?php echo $subcategory_id ?>";
	var mail_id = "<?php echo $mail_id ?>";
	var all = "<?php echo $all ?>";
	var archive = "<?php echo $archive ?>";
	var urlsecurity = "?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>";
	var thisurl = "superuser_members_list.php";
	var allsubs = 0;
	var targetdiv = "#Div<?php echo $subcategory_id ?>";
</script>
<script src="js/link_changer.js" type="text/javascript"></script>
<script type="text/javascript">
$(".letterpic").letterpic();
$(document).ready(function() {
	
	<?php if($filter_id <> "0"){ ?>
	 var cur_filters = $('.showfilters').html();
	  if(cur_filters)
	  $('.showfilters').html(cur_filters + "<small><?php echo showFilterMembers($filter_id) ?></small>");
	  else
	  $('.showfilters').html("<small>filter: <?php echo showFilterMembers($filter_id) ?></small>");
	<?php
	}
	?>

	var files =	$('#MembersList').DataTable( {
			"bFilter": true,
			"paging":   false,
			"sDom": "t",
			"info":     true,
			"order": [[ 1, "asc" ]],
		
			"language": {
				"info":   "_START_ tot _END_ van de _TOTAL_ resultaten",
				"search": "Zoek in je leden: ",
				"infoFiltered": "",
				"lengthMenu": "<?php echo $m['show']; ?> _MENU_ <?php echo $m['records']; ?>",
				"zeroRecords": "Er zijn geen resultaten gevonden",
				"paginate": {
					"next": "<?php echo $m['next']; ?>",
					"previous": "<?php echo $m['previous']; ?>"
				}
			},
            "columnDefs": [
                { orderable: false, targets: '_all' },
	            {
	                "targets": [ 1 ],
	                "visible": false,
	                "searchable": false
	            },
	            {
	                "targets": [ 2 ],
	                "visible": false
	            }
	        ],

		});
		
		$('#customSearchBox').on('keyup', function(){
            files.search($(this).val()).draw() ;
        });

});
</script>

	
