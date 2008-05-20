
function createXMLHttpRequest() {
    var request = false;

    /* does XLMHttpRequest work here? */
    if (window.XMLHttpRequest) {
        if (typeof XMLHttpRequest != 'undefined') {
            /* Try to create a new XMLHttpRequest object */
            try {
                request = new XMLHttpRequest();
            }
            catch (e) {
                request = false;
            }

        } else if (window.ActiveXObject) {
            /* Try to create a new ActiveX XMLHTTP object */
            try {
                request = new ActiveXObject('Msxml2.XMLHTTP');
            }
            catch(e) {
                try {
                    request = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (e) {
                    request = false;
                }
            }
        }
    }
    return request;
}

function requestData(p_request, p_URL, p_data) {
    if (p_request) {
        p_request.open('GET', p_URL, true);
        p_request.onreadystatechange = function() {
            if (p_request.readyState == 4) {
                if (p_request.status == 200)
                    alert(p_request.responseText);
            }
        }
        p_request.send(p_data);
    }
    else {
        alert(p_request);
    }
}

var req = createXMLHttpRequest();
window.onload = requestData(req, 
            '/resource.php?folksoresourceuri=http://fabula.org', 
            '');






 