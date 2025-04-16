<?php 
include('login/includes/api.php');
logged_in();
include('functions.php');
include('superuser_security.php');
allowedLevels("2");

//////////////////////////////////////////////////////////////////
//PAYBUTTON FORM
//////////////////////////////////////////////////////////////////

$subtype = "0";
$alert = "0";
$discount = "1";

if(isset($_GET['button_id']) && !empty($_GET['button_id']) && is_numeric($_GET['button_id']) && ($_GET['button_id'] <> 0)) $button_id=mres($_GET['button_id']);
if(isset($_GET['subtype']) && !empty($_GET['subtype']) && is_numeric($_GET['subtype']) && ($_GET['subtype'] <> 0)) $subtype=mres($_GET['subtype']);
if(isset($_GET['event_id']) && !empty($_GET['event_id']) && is_numeric($_GET['event_id']) && ($_GET['event_id'] <> 0)){ 
$eventID=mres($_GET['event_id']);
$divtarget = ".DivPayButtonsInclude";
}
else {
	$eventID = 0;
	$divtarget = "#Div" . $subcategory_id;
}

if($subtype == "2"){
	$discount = "1";
	$alert = "1";
}
if($subtype == "3"){
	$discount = "0";
	$alert = "2";
}
?>
<style>

</style>
<?php if($eventID == 0){ ?>
<div align="center" class="titleunderbuttons">Beheer Shop-items</div><br>
<?php } ?>

<?php if($alert <> "0"){ ?>
<ul class="media-list alert alert-info" style="padding-left: 10px">
  <li class="media">
    <div class="media-left"><span class="fa fa-info fa-lg bg-icons bg-color-4 icon-circle"></span></div>
    <div class="media-body">
      <div class="media-heading">
	      <?php if($alert == "1"){ ?>
	      Je hebt gekozen om met een bestelformulier te werken (zoals bv. inschrijving voor een eetfeest).<br>
	      1. Vul een omschrijving in en een eenheidsprijs en klik dan op 'voeg toe'.<br>
	      2. Je leden kunnen bij elk item een aantal ingeven.<br>
	      3. <?php echo $appname ?> berekent dan automatisch het totaalbedrag.
	      <?php }
		      if($alert == "2"){ ?>
		  Je hebt gekozen om verschillende opties/prijzen in te stellen.<br>
	      1. Vul een omschrijving in en een prijs en klik dan op 'voeg toe'.<br>
	      2. Je leden kunnen dan 1 van deze opties selecteren op de betaalpagina.<br>  
		  <?php } ?>
      </div>
	</div>
  </li>
</ul>
<?php } ?>

<form id="AddOptionForm">	
		<?php
		$sql_options = mysqli_query($con,"SELECT * FROM paybuttons_form WHERE " . $superuser . "_id = $superuser_id AND button_id = $button_id");
				if(mysqli_num_rows($sql_options) > 0){
				?>

				<?php
				while($row_options = mysqli_fetch_array($sql_options)){
				if (!empty($row_options['image'])) {
        echo "<img src='" . htmlspecialchars($row_options['image']) . "' alt='Afbeelding' style='max-width: 100px; margin-top: 10px;'>";
    }    
				    
				if($row_options['exclusive'] == 0){
					$subtype = "2";
					}
				if($row_options['exclusive'] == 1){
					$subtype = "3";
					}	
				?>
				<div class="input-group double-input">
		    <input type="text" name="description" value="<?php echo $row_options['description'] ?>"  class="form-control" disabled/>
		    <span class="input-group-addon" id="basic-addon3">&euro;</span>
		    <input type="text" name="amount" value="<?php echo $row_options['amount'] ?>" class="form-control" aria-describedby="basic-addon3" disabled/>
		    <span class="input-group-btn">
		        <?php 
			        echo "<div class=\"dropdown  pull-right\" style=\"display: inline-block;\"><button class=\"btn btn-danger dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\"><span class=\"fas fa-trash-alt\" aria-hidden=\"true\"></span></button><ul class=\"dropdown-menu\"><li><a class=\"DeleteFormOption\" name=\"$button_id\" id=\"$row_options[id]\" subtype_attr=\"$subtype\">Verwijder optie '$row_options[description]' </a></li></ul></div>"; 
			        ?>
		    </span>
		</div>
		<?php
		}
		}
		?>
			<label class="control-label">Geef <?php if(mysqli_num_rows($sql_options) > 0){ echo "nog"; } ?> een item in:</label>
		
		<div class="input-group double-input">
		    <input type="text" name="description" placeholder="Omschrijving" class="form-control" />
		    <span class="input-group-addon" id="basic-addon3">&euro;</span>
		    <input type="text" name="amount" placeholder="Prijs" class="form-control" aria-describedby="basic-addon3"/>
		        <input type="file" name="image" accept="image/*" class="form-control" style="margin-top: 10px;"/>

		    <span class="input-group-btn">
		        <button class="btn btn-success SubmitFormOption" type="submit" subtype_attr="<?php echo $subtype ?>"><span class="fa fa-plus"></span> <small>Voeg toe</small></button>
		    </span>
		</div><!-- /input-group -->
	
		    <div align="center">
			<input type="hidden" name="action" value="AddFormOption">
			<input type="hidden" name="subtype" value="<?php echo $subtype ?>">
			<input type="hidden" name="subcategory_id" value="<?php echo $subcategory_id ?>">
			<input type="hidden" name="button_id" value="<?php echo $button_id ?>">
			<input type="hidden" name="question_id" value="<?php echo $button_id ?>">
		    
		    
		    </div>
		</form>
		<?php if($discount == "1"){ ?>
		<table width="100%" class="table">
		<tr>
			<td align="left" width="10%">
				<label class="switch">
				<input name="ActivateDiscount" id="ActivateDiscount" class="ActivateDiscount" type="checkbox" <?php if(mysqli_num_rows(mysqli_query($con, "SELECT id FROM paybuttons WHERE id = $button_id AND activatediscount = 1")) > 0) { echo "checked"; } ?>><span class="slider round"></span>
				</label>
			</td>
			<td align="left" width="90%">
		    Laat gebruikers toe om een korting toe te passen. <?php echo helpPopup("whatsactivatediscount", "black", "question-circle", "info", "") ?>
			</td>
		</tr>
		</table>
		<?php } ?>
		<div align="center">
		<?php if($eventID == 0){ ?>
		<a class="btn btn-warning btn-sm PreviousStep btn-block" role="button"><span class="fa fa-chevron-left"></span> Vorige stap</a>
		<?php } ?>
		<a class="btn btn-success btn-sm SaveButton btn-block" id="<?php echo $button_id ?>" role="button" <?php if(mysqli_num_rows($sql_options) == 0){ echo "disabled"; } ?>><span class="fas fa-save"></span> Bewaar</a>
		</div>


<script type="text/javascript">

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

	
	$('.NextStep').hide();
	$('.BackButtonPayButtonModal').hide();
	
	
	$(".SaveButton").on('click', function(e) {
		console.log(e);
	    e.preventDefault();
	    var button_id = this.id;
	    if($('#ActivateDiscount').not(':checked').length){
	       activatediscount = "0";
	    }else{
	      activatediscount = "1";
	    };
	    
	    var submiturl = "superuser_actions.php?get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&action=SaveOrderForm"; // the script where you handle the form input.
		
			 $.ajax({
	           type: "POST",
	           url: submiturl,
	           data: {
		           button_id: button_id,
		           activatediscount: activatediscount
		           }, // serializes the form's elements.
	           success: function(data)
	           {
		           
		           notif({
							  msg: "Je hebt het bestelformulier bewaard.",
							  position: "center",
							  type: "success",
							  width: 300,
							  time: 2500,
							  autohide: true,
							  multiline: true,
							  timeout: 3000
							}); 
		       
		       <?php if($eventID == "0"){ ?>
              $('#Div<?php echo $subcategory_id ?>').load('superuser_paybuttons_list.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>');
              <?php } ?>
              <?php if($eventID <> "0"){ ?>
              $('.CalendarInfoDiv').load("superuser_paybuttons.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&event_id=<?php echo $eventID ?>");
              <?php } ?>
		       
			  },
	         });

	});
	
	//previousstep not from calendar
	$(".PreviousStep").on('click', function() {
	$('#Div<?php echo $subcategory_id ?>').load("superuser_paybuttons_new_edit.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&button_id=<?php echo $button_id ?>");
	});
	
	$('.SubmitFormOption').on('click', function(e){
	    console.log(e);
	    e.preventDefault();
	    var subtype = $(this).attr("subtype_attr");
	    
	     var submiturl = "superuser_actions.php?get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>"; // the script where you handle the form input.
		
			 $.ajax({
	           type: "POST",
	           url: submiturl,
	           data: $("#AddOptionForm").serialize(), // serializes the form's elements.
	           success: function(data)
	           {
		       $('<?php echo $divtarget ?>').load("superuser_paybuttons_form.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&button_id=<?php echo $button_id ?>&subtype="+subtype+'&event_id=<?php echo $eventID ?>');
			  },
	         });	    
	});
	
	$('.DeleteFormOption').on('click', function() {
			
			var id = this.id;
			var button_id = this.name;
			var subtype = $(this).attr("subtype_attr");
			console.log("Delete Form Option: option_id : " + id + " & button_id : " + button_id + "& subtype : " + subtype);					
		 $.ajax({
	
		    type: "POST",
		    url: "superuser_actions.php?get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&action=DeleteFormOption",
		    data:{
	          id: id,
	          button_id: button_id
	        },
		    success: function(data){
			    	  
			 $('<?php echo $divtarget ?>').load("superuser_paybuttons_form.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&button_id=<?php echo $button_id ?>&subtype="+subtype+'&event_id=<?php echo $eventID ?>');
						  
		    },
		    error: function(){
		      alert("Er is een probleem opgetreden met de <?php echo $appname ?>-app (functie: Verwijder Optie). Gelieve opnieuw te proberen.");
		    }, 
		    
		  });
		  
		});
</script>


