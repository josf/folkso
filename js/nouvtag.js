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

  action_col.append(fk.initialize_tagbox(box, url, title));
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
    + "<p class=\"curr_tags\"></p>"
    + "<p>Tag: "
    + "<input type=\"text\" class=\"maintag\" length=\"50\"></input> "
    + "</p>"
    + "<p>Métatag: "
    + "<input type=\"text\" length=\"40\" class=\"meta\"></input>"
    + "</p>"
    + "<a href=\"#\" class=\"validate_taggage\">Valider</a>"
    + "<p><a href=\"#\" class=\"tagbox_fermer\">Fermer</a></p>"
    + "</div>"
  );
};

/**
 * Get tags for the given resource and insert them into the DOM.
 *
 * @param res String resource url
 * @param insert $() jQuery DOM object. The place where the resulting
 * tag string will be appended.
 * @param tag_fmt Function to be called on each xml tag result
 */
fk.insert_current_tags = function(res, insert, tag_fmt){
  $.ajax({
           url: '/resource.php',
           type: 'get',
           datatype: 'xml',
           data: {
             folksores: res,
             folksodatatype: 'xml'
           },
           error: function(xhr, msg){
             console.log(xhr.status + " " + msg + " " + xhr.statusText);
           },
           success: function(answer){
             var xmltags = $(answer).find("tag");
             for (var i = 0; i < xmltags.length; ++i){
               insert.append(
                 tag_fmt(
                   res,
                   $(xmltags[i]).find("display").text(),
                   $(xmltags[i]).find("numid").text(),
                   $(xmltags[i]).find("metatag").text()
                 )
               );
             }
           }
         });
};


fk.make_currtag_unit = function (res, tagdisp, tagid, meta){
  var metapart = "";
  if ((meta.length) &&
      meta != "normal") {
        metapart = "(" + meta + ")";
  }

  var h = $("<span class=\"a_tag\">"
           + tagdisp
           + metapart
           + "<a class=\"tag_del_button\" href=\"#\">"
           + "<span class=\"tag_del_button_text\">Suppr.</span>"
           + "</a>"
           + "</span>");

  h.find("a").click(
    function(ev){
      ev.preventDefault();
      $.ajax({
               url: '/resource.php',
               type: 'post',
               datatype: 'text/text',
               data: {
                 folksores: res,
                 folksotag: tagid,
                 folksodelete: 1
               },
               error: function(xhr, msg){
                 console.log(xhr.status + " " + msg);
               },
               success: function (data){
                 h.remove();
               }
      });
    }
  );
  return h;
};


/**
 * @param box jQuery tagbox elem
 */
fk.initialize_tagbox = function(box, url, title){
  /**  Fermer **/
  box.find("a.tagbox_fermer").click(
    function(ev){
      ev.preventDefault();
      box.remove();
    });

  var tagput = box.find("input.maintag");
  var metaput = box.find("input.meta");

  /** immediately report resource to system (might be first time)**/
  $.ajax({
           url: '/resource.php',
           type: 'post',
           datatype: 'text/text',
           data: {
             folksores: url,
             folksonewresource: 1,
             folksonewtitle: title
           },
           error: function(xhr, msg){
             alert(xhr.statusText + xhr.status);
           }
         });

  fk.insert_current_tags(url,
                         box.find("p.curr_tags"),
                         fk.make_currtag_unit);

  /** setup autocomplete (note that we have to modify jquery.autocomplete
   * to add "folkso" in front of "q" header)
   **/
  tagput.autocomplete("http://localhost/tagcomplete.php");
  metaput.autocomplete("/metatag.php");

  box.find("a.validate_taggage").click(
    function(ev){
      ev.preventDefault();
      if (tagput.val().length > 1){
        $.ajax({
                 url: '/resource.php',
                 type: 'post',
                 datatype: 'text/text',
                 data: {
                   folksores: url,
                   folksotag: tagput.val(), // TODO: add meta tag
                   folksometa: metaput.val()
                 },
                 error: function(xhr, msg){
                   console.log(xhr.status + " " + msg + " " + xhr.statusText);
                 },
                 success: function(){
                   console.log("success with " + tagput.val());
                   var old = box.find("p.curr_tags").text();
                   if (old) {
                     old = old + " ; ";
                   }
                   box.find("p.curr_tags").text(old + " " + tagput.val());
                   tagput.val("");
                 }
               });
        }
    });


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
                    /** avoid creating multiple boxes on multiple clicks**/
                    if (action.find(".tagbox").length == 0){
                    activate_fn(tr);
                    }
                  });

  action.append(tagbutton);
};



/** Build, open tag box **/

/** Send new tag info **/