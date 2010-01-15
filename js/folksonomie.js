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
                 postTagUrl: 'url', postResUrl: 'userLevel'},
         cf: {},
         /**
          * @target {jQuery} Element that the new element will be appended to
          */
         simpleres: function(target, display, url, id) 
         {
             var resdata = { display: display,
                             url: url,
                             id: id};
             fK.cf.simpleResTemplate.jqote({ display: display,
                                             url: url,
                                             id: id}).appendTo("#restarget");
          //   item.data(resdata);
//             target.append(item);
             // setup commands here (event listeners assigned to selectors)
         },
         /**
          * @target {jQuery} Element that the new element will be appended to
          */
         simpletag: function( display, tagnorm, id) 
         {
             var tagdata = { display: display,
                             tagnorm: tagnorm,
                             id: id};
             var item = fK.simpleTagTemplate.jqote(tagdata);
             // setup commands here (event listeners assigned to selectors)
             // assign data (tag id etc.) to containing element
         },
         /*
          * Internal functions (not really "private" I guess, but users shouldn't 
          * be using them 
          */
         fk: {
             /*
              * Assign handler functions to appropriate elements 
              */
             tagevents: function() 
             {

             },
             resevents: function() 
             {

             }
             
         }
     };
     

})();