/** Find nouvelles to tag: find all nouvelles,
 * eliminate non valid ones, get resource id **/

var fk = fk || {};

fk.row_filter = function(row) {
  var tr =$(row);
  if (tr.find("td").length != 4) {
    return false;
  }
    if($(tr.find("td")[3]).text().search(/valid/) != -1) {
    return false;
  }
  return true;
};


 /** Activate click mechanism
  *
 * The idea here it to do as little as possible until the user clicks
 * on the tagging link. Only then do we gather up all the relevant
 * information and set up the tagging environment.
 *
 * @param tr $("tr")
 *
  **/
fk.prep_tagbutton = function (tr){
  var titre_col = $(tr.find("td")[2]);
  var action_col = $(tr.find("td")[3]);

  var title = titre_col.find("a").text();
  var url = titre_col.find("a").attr("href");

  console.log("found " + title + " and " + url);
  var box = fk.tagbox_setup(title, url);

  $("body").append(fk.initialize_tagbox(box));
};

/**
 * Build the initial html. No actions included yet.
 *
 * @returns jQuery object
 */
fk.tagbox_setup = function (title, url) {
  return $(
    "<div class=\"tagbox\">"
    + "<p class=\"tag_title\">Tagger: <em>"
    + title
    + "</em></p>"
    + "<p>"
    + url
    + "</p>"
    + "<input type=\"text\" length=\"50\"></input> "
    + "<a href=\"#\" class=\"validate_taggage\">Valider</a>"
    + "<p><a href=\"#\" class=\"tagbox_fermer\">Fermer</a></p>"
  );
};

/**
 * @param box jQuery tagbox elem
 */
fk.initialize_tagbox = function(box){
  /**  Fermer **/
  box.find("a.tagbox_fermer").click(
    function(ev){
      ev.preventDefault();
      box.remove();
    }
  );

  


  return box;
};


$(document).ready(
  function(){

    var rows = jQuery.grep($("tr"),
                           fk.row_filter
                          );
    console.log("found " + rows.length + " valid rows");

    /** slice is to avoid 1st row (headers) **/
    $(rows.slice(1)).each(function() {
                            var rw = this;
                            fk.insert_taglink.call(rw, fk.prep_tagbutton);
                          });

});


/**
 * Inserts [tagger] link in each Action
 */
fk.insert_taglink = function(activate_fn) {
  var tr = $(this);
  var action = $(tr.find("td")[3]);
  var tagbutton = $("<a href=\"#\">[tagger]</a>");

  tagbutton.click(function(ev) {
                    ev.preventDefault();
                    activate_fn(tr);
                  });

  action.append(tagbutton);
};



/** Build, open tag box **/

/** Send new tag info **/