<?php 
include('login/includes/api.php');
logged_in();
include('functions.php');
include('superuser_security.php');

//////////////////////////////////////////////////////////////////
//ADD PAYBUTTON TO EVENT/ACTIVITY IN CALENDAR
//////////////////////////////////////////////////////////////////

$eventID = 0;
if(isset($_GET['event_id']) && is_numeric($_GET['event_id']) && !empty($_GET['event_id']) && ($_GET['event_id']) <> 0){  
	$eventID=mres($_GET['event_id']);
}
if(isset($_POST['event_id']) && is_numeric($_POST['event_id']) && !empty($_POST['event_id']) && ($_POST['event_id']) <> 0){  
	$eventID=mres($_POST['event_id']);
}
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h5 class="modal-title">Shop-items bij de activiteit van <?php echo dutchDateFromEvent($eventID) ?></h5>
  </div>
  <div class="modal-body">
	  <!-- container for finished or new survey -->
	  <div class="DivPayButtonsInclude">

<?php		
//QUERY
$sql = mysqli_query($con,"SELECT paybuttons.id AS button_id, paybuttons.*, " . $superuser . "s_paybuttons.* FROM " . $superuser . "s_paybuttons
JOIN paybuttons ON paybuttons.id = " . $superuser . "s_paybuttons.button_id
WHERE " . $superuser . "s_paybuttons." . $superuser . "_id = $superuser_id AND " . $superuser . "s_paybuttons.event_id = $eventID");
$numrows = mysqli_num_rows($sql);
if($numrows > 0){
while($row_button = mysqli_fetch_array($sql)) {
	$button_id = $row_button['button_id'];
	?>
	
	<div class="dropdown pull-right">
	<button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
	<span class="fa fa-times" aria-hidden="true"></span>
	</button>
	<ul class="dropdown-menu">
		<li><a class="UnlinkPayButton" id="<?php echo $button_id ?>" name="<?php echo $eventID ?>">Koppel shop-item los van dit agendapunt</a></li>
	</ul>
	</div>
	<div class="pull-right">
		&nbsp;|&nbsp;
	</div>
	<a href="<?php echo $full_path ?>/superuser_paybuttons_details.php?<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&type=<?php echo $row_button['type'] ?>&button_id=<?php echo $button_id ?>&test=1" target="_blank" role="button" class="btn btn-primary btn-xs <?php if($row_button['archive'] == 1) { echo "disabled"; } ?>"><span class="fa fa-credit-card"></span> <?php echo $row_button['title'] ?></a>
	<a class="btn btn-default btn-xs pull-right PayButtonDetails" id="<?php echo $button_id ?>"><span class="fa fa-folder-open"></span> details</a><br><br>
	<?php
}
}
else {
	?>
	<ul class="media-list alert alert-warning padding-left-10">
		  <li class="media">
		    <div class="media-left"><span class="fa fa-info fa-lg bg-icons bg-color-1 icon-circle"></span></div>
		    <div class="media-body">
		      <div class="media-heading"><strong>Er zijn nog geen shop-items toegevoegd aan de activiteit van <?php echo dutchDateFromEvent($eventID) ?>.</strong>
		      </div>
				</div>
		  </li>
		</ul>
	<?php
	}
	?>

</div> <!-- end DivPayButtonsInclude -->
  </div>
<div id="myFooterPayButtons<?php echo $eventID ?>" class="modal-footer">
	    <div class="dropup">
		    <button class="btn btn-success btn-block dropdown-toggle AddNewPayButtonModal" type="button" data-toggle="dropdown">
		    <span class="fa fa-plus"></span> Voeg shop-item toe <span class="caret"></span>
		    </button>
		    <ul class="dropdown-menu width-100-percent">
			    <li><a class="<?php 
						if(is_allowedLevel("2")){
							if(userValue(null,"paypal_id") == '' && userValue(null,"mollie_key") == ''){
								echo "NoPayPalMollie";
							}
							else { 
							echo "MakeNewPayButton"; 
							}
						} else 
							{ 
							echo "notifit_custom WhyCantIAddPaybutton"; 
							} 
						?>" id="<?php echo $superuser ?>">Maak een nieuw shop-item aan</a></li>
			    <li><a class="ShowListPayButtons">Koppel een actieve shop-item aan dit agendapunt</a></li>
			</ul>
		</div>
		
		<a class="btn btn-success btn-sm AddPayButton SaveButton btn-block" style="display: none" role="button"><span class="fas fa-credit-card"></span> Voeg shop-item toe</a>
		<a class="btn btn-warning btn-sm AddPayButton SaveButton NextStep btn-block" role="button" style="display: none"><span class="fa fa-chevron-right"></span> Volgende stap</a>
		
	    <a class="btn btn-warning btn-block btn-sm BackButtonPayButtonModal" role="button" style="display: none"><span class="fa fa-chevron-left"></span> Ga terug</a>
        <button type="button" class="btn btn-primary btn-sm ClosePayButtons btn-block" name="<?php echo $subcategory_id ?>" id="<?php echo $eventID ?>" data-dismiss="modal"><span class="fa fa-times" aria-hidden="true"></span>&nbsp;Sluit</button>

	</div>
</div>

<script type="text/javascript">
	var superuser = "<?php echo $superuser ?>";
	var superuser_id = "<?php echo $superuser_id ?>";
	var subcategory_id = "<?php echo $subcategory_id ?>";
	var event_id = "<?php echo $eventID ?>";
	var urlsecurity = "?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>";
	var thisurl = "superuser_paybuttons.php";
	var targetdiv = ".DivPayButtonsInclude";
</script>
		
