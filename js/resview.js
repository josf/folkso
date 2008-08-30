if (! Document.hasOwnProperty("folksonomie")) {
  Document.folksonomie = new Object();
}

var getresphp = Document.folksonomie.getbase + 'resource.php';
var postresphp = Document.folksonomie.postbase + 'resource.php';


$(document).ready(
  function(){
    alert("ready");
    $("a.editresource").click(
      function(event) {
        event.preventDefault();
        alert("soemthing");
        var lis = $(this).parent().parent("li");
        var resid = lis.attr("id").substring(3);
        lis.append(editBox(resid, lis));
      });

  });

function editBox(resid, lis){
  var box = $("<div class='editbox'>");
  box.append($("<div class=\"tagger\">"
               + "<input type='text' class='tagbox' length='20'>"
               + "</input>"
               + " <span class=\"infohead\">"
               + "Meta</span>"
               + "<select class=\"metatagbox\" size=\"1\">"
               + "</select>"
               + "<a class=\"tagbutton\" href=\"#\">Valider</a>"
               + "</div>"));
  return box;
}