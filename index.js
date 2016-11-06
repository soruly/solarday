function logout(){
  $.ajax({
    url: "ajax.php?logout",
    global: false,
    type: "GET",
    dataType: "html",
    async:false,
    success: function(){
      location.reload();
    }
  });
}

$("#blog_title").attr("contenteditable", "false");

$.ctrl = function(key, callback, args) {
    $(document).keydown(function(e) {
        if(!args) args=[]; // IE barks when args is null
        if(e.keyCode == key.charCodeAt(0) && e.ctrlKey) {
            callback.apply(this, args);
            return false;
        }
    });
};


function add_blog(){
  var d = new Date();
  $.ajax({
      url: "ajax.php?add_blog&timezone="+d.getTimezoneOffset()/60,
      global: false,
      type: "POST",
      data: ({}),
      dataType: "html",
      async:false,
      success: function(msg){
       if(parseInt(msg) != "NaN"){
         location.href = "/blog/"+msg+"/";
       }else{
         alert(msg);
       }
      }
     }
  )
}
function delete_blog(){
  if(confirm("Delete this blog?")){
    $.ajax({
        url: "ajax.php?delete_blog",
        global: false,
        type: "POST",
        data: ({id: $("#blog_id").html()}),
        dataType: "html",
        async:false,
        success: function(msg){
         if(parseInt(msg) != "NaN"){
           location.href = "/blog/"+msg+"/";
         }else{
           alert(msg);
         }
        }
       }
    );
  }
}

var category_list = Array();
category_list[1] = 'Life';
category_list[2] = 'Development';
category_list[3] = 'Playback';
category_list[4] = 'Article';
var category_id = 0;

function edit_blog(){
    for(i=1;i<=category_list.length;i++){
      if($("#blog_category").html() == category_list[i]){category_id = i;}
    }

  if($("#blog_title").attr("contenteditable") == "false"){
    $("#blog_title").attr("contenteditable", "true");
    $("#blog_content").attr("contenteditable", "true");
    $("#blog_category").click(function() {
      category_id = category_id >= category_list.length-1 ? 1 : ++category_id;
      $("#blog_category").html(category_list[category_id]);
    });
    $("#blog_time").html(raw_time($("#blog_time").html()));
    $("#blog_time").click(function() {
      var d=new Date();
      var date = d.getFullYear()+"-"+zerofill(d.getMonth()+1)+"-"+zerofill(d.getDate());
      var time = zerofill(d.getHours())+":"+zerofill(d.getMinutes())+":"+zerofill(d.getSeconds());
      $("#blog_time").attr("data-time",date+" "+time);
      $("#blog_time").html(date+" "+time);
    });
    $("#blog_time").attr("contenteditable", "true");
  }
  else{
    $("#blog_title").attr("contenteditable", "false");
    $("#blog_time").attr("contenteditable", "false");
    $("#blog_content").attr("contenteditable", "false");
    $("#blog_category").unbind('click');
    $("#blog_time").html(fmt_time($("#blog_time").attr("data-time")));
    $("#blog_time").unbind('click');
  }
}

$("#blog_private").click(function(){
  var private = $("#blog_private").html() == "private" ? 0 : 1;
  $.ajax({
    url: "ajax.php?private_blog",
    global: false,
    type: "POST",
    data: ({id: $("#blog_id").html(), private: private}),
    dataType: "html",
    async:true,
    success: function(msg){
      var str = private === 1 ? "private" : "public";
      if(msg != ""){alert(msg);}
      else{$("#blog_private").html(str);}
    }
  });
});

function html_2_bbcode(str){
  str = str.replace(/(\r\n|\n|\r)/igm,"");
  str = str.replace(/<div><br><\/div>/igm,"<div></div>");
  str = str.replace(/(.)<div>/igm,"$1<br>");
  str = str.replace(/<div class="right">([^<]*)<\/div>/igm,"[right]$1[/right]");
  str = str.replace(/<div class="center">([^<]*)<\/div>/igm,"[center]$1[/center]");
  str = str.replace(/<div(.*?)>/igm,"");
  str = str.replace(/<\/div>/igm,"");
  str = str.replace(/<br.?\/?>/igm,"\r\n");
  str = str.replace(/<cite>([^<]*)<\/cite>/igm,"[quote]$1[/quote]");
  str = str.replace(/<code>([^<]*)<\/code>/igm,"[code]$1[/code]");
  str = str.replace(/<b>([^<]*)<\/b>/igm,"[b]$1[/b]");
  str = str.replace(/<i>([^<]*)<\/i>/igm,"[i]$1[/i]");
  str = str.replace(/<u>([^<]*)<\/u>/igm,"[u]$1[/u]");
  str = str.replace(/<s>([^<]*)<\/s>/igm,"[s]$1[/s]");
  str = str.replace(/<a href="([^"]*)" target="_blank">[^<]*<\/a>/igm,"[url]$1[/url]");
  str = str.replace(/<span class="highlight">([^<]*)<\/span>/igm,"[hl]$1[/hl]");
  str = str.replace(/<img class="icon"[^<]*\/([^<]*).gif">/igm,"[icon]$1[/icon]");
  str = str.replace(/<img[^>]*\/(\d+).jpg".*?>/igm,"[pic]$1[/pic]");
  str = str.replace(/<audio.*src="\/[^"]*\/([^"]*)".*<\/audio>/igm,"[music]$1[/music]");
  str = str.replace(/<[^<]+?>/igm,"");
  return str;
}

$.ctrl('S', function() {
  if($("#blog_content").attr("contenteditable") == "true"){
    save();
  }
});

$.ctrl('E', function() {
  edit_blog();
});

$.ctrl('A', function() {
  if($("#blog_content").attr("contenteditable") == "false"){
    add_blog();
  }
});

$.ctrl('D', function() {
  delete_blog();
});

$.ctrl('Q', function() {
  if($("#blog_content").attr("contenteditable") == "true"){
    $('#emoticon').blindshow();
  }
});


function save(){
  document.title = "Saving...";
  var private = $("#blog_private").html() == "private" ? 1 : 0;
  var datetime = utc_time($("#blog_time").html());
  $.ajax({
    url: "ajax.php?edit_blog",
    global: false,
    type: "POST",
    data: ({id: $("#blog_id").html(), category: category_id, private: private, time: datetime, title: html_2_bbcode($("#blog_title").html()), blog: html_2_bbcode($("#blog_content").html())}),
    dataType: "html",
    async:true,
    success: function(msg){
      $("#bbcode").html(html_2_bbcode($("#blog_content").html()));
      if(msg != ""){alert(msg);}
      else{document.title = "SolarDay";}
    }
  });
}

function raw_time(strtime){
  apm = strtime.replace(/.*(AM|PM).*/ig,"$1");
  if(apm == "PM"){
    hour = parseInt(strtime.replace(/(\d+)..(\d+)..(\d+).{11}(\d+):(\d+):(\d+)/ig,"$4"),10)+12;
    timestr = strtime.replace(/(\d+)..(\d+)..(\d+).{11}(\d+):(\d+):(\d+)/ig,"$1-$2-$3 ");
    timestr += hour.toString();
    timestr += strtime.replace(/(\d+)..(\d+)..(\d+).{11}(\d+):(\d+):(\d+)/ig,":$5:$6");
  }
  else{
    timestr = strtime.replace(/(\d+)..(\d+)..(\d+).{11}(\d+):(\d+):(\d+)/ig,"$1-$2-$3 $4:$5:$6");
  }
  return timestr;
}

function utc_time(strtime){
  var datestr = strtime.split(" ")[0];
  var timestr = strtime.split(" ")[1];
  var d=new Date(datestr.split("-")[0],datestr.split("-")[1]-1,datestr.split("-")[2]);
  d.setHours(timestr.split(":")[0],timestr.split(":")[1],timestr.split(":")[2]);
  d.setHours(d.getHours()+(d.getTimezoneOffset()/60));
  
  var date = d.getFullYear()+"-"+zerofill(d.getMonth()+1)+"-"+zerofill(d.getDate());
  var time = zerofill(d.getHours())+":"+zerofill(d.getMinutes())+":"+zerofill(d.getSeconds());
  return date+" "+time;
}

function findNode(list, node) {
  for (var i = 0; i < list.length; i++) {
    if (list[i] == node) {
      return i;
    }
  }
  return -1;
}

function getCursorPos() {
  var cursorPos;
  if (window.getSelection) {
    var selObj = window.getSelection();
    var selRange = selObj.getRangeAt(0);
    cursorPos =  findNode(selObj.anchorNode.parentNode.childNodes, selObj.anchorNode) + selObj.anchorOffset;
    /* FIXME the following works wrong in Opera when the document is longer than 32767 chars */
  }
  else if (document.selection) {
    var range = document.selection.createRange();
    var bookmark = range.getBookmark();
    /* FIXME the following works wrong when the document is longer than 65535 chars */
    cursorPos = bookmark.charCodeAt(2) - 11; /* Undocumented function [3] */
  }
  return cursorPos;
}

function addemoticon(src){
  $("#emoticon").hide();
  $("#blind").hide();

  var selection, range;
  selection = window.getSelection();
  if (selection.getRangeAt && selection.rangeCount) {
    range = selection.getRangeAt(0);
    range.deleteContents();
    var image = document.createElement('img');
    image.className = "icon";
    image.src = src;
    range.insertNode(image);
  }
}

function filesize(bytes){
  size = parseInt(bytes);
  if(bytes>1000000) size = parseFloat(size/1024/1024).toFixed(2)+'MB';
  else if(bytes>1000) size = parseFloat(size/1024).toFixed(2)+'KB';
  else size = parseFloat(size).toFixed(2)+'Bytes';
  return size;
}

$(function() {
  $("#blog_content").html5Uploader({
    name: "file",
    postUrl: "ajax.php?upload",
    onClientLoadStart:function(e,file){
      $("#messagebox").blindshow();
    },
    onClientLoad:function(e,file){
    },
    onServerLoadStart:function(e,file){
        percent = parseInt(parseInt(e.loaded) / parseInt(e.total)*100);
        $("#messagebox").html('Uploading......<br>'+file.name+'<br><br>'+filesize(e.loaded)+"/"+filesize(e.total)+'<br><div style="text-align:right;border-top:1px solid #000;width:'+percent*3+'px">'+percent+'%</div>');
    },
    onServerProgress:function(e,file){
      if(e.lengthComputable){
        percent = parseInt(parseInt(e.loaded) / parseInt(e.total)*100);
        $("#messagebox").html('Uploading......<br>'+file.name+'<br><br>'+filesize(e.loaded)+"/"+filesize(e.total)+'<br><div style="text-align:right;border-top:1px solid #000;width:'+percent*3+'px">'+percent+'%</div>');
      }
    },
    onload:function(e,file,responseText){
      var ext = (/[.]/.exec(file.name)) ? /[^.]+$/.exec(file.name) : undefined;
      ext = ext.toString().toLowerCase();

      var selection, range;
      selection = window.getSelection();
      if (selection.getRangeAt && selection.rangeCount) {
        range = selection.getRangeAt(0);
        range.deleteContents();

        if(ext == "jpg" || ext == "png" || ext == "gif"){
          var image = document.createElement('img');
          image.setAttribute("onclick","show_photo(this.src);");
          image.className = "photo";
          image.src = '/pic/thumb_big/'+responseText+'.jpg';
          range.insertNode(image);
        }
        else if(ext == "mp3"){
          var name = document.createTextNode(responseText.slice(0,-4));
          var br = document.createElement('br');
          var audio = document.createElement('audio');
          audio.setAttribute("controls","controls");
          audio.setAttribute("type","audio/mp3");
          audio.src = "/music/"+responseText;
          range.insertNode(audio);
          range.insertNode(br);
          range.insertNode(name);
        }
      }
      $("#messagebox").hide();
      $("#blind").hide();
    }
  });
});

function highlight() {
  var selection, range;
    selection = window.getSelection();
  if (selection.getRangeAt && selection.rangeCount) {
    range = selection.getRangeAt(0);
    var hltext = document.createElement("span")
    hltext.style.color = "#3366FF";
    hltext.innerHTML = selection;
    range.deleteContents();
    range.insertNode(hltext);
    selection.addRange(range);
  }
}
