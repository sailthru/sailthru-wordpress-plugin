var SailthruGigya = {

  sailthru_response : '',

  syncProfile : function(eventObj, callback_url) {

    if (callback_url == '') {
      sailthru_response = 'no callback set';
      console.log(SaithruGigya.sailthru_response);
      return false;
    }
    var x = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    var profile = SailthruGigya.serialize_profile(eventObj);
    var params = 'json='+profile;

    x.open("POST", callback_url, true);
    x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    x.setRequestHeader("Content-length", params.length);
    x.setRequestHeader("Connection", "close");

    x.onreadystatechange=function()
    {
    if (x.readyState==4 && x.status==200)
      {
        sailthru_response = x.responseText;
      } else {
        sailthru_response = x.readyState;
      }
    }
    x.send(params);
  },

  serialize_profile: function(obj) {
    user = JSON.stringify(obj);
    return user;
  }

};

function SailthruSync(eventObj) {
  SailthruGigya.syncProfile(eventObj,SailthruGigya.callback_url)
}

