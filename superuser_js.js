console.log("JS geladen");
$(".letterpic").letterpic();
//$(document).on('blur', 'input', function(){ window.scrollTo(0,NaN) }); /* from https://github.com/apache/cordova-ios/issues/417 */

let FullCalendarActions = {
  currentTime: null,
  isDblClick: function () {
    let prevTime =
      typeof FullCalendarActions.currentTime === null
        ? new Date().getTime() - 1000000
        : FullCalendarActions.currentTime;
    FullCalendarActions.currentTime = new Date().getTime();
    return FullCalendarActions.currentTime - prevTime < 500;
  },
};

$(document).off('click','.OpenSwipe').on("click",".OpenSwipe",function() {
	var file = this.name
	$( '.box-'+file+'-1').click();
}); 

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: superuser_subcategory ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(document).off('click','.SaldoInfo').on("click",".SaldoInfo",function(e) {
    e.preventDefault();
	var club_id = $(this).attr("club_id_attr");
	var type = $(this).attr("type_attr");
	$('.modalplusinfo').modal('show');
    $('.PlusInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	
	$.ajax({
	
    type: "POST",
    url: "modal_include.php?get_"+superuser+"_id="+superuser_id,
     data:{
      club_id: club_id,
      modal: type
    },
    
    success: function(data){
           $('.PlusInfoDiv').html(data);     
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (Modal UserInfo Saldo). Gelieve opnieuw te proberen.");
    }, 
    
	});	
	
});

$(document).off('click','.OpenSearchField').on("click",".OpenSearchField",function() {
		  $('.SearchBox').slideDown("fast");
		  $('.OpenSearchField').hide();
		  $('.CloseSearchField').show();
		  $('.SearchBox').load("superuser_searchbox.php"+urlsecurity);	
});

$(document).off('click','.CloseSearchField').on("click",".CloseSearchField",function() {		
		  $('.SearchBox').slideUp("fast");
		  $('.OpenSearchField').show();
		  $('.CloseSearchField').hide();
		  $('.result').html();	
});

$(document).off('click','.DivCalendarLink').on("click",".DivCalendarLink",function() {
		var OpenUp = this.id;
		var subcategory_id =  $(this).attr('subcategory_attr');
		var strDate = $.datepicker.formatDate('yy-mm-dd', new Date());
		var attr = $(this).attr('name');
		if (typeof attr !== 'undefined' && attr !== false) {
			startdate = this.name;
		}
		else {
			startdate = strDate;
		}
		var trigger = $(this).attr("trigger_attr");
		var triggerevent = $(this).attr("triggerevent_attr");
		
		$('a.red').removeClass('red')
		$('.DivCalendarLink').addClass('red');
		$('#Div'+subcategory_id).load("superuser_calendar.php"+urlsecurity+"&OpenUp="+OpenUp+"&startdate="+startdate+"&trigger="+trigger+"&triggerevent="+triggerevent);
		console.log(subcategory_id);
});
				
$(document).off('click','.DivMembersLink').on("click",".DivMembersLink",function() {	
		var subcategory_id =  $(this).attr('subcategory_attr');
		$('a.red').removeClass('red')
		$('.DivMembersLink').addClass('red');
		$('.DivMembersLink').find('span.badge').remove();
		$('#Div'+subcategory_id).load("superuser_members_add.php"+urlsecurity);		 
});
		
$(document).off('click','.OpenMembers').on("click",".OpenMembers",function() {	
		var board = this.id;	
		var subcategory_id =  $(this).attr('subcategory_attr');		
		$('a.red').removeClass('red');
		$('.btn.OpenMembers').addClass('red');
		$('.btn.OpenMembersBoard').addClass('red');
		if(board == 0){
		$('#Div'+subcategory_id).load("superuser_members_list.php"+urlsecurity);
		}
		if(board == 1){
		$('#Div'+subcategory_id).html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
		$('#Div'+subcategory_id).load("superuser_members_list_all.php"+urlsecurity);	
		}
});

//button open chatbox
$(document).off('click','.OpenChatBox').on("click",".OpenChatBox",function() {	
	var board = this.id;	
	var subcategory_id =  $(this).attr('subcategory_attr');		
	$('a.red').removeClass('red');
	$('.btn.OpenChatBox').addClass('red');
	$('.btn.OpenChatBoxBoard').addClass('red');
	if(board == 0){
	$('#Div'+subcategory_id).load("superuser_chatbox.php"+urlsecurity);
	}
	if(board == 1){
	$('#Div'+subcategory_id).html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	$('#Div'+subcategory_id).load("superuser_chatbox_all.php"+urlsecurity);	
	}
});

		
$(document).off('click','.OpenFiles').on("click",".OpenFiles",function() {	
	var subcategory_id =  $(this).attr('subcategory_attr');				
		$('a.red').removeClass('red');
		$('.OpenFilesDropDown').addClass('red');
		$('#Div'+subcategory_id).load("superuser_files_list.php"+urlsecurity);		
});

$(document).off('click','.SyncCal').on("click",".SyncCal",function() {	
		$('#Div'+subcategory_id).load("superuser_subcategory_sync.php"+urlsecurity);			
});

$(document).off('click','.OpenBoardFiles').on("click",".OpenBoardFiles",function() {	
		var filter = this.id;
		var subcategory_id =  $(this).attr('subcategory_attr');
		$('a.red').removeClass('red');	
		$('.OtherFunctions').addClass('red');			
		$('#Div'+subcategory_id).load("superuser_files_list.php"+urlsecurity+"&filter="+filter);
});
		
$(document).off('click','.OpenContacts').on("click",".OpenContacts",function() {
		var subcategory_id =  $(this).attr('subcategory_attr');
		$('a.red').removeClass('red');	
		$('.OtherFunctions').addClass('red');
		$('#Div'+subcategory_id).load("superuser_contacts_list.php"+urlsecurity);			
});

$(document).off('click','.OpenContactsAll').on("click",".OpenContactsAll",function() {	
		  var subcategory_id =  $(this).attr('subcategory_attr');
		  $('a.red').removeClass('red');
		  $('.OtherFunctions').addClass('red');
		  $('#Div'+subcategory_id).load("superuser_contacts_list_all.php"+urlsecurity);		
});
		
$(document).off('click','.EditSubcategory').on("click",".EditSubcategory",function() {			
		var subcategory_id =  $(this).attr('subcategory_attr');
		$('a.red').removeClass('red');
		$('.OtherFunctions').addClass('red');
		$('#Div'+subcategory_id).load("superuser_subcategory_edit.php"+urlsecurity);	
});

$(document).off('click','.PremiumRead').on("click",".PremiumRead",function() {
		 var order_id = this.id;
		 $.ajax({
			type: "POST",
		    url: "superuser_actions.php?action=PremiumRead",
		    data:{
	          order_id: order_id
	        },
		    success: function(data){			              
					//console.log("Premium message read: order_id: " + order_id);
				    $('#LoadSubgroup').load("superuser_subcategory.php"+urlsecurity);
					
		    },
		    error: function(){
		      alert("Er is een probleem opgetreden met de app (functie: Premium message read). Gelieve opnieuw te proberen.");
		    }, 
		    
		  });
});
			
$(document).off('click','.SurveyDetails').on("click",".SurveyDetails",function() {							
		var subcategory_id =  $(this).attr('subcategory_attr');
		$('a.red').removeClass('red');
		$('.OpenFilesDropDown').addClass('red');
		$('#Div'+subcategory_id).load("superuser_survey_list.php"+urlsecurity);
});
		
$(document).off('click','.PayButtonMoreInfo').on("click",".PayButtonMoreInfo",function() {
		var subcategory_id =  $(this).attr('subcategory_attr');
		$('a.red').removeClass('red');
		$('.OpenFilesDropDown').addClass('red');
		$('#Div'+subcategory_id).load("superuser_paybuttons_list.php"+urlsecurity);
});
		
$(document).off('click','.OpenStats').on("click",".OpenStats",function() {	
		var subcategory_id =  $(this).attr('subcategory_attr');
		$('a.red').removeClass('red');
		$('.OpenFilesDropDown').addClass('red');		
		$('#Div'+subcategory_id).load("superuser_stats_RSVP.php"+urlsecurity);
});

$(document).off('click','.OpenStatsMail').on("click",".OpenStatsMail",function() {		
		var subcategory_id =  $(this).attr('subcategory_attr');
		$('a.red').removeClass('red');
		$('.OpenFilesDropDown').addClass('red');		
		$('#Div'+subcategory_id).load("superuser_stats_mails.php"+urlsecurity);
});

$(document).off('click','.OpenPublic').on("click",".OpenPublic",function() {
		window.location.href = "login/profile.php?goto=BtnPublicCalendar";
});

$(document).off('click','.MailListButtons').on("click",".MailListButtons",function() {		
		var subcategory_id =  $(this).attr('subcategory_attr');
		$('a.red').removeClass('red');
		$('.OpenFilesDropDown').addClass('red');
			var board = this.id;
			var archive = this.name;
		$('#Div'+subcategory_id).load("superuser_mails_list.php"+urlsecurity+"&archive="+archive+"&all="+board);
});

$(document).off('click','.SortSubgroups').on("click",".SortSubgroups",function() {		
		$('#LoadSubgroup').load("superuser_subcategory_sort.php"+urlsecurity);
});

$(document).off('click','.OpenSwitch').on("click",".OpenSwitch",function() {				
		$('#LoadSubgroup').load("switch.php");
});

$(document).off('click','.DeleteSubcategory').on("click",".DeleteSubcategory",function(e) {
	    e.preventDefault();
	    var subcategory_id = this.id;
	    if($('.ImPositive').not(':checked').length){
		NotifError("impositive");
		return false;
		} else {
		var submiturl = "superuser_actions.php"+urlsecurity+"&action=delete"; // the script where you handle the form input.
	
		 $.ajax({
           type: "POST",
           url: submiturl,
           data: {
	          id: subcategory_id 
           },
           success: function(data)
           {
	          notif({
				  msg: "De subgroep is verwijderd uit de database.",
				  position: "center",
				  type: "info",
				  time: 3000
				});
	          window.location.href = "superuser_index.php";
              $('#DeleteSubcategory'+subcategory_id).modal('hide');
			  $('body').removeClass('modal-open');
			  $('.modal-backdrop').remove();   
           },
         });
	    }	    
});

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: update blocks actions  ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(document).off('click','.EditBlock').on("click",".EditBlock",function() {
	var title = this.id;
	//console.log("superuser_blocks_new.php"+urlsecurity+"&reuse=1&title="+title+"&target_subcategory_id="+target_subcategory_id);
	$('#Div'+subcategory_id).load("superuser_blocks_new_edit.php"+urlsecurity+"&edit=1&title="+title);	
});

$(document).off('click','.NewBlock').on("click",".NewBlock",function() {
	$('#Div'+subcategory_id).load("superuser_blocks_new_edit.php"+urlsecurity);
});

$(document).off('click','.ReuseBlock').on("click",".ReuseBlock",function() {
	var title = this.id;
	var target_subcategory_id = $(this).attr("subcategory_attr");
	$('#Div'+subcategory_id).load("superuser_blocks_new_edit.php"+urlsecurity+"&reuse=1&title="+title+"&target_subcategory_id="+target_subcategory_id);
});

//button delete block
$(document).off('click','.DeleteOwnBlock').on("click",".DeleteOwnBlock",function(e) {
	e.preventDefault();
	var block_id = this.id;					
//console.log("Delete block: block_id:" + block_id);					
	 $.ajax({
	    type: "POST",
	    url: "superuser_actions.php"+urlsecurity+"&action=DeleteBlock",
	    data:{
          block_id: block_id
        },
	    success: function(data){
		    	  
		             $('#Div'+subcategory_id).load("superuser_blocks_list.php"+urlsecurity);
		              $('#myModalRemoveBlock'+block_id).modal('hide');
					  $('body').removeClass('modal-open');
					  $('.modal-backdrop').remove();
					  
				notif({
				  msg: "Je hebt het agendablokje succesvol verwijderd.",
				  position: "center",
				  type: "info",
				  width: 300,
				  time: 2500
				});
		               

	    },
	    error: function(){
	      alert("Er is een probleem opgetreden met de app (functie: Verwijder agendablok). Gelieve opnieuw te proberen.");
	    }, 
	    
	  });
});

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: superuser_members_add.php ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//open modal user
$(document).off('click', '.OpenModalUser').on("click", ".OpenModalUser", function (e) {
	var kid_id = $(this).attr("kid_id_attr");
	var parent_id = $(this).attr("parent_id_attr");
	var manual_id = $(this).attr("manual_id_attr");
	var subcategory_id = this.name;
	var chat_id = $(this).attr("chat_id_attr");
	var subcategory_suggestion = this.id;
	var type = $(this).attr("type_attr");
	var extra_var = $(this).attr("extra_var");
	$('.userinfo').modal('show');
	$('.UserInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	//console.log('Open Modal: type: ' + type + ' & kid_id: ' + kid_id + '  & parent_id: ' + parent_id + ' & manual_id: ' + manual_id + ' & subcategory_id: ' + subcategory_id + ' & extra_var: ' + extra_var);

	$.ajax({

		type: "POST",
		url: "modal_include.php"+urlsecurity,
		data: {
			kid_id: kid_id,
			parent_id: parent_id,
			manual_id: manual_id,
			subcategory_suggestion: subcategory_suggestion,
			old_subcategory: subcategory_id,
			chat_id: chat_id,
			superuser: superuser,
			superuser_id: superuser_id,
			extra_var: extra_var,
			modal: type
		},

		success: function (data) {
			$('.UserInfoDiv').html(data);
		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (Modal UserInfo). Gelieve opnieuw te proberen.");
		},

	});
});


//remove user from subgroup
$(document).off('click', '.SubmitRemove').on("click", ".SubmitRemove", function (e) {
	e.preventDefault();
	var type = this.id;
	var kid_id = $(this).attr("kid_id_attr");
	var parent_id = $(this).attr("parent_id_attr");
	var manual_id = $(this).attr("manual_id_attr");
	var subcategory_id = this.name;
	var submiturl = "superuser_actions.php" + urlsecurity + "&action=RemoveUser";
	//console.log('Remove user from one subgroup: type: ' + type + ' & kid_id: ' + kid_id + ' & parent_id: ' + parent_id + ' & manual_id: ' + manual_id + '& subcategory_id: ' + subcategory_id);

	$.ajax({
		type: "POST",
		url: submiturl,
		data: {
			type: type,
			kid_id: kid_id,
			parent_id: parent_id,
			manual_id: manual_id,
			subcategory_id: subcategory_id
		},
		success: function () {
			var message;
			if (type == "RemoveFromSubcategoryMono") {
				message = "Je hebt deze gebruiker uit de subgroep verwijderd. Je kan hem/haar nu terug indelen in een andere subgroep";
			}
			if (type == "RemoveFromOneSubcategoryMulti") {
				message = "Je hebt deze gebruiker uit 1 subgroep verwijderd.";
			}
			if (type == "RemoveTotal") {
				message = "Je hebt deze gebruiker volledig verwijderd.";
			}

			notif({
				msg: message,
				position: "center",
				type: "info",
				multiline: true,
				width: 300,
				time: 2500
			});
			$(targetdiv).load(thisurl + urlsecurity);
			$('.userinfo').modal('hide');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
		}
	});
});

//report member
$(document).off('click', '.SubmitReportUser').on("click", ".SubmitReportUser", function (e) {
	e.preventDefault();
	var type = $(this).attr("type_id_attr");
	var kid_id = $(this).attr("kid_id_attr");
	var parent_id = $(this).attr("parent_id_attr");
	var subcategory_id = this.name;
	var submiturl;
	if (type == "report") {
		submiturl = "superuser_actions.php" + urlsecurity + "&action=ReportUser";
	}
	if (type == "report_undo") {
		submiturl = "superuser_actions.php" + urlsecurity + "&action=ReportUserUndo";
	}
	//console.log('Remove user: type: ' + type + ' & kid_id: ' + kid_id + ' & parent_id: ' + parent_id + '& subcategory_id: ' + subcategory_id);

	$.ajax({
		type: "POST",
		url: submiturl,
		data: {
			type: type,
			kid_id: kid_id,
			parent_id: parent_id,
			subcategory_id: subcategory_id
		},
		success: function () {

			var message;
			if (type == "report") {
				message = "Je hebt deze gebruiker gerapporteerd. Je ontvangt automatisch een bevestigingsmail met meer details.";
			}
			if (type == "report_undo") {
				message = "De rapportage is ongedaan gemaakt. Je ontvangt automatisch een bevestigingsmail.";
			}

			notif({
				msg: message,
				position: "center",
				type: "info",
				multiline: true,
				width: 300,
				time: 2500
			});

			$(targetdiv).load(thisurl + urlsecurity);
			//console.log(targetdiv + " - " + thisurl + urlsecurity);

			if (type == "report") {
				$('.userinfo').modal('hide');
				$('body').removeClass('modal-open');
				$('.modal-backdrop').remove();
			}
		}
	});
});

//put suggestion in correct subcategory
$(document).off('click', '.AddMember').on("click", ".AddMember", function () {
	var new_subcategory_id = this.id;
	var subcategory_id = this.name;
	var kid_id = $(this).attr("kid_id_attr");
	var parent_id = $(this).attr("parent_id_attr");
	$("#loadingdiv" + parent_id + "_" + kid_id).css('display', 'inline-block');
	//console.log('Add member to selected subgroup: kid_id: ' + kid_id + ' & parent_id: ' + parent_id + ' & new_subcategory: ' + new_subcategory_id);
	$.ajax({
		type: "POST",
		url: "superuser_actions.php" + urlsecurity + "&action=MoveCopyMembers",
		data: {
			new_subcategory_id: new_subcategory_id,
			old_subcategory_id: "0",
			id_list_kids: kid_id,
			id_list_parents: parent_id,
			type: "movemembers",
			sendmail: "1"
		},
		success: function () {
			notif({
				msg: "Je hebt deze gebruiker toegevoegd aan de geselecteerde subgroep.",
				position: "center",
				type: "success",
				width: 300,
				time: 2500,
				multiline: true
			});

			$("#Div" + subcategory_id).load(thisurl + urlsecurity);
		}
	});
});


//put suggestion in correct subcategory
$(document).off('click', '.AddMemberToMultipleSubs').on("click", ".AddMemberToMultipleSubs", function () {
	var new_subcategory_id = $(this).attr("subs_id_attr");
	var subcategory_id = this.name;
	var kid_id = $(this).attr("kid_id_attr");
	var parent_id = $(this).attr("parent_id_attr");
	//console.log('Add member to multiple selected subgroups: kid_id: ' + kid_id + ' & parent_id: ' + parent_id + ' & new_subcategories: ' + new_subcategory_id);

	if ((new_subcategory_id === '') || (new_subcategory_id === '0')) {
		NotifError("addmembers");
		return false;
	}

	$.ajax({
		type: "POST",
		url: "superuser_actions.php" + urlsecurity + "&action=MoveCopyMembers",
		data: {
			new_subcategory_id: new_subcategory_id,
			old_subcategory_id: "0",
			id_list_kids: kid_id,
			id_list_parents: parent_id,
			type: "newmembers",
			sendmail: "1"
		},
		success: function () {
			notif({
				msg: "Je hebt deze gebruiker toegevoegd aan de geselecteerde subgroep(en). Er werd automatisch een bevestigingsmail gestuurd naar (de ouders van) deze gebruiker.",
				position: "center",
				type: "success",
				width: 300,
				time: 2500,
				multiline: true
			});

			$('.userinfo').modal('hide');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
			$("#Div" + subcategory_id).load(thisurl + urlsecurity);
		}
	});
});

//put suggestion in correct subcategory
$(document).off('click', '.AddNewMemberAsContact').on("click", ".AddNewMemberAsContact", function () {
	var subcategory_id = $(this).attr("subs_id_attr");
	var kid_id = $(this).attr("kid_id_attr");
	var parent_id = $(this).attr("parent_id_attr");
	var manual_id = $(this).attr("manual_id_attr");
	//console.log('Add member as contact to multiple selected subgroups: kid_id: ' + kid_id + ' & parent_id: ' + parent_id + ' & manual_id: ' + manual_id + ' & new_subcategories: ' + subcategory_id);

	if (subcategory_id === '') {
		NotifError("addmembers");
		return false;
	}

	$.ajax({
		type: "POST",
		url: "superuser_actions.php" + urlsecurity + "&action=AddContactSuper",
		data: {
			subcategory_id: subcategory_id,
			kid_id: kid_id,
			parent_id: parent_id,
			manual_id: manual_id
		},
		success: function () {
			notif({
				msg: "Je hebt deze gebruiker als contactpersoon toegevoegd aan de geselecteerde subgroep(en). Je kan deze gebruiker nu ook nog indelen als lid van een subgroep.",
				position: "center",
				type: "success",
				width: 300,
				time: 2500,
				multiline: true
			});

			$('.userinfo').modal('hide');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
			$("#Div" + subcategory_id).load(thisurl + urlsecurity);
		}
	});
});

//put suggestion in correct subcategory
$(document).off('click', '.CloseModalNewMemberAsContact').on("click", ".CloseModalNewMemberAsContact", function () {
	$("#Div" + subcategory_id).load(thisurl + urlsecurity);
});

//remove contact person from popup modal
$(document).off('click', '.SubmitRemoveContact').on("click", ".SubmitRemoveContact", function () {
	var id = this.id;
	var subcategory_id = this.name;
	var kid_id = $(this).attr("kid_id_attr");
	var parent_id = $(this).attr("parent_id_attr");
	var manual_id = $(this).attr("manual_id_attr");
	var type = $(this).attr("type_attr");

	//console.log("Remove contact person: id : " + id);

	$.ajax({
		type: "POST",
		url: "superuser_actions.php" + urlsecurity + "&action=remove_contact",
		data: {
			id: id,
			subcategory_id: subcategory_id
		},
		success: function () {
			notif({
				msg: "Je hebt deze gebruiker verwijderd als contactpersoon van de deze subgroep.",
				position: "center",
				type: "info",
				width: 300,
				time: 2500,
				multiline: true
			});

			$.ajax({
				type: "POST",
				url: "modal_include.php" + urlsecurity,
				data: {
					kid_id: kid_id,
					parent_id: parent_id,
					manual_id: manual_id,
					modal: type
				},
				success: function (data) {
					$('.UserInfoDiv').html(data);
				},
				error: function () {
					alert("Er is een probleem opgetreden met de app (Modal UserInfo). Gelieve opnieuw te proberen.");
				},

			});
		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (verwijder contactpersoon). Gelieve opnieuw te proberen.");
		},
	});
});

$(document).off('click', '.NewMember').on("click", ".NewMember", function () {
	$('#Div' + subcategory_id).load("superuser_members_new.php" + urlsecurity + "&board=" + allsubs);
	//console.log("superuser_members_new.php" + urlsecurity + "&board=" + allsubs);
});

$(document).off('click', '.MoreMembers').on("click", ".MoreMembers", function () {
	$(".moremembersdiv").show();
	$(".automaticmembersdiv").hide();
	$(".MoreMembers").hide();
});

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: superuser_calendar.php ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(document).off('click', '.OpenModalExportButton').on("click", ".OpenModalExportButton", function () {
		$('.calendarinfo').modal('show');
	    $('.CalendarInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	    $('.CalendarInfoDiv').load('superuser_export_calendar_pdf.php?modal=1&'+superuser+'_id='+secret_id+'&subcategory_id='+secret_sub)
});

$(document).off('click', '.OpenModalImportButton').on("click", ".OpenModalImportButton", function () {
		$('.calendarinfo').modal('show');
	    $('.CalendarInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	    $('.CalendarInfoDiv').load('superuser_import_ics.php'+urlsecurity);
});

$(document).off('click', '.SyncSport').on("click", ".SyncSport", function () {		
		$('#Div'+subcategory_id).load("superuser_external_sync_include.php"+urlsecurity);
});

$(document).off('click', '.UpdateBlocks').on("click", ".UpdateBlocks", function () {
	    $('#Div'+subcategory_id).load("superuser_blocks_list.php"+urlsecurity);
});

//delete event
$(document).off('click', '.SubmitMoveCopyEvent').on("click", ".SubmitMoveCopyEvent", function () {	
		var event_id = this.id;
		var subcategory_id = this.name;
		var type = $(this).attr("type_attr");
		var start = $(this).attr("new_start_attr");
		var end = $(this).attr("new_end_attr");
		
		if(type === "cancel"){
		calendar.refetchEvents();
		$('.userinfo').modal('hide');
		$('body').removeClass('modal-open');
		$('.modal-backdrop').remove();	
		}
		else {
		$.ajax({
			url: 'superuser_actions.php',
			data: 'subcategory_id='+subcategory_id+'&action=movecopyevent&type='+type+'&get_'+superuser+'_id='+superuser_id+'&start='+start+'&end='+end+'&eventid='+event_id,
			type: 'POST',
			dataType: 'json',
			success: function(response){
				//console.log(response.document_id);    				
				calendar.refetchEvents();
				$('.userinfo').modal('hide');
				$('body').removeClass('modal-open');
				$('.modal-backdrop').remove();
				$('#show_dates'+subcategory_id).load("superuser_calendar_include.php?subcategory_id="+subcategory_id+"&get_"+superuser+"_id="+superuser_id+"&startdate=" + start);
		},
			error: function(e){
				calendar.refetchEvents();
			}
		});
		}
});	

//delete event
$(document).off('click', '.DeleteButton').on("click", ".DeleteButton", function () {	
		var event_id = this.id;
		var subcategory_id = this.name;
		//console.log('Delete event: event_id: ' + event_id + ' & subcategory_id = ' + subcategory_id);
	
		$.ajax({
    		url: 'superuser_actions.php',
    		data: 'subcategory_id='+subcategory_id+'&get_'+superuser+'_id='+superuser_id+'&action=remove&eventid='+event_id,
    		type: 'POST',
    		dataType: 'json',
    		success: function(response){
    			//console.log(response);
    			if(response.status == 'success'){
	    				notif({
				  msg: "Je hebt de activiteit van " + startdate_format + " verwijderd",
				  position: "center",
				  type: "info",
				  time: 2500,
				  multiline: true
				});
    				calendar.refetchEvents();
					$('#show_dates'+subcategory_id).load("superuser_calendar_include.php"+urlsecurity+"&startdate="+startdate);
					$('#DeleteDate'+event_id).modal('hide');
					$('body').removeClass('modal-open');
					$('.modal-backdrop').remove();
				
				}
    		},
    		error: function(e){	
    			alert("Er is een probleem opgetreden met de app (verwijder activiteit). Gelieve opnieuw te proberen.");	
    		}
		});
});	
				
//delete multiple events at once
$(document).off('click', '.DeleteButtonAll').on("click", ".DeleteButtonAll", function () {
		var repeat_id = this.id;
		var subcategory_id = this.name;
		//console.log('Delete event multiple: repeat_id: ' + repeat_id + ' & subcategory_id = ' + subcategory_id);
		$('.DeleteButtonAll').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Verwijderen...');
	
		$.ajax({
    		url: 'superuser_actions.php',
    		data: 'subcategory_id='+subcategory_id+'&action=removeAll&get_'+superuser+'_id='+superuser_id+'&repeat_id='+repeat_id,
    		type: 'POST',
    		dataType: 'json',
    		success: function(response){
    			//console.log(response);
    			if(response.status == 'success'){
	    				notif({
				  msg: "Je hebt de geselecteerde activiteiten verwijderd",
				  position: "center",
				  type: "info",
				  time: 2500,
				  multiline: true
				});
    				calendar.refetchEvents();
					$('#show_dates'+subcategory_id).load("superuser_calendar_include.php"+urlsecurity+"&startdate="+startdate);
					$('#DeleteDate'+repeat_id).modal('hide');
					$('body').removeClass('modal-open');
					$('.modal-backdrop').remove();
				}
    		},
    		error: function(e){	
    			alert("Er is een probleem opgetreden met de app (verwijder multipele activiteiten). Gelieve opnieuw te proberen.");
    		}
		});
});	
				
				
//cancel event
$(document).off('click', '.CancelActivityButton').on("click", ".CancelActivityButton", function () {	
		$('.CancelActivityButton').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
		var event_id = this.id;
	    var repeat_id = $(this).attr("repeat_id_attr"); //is O if no repeated event
		var subcategory_id = this.name;
		var all = $(this).attr("all_attr"); //cancel one or all repeated events at once
		
		var alertcancel = 0;
		
		if($('.AlertCancel'+event_id).not(':checked').length){
		alertcancel = "0";
		}else{
		alertcancel = "1";
		};
		
		//console.log(event_id + " - " + repeat_id + " - " + subcategory_id + " - " + all + " - alert: " + alertcancel);
		

		$.ajax({
    		url: 'superuser_actions.php'+urlsecurity,
    		type: 'POST',
    		data:{
	          action: "CancelActivity",
	          event_id: event_id,
	          repeat_id: repeat_id,
	          all: all,
	          cancelalert: alertcancel,
	          subcategory_id: subcategory_id
	        },
    		success: function(response){

	    		notif({
				  msg: "Je hebt de status van de activiteit van " + startdate_format + " aangepast",
				  position: "center",
				  type: "info",
				  time: 2500,
				  multiline: true
				});

    				calendar.refetchEvents();
					$('#show_dates'+subcategory_id).load("superuser_calendar_include.php"+urlsecurity+"&startdate="+startdate);
					$('#CancelDate'+event_id).modal('hide');
					$('body').removeClass('modal-open');
					$('.modal-backdrop').remove();

    		},
    		error: function(e){	
    			alert("Er is een probleem opgetreden met de app (annuleer activiteit). Gelieve opnieuw te proberen.");
    		}
		});
		

});
					


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: superuser_calendar_include.php ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$(document).off('click', '.UpdateEventButton').on("click", ".UpdateEventButton", function () {
		var event_id = this.id;
		$('.calendarinfo').modal({
	    			show: true,
	    			backdrop: 'static', //disable click outside modal
					keyboard: false //disable escape button
    	});
	    $('.CalendarInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	    $('.CalendarInfoDiv').load('superuser_calendar_update_event.php'+urlsecurity+'&event_id='+event_id);
	    //console.log('superuser_calendar_update_event.php'+urlsecurity+'&event_id='+event_id);
});

//open dropdown in edit event
$(document).on("change", ".ForAll", function () {
   var val = this.value;
   if(val == '2') {
        $('#SomeSubcategories').show(); 
        $('#SomeStudents').hide(); 
   }
   if(val == '3') {
        $('#SomeStudents').show(); 
        $('#SomeSubcategories').hide();
   }
   if((val == '1') || (val == '0')) {
        $('#SomeSubcategories').hide(); 
        $('#SomeStudents').hide();    
   }
});

$(document).on("change", ".ChangeTimeFrame", function () {
   var val = this.value;
   if(val == '0') {
        $('#ShowTimeFrame').show(); 
   }
   if((val == '2') || (val == '1')) {
        $('#ShowTimeFrame').hide();  
   }
});

$(document).off('click', '.share-button').on("click", ".share-button", function () {
	var title = $(this).attr("title_attr");
	var url = $(this).attr("url_attr");
	var text = $(this).attr("text_attr");
	if (navigator.share) {
		navigator.share({
				title: title,
				url: url,
				text: text
			});
	} else {
		console.log("sharing is currently not supported on Android Webview");
		//alert("Sorry, delen is met jouw toestel niet mogelijk...: " + url + " - " + title + " - " + text);
	}
});

//open google maps when clicked on location event
$(document).off('click', '.location-google').on("click", ".location-google", function () {	
	var event_id = this.name;
	var iframe = $("#googleframe"+event_id);
	iframe.attr("src", iframe.data("src")); 
	//console.log('Open Google Maps: event_id: ' + event_id);
});

//open modal attachments
$(document).off('click', '.OpenAttachments').on("click", ".OpenAttachments", function () {
		var event_id = this.id;
		var subcategory_id = this.name;
		var chatbox = $(this).attr("chatbox_attr");
		
		$('.calendarinfo').modal('show');
	    $('.CalendarInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	    $('.CalendarInfoDiv').load("superuser_attachments.php"+urlsecurity+"&event_id="+event_id+"&chatbox="+chatbox);		
		console.log('Open Attachments: event_id: ' + event_id  + ' & subcategory_id: ' + subcategory_id + ' & chatbox: ' + chatbox);
}); 

//close modal attachment
$(document).off('click', '.CloseAttachments').on("click", ".CloseAttachments", function () {	
		var event_id = this.id;
		var subcategory_id = this.name;
		$('.CloseAttachments').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
		//console.log('Close modal attachments: event_id: ' + event_id + ' & subcategory_id = ' + subcategory_id);
		calendar.refetchEvents();
    	$('#show_dates'+subcategory_id).load("superuser_calendar_include.php"+urlsecurity+"&startdate="+startdate);
    	$('.calendarinfo').modal('hide');
});

//open modal take absences - settings absences
$(document).off('click', '.OpenAbsences').on("click", ".OpenAbsences", function () {
    var event_id = this.id;
    var gotosection = this.name;
    $('.calendarinfo').modal('show');
	$('.CalendarInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	$('.CalendarInfoDiv').load("superuser_absences.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate+"&goto="+gotosection);
	//console.log('Open Absences: event_id: ' + event_id + ' & subcategory_id: ' + subcategory_id);
});


//open modal export	
$(document).off('click', '.OpenExportFromCalendar').on("click", ".OpenExportFromCalendar", function () {
	var event_id = this.id;
	var opennewmodal = this.name;
	if(opennewmodal == 1){
		$('.calendarinfo').modal('show');
		$('.CalendarInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	}
		$('.CalendarInfoDiv').load("superuser_export.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate);
		//console.log('Open Export: event_id: ' + event_id + ' & subcategory_id: ' + subcategory_id);
}); 

//open modal survey
$(document).off('click', '.OpenSurvey').on("click", ".OpenSurvey", function () {	
    var event_id = this.id;
    var subcategory_id = this.name;
   
    $('.calendarinfo').modal({
		show: true,
		//backdrop: 'static', //disable click outside modal
		//keyboard: false //disable escape button
   	});
	$('.CalendarInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	$('.CalendarInfoDiv').load("superuser_survey.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate);
	//console.log('Open Survey: event_id: ' + event_id + ' & subcategory_id: ' + subcategory_id);
});

//close modal survey
$(document).off('click', '.CloseModalSurvey').on("click", ".CloseModalSurvey", function () {
	var event_id = this.id;
	var subcategory_id = this.name;
	$('.CloseModalSurvey').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	calendar.refetchEvents();
	$('#show_dates'+subcategory_id).load("superuser_calendar_include.php"+urlsecurity+"&startdate="+startdate);
	$('.calendarinfo').modal('hide');
});

//open modal paybuttons
$(document).off('click', '.OpenPayButtons').on("click", ".OpenPayButtons", function () {
	var event_id = this.id;
	var subcategory_id = this.name;
	$('.calendarinfo').modal('show');
	$('.CalendarInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	$('.CalendarInfoDiv').load("superuser_paybuttons.php"+urlsecurity+"&event_id="+event_id);
	//console.log('Open Modal PayButtons: event_id: ' + event_id  + ' & subcategory_id: ' + subcategory_id);
}); 
				
//close modal paybuttons
$(document).off('click', '.ClosePayButtons').on("click", ".ClosePayButtons", function () {
	var event_id = this.id;
	var subcategory_id = this.name;
	$('.ClosePayButtons').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	//console.log('Close Modal PayButtons: event_id: ' + event_id + ' & subcategory_id = ' + subcategory_id);
	calendar.refetchEvents();
	$('#show_dates'+subcategory_id).load("superuser_calendar_include.php"+urlsecurity+"&startdate="+startdate);
	$('.calendarinfo').modal('hide');
});


$(document).off('click', '.ShowMoreLink').on("click", ".ShowMoreLink", function () {			    
	var eventID = this.id;
	$('#ShowAll'+eventID).show();
	$('#ShowMore'+eventID).hide();	
});

$(document).off('click', '.ShowMoreLinkNoGo').on("click", ".ShowMoreLinkNoGo", function () {				
	var eventID = this.id;
	$('#ShowAllNoGo'+eventID).show();
	$('#ShowMoreNoGo'+eventID).hide();	
});

$(document).off('click', '.ShowMoreLinkRead').on("click", ".ShowMoreLinkRead", function () {				
	var eventID = this.id;
	var mail_id = this.name;
	$('#ShowAllRead'+eventID+'_'+mail_id).show();
	$('#ShowRead'+eventID+'_'+mail_id).hide();	
});

//button CHAT
$(document).off('click', '.ChatButton').on("click", ".ChatButton", function () {	
	var event_id = this.name;
	var encrypted_event_id = $(this).attr("encrypted_event_id");
	var token = $(this).attr("token_attr");
	//console.log('Start Chat: event_id: ' + event_id + ' & subcategory_id = ' + subcategory_id);
	
	$('.UnreadNotification').hide();
	$('#ChatContent'+subcategory_id+'_'+event_id).load('chat_include.php?'+superuser+'_id='+superuser_id+'&subcategory_id='+subcategory_id+'&token='+secret_date+'&eventID='+encrypted_event_id+'&chatter=superuser');
	$('#MailContent'+subcategory_id+'_'+event_id).load("superuser_chat.php?"+superuser+"_id="+superuser_id+"&subcategory_id="+subcategory_id+"&startdate="+startdate+"&eventID="+event_id+"&is_superuser=1");
	
	//scroll to bottom of chatpanel
	setTimeout(function(){
		var nav = $('.ScrollToButtons');
		 if (nav.length) {
		 $('html, body').animate({
			 scrollTop: nav.offset().top
		  }, 100)
		  }
	 }, 500);
	
 });



//button DELETE CHAT
$(document).off('click','.DeleteAnswer').on("click",".DeleteAnswer",function() {
	var answer_id = this.id;
	var superuser_id = this.name;
	var event_id = $(this).attr("event_id_attr");
	var subcategory_id = $(this).attr("subcategory_attr");
	var superuser = $(this).attr("superuser_attr");
	var chatbox_token = $(this).attr("chatbox_token_attr"); 
	var token = $(this).attr("token_attr"); 
	var encrypted_event_id = $(this).attr("encrypted_event_attr"); 
	var startdate = $(this).attr("startdate_attr"); 
	console.log('Delete Chat: answer_id: ' + answer_id + ' & subcategory_id = ' + subcategory_id + ' & superuser_id = ' + superuser_id + ' & event_id = ' + event_id + ' & superuser = ' + superuser + ' & chatbox_token = ' + chatbox_token + ' & token = ' + token + ' & encrypted_event_id = ' + encrypted_event_id + ' & startdate = ' + startdate);
 

  $.ajax({
    type: "POST",
    url: "superuser_actions.php?action=DeleteAnswer",
    data:{
      answer_id: answer_id
    },
    success: function(data){
	    	if(event_id != 0){			
				$('#ChatContent'+subcategory_id+'_'+event_id).load('chat_include.php?'+superuser+'_id='+superuser_id+'&subcategory_id='+subcategory_id+'&token='+token+'&eventID='+encrypted_event_id+'&chatter=superuser');
				$('#MailContent'+subcategory_id+'_'+event_id).load("superuser_chat.php?"+superuser+"_id="+superuser_id+"&subcategory_id="+subcategory_id+"&startdate="+startdate+"&eventID="+event_id+"&is_superuser=1");
			}
			else {
				$(".chatbox_balloons"+superuser+"_"+superuser_id+"_"+subcategory_id).load('chat_include.php?'+superuser+'_id='+superuser_id+'&subcategory_id='+subcategory_id+'&chatbox='+chatbox_token+'&chatter=superuser');
				$(".chatbox_input"+superuser+"_"+superuser_id+"_"+subcategory_id).load('superuser_chat.php?'+superuser+'_id='+superuser_id+'&subcategory_id='+subcategory_id+'&is_superuser=1&chatbox=' + chatbox_token);
			}
	    	    
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: delete chat). Gelieve opnieuw te proberen.");
    },  
  });

});


// KFJ

//button RSVP
$(document).off('click', '.RSVPButton').on("click", ".RSVPButton", function () {	
	var event_id = this.name;
	var encrypted_event_id = $(this).attr("encrypted_event_id");
	var token = $(this).attr("token_attr");
	//console.log('Start Chat: event_id: ' + event_id + ' & subcategory_id = ' + subcategory_id);
	
	$('.UnreadNotification').hide();
	$('#ChatContent'+subcategory_id+'_'+event_id).load('chat_include.php?'+superuser+'_id='+superuser_id+'&subcategory_id='+subcategory_id+'&token='+secret_date+'&eventID='+encrypted_event_id+'&chatter=superuser');
	$('#MailContent'+subcategory_id+'_'+event_id).load("superuser_chat.php?"+superuser+"_id="+superuser_id+"&subcategory_id="+subcategory_id+"&startdate="+startdate+"&eventID="+event_id+"&is_superuser=1");
	
	//scroll to bottom of chatpanel
	setTimeout(function(){
		var nav = $('.ScrollToButtons');
		 if (nav.length) {
		 $('html, body').animate({
			 scrollTop: nav.offset().top
		  }, 100)
		  }
	 }, 500);
	
 });




/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: labels/marks ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(document).off('click','.NewLabel').on("click",".NewLabel",function() {
	$('#Div'+subcategory_id).load("superuser_marks_new.php"+urlsecurity);
});

//button delete label
$(document).off('click','.DeleteLabelButton').on("click",".DeleteLabelButton",function() {
	var label_id = this.id;	
	//console.log("Delete label: label_id:" + label_id);				
 $.ajax({
    type: "POST",
    url: "superuser_actions.php"+urlsecurity+"&action=DeleteLabel",
    data:{
      label_id: label_id
    },
    success: function(data){
	    	  
	              $('#Div'+subcategory_id).load('superuser_marks_list.php'+urlsecurity);
	              $('#myModalRemoveLabel'+label_id).modal('hide');
				  $('body').removeClass('modal-open');
				  $('.modal-backdrop').remove();
				  
			notif({
			  msg: "Label is verwijderd!",
			  position: "center",
			  type: "info",
			  width: 300,
			  time: 2500
			});
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: Verwijder label). Gelieve opnieuw te proberen.");
    }, 
    
  });
  
});


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: upload attachment actions ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//upload new attachment
$(document).off('click','.AttachmentsIncludeLink').on("click",".AttachmentsIncludeLink",function() {
	var event_id = this.id;
	var chatbox = $(this).attr("chatbox_attr");
	
	$( ".AddAttachmentButton" ).hide();
	$('#AttachmentsInclude').load("superuser_attachments_include.php"+urlsecurity+"&event_id="+event_id+"&chatbox="+chatbox);	
	console.log("Add new attachment: event_id: " + event_id + " & chatbox: " + chatbox);		    
});

//open list old attachments (filelist) to add to calendar item
$(document).off('click','.ReUseAttachment').on("click",".ReUseAttachment",function() {
	$('#AttachmentsInclude').load('superuser_files_list.php'+urlsecurity+'&event_id='+event_id+'&filter=calendar');
	$( ".BackButtonAttachmentModal" ).show();
	$( ".AddAttachmentButton" ).hide();			    
});

//open list old attachments (filelist) to add to chat
$(document).off('click','.OpenFilesListChat').on("click",".OpenFilesListChat",function() {
	$('.FilesListChat').show();
	$('.FilesListChat').load('superuser_files_list.php'+urlsecurity+'&event_id=0&filter=calendar');	    
});

//go back	
$(document).off('click','.BackButtonAttachmentModal').on("click",".BackButtonAttachmentModal",function() {
	$('.CalendarInfoDiv').load("superuser_attachments.php"+urlsecurity+"&event_id="+event_id);
	$( ".BackButtonAttachmentModal" ).hide();			    
});
	
//button delete document
$(document).off('click','.DeleteAttachmentButton').on("click",".DeleteAttachmentButton",function() {
	var document_id = this.id;	
	//console.log("Delete Document from attachment: document_id: " + document_id);				
 $.ajax({

    type: "POST",
    url: "superuser_actions.php"+urlsecurity+"&action=delete_document",
    data:{
      document_id: document_id
    },
    success: function(data){   	  
	          $('.CalendarInfoDiv').load("superuser_attachments.php"+urlsecurity+"&event_id="+event_id);			  
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: Verwijder bijlage). Gelieve opnieuw te proberen.");
    },  
  });
  
});

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: superuser_files_list actions ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(document).off('click','.filetype').on("click",".filetype",function() {
    var filetype = this.id;
	if(filter == "calendar"){
		$('#AttachmentsInclude').load("superuser_files_list.php"+urlsecurity+"&filter=calendar&event_id="+event_id+"&filetype="+filetype);
	}
	else {
		$('#Div'+subcategory_id).load("superuser_files_list.php"+urlsecurity+"&filetype="+filetype);
	}
    
});

$(document).off('click','.SelectTag').on("click",".SelectTag",function() {
	var tag = this.id;
	if(filter == "calendar"){
	$('#AttachmentsInclude').load("superuser_files_list.php"+urlsecurity+"&filter=calendar&event_id="+event_id+"&tag="+tag);
	} else {
	$('#Div'+subcategory_id).load("superuser_files_list.php"+urlsecurity+"&tag="+tag);
	}
});

$(document).off('click','.RemoveFilterFiles').on("click",".RemoveFilterFiles",function() {			        
	if(filter == "calendar"){
	$('#AttachmentsInclude').load("superuser_files_list.php"+urlsecurity+"&filter=calendar&event_id="+event_id);
	} else {
	$('#Div'+subcategory_id).load("superuser_files_list.php"+urlsecurity);
	}
});
						
$(document).off('click','.NewFile').on("click",".NewFile",function() {
	var document_id = this.name;
	var filetype = this.id;
	if(document_id != 0){
		$('#myModalExtraAttachment'+document_id).modal('hide');
		$('body').removeClass('modal-open');
		$('.modal-backdrop').remove();
	}
	$('#Div'+subcategory_id).load("superuser_file_new.php"+urlsecurity+"&filetype="+filetype+"&document_id="+document_id);
});
				        
					
$(document).off('click','.EditDocument').on("click",".EditDocument",function() {
	var document_id = this.id;
	$('#Div'+subcategory_id).load("superuser_file_edit.php"+urlsecurity+"&document_id="+document_id);
});
			
$(document).off('click', '.FileUpdateAction').on("click", ".FileUpdateAction", function () {
	var document_id = this.id;
	var percentage = $(this).attr("percent_attr");
	var type = $(this).attr("type_attr");
	var shared_id = $(this).attr("shared_attr");
	$('.filesinfo').modal('show');
	$('.FilesInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	$.ajax({

		type: "POST",
		url: "modal_include.php"+urlsecurity,
		data: {
			document_id: document_id,
			percentage: percentage,
			subcategory_id: subcategory_id,
			superuser: superuser,
			superuser_id: superuser_id,
			shared_id: shared_id,
			modal: type
		},

		success: function (data) {
			$('.FilesInfoDiv').html(data);
		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (Modal FileUpdateAction). Gelieve opnieuw te proberen.");
		},

	});
});
		
//button delete link with another file
$(document).off('click','.DeleteLinkDocumentButton').on("click",".DeleteLinkDocumentButton",function() {
	var shared_document_id = this.id;
	var document_id = this.name;			
 $.ajax({
    type: "POST",
    url: "superuser_actions.php"+urlsecurity+"&action=delete_document_link",
    data:{
	  shared_document_id: shared_document_id,
      document_id: document_id
    },
    success: function(data){
	    	  
	              $('#Div'+subcategory_id).load('superuser_files_list.php'+urlsecurity+'&filter='+filter);
	              $('.filesinfo').modal('hide');
				  $('body').removeClass('modal-open');
				  $('.modal-backdrop').remove();
				  
			notif({
			  msg: "Bestand is losgekoppeld!",
			  position: "center",
			  type: "info",
			  width: 300,
			  time: 2500
			});
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: Verwijder link met document). Gelieve opnieuw te proberen.");
    },    
  }); 
});
				
$(document).off('click','.AddOldFileAttachment').on("click",".AddOldFileAttachment",function(e) {
	var shared_document_id = this.id;
	var document_id = this.name;			
 $.ajax({

    type: "POST",
    url: "superuser_actions.php"+urlsecurity+"&action=add_document_link",
    data:{
	  shared_document_id: shared_document_id,
      document_id: document_id
    },
    success: function(data){
	    	  
	              $('#Div'+subcategory_id).load('superuser_files_list.php'+urlsecurity+'&filter='+filter);
	              $('.filesinfo').modal('hide');
				  $('body').removeClass('modal-open');
				  $('.modal-backdrop').remove();
				  
			notif({
			  msg: "Bestand is toegevoegd!",
			  position: "center",
			  type: "success",
			  width: 300,
			  time: 2500
			}); 
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: Voeg bestaand bestand toe aan document). Gelieve opnieuw te proberen.");
    },    
  }); 
});
				
					
//button delete document
$(document).off('click','.DeleteDocumentButton').on("click",".DeleteDocumentButton",function(e) {
	var document_id = this.id;	
	//console.log("Delete document: document_id:" + document_id);				
 $.ajax({
    type: "POST",
    url: "superuser_actions.php"+urlsecurity+"&action=delete_document",
    data:{
      document_id: document_id
    },
    success: function(data){
	    	  
	              $('#Div'+subcategory_id).load('superuser_files_list.php'+urlsecurity+'&filter='+filter);
	              $('.filesinfo').modal('hide');
				  $('body').removeClass('modal-open');
				  $('.modal-backdrop').remove();
				  
			notif({
			  msg: "Bestand is verwijderd!",
			  position: "center",
			  type: "info",
			  width: 300,
			  time: 2500
			});
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: Verwijder document). Gelieve opnieuw te proberen.");
    }, 
    
  });
  
});
				
//button hide document
$(document).off('click','.HideDocumentButton').on("click",".HideDocumentButton",function() {
	var document_id = this.id;
	var hide = this.name;	
	//console.log("Hide/unhide document: document_id:" + document_id + '& hide: ' + hide);				
 $.ajax({

    type: "POST",
    url: "superuser_actions.php"+urlsecurity+"&action=hide_document",
    data:{
      document_id: document_id,
      hide: hide
    },
    success: function(data){
	    	  
      $('#Div'+subcategory_id).load('superuser_files_list.php'+urlsecurity+'&filter='+filter);
      $('.filesinfo').modal('hide');
	  $('body').removeClass('modal-open');
	  $('.modal-backdrop').remove();
			
	  if(hide == 1){ 	  
	  notif({
	  msg: "Je hebt het bestand verborgen",
	  position: "center",
	  type: "info",
	  width: 300,
	  time: 2500
		});
		}
	  if(hide == 0){ 	  
	  notif({
	  msg: "Je hebt het bestand terug zichtbaar gemaakt",
	  position: "center",
	  type: "info",
	  width: 300,
	  time: 2500
		});
		}            
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: Hide/unhide document). Gelieve opnieuw te proberen.");
    },     
  });
});
					
		
				


$(document).off('click','.AddAttachmentToEvent').on("click",".AddAttachmentToEvent",function() {
	var document_id = this.id;
	var subcategory_id = this.name;
	var recurrent = $(this).attr("recurrent_attr");
	var url = "superuser_actions.php"+urlsecurity+"&action=add_old_attachment";
	$.ajax({
	  type: "POST",
	  url: url,
	  data: {
		  document_id: document_id,
		  event_id: event_id,
		  subcategory_id: subcategory_id,
		  recurrent: recurrent
	  },
	  success: function(data) {
	   $('.CalendarInfoDiv').load("superuser_attachments.php"+urlsecurity+"&event_id="+event_id); 
			notif({
		  msg: "Je hebt de bijlage succesvol toegevoegd!",
		  position: "center",
		  type: "success",
		  time: 2500,
		  multiline: true
		});
	   $( ".BackButtonAttachmentModal" ).hide();
	  }
	});
});
  

$(document).off('click','.SearchFileButton').on("click",".SearchFileButton",function(event) {  
  event.preventDefault();
  var document_id = this.id
  var search_file = $("#"+document_id+".search_file").val();
  
  $('.SearchFileButton').html('<i class="fal fa-spinner fa-pulse fa-fw" aria-hidden="true"></i>');
  
  if(search_file == ''){
     NotifError("fillallfields");
     $(".ExtraAttachmentDiv").html('');
     $('.SearchFileButton').html('<span class="fal fa-search fa-xlg"></span>');
     return false;
    }

    $.post("superuser_database_files.php"+urlsecurity, 
    { 
        search : search_file,
        document_id: document_id 
    }, function(data) {
            $(".ExtraAttachmentDiv").html(data);
            $('.SearchFileButton').html('<span class="fal fa-search fa-xlg"></span>');
        });
    

}); 

$(document).off('click','.CloseExtraAttachment').on("click",".CloseExtraAttachment",function() {
	var document_id = this.id;
	$('#Div'+subcategory_id).load('superuser_files_list.php'+urlsecurity+'&filter='+filter);
		$('.filesinfo').modal('hide');
		$('body').removeClass('modal-open');
		$('.modal-backdrop').remove();
});


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: paybuttons actions ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(document).off('click','.MakeNewPayButton').on("click",".MakeNewPayButton",function() {
	$(targetdiv).load("superuser_paybuttons_new_edit.php"+urlsecurity+"&event_id="+event_id);
	$('.AddNewPayButtonModal').hide();
});

$(document).off('click','.ShowListPayButtons').on("click",".ShowListPayButtons",function() {
	$(targetdiv).load("superuser_paybuttons_list.php"+urlsecurity+"&event_id="+event_id);
});

$(document).off('click','.BackButtonPayButtonModal').on("click",".BackButtonPayButtonModal",function() {
	$('.CalendarInfoDiv').load("superuser_paybuttons.php"+urlsecurity+"&event_id="+event_id);			    
});

$(document).off('click','.PayButtonDetails').on("click",".PayButtonDetails",function() {
	var button_id = this.id;
	//console.log("open details paybutton: button_id: " + button_id);
	$('#Div'+subcategory_id).load("superuser_paybuttons_list.php"+urlsecurity);
	$('.calendarinfo').modal('hide');			    
});

//button unlink paybutton from event
$(document).off('click','.UnlinkPayButton').on("click",".UnlinkPayButton",function() {
	var button_id = this.id;
	var event_id = this.name;					
	
	$.ajax({

	    type: "POST",
	    url: "superuser_actions.php"+urlsecurity+"&action=UnlinkPayButtonEvent",
	    data:{
          button_id: button_id,
          event_id: event_id
        },
	    success: function(data){
		    	  $('.CalendarInfoDiv').load("superuser_paybuttons.php"+urlsecurity+"&event_id="+event_id);			  
	    },
	    error: function(){
	      alert("Er is een probleem opgetreden met de app (functie: Verwijder Shopitem uit bijlage). Gelieve opnieuw te proberen.");
	    },   
	});
});

$(document).off('click','.EditPayButton').on("click",".EditPayButton",function() {
	var button_id = this.id;
	//console.log("Edit paybutton: button_id = " + button_id);
	$(targetdiv).load("superuser_paybuttons_new_edit.php"+urlsecurity+"&button_id="+button_id);
});

$(document).off('click','.EditOrderForm').on("click",".EditOrderForm",function() {
	var button_id = this.id;
	var subtype = this.name
	//console.log("Edit Order Form paybutton: button_id = " + button_id);
	$(targetdiv).load("superuser_paybuttons_form.php"+urlsecurity+"&button_id="+button_id+"&subtype="+subtype);
});
						
//archive paybutton
$(document).off('click','.ArchivePayButton').on("click",".ArchivePayButton",function() {
	var button_id = this.id;	
	//console.log("Archive paybutton: button_id:" + button_id);				
	 $.ajax({
	    type: "POST",
	    url: "superuser_actions.php"+urlsecurity+"&action=ArchivePayButton",
	    data:{
          button_id: button_id
        },
	    success: function(data){
		    	  
		              $(targetdiv).load('superuser_paybuttons_list.php'+urlsecurity);
		              $('#myModalArchivePayButton'+button_id).modal('hide');
					  $('body').removeClass('modal-open');
					  $('.modal-backdrop').remove();
					  
				notif({
				  msg: "Shopitem is gearchiveerd!",
				  position: "center",
				  type: "info",
				  width: 300,
				  time: 2500
				});
	    },
	    error: function(){
	      alert("Er is een probleem opgetreden met de app (functie: Archiveer Shopitem). Gelieve opnieuw te proberen.");
	    }, 		    
	});
});

//reactivate paybutton
$(document).off('click','.ActivatePayButton').on("click",".ActivatePayButton",function() {
	var button_id = this.id;	
	//console.log("Activate paybutton: button_id:" + button_id);				
	 $.ajax({

	    type: "POST",
	    url: "superuser_actions.php"+urlsecurity+"&action=ActivatePayButton",
	    data:{
          button_id: button_id
        },
	    success: function(data){
		    	
		    	$(targetdiv).load('superuser_paybuttons_list.php'+urlsecurity);

			    notif({
				  msg: "Shopitem is terug actief gemaakt!",
				  position: "center",
				  type: "success",
				  width: 300,
				  time: 2500
				});
	    },
	    error: function(){
	      alert("Er is een probleem opgetreden met de app (functie: Activeer Shopitem). Gelieve opnieuw te proberen.");
	    },  
	});	  
});
				
		
//delete archived paybutton
$(document).off('click','.DeletePayButton').on("click",".DeletePayButton",function() {
	var button_id = this.id;	
	//console.log("Delete paybutton: button_id:" + button_id);				
 
 $.ajax({
    type: "POST",
    url: "superuser_actions.php?get_<?php echo $superuser ?>_id=<?php echo $superuser_id ?>&action=DeletePayButton",
    data:{
      button_id: button_id
    },
    success: function(data){
	    	  
      $(targetdiv).load('superuser_paybuttons_list.php'+urlsecurity);
      $('#myModalDeletePayButton'+button_id).modal('hide');
	  $('body').removeClass('modal-open');
	  $('.modal-backdrop').remove();
	
	notif({
	  msg: "Shopitem is verwijderd!",
	  position: "center",
	  type: "info",
	  width: 300,
	  time: 2500
	});  
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: Archiveer Shopitem). Gelieve opnieuw te proberen.");
    }, 
    
  });
  
});


$(document).off('click','.AddPayButtonToEvent').on("click",".AddPayButtonToEvent",function() {			
	var button_id = this.id;
    var url = "superuser_actions.php"+urlsecurity+"&action=AddPayButtonEvent";
    $.ajax({
      type: "POST",
      url: url,
      data: {
	      button_id: button_id,
	      event_id: event_id,
	      subcategory_id: subcategory_id
      },
      success: function(data) {
       $('.CalendarInfoDiv').load("superuser_paybuttons.php"+urlsecurity+"&event_id="+event_id);
       $( ".BackButtonPayButtonModal" ).hide();
       
	   notif({
		  msg: "Je hebt de gekozen Shopitem succesvol toegevoegd!",
		  position: "center",
		  type: "success",
		  time: 2500,
		  multiline: true
		});
      }
    });
});
 
//mail users who haven't payed
$(document).off('click','.MailNotPayed').on("click",".MailNotPayed",function() {
	var val_kids = $(this).attr("kid_id_attr");
	var val_parents = $(this).attr("adult_id_attr");
	var val_manual = $(this).attr("manual_id_attr");
	var button_id = this.id;
	//console.log("Mail members not payed: id_list_kids: " + val_kids + " & id_list_parents:" + val_parents + " & id_list_manual: " + val_manual);

	$.ajax({
	    type: "POST",
	    url: "superuser_mail.php"+urlsecurity,
	     data:{
          id_list_kids: val_kids.toString(),
          id_list_parents: val_parents.toString(),
          id_list_manual: val_manual.toString(),
          button_id: button_id
        },
	    
	    success: function(data){
	           $(targetdiv).html(data);     
	    },
	    error: function(){
	      alert("Er is een probleem opgetreden met de app (mailfunctie vanuit paybuttons list). Gelieve opnieuw te proberen.");
	    }, 
	    
	  });

});
					
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: survey actions ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(document).off('click','.DeleteQuestionButton').on("click",".DeleteQuestionButton",function() {
	var question_id = this.id;
	var type = this.name
	//console.log("Delete Question!!: question_id : " +question_id+ " & type : " +type);					
	 $.ajax({
	
	    type: "POST",
	    url: "superuser_actions.php"+urlsecurity+"&action=DeleteQuestion",
	    data:{
	      question_id: question_id,
	      type: type,
	      event_id: event_id
	    },
	    success: function(data){ 	  
		 if(event_id != 0){
		   $('.CalendarInfoDiv').load("superuser_survey.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate);
		   } else {
		   $('#Div'+subcategory_id).load("superuser_survey_list.php"+urlsecurity);
		   }
		    $('#myModalArchiveSurvey'+question_id).modal('hide');
		    $('body').removeClass('modal-open');
			$('.modal-backdrop').remove();			  
	    },
	    error: function(){
	      alert("Er is een probleem opgetreden met de app (functie: Verwijder vraag). Gelieve opnieuw te proberen.");
	    }, 
	    
	  });
});

$(document).off('click','.EndQuestion').on("click",".EndQuestion",function() {
	var question_id = this.id;
	//console.log("end question");
	$(targetdiv).load("superuser_survey_new_edit.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate+"&question_id="+question_id);
});

//create new survey	
$(document).off('click','.AddNewSurvey').on("click",".AddNewSurvey",function() {	
	$(targetdiv).load("superuser_survey_new_edit.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate);		
});

//open list old surveys
$(document).off('click','.SurveyList').on("click",".SurveyList",function() {
	$(targetdiv).load("superuser_survey_list.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate);
});

//go step back		
$(document).off('click','.BackButtonSurveyModal').on("click",".BackButtonSurveyModal",function() {
	$('.CalendarInfoDiv').load("superuser_survey.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate);
});

//edit question after it's finished	
$(document).off('click','.EditOptions').on("click",".EditOptions",function() {			
	var question_id = this.id;
	//console.log("Edit Options: question_id : " +question_id);
						
 $.ajax({

    type: "POST",
    url: "superuser_actions.php"+urlsecurity+"&action=EditOptions&question_id="+question_id,
    data:{
      question_id: question_id
    },
    success: function(data){
	    	  
	$('.DivSurveyInclude').load("superuser_survey_new_edit.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate+"&question_id="+question_id);
				  
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: Edit Opties). Gelieve opnieuw te proberen.");
    }, 
    
  });  
});

$(document).off('click','.EditSurveyButton').on("click",".EditSurveyButton",function() {
	var question_id = this.id;
	//console.log("Edit Survey: question_id : " +question_id);					
	$('#Div'+subcategory_id).load("superuser_survey_new_edit.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate+"&question_id=" + question_id);
});

//activate Survey
$(document).off('click','.ActivateSurvey').on("click",".ActivateSurvey",function() {
	var question_id = this.id;	
	//console.log("Activate survey: question_id:" + question_id);				
 $.ajax({

    type: "POST",
    url: "superuser_actions.php"+urlsecurity+"&action=ActivateSurvey",
    data:{
      question_id: question_id
    },
    success: function(data){
	   if(event_id != 0){
	   $('.CalendarInfoDiv').load("superuser_survey_list.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate);
	   } else {
	   $('#Div'+subcategory_id).load("superuser_survey_list.php"+urlsecurity);
	   }
	              								  
	notif({
	  msg: "Antwoordstrookje is terug actief. Je kan het nu terug invoegen in een agendapunt!",
	  position: "center",
	  multiline: true,
	  type: "info",
	  width: 300,
	  time: 2500
	});
    },
    error: function(){
      alert("Er is een probleem opgetreden met de app (functie: Activeer antwoordstrookje). Gelieve opnieuw te proberen.");
    }, 
    
  });
  
});

$(document).off('click','.AddSurveyToEvent').on("click",".AddSurveyToEvent",function() {				
	var question_id = this.id;
	var subcategory_id = this.name;
	var active = $(this).attr("active_attr");
    var url = "superuser_actions.php"+urlsecurity+"&action=ReUseSurvey";
    $.ajax({
      type: "POST",
      url: url,
      data: {
	      question_id: question_id,
	      event_id: event_id,
	      subcategory_id: subcategory_id,
	      active: active
      },
      success: function(data) {
       $('.CalendarInfoDiv').load("superuser_survey.php"+urlsecurity+"&event_id="+event_id+"&startdate="+startdate);
      }
    });
  });
 
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: superuser_mails_list.php en superuser_mails_details ////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$(document).off('click','.OpenMail').on("click",".OpenMail",function() {
	var mail_id = this.id;
	$('#Div'+subcategory_id).load('superuser_mails_details.php'+urlsecurity+'&mail_id='+mail_id+'&all='+all+'&archive='+archive);
});

$(document).off('click','.GoFilter').on("click",".GoFilter",function() {			
	var year = this.name;				        	
	$('#Div'+subcategory_id).load("superuser_mails_list.php"+urlsecurity+"&all="+all+"&archive="+archive+"&year="+year);
});

$(document).off('click', '.DuplicateMail').on("click", ".DuplicateMail", function () {		
   var mail_id = this.id;
   var board = this.name;
   if(board == "0"){
	   $('#Div'+subcategory_id).load('superuser_members_list.php'+urlsecurity+'&mail_id='+mail_id+'&all='+all+'&archive='+archive);
	   }
	   else {
	   $('#Div'+subcategory_id).load('superuser_members_list_all.php'+urlsecurity+'&mail_id='+mail_id+'&all='+all+'&archive='+all);  
	   }
});

$(document).off('click', '.EditMail').on("click", ".EditMail", function () {				
	   var mail_id = this.id;
	   var board = this.name;
	   $('#Div'+subcategory_id).load('superuser_mail.php'+urlsecurity+'&mail_id='+mail_id+'&all='+all+'&archive='+archive+'&reuse=0&board='+board);
});

$(document).off('click', '.SubmitRemoveMail').on("click", ".SubmitRemoveMail", function (e) {
   e.preventDefault();
   var mail_id = this.id;
   var mailtype = this.name;
    $.ajax({
        type: "POST",
        url: "superuser_actions.php"+urlsecurity+"&action=remove_mail",
        data:{
			mail_id: mail_id,
			mailtype: mailtype
		},
        cache: false,
        success: function(data)
       {
              notif({
			  msg: "Je hebt de mail succesvol verwijderd.",
			  position: "center",
			  type: "info",
			  width: 300,
			  time: 2500,
			  multiline: true
			});
		   $('.userinfo').modal('hide');
		   $('body').removeClass('modal-open');
		   $('.modal-backdrop').remove();
           $('#Div'+subcategory_id).load("superuser_mails_list.php"+urlsecurity+"&all="+all+"&archive="+archive);
       }
    });
});

  
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: superuser_members_list.php ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//open modal bulk actions
$(document).off('click','.OpenModalBulkSolo').on("click",".OpenModalBulkSolo",function() {

	var old_subcategory;

	if (allsubs === 1) {
		old_subcategory = $(this).attr("old_sub_attr");
	}
	if (allsubs === 0) {
		old_subcategory = subcategory_id;
	}


	var type = $(this).attr("type_attr");
	var val_kids = $(this).attr("kid_id_attr");
	var val_parents = $(this).attr("parent_id_attr");
	var val_manual = $(this).attr("manual_id_attr");

	$('.memberinfo').modal('show');
	$('.MemberInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	//console.log("Open modal move/copy members: kid_id: " + val_kids + " & parent_id:" + val_parents + " & manual_id: " + val_manual);

	$.ajax({
		type: "POST",
		url: "modal_include.php" + urlsecurity,
		data: {
			id_list_kids: val_kids,
			id_list_parents: val_parents,
			id_list_manual: val_manual,
			old_subcategory: old_subcategory,
			modal: type
		},
		success: function (data) {
			$('.MemberInfoDiv').html(data);
		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (Move/Copy open Modal). Gelieve opnieuw te proberen.");
		},
	});
});

//open modal bulk actions
$(document).off('click','.OpenModalBulk').on("click",".OpenModalBulk",function() {
	var old_subcategory;

	if (allsubs === 1) {
		var val_subgroup = [];
		$('.checkbox_subgroup:checked').each(function (i) {
			val_subgroup[i] = $(this).val();
		});

		if (val_subgroup.length > 1) {
			NotifError("morethanonesubgroup");
			return false;
		}
		old_subcategory = val_subgroup.toString();
	}
	if (allsubs === 0) {
		old_subcategory = subcategory_id;
	}

	var type = $(this).attr("type_attr");
	var val_kids = [];
	var val_parents = [];
	var val_manual = [];
	var i = 0;
	$('.checkbox_kids:checked').each(function (i) {
		val_kids[i] = $(this).val();
	});
	$('.checkbox_parents:checked').each(function (i) {
		val_parents[i] = $(this).val();
	});
	$('.checkbox_manual:checked').each(function (i) {
		val_manual[i] = $(this).val();
	});

	if (val_kids + val_parents + val_manual === '') {
		NotifError("movecopymembers");
		$(".Other").dropdown('toggle');
		return false;
	}

	$('.memberinfo').modal('show');
	$('.MemberInfoDiv').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	//console.log("Open modal move/copy members: id_list_kids: " + val_kids + " & id_list_parents:" + val_parents + " & id_list_manual: " + val_manual);

	$.ajax({
		type: "POST",
		url: "modal_include.php" + urlsecurity,
		data: {
			id_list_kids: val_kids.toString(),
			id_list_parents: val_parents.toString(),
			id_list_manual: val_manual.toString(),
			old_subcategory: old_subcategory,
			modal: type
		},
		success: function (data) {
			$('.MemberInfoDiv').html(data);
		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (Move/Copy open Modal). Gelieve opnieuw te proberen.");
		},
	});
});

//move/copy user
$(document).off('click', '.SubmitMoveCopy').on("click", ".SubmitMoveCopy", function (e) {
	e.preventDefault();
	var type = $(this).attr("type_attr");
	var success_message;
	if (type == "movemembers") {
		success_message = "De geselecteerde gebruikers zijn verplaatst!";
	}
	if (type == "copymembers") {
		success_message = "De geselecteerde gebruikers zijn gekopieerd!";
	}


	$.ajax({
		type: "POST",
		url: "superuser_actions.php" + urlsecurity + "&action=MoveCopyMembers",
		data: $('.MoveCopyMembersForm').serialize(),
		success: function () {
			notif({
				msg: success_message,
				position: "center",
				type: "success",
				width: 300,
				time: 2500,
				multiline: true
			});
			$(targetdiv).load(thisurl + urlsecurity);
			$('.memberinfo').modal('hide');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (CopyMove-functie). Gelieve opnieuw te proberen.");
		},
	});
});
    // Added KFJ open modal paybuttoninfo 
    $(document).off("click", ".paybuttoninfo").on("click", ".paybuttoninfo", function () {
    	var content = $(this).attr("name");     // De inhoud van de popup
    	var buttonHtml = $(this).attr("url_attr");  // De knop (zoals 'markeer als betaald')

	// Voeg hier je eigen modal / popup logica toe, bijv. met Notiflix of Bootstrap
	let popupHtml = `
		<div id="payInfoPopup" style="padding: 15px;">
			${content}
			<hr>
			${buttonHtml}
		</div>
	`;

	// Simpele popup met Notifit (als je die gebruikt)
	notif({
		msg: popupHtml,
		position: "center",
		type: "info",
		multiline: true,
		width: 350,
		time: 222500
	});
});
// end Added KFJ

$(document).ready(function () {
  console.log("JS geladen");

  $(document).off('click', '.manualpayed').on("click", ".manualpayed", function () {
    var kid_id = $(this).attr("kid_id_attr");
    var adult_id = $(this).attr("adult_id_attr");
    var manual_id = $(this).attr("manual_id_attr");
    var button_id = this.id;
    var method = this.name;

    console.log("Klik gedetecteerd op knop");
    console.log("Method: " + method);

    $.ajax({
      type: "POST",
      url: "superuser_actions.php" + urlsecurity + "&action=ManualPay",
      data: {
        kid_id: kid_id,
        adult_id: adult_id,
        manual_id: manual_id,
        button_id: button_id,
        method: method
      },
      success: function () {
        console.log("AJAX succesvol");
        notif({
          msg: "Je hebt de betaalstatus aangepast",
          position: "center",
          type: "info",
          multiline: true,
          width: 300,
          time: 2500
        });
        $(targetdiv).load(thisurl + urlsecurity);
      }
    });
  });
});


//edit kid
$(document).off('click', '.EditMember').on("click", ".EditMember", function () {
	var all = $(this).attr("all_attr");
	var kid_id = $(this).attr("kid_id_attr");
	var parent_id = $(this).attr("parent_id_attr");
	var manual_id = $(this).attr("manual_id_attr");
	var subcategory_id = this.name;

	$.ajax({

		type: "POST",
		url: "superuser_members_edit.php" + urlsecurity,
		data: {
			kid_id: kid_id,
			parent_id: parent_id,
			manual_id: manual_id,
			all: all
		},

		success: function (data) {
			$(targetdiv).html(data);

		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (members edit). Gelieve opnieuw te proberen.");
		},

	});
});

$(document).off('click', '.MailList').on("click", ".MailList", function () {
	var board = this.id;
	var archive = this.name;
	$(targetdiv).load("superuser_mails_list.php" + urlsecurity + "&archive=" + archive + "&all=" + board);
});

$(document).off('click', '.EditMarks').on("click", ".EditMarks", function () {
	$(targetdiv).load("superuser_marks_list.php" + urlsecurity);
});

$(document).off('click', '#MailSome').on("click", "#MailSome", function () {

	var val_subgroup = "";

	if (allsubs === 1) {
		val_subgroup = [];
		$('.checkbox_subgroup:checked').each(function (i) {
			val_subgroup[i] = $(this).val();
		});
	}
	if (allsubs === 0) {
		val_subgroup = 0;
	}

	var val_kids = [];
	var val_parents = [];
	var val_manual = [];
	var i = 0;
	$('.checkbox_kids:checked').each(function (i) {
		val_kids[i] = $(this).val();
	});
	$('.checkbox_parents:checked').each(function (i) {
		val_parents[i] = $(this).val();
	});
	$('.checkbox_manual:checked').each(function (i) {
		val_manual[i] = $(this).val();
	});

	var submiturl;
	if (mail_id === 0) {
		submiturl = "superuser_mail.php" + urlsecurity;
	} else {
		submiturl = "superuser_mail.php" + urlsecurity + "&mail_id=" + mail_id + "&all=" + all + "&archive=" + archive + "&reuse=1";
	}

	if (val_kids + val_parents + val_manual === '') {
		NotifError("mailmembers");
		$(".Mail").dropdown('toggle');
		return false;
	}
	//console.log("Mail members!: id_list_kids: " + val_kids + " & id_list_parents:" + val_parents + " & id_list_manual: " + val_manual);

	$.ajax({

		type: "POST",
		url: submiturl,
		data: {
			board: allsubs,
			id_list_kids: val_kids.toString(),
			id_list_parents: val_parents.toString(),
			id_list_manual: val_manual.toString(),
			id_list_subgroups: val_subgroup.toString(),
			mail_id: mail_id
		},

		success: function (data) {
			$(targetdiv).html(data);

		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (mailfunctie). Gelieve opnieuw te proberen.");
		},

	});
});

//open modal export	
$(document).off('click', '.OpenExportFromMembers').on("click", ".OpenExportFromMembers", function () {
	var event_id = this.id;
	var subcategory_id = this.name;
	var val_kids = [];
	var val_parents = [];
	var val_manual = [];
	var i = 0;
	$('.checkbox_kids:checked').each(function (i) {
		val_kids[i] = $(this).val();
	});
	$('.checkbox_parents:checked').each(function (i) {
		val_parents[i] = $(this).val();
	});
	$('.checkbox_manual:checked').each(function (i) {
		val_manual[i] = $(this).val();
	});

	if (val_kids + val_parents + val_manual === '') {
		NotifError("exportmembers");
		$(".Other").dropdown('toggle');
		return false;
	}
	//console.log("Open modal export members!: id_list_kids: " + val_kids + " & id_list_parents:" + val_parents + " & id_list_manual: " + val_manual);

	$.ajax({

		type: "POST",
		url: "superuser_export.php" + urlsecurity,
		data: {
			id_list_kids: val_kids.toString(),
			id_list_parents: val_parents.toString(),
			id_list_manual: val_manual.toString(),
			board: allsubs
		},

		success: function (data) {
			$('.memberinfo').modal('show');
			$('.MemberInfoDiv').html(data);
		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (exportfunctie). Gelieve opnieuw te proberen.");
		},

	});
});

$(document).off('click', '.TextSome').on("click", ".TextSome", function () {
	var val_kids = [];
	var val_parents = [];
	var val_manual = [];
	var i = 0;
	$('.checkbox_kids:checked').each(function (i) {
		val_kids[i] = $(this).val();
	});
	$('.checkbox_parents:checked').each(function (i) {
		val_parents[i] = $(this).val();
	});
	$('.checkbox_manual:checked').each(function (i) {
		val_manual[i] = $(this).val();
	});

	if (val_kids + val_parents + val_manual === '') {
		NotifError("textmembers");
		return false;
	}

	var url = "superuser_actions.php" + urlsecurity + "&action=sms_member"; // the script where you handle the form input.
	var dataString = "subcategory=" + subcategory_id + "&id_list_kids=" + val_kids + "&id_list_parents=" + val_parents + "&id_list_manual=" + val_manual;


	$.ajax({
		type: "POST",
		url: url,
		async: false,
		dataType: "json",
		data: dataString,
		success: function (response) {
			var phone_list_iOS = response.phone_list_iOS;
			var phone_list_Android = response.phone_list_Android;

			if (navigator.userAgent.match(/(iPod|iPhone|iPad)/i)) {
				window.location.href = "sms:/open?addresses=" + phone_list_iOS;
				//window.open("sms:/open?addresses=" + phone_list_iOS);
				//$(".smsbutton").attr("href", "sms:/open?addresses=" + phone_list_iOS);
				$(".smsnotification").html("<div class='alert alert-success alert-dismissible'><span class='fal fa-info-circle'></span><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a> Ben je zeker dat je een sms wil versturen naar deze leden?<br><br><div align='center'><a class='btn btn-success btn-sm SendSMS' href='sms:/open?addresses=" + phone_list_iOS + "' target='_blank'><span class='fas fa-paper-plane'></span> verzend sms</a></div></div>");
			}
			if (navigator.userAgent.match(/(Android)/i)) {
				window.location.href = "sms:" + phone_list_Android;
				//window.open("sms:" + phone_list_Android);
				//$(".smsbutton").text("android start");
				//$(".smsbutton").attr("href", "sms:" + phone_list_Android);
			}

		},
		error: function () {
			alert("Er is een probleem opgetreden met het samenstellen van de GSM-nummers. Gelieve opnieuw te proberen.");
		},
	});
});

$(document).off('click', '.SendSMS').on("click", ".SendSMS", function () {
	$(".smsnotification").html("");
})

$(document).off('click', '.CheckSome').on("click", ".CheckSome", function () {
	var type = this.name;
	var label_id = this.id;
	var val_kids = [];
	var val_parents = [];
	var val_manual = [];
	var i = 0;
	$('.checkbox_kids:checked').each(function (i) {
		val_kids[i] = $(this).val();
	});
	$('.checkbox_parents:checked').each(function (i) {
		val_parents[i] = $(this).val();
	});
	$('.checkbox_manual:checked').each(function (i) {
		val_manual[i] = $(this).val();
	});

	var submiturl;
	if (type === "check") {
		submiturl = "superuser_actions.php" + urlsecurity + "&action=label_member";
	}
	if (type === "uncheck") {
		submiturl = "superuser_actions.php" + urlsecurity + "&action=unlabel_member";
	}

	if (val_kids + val_parents + val_manual === '') {
		NotifError("markmembers");
		$(".Labels").dropdown('toggle');
		return false;

	}
	//console.log("Label members: label_id: " + label_id + " & id_list_kids: " + val_kids + " & id_list_parents:" + val_parents + " & id_list_manual: " + val_manual);


	$.ajax({

		type: "POST",
		url: submiturl,
		data: {
			label_id: label_id,
			subcategory_id: subcategory_id,
			id_list_kids: val_kids.toString(),
			id_list_parents: val_parents.toString(),
			id_list_manual: val_manual.toString()
		},
		success: function (data) {
			$("#Div" + subcategory_id).load(thisurl + urlsecurity);
		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (label functie). Gelieve opnieuw te proberen.");
		},

	});
});

$(document).off('click', '#CloseButtonReUseMail').on("click", "#CloseButtonReUseMail", function () {
	$(targetdiv).load("superuser_mails_list.php" + urlsecurity + "&all=" + all + "&archive=" + archive);
});


$(document).off('click', '.filtermembers').on("click", ".filtermembers", function () {
	var val = this.id;
	$(targetdiv).load(thisurl + urlsecurity + "&filter_id=" + val);
});

$(document).off('click', '.RemoveFilter').on("click", ".RemoveFilter", function () {
	$(targetdiv).load(thisurl + urlsecurity);
});

//select all checkboxes
$(document).on("change", "#select_all", function () {
	$(".checkbox").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
});

//".checkbox" change 
$(document).on("change", ".checkbox", function () {
	//uncheck "select all", if one of the listed checkbox item is unchecked
	if (false === $(this).prop("checked")) { //if this item is unchecked
		$("#select_all").prop('checked', false); //change "select all" checked status to false
	}
	//check "select all" if all checkbox items are checked
	if ($('.checkbox:checked').length == $('.checkbox').length) {
		$("#select_all").prop('checked', true);
	}
});

$(document).off('click', '.FilterSome').on("click", ".FilterSome", function (e) {
	e.preventDefault();
	var label_id = this.id;
	var name = this.name;
	var filter_attr = $(this).attr("filter_attr");
	var title_attr = $(this).attr("title_attr");

	if (name == "paybutton") {
		$(".checkbox").prop('checked', true);
	}

	if (filter_attr === "negative") {
		$(".checkbox").prop('checked', true);
	}

	var submiturl = "superuser_actions.php" + urlsecurity + "&action=filter"; // the script where you handle the form input.

	$.ajax({
		type: "POST",
		url: submiturl,
		async: false,
		dataType: "json",
		data: {
			label_id: label_id,
			subcategory_id: subcategory_id,
			board: allsubs,
			name: name
		},
		success: function (response) {
			var id_list_kids = response.id_list_kids;
			var id_list_parents = response.id_list_parents;
			var id_list_manual = response.id_list_manual;
			var cur_filters = $('.showfilters').html();

			if (filter_attr === "positive") {
				$.each($(id_list_kids), function (key, value) {
					//console.log("kid_id: " + value);
					if (name == "label") {
						$(".checkbox_kids:checkbox[value=" + value + "]").prop("checked", true).change();
					}
					if (name == "paybutton") {
						$(".checkbox_kids:checkbox[value=" + value + "]").prop("checked", false).change();
					}

				});

				$.each($(id_list_parents), function (key, value) {
					//console.log("parent_id: " + value);
					if (name == "label") {
						$(".checkbox_parents:checkbox[value=" + value + "]").prop("checked", true).change();
					}
					if (name == "paybutton") {
						$(".checkbox_parents:checkbox[value=" + value + "]").prop("checked", false).change();
					}

				});

				$.each($(id_list_manual), function (key, value) {
					//console.log("manual_id: " + value);
					if (name == "label") {
						$(".checkbox_manual:checkbox[value=" + value + "]").prop("checked", true).change();
					}
					if (name == "paybutton") {
						$(".checkbox_manual:checkbox[value=" + value + "]").prop("checked", false).change();
					}

				});


				if (cur_filters)
					$('.showfilters').html(cur_filters + " | <small><u>met</u> label: " + title_attr + "</small>");
				else
					$('.showfilters').html("<small><u>met</u> label: " + title_attr + "</small>");

			}
			if (filter_attr === "negative") {

				$.each($(id_list_kids), function (key, value) {
					//console.log("kid_id: " + value);
					if (name == "label") {
						$(".checkbox_kids:checkbox[value=" + value + "]").prop("checked", false).change();
					}
					if (name == "paybutton") {
						$(".checkbox_kids:checkbox[value=" + value + "]").prop("checked", true).change();
					}

				});

				$.each($(id_list_parents), function (key, value) {
					//console.log("parent_id: " + value);
					if (name == "label") {
						$(".checkbox_parents:checkbox[value=" + value + "]").prop("checked", false).change();
					}
					if (name == "paybutton") {
						$(".checkbox_parents:checkbox[value=" + value + "]").prop("checked", true).change();
					}

				});

				$.each($(id_list_manual), function (key, value) {
					//console.log("manual_id: " + value);
					if (name == "label") {
						$(".checkbox_manual:checkbox[value=" + value + "]").prop("checked", false).change();
					}
					if (name == "paybutton") {
						$(".checkbox_manual:checkbox[value=" + value + "]").prop("checked", true).change();
					}

				});

				if (cur_filters)
					$('.showfilters').html(cur_filters + " | <small><u>zonder</u> label: " + title_attr + "</small>");
				else
					$('.showfilters').html("<small><u>zonder</u> label: " + title_attr + "</small>");

			}


		},
		error: function (req, err) {
			//console.log('my message: ' + err);
		},

	});

});


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: superuser_members_list_all.php en superuser_contacts_list_all.php /////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var $panels = $('.panel');

$(document).off('click', '.OpenAll').on("click", ".OpenAll", function () {
	$('#accordion .collapse').collapse('show');
});

$(document).off('click', '.CloseAll').on("click", ".CloseAll", function () {
	$('#accordion .collapse').collapse('hide');
});

$(document).off('click', '.ResetSearch').on("click", ".ResetSearch", function () {
	$panels.show();
	$('.CloseAll').trigger("click");
	$('.SearchMember').val('');
});

$(document).off('click', '.SortSubgroups').on("click", ".SortSubgroups", function () {
	$('#LoadSubgroup').load("superuser_subcategory_sort.php" + urlsecurity);
});

/*
$(document).off('click', '.SearchMember').on("click", ".SearchMember", function () {
	var val = $('.SearchMember').val().toLowerCase();
	if (val !== '') {
		$panels.show().filter(function () {
			var panelBodyText = $(this).find('.panel-body').text().toLowerCase();
			return panelBodyText.indexOf(val) < 0;
		}).hide();

		$('.OpenAll').trigger("click");
	}

});
*/

function filterSearch(unique_id, searchTerm, delimitor, playground){
	var $panels = $('.panel');
	// remove any old highlighted terms
	$('body').unhighlight();
	if (!searchTerm ) {
	$panels.show(); 
	$(playground).show(); 
	$('.collapse').collapse('hide');
	}

	// disable highlighting if empty
	if ( searchTerm ) {
		//add + between terms to search for multiple
		var terms = searchTerm.split(delimitor);
	   $.each(terms, function(_, term){
			  // highlight the new term
		term = term.trim();
		if(term != ""){

			//hide panels that do not contain terms
			$panels.show().filter(function() {
				var panelBodyText = $(this).find('.panel-body').text().toLowerCase();
				return panelBodyText.indexOf(term) < 0;
			}).hide();
			
			//hide <li> that do not contain terms

			$(playground).show().filter(function() {
				var panelBodyText = $(this).text().toLowerCase();
				return panelBodyText.indexOf(term) < 0;
			}).hide();

			$(playground).highlight(term);
			$('.collapse').collapse('show');
	   }
		});  
	
		console.log($(playground + ' .highlight:visible').length);
		
	}
	
}

$(document).on("keyup change", ".SearchMember", function () {
	$('.tagtitles').remove();
	var unique_id = this.id;
	var searchTerm = $('.SearchMember').val().toLowerCase();
	filterSearch(unique_id, searchTerm, ' ', '.panel-body');
});	

$(document).off('click', '.RemoveFilters').on("click", ".RemoveFilters", function () {
	$(".checkbox").prop('checked', false);
	$('.showfilters').html("");
});

//select all checkboxes
$(document).on("change", ".checkbox_subgroup", function () {
	var id = this.id;
	$("#" + id + ".checkbox_subgroup_child").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
});

$(document).on("change", ".checkbox_subgroup_child", function () {
	var id = this.id;
	if (false === $(this).prop("checked")) { //if this item is unchecked
		$("#" + id + ".checkbox_subgroup").prop('checked', true);
	}
	if (true === $(this).prop("checked")) { //if this item is unchecked
		$("#" + id + ".checkbox_subgroup").prop('checked', true);
	}
	//check "select all" if all checkbox items are checked
	if ($("#" + id + ".checkbox_subgroup_child:checked").length === 0) {
		$("#" + id + ".checkbox_subgroup").prop('checked', false);
	}
});

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////// PARAGRAPH: superuser_contacts_list.php ///////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(document).off('click', '.NewContact').on("click", ".NewContact", function () {
	var subcategory = $(this).attr("subcategory_attr");
	$('.NewContact').html('<i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Laden...');
	$('#Div' + subcategory_id).load("superuser_contacts_new.php" + urlsecurity + "&addto=" + subcategory + "&board=" + allsubs);
});

$(document).off('click', '.EditContact').on("click", ".EditContact", function () {
	var id = this.id;
	//console.log("Edit contact person: id : " + id);
	$('#Div' + subcategory_id).load("superuser_contacts_edit.php" + urlsecurity + "&contact_id=" + id + "&board=" + allsubs);
});

//remove contact person
$(document).off('click', '.SubmitRemoveContactFromList').on("click", ".SubmitRemoveContactFromList", function () {
	var id = this.id;
	var this_subcategory = $(this).attr("subcategory_attr");
	//console.log("Remove contact person from subcategory: " + subcategory_id + " & id : " + id);
	$.ajax({

		type: "POST",
		url: "superuser_actions.php" + urlsecurity + "&action=remove_contact",
		data: {
			id: id,
			subcategory_id: this_subcategory
		},
		success: function (data) {
			notif({
				msg: "Je hebt deze contactpersoon verwijderd.",
				position: "center",
				type: "success",
				width: 300,
				time: 2500,
				multiline: true
			});

			$('#Div' + subcategory_id).load(thisurl + urlsecurity);
			$('#myModalRemove' + id).modal('hide');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();

		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (verwijder contactpersoon vanuit contactlijst). Gelieve opnieuw te proberen.");
		},

	});
});

//link clubaccount to personal account
$(document).off('click', '.SubmitLink').on("click", ".SubmitLink", function () {
	var id = this.id;
	var user_id = this.name;
	var this_subcategory = $(this).attr("subcategory_attr");
	//console.log("Link contact person to subcategory : " + this_subcategory + " & id : " + id);

	$.ajax({
		type: "POST",
		url: "superuser_actions.php" + urlsecurity + "&action=link_contact",
		data: {
			superuser_contact_id: id,
			superuser_user_id: superuser_user_id,
			subcategory_id: this_subcategory,
			user_id: user_id,
		},

		success: function (data) {
			notif({
				msg: "Je hebt het account succesvol gekoppeld aan de gekozen subgroep.",
				position: "center",
				type: "success",
				width: 300,
				time: 2500,
				multiline: true
			});

			$('#Div' + subcategory_id).load(thisurl + urlsecurity);
			$('#myModalLink' + id).modal('hide');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();

		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (link contactpersoon). Gelieve opnieuw te proberen.");
		},

	});
});

//UNlink clubaccount from personal account
$(document).off('click', '.SubmitLinkRemove').on("click", ".SubmitLinkRemove", function () {
	var id = this.id;
	var this_subcategory = $(this).attr("subcategory_attr");
	//console.log("Unlink contact person from subcategory: " + this_subcategory + " & id : " + id);

	$.ajax({

		type: "POST",
		url: "superuser_actions.php" + urlsecurity + "&action=unlink_contact",
		data: {
			subcategory_id: this_subcategory,
			superuser_contact_id: id

		},

		success: function (data) {
			notif({
				msg: "Je hebt het account van deze contactpersoon ontkoppeld van de subgroep.",
				position: "center",
				type: "info",
				width: 300,
				time: 2500,
				multiline: true
			});

			$('#Div' + subcategory_id).load(thisurl + urlsecurity);
			$('#myModalLink' + id).modal('hide');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();

		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (unlink contactpersoon). Gelieve opnieuw te proberen.");
		},

	});
});

$(document).off('click', '.ContactHead').on("click", ".ContactHead", function () {
	var id = this.id;
	var this_subcategory = this.name;
	//console.log("Make contact person head for subcategory: " + this_subcategory + " & id : " + id);

	$.ajax({
		type: "POST",
		url: "superuser_actions.php" + urlsecurity + "&action=contact_head",
		data: {
			id: id,
			subcategory_id: this_subcategory,
		},
		success: function (data) {
			$('#Div' + subcategory_id).load(thisurl + urlsecurity);
		},
		error: function () {
			alert("Er is een probleem opgetreden met de app (markeer als hoofd functie). Gelieve opnieuw te proberen.");
		},

	});
});


$(document).on("change", "input.checkbox_kids:checkbox", function () {
	//if(this.checked)    // optional, depends on what you want
	$('input.checkbox_kids[value="' + this.value + '"]:checkbox').prop('checked', this.checked);
});

$(document).on("change", "input.checkbox_parents:checkbox", function () {
	//if(this.checked)    // optional, depends on what you want
	$('input.checkbox_parents[value="' + this.value + '"]:checkbox').prop('checked', this.checked);
});

$(document).on("change", "input.checkbox_manual:checkbox", function () {
	//if(this.checked)    // optional, depends on what you want
	$('input.checkbox_manual[value="' + this.value + '"]:checkbox').prop('checked', this.checked);
});
