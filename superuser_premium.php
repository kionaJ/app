<?php
include('login/includes/api.php');
logged_in();
include('functions.php');
include('superuser_security.php');

//////////////////////////////////////////////////////////////////
//PREMIUM OVERVIEW / HOMEPAGE
//////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<?php include("header.php") ?>
</head>
<body>
<?php include("top_min.php");
//show bottom banner if mobile
if ($detect->isMobile() ) {
include("banner_default.php"); 
}
?>
<div class="container" id="content2" align="center">
		<br>

<div id="rcorners_flat">
<h4 align="center"><?php echo $appname ?> Premium versie</h4><br>

<div class="alert alert-warning">
	<div class="media-list">
		<div class="media-left"><span class="fas fa-euro-sign fa-lg bg-icons bg-color-1 icon-circle"></span></div>
		<div class="media-body"><strong>Wil je nog meer functies in <?php echo $appname ?>?</strong><br>
			Kies hieronder het pakket dat het best bij je <?php echo $superuser_default ?> past en geniet nog meer van wat <?php echo $appname ?> voor jouw <?php echo $superuser_default ?> kan betekenen!
		</div>

	</div>
</div>
<div class="alert alert-success">
	<div class="media-list">
		<div class="media-left"><span class="fas fa-star fa-lg bg-icons bg-color-2 icon-circle"></span></div>
		<div class="media-body"><strong> 
			<?php if(checkPremiumLevel($superuser_id, $superuser, "") == "level 0"){
				echo "Jouw vereniging gebruikt nu het gratis pakket '$level_0_name' van " . $appname . ".";
				}
			if(checkPremiumLevel($superuser_id, $superuser, "") == "level 1"){
				echo "Jouw vereniging is geabonneerd op het premium model $level_1_name' tot " . date("d-m-Y", strtotime(checkPremiumLevel($superuser_id, $superuser, "end_date"))) . ".";
				}
			if(checkPremiumLevel($superuser_id, $superuser, "") == "level 2"){
				echo "Jouw vereniging is geabonneerd op het premium model '$level_2_name' tot " . date("d-m-Y", strtotime(checkPremiumLevel($superuser_id, $superuser, "end_date"))) . ".";
				}
			?>
		</strong>
		</div>

	</div>
</div>

<table class="table table-responsive table-striped" style="table-layout: fixed">
						<thead>
							<tr class="info">
								<td width="40%"><?php echo $appname ?> Premium pakket</td>
								<td><?php echo $level_0_name ?></td>
								<?php if($superuser == "club"){ ?>
								<td><?php echo $level_1_name ?></td>
								<?php } ?>
								<td><?php echo $level_2_name ?></td>
							</tr>
						</thead>
					<tbody>
						<tr class="warning">
							<td colspan="<?php if($superuser == "club") { echo '4'; } else { echo '3';} ?>" align="center">SUBGROEPEN</td>
						</tr>
						<tr>
							<td>Aanmaken en/of bewerken van subgroepen</td>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>

						</tr>
						<tr>
							<td>Aanmaken van Shopitems</td>
							<td>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>

						</tr>
						
						<tr class="warning">
							<td colspan="<?php if($superuser == "club") { echo '4'; } else { echo '3';} ?>" align="center">CONTACTPERSONEN</td>
						</tr>
						<tr>
							<td>Toevoegen en koppelen van contactpersonen per subgroep<br>
								<small><font color="gray">* max 2</font></small>
							</td>
							<td>
								<span class="fas fa-check fa-lg"></span> <font color="gray">*</font>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span><br><small></small>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span><br><small></small>
							</td>
						</tr>
						<tr class="warning">
							<td colspan="<?php if($superuser == "club") { echo '4'; } else { echo '3';} ?>" align="center">AGENDABEHEER</td>
						</tr>
						<tr>
							<td>Standaard agendablokken aanmaken en/of bewerken<br>
								<small><font color="gray">* max 2</font></small>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span> <font color="gray">*</font>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span><br><small></small>
							</td>
							<td>
								<span class="fas fa-check fa-lg"></span><br><small></small>
							</td>
						</tr>
						<tr>
							<td>Importeer je Google kalender in <?php echo $appname ?></td>
							<td>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
						</tr>
						<tr>
							<td>Agendapunten in je stad laten plaatsen</td>
							<td>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
						</tr>
						<tr>
							<td>Sluit <?php echo $appname ?>-agenda in op je website<br>
							</td>
							<td>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
						</tr>
						<tr>
							<td>Grootte bijlages in agendapunten<br>
							</td>
							<td>
								5Mb
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
							10Mb
							</td>
							<?php } ?>
							<td>
							15Mb
							</td>
						</tr>
						<tr class="warning">
							<td colspan="<?php if($superuser == "club") { echo '4'; } else { echo '3';} ?>" align="center">COMMUNICATIE</td>
						</tr>
						<tr>
							<td>E-mail je leden<br>
								<small><font color="gray">* niet gepersonaliseerd met <?php echo $appname ?>-logo</font><br>
								<font color="gray">** gepersonaliseerd met eigen logo</font></small>
							</td>
							<td>
							<span class="fas fa-check fa-lg"></span> <font color="gray">*</font>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
							<span class="fas fa-check fa-lg"></span> <font color="gray">**</font>
							</td>
							<?php } ?>
							<td>
							<span class="fas fa-check fa-lg"></span> <font color="gray">**</font>
							</td>
						</tr>
						<tr>
							<td>Uitgesteld mailen</td>
							<td>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
							<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
							<span class="fas fa-check fa-lg"></span>
							</td>
						</tr>
						<tr>
							<td>Voorkom dat leden mails kunnen beantwoorden</td>
							<td>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
							<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
							<span class="fas fa-check fa-lg"></span>
							</td>
						</tr>
						<tr class="warning">
							<td colspan="<?php if($superuser == "club") { echo '4'; } else { echo '3';} ?>" align="center">LEDENBEHEER</td>
						</tr>
						<tr>
							<td>Labels<br>
								<small><font color="gray">* max 1</font></small>
							</td>
							<td>
								<span class="fas fa-check fa-lg"></span> <font color="gray">*</font>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
							<span class="fas fa-check fa-lg"></span><br><small></small>
							</td>
							<?php } ?>
							<td>
							<span class="fas fa-check fa-lg"></span><br><small></small>
							</td>
						</tr>
						<tr>
							<td>Opnemen van afwezigheden</td>
							<td>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
							<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
							<span class="fas fa-check fa-lg"></span>
							</td>
						</tr>

						<tr>
							<td>Exporteren van leden op PDF of Excel<br>
								<small><font color="gray">* gelimiteerd</font></small>
							</td>
							<td>
								<span class="fas fa-check fa-lg"></span> <font color="gray">*</font>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
						</tr>
						
						<tr class="warning">
							<td colspan="<?php if($superuser == "club") { echo '4'; } else { echo '3';} ?>" align="center">BESTANDSBEHEER</td>
						</tr>
						<tr>
							<td>Grootte serverspace</td>
							<td>
								250 Mb
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								2 Gb
							</td>
							<?php } ?>
							<td>
								10 Gb
							</td>
						</tr>
						<tr>
							<td>Fotoalbum: aantal foto's in bulk uploaden per keer</td>
							<td>
								25
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								40
							</td>
							<?php } ?>
							<td>
								50
							</td>
						</tr>
						<tr class="warning">
							<td colspan="<?php if($superuser == "club") { echo '4'; } else { echo '3';} ?>" align="center">MARKETING</td>
						</tr>
						<tr>
							<td><?php echo $affiliatename ?> Commissie</td>
							<td>
								80%
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								80%
							</td>
							<?php } ?>
							<td>
								80%
							</td>
						</tr>
						<tr>
							<td><?php echo $appname ?>-Advertentie in mailing</td>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								
							</td>
							<?php } ?>
							<td>
								
							</td>
						</tr>
						<tr>
							<td>Eigen sponsors in mailing</td>
							<td>
								
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
						</tr>
					    <tr class="warning">
							<td colspan="<?php if($superuser == "club") { echo '4'; } else { echo '3';} ?>" align="center">SUPPORT</td>
						</tr>
						<!--
						<tr>
							<td>Promomateriaal (flyers, posters, spandoeken)<br>
								<small><font color="gray">* bij opstart</font></small><br>
								<small><font color="gray">** jaarlijks</font></small>
								
							</td>
							<td>
								<span class="fas fa-check fa-lg"></span> <font color="gray">*</font>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span> <font color="gray">**</font>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span> <font color="gray">**</font>
							</td>
						</tr>
						-->
						<tr>
							<td>Support via mail<br>
								<small><font color="gray">*</font> PRIO</small>
							</td>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span> <font color="gray">*</font>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span> <font color="gray">*</font>
							</td>
						</tr>
						<!--
						<tr>
							<td>Support via telefoon<br>
							</td>
							<td>
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
							<?php } ?>
							<td>
								<span class="fas fa-check fa-lg"></span>
							</td>
						</tr>
						-->
<tr class="warning">
    <td colspan="<?php echo ($superuser == "club") ? '4' : '3'; ?>" align="center">PRIJS</td>
</tr>
<tr>
    <td>Prijs per jaar</td>
    <td>GRATIS</td>
    <?php if ($superuser == "club") { ?>
        <td>
            <?php
        // Assuming you have a way to obtain the user ID of the currently logged-in user
        $loggedInUserId = $id; // Replace this with the actual user ID

        // Now use $loggedInUserId in your query to retrieve the user's information
        $users = mysqli_query($con, "SELECT * FROM users WHERE id = $loggedInUserId");
    while ($u = mysqli_fetch_array($users)) {
        
        // Assuming $u['registered_on'] contains the registration date as a Unix timestamp
        $registrationDate = $u['registered_on'];
        $cutoffDate = strtotime('2023-12-15');

        // Convert Unix timestamp to a human-readable format for debugging output
        $formattedRegistrationDate = date('Y-m-d', $registrationDate);

        // Check if registered before or after the cutoff date
        $isExistingCustomer = ($registrationDate < $cutoffDate);

        // Debugging output
    /*    echo "User ID: " . $u['id'] . "<br>";
        echo "Registration Date: " . $formattedRegistrationDate . "<br>";
        echo "Cutoff Date: " . date('Y-m-d', $cutoffDate) . "<br>";
        echo "Condition Result: " . (($registrationDate < $cutoffDate) ? 'true' : 'false') . "<br>";
        echo "Is Existing Customer: " . ($isExistingCustomer ? 'true' : 'false') . "<br>";*/


        if ($isExistingCustomer) {
            $priceToShow = $level_1_price;
        } else {
            $priceToShow = $newLevel1Price;
        }

        echo $priceToShow . ' euro';
    }
        ?>
        </td>
    <?php } ?>
    <td>
        <?php
        // Assuming you have a way to obtain the user ID of the currently logged-in user
        $loggedInUserId = $id; // Replace this with the actual user ID

        // Now use $loggedInUserId in your query to retrieve the user's information
        $users = mysqli_query($con, "SELECT * FROM users WHERE id = $loggedInUserId");
    while ($u = mysqli_fetch_array($users)) {
        
        // Assuming $u['registered_on'] contains the registration date as a Unix timestamp
        $registrationDate = $u['registered_on'];
        $cutoffDate = strtotime('2023-12-15');

        // Convert Unix timestamp to a human-readable format for debugging output
        $formattedRegistrationDate = date('Y-m-d', $registrationDate);

        // Check if registered before or after the cutoff date
        $isExistingCustomer = ($registrationDate < $cutoffDate);

        // Debugging output
      /*  echo "User ID: " . $u['id'] . "<br>";
        echo "Registration Date: " . $formattedRegistrationDate . "<br>";
        echo "Cutoff Date: " . date('Y-m-d', $cutoffDate) . "<br>";
        echo "Condition Result: " . (($registrationDate < $cutoffDate) ? 'true' : 'false') . "<br>";
        echo "Is Existing Customer: " . ($isExistingCustomer ? 'true' : 'false') . "<br>";*/


        if ($isExistingCustomer) {
            $priceToShow = $level_2_price;
        } else {
            $priceToShow = $newLevel2Price;
        }

        echo $priceToShow . ' euro';
    }
        ?>

    </td>
</tr>
						<tr>
							<td></td>
							<td>
								
							</td>
							<?php if($superuser == "club"){ ?>
							<td>
							<div class="dropup">
							  <button class="btn btn-primary dropdown-toggle btn-xs" type="button" data-toggle="dropdown">actie
							  <span class="caret"></span></button>
							  <ul class="dropdown-menu dropdown-menu-right width-200">
							    <?php if(checkPremiumLevel($superuser_id, $superuser, "") == "level 1"){ 
									?>
									<li><a role="button" id="1" name="0" class="GoCheckout"><span class="fa fa-shopping-cart pull-right"></span> verleng</a></li>
									<?php 
								}
								else { ?>
								<li><a role="button" id="1" name="0" class="GoCheckout"><span class="fa fa-shopping-cart pull-right"></span> selecteer</a></li>
								<?php } ?>
							    <?php 
									//if trial for level 1 or 2 used -> then no more trial possible
									$check = mysqli_query($con,"SELECT * FROM premium WHERE level IN(1,2) AND " . $superuser . "_id = $superuser_id"); 
										  if(mysqli_num_rows($check) > 0){ ?>
											  
										<li><a role="button" disabled style="color: lightgray"><span class="fa fa-shopping-bag pull-right" style="color: lightgray"></span> probeer uit</a></li>
										<?php
											}
											else {
												?>
										<li><a role="button" id="1" name="1" class="GoCheckout"><span class="fa fa-shopping-bag pull-right"></span> probeer gratis</a></li>
												<?php
											}
											?>
							    <li><a role="button" class="GoCoupon"><span class="fa fa-code pull-right"></span> ik heb een coupon code</a></li>
							  </ul>
							</div>
							</td>
							<?php } ?>
							<td>
							<div class="dropup">
							  <button class="btn btn-primary dropdown-toggle btn-xs" type="button" data-toggle="dropdown">actie
							  <span class="caret"></span></button>
							  <ul class="dropdown-menu dropdown-menu-right width-200">
							    <?php if(checkPremiumLevel($superuser_id, $superuser, "") == "level 2"){ 
									?>
									<li><a role="button" id="2" name="0" class="GoCheckout"><span class="fa fa-shopping-cart pull-right"></span> verleng</a></li>
									<?php 
								}
								elseif(checkPremiumLevel($superuser_id, $superuser, "") == "level 1") { ?>
								<li><a role="button" id="2" name="0" class="GoCheckout"><span class="fa fa-shopping-cart pull-right"></span> upgrade</a></li>
								<?php }
								else { ?>
								<li><a role="button" id="2" name="0" class="GoCheckout"><span class="fa fa-shopping-cart pull-right"></span> selecteer</a></li>
								<?php } ?>
							    <?php 
									//if trial for level 1 or 2 used -> then no more trial possible
									$check = mysqli_query($con,"SELECT * FROM premium WHERE level IN(1,2) AND " . $superuser . "_id = $superuser_id");  
										  if(mysqli_num_rows($check) > 0){ ?>
											  
										<li><a role="button" disabled style="color: lightgray"><span class="fa fa-shopping-bag pull-right" style="color: lightgray"></span> probeer uit</a></li>
										<?php
											}
											else {
												?>
										<li><a role="button" id="2" name="1" class="GoCheckout"><span class="fa fa-shopping-bag pull-right"></span> probeer gratis</a></li>
												<?php
											}
											?>
							    <li><a role="button" class="GoCoupon"><span class="fa fa-code pull-right"></span> ik heb een coupon code</a></li>
							  </ul>
							</div>
							</td>
							</tr>
					</tbody>
					</table>

<div class="modal fade modalpremiuminfo" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">
	        <?php echo $appname ?> Premium
	       </h5>
      </div>
      <div class="modal-body">
	      <div class="PremiumInfoDiv"></div>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary btn-block btn-sm" data-dismiss="modal"><span class="fa fa-times fa-fw"></span>&nbsp;Sluit</button>
      </div>
    </div>
  </div>
</div>



<script type="text/javascript">
$('.GoCheckout').on('click', function() {
	var level = this.id;
	var trial = this.name;
	$('.modalpremiuminfo').modal('show');
    $('.PremiumInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	$('.PremiumInfoDiv').load('superuser_premium_checkout.php?get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&level='+level+'&trial='+trial);
});


$('.GoCoupon').on('click', function() {
	$('.modalpremiuminfo').modal('show');
    $('.PremiumInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	$('.PremiumInfoDiv').load('superuser_premium_coupon.php?get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>');
});
</script>
<?php include("footer.php")	?>				
