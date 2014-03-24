var latt,lont,altt,epoch_g;
var message_buffer="";

$(function() {

    // Do an automatic check-in to test credentials and log coordinates without a mesage 
    if (navigator.geolocation) {
	navigator.geolocation.getCurrentPosition(initPositionProbe);
    }
    else {
	$("#response").addClass("alert alert-danger");
	$("#response").html("Geolocation is not supported or enabled");
    }
       
    // Enables the user to submit status by pressing down the Space key 3 times in a row 
    // TODO: implement cellphone longpress unicode triggers
    var spaces = 0;
    $("#status-textarea").keydown(function( event ) {

	if ( event.which == 32 ) {
	    //event.preventDefault();
	    spaces++;
	    if (spaces == 3){
		spaces = 0;
		submit_status();
		$("#status-textarea").addClass("textarea-pending");
	    }
	}
	else{
	    spaces = 0;
	}
	
    });

    $("#submiter").click(submit_status);

});

// Try to submit a GPS tagged message to the server with credentials 
function submit_status(){

    if (message_buffer === ""){
	message_buffer = $("#status-textarea").val();
    }

    $("#status-textarea").val("");
    $('#status-textarea').prop('disabled', true);
    $("#status-textarea").attr("placeholder","Sending . . .");

    $.ajax({
	url: "cgi/status-update.php",
	type: "POST",
	cache:false,

	data: {
	    password: $("#pin-input").val(),
	    message: message_buffer,
	    lat: latt,
	    alt: altt,
	    lon: lont,
	    date: epoch_g},
	
	success:response_handler
    });
}


function updatePosition(){
    navigator.geolocation.getCurrentPosition(initPositionProbe);
}

// General getCurrentPosition callback that updates and displays the coordinates
function showPosition(position) {

    var epoch = parseInt(position.timestamp/1000);

    var date = new Date(position.timestamp);
    $("#epoch").text(date.toDateString() + " " + date.toLocaleTimeString());
    $("#latitude").text( DECtoDMS_string(position.coords.latitude,"lat"));
    $("#longitude").text( DECtoDMS_string(position.coords.longitude,"lon"));

    if (position.coords.altitude)
	$("#altitude").html(Math.round(position.coords.altitude)+" m");
    else
	$("#altitude").html("<small><tt>unknown</tt></small>");

    // Update the coordinate global vars
    latt = position.coords.latitude;
    altt = position.coords.altitude;
    lont = position.coords.longitude;
    epoch_g = epoch;
}


// GPS getCurrentPosition callback for the initial page load 
//  database status messages are identified with '#probe'
function initPositionProbe(position) {
    $.ajax({
	url: "cgi/status-update.php",
	type: "POST",
	cache:false,
	
	data: {
	    password: $("#pin-input").val(),
	    message: "#probe",
	    lat: position.coords.latitude,
	    alt: position.coords.altitude,
	    lon: position.coords.longitude,
	    date: parseInt(position.timestamp/1000)},
	
	success: function(response){
	    // Same as function response_handler with PIN focus removed

	    for (index = 0; index < response.log.length; ++index) {
		console.log(response.log[index]);
	    }

	    $("#status-textarea").focus();

	    if ( response.success ) {
		$("#status-textarea").focus();
		
		$("#creds").removeClass("has-error");
		$("#creds").addClass("has-success");
	    }
	    else {
		if ( response.error ) {
		    $("#response").addClass("alert alert-danger");
		    $("#response").html(response.message);
		}
		
		if ( response.validated ) {
		    navigator.geolocation.getCurrentPosition(showPosition);
		    $("#pin-input").attr("placeholder","PIN");
		    $("#creds").removeClass("has-error");
		    $("#creds").addClass("has-success");
		}
		else{
		    // $("#pin-input").focus();

		    $("#pin-input").attr("placeholder","PIN needed");	
		    $("#creds").addClass("has-error");
		    $("#creds").removeClass("has-success");
		}
	    }

	}
	    
    });

    showPosition(position);
}

// Parse the response json 
function response_handler(response){

    for (index = 0; index < response.log.length; ++index) 
	console.log(response.log[index]);
    
    if ( response.success ) {
	$("#status-textarea").attr("placeholder","Success . . .");
	$("#status-textarea").attr("disabled", false);
	message_buffer = "";

	$("#status-textarea").val("");	    
	$("#status-textarea").focus();

	$("#creds").removeClass("has-error");
	$("#creds").addClass("has-success");
	$("#status-textarea").removeClass("textarea-error textarea-pending");

    }
    else {

	$("#status-textarea").addClass("textarea-error");
	$("#status-textarea").removeClass("textarea-pending");
	
	if ( response.error ) {
	    $("#response").addClass("alert alert-danger");
	    $("#response").html(response.message);
	}


	if ( response.validated ) {
	    navigator.geolocation.getCurrentPosition(showPosition);
	    $("#pin-input").attr("placeholder","PIN");
	    $("#creds").removeClass("has-error");
	    $("#creds").addClass("has-success");

	}
	else{
	    $("#pin-input").focus();
	    $("#pin-input").val("");
	    $("#pin-input").attr("placeholder","PIN needed");	
	    $("#status-textarea").attr("placeholder","PIN needed");
	    $("#creds").addClass("has-error");
	    $("#creds").removeClass("has-success");
	}
    }
    
}


function DECtoDMS_string(decimal, coordinate){
    if (decimal == 0)
	return "0° 0′ 0″";
    
    var positivity = decimal > 0;
    var compass = "";
    if (coordinate == "lat")
	compass = positivity ? "N" : "S";
    if (coordinate == "lon")
	compass = positivity ? "E" : "W";

    decimal = Math.abs(decimal);

    var degrees = Math.floor(decimal);
    var float_minutes = (decimal % 1) * 60 ; 
    var minutes = Math.floor(float_minutes);
    var seconds = Math.round(((float_minutes % 1) * 60) * 100) / 100;
    
    return  degrees + "° " + minutes + "′ " + seconds + "″ "+ compass ;
}

