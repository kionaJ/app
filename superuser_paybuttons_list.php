<?php 
include('login/includes/api.php');
logged_in();
include('functions.php');
include('superuser_security.php');

$eventID = 0;
$string = "";

//////////////////////////////////////////////////////////////////
//OVERVIEW OF ALL ADDED PAYBUTTONS
//////////////////////////////////////////////////////////////////

if(isset($_GET['event_id']) && is_numeric($_GET['event_id']) && !empty($_GET['event_id']) && ($_GET['event_id']) <> 0){  
	$eventID=mres($_GET['event_id']);
	$string = "AND CURDATE() <= enddate AND archive = 0";
}

if($eventID == "0"){
?>
<div align="center" class="titleunderbuttons">Beheer je shop</div><br>
<div align="left" id="rcorners_table_rounded" class="DivMoreInfoPayButtons" style="display:none; margin-bottom: 10px">In <?php echo $appname ?> kan je shop-items genereren, die je kan gebruiken om betalingen van je leden aan je <?php echo $superuser_default ?> te organiseren.<br>
		<ul style="margin-left: 20px">
			<li><?php echo $appname ?> werkt voor het systeem met betalingen met <strong><a class="location" href="http://www.paypal.com" target="_blank">PayPal</a></strong> of met <strong><a class="location" href="http://www.mollie.com" target="_blank">Mollie</a></strong>.<br>
	<li>Voordat je van start kan gaan, hebben we enkele gegevens nodig: <a href="login/profile.php?goto=BtnEditPayButtons" class="location">Configureer shop-items in je account op <?php echo $appname ?>.</a></li>
	<li>De <i>Administrator</i> van je <?php echo $superuser_default ?> kan een shop-item toevoegen en aangeven wanneer die actief mag zijn in de gekozen subgroepen</li>
	<li>Wanneer een shop-item actief wordt, zal:
		<ol>
			<li>Deze <u>automatisch</u> in de <?php echo $appname ?>-accounts van je leden <u>verschijnen</u> (de leden waarbij de betaling van toepassing is)</li>
			<li>Deze toe te voegen zijn in een agendapunt</li>
			<li>Deze toe te voegen zijn in een mail die je naar je leden stuurt</li>
		</ol>
	</li>
	<li>Je kan erg gemakkelijk de betalingen opvolgen: in de ledenlijsten van de subgroepen staat er een <a role="button" class="labelinfo"  style="display:inline; border: 0px solid lightgray; box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; color:white; background-color: green"><span class="fa fa-euro-sign fa-fw"></span></a> achter elk lid dat betaald heeft</li>
	<li>Wanneer de einddatum van een shop-item verstreken is, zal:
	<ol>
			<li>Deze <u>automatisch</u> uit de <?php echo $appname ?>-accounts van je leden <u>verdwijnen</u></li>
			<li>Deze niet meer toe te voegen zijn in een agendapunt</li>
			<li>Deze niet meer toe te voegen zijn in een mail die je naar je leden stuurt</li>
			<li>Het betalingslabel <a role="button" class="labelinfo"  style="display:inline; border: 0px solid lightgray; box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; color:white; background-color: green"><span class="fa fa-euro-sign fa-fw"></span></a> in je ledenlijst <u>automatisch</u> verdwijnen</li>
			<li>Je kan altijd wel nog een overzicht van de betalingen bekijken hieronder op deze pagina</li>
		</ol>
	</li>
		</ul>
		<br>
</div>
<?php if((is_allowedLevel("2") && (userValue(null,"paypal_id") == '') && userValue(null,"mollie_key") == '')){ ?>
<br>
<div align="left" class="alert alert-warning" role="alert"><span class="fa fa-exclamation-circle" aria-hidden="true"></span>&nbsp;&nbsp;
	Je kan momenteel geen shop-items aanmaken, omdat we nog enkele gegevens nodig hebben.<br><br>
	<a href="login/profile.php?goto=BtnEditPayButtons" class="btn btn-success btn-block btn-sm">Configureer shop-items in je account op <?php echo $appname ?>.</a>
	<a class="btn btn-success btn-block btn-sm MoreInfoPayButtons" role="button">Ik wil meer informatie</a>
</div>
<script>
	$(".MoreInfoPayButtons").on('click', function() {
						$('.DivMoreInfoPayButtons').toggle();
						});

</script>
	<?php exit; } 
		?>


<a class="<?php if(is_allowedLevel("2")){ echo "MakeNewPayButton";  } else { echo "notifit_custom WhyCantIAddPaybuttons"; } ?> btn btn-success btn-sm btn-block" id="<?php echo $superuser ?>" role="button"><span class="fa fa-plus"></span> Maak een nieuw shop-item aan <?php if(!is_allowedLevel("2")){ ?><span class='label label-danger pull-right'>ADMIN</span><?php } ?></a>
<br>
<?php } ?>

<div class="row">
		  <div class="col-lg-12">
		    <div class="input-group">
		      <input class="form-control-custom" placeholder="Zoek shop-items..." type="text" id="customSearchBox">
			  <span class="input-group-btn">
				        <a class="btn btn-default MoreInfoPayButtons" style="background: transparent"><span class="fal fa-question-circle fa-lg"></span></a>
				</span>
		    </div><!-- /input-group -->
		  </div><!-- /.col-lg-6 -->
</div><!-- /.row -->

<table class='table table-striped display compact' id='Labellist'>
						<thead>
							<tr>
							<td></td>
							<td style="border-bottom: 1px solid lightgray"></td>
							</tr>
						</thead>
			<tbody>

			<?php	
			//second show added buttons
			$sql = mysqli_query($con,"SELECT *, id AS question_id FROM paybuttons WHERE " . $superuser . "_id = $superuser_id $string");
			$cnt = mysqli_num_rows($sql);
			if($cnt > 0) {
			while($row_button=mysqli_fetch_array($sql)){
			$button_id = $row_button['id'];
			$type = $row_button['type'];

			?>
			<tr style="border-bottom: 1px solid gray !important">
			<td><?php echo $row_button['enddate'] ?></td>
			
			<td style="border-bottom: 1px solid lightgray !important">
		        <div class="media" style="overflow: visible !important; list-style: none; padding-bottom: 5px;">
				  <div class="media-left" style="vertical-align: middle">
					  <?php if($row_button['archive'] == 1){ ?><span class="fa fa-lock fa-lg bg-icons bg-color-3 icon-circle"></span>	
					  <?php } else { if($row_button['enddate'] >= date('Y-m-d')){ ?><span class="fa fa-check fa-lg bg-icons bg-color-2 icon-circle"></span>	
				      <?php } else{ ?><span class="fa fa-history fa-lg bg-icons bg-color-8 icon-circle"></span>	
					  <?php
					       }
						   }
			           ?>
					</div>
				    <div class="media-body">
				
		        <h5><?php echo $row_button['title'] ?></h5>
				<font color="gray">
		        Bedrag: 
		        <?php
			        if($row_button['paydynamic'] == "0"){  
			        echo $row_button['amount'];
			        }
			        if($row_button['paydynamic'] == "1"){
				        echo "<i>[variabel]</i>";
				    }
				    if($row_button['paydynamic'] == "2"){
				        echo "<i>[via bestelformulier]</i>";
				    }
				    if($row_button['paydynamic'] == "3"){
				        echo "<i>[meerdere opties]</i>";
				    }
				   ?>
				<br>
				Status:
						<?php if($row_button['archive'] == 1){ 
							echo "GEARCHIVEERD";
				        }
					    else {
						if($row_button['enddate'] >= date('Y-m-d')){ 
							echo "ACTIEF tot " . date("d-m-Y", strtotime($row_button['enddate']));
						}
						else { 
							echo "VERLOPEN sinds " . date("d-m-Y", strtotime($row_button['enddate'] . ' + 1 day'));
						}
						}
					    ?>
		        </font>
		        <div class="showmoreinfodiv" id="<?php echo $row_button['id'] ?>" style="display: none">
				<ul class="fa-ul" style="color:gray; margin:0;">
		        <li>Geldig van <?php echo date('d-m-Y',strtotime($row_button['startdate'])) ?> tot <?php echo date('d-m-Y',strtotime($row_button['enddate'])) ?></li>
	            <li>Uw referentie: <font color="gray"><i><?php echo $row_button['invoice'] ?></i></font></li>
				<li><span class="fa fa-map-marker-alt fa-fw"></span> Van toepassing op: 
					<font color="gray"><i> 
						<?php 
						if ($row_button['ForAll'] == "1"){
				        echo "<u>alle subgroepen</u>";
				        }
				        if ($row_button['ForAll'] == "2"){
					        $list = explode(',',$row_button['ForSome']);
					        sort($list);
					        foreach($list AS $sub){
				        echo "<u>" . userValueSubcategory($sub, $superuser, "cat_name") . " | </u>";
				        	}
				        }
				        if ($row_button['ForAll'] == "0"){
					        echo "<u>" . userValueSubcategory($row_button['subcategory_id'], $superuser, "cat_name") . "</u>";		
					    }
					    if ($row_button['ForAll'] == "3"){
						    $list_kids = $row_button['ForKids'];
						    if($list_kids <> ""){
							    $list_kids = explode(",", $list_kids);
							    foreach($list_kids AS $kid_id){
								    echo "<u>" . userValueKid($kid_id, "name") . " " . userValueKid($kid_id, "surname") . " | </u>";
							    }
						    }
						    $list_adults = $row_button['ForAdults'];
						    if($list_adults <> ""){
							    $list_adults = explode(",", $list_adults);
							    foreach($list_adults AS $adult_id){
								    echo "<u>" . userValue($adult_id, "username") . " | </u>";
							    }
						    }
						    $list_manual = $row_button['ForManual'];
						    if($list_manual <> ""){
							    $list_manual = explode(",", $list_manual);
							    foreach($list_manual AS $manual_id){
								    echo "<u>" . userValueManual($manual_id, $superuser, $superuser_id, "name") . " " . userValueManual($manual_id, $superuser, $superuser_id, "surname") . " | </u>";
							    }
						    }
				        }			       
					?>
					</i></font>	
				</li>
				<li><span class="fa fa-users"></span> Van toepassing op: 
						<?php
						//count kids
						if ($row_button['ForAll'] == "1"){
				        echo CountMembersSuper($superuser_id, $superuser) . " leden";
				        }
				        if ($row_button['ForAll'] == "2"){
					    echo CountMembersSubcategory($superuser_id, $superuser,$row_button['ForSome']) . " leden";	
					    }
				        if ($row_button['ForAll'] == "0"){
					    echo CountMembersSubcategory($superuser_id, $superuser, $row_button['subcategory_id']) . " leden";					    
					    }

						?>
				<?php
		        $check_payments = mysqli_query($con, "SELECT * FROM paybuttons_results WHERE " . $superuser . "_id = $superuser_id AND button_id = $button_id AND status_id = 1");
		        if(is_allowedLevel("2")){ 
		        ?>
		        <li><span class="fa fa-euro-sign fa-fw"></span> <?php echo mysqli_num_rows($check_payments) ?> betalingen</li>
		        <?php }
			        else {
				        ?>
				 <li><span class="fa fa-euro-sign fa-fw"></span> <?php echo mysqli_num_rows($check_payments) . " betalingen" ?></li>       
			        <?php
			        }
			    ?>	
				</ul>
				</div>					
				    </div>
				    <div class="media-right" style="vertical-align: middle">
					    <div class="dropdown">
							  <button class="btn btn-default btn-xs dropdown-toggle pull-right" type="button" id="dropdownMenu<?php echo $row_button['id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="background-color: transparent; color: #bdbdbd">
							    <span class="fa fa-ellipsis-v fa-2x"></span>
							  </button>

							  <ul class="dropdown-menu pull-right" style="width: 280px;" aria-labelledby="dropdownMenu<?php echo $row_button['id'] ?>">
								  <li><a class="showmoreinfo" id="<?php echo $row_button['id'] ?>"><span class="fa fa-info-circle pull-right"></span> Informatie bestand</a></li>

								  <?php if($eventID <> "0"){ 
									  if($row_button['archive'] == 1){ ?>
									  <li><a role="button" disabled><span class="fa fa-calendar-plus pull-right" aria-hidden="true"></span>Een gearchiveerde shop-item kan je niet toevoegen aan een agendapunt</a></li>
									  <?php }
										elseif($row_button['enddate'] < date('Y-m-d')){
								  	   ?>
								  	  <li><a role="button" disabled><span class="fa fa-calendar-plus pull-right" aria-hidden="true"></span>Een verlopen shop-item kan je niet toevoegen aan een agendapunt</a></li>
								  <?php } 
									  elseif(mysqli_num_rows(mysqli_query($con, "SELECT * FROM " . $superuser . "s_paybuttons WHERE event_id = $eventID AND button_id = $button_id AND " . $superuser . "_id = $superuser_id")) > 0) { ?> 
									  <li><a role="button" disabled><span class="fa fa-calendar-plus pull-right" aria-hidden="true"></span>Deze shop-item is al toegevoegd aan het agendapunt van <?php echo dutchDateFromEvent($eventID) ?></a></li>
									  <?php 
										  
										 
									  
									  }
									  else { ?>
							<li><a class="AddPayButtonToEvent" id="<?php echo $button_id ?>" name="<?php echo $subcategory_id ?>"><span class="fa fa-calendar-plus pull-right" aria-hidden="true"></span>Voeg deze shop-item toe aan het agendapunt van <?php echo dutchDateFromEvent($eventID) ?></a></li>
								<?php }
									}
								?>
							<li><a href="<?php echo $full_path ?>/superuser_paybuttons_details.php?<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&type=<?php echo $row_button['type'] ?>&button_id=<?php echo $button_id ?>" target="_blank"><span class="fa fa-folder-open pull-right" aria-hidden="true"></span>Voorbeeld</a></li>	  
							<?php if($eventID == "0"){ 
								if(is_allowedLevel("2")){ ?>
							<?php $encrypted_button_id = base64_url_encode($row_button['id']);
							?>	
							<li><a href="superuser_export_paybuttons_results_excel.php?subcategory_id=<?php echo $subcategory_id ?>&button_id=<?php echo $encrypted_button_id ?>" target="_blank" class="<?php if($detect->isMobile()) { echo "mobile_disabled"; } ?>"><span class="far fa-file-excel pull-right" aria-hidden="true"></span>Exporteer betaalresultaten naar Excel</a></li> 
    
						<?php if($row_button['archive'] == 0){ ?>
						<li><a role="button" class="EditPayButton" id="<?php echo $button_id ?>"><span class="fa fa-pencil pull-right" aria-hidden="true"></span>Bewerk shop-item</a></li>
						<?php if($row_button['paydynamic'] == "2"){ ?>
						<li><a role="button" class="EditOrderForm" name="2" id="<?php echo $button_id ?>"><span class="fa fa-edit pull-right" aria-hidden="true"></span>Bewerk bestelformulier</a></li>
						<?php } ?>
						
						<?php if($row_button['paydynamic'] == "3"){ ?>
						<li><a role="button" class="EditOrderForm" name="3" id="<?php echo $button_id ?>"><span class="fa fa-edit pull-right" aria-hidden="true"></span>Bewerk prijzen</a></li>
						<?php } ?>

						<li><a href="#" role="button" data-toggle="modal" data-target="#myModalArchivePayButton<?php echo $button_id ?>"><span class="fa fa-archive pull-right"></span>Archiveer shop-item</a></li>
						
						<?php } else { ?>
						<li><a role="button" class="ActivatePayButton" id="<?php echo $button_id ?>"><span class="fa fa-box-open pull-right" aria-hidden="true"></span>Maak knop terug actief</a></li>
						<li><a href="#" role="button" data-toggle="modal" data-target="#myModalDeletePayButton<?php echo $button_id ?>"><span class="fas fa-trash-alt pull-right"></span>Verwijder shop-item</a></li>
							  <?php }
								  ?>
							<li><a role="button" class="MailNotPayed" kid_id_attr="<?php echo PayButtonNotPayed($superuser_id, $superuser, $button_id, "kids") ?>" adult_id_attr="<?php echo PayButtonNotPayed($superuser_id, $superuser, $button_id, "adults") ?>" manual_id_attr="<?php echo PayButtonNotPayed($superuser_id, $superuser, $button_id, "manual") ?>" id="<?php echo $button_id ?>"><span class="fa fa-envelope pull-right" aria-hidden="true"></span>Mail diegenen die nog niet betaald hebben</a></li>	  
								  <?php 
								  }
								  else {
									  ?>
								<li><a role="button"><span class="fa fa-exclamation-triangle pull-right" aria-hidden="true"></span>Bewerken van shop-items kan enkel door de Administrator van je vereniging gebeuren.</a></li>
							  <?php 
								  }
								  }
								?>
							  </ul>
							</div>

				    </div>
			</tr>

			<!-- Modal archive paybutton -->
						<div id="myModalArchivePayButton<?php echo $button_id ?>" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h5 class="modal-title">Archiveer shop-item "<i><?php echo $row_button['title'] ?></i>"</h5>
						      </div>
						      <div class="modal-body">
									<h4>Wat gebeurt er wanneer ik een knop archiveer?</h4>
									<br>		
									<ol>
										<li>Deze shop-item zal automatisch verdwijnen uit de <?php echo $appname ?>-accounts van je leden.</li>
										<li>Deze shop-item kan niet meer toegevoegd worden in een agendapunt of een mail</li>
										<li>Als deze shop-item al eerder in een agendapunt geplaatst werd, zal deze automatisch verdwijnen.</li>
										<li>De betaallabels in ledenbeheer (een label dat automatisch verschijnt als een lid betaald heeft) van <u>deze</u> shop-item zullen automatisch verdwijnen.</li>
									<li>Het overzicht van de betalingen van je leden zal <u>niet</u> verwijderd worden. Deze zullen nog altijd raadpleegbaar zijn via deze pagina.</li>
									</ol>				
							
						      </div>
						      <div class="modal-footer">
							    
							   	<button type="button" class="btn btn-primary btn-sm btn-block" data-dismiss="modal">Annuleer</button>
						        <button type="submit" id="<?php echo $button_id ?>" class="btn btn-danger btn-sm btn-block ArchivePayButton"><span class="fa fa-archive" aria-hidden="true"></span>&nbsp;Archiveer</button>
							    
						      </div>
						    </div>
						
						  </div>
						</div>

			<!-- Modal delete paybutton -->
						<div id="myModalDeletePayButton<?php echo $button_id ?>" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h5 class="modal-title">Verwijder shop-item "<i><?php echo $row_button['title'] ?></i>"</h5>
						      </div>
						      <div class="modal-body">
									<h4>Wat gebeurt er wanneer ik een gearchiveerde knop verwijder?</h4>
									<br>
									<ol>
										<li>Deze shop-item kan niet meer toegevoegd worden in een agendapunt of een mail</li>
										<li>Als deze shop-item al eerder in een agendapunt geplaatst werd, zal deze automatisch verdwijnen.</li>
										<li>De betaallabels in ledenbeheer (een label dat automatisch verschijnt als een lid betaald heeft) van <u>deze</u> shop-item zullen automatisch verdwijnen.</li>
									<li>Het overzicht van de betalingen van je leden zal <u>OOK</u> permanent verwijderd worden.</li>
									<li>Deze actie kan je niet ongedaan maken.</li>
									</ol>				
							
						      </div>
						      <div class="modal-footer">
							    
							   	<button type="button" class="btn btn-primary btn-sm btn-block" data-dismiss="modal">Annuleer</button>
						        <button type="submit" id="<?php echo $button_id ?>" class="btn btn-danger btn-sm btn-block DeletePayButton"><span class="fas fa-trash-alt" aria-hidden="true"></span>&nbsp;Verwijder</button>
							    
						      </div>
						    </div>
						
						  </div>
						</div>

				

		<?php
		}
		}
		?>		</tbody>
				</table>
		
<script src="js/link_changer.js" type="text/javascript"></script>
<script type="text/javascript">
	var superuser = "<?php echo $superuser ?>";
	var superuser_id = "<?php echo $superuser_id ?>";
	var subcategory_id = "<?php echo $subcategory_id ?>";
	var event_id = "<?php echo $eventID ?>";
	var urlsecurity = "?subcategory_id=<?php echo $subcategory_id ?>&get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>";
	var thisurl = "superuser_paybuttons_list.php";
	var targetdiv = "#Div<?php echo $subcategory_id ?>";
</script>
	<script type="text/javascript">
$(document).ready(function() {

$( ".AddNewPayButtonModal" ).hide();
$( ".BackButtonPayButtonModal" ).show();				
					
var files = $('#Labellist').DataTable( {
		"bFilter": true,
		"paging":   true,
		"pageLength": 50,
		"ordering": true,
		"bLengthChange": false,
		"info":     true,
		"bInfo" : false,
		"sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
		"order": [[ 0, "desc" ]],
		"columnDefs": [ { "orderable": false, "targets": 0 } ],
		"language": {
			"info":   "_START_ tot _END_ van de _TOTAL_ resultaten",
			"search": "",
			"infoFiltered": "",
			"infoEmpty": "",
			"searchPlaceholder": "",
			"lengthMenu": "<?php echo $m['show']; ?> _MENU_ <?php echo $m['records']; ?>",
			"emptyTable": '',
	"zeroRecords": 'Geen shop-items gevonden',
			"paginate": {
				"next": "<?php echo $m['next']; ?>",
				"previous": "<?php echo $m['previous']; ?>"
			}
		},
		"columnDefs": [
			{ orderable: false, targets: '_all' },
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false
            }
        ],
});
	
$('#customSearchBox').on('keyup', function(){
    files.search($(this).val()).draw() ;
});
																
$(".MoreInfoPayButtons").on('click', function() {
$('.DivMoreInfoPayButtons').toggle();
});
			
$(".showmoreinfo").on('click', function() {
	var id = this.id;
	$("#"+id+".showmoreinfodiv").show();
	$("#"+id+".showmoreinfo").hide();
});
});
</script>