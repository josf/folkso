

$(document).ready(
    function() {

        var user = {}; // will contain all the  user data, most of which is not displayed.
        var $msgBox = $("#messageBox");
        var $editor = $("#adminCvEditor");


        var hostAndPath = '/tags/';
        fK.init({
                    autocompleteUrl: hostAndPath + 'tagcomplete.php',
                    postResUrl: hostAndPath + 'resource.php',
                    getResUrl: hostAndPath + 'resource.php',
                    getUserUrl: hostAndPath + 'user.php',
                    oIdLogoPath: "/tags/logos/",
                    oIdPath: '/tags/fdent/'
                });
        
        var fetchUserDataSuccess = 
            function(xml, status, xhr) {
                if (xhr.status !== 200) {
                    $msgBox.html("Aucune donnée utilisateur n'a été trouvée. Commencez votre compte dans l'Espace Tags");
                    return;
                }

                user.firstname = $("firstname", xml).text();
                user.lastname  = $("lastname", xml).text();
                user.email = $("email", xml).text();
                user.institution = $("institution", xml).text();
                user.pays = $("pays", xml).text();
                user.fonction = $("fonction", xml).text();
                user.cv = $("cv", xml).xml();

                $msgBox.html("Bonjour " + user.firstname + " !");

                $editor.html(user.cv);
                $editor.tinymce(
                    {
                        script_url: '/tags/js/tinymce/jscripts/tiny_mce/tiny_mce.js',
                        theme: "advanced",
                        theme_advanced_toolbar_location: "top",
                        theme_advanced_disable: "strikethrough,outdent,code,anchor,forecolor,backcolor,newdocument,visualaid,sub,indent,styleselect,formatselect",
                        theme_advanced_buttons1: "bold,italic,underline,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist",
                        theme_advanced_buttons2: "image,link,unlink,cleanup,hr,removeformat,sup,charmap",
                        theme_advanced_buttons3: "",
                        valid_elements: "a[href],span[style],strong/b,em/i,ul,li,sup,p,h1,h2,h3,h4,img",
                        width: "500", 
                        remove_redundant_brs: false,

                             /*
                              * It is crucial to use raw here. Otherwise 
                              * the system will choke on the "&"
                              */
                        entity_encoding: 'raw'
                    });
            };


        var getUser_aj = 
            fK.fn.userGetObject(
                {folksouserdata: 1},
                fetchUserDataSuccess,
                function(xhr, tS, e) {
                    $msgBox.html(xhr.status + " " + tS);
                });
        getUser_aj.dataType = "xml";
        getUser_aj.cache = false;

        $.ajax(getUser_aj);


        var cvUpdateSuccess = 
            function(xml, status, xhr) {
                $editor.html($("cv", xml).xml());
                $msgBox.html("CV mis à jour");
            };



        $("#cv-send").click(
            function (ev) {
                ev.preventDefault();
                $msgBox.html("...envoi...");
                $.ajax(fK.fn.userPostObject({folksosetcv: $editor.tinymce().getContent(),
                                            folksosetfirstname: user.firstname,
                                            folksosetlastname: user.lastname}, 
                                            cvUpdateSuccess,
                                            function(xhr, tS, e) {
                                                $msgBox.html("échec " + xhr.status + " " + tS);
                                            }));

            });

});