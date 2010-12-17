/*
 * Copyright (c) 2010 Joseph Fahey
 * GNU Public Licence.
 *
 * Common lib for folksonomie related javascript
 *
 * Requires jQuery
 *
 * These functions should eventually be used by all the folksonomie js
 *  as a common way for setting up interactive pages.
 *
 */


(function() {
     var fK = window.fK = {

         /**
          * Setup all the other functions based on user input
          *
          * config should look like:
          *
          * { simpleResTemplate: selector,
          *   simpleTagTemplate: selector,
          *   getTagUrl: url,
          *   getResUrl: url,
          *   postTagUrl: url,
          *   postResUrl: url,
          *   userLevel: number // user is just user (1), or redacteur(2), or admin?
          *  }
          *
          * * Templates
          *
          * Templates must be included in the html page itself, in the form
          * of <script> elements having an unknown type (ie. not text/javascript)
          * and an id that will allow us to retreive them. There must be an individual
          * <script> element for each template.
          *
          * When the templates are used, they are wrapped into a containing element,
          * a list item for example, designated by a config item (name of template
          * followed by "Wrap": simpleTagWrap for example). Defaults are provided
          * in the templating functions. The config item should be something that can
          * be plugged into a jQuery $(), such as $("<li class=\"tagitem">).
          *
          * * Templates and events
          *
          * Templates should have elements with appropriate classes that will be
          * automatically associated with the appropriate events.
          *
          */
         init: function(config) {
             jQuery.ajaxSettings.traditional = true;
             for (var prop in config) {

                 // here we check for jQuery selectors (yes, this is
                 // crude) and activate them
                 var reg = /^#/;
                 if (fK.attrs[prop]) {
                     if (reg.test(config[prop])) {
                         fK.cf[prop] = $(config[prop]);
                     } else {
                         fK.cf[prop] = config[prop];
                     }
                 }
             }
         },
         /**
          * Different scripts may need to set this variable. True of false.
          */
         loginStatus: false,
         /**
          * An array of functions to be run when the user is logged in
          * (ie. loginStatus goes from false to true. These will _not_
          *  be run when the user is logged in from the beginning.
          */
         onLoggedIn: [],
         attrs: {simpleResTemplate: 'jQuery id of the template element',
                 simpleTagTemplate: 'jQuery id of the template element',
                 getTagUrl: 'url', getResUrl: 'url',
                 postTagUrl: 'url', postResUrl: 'userLevel',
                 getUserUrl: 'url', autocompleteUrl: 'url',
                 getAdminUrl: 'url', postAdminUrl: 'url',
                 simpleTagWrap: 'html string for element creation',
                 simpleResWrap: 'html string for element creation',
                 oIdLogoPath: 'string or empty string: path to logos',
                 oIdPath: 'string or empty string: path for Open Id ops'
                },

         /**
          * All your events can go here. For simple namespacing, there
          * could be children of fK.events, but that probably won't be
          * necessary.
          */
         events: {},

         /**
          * Config information set on init.
          *
          * Other scripts could use this for their data, maybe.
          */
         cf: {
             /**
              * Maximum number of tags to show in a tag list before proposing to s
              * scroll.
              */
             tagListMax: 10
         },

         /**
          * The ufn "namespace" is for functions to be defined in other scripts,
          * ie. page specific functions that override some of functions in the
          * fK.fn "namespace".
          *
          * Be warned: if you put anything beside functions in this namespace,
          * there will be trouble. Functions will test for existence of functions
          * here, but will not check to see if the variables are indeed functions.
          * So just put functions here, nothing else.
          *
          * The only exception is the "hooks" sub-object, which should contain
          * only functions.
          */
         ufn: {
             hooks: {}
         },

         /**
          * Controller for a given part of the DOM. Ajax requests update the 
          * data in the controller object and it updates the DOM.
          */
         Ktl: function (jQplace, dataOb) {
             var 
             data = this.data = dataOb, fields = this.fields =  {},
             $place = this.$place = jQplace;


             /**
              * 
              */
             this.addControl = function(type, name, props) {
                 if (fields[name]) {
                     throw new Error("field " + name + " already exists");
                 } 
                 else {
                     /**
                      * remove is an internal flag, true or false, deleteElem is 
                      * the function that does the deleting.
                      * 
                      * removeVal is the list counterpart of remove, but takes a value 
                      * for matching against existing values.
                      */
                     var requireds = ["value", "newval", "selector", 
                                      "init", "update", "deleteElem", "remove", "removeVal",
                                      "element"];
                     for (var i = 0; i < requireds.length; i++) {
                         if (props[requireds[i]] === undefined) {
                             props[requireds[i]] = null;
                         }
                     }
                     props.type = type; // must be either basic or list
                     if (props.type == 'list') {
                         props.value = [];
                         props.appendval = [];
                         props.removeVal = [];
                         props.updateVals = [];
                     }

                     /*
                      * We redefine init to be closed over the "initialized" 
                      * flag, allowing us to check on whether the element has been
                      * initialized or not.
                      */
                     props.initialized = false;
                     if ($.isFunction(props.init)) {
                         var oldinit = props.init;
                         props.init = function() {
                             var args = Array.prototype.slice.call(arguments, 0);
                             props.initialized = true;
                             oldinit.apply(this, args);
                         };
                     }

                     fields[name] = props;
                 }
             };


             this.addBasic = function(name, props) {
               this.addControl('basic', name, props);  
             };

             this.addList = function(name, props) {
                 this.addControl('list', name, props);
             };

             /**
              * @return Function for updating field value
              * 
              * Writes over previous value. Use only for basic elements.
              */
             this.setfield = function(fieldname) {
                 if (fields[fieldname]) {
                     return function(value) {
                         fields[fieldname].newval = value;
                     };
                 }
                 else {
                     alert("Undefined Kontroler field: " + fieldname);
                 }
                 return false;
             };

             /**
              * @param fieldname String 
              * @param data Optional 
              * 
              * "data" is the data argument to be matched to determine deletion 
              * from list. Only applicable for list elements.
              */
             this.deletefield = function(fieldname) {
                 if (fields[fieldname]) {
                     with (fields[fieldname]) {
                     if (type == 'basic') {
                         return function() {
                             remove = true;
                         };
                     }
                         else { // list type
                             return function(data) {
                               removeVal.push(data);
                             };
                         }
                     }
                 }
                 else {
                     throw new Error("Undeclared field");
                 }
             };


             this.appendField = function(fieldname) {
                 if (fields[fieldname] && fields[fieldname].type == 'list') {
                     return function (newthing) {
                         fields[fieldname].newval = null;
                         fields[fieldname].appendval.push(newthing);
                     };
                 }
                 else {
                     alert("Cannot append: undefined Kontroler field or " 
                           + "field is not a list: " + fieldname);
                 }
                 return false;
             },


             this.updateField = function(fieldname) {
                 if (fields[fieldname] && fields[fieldname].type == 'list') {
                     return function(oldthing, newthing) {
                         fields[fieldname].updateVals.push({oldthing: oldthing,
                                                            newthing: newthing});
                     };
                 }
                 else {
                     throw new Error("Bad fieldname (" + 
                                     fieldname + 
                                     ") or else this is not a list field");
                 }
             },

             this.restartList = function(fieldname) {
                 if (fields[fieldname] && fields[fieldname].type == 'list') {
                     return function() {
                       with( fields[fieldname] ) {
                           $(selector, $place).children().remove();
                           value = [];
                       }
                     };
                 }
                 else {
                     throw new Error("restartList does not work with non list fields");
                 }
             },


             this.updateAllNew = function() {
                 for (var fieldname in fields) {
                     with(fields[fieldname]) {
                         if (type == 'basic') {
                             if (newval && newval !== value) {
                                 value = newval; newval = null;
                                 if (initialized) {
                                     update(selector, $place, value);
                                 }
                                 else {
                                     init(selector, $place, value);
                                 }
                             }
                             else if (remove) {
                                 deleteElem(selector, $place, value);
                             }
                         }
                         else { // type is therefore 'list'
                             if (appendval.length > 0) {
                                 $.each(appendval,
                                        function(index, thing) {
                                            value.push(thing);
                                            $(selector, $place)
                                                .append(init(selector, $place, thing));
                                        });
                                 appendval = [];
                             }
                             
                             if (removeVal.length > 0){
                                 // remove from values first
                                 $.each(removeVal,
                                        function(idx, thing) {
                                            value = $.grep(value, match(thing), true); // true means: invert
                                        });

                                 // then remove and rebuild list from values
                                 $(selector, $place).children().remove();
                                 $.each(value, 
                                        function(index, val) {
                                            $(selector, $place)
                                                .append(init(selector, $place, val));
                                        });
                                 removeVal = [];
                             }

                             if (updateVals.length > 0) {
                                 $.each(updateVals,
                                        function(idx, ob) {
                                            value = $.map(value, 
                                                          function (val, i) {
                                                              var matcher = match(ob.oldthing);
                                                              if (matcher(val)) {
                                                                  return ob.newthing;
                                                              }
                                                              return val;
                                                          });
                                                });

                                 $(selector, $place).children().remove();
                                 $.each(value, 
                                        function(index, val) {
                                            $(selector, $place)
                                                .append(init(selector, $place, val));
                                        });
                                 updateVals = [];
                             }
                         }
                     }
                 }
             };
             $(this).bind("update", this.updateAllNew);
         },


         /**
          * @target {jQuery} Element that the new element will be appended to
          */
         simpleres: function(target, display, url, id)
         {
             var resdata = { display: display,
                             url: url,
                             id: id};
             var wrapper = $(fK.cf.simpleResWrap || "<div class=\"ares\">");
             fK.cf.simpleResTemplate
                 .jqote(resdata)
                 .appendTo(wrapper);

             var dropresfn = fK.ufn.dropres_react ?
                 fK.ufn.dropres_react(resdata) : fK.fn.dropres_react(resdata);

             wrapper.data("fKres", resdata);
             wrapper.find("a.dropres").click(dropresfn);

             //   item.data(resdata);
             //             target.append(item);
             // setup commands here (event listeners assigned to selectors)
             target.append(wrapper);
         },
         /**
          * Classes and actions:
          *
          * ul.tag_resources => list of resources (filled via ajax)
          * a.droptag => erase this tag (from this users tags)
          * a.expandtag => see the resources (via ajax)
          * a.hidereslist => hide the resources
          *
          * @target {jQuery} Element that the new element will be appended to
          */
         simpletag: function(target, display, tagnorm, id)
         {
             var
             tagdata = { display: display,
                         tagnorm: tagnorm,
                         id: id},
             newtag = fK.cf.simpleTagTemplate.jqote(tagdata),
             wrapper = $(fK.cf.tagwrap || "<div class=\"atag\">");

             // we keep a link back to what will be the DOM element
             tagdata.element = wrapper;

             newtag.appendTo(wrapper);
             wrapper.data("fKtag", tagdata);
             var resUL = $(".tag_resources", wrapper);

             var
             droptagfn = fK.fn.droptag_react(tagdata),
             expandtagfn = fK.fn.expandtag_react(tagdata, resUL);

             $("a.droptag", wrapper).click(droptagfn);
             $("a.expandtag", wrapper).click(expandtagfn);
             $("a.hidereslist", wrapper).click(function(ev)
                                               { ev.preventDefault();
                                                 resUL.hide();
                                               });

             target.append(wrapper);
         },

         /**
          * Internal functions (not really "private" I guess, but users shouldn't
          * be using them
          */
         fn: {

             boolComp: function(fn) {
                 return function() {
                     return !fn.apply(null, arguments);
                 };
             },


             /**
              * Takes any number of functions as arguments.  Each function except
              * the last  must have an "errorcode" value (ie. fn.errorcode = 404).
              *
              */
             errorChoose: function(fn) {
                 var lastFn = arguments[arguments.length - 1],
                     fns = Array.prototype.slice.call(arguments, 0, -1);
                 return function(xhr, textStatus, errThrown) {
/*                     if (window.console){
                         console.log(xhr.status, textStatus);
                         console.log(e);
                     }*/
                     for (var i = 0; i < fns.length; i++) {
                         if (fns[i].errorcode == xhr.status) {
                             return fns[i](xhr, textStatus, errThrown);
                         }
                     }
                     return lastFn(xhr, textStatus, errThrown);
                 };
             },
             defErrorFn: function(code, fn) {
                 fn.errorcode = code;
                 return fn;
             },

             /**
              *
              */
             tagGetObject: function(data, success, error) {
                 return { url: fK.cf.getTagUrl, type: "get",
                          data: data, success: success, error: error };
             },
             tagPostObject: function(data, success, error) {
               return {  url: fK.cf.postTagUrl, type: "post",
                         data: data, success: success, error: error };
             },
             tagDeleteObject: function (data, success, error) {
                 return {url: fK.cf.postTagUrl, type: "delete",
                         data: data, success: success, error: error};
             },
             resGetObject: function(data, success, error) {
                 return { url: fK.cf.getResUrl, type: "get", dataType: "xml",
                          data: data, success: success, error: error };
             },
             resPostObject: function (data, success, error) {
                 return {url: fK.cf.postResUrl, type: "post",
                         data: data, success: success, error: error};
             },
             resDeleteObject: function (data, success, error) {
                 return {url: fK.cf.postResUrl, type: "delete",
                         data: data, success: success, error: error};
             },
             userGetObject: function (data, success, error) {
                 return {url: fK.cf.getUserUrl, type: "get",
                         data: data, cache: false, dataType: "json", 
                         success: success, error: error };
             },
             userPostObject: function (data, success, error) {
                 return {url: fK.cf.postUserUrl || fK.cf.getUserUrl,
                         type: "post", data: data, dataType: "xml",
                         success: success, error: error };
             },
             adminGetObject: function (data, success, error) {
                 return {url: fK.cf.getAdminUrl,
                         type: "get", data: data, dataType: "xml",
                         success: success, error: error};
             },
             adminPostObject: function (data, success, error) {
                 return {url: fK.cf.postAdminUrl || fK.cf.getAdminUrl,
                         type: "post", data: data, dataType: "xml",
                         success: success, error: error };
             },

             /**
              * Returns a function to be associated with droptag events
              */
             droptag_react: function() {
                 var funcs = {}, tdata = arguments[0];
                 funcs.displaySuccess = function(){ tdata.element.remove(); };
                 funcs.error403 = function(xhr, msg) {
                     alert("Il faut vous loguer d'abord."); };
                 funcs.error403.errorcode = 403;
                 funcs.error404 = function(xhr, msg) { alert("Le tag n'existe pas."); };
                 funcs.error404.errorcode = 404;
                 funcs.errorOther = function(xhr, msg) { alert("Erreur serveur: "
                                                               + xhr.status); };

                 var ajOb = fK.fn.tagDeleteObject(
                     {folksotag: tdata.tagnorm || tdata.id},
                     funcs.displaySuccess,
                     fK.fn.errorChoose(funcs.error403,
                                       funcs.error404,
                                       funcs.errorOther));

                 return function(ev)
                 {
                     ev.preventDefault();
                     // consider attaching funcs to the DOM here (via $(this))
                     $.ajax(ajOb);
                 };
             },


             expandtag_react: function() {
                 var
                 tdata = arguments[0],
                 target = arguments[1],
                 displaySuccess = fK.fn.displayJsonResList(target),
                 errorOther = function(xhr, msg) {alert("Erreur serveur "
                                                        + xhr.status + " "
                                                        + tdata.tagnorm); };

                 var ajOb = fK.fn.userGetObject(
                     {folksotag: tdata.tagnorm || tdata.id,
//                      folksouid: "gustav-2010-001",
                      folksodatatype: "json"},
                     displaySuccess,
                     fK.fn.errorChoose(errorOther)
                 ); //add more here!

                 return function(ev)
                 {
                     ev.preventDefault();
                     if ($("li", target).length > 0) {
                         target.parent().show();
                         target.show();
                     }
                     else {
                         $.ajax(ajOb);
                     }
                 };
             },
             /**
              *
              * Sets "reslist", "starting" and "ending" in the target's $().data
              *
              * @param target {jQuery}
              * @return Returns a function taking one argument.
              *
              */
             displayJsonResList: function() {
                 var target = arguments[0];
                 return function (json) {
                     target.data("reslist", json);
                     target.data("starting", 0);
                     for (var i = 0; (i < fK.cf.tagListMax) && (i < json.length); i++) {
                         fK.simpleres(target, json[i].title,
                                      json[i].url, json[i].resid);
                     }
                     if (json.length > fK.cf.tagListMax) {
                         target.data("ending", fK.cf.tagListMax - 1);
                     }
                     else {
                         target.data("ending", json.length - 1);
                     }
                     if (fK.ufn.hooks.displayJsonResList) {
                         fK.ufn.hooks.displayJsonResList(target);
                     }
                 };
             },
             /**
              * Scroll forward through a list
              *
              * @param ul {jQuery} The element we are scrolling inside of
              * @param endFn {function} (Optional) Function to be called when
              * we can no longer scroll forward
              */
             advance1: function(ul, endFn)
             {
                 // do something if we don't have data?
                 var json = ul.data("reslist"), start = ul.data("starting"),
                 end = ul.data("ending");

                 if (($("li:visible", ul).length == json.length) ||
                     (json.length == end + 1)){
                     if (endFn) {
                         endFn();
                     }
                     return;
                 }

                 if (end + 1 < $("li", ul).length){
                     $("li", ul).eq(end + 1).show();
                 }
                 else {
                     fK.simpleres(ul, json[end + 1].title, json[end + 1].url,
                                  json[end + 1].resid);
                 }
                 $("li:visible:first", ul).hide();
                 ul.data("ending", end + 1);
                 ul.data("starting", start + 1);
             },
             /**
              * Scroll backwards
              *
              */
             rewind1: function(ul, endFn)
             {

                 var json = ul.data("reslist"), start = ul.data("starting"),
                 end = ul.data("ending");

                 if (start == 0) {
                     if (endFn) {
                         endFn();
                     }
                     return;
                 }
                 var lis = $("li", ul), firstNotHidden;
                 for (var i = 0; i < lis.length; i++) {
                         if (lis.eq(i).is(":visible")) {
                             firstNotHidden = i; i = lis.length;
                         }
                 }

//                 if (firstNotHidden == 0) return; // should not be necessary...
                 lis.eq(firstNotHidden - 1).show();
                 $("li:visible:last", ul).hide();

                 ul.data("ending", end - 1);
                 ul.data("starting", start - 1);
             },

             /**
              * Returns a function that removes a resource from a user's list of
              *  tagged resources.
              *
              * @param cred Optional (Usually the browser cookie should suffice,
              * might be useful for testing.
              */
             dropres_react: function() {
                 var
                 resdata = arguments[0],
                 displaySuccess = function() { resdata.element.remove(); },
                 error204 = function(xhr, msg) { alert("You do not have this tag"); },
                 errorOther = function(xhr, msg) { alert("Could not remove resource");};

                 error204.errorcode = 204;

                 var ajOb = fK.fn.resDeleteObject(
                     {folksores: resdata.url || resdata.resid,
                      folksouserdelete: "1"},
                     displaySuccess,
                     fK.fn.errorChoose(error204, errorOther)
                 );

                 return function(ev) {
                     ev.preventDefault();
                     $.ajax(ajOb);
                 };
             },

             tagres_react: function() {
                 var
                 tagbox = arguments[0],
                 target = arguments[1],
                 url = arguments[2] || window.location;

                 return function(ev) {
                     ev.preventDefault();
                     if (tagbox.val().length < 2) {
                         alert("Il faut choisir un tag d'abord"); return;
                     }

//                     var tagAjax = function() {}; // dummy var placeholder

                     var tag = tagbox.val(),
                     onSuccess = function(xml, status, xhr) {
                         tagbox.val("");
                         target.append("<li><a href=\""
                                       + $("tag", xml).text()
                                       + "\">"
                                       + tag + "</a></li>");
                         target.parent().show();
                         target.show();
                     },
                     error404 = function(xhr, msg)
                     {
                         if (/Tag does not exist/i.test(xhr.statusText)) {
                             if (/create the tag/i.test(xhr.responseText)) {
                                 var tagFn = fK.fn.tagCreate(tag, tagAjax),
                                 $dia = $("<p>Le tag « " + tag + " » n'existe pas. "
                                          + " Souhaitez-vous le créer ?</p>");

                                 target.append($dia);
                                 $dia.dialog({ title: "Création d'un nouveau tag",
                                               modal: true,
                                               buttons: {
                                                   "Abandonner": function() {
                                                       $dia.dialog("close");
                                                   },
                                                   "Créer": function() {
                                                       tagFn();
                                                       $dia.dialog("close");
                                                   }
                                               }
                                             });
                             }
                             else {
                                 alert("Le tag '" + tag + "' n'existe pas encore");
                             }
                         }
                         else {
                             alert("Erreur : cette page n'est pas encore indexée");
                         }
                     },
                     tagAjax = function() {
                         $.ajax( fK.fn.resPostObject({folksotag: tag,
                                                      folksores: url },
                                                     onSuccess,
                                                     fK.fn.errorChoose(error404,
                                                                       error401,
                                                                       errorOther)));
                     },


                     error401 = function(xhr, msg) { alert("Loggez-vous"); },
                     errorOther = function(xhr, msg) {
                         alert("Erreur: " + xhr.statusText);
                     };
                     error404.errorcode = 404; error401.errorcode = 401;
//                     alert("tag " + tag + " and url " + url );

                     tagAjax();
                 };
             },

             /**
              * Returns a function that will post a new tag.
              *
              * @param tag String Name of tag to create
              * @param success Function Continuation function to call
              * @return Function
              */
             tagCreate: function (tag, success) {
                 return function() {
                     $.ajax( fK.fn.tagPostObject({folksonewtag: tag},
                                             success,
                                             function(xhr, msg)
                                             {
                                                 alert("Tag creation error: "
                                                       + xhr.status + " "
                                                       + xhr.statusText);
                                             }));
                 };
             },

             /**
              * Get tag cloud and add each tag as an individual <li> to the
              * target elemnt
              *
              * testxml is _very_ optional (testing only, bypasses the ajax call)
              */
             buildCloud: function(target, url, testxml) {

                 var success =
                     function(xml, status, xhr) {
                         $("tag", xml)
                             .each(function()
                                   {
                                       var elem = $(this);
                                       target.append($("<li><a href=\""
                                                       + $("link", elem).attr("href")
                                                       + "\" class=\"cloudclass"
                                                       + $("weight", elem).text()
                                                       + "\">"
                                                       + $("display", elem).text()
                                                       + "</a></li>"));

                                   });
                     },
                 fail = function (xhr, status, errorThrown){
                     alert(xhr.status + " " + xhr.statusText + " " + xhr.responseText);
                 };

                 if (testxml) {
                     success(testxml, "Bogus status");
                 }
                 else
                 {

                     $.ajax(fK.fn.resGetObject( {folksores: url || window.location,
                                                 folksodatatype: "xml",
                                                 folksoclouduri: "1"},
                                                success,
                                                fail));
                 }
             },

             /**
              * Returns simple list item
              * 
              * 
              * Sample xml tag item (simpleTagList)
              * 
              * <tag>
              * <numid>1</numid>
              * <tagnorm>tagone</tagnorm>
              * <link>http://localhost/tag/tagone</link>
              * <display>tagone</display>
              * <count/>
              * </tag>
              * 
              */
             formatSimpleTagListItem: function(itemOb) {
                 with (itemOb) {
                     return "<li><a href=\"" + link + ">"
                     + display + "</a></li>";
                 }
             },


             /**
              * This function allows us to listen for the addition of
              * a folksosess cookie. Specifically, this might happen via
              * a facebook login.
              */
             pollFolksoCookie: function ()
             {
                 $('body').bind('loggedIn', function() {
                                    fK.loginStatus = true;
                                    if (fK.onLoggedIn.length > 0) {
                                        for (var i = 0; i < fK.onLoggedIn.length; i++) {
                                            fK.onLoggedIn[i]();
                                        }
                                    }
                                });
                 $('body').bind('loggedOut', function() {fK.loginStatus = false; });

                 var poller =
                     setInterval(function()
                                 {

                                     if (/folksosess/.test(document.cookie)) {
                                         $('body').trigger('loggedIn');
                                         clearInterval(poller);
                                     }
                                 },
                                 500);

             },

             /**
              * Verify that a FB user has an account here.
              */
             checkFBuserid: function(uid, okFunc, badFunc) {

                 var
                 error406 = function() {
                     alert("Erreur système: identifiant Facebook malformé");
                     badFunc();
                 },
                 error404 = function() {
                     alert("Utilisateur non encore inscrit");
                     badFunc();
                 },
                 errorOther = function(xhr, msg) {
                     alert("Problème: " + xhr.status
                           + " " + xhr.statusText
                           + " " + msg);
                     badFunc();
                 };

                 error406.errorcode = 406; error404.errorcode = 404;

                 var aj =
                     fK.fn.userGetObject({folksofbuid: uid,
                                    folksocheck: "1"},
                                   function() { okFunc();},
                                   fK.fn.errorChoose(error406, error404, errorOther)
                                   );
                 aj.dataType = "text";
                 $.ajax(aj);
             },

             /**
              * Log a Facebook user in.  This assumes that the FB
              * Connect stuff is done.
              */
             completeFBlogin: function(okFunc, badFunc) {
                 var
                 error400 = function(xhr, msg)
                 {
                     alert("Erreur système: informations insuffisantes");
                     badFunc();
                 },
                 error500 = 
                     function(xhr, msg){
                         alert("Erreur interne du système des utilisateurs "
                               + xhr.status + " " + xhr.statusText);
                         badFunc();
                     },
                 error404 = 
                     function(xhr, msg) {
                         fK.fn.createFBuser(okFunc);    
                     },
                 errorOther = function(xhr, msg) {
                     alert("Erreur inattendue: " + xhr.status + " " + xhr.statusText);
                     badFunc();
                 };
                 error400.errorcode = 400; error500.errorcode = 500; 
                 error404.errorcode = 404;

                 var aj =
                     fK.fn.userGetObject({folksofblogin: "1"},
                                         okFunc,
                                         fK.fn.errorChoose(error400, error404, error500, errorOther));
                 aj.dataType = "text";
                 $.ajax(aj);

             },
             /**
              * Create a new user as a Facebook user. The ajax call should also 
              * start a new session.
              * 
              * @param okFunc Post-completion callback. Probably the same as
              *  in completeFBlogin().
              */
             createFBuser: function(okFunc){
                 var aj = fK.fn.userPostObject({folksonewfb: "1"},
                                               okFunc,
                                               function(xhr, msg) {
                                                   alert("Failed to create new user");
                                               });/*
#ifdef DEBUG                  
                 if (window.console) {
                     console.log("about to call createFBuser");
                 }
#endif */
                 aj.dataType = "xml";
                 jQuery.ajax(aj);
             }
         }
     };


})();