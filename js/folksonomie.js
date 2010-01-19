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
         attrs: {simpleResTemplate: 'jQuery id of the template element', 
                 simpleTagTemplate: 'jQuery id of the template element',
                 getTagUrl: 'url', getResUrl: 'url', 
                 postTagUrl: 'url', postResUrl: 'userLevel',
                 simpleTagWrap: 'html string for element creation',
                 simpleResWrap: 'html string for element creation'
                },

         /**
          * Config information set on init.
          */
         cf: {},

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
             wrapper.data("fKres", resdata);

             //   item.data(resdata);
             //             target.append(item);
             // setup commands here (event listeners assigned to selectors)
             target.append(wrapper);
         },
         /**
          * @target {jQuery} Element that the new element will be appended to
          */
         simpletag: function(target, display, tagnorm, id) 
         {
             var tagdata = { display: display,
                             tagnorm: tagnorm,
                             id: id};
             var newtag = fK.cf.simpleTagTemplate.jqote(tagdata);
             var wrapper = $(fK.cf.tagwrap || "<div class=\"atag\">");

             // we keep a link back to what will be the DOM element
             tagdata.element = wrapper;

             newtag.appendTo(wrapper);
             wrapper.data("fKtag", tagdata);
             

             // crude version 
             // droptag_react returns a function for .click
             var func = fK.fn.droptag_react(tagdata);
             wrapper.find("a.droptag").click(func);

             // setup commands here (event listeners assigned to selectors)
             // assign data (tag id etc.) to containing element

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
             ajaxBasic: function(url, type, data, success, error) 
             {
                 $.ajax({url: url, type: type, data: data, success: success, error: error});
             },
             deleteBase: function() {
               return fK.fn.ajaxBasic.partial(fK.cf.postResUrl, "delete", _ , _);
             },
             /**
              * Prepares then executes a function to be associated with droptag events
              */
             droptag_react: function() {
                 var funcs = {}, tdata = arguments[0];
                 funcs.displaySuccess = function(){ tdata.element.remove(); };
                 funcs.error403 = function(xhr, msg) { alert("Better login, dude"); };
                 funcs.error404 = function(xhr, msg) { alert("Sorry, no tag"); };
                 funcs.errorOther = function(xhr, msg) { alert("Wierd error"); };
                 return function(ev) 
                 { 
                     ev.preventDefault();
                     fK.fn.deleteBase(
                         {folksotag: tdata.tagnorm || tdata.id },
                         funcs.displaySuccesss,
                         function(xhr, msg) 
                         {
                             switch(xhr.status) {
                             case 403:
                                 funcs.error403(xhr, msg); break;
                             case 404: 
                                 funcs.error404(xhr, msg); break;
                             default:
                                 funcs.errorOther(xhr, msg); break;
                             }
                         });
                 };
             },

             /**
              * Returns a function that removes a resource from a user's list of
              *  tagged resources. 
              * 
              * @param cred Optional (Usually the browser cookie should suffice, 
              * might be useful for testing.
              */
             dropres_f: function(res, cred) {
                 return function() 
                 {
                     var payload = {folksores: res};
                     if (cred) payload.folksosession = cred;
                                   
                     $.ajax({
                                
                                
                                
                                error: function(xhr,msg){ alert(msg); },
                                success: remove_res

                                
                            });
                 };
             }
             
         }
     };
     

})();