<?php 
include('login/includes/api.php');
logged_in();
include('functions.php');
include('superuser_security.php');
allowedLevels("2");
setlocale(LC_ALL, 'nl_NL');

$eventID = 0;
$string = "";

//////////////////////////////////////////////////////////////////
//INSERT OR EDIT A PAYBUTTON
//////////////////////////////////////////////////////////////////

if(isset($_GET['event_id']) && is_numeric($_GET['event_id']) && !empty($_GET['event_id']) && ($_GET['event_id']) <> 0){  
	$eventID=mres($_GET['event_id']);
	$divtarget = ".DivPayButtonsInclude";
}
else {
	$eventID = 0;
	$divtarget = "#Div" . $subcategory_id;
}

//make personal dir for club inside clubs_images folder
if (!is_dir('../data/' . $superuser . 's_webshop/'.$superuser_id)) {
    mkdir('../data/' . $superuser . 's_webshop/'.$superuser_id, 0777, true);
}

$button_id = 0;
$title = "";
$message = "";
$invoice = "";
$startdate = date('Y-m-d');
$enddate = date('Y-m-d');
$paydynamic = 0;
$amount = "";
$ForAll = 0;
$type = 0;

if(isset($_GET['button_id']) && is_numeric($_GET['button_id']) && !empty($_GET['button_id']) && ($_GET['button_id']) <> 0){  
	$button_id=mres($_GET['button_id']);

	$sql_button = mysqli_query($con,"SELECT * FROM paybuttons WHERE " . $superuser . "_id = $superuser_id AND id = $button_id");
	$row_button = mysqli_fetch_array($sql_button);
	$title = $row_button['title'];
	$message = $row_button['message'];
	$invoice = $row_button['invoice'];
	$startdate = $row_button['startdate'];
	$enddate = $row_button['enddate'];
	$paydynamic = $row_button['paydynamic'];
	$amount = $row_button['amount'];
	$ForAll = $row_button['ForAll'];
	$type = $row_button['type'];
}
?>


<script>
tinymce.remove("#message");
tinymce.init({
  selector: 'textarea#message',
  height: 250,
  menubar: false,
  language_url : 'js/nl_tinymce5.js', 
  language : "nl",
  plugins: [
    'advlist autolink lists link image charmap print preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table paste code help wordcount'
  ],
  plugins: [
    'advlist autolink lists link',
    'media table',
    'emoticons textpattern preview'
  ],
  toolbar1: 'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
  toolbar2: 'forecolor backcolor emoticons link preview | fontselect fontsizeselect',
  content_css: [
	//'css/style_tinymce.css?time=' + new Date().getTime()
  ]
});
  </script>
  	
<?php if($eventID == 0){ ?>
<div align="center" class="titleunderbuttons"><?php 
	if($button_id == 0){ echo "Maak een nieuw shop-item aan";
		}
		else {
			echo "Bewerk shop-item";
			} 
		?>
<?php if($eventID == "0") { ?><a class="btn btn-default pull-right PayButtonMoreInfo" subcategory_attr="<?php echo $subcategory_id ?>"><span class="fal fa-times fa-2x"></span></a><?php } ?></div><br>
<?php }
if($button_id <> 0){ ?>
<ul class="media-list alert alert-warning padding-left-10">
  <li class="media">
    <div class="media-left"><span class="fa fa-info fa-lg bg-icons bg-color-1 icon-circle"></span></div>
    <div class="media-body">
      <div class="media-heading"><strong>Tip!</strong><br>
	      Gebruik deze functie niet om een oude shop-item te hergebruiken. Elke knop heeft zijn eigen uniek id, waar de betalingen aan gekoppeld worden.<br>
	      Maak voor een nieuwe betaalaanvraag dus een nieuwe knop aan, zodat je weer duidelijk kan opvolgen wie wel of niet betaald heeft.
      </div>
	</div>
  </li>
</ul>
<?php } ?>


		<div class="control-group">
		    <label class="control-label">Korte titel van de knop *:</label> <?php echo helpPopup("titlepaybutton", "black", "question-circle", "info", "") ?>
		    <div class="controls">
		      <input type="text" class="form-control-custom" name="title" id="title" value="<?php echo $title ?>" placeholder="bv. Betaal je lidgeld"/>
		      <p class="help-block" style="color: red"></p>
		    </div>
		</div>
		<div class="control-group">
		    <label class="control-label">Omschrijving (geef meer uitleg over de betaling - optioneel):</label>
		    <div class="controls">
		      <textarea rows="5" name="message" id="message" class="form-control-custom"><?php echo $message ?></textarea>
		      <p class="help-block" style="color: red"></p>
		    </div>
		</div>
		<div class="control-group">
			<label class="control-label">Upload een foto/afbeelding (optioneel):</label>
			<div class="controls">
			<input id="images" name="images[]" type="file" accept="image/*" placeholder="Selecteer afbeelding..."  multiple>	
			<?php if($button_id <> 0){
			    if($row_button['picture'] <> ""){ 
				echo "<label class='control-label'>Huidige afbeelding:</label><br>";
				echo "<strong>Selecteer hierboven een nieuwe afbeelding om deze te overschrijven.</strong><br>";
				echo "<img height='120px;' src='/data/" . $superuser ."s_webshop/" . $superuser_id ."/" . $row_button['picture'] . "'>";
				}
				
			}
			?>
			</div>
		</div>
		<div class="control-group">
		    <label class="control-label">Jouw referentie (optioneel):</label> <?php echo helpPopup("whatsreference", "black", "question-circle", "info", "") ?>
		    <div class="controls">
		      <input type="text" class="form-control-custom" name="invoice" id="invoice" value="<?php echo $invoice ?>" placeholder="bv. Lidgeld 2018-2019"/>
		      <p class="help-block" style="color: red"></p>
		    </div>
		</div>

		<div class="control-group">
			<label class="control-label">Startdatum shop-item *:</label> <?php echo helpPopup("whatstimeframepaybutton", "black", "question-circle", "info", "") ?>
		    <div id="datepicker_start" data-date="<?php echo $startdate ?>"></div>
			<input type="hidden" name="startdate" id="startdate" value="<?php echo $startdate ?>">
			</div>

        <br>
		<div class="control-group">
			<label class="control-label">Einddatum betaling *:</label>
		    <div id="datepicker_end" data-date="<?php echo $enddate ?>"></div>
			<input type="hidden" name="enddate" id="enddate" value="<?php echo $enddate ?>">
			</div>

        <br>
        <div class="control-group">
		    <label class="control-label">Bedrag betaling *: </label>
		    <div class="controls">
			    <select class="form-control-custom paydynamic" name="paydynamic" id="paydynamic">
				    <option value="0" <?php if($paydynamic == "0") { echo "selected"; } ?>>Ik vraag een vast bedrag voor <?php echo $superuser_default ?></option>
				    <option value="3" <?php if($paydynamic == "3") { echo "selected"; } ?>>Ik geef een aantal keuzes voor <?php echo $superuser_default ?></option>
				    <option value="1" <?php if($paydynamic == "1") { echo "selected"; } ?>>Gebruiker mag zelf een bedrag ingeven.</option>
				    <option value="2" <?php if($paydynamic == "2") { echo "selected"; } ?>>Lijst met foto’s en prijzen, via bestelformulier</option>
			    </select>
			<br>
		    </div>
        </div>   
        
        <div class="payfixed" <?php if($paydynamic <> "0") { echo "style='display:none'"; } ?>>
			<label class="control-label">Vast bedrag betaling (minimum 1 euro) *:</label> <?php echo helpPopup("whatsfee", "red", "exclamation-circle", "info", "") ?>
			<div class="input-group">
			  <span class="input-group-addon" id="basic-addon3">&euro;</span>
			  <input type="text" class="form-control-custom" id="amount" name="amount" value="<?php echo $amount ?>" aria-describedby="basic-addon3">
			</div>
		</div>
		<div class="paydynamic1" <?php if($paydynamic <> "1") { echo "style='display:none'"; } ?>>
			<label class="control-label">Variabel bedrag betaling:</label> <?php echo helpPopup("dynamicpaybutton", "black", "question-circle", "info", "") ?> <?php echo helpPopup("whatsfee", "red", "exclamation-circle", "info", "") ?>
			<div class="input-group">
			  <span class="input-group-addon" id="basic-addon3">&euro;</span>
			  <input type="text" class="form-control-custom" id="amount" name="amount" aria-describedby="basic-addon3" disabled="">
			</div>
		</div>
		<div class="paydynamic2" <?php if($paydynamic <> "2") { echo "style='display:none'"; } ?>>
			<label class="control-label">Variabel bedrag betaling via bestelformulier:</label>
			<div class="input-group">
			  <ul class="media-list alert alert-info padding-left-10">
			  <li class="media">
			    <div class="media-left"><span class="fa fa-info fa-lg bg-icons bg-color-4 icon-circle"></span></div>
			    <div class="media-body">
			      <div class="media-heading">
				      Je hebt gekozen om met een bestelformulier te werken (zoals bv. inschrijving voor een eetfeest).<br>
				      Klik op 'Volgende stap' onderaan de pagina om je bestelformulier aan te maken.
			      </div>
				</div>
			  </li>
			</ul>
			</div>
		</div>
		<div class="paydynamic3" <?php if($paydynamic <> "3") { echo "style='display:none'"; } ?>>
			<label class="control-label">Selectielijst:</label>
			<div class="input-group">
			  <ul class="media-list alert alert-info padding-left-10">
			  <li class="media">
			    <div class="media-left"><span class="fa fa-info fa-lg bg-icons bg-color-4 icon-circle"></span></div>
			    <div class="media-body">
			      <div class="media-heading">
				      Je hebt gekozen om meerdere opties/prijzen in te geven.<br>
				      Klik op 'Volgende stap' onderaan de pagina om deze opties in te geven.
			      </div>
				</div>
			  </li>
			</ul>
			</div>
		</div>
        <div class="control-group">
		    <label class="control-label">Deze shop-item is van toepassing op...</label> <?php echo helpPopup("locationpaybutton", "black", "question-circle", "info", "") ?>
		    <div class="controls">
			    <select class="form-control-custom ForAll" id="ForAll" name="ForAll">
				    <option value="0" <?php if($ForAll == 0){ echo "selected"; } ?>>... <?php echo userValueSubcategory($subcategory_id, $superuser, "cat_name") ?></option>
				    <option value="1" <?php if($ForAll == 1){ echo "selected"; } ?>>... alle subgroepen van je <?php echo $superuser_default ?></option>
				    <option value="2" <?php if($ForAll == 2){ echo "selected"; } ?>>... meerdere subgroepen van je <?php echo $superuser_default ?>...</option>
				    <option value="3" <?php if($ForAll == 3){ echo "selected"; } ?>>... enkele leden...</option>
			    </select>
			<br>   
		    <div id="SomeSubcategories" class="margin-left-20" style="<?php 
			    if($button_id <> 0){ 
				    if($row_button['ForAll'] <> "2") { echo "display: none"; } 
				}
				if($button_id == 0){
					echo "display: none";
				} ?>">
			    <ul class="subcategory-list">
			        <?php
				$sql_subcategories = mysqli_query($con,"SELECT id,cat_name FROM " . $superuser . "s_categories WHERE " . $superuser . "_id = $superuser_id ORDER BY row_order, board ASC, start_year, cat_name");
				if(mysqli_num_rows($sql_subcategories) > 0) {
				while($row_subcategory=mysqli_fetch_array($sql_subcategories)){
					$checked = "";
					$gray = "";
					$disabled = "";
					if($button_id <> 0){ 
					$list = explode(',',$row_button['ForSome']);
					if(in_array($row_subcategory['id'], $list) || ($row_subcategory['id'] == $subcategory_id)) {
					  $checked = "checked";
					  
					}
					}
					if($row_subcategory['id'] == $subcategory_id){
						$checked = "checked";
						$disabled = " disabled";
						$gray = "style='color:lightgray'";
					}
					?>
					<li class="mobile-list">
					<label class="checklabel">
		          <input type="checkbox" class="CopyToSubcategory" name="CopyToSubcategory[]" value="<?php echo $row_subcategory['id'] ?>" style="display:none" <?php echo $checked . " " . $disabled ?>><span <?php echo $gray ?>><?php echo $row_subcategory['cat_name'] ?></span><i class="far fa-square fa-lg pull-right"></i><i class="fa fa-check-square fa-lg pull-right" <?php echo $gray ?>></i>
		        </label>
					</li>												<?php
					}
					}
				?>
			    </ul>
				<!-- disabled checkbox doesn't show up in $_POST -->
		        <input type="hidden" name="CopyToSubcategory[]" value="<?php echo $subcategory_id ?>">
				<br>
		        </div>
		        <div id="SomeStudents" class="margin-left-20" style="<?php 
			    if($button_id <> 0){ 
				    if($row_button['ForAll'] <> "3") { echo "display: none"; } 
				}
				if($button_id == 0){
					echo "display: none";
				} ?>">
									<ul class="subcategory-list">
							        <?php
								        
								  //QUERY KIDS
									$sql = mysqli_query($con,"SELECT kid_id AS member FROM kids_" . $superuser . "s 
									WHERE " . $superuser . "_id = $superuser_id
									AND subcategory = $subcategory_id
									AND kid_id NOT IN (SELECT id FROM kids WHERE inactive = 1)");
									$cnt = mysqli_num_rows($sql);
									
									//QUERY ADULTS
									$sql_parents = mysqli_query($con,"SELECT parent_id AS member FROM parents_" . $superuser . "s 
									WHERE " . $superuser . "_id = $superuser_id AND parents_" . $superuser . "s.subcategory = $subcategory_id");
									$cnt_parents = mysqli_num_rows($sql_parents);
									
									//QUERY MANUALLY ADDED KIDS
									$sql_manual = mysqli_query($con,"SELECT id AS member FROM kids_" . $superuser . "s_manual WHERE " . $superuser . "_id = $superuser_id AND subcategory = $subcategory_id");
									$cnt_manual = mysqli_num_rows($sql_manual);

									
									if($cnt > 0) {
									while($row_members=mysqli_fetch_array($sql)){
									$checked = "";
							          if($button_id <> 0){
							          $listkids = explode(',',$row_button['ForKids']);
										if(in_array($row_members['member'], $listkids)) {
										  $checked = "checked";
										}
									  }
									?>
										<!-- checkboxes -->
										<li class="mobile-list">
									    <label class="checklabel">
									    
								          <input type="checkbox" class="CheckboxForKids" style="display:none" value="<?php echo $row_members['member'] ?>" <?php echo $checked ?>>
								          <span><?php echo userValueKid($row_members['member'], "name") . " " .  userValueKid($row_members['member'], "surname") ?></span>
								          <i class="far fa-square fa-lg pull-right"></i><i class="fa fa-check-square fa-lg pull-right"></i>
								          <div class="pull-right" style="margin-right: 10px">
										  <?php showLabels($row_members['member'], "0", "0", $superuser_id, $superuser, $subcategory_id, "false") ?>
										  </div>
								        </label>
										</li>
									<?php
										}
										}
									
									if($cnt_parents > 0) {
									while($row_parents=mysqli_fetch_array($sql_parents)){
										$checked = "";
										if($button_id <> 0){
										$listadults = explode(',',$row_button['ForAdults']);
												if(in_array($row_parents['member'], $listadults)) {
												  echo "checked";
												}
										}
									?>
										<!-- checkboxes -->
										<li class="mobile-list">
									    <label class="checklabel">
								          <input type="checkbox" class="CheckboxForAdults"  style="display:none" value="<?php echo $row_parents['member'] ?>" <?php echo $checked ?>><span><?php echo userValue($row_parents['member'], "username") ?></span><i class="far fa-square fa-lg pull-right"></i><i class="fa fa-check-square fa-lg pull-right"></i>
								          <div class="pull-right" style="margin-right: 10px">
										  <?php showLabels("0", $row_parents['member'], "0", $superuser_id, $superuser, $subcategory_id, "false") ?>
										  </div>
								        </label>
										</li>
									<?php
										}
										}
									
									if($cnt_manual > 0) {
									while($row_manual=mysqli_fetch_array($sql_manual)){
										$checked = "";
										if($button_id <> 0){
											$listmanual = explode(',',$row_button['ForManual']);
											if(in_array($row_manual['member'], $listmanual)) {
											  echo "checked";
											}
										}
									?>
										<!-- checkboxes -->
										<li class="mobile-list">
									    <label class="checklabel">
								          <input type="checkbox" class="CheckboxForManual" style="display:none" value="<?php echo $row_manual['member'] ?>"
								          <?php echo $checked ?>><span><?php echo userValueManual($row_manual['member'], $superuser, $superuser_id, "name") . " " .  userValueManual($row_manual['member'], $superuser, $superuser_id, "surname") ?></span><i class="far fa-square fa-lg pull-right"></i><i class="fa fa-check-square fa-lg pull-right"></i>
								          <div class="pull-right" style="margin-right: 10px">
										  <?php showLabels("0", "0", $row_manual['member'], $superuser_id, $superuser, $subcategory_id, "false") ?>
										  </div>
								        </label>
										</li>
									<?php
										}
										}   
								?>
									</ul>
								<br>
						        </div>
		    </div>
		</div>

		<div class="control-group">
		    <label class="control-label">Ontvang betalingen via:</label> <?php echo helpPopup("whatsmollie", "black", "question-circle", $superuser, "") ?>
		    <div class="controls">
			    <select class="form-control-custom" id="type" name="type">
				    
					 <?php
					    if(userValue(null, "mollie_key") != ""){ ?>
				    <option value="1" <?php if($type == 1){ echo "selected"; } ?>>Mollie.com (alle betaalmethodes)</option>
				    <?php 
					    }
					    else {
						    ?>
						 <option value="1" disabled>Alle betaalmethodes via Mollie (Mollie niet geconfigureerd in accountpagina)</option>
						    <?php
					    }
					    
					    ?>
					    <option value="0" <?php if($type == 0){ echo "selected"; } ?>>PayPal.com (enkel PayPal)</option>
			    </select>
			<br>  
		</div> 

		<div align="center">
		<input type="hidden" name="subcategory_id" id="subcategory_id" value="<?php echo $subcategory_id ?>">
		<input type="hidden" name="event_id" id="event_id" value="<?php echo $eventID ?>">
		<input type="hidden" name="addedfrom" id="addedfrom" value="<?php 
			if($eventID == "0"){ 
			echo "list"; 
			}
			else {
				echo "calendar";
				}
			?>">
		<?php if($eventID == 0){ ?>
		<input type="hidden" name="button_id" id="button_id" value="<?php echo $button_id ?>">
		<a class="btn btn-success btn-sm SaveButton btn-block" role="button" <?php if(($paydynamic == "2") || ($paydynamic == "3")){ echo "style='display:none'"; } ?>><span class="fas fa-credit-card"></span> Bewaar shop-item</a>
		<a class="btn btn-warning btn-sm SaveButton NextStep btn-block" role="button" <?php if(($paydynamic != "2") && ($paydynamic != "3")){ echo "style='display:none'"; } ?>><span class="fa fa-chevron-right"></span> Volgende stap!</a>
		<a class="btn btn-primary btn-sm btn-block PayButtonDetails" role="button"><span class="fa fa-times"></span> Ga terug</a>
		<?php } ?>
		</div>
		<br>
		
       </div>
       </div>



<br>  
<script type="text/javascript">
var ListSubcategories = "";
var id_list_kids = "";
var id_list_adults = "";
var id_list_manual = "";
var event_id = "<?php echo $eventID ?>";
var divtarget = "<?php echo $divtarget ?>";

//when used in modal calendar
if(event_id != 0){
$('.SaveButton').show();
}
$('.BackButtonPayButtonModal').show();

/*  
$("input[name='amount']").on("input", CalculatePercentage);
function CalculatePercentage() {
        var price = parseInt($("#amount").val());
        CalculateResult = (((price / 100) * 3.4) + 0.35).toFixed(2);
        $(".CalculateResult").html('<span class="fa fa-info-circle fa-fw"></span> PayPal zal maximum ' + CalculateResult + ' euro afhouden van dit bedrag');
        }
*/
    
$(".paydynamic").on('change', function() {
	   var val = this.value;
       if(val == '0') {
            $('.paydynamic1').hide();
            $('.paydynamic2').hide();
            $('.paydynamic3').hide();
            $('.payfixed').show();
            $('.SaveButton').show();
            $('.NextStep').hide(); 
       }
       if(val == '1') {
            $('.paydynamic1').show();
            $('.paydynamic2').hide();
            $('.paydynamic3').hide();
            $('.payfixed').hide(); 
            $('.SaveButton').show();
            $('.NextStep').hide(); 
       }
       if(val == '2') {
            $('.paydynamic1').hide();
            $('.paydynamic2').show();
            $('.paydynamic3').hide();
            $('.payfixed').hide();
            $('.SaveButton').hide();
            $('.NextStep').show();  
       }
       if(val == '3') {
            $('.paydynamic1').hide();
            $('.paydynamic2').hide();
            $('.paydynamic3').show();
            $('.payfixed').hide();
            $('.SaveButton').hide();
            $('.NextStep').show();  
       }
	}); 
	

//go back if event_id = 0 (if event_id <> 0 there is button in clubs_calendar_include)
$(".PayButtonDetails").on('click', function() {
    tinymce.remove("#message");
    $('#Div<?php echo $subcategory_id ?>').load('superuser_paybuttons_list.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>');
});
	
//datepicker start
$('#datepicker_start').datepicker({
		format: 'yyyy-mm-dd',
    language: "nl",
    startDate: '<?php echo date('Y-m-d') ?>',
    todayHighlight: true
	});
$('#datepicker_start').on('changeDate', function() {
    $('#startdate').val(
        $('#datepicker_start').datepicker('getFormattedDate')
    );
});

//datepicker end
$('#datepicker_end').datepicker({
		format: 'yyyy-mm-dd',
    language: "nl",
    startDate: '<?php echo date('Y-m-d') ?>',
    todayHighlight: true
	});
$('#datepicker_end').on('changeDate', function() {
    $('#enddate').val(
        $('#datepicker_end').datepicker('getFormattedDate')
    );
});
	

//save buton
$(document).off('click','.SaveButton').on("click",".SaveButton",function(e) {
e.preventDefault();

	var title = $('#title').val();
	if (title.length > 0)
    {
    }
    else {
        NotifError("fillallfields");
		return false;
    }
    
    var paydynamic = $('.paydynamic').val();
    if(paydynamic === "0") { 
    
    var amount = $('#amount').val();
    if (amount.length  >   0) 
    {
    }
    else {
        NotifError("fillallfields");
		return false;
    }
	
	
	var price = parseInt($("#amount").val());
    if(price >= 1){
		    }
		    else {
		        NotifError("minimumvalue"); //Show error
				return false;
		    }
	}
	
	var CopyToSubcategory = [];
	$('.CopyToSubcategory:checked').each(function(i){
          CopyToSubcategory[i] = $(this).val();
        });
    ListSubcategories = CopyToSubcategory;
    
    var ForKids = [];
    $('.CheckboxForKids:checked').each(function(i){
          ForKids[i] = $(this).val();
        });
    id_list_kids = ForKids;
    
    var ForAdults = [];
    $('.CheckboxForAdults:checked').each(function(i){
          ForAdults[i] = $(this).val();
        });
    id_list_adults = ForAdults;
    
    var ForManual = [];
    $('.CheckboxForManual:checked').each(function(i){
          ForManual[i] = $(this).val();
        });
    id_list_manual = ForManual;
	
	//console.log("kids: " + id_list_kids + " - adults: " + id_list_adults + " - manual: " + id_list_manual);

//get the message
tinymce.triggerSave();

//console.log("subgroups: " + ListSubcategories + " & ForKids: " + id_list_kids + " & ForAdults: " + id_list_adults + " & ForManual: " + id_list_manual);

//upload the attachments
$('#images').fileinput('upload');
});

$("#images").fileinput({
    uploadAsync: false,
    maxFileSize: 5000,
    theme: 'fa',
    language: "nl",
    showUpload: false,
    dropZoneEnabled: false,
    maxFileCount: 1,
    overwriteInitial: false,
    browseClass: "btn btn-success",
    allowedFileExtensions: ["jpg", "JPG", "jpeg", "png"],
    browseLabel: "Kies afbeelding",
    browseIcon: "<i class=\"fa fa-image\"></i> ",
    layoutTemplates: {actionUpload: ""},
    removeClass: "btn btn-danger",
    removeLabel: "Verwijder alles",
    removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i> ",
    uploadClass: "btn btn-info",
    uploadLabel: "Upload",
    uploadIcon: "<i class=\"glyphicon glyphicon-upload\"></i> ",
    uploadUrl: "superuser_actions.php?action=CreatePayButtonNew&subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>",


    uploadExtraData: function() {
        return {
            title: $("#title").val(),
            invoice: $("#invoice").val(),
            message: $("#message").val(),
            startdate: $("#startdate").val(),
            enddate: $("#enddate").val(),
            addedfrom: $("#addedfrom").val(),
            event_id: $("#event_id").val(),
            subcategory_id: $("#subcategory_id").val(), 
            amount: $("#amount").val(),
            paydynamic: $("#paydynamic").val(),
            type: $("#type").val(), 
            ForAll: $("#ForAll").val(),
            button_id: $("#button_id").val(),
            CopyToSubcategory: ListSubcategories,
            id_list_manual: id_list_manual,
            id_list_kids: id_list_kids,
            id_list_adults: id_list_adults
        };
    }
});

    $('#images').on('filebatchuploadsuccess', function(event, data, previewId, index) {
    var form = data.form, files = data.files, extra = data.extra,
        response = data.response, reader = data.reader;
        
        var paydynamic = $('.paydynamic').val();
        var button_id = response.last_id;
		console.log('File batch upload success; button_id: ' + button_id + paydynamic);
		
		if((paydynamic === "2") || (paydynamic === "3")) {
            $(divtarget).load('superuser_paybuttons_form.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&button_id='+button_id+'&subtype='+paydynamic+'&event_id=<?php echo $eventID ?>');
			}
			else {
			  if(event_id === "0"){
              $(divtarget).load('superuser_paybuttons_list.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>');
              }
              if(event_id != "0"){
              $(".CalendarInfoDiv").load("superuser_paybuttons.php?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&event_id=<?php echo $eventID ?>");
             }
              
              notif({
			  msg: "Gegevens succesvol bewaard!",
			  position: "center",
			  type: "success",
			  time: 2500,
			  multiline: true
			});
              }
    
		tinymce.remove("#message");
});

	
	

	</script>
		
	   
