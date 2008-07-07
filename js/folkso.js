

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
}

function modifyTagPrep(itemjq) {
  var thisid = itemjq.attr('id');
  var tagid = thisid.substring(5);

  var sourcetag = itemjq.find("input[type=text]");

  itemjq.find("button.delete").click(
    function(event) {
      event.preventDefault();
    }


}


