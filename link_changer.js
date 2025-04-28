//convert premium-only links to disabled with notifit
$(".premium_disabled").css("color", "gray");
$(".premium_disabled").attr("href", "#");
$(".premium_disabled").attr("data-target", "#");
$(".premium_disabled").find("span").remove();
$(".premium_disabled").attr('class', 'notifit_custom whatspremium');
$(".whatspremium").find("span").remove();
$(".whatspremium").prepend("<span class='label label-warning pull-right'>PREMIUM</span>");

//convert desktop-only links to disabled with notifit
$(".mobile_disabled").css("color", "gray");
$(".mobile_disabled").attr("href", "#");
$(".mobile_disabled").attr("data-target", "#");
$(".mobile_disabled").find("span").remove();
$(".mobile_disabled").attr('class', 'notifit_custom whatsondesktop');
$(".whatsondesktop").find("span").remove();
$(".whatsondesktop").prepend("<span class='label label-info pull-right'>DESKTOP</span>");

//convert desktop-only links to disabled with notifit
$(".desktop_disabled").css("color", "gray");
$(".desktop_disabled").attr("href", "#");
$(".desktop_disabled").attr("data-target", "#");
$(".desktop_disabled").find("span").remove();
$(".desktop_disabled").attr('class', 'notifit_custom whatsonmobile');
$(".desktop_disabled").find("span").remove();
$(".desktop_disabled").prepend("<span class='label label-info pull-right'>MOBILE</span>");



//convert permission (beheerder) links to disabled with notifit
$(".permission_disabled").css("color", "gray");
$(".permission_disabled").attr("href", "#");
$(".permission_disabled").attr("data-target", "#");
$(".permission_disabled").find("span").remove();
$(".permission_disabled").attr('class', 'notifit_custom whatsadminfunction');
$(".whatsadminfunction").find("span").remove();
$(".whatsadminfunction").prepend("<span class='label label-danger pull-right'>ADMIN</span>");


