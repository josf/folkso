var urlbase = '';

$(document).ready(function() {
                    $(".fusionbox").autocomplete(urlbase + "tagcomplete.php");
                    $('.tagentry').each(fkPrepare);
                  });


function fkPrepare(selector) {
  // tagid is 'tagid' + number
  var thisid = $(this).attr('id');
  var tagid = thisid.substring(5);

  $(this).find("button.delete").click(
    function(event) {
      event.preventDefault();
      $.post(urlbase + 'tag.php',
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
