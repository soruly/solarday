function login(pwd){
  if(pwd != null){
    sha256('solarday_'+pwd).then(function(digest){
      $.ajax({
        url: "ajax.php?login",
        global: false,
        type: "POST",
        data: {password: digest},
        dataType: "html",
        async:false,
        success: function(){
          location.reload();
        }
      });
    });
  }
}

function sha256(str) {
  var buffer = new TextEncoder("utf-8").encode(str);
  return crypto.subtle.digest("SHA-256", buffer).then(function (hash) {
    return hex(hash);
  });
}

function hex(buffer) {
  var hexCodes = [];
  var view = new DataView(buffer);
  for (var i = 0; i < view.byteLength; i += 4) {
    var value = view.getUint32(i)
    var stringValue = value.toString(16)
    var padding = '00000000'
    var paddedValue = (padding + stringValue).slice(-padding.length)
    hexCodes.push(paddedValue);
  }
  return hexCodes.join("");
}

var loginButton = document.createElement('a');
loginButton.textContent = 'login';
loginButton.addEventListener('click', function() {
  $('#login').blindshow();
  $('#pwd').focus();
});

document.querySelector("#blog_foot").innerHTML = '';
document.querySelector("#blog_foot").appendChild(loginButton);
document.querySelector("#login>form").addEventListener('submit', function(){
  login(document.querySelector("#pwd").value);
  return false;
});

function zerofill(number) {
    result = number.toString().length == 1 ? '0' + number.toString() : number.toString();
    return result;
}

function fmt_time(strtime,local){
  local = (local == "undefined") ? false : local;
  var week = new Array("日","一","二","三","四","五","六");
  var datestr = strtime.split(" ")[0];
  var timestr = strtime.split(" ")[1];
  var d=new Date(datestr.split("-")[0],datestr.split("-")[1]-1,datestr.split("-")[2]);
  d.setHours(timestr.split(":")[0],timestr.split(":")[1],timestr.split(":")[2]);
  if(!local) d.setHours(d.getHours()-(d.getTimezoneOffset()/60));
  
  var date = d.getFullYear()+"年 "+zerofill(d.getMonth()+1)+"月 "+zerofill(d.getDate())+"日";
  var day = "星期"+week[d.getDay()];
  if(d.getHours() < 12) {var apm = "AM"}
  else{
    var apm = "PM";
    d.setHours(d.getHours()-12);
  }
  var time = zerofill(d.getHours())+":"+zerofill(d.getMinutes())+":"+zerofill(d.getSeconds());
  return date+" ("+day+") "+apm+" "+time;
}

$('.time').each(function(index) {
    $(this).html(fmt_time($(this).attr("data-time")));
});

function fmt_shorttime(strtime){
  if(strtime.split(" ").length === 3){
    return strtime;
  }
  else{
  var datestr = strtime.split(" ")[0];
  var timestr = strtime.split(" ")[1];
  var d = new Date(datestr.split("-")[0],datestr.split("-")[1]-1,datestr.split("-")[2]);
  d.setHours(timestr.split(":")[0],timestr.split(":")[1],timestr.split(":")[2]);
  d.setHours(d.getHours()-(d.getTimezoneOffset()/60));
  
  var date = d.getFullYear()+"-"+zerofill(d.getMonth()+1)+"-"+zerofill(d.getDate());
  if(d.getHours() < 12) {var apm = "AM"}
  else{
    var apm = "PM";
    d.setHours(d.getHours()-12);
  }
  var time = zerofill(d.getHours())+":"+zerofill(d.getMinutes())+":"+zerofill(d.getSeconds());
  return date+" "+apm+" "+time;
  }
}

$('.shorttime').each(function(index) {
    $(this).html(fmt_shorttime($(this).text()));
});


jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop() + "px");
    this.css("left", (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft() + "px");
    return this;
}

jQuery.fn.blindshow = function () {
  $(this).center();
  $("#blind").center();
  $("#blind").show();
  var obj = $(this);
  obj.show();
  $("#blind").click(function(){
    obj.hide();
    $("#blind").hide();
    $("#blind").unbind("click");
  });
    return this;
}

resize_photo = function(){
    $(".photo").each(function(index,element){
        var max_height,margin;
        if(window.innerWidth < 530){
            margin = 40;
        }
        else if(window.innerWidth < 760){
            margin = 120;
        }
        max_height = (window.innerWidth - margin) / $(this).attr("data-width") * $(this).attr("data-height");
        if(max_height > $(this).attr("data-height"))
            max_height = $(this).attr("data-height");
        max_height = max_height - max_height%25;
        
        $(this).css("max-height",max_height+"px");
    });
};
window.onresize = resize_photo;
resize_photo();

/*
document.body.onkeyup = function(e){
  e.preventDefault();
  if(e.key === "ArrowLeft"){
    document.querySelector("#navigationbar_top").querySelectorAll("a")[0].click();
  }
  if(e.key === "ArrowRight"){
    document.querySelector("#navigationbar_top").querySelectorAll("a")[7].click();
  }
};
*/
