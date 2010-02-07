/* 
 * Copyright (c) 2010 Joseph Fahey 
 * GNU Public Licence.
 * 
 * JS specific to the myfabula.php page.
 * 
 * Requires jQuery and folksonomie.js. They should be loaded first.
 * 
 * 
 * 
 */


$(document).ready(function()
                  {
                      var hostAndPath = 'http://localhost/';
                      // var hostAndPath = 'http://www.fabula.org/tags/';
                      fK.init({getUserUrl: hostAndPath + 'user.php',
                               getTagUrl:  hostAndPath + 'tag.php',
                               getResUrl: hostAndPath + 'resource.php',
                               postTagUrl: hostAndPath + 'tag.php',
                               postResUrl: hostAndPath + 'resource.php',
                               simpleTagTemplate: "#tagitem",
                               simpleResTemplate: "#resitem",
                               simpleResWrap: "<li class=\"res\">"});


                      fK.ufn.hooks.displayJsonResList = function(target) 
                      {
                          var scrollContainer = target.parent(), tmr;
                          scrollContainer.show();
                          var stopTmr = function() { clearInterval(tmr); };                          

                          $(".scrollback", scrollContainer)
                              .mousedown(function(ev) {
                                             ev.preventDefault();
                                             fK.fn.rewind1(target);
                                             tmr = setInterval(function()
                                                               {
                                                                   fK.fn.rewind1(
                                                                       target,
                                                                       stopTmr);
                                                               },
                                                               200);
                                     })
                              .mouseup(function(ev)
                                       {
                                           ev.preventDefault(); clearInterval(tmr);
                                       })
                          .click(function(ev){ ev.preventDefault(); });

                          $(".scrollforward", scrollContainer)
                              .mousedown(function(ev) 
                                         {
                                             ev.preventDefault();
                                             fK.fn.advance1(target);
                                             tmr = setInterval(function()
                                                               {
                                                                   fK.fn.advance1(
                                                                       target,
                                                                       stopTmr);
                                                               },
                                                               200);
                                     })
                              .mouseup(function(ev)
                                       {
                                           ev.preventDefault();
                                           clearInterval(tmr);
                                       })
                              .click(function(ev) { ev.preventDefault(); });
                          
                      };

                      fK.myfab.taglistFromData($("#tagholder"),
                                                   fK.data.myfab);

                      if (fK.myfab.loginStatus) {
                          $("#oidlist, #loginbox").hide();
                      }
                      fK.oid.logopath = "/logos/";                      
                      $("#oidlist").append(fK.oid.providerList());

                  }); // end document.ready



(function() {
     /*
      * the myfab part of the fK namespace should only be used for this page.
      */
     var myfab = window.fK.myfab = {
         loginStatus: false,

             /**
              * Immediately appends the new elements 
              * 
              * @param target {jQuery} Where the new objects will be appended.
              */
         taglistFromData: function(target, json) {
             var data = json || fK.data.myfab;
             if (data) {
                 for (var i = 0; i < data.length; i++){
                     fK.simpletag(target, 
                                  fK.data.myfab[i].tagdisplay,
                                  fK.data.myfab[i].tagnorm,
                                  fK.data.myfab[i].resid);
                 }
             }               
         }
     };
 })();

