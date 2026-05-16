function annulerquestion() {
    var radioButtons = document.querySelectorAll('input[type="radio"]');
        
        radioButtons.forEach(function(button) {
            button.checked = false;
        });
    
    
}

var adresseEmail =document.getElementById("user");
var chaine =document.getElementById("pass");

function test(adresseEmail,chaine) {
    
    var pattern = /^[a-zA-Z0-9]{3,}@([a-zA-Z0-9]{3,}\.){1,}[a-zA-Z]{2,4}$/;
    var patterno = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6}$/;
    var htmlPageUrl = "statistiques.html";

   

    
    if (pattern.test(adresseEmail)==false && patterno.test(chaine)==false) {
alert('error pattern email')
    
    }
    else {window.location.href = htmlPageUrl;}
    

    
    
}
