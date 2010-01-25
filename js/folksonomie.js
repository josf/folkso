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
                 getUserUrl: 'url',
                 simpleTagWrap: 'html string for element creation',
                 simpleResWrap: 'html string for element creation'
                },

         /**
          * Config information set on init.
          * 
          * Other scripts could use this for their data, maybe.
          */
         cf: {},

         /**
          * The ufn "namespace" is for functions to be defined in other scripts, 
          * ie. page specific functions that override some of functions in the 
          * fK.fn "namespace".
          * 
          * Be warned: if you put anything beside functions in this namespace, 
          * there will be trouble. Functions will test for existence of functions 
          * here, but will not check to see if the variables are indeed functions. 
          * So just put functions here, nothing else.
          */
         ufn: {},

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

             var 
             droptagfn = fK.fn.droptag_react(tagdata),
             expandtagfn = fK.fn.expandtag_react(tagdata);

             wrapper.find("a.droptag").click(droptagfn);
             wrapper.find("a.expandtag").click(expandtagfn);

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
                         data: data, success: success, error: error };
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
                 displaySuccess = fK.fn.displayJsonResList(tdata),
                 errorOther = function(xhr, msg) {alert("More wierdness"); };

                 var ajOb = fK.fn.userGetObject(
                     {folksores: tdata.url || tdata.resid},
                     displaySuccess,
                     fK.fn.errorChoose(errorOther)
                 ); //add more here!
                      
                 return function(ev)
                 {
                     ev.preventDefault();
                     $.ajax(ajOb);
                 };
             },
             /**
              * @return Returns a function taking one argument.
              */
             displayJsonResList: function() {
                 var tdata = arguments[0];
                 return function (json) {
                     for (var i = 0; i < json.length; ++i) {
                             fK.simpleres(tdata.element, json[i].title, 
                                             json[i].url, json[i].resid);
                     }
                 };
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
                 errorOther = function(xhr, msg) { alert("Could not remove resource");};
                 
                 var ajOb = fK.fn.resDeleteObject(
                     {folksores: resdata.url || resdata.resid,
                      folksouserdelete: "1"},
                     displaySuccess,
                     errorOther);

                 return function(ev) {
                     ev.preventDefault();
                     $.ajax(ajOb);
                 };
             }
         }
     };
     

})();