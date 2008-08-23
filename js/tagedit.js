var urlbase = '';

$(document).ready(function() {
                    $(".fusionbox").autocomplete(urlbase + "tagcomplete.php");
                    $('.tagentry').each(fkPrepare);

                    $('a.edit').click(
                      function(event) {
                        event.preventDefault();
                        // first parent is a <p>
                        $(this).parent().parent("li").find(".tagcommands").show();
                        $(this).hide();
                      });

                    $("a.restags").click(
                      function(event){
                        event.preventDefault();
                        $("li.res").show();
                        $("li.nores").hide();
                      });
                    $("a.norestags").click(
                      function(event){
                        event.preventDefault();
                        $("li.nores").show();
                        $("li.res").hide();
                      });
                    $("a.seealltags").click(
                      function(event){
                        event.preventDefault();
                        $("li.res").show();
                        $("li.nores").show();
                      });
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
}

