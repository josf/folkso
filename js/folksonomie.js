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
                 simpleTagWrap: 'html string for element creation',
                 simpleResWrap: 'html string for element creation'
                },

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
             /**
              * Takes any number of functions as arguments. Last argument must  be
              * an integer corresponding to an HTTP error code. Each function except 
              * the last  must have an "errorcode" value (ie. fn.errorcode = 404).
              * 
              */
             errorChoose: function(fn) {
                 var lastFn = arguments[arguments.length - 1],
                     fns = Array.prototype.slice.call(arguments, 0, -1);
                 return function(errno) {
                     for (var i = 0; i < fns.length; i++) {
                     if (fns[i].errorcode == errno) {
                         return fns[i]();
                     }
                     }
                     return lastFn();
                 };
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
                 return { url: fK.cf.getResUrl, type: "get", 
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
                         data: data, cache: false, dataType: "json", success: success, error: error };
             },

             /**
              * Returns a function to be associated with droptag events
              */
             droptag_react: function() {
                 var funcs = {}, tdata = arguments[0];
                 funcs.displaySuccess = function(){ tdata.element.remove(); };
                 funcs.error403 = function(xhr, msg) { alert("Better login, dude"); };
                 funcs.error403.errorcode = 403;
                 funcs.error404 = function(xhr, msg) { alert("Sorry, no tag"); };
                 funcs.error404.errorcode = 404;
                 funcs.errorOther = function(xhr, msg) { alert("Wierd error"); };
                 
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
                 errorOther = function(xhr, msg) {alert("More wierdness" + tdata.tagnorm); };

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

             }
         }
     };
     

})();