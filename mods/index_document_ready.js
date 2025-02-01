$('#tableSelectBox').append("<option disabled>---------------------------------------</option>");
$('#tableSelectBox').append("<option value='logout'><b>Abmelden</b></option>");

$( "#tableSelectBox" ).on( "change", function() {
    if($( this ).val() == 'logout'){
      window.location.href = "ypum/yfront/login.php";
    }
  });


