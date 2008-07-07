

$(document).ready(function() {
                    $('.tagentry').each(fkPrepare);
                  });

function fkPrepare(selector) {
  // tagid is 'tagid' + number
  var thisid = $(this).attr('id');
  var tagid = thisid.substring(5);

  $(this).find("button.delete").click(
    function(event) {
      event.preventDefault();
      $.post('http://localhost/tag.php',
             {folksotag: tagid,
              folksodelete: ''},
             function() {
               $("#" + thisid).hide("slow");
               $("#" + thisid).remove();
             });
    });
  modifyTagPrep($(this));
  mergeTagPrep($(this));
}

function modifyTagPrep(itemjq) {
  var thisid = itemjq.attr('id');
  var tagid = thisid.substring(5);

  var modform = itemjq.find("form.rename");
  var modbutton = modform.find("input[type=submit]");
  var modtextInput = modform.find("input[type=text]");
  modtextInput.val(itemjq.find("a.tagname").text());

  modbutton.click(
    function(event) {
      event.preventDefault();
      var newname = modtextInput.val();

      //validate the modification
      if ((newname == itemjq.find("a.tagname").text()) ||
        (modtextInput.val().trim == '')){
          alert("Pas de modification.");
          return;
      }
      //then make that change
      $.post('http://localhost/tag.php',
             {folksotag: tagid,
              folksonewname: modtextInput.val()},
             function() {
               itemjq.find("a.tagname").text(newname);
             });

    });
}

function mergeTagPrep(itemjq) {
  var thisid = itemjq.attr('id');
  var tagid = thisid.substring(5);

  // text to put in textbox before user modification
  var emptymergeText = "Un autre tag";

  var mergeform = itemjq.find("form.merge");
  var mergetextInput = mergeform.find("input[type=text]");
  mergetextInput.val(emptymergeText);
  mergetextInput.focus(function() {
                        $(this).val('');
                      });

  mergeform.find("button[type=submit]").click(
    function(event) {
      event.preventDefault();
      var othertag = mergetextInput.val();

      if (othertag == emptymergeText) {
        alert("You must enter the name or id of the tag you wish to merge with.");
        return;
      }

      $.post('http://localhost/tag.php',
             {folksotag: tagid,
              folksotarget: othertag},
                function () {
                  itemjq.hide();
                  itemjq.remove();
                });
    });
}


