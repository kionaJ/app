$(document).on("click", '.notifit_custom', function(event) {
	event.preventDefault();
	var popupmodule = $(this).attr('class').split(' ')[1];
	var text = "";
	var extravar = this.id;
	
	var name = "club";
	if(extravar === "club"){
		name = "vereniging";
	}

	switch (popupmodule) { 
	
	case 'readonly': 
	text =  "<span class='fa fa-info-circle'></span> Indien je gekoppeld bent aan een ander account, kan je een ander volwassen profiel als deze enkel raadplegen, niet aanpassen. Bij gedeelde kindprofielen kan dat wel.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	// added KFJ shopitemlimiet
	case 'ShopItemLimitReached':
            text = "<span class='fa fa-info-circle'></span> Je hebt het maximum van " + maxShopitems + " shopitems bereikt. Upgrade je abonnement om meer shopitems aan te maken.<br><br>Klik op deze melding om deze te verwijderen.";
            break;
    // end added
	case 'whatslinked': 
	text = "<span class='fa fa-info-circle'></span> Het persoonlijke " + appname + "-account van deze contactpersoon is gekoppeld aan deze subgroep. Dit wil zeggen dat deze contactpersoon vanuit zijn/haar persoonlijk " + appname + "-account gemakkelijk kan switchen naar deze subgroep, zonder uit en in te loggen!<br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsmanualmember': 
	text =  "<span class='fa fa-info-circle'></span> Indien je gekoppeld bent aan een ander account, kan je een ander volwassen profiel als deze enkel raadplegen, niet aanpassen. Bij gedeelde kindprofielen kan dat wel.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsdeletefacebook': 
	text =  "<span class='fa fa-info-circle'></span> Je bent geregistreerd op " + appname + " met Facebook. Voor jouw veiligheid en privacy hebben we besloten inloggen via Facebook weg te halen. Maar geen nood: je kan hier simpel een wachtwoord aanmaken, zodat je in het vervolg gemakkelijk kan inloggen met je e-mail en wachtwoord.";
	break;
	
	case 'whatsaddmanualmember':
	text = "<span class='fa fa-info-circle'></span> Leden niet op het internet? Voeg zelf een lid manueel toe. Leden die " + appname + " niet gebruiken, kunnen toch toegevoegd worden aan je " + name + ": Geavanceerde functies zullen hier niet mogelijk zijn, dus we raden aan om je leden toch te stimuleren zelf een account en profiel aan te laten maken via " + appname + ".";
	break;
	
	case 'whatsnotlinked': 
	text = "<span class='fa fa-info-circle'></span> Het persoonlijke " + appname + "-account van deze contactpersoon is niet gekoppeld aan deze subgroep. Een koppeling zorgt ervoor dat deze persoon deze subgroep kan beheren vanuit zijn/haar persoonlijke " + appname + "-account. Selecteer 'Koppel account' om te starten.<br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsselectcalendars': 
	text =  "<span class='fa fa-info-circle'></span> Hieronder vind je een lijst van " + appname + "-kalenders van jezelf, je kind(eren) en/of je gekoppelde account(s). Kies de kalenders die je wil synchroniseren met je smartphone en klik op 'Bewaar'. Ga dan naar stap 2.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsmakelinksmartphone': 
	text = "<span class='fa fa-info-circle'></span> De volgende stappen moet je maar <u><strong>één keer</strong></u> uitvoeren. Als je naderhand de kalenders (stap 1)  aanpast, zal dit automatisch ook in je smartphone aangepast worden. Soms kan dit wel enkele uren duren, afhankelijk van je smartphone.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsRSVPblocks': 
	text = "<span class='fa fa-info-circle'></span> Kies voor welke agendablokken je aanwezigheidsstatistieken wil genereren.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsMailsSubs': 
	text =  "<span class='fa fa-info-circle'></span> Kies voor welke subgroepen je e-mailstatistieken wil genereren.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsOtherBlocks2': 
	text =  "<span class='fa fa-info-circle'></span> Selecteer ook alle activiteiten, die niet in deze subgroep zijn aangemaakt, maar wel van toepassing zijn op deze subgroep.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatstimeframeRSVP': 
	text = "<span class='fa fa-info-circle'></span> Kies een termijn waarin je statistieken wil genereren.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsautomaticimport': 
	text =  "<span class='fa fa-info-circle'></span> Dit agendapunt is automatisch geïmporteerd vanuit een sportdatabank. De <i>Administrator</i> van je vereniging kan deze import beheren.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsboard':
	text =  "<span class='fa fa-info-circle'></span> Een bestuursgroep heeft meer mogelijkheden dan een gewone subgroep. In de knoppenbalk zie je meer opties, zoals overzicht van alle leden van je " + name + ", facturen enzoverder.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsboard_details':
	text =  "<span class='fa fa-info-circle'></span> Bij het aanmaken van subgroepen raden we aan om tenminste 1 subgroep als 'bestuursgroep' te labelen. Een bestuursgroep heeft meer mogelijkheden dan een gewone subgroep: in de knoppenbalk zie je meer opties, zoals overzicht van alle leden en contactpersonen van je " + name + ", facturen enzoverder. Het is dus niet nodig om élke subgroep te labelen als bestuursgroep, maar enkel de subgroep die je als bestuur van je " + name + " gaat gebruiken (bv. directie, trainers, bestuur, oudervereniging, ...)<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatshead': 
	text =  "<span class='fa fa-info-circle'></span> Een contactpersoon als hoofdverantwoordelijke (<span class='fa fa-star'></span>) aangeven wil enkel zeggen dat deze persoon als eerste aanspreekpunt voor deze subgroep kan gezien worden door de (ouders van de) leden.<br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'legit': 
	text =  "<span class='fa fa-info-circle'></span> Wat betekenen deze symbolen?<br>Aan de hand van deze symbolen kan je de betrouwbaarheid van een profiel inschatten.<br><br><span class='fa fa-check-circle' style='color:red;' aria-hidden='true'></span>&nbsp; GSM-nummer van de ouder <u>en</u> de profielfoto van het kind zijn niet toegevoegd.<br><span class='fa fa-check-circle' style='color:orange;' aria-hidden='true'></span>&nbsp; GSM-nummer van de ouder <u>of</u> de profielfoto van het kind zijn niet toegevoegd.<br><span class='fa fa-check-circle' style='color:green;' aria-hidden='true'></span>&nbsp; GSM-nummer van de ouder <u>en</u> de profielfoto van het kind zijn toegevoegd.<br><br>Klik op deze melding om ze te verwijderen";
	break;
	
	case 'face': 
	text = "<span class='fa fa-info-circle'></span> Wat betekenen deze symbolen?<br>Aan de hand van deze symbolen kan je opvolgen of een lid goedkeuring heeft gegeven om beeldmateriaal te publiceren.<br><br><span class='fa fa-camera' style='color:red;' aria-hidden='true'></span>&nbsp; Lid heeft geen goedkeuring gegeven<br><span class='fa fa-camera' style='color:orange;' aria-hidden='true'></span>&nbsp; Lid heeft nog geen goedkeuring gegeven<br><span class='fa fa-camera' style='color:green;' aria-hidden='true'></span>&nbsp; Lid heeft goedkeuring gegeven.<br><br>Bij manueel toegevoegde leden kan je, mits je een schriftelijke toestemming hebt, zelf dit registreren door het toegevoegd lid te bewerken.<br><br>Klik op deze melding om ze te verwijderen";
	break;
	
	case 'whatspaper': 
	text =  "<span class='fa fa-info-circle'></span> Standaard kan je de ingevulde agenda van je subgroep exporteren naar pdf, om ze af te drukken of voor die leden die geen internet hebben.<br><br>Klik op deze melding om ze te verwijderen";
	break;
	
	case 'whatsfolder': 
	text =  "<span class='fa fa-info-circle'></span> Gebruik deze folder om reclame te maken voor " + appname + " bij jullie leden!<br><br>Klik op deze melding om ze te verwijderen";
	break;	
	
	case 'WhatsAddNew': 
	text =  "<span class='fa fa-info-circle'></span> Hier vind je " + appname + "-gebruikers die hebben aangegeven dat ze lid zijn bij jouw " + name + ". De leden die het meeste matchen met de instellingen (leeftijdscategorie en geslacht) van deze subgroep staan bovenaan (met grijze achtergrond). Deel je leden in in de juiste subgroep(en).<br><br>Klik op deze melding om ze te verwijderen";
	break;
	
	case 'whatsaddtomultiplesubgroups': 
	text =  "<span class='fa fa-info-circle'></span> Kies de subgroepen waar deze gebruiker lid van is. Een gebruiker kan bij aanmaak van zijn/haar " + appname + "-profiel zelf optioneel aangeven in welke subgroepen hij/zij wil ingedeeld worden. Deze subgroepen zijn hier automatisch aangevinkt.<br><br>Klik op deze melding om ze te verwijderen";
	break;
	
	case 'whatsaddnewmembercontactperson': 
	text =  "<span class='fa fa-info-circle'></span> Kies de subgroepen waar je deze gebruiker contactpersoon van wil maken. Deze gebruiker kan dan ook vanuit zijn persoonlijk " + appname + "-account deze subgroep(en) zelf beheren (als <i>Beheerder van de subgroep</i>).<br><br>Klik op deze melding om ze te verwijderen";
	break;	
	
	case 'whatsQRNew': 
	text =   "<span class='fa fa-info-circle'></span> Exporteer QR-codes van de geselecteerde leden. Deze kan je bij elke activiteit inscannen met de " + appname + "-app om de fysieke aanwezigheid van je leden op de activiteit te bevestigen. In elk agendapunt kan je de scanfunctie openen via de knop <span class='fa fa-ellipsis-h fa-lg' aria-hidden='true'></span><br><br>Klik op deze melding om ze te verwijderen";
	break;
	
	case 'whatstitleblock': 
	text =  "<span class='fa fa-info-circle'></span> Geef elke agendablok een referentie. Dit kan dienen als legende om het overzicht te bewaren. Deze referentie wordt <u>in</u> de agendablok boven de kalender geprojecteerd, maar wordt niet getoond in de agenda zelf.<br><br>Bijvoorbeeld:<br><div class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event' style='vertical-align: top; width:40px; height:40px;background-color: #663300; border-color: #663300; color: white; display: inline-block; font-size: xx-small'><span class='fa fa-calendar' style='color: white'></span><br>Titel</div>";
	break;
	
	case 'whatscolorblock': 
	text =  "<span class='fa fa-info-circle'></span> Een kleur geven aan een agendablok is bedoeld om de kalender overzichtelijker te maken. Jullie leden zullen deze verschillende kleuren ook zien in hun " + appname + "-agenda.</div>";
	break;
	
	case 'whatsprivacyGDPR': 
	text =  "<span class='fa fa-info-circle'></span> Vanaf 25 mei 2018 is het verplicht dat je als " + name + " een privacyverklaring opstelt voor je leden. " + appname + " geeft je met dit document een duidelijke aanzet om, mits kleine aanpassingen, toe te passen binnen jouw organisatie.";
	break;
	
	case 'whatspaybuttonmail': 
	text =   "<span class='fa fa-info-circle'></span> Je kan hier beslissen om een Shopitem in de mail mee te sturen. Shopitems kan je aanmaken door in de knoppenbalk op <span class='fa fa-file'></span> te klikken en 'Shopitems' te selecteren.";
	break;
	
	case 'whatssurveymail': 
	text =  "<span class='fa fa-info-circle'></span> Je kan hier beslissen om een antwoordstrookje in de mail mee te sturen. Antwoordstrookjes kan je aanmaken door in de knoppenbalk op <span class='fa fa-file'></span> te klikken en 'Antwoordstrookjes' te selecteren.";
	break;
	
	case 'whatssurveymail': 
	text =  "<span class='fa fa-info-circle'></span> Je kan hier beslissen om een antwoordstrookje in de mail mee te sturen. Antwoordstrookjes kan je aanmaken door in de knoppenbalk op <span class='fa fa-file'></span> te klikken en 'Antwoordstrookjes' te selecteren.";
	break;
	
	case 'replytomail': 
	text =  "<span class='fa fa-info-circle'></span> Deze mail wordt verstuurd vanuit de " + appname + "-applicatie. Je kan hier beslissen naar wie de bestemmelingen kunnen een antwoord sturen. Je kan ook aangeven dat er op deze mail niet gereageerd kan worden.";
	break;
	
	case 'attachmentsmail': 
	text =  "<span class='fa fa-info-circle'></span> Toegelaten: PDF, Word of Foto's - maximum 5 - maximum grootte per document: 5Mb.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'duplicateprofile': 
	text =  "<span class='fa fa-info-circle'></span> Controleer even of je partner met zijn/haar account niet reeds je kind heeft toegevoegd. Als dat zo is, kan je je ouderaccount koppelen aan dat van je partner.<br><br><a href='user_share.php' class='btn btn-success btn-block btn-sm'><span class='fa fa-link'></span> koppel je account</a><br><br>Als je zeker weet dat je kind nog niet is ingevoerd, gelieve het " + appname + "-team te contacteren op <a href='mailto: info@" + appname + ".be' target='_blank'>info@" + appname + ".be</a><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'AttachmentsAllowed': 
	text =  "<span class='fa fa-info-circle'></span> Je kan een document (Word of PDF) of een fotobestand uploaden als bijlage.<br>Toevoegen van een fotoalbum, video, of muziekbestand kan ook. Voeg deze dan eerst toe aan het documentenbeheer (<span class='fa fa-file'></span> in de knoppenbalk) en koppel ze daarna aan dit agendapunt.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'SendEvent': 
	text =  "<span class='fa fa-info-circle'></span> Door deze functie aan te zetten zal er, na het drukken op 'Pas Aan', automatisch een mail en pushbericht verstuurd worden naar de leden van de <u>geselecteerde subgroepen</u> (manueel toegevoegde leden krijgen enkel een mail, geen pushbericht).<br>Als er bijlages of een antwoordstrookje toegevoegd zijn aan het agendapunt, worden deze ook automatisch meegestuurd.<br><br><strong>Belangrijk: Bulk mails naar meer dan " + max_mails_direct + " gebruikers ineens worden niet onmiddellijk verstuurd, maar gefaseerd. Je kan de status van verzending opvolgen in 'verzonden mails' (Premium functie)</strong><br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'SendEventSMS': 
	text =  "<span class='fa fa-info-circle'></span> Door deze functie aan te zetten zal, na het drukken op 'Pas Aan', je SMS-programma openen op je smartphone. De GSM-nummers van de leden die je wil bereiken zullen automatisch ingevuld zijn. Ook zullen alle details van dit agendapunt automatisch in het bericht klaarstaan.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'AutomaticSendEvent': 
	text =   "<span class='fa fa-info-circle'></span> Geef aan wanneer " + appname + " automatisch een (herinnerings)mail en pushbericht mag versturen naar de leden van de <u>geselecteerde subgroepen</u> voor de start van de activiteit.<br><strong>Als er bijlages of een antwoordstrookje toegevoegd zijn aan het agendapunt, worden deze ook automatisch meegestuurd.</strong><br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'RSVPEvent': 
	text = "<span class='fa fa-info-circle'></span> Deze functie is handig voor de manueel toegevoegde leden. " + appname + "-leden kunnen bij élk toegevoegd agendapunt in de app aangeven of ze aanwezig willen zijn of niet. Leden die geen gebruik maken van " + appname + " kunnen via een knop in de e-mail uitgenodigd worden om aan te geven of ze al dan niet aanwezig zullen zijn op deze activiteit.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'EnddateSurvey': 
	text =  "<span class='fa fa-info-circle'></span> Geef een einddatum van je antwoordstrookje op. Na deze datum kunnen je leden niet meer antwoorden op het antwoordstrookje. Je leden worden automatisch op de hoogte gebracht als de einddatum nadert, en ze nog geen antwoord hebben gegeven.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'SurveyOptions': 
	text =  "<span class='fa fa-info-circle'></span> Er zijn verschillende opties mogelijk:<br>Laat per gebruiker meerdere antwoordopties tegelijk toe, laat per gebruiker maar 1 antwoordoptie toe, of voorkom zelfs dat verschillende gebruikers hetzelfde antwoord geven (dit is bijvoorbeeld handig bij een antwoordstrookje met afspraakuren).<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'SelectMembers': 
	text =   "<span class='fa fa-info-circle'></span> Vink leden aan om ze te selecteren en druk dan op een groene knop hierboven om een actie uit te voeren.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'SendUpload': 
	text =  "<span class='fa fa-info-circle'></span> Door deze functie aan te zetten zal er, na het drukken op 'Voeg toe', automatisch een mail en pushbericht verstuurd worden naar de leden van de <u>geselecteerde subgroepen</u> om te melden dat dit bestand toegevoegd is.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'AddTag': 
	text =  "<span class='fa fa-info-circle'></span> Je kan bij toevoegen van een document of bestand deze indelen in één of meerdere eigen aangemaakte subcategorieën, zoals bv. wedstrijdverslagen, partituren, trainingsschema, adressenlijst, ... . Op deze manier kunnen je leden de bestanden sneller terugvinden.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'EditDocumentDisabled': 
	text =  "<span class='fa fa-info-circle'></span> Je kan het bestand zelf niet aanpassen. Enkel de naam, de categorieën en in welke subgroepen je het bestand wil laten verschijnen.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'WhyCantIEdit': 
	text =  "<span class='fa fa-info-circle'></span> Als je ingelogd bent als beheerder van een subgroep, kan je een agendapunt dat is aangemaakt in een subgroep waaraan je niet gekoppeld bent, niet bewerken of verwijderen. Enkel de Administrator kan dit. De Administrator kan je wel deze rechten toewijzen in de instellingen van de app.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'WhyCantIAddPaybuttons': 
	text =  "<span class='fa fa-info-circle'></span> Als je ingelogd bent als beheerder van een subgroep, kan je geen Shopitems toevoegen. Enkel de <i>Administrator</i> kan dit. De <i>Administrator</i> is de persoon die inlogt met het e-mailadres dat gebruikt werd om je " + name + " te registeren.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'WhyCantIAddContacts': 
	text =   "<span class='fa fa-info-circle'></span> Als je ingelogd bent als beheerder van een subgroep, kan je geen contactpersonen toevoegen. Enkel de <i>Administrator</i> kan dit. De <i>Administrator</i> is de persoon die inlogt met het e-mailadres dat gebruikt werd om je " + name + " te registeren.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'NoPayPalMollie': 
	text =  "<span class='fa fa-info-circle'></span>Je kan momenteel geen Shopitems aanmaken, omdat we nog enkele gegevens nodig hebben.<br><br><a href='login/profile.php?goto=BtnEditPayButtons' class='btn btn-success btn-block btn-sm'>Configureer gegevens.</a><br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'WhyCantIEditDocument': 
	text =  "<span class='fa fa-info-circle'></span> Als je ingelogd bent als beheerder van een subgroep, kan je een document of bestand dat is toegevoegd in een subgroep waaraan je niet gekoppeld bent, niet bewerken of verwijderen. Enkel de Administrator kan dit. De Administrator kan je wel deze rechten toewijzen in de instellingen van de app.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'ApproveFaceManual': 
	text =  "<span class='fa fa-info-circle'></span> Het is volgens de huidige privacyrichtlijnen verplicht om goedkeuring te vragen aan je leden om beeldmateriaal (foto's, video's) van hen op " + appname + " te publiceren. Vink aan als je schriftelijke toestemming van dit toegevoegd lid hebt.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'HelpSuperCalendar': 
	text =  "<span class='fa fa-info-circle'></span> Om een agendapunt toe te voegen, sleep je een van deze blokken op de kalender. Je kan ook blokken toevoegen en configureren door op <span class='fa fa-cog'></span> en dan 'Beheer agendablokken' te klikken.<br><br>Agendablokjes kunnen ook van de ene dag op de andere gesleept worden.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'WhatsPlannedMailCalendar': 
	text =  "<span class='fa fa-exclamation-triangle'></span> Deze mail staat gepland om automatisch verzonden te worden vanuit de kalender. Om de gegevens van deze mail aan te passen ga je naar de desbetreffende activiteit in de kalender.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsmaillistarchive': 
	text =  "<span class='fa fa-exclamation-triangle'></span> Hier staan alle mails (via de kalender of manueel) die je vanuit deze subgroep verzonden hebt. Als je ingelogd bent als <i>Administrator</i> kan je in de bestuursgroepen hier alle verzonden mails vanuit alle subgroepen raadplegen.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsmaillistplanned': 
	text =   "<span class='fa fa-exclamation-triangle'></span> Hier staan alle mails (via de kalender of manueel) die vanuit deze subgroep gepland staan. Als je ingelogd bent als <i>Administrator</i> kan je in de bestuursgroepen hier alle geplande vanuit alle subgroepen raadplegen.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsmollie': 
	text =  "<span class='fa fa-info-circle'></span> Je kan hier aangeven via welke betaalmethodes je leden de betalingen aan je " + name + " kunnen verrichten. Meer uitleg hierover in je account-pagina (Account in het hoofdmenu).<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsreference': 
	text =   "<span class='fa fa-info-circle'></span> Geef een persoonlijke referentie aan voor de Shopitem. Naast deze referentie zal " + appname + " ook altijd automatisch doorgeven in de ledenlijst welke gebruiker betaald heeft.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsfee': 
	text =  "<span class='fa fa-info-circle'></span> Denk eraan dat bij elke transactie PayPal of Mollie kosten aanrekent (0,25 tot 0,40 cent + vaak ook 1% tot 3% van het bedrag). Consulteer het dashboard van PayPal of Mollie om een overzicht te zien.<br><br>Indien je gebruik maakt van Mollie zal er ook een fee van 1% van dit bedrag naar " + appname + " gaan. Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatstimeframepaybutton': 
	text = "<span class='fa fa-info-circle'></span> Geef een start- en einddatum op wanneer de Shopitem automatisch zichtbaar moet zijn in de app. Na het verstrijken van de einddatum zal de Shopitem automatisch verlopen en niet meer zichtbaar zijn voor je leden. Kies dus een datum ver genoeg in de toekomst.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsactivatediscount': 
	text =  "<span class='fa fa-info-circle'></span> Je kan hier aangeven dat je gebruikers op de betaalpagina een invulveld krijgen om een eventuele afgesproken korting in te geven.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsaccountsponsors': 
	text =  "<span class='fa fa-info-circle'></span> Beheer welke sponsors je in de mailing naar je leden wil laten verschijnen.<br>Voeg hieronder je sponsors toe. Zij zullen in elke mail die je uitstuurt verschijnen.";
	break;
	
	case 'whatseditcontact': 
	text =   "<span class='fa fa-info-circle'></span> Personen die gekoppeld zijn aan een subgroep, kunnen hier de persoonsgegevens naar believen aanpassen. Wil je een ander e-mailadres gebruiken voor je communicatie? Wil je je naam veranderen naar 'Leider Frank' of 'Juffrouw Els' bijvoorbeeld of wil je een ander GSM-nummer opgeven? Dat kan allemaal. Deze aangepaste gegevens kan je dan gebruiken om te communiceren met de (ouders van de) leden, terwijl je privé-gegevens in je persoonlijke " + appname + "-account onaangeroerd blijven.";
	break;
	
	case 'whatspermissions': 
	text = "<span class='fa fa-info-circle'></span> Je bent ingelogd als <i>Administrator</i>. Dit wil zeggen dat je meer rechten en functies in de app hebt dan een contactpersoon die gekoppeld is aan een subgroep (de zogenaamde <i>Beheerder van de subgroep</i>). Hieronder kan je bepalen wat een <i>Beheerder van een subgroep</i> allemaal kan en mag in de app (permissies).";
	break;
	
	case 'whatsinvoicedata': 
	text =  "<span class='fa fa-info-circle'></span> Geef hier de administratieve informatie van je organisatie in, zodat we de uitbetalingen van je verdiensten op " + affiliatename + " kunnen organiseren.<br><strong>Disclaimer:</strong> door deze gegevens in te vullen, geef je aan " + appname + " toestemming om deze gegevens te gebruiken om uitbetalingen te doen. Deze gegevens worden in geen geval met derden gedeeld en worden veilig opgeslagen in onze database.";
	break;
	
	case 'paybuttonpaypal': 
	text = "<span class='fa fa-info-circle'></span> Om van deze optie gebruik te maken, heb je een gratis zakelijk account op 'PayPal' nodig. De aanmeldingsprocedure is erg eenvoudig: ga hiervoor naar <a href='http://www.paypal.com/be/webapps/mpp/account-selection' target='_blank'>PayPal</a>. Vul in " + appname + " het e-mailadres in dat je gebruikt hebt bij de registratie op PayPal. Je kan via deze methode wel maar enkel betalingen via PayPal ontvangen.";
	break;
	
	case 'paybuttonmollie': 
	text =  "<span class='fa fa-info-circle'></span> Om van deze optie gebruik te maken, heb je een gratis account van 'Mollie' nodig. De aanmeldingsprocedure is wegens veiligheidsredenen iets uitgebreider, maar de betaalmogelijkheden zijn compleet!<br><strong>Stap 1:</strong> maak een <u>gratis</u> account aan op <a href='http://www.mollie.com' target='_blank'>www.mollie.com</a> Mollie en " + appname + " zullen enkel een kleine fee per verrichting aanrekenen (Mollie afhankelijk van de betaalmethode, " + appname + " 1% per verrichting). Er zijn geen abonnementskosten! <br><strong>Stap 2:</strong> Druk op de knop 'Connect via Mollie' om " + appname + " de goedkeuring te geven om betalingen voor jou te regelen via jouw Mollie-account.";
	break;
	
	case 'titlepaybutton': 
	text =  "<span class='fa fa-info-circle'></span> Geef een titel aan je Shopitem. Dit is wat de gebruikers dan zullen zien.<br>Bijvoorbeeld:<br><a role='button' class='btn btn-block btn-sm btn-primary'><span class='fa fa-credit-card'></span> betaal je lidgeld</a><br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsselectpicturepaybutton': 
	text =  "<span class='fa fa-info-circle'></span> Voeg optioneel een foto of banner toe aan de betaalpagina voor je gebruikers.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'locationpaybutton': 
	text =  "<span class='fa fa-info-circle'></span> Een Shopitem zal niet enkel in het agendapunt in te voegen zijn, maar ook automatisch in de documentenpagina van je leden verschijnen. Kies hier dus voor welke leden deze Shopitem van toepassing is!<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'dynamicpaybutton': 
	text =  "<span class='fa fa-info-circle'></span> Je kan ook kiezen om geen vast bedrag toe te wijzen aan een Shopitem. Je gebruikers kunnen dan zelf het te betalen bedrag invullen.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsdiscountpaybutton': 
	text =  "<span class='fa fa-info-circle'></span> Indien van toepassing en toegestaan door je " + name + ", kan je hier een korting ingeven in euro.";
	break;
	
	case 'shareaccountotheruser': 
	text =  "<span class='fa fa-info-circle'></span> Een leeg " + appname + "-account (dit is een account zonder (kind)profielen eraan toegevoegd) kan gekoppeld worden aan een ander " + appname + "-account, dat wel (kind)profielen heeft toegevoegd. Na koppeling kunnen dan beide gekoppelde account de (kind)profielen beheren.";
	break;
	
	case 'whatsadministrator': 
	text =  "<span class='fa fa-info-circle'></span> Je bent ingelogd als <i>Administrator</i>. Dit wil zeggen dat je meer rechten en functies in de app hebt dan een contactpersoon die gekoppeld is aan een subgroep (de zogenaamde <i>Beheerder van de subgroep</i>). In de 'Account'-pagina van je " + name + " kan je als <i>Administrator</i> bepalen wat een <i>Beheerder van een subgroep</i> allemaal kan en mag in de app (permissies). Klik <a href='login/profile.php?goto=BtnEditPermissions'>hier</a> om de permissies te beheren.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsbeheerder': 
	text =  "<span class='fa fa-info-circle'></span> Je bent ingelogd als <i>Beheerder van een subgroep</i>. Dit wil zeggen dat je één of meerdere subgroepen kan beheren als contactpersoon van die subgroep. Je hebt minder rechten en functies in de app dan de <i>Administrator</i> van je " + name + ". De <i>Administrator</i> is de persoon die inlogt met het e-mailadres dat gebruikt werd om je " + name + " te registeren.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatspayments': 
	text =  "<span class='fa fa-info-circle'></span> Open de ledenlijst van de subgroep om op te volgen wie wel of niet betaald heeft. Zolang een Shopitem actief is, zal er een icoontje <a role='button' style='display:inline; border: 0px solid lightgray; box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; color:white; background-color: orange'><span class='fa fa-euro-sign fa-fw'></span></a> achter elk lid verschijnen. Als het lid betaald heeft, wordt het icoontje groen.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsiconscalendarsuperuser': 
	text =  "Verklaring van de symbolen:<br><span class='fal fa-clock fa-fw'></span>: tijdsduur activiteit<br><span class='fal fa-comment fa-fw'></span>: extra informatie<br><span class='fal fa-map-marker fa-fw'></span>: locatie activiteit<br><span class='fal fa-credit-card fa-fw'></span>: Shopitems<br><span class='fal fa-cut fa-fw'></span>: antwoordstrookje<br><span class='fal fa-envelope fa-fw'></span>: activiteit gemaild<br><span class='fal fa-paperclip fa-fw'></span>: bijlages<br><span class='fal fa-user-check fa-fw'></span>: aanwezigheden<br><span class='fal fa-user-times fa-fw'></span>: afwezigheden<br><span class='fal fa-envelope-open fa-fw'></span>: leesbevestigingen mails<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsondesktop': 
	text =   "<span class='fa fa-info-circle'></span> Deze functie is enkel mogelijk als je de app gebruikt vanop een computer.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsonmobile': 
	text = "<span class='fa fa-info-circle'></span> Deze functie is enkel mogelijk als je de app gebruikt vanop een smartphone.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsadminfunction': 
	text = "<span class='fa fa-info-circle'></span> Je bent ingelogd als <i>Beheerder van een subgroep</i>. Je hebt minder rechten en functies in de app dan de <i>Administrator</i>. De <i>Administrator</i> kan bepaalde functies voor een <i>Beheerder van een subgroep</i> aan- of uitzetten. De <i>Administrator</i> is de persoon die inlogt met het e-mailadres dat gebruikt werd om je organisatie te registeren.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatspremium': 
	text =  "<span class='fa fa-info-circle'></span> Deze functie is enkel mogelijk in de Premium pakketten van " + appname + ".<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsmaxcontactspremium': 
	text =  "<span class='fa fa-info-circle'></span> Je kan geen extra contactpersonen toevoegen...</strong><br>Je kan maximum 2 contactpersonen per subgroep toevoegen. Upgrade naar " + appname + " Premium en voeg een ongelimiteerd aantal contactpersonen toe.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsopenrate': 
	text =  "<span class='fa fa-info-circle'></span> Je kan hier zien hoeveel procent van de ontvangers je mail hebben geopend:<br>voorbeeld: 45 (geopend) / 90 (verzonden) = 50%";
	break;
	
	case 'whatssurvey': 
	var extra_info = "";
	if(extravar === "club"){
		extra_info = "<br><strong>Tip:</strong> Het is niet nodig om een antwoordstrookje te maken met de vraag of je leden aanwezig zullen zijn op een activiteit. Bij elke activiteit staat er standaard een aanwezigheidsknop in de agenda van je leden.";
	}
	text =  "<span class='fa fa-info-circle'></span> In " + appname + " kan je erg gemakkelijk antwoordstrookjes aanmaken. Een antwoordstrookje maak je aan via een agendapunt." + extra_info + "<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatssurveyanswers': 
	text =   "<span class='fa fa-info-circle'></span> Voeg een antwoordoptie toe en klik op '+' of klik op <span class='fa fa-calendar-alt'></span> om een datum/uur toe te voegen. Als je klaar bent, klik je op 'Volgende stap'.<br><br>Belangrijke info: je leden zullen in hun antwoordformulier ook altijd automatisch een vrij invulveld hebben voor eventuele opmerkingen!";
	break;
	
	case 'HelpSurveyList': 
	text =  "<span class='fa fa-info-circle'></span> Kies 'Voeg toe...' om een bestaand antwoordstrookje toe te voegen in dit agendapunt.<br>Kies 'Dupliceer...' om een <u>nieuw</u> antwoordstrookje aan te maken, met de gegevens van het geselecteerde strookje.";
	break;
	
	case 'NewSurveyPopup': 
	text =  "<span class='fa fa-info-circle'></span> Je kan een nieuw antwoordstrookje aanmaken via een agendapunt in de kalender.";
	break;
	
	case 'whatsicon': 
	var icontype = this.name;
	if(icontype == "user"){
		message = "Manueel opgestelde mail.";
	}
	if(icontype == "file"){
		message = "Mail, verstuurd vanuit bestandsbeheer.";
	}
	if(icontype == "calendar"){
		message = "Mail, verstuurd vanuit de kalender.";
	}
	text =  "<span class='fa fa-info-circle'></span> " + message;
	break;
	
	case 'whatsdeletemultiple': 
	text =  "<span class='fa fa-info-circle'></span> Je kan omwille van veiligheidsredenen gebruikers niet in bulk verwijderen. Je kan wel elke gebruiker individueel verwijderen in de subgroep, door op het icoontje <span class='fa fa-ellipsis-v'></span> te klikken achter zijn/haar naam.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatstakeabsences': 
	text =  "<span class='fa fa-info-circle'></span> Gebruik deze functie om zelf afwezigheden op een activiteit op te nemen. Hier vind je ook een overzicht van de gebruikers die al zelf in " + appname + " hebben aangegeven of ze wel of niet aanwezig zullen zijn, met eventuele extra motivatie.<br><br>Legende:<br><span class='fa fa-user-check'></span> Aanwezig<br><span class='fa fa-user-times'></span> Afwezig<br><span class='fa fa-user-shield'></span> Afwezig met reden<br>";
	break;
	
	case 'whatsalsocontact': 
	text =  "<span class='fa fa-info-circle'></span> Voeg deze gebruiker toe als contactpersoon door bovenaan de paginag op 'Bewerk' te klikken en dan 'Contactpersonen' te selecteren.<br>Deze functie is enkel mogelijk als je ingelogd bent als <i>Administrator</i>.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsmultiplesubgroups': 
	text =  "<span class='fa fa-info-circle'></span> Om een persoon in meerdere subgroepen tegelijk in te delen, voeg je deze eerst toe aan één subgroep. Daarna kan je in ledenbeheer (knop <span class='fa fa-users'></span> in de knoppenbalk) deze persoon kopiëren naar een andere subgroep. <br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'limitationsphotoalbum': 
	text =  "<span class='fa fa-info-circle'></span> Afhankelijk van je premium account op " + appname + " kan je meerdere foto's in bulk uploaden. Er is geen maximum grootte per foto, maar " + appname + " zal elke foto, groter dan 1Mb automatisch verkleinen.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatssearchnewcontact': 
	text =  "<span class='fa fa-info-circle'></span> Hieronder zie je een lijst van alle <u>volwassen of manueel toegevoegde</u> gebruikers op " + appname + ", verbonden aan je " + name + ". Zoek een naam en klik op <span class='fa fa-ellipsis-v'></span> om deze toe te voegen als contactpersoon van de subgroep.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsotherfunctionsadmin': 
	text = "<span class='fa fa-info-circle'></span> Andere functies, zoals verplaatsen, kopiëren, verwijderen, toevoegen als contactpersoon, ... zijn mogelijk vanuit de <i>Ledenlijsten</i><br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsotherfunctionsbeheerder': 
	text =  "<span class='fa fa-info-circle'></span> Andere functies, zoals verplaatsen, kopiëren, verwijderen, ... is mogelijk vanuit de ledenlijst van de subgroepen die je beheert.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'labelinfo':
	text = "Legende van label <span class='fa "+this.id+"'></span>:<br><i>" +this.name+"</i>"; 
	break;
	
	case 'WhatsSaldo': 
	text = "Dit is het geld dat je club met " + affiliatename + " gespaard heeft.<br>Meer info over " + affiliatename + "? Klik dan <a href='https://www." + appname + ".be/plus/?blank=1' target='_blank'>hier</a>.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'plusinfo': 
	text =   "Met " + affiliatename + " lanceren we de Wafelenbak 2.0. " + affiliatename + " is shoppen bij alle grote en exclusieve partners én je vereniging of sportclub steunen. Gratis!<br>Koop via " + appname + " en shop voortaan voor de goede zaak!<br>Meer info? Klik dan <a href='https://www." + appname + ".be/plus/blank=1' target='_blank'>hier</a>.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'commission': 
	text =  "Dit is het percentage of een vast bedrag van je aankoopbedrag dat " + appname + " sponsort aan je vereniging of sportclub. " + appname + " sponsort ongeacht of er acties of promocodes beschikbaar zijn. Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatslocal': 
	text = "<span class='fa fa-info-circle'></span> Lokale handelaren kunnen nu ook adverteren op onze " + affiliatename + "-pagina, zelfs als ze geen webshop hebben. Meer info vind je <a href='https://www." + appname + ".be/plus' target='_blank'>hier</a>.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsbulkimport': 
	text = "<span class='fa fa-info-circle'></span> Kies een subgroep om je leden te importeren. Je kan ze daarna nog verplaatsen naar andere subgroepen, indien nodig.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatssync': 
	text = "<span class='fa fa-info-circle'></span> Via deze pagina kan je officiële databanken van verschillende sporten automatisch synchroniseren met de " + appname + " verenigingskalender.<br>Dit wil zeggen dat alle matchen automatisch in de " + appname + "-kalender zullen geïmporteerd worden. De " + appname + "-agenda synchroniseert automatisch 1x per dag (elke middag).<br>Het volstaat dus om éénmalig de juiste gegevens hieronder in te geven en je " + appname + "-kalender is altijd up-to-date!<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatspermissionchild': 
	text = "<span class='fa fa-info-circle'></span> " + appname + " vraagt gegevens, zoals geboortedatum, geslacht, postcode en het verenigingsleven. Deze gegevens zullen het ook voor de vereniging(en) van je kind(eren) erg gemakkelijk maken om je kind(eren) te herkennen en ze in te delen in de juiste subgroep. Deze gegevens worden veilig in onze database opgeslagen, GDPR-conform, en worden <u>niet</u> gedeeld met derden!<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatspublicall': 
	text = "<span class='fa fa-info-circle'></span> Als je deze optie selecteert, zal de publieke kalender die je insluit op je website, standaard een 'overzichtskalender' genereren, met <u>alle</u> activiteiten van alle geselecteerde subgroepen in 1 overzicht. Als je deze optie niet selecteert, zal standaard de eerste geselecteerde subgroep getoond worden<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'cantchangecolor': 
	text =  "<span class='fa fa-info-circle'></span> Het is niet mogelijk om de kleur van een agendapunt, aangemaakt in een andere subgroep, te veranderen.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatschoosecolor': 
	text = "<span class='fa fa-info-circle'></span> Bepaal welke kleur de geïmporteerde activiteiten in de agenda mogen hebben. Je kan meer agendablokken/kleuren aanmaken door in de agenda naast de bestaande agendablokken op <span class='fa fa-cog'></span> te klikken en 'beheer agendabokken' te selecteren.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsQRcodeReaderNew': 
	text = "<span class='fa fa-info-circle'></span> Deze QR-code kan door je vereniging gebruikt worden om je fysieke aanwezigheid te registreren op een activiteit.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsQR': 
	text = "<span class='fa fa-info-circle'></span> Open de camera op je smartphone om de QR code te scannen die je leden kunnen oproepen in de details van deze activiteit. Deze functie gebruik je dus best met je smartphone.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatstags': 
	text =  "<span class='fa fa-info-circle'></span> Voeg tags/zoektermen toe aan je organisatie. Zo kunnen nieuwe leden jou sneller vinden in de " + appname + "-database.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatstagssearch': 
	text = "<span class='fa fa-info-circle'></span> Jouw vereniging kan categoriëen/tags toevoegen aan bestanden, om ze gemakkelijker te kunnen indelen en terugvinden.";
	break;
	
	case 'WhatsSticky': 
	text = "<span class='fa fa-info-circle'></span> Selecteer deze optie om dit bestand altijd vanboven in het bestandsbeheer te laten staan.";
	break;
	
	case 'SymbolsCalendarKid': 
	text = "<span class='fa fa-info-circle'></span> Verklaring van de symbolen in de kalender:<br><span class='fal fa-check fa-fw'></span>: je hebt aangegeven aanwezig te zijn op de activiteit<br><span class='fal fa-times fa-fw'></span>: je hebt aangegeven NIET aanwezig te zijn op de activiteit<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'SymbolsCalendarAdult': 
	text = "<span class='fa fa-info-circle'></span> Verklaring van de symbolen in de kalender:<br><span class='fa fa-check fa-fw'></span>: je hebt aangegeven aanwezig te zijn op de activiteit<br><span class='fa fa-times fa-fw'></span>: je hebt aangegeven NIET aanwezig te zijn op de activiteit<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'AdultEditName': 
	text =  "<span class='fa fa-info-circle'></span> De naam van volwassen gebruikers kan je omwille van privacyredenen niet aanpassen. Volwassen gebruikers kunnen dit zelf doen in hun Account (hoofdmenu: Algemene instellingen...)<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'WhatsBlocks': 
	text = "<span class='fa fa-info-circle'></span> Elke subgroep heeft standaard 2 soorten agendablokken - een bruine en een oranje blok - die op de kalenders gesleept kunnen worden.<br>Hieronder kan je 'standaard gegevens' opgeven, die dan automatisch ingevuld zijn bij het slepen op de kalender.<br>Je kan onderaan deze pagina ook optioneel meer agendablokken toevoegen.<br>Dit systeem zal de workflow versnellen.<br><br>Klik op deze melding om ze te verwijderen.";
	break;
	
	case 'whatsmarkslist': 
	text = "<span class='fa fa-info-circle'></span> Elke subgroep heeft standaard 1 soort label (<span class='fa fa-flag' style='color:green'></span>) dat je kan gebruiken om leden te markeren.<br>Hier kan je (optioneel) extra labels toevoegen, eigen aan je " + name + " of de werking ervan.<br>Dit systeem zal nuttig zijn in je ledenbeheer.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatscloudservice': 
	text =  "<span class='fa fa-info-circle'></span> In websites zoals Dropbox of Google Drive kan je bestanden of mappen publiek delen. Er zal dan een link gegenereerd worden, die raadpleegbaar is door iedereen waar je deze link mee deelt. Hier kan je in " + appname + " zo een link permanent beschikbaar stellen voor je leden. Maak de link aan in bv. Dropbox of Google Drive en vul die hieronder in.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whyemptycalendar': 
	text = "<span class='fa fa-info-circle'></span> Er zijn geen verenigingsactiviteiten om te laten zien in de agenda, omdat je ofwel niet verbonden bent aan vereniging, of omdat de verantwoordelijke van je vereniging je nog niet heeft ingedeeld/geactiveerd.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatssharedcontact': 
	text = "<span class='fa fa-info-circle'></span> Als je deze gebruiker contactpersoon maakt, zal hij/zij ook automatisch deze subgroep kunnen beheren vanuit zijn/haar persoonlijk " + appname + "-account.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsunsharedcontact': 
	var icon = this.id;
	var title = this.name;
	var url = $(this).attr("url_attr");
	text = "<span class='fa "+icon+"'></span> Shopitem actief<br>"+title+"<br>"+url+"<br><br><br><br>";
	break;
	
	case 'whatsextraattachment': 
	var document_name = this.id;
	text = "<span class='fa fa-info-circle'></span> Zoek naar een reeds toegevoegd bestand om te koppelen aan '" + document_name + "' of voeg een nieuw bestand toe door op 'Voeg extra bestand toe...' te klikken<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatseditnotifications': 
	text = "<span class='fa fa-info-circle'></span> Geef hier aan welke meldingen (het knopje <span class='fa fa-bell'></span>) je in de app wil ontvangen<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsselectmailchat': 
	text = "<span class='fa fa-info-circle'></span> Hieronder vind je een lijst van de verenigingen en scholen waaraan jij, je kind(eren) of je gekoppelde account(s) verbonden bent. Duid aan vanuit welke subgroepen je graag mails of berichtgeving over nieuwe chatberichten wil ontvangen.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'mailinque': 
	text = "<span class='fa fa-info-circle'></span> Deze mail staat in de wachtrij om verzonden te worden. Alle mails die verstuurd worden naar meer dan 50 gebruikers ineens zullen niet onmiddellijk verstuurd worden, maar worden in een wachtlijst gezet. Elk uur zal " + appname + " mails uit de wachtlijst uitsturen. Je kan de status van de verzending volgen in 'verzonden mails' (Premium functie).<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'mailinprogress': 
	text = "<span class='fa fa-info-circle'></span> Deze mail wordt momenteel verzonden.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'mailerror': 
	text =  "<span class='fa fa-info-circle'></span> Je mail is niet (correct) verzonden. Dit kan zijn omwille van:<br>1. Je hebt het verzendingsproces afgebroken tijdens het opstellen van de mail.<br>2. Er is een time-out opgetreden op de server<br>3. Er is een probleem met één of meerdere e-mailadressen van je leden<br><br>Indien dit probleem blijft bestaan, gelieve het " + appname + "-team te contacteren op info@" + appname + ".be<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'mailsuccess': 
	var time = this.id;
	text = "<span class='fa fa-info-circle'></span> Deze mail is succesvol verzonden op " + time + ".<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsmaxlimitreason': 
	text = "<span class='fa fa-info-circle'></span> Indien u dit veld invult, zal deze tekst aan de gebruikers in het agendapunt getoond worden. Indien u niets invult, komt er volgende tekst te staan: 'Er zijn maximum x deelnemers toegestaan voor deze activiteit'<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsmaxlimitreached': 
	text = "<span class='fa fa-info-circle'></span> Indien u dit veld invult, zal deze tekst aan de gebruikers in het agendapunt getoond worden, als het maximum aantal deelnemers bereikt is. Indien u niets invult, komt er volgende tekst te staan: 'Sorry, het maximum aantal deelnemers is bereikt voor deze activiteit.'<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsblockreason': 
	text = "<span class='fa fa-info-circle'></span> Indien u dit veld invult, zal deze tekst aan de gebruikers in het agendapunt getoond worden. Indien u niets invult, komt er volgende tekst te staan: 'Inschrijvingen voor deze activiteit zijn uitgeschakeld.'<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsblockRSVP': 
	text = "<span class='fa fa-info-circle'></span> Gebruik deze functie om inschrijvingen (leden die hun aanwezigheid bevestigen) op deze activiteit uit te schakelen. ENKEL je leden kunnen de inschrijfknop niet meer gebruiken. Je kan wel nog manueel afwezigheden opnemen!";
	break;
	
	case 'whatsblockRSVPusers': 
	text = "<span class='fa fa-info-circle'></span> Je vereniging heeft inschrijvingen voor deze activiteit uitgeschakeld.<br>Contacteer je vereniging als je je aanwezigheid nog wil aanpassen.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatslimitRSVP': 
	text =  "<span class='fa fa-info-circle'></span> Gebruik deze functie om een limiet op het aantal aanwezigheden op deze activiteit in te stellen.";
	break;
	
	case 'WhatsWeather': 
	text = "<span class='fa fa-info-circle'></span> Selecteer deze optie om het weer (beschrijving, temperatuur, wind en windrichting) op de dag van de activiteit weer te geven. Het weer wordt automatisch weergegeven binnen de 7 dagen in de toekomst én als er een locatie (postcode en/of stad) is ingegeven in het veld 'locatie van activiteit'.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'WhatsNotes': 
	text = "<span class='fa fa-info-circle'></span> Vul hier nog extra informatie in, die van toepassing is op deze gebruiker. Dit veld is <u>niet</u> zichtbaar voor de gebruiker zelf.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsshare': 
	text =  "<span class='fa fa-info-circle'></span> Duid deze optie aan als andere gebruikers jouw telefoonnummer mogen zien als je hebt aangegeven dat je naar een activiteit van je vereniging gaat. Je e-mailadres is niet zichtbaar, maar andere gebruikers kunnen jou wel mailen via " + appname + ".<br>Disclaimer: Jouw vereniging(en) zullen <u>altijd</u> jouw administratieve gegevens kunnen raadplegen, zoals beschreven in de privacyverklaring.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatstestmode': 
	text = "<span class='fa fa-info-circle'></span> " + appname + "-testmodus: Je leden zullen geen agendapunten, contactgegevens, automatische mails, notificaties en toegevoegde bestanden meer zien totdat je de testmodus desactiveert. Je kan wel nog manueel opgestelde mails verzenden.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatstestmodeforusers': 
	var superusername = this.id;
	text = "<span class='fa fa-info-circle'></span> " + superusername + " is even achter de schermen aan het werken. Je ziet hier daardoor tijdelijk geen agendapunten meer. Geen nood. Zodra " + superusername + " klaar is met dit 'groot onderhoud', is alles weer zoals vanouds. Maar dan beter uiteraard.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsgezinsoverzicht': 
	text =  "<span class='fa fa-info-circle'></span> Dit is een overzicht van alle toegevoegde profielen op 1 pagina. Je kan dit overzicht standaard tonen bij inladen van de app. Klik hiervoor op 'Bewerk' onder 'Gezinsoverzicht'.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsresultscalendarsearch': 
	text ="<span class='fa fa-info-circle'></span> Deze zoekfunctie toont de 15 meest recente zoekresultaten.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	
	case 'WhatsChatOverview': 
	text = "<span class='fa fa-info-circle'></span> Op deze pagina kan je met je vereniging chatten, per subgroep waaraan je bent aangesloten. Ook zie je een overzicht van alle chatberichten die in de kalender zijn gepost.";
	break;
	
	case 'whatswaittobeadded': 
	var superusername = this.id;
	text = "<span class='fa fa-info-circle'></span> Goed nieuws! Je " + superusername + " is lid van " + appname + ". De verantwoordelijke van je " + superusername + " is automatisch ingelicht dat jij ook een " + appname + "-account hebt. Wacht even tot de verantwoordelijke je indeelt in de juiste categorie of subgroep van je " + superusername + ". Vanaf dan kan je de agendapunten en documenten in je " + appname + "-account zien.<br><br>Klik op deze melding om ze te verbergen.";
	break;
	
	case 'whatsremoveaccount': 
	text = "<span class='fa fa-info-circle'></span> Wil je je " + appname + "-account verwijderen, dan kan dat hier, stap voor stap.";
	break;
	
	
	}
	

	
	notif({
	  msg: text,
	  position: "center",
	  type: "info",
	  multiline: true,
	  width: 300,
	  autohide: false
	});

});

function NotifError(x) {
	event.preventDefault();
	var errormessage = x;
	if(errormessage == "fillallfields"){
		errormessage = "Gelieve alle verplichte velden in te vullen";
	}
	if(errormessage == "mailmembers"){
		errormessage = "Gelieve eerst de gebruikers te selecteren die je wil e-mailen.";
	}
	if(errormessage == "emptyrecipients"){
		errormessage = "Gelieve tenminste 1 gebruiker te selecteren die je wil e-mailen.";
	}
	if(errormessage == "exportmembers"){
		errormessage = "Gelieve eerst de gebruikers te selecteren die je wil exporteren.";
	}
	if(errormessage == "movecopymembers"){
		errormessage = "Gelieve eerst de gebruikers te selecteren die je wil verplaatsen of kopiëren.";
	}
	if(errormessage == "addmembers"){
		errormessage = "Gelieve minimum 1 subgroep te selecteren.";
	}
	if(errormessage == "textmembers"){
		errormessage = "Gelieve eerst de gebruikers te selecteren waarnaar je een sms wil sturen.";
	}
	if(errormessage == "markmembers"){
		errormessage = "Gelieve eerst de gebruikers te selecteren die je wil labelen.";
	}
	if(errormessage == "unmarkmembers"){
		errormessage = "Gelieve eerst de gebruikers te selecteren waarbij je het label wil opheffen.";
	}
	if(errormessage == "movemembers"){
		errormessage = "Gelieve eerst de gebruikers te selecteren die je wil verplaatsen.";
	}
	if(errormessage == "copymembers"){
		errormessage = "Gelieve eerst de gebruikers te selecteren die je wil kopiëren.";
	}
	if(errormessage == "minimumoptions"){
		errormessage = "Gelieve minimum 1 optie aan te duiden!";
	}
	if(errormessage == "minimumvalue"){
		errormessage = "Bedrag moet hoger zijn dan 1 euro!";
	}
	if(errormessage == "validpostal"){
		errormessage = "Gelieve een geldige postcode in te vullen.";
	}
	if(errormessage == "morethanonesubgroup"){
		errormessage = "Om gebruikers te verplaatsen of kopiëren mag je niet meer dan 1 subgroep tegelijk aanduiden. Werk subgroep per subgroep.";
	}
	if(errormessage == "notthesamesubgroup"){
		errormessage = "Je kan gebruikers niet verplaatsen of kopiëren naar hun eigen subgroep.";
	}
	if(errormessage == "toomanyfiles"){
		errormessage = "Je hebt teveel foto's ineens geselecteerd. Gelieve opnieuw te proberen.";
	}
	if(errormessage == "onlynumbers"){
		errormessage = "Gelieve enkel cijfers te gebruiken.";
	}
	if(errormessage == "validurl"){
		errormessage = "Gelieve een juist webadres in te vullen volgens de gegeven richtlijnen.";
	}
	if(errormessage == "selectblock"){
		errormessage = "Gelieve minimaal 1 agendablok te selecteren.";
	}
	if(errormessage == "falseQR"){
		errormessage = "Sorry, geen QR-code gevonden. Gelieve opnieuw te proberen.";
	}
	if(errormessage == "falseQR2"){
		errormessage = "Sorry, deze QR-code is niet gekoppeld aan een " + appname + "-lid. Gelieve opnieuw te proberen.";
	}
	if(errormessage == "NoMemberQR"){
		errormessage = "Sorry, Deze persoon is geen lid van je vereniging. Registratie is mislukt. Gelieve opnieuw te proberen.";
	}
	if(errormessage == "emptymessage"){
		errormessage = "Je kan geen leeg chatbericht versturen.";
	}
	if(errormessage == "ageconfirm"){
		errormessage = "Gelieve aan te geven of je 18 jaar bent of toestemming hebt";
	}
	if(errormessage == "catname"){
		errormessage = "Gelieve een naam op te geven voor de subgroep";
	}
	if(errormessage == "catage"){
		errormessage = "Gelieve aan te geven voor welke leeftijdsgroep deze subgroep bedoeld is";
	}
	if(errormessage == "max_limit"){
		errormessage = "Het maximum aantal deelnemers mag niet 0 zijn.";
	}
	if(errormessage == "impositive"){
		errormessage = "Gelieve eerst aan te duiden dat je zeker bent!";
	}
	if(errormessage == "firstsubmitoption"){
		errormessage = "Je hebt nog een optie ingevuld die je moet bevestigen: klik op 'Voeg Toe' achter de optie.";
	}
	
notif({
  msg: "<span class='fa fa-exclamation-triangle'></span> " + errormessage,
  position: "center",
  type: "error",
  multiline: true,
  width: 300,
  autohide: true,
  timeout: 4000
});
};








