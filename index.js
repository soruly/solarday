const logout = async () => {
  await fetch("ajax.php?logout");
  location.reload();
};

const logoutButton = document.createElement("a");
logoutButton.textContent = "logout";
logoutButton.addEventListener("click", logout);

document.querySelector("#blog_foot").innerHTML = "";
document.querySelector("#blog_foot").appendChild(logoutButton);

if (document.querySelector("#search_form")) {
  document.querySelector("#search_form").addEventListener("submit", (e) => {
    e.preventDefault();
    window.location = `/search/${encodeURIComponent(document.querySelector("#search").value)}`;
    return false;
  });
}

document.querySelector("#blog_title").contentEditable = false;

const raw_time = (strtime) => {
  const apm = strtime.replace(/.*(AM|PM).*/ig, "$1");
  let timestr = "";
  if (apm === "PM") {
    const hour = parseInt(strtime.replace(/(\d+)..(\d+)..(\d+).{11}(\d+):(\d+):(\d+)/ig, "$4"), 10) + 12;
    timestr = strtime.replace(/(\d+)..(\d+)..(\d+).{11}(\d+):(\d+):(\d+)/ig, "$1-$2-$3 ");
    timestr += hour.toString();
    timestr += strtime.replace(/(\d+)..(\d+)..(\d+).{11}(\d+):(\d+):(\d+)/ig, ":$5:$6");
  } else {
    timestr = strtime.replace(/(\d+)..(\d+)..(\d+).{11}(\d+):(\d+):(\d+)/ig, "$1-$2-$3 $4:$5:$6");
  }
  return timestr;
};

const utc_time = (strtime) => {
  const [
    datestr,
    timestr
  ] = strtime.split(" ");
  const d = new Date(datestr.split("-")[0], datestr.split("-")[1] - 1, datestr.split("-")[2]);
  d.setHours(timestr.split(":")[0], timestr.split(":")[1], timestr.split(":")[2]);
  d.setHours(d.getHours() + (d.getTimezoneOffset() / 60));

  const date = `${d.getFullYear()}-${(d.getMonth() + 1).toString().padStart(2, "0")}-${d.getDate().toString().padStart(2, "0")}`;
  const time = `${d.getHours().toString().padStart(2, "0")}:${d.getMinutes().toString().padStart(2, "0")}:${d.getSeconds().toString().padStart(2, "0")}`;
  return `${date} ${time}`;
};


const add_blog = async () => {
  const d = new Date();
  const msg = await fetch(`ajax.php?add_blog&timezone=${d.getTimezoneOffset() / 60}`).then((e) => e.text());
  if (isNaN(parseInt(msg, 10))) {
    alert(msg); // eslint-disable-line no-alert
    return;
  }
  location.href = `/blog/${msg}/`;
};

const delete_blog = async () => {
  if (confirm("Delete this blog?")) { // eslint-disable-line no-alert
    const msg = await fetch(`ajax.php?delete_blog=${document.querySelector("#blog_id").innerText}`).then((e) => e.text());
    if (isNaN(parseInt(msg, 10))) {
      alert(msg); // eslint-disable-line no-alert
      return;
    }
    location.href = `/blog/${msg}/`;
  }
};

const category_list = [
  "",
  "Life",
  "Development",
  "Playback",
  "Article"
];
window.category_id = 0;

const edit_blog = () => {
  for (let i = 1; i <= category_list.length; i += 1) {
    if (document.querySelector("#blog_category").innerText === category_list[i]) {
      window.category_id = i;
    }
  }

  if (document.querySelector("#blog_title").contentEditable === "true") {
    document.querySelector("#blog_title").contentEditable = false;
    document.querySelector("#blog_time").contentEditable = false;
    document.querySelector("#blog_content").contentEditable = false;
    document.querySelector("#blog_category").onclick = null;
    document.querySelector("#blog_time").innerText = fmt_time(document.querySelector("#blog_time").dataset.time);
    document.querySelector("#blog_time").onclick = null;
  } else {
    document.querySelector("#blog_title").contentEditable = true;
    document.querySelector("#blog_content").contentEditable = true;
    document.querySelector("#blog_category").onclick = () => {
      window.category_id = window.category_id >= category_list.length - 1 ? 1 : ++window.category_id;
      document.querySelector("#blog_category").innerText = category_list[window.category_id];
    };
    document.querySelector("#blog_time").innerText = raw_time(document.querySelector("#blog_time").innerText);
    document.querySelector("#blog_time").onclick = () => {
      const d = new Date();
      const date = `${d.getFullYear()}-${(d.getMonth() + 1).toString().padStart(2, "0")}-${d.getDate().toString().padStart(2, "0")}`;
      const time = `${d.getHours().toString().padStart(2, "0")}:${d.getMinutes().toString().padStart(2, "0")}:${d.getSeconds().toString().padStart(2, "0")}`;
      document.querySelector("#blog_time").dataset.time = `${date} ${time}`;
      document.querySelector("#blog_time").innerText = `${date} ${time}`;
    };
    document.querySelector("#blog_time").contentEditable = true;
  }
};

if (document.querySelector("#blog_private")) {
  document.querySelector("#blog_private").onclick = async () => {
    const action = document.querySelector("#blog_private").innerText === "private" ? "public" : "private";
    const msg = await fetch(`ajax.php?${action}_blog=${document.querySelector("#blog_id").innerText}`).then((e) => e.text());
    if (msg !== "") {
      alert(msg); // eslint-disable-line no-alert
      return;
    }
    document.querySelector("#blog_private").innerText = action;
  };
}

const html_2_bbcode = (html) => {
  let str = html;
  str = str.replace(/(\r\n|\n|\r)/igm, "");
  str = str.replace(/<div><br><\/div>/igm, "<div></div>");
  str = str.replace(/(.)<div>/igm, "$1<br>");
  str = str.replace(/<div class="right">([^<]*)<\/div>/igm, "[right]$1[/right]");
  str = str.replace(/<div class="center">([^<]*)<\/div>/igm, "[center]$1[/center]");
  str = str.replace(/<div(.*?)>/igm, "");
  str = str.replace(/<\/div>/igm, "");
  str = str.replace(/<br.?\/?>/igm, "\r\n");
  str = str.replace(/<cite>([^<]*)<\/cite>/igm, "[quote]$1[/quote]");
  str = str.replace(/<code>([^<]*)<\/code>/igm, "[code]$1[/code]");
  str = str.replace(/<b>([^<]*)<\/b>/igm, "[b]$1[/b]");
  str = str.replace(/<i>([^<]*)<\/i>/igm, "[i]$1[/i]");
  str = str.replace(/<u>([^<]*)<\/u>/igm, "[u]$1[/u]");
  str = str.replace(/<s>([^<]*)<\/s>/igm, "[s]$1[/s]");
  str = str.replace(/<a href="([^"]*)" target="_blank">[^<]*<\/a>/igm, "[url]$1[/url]");
  str = str.replace(/<span class="highlight">([^<]*)<\/span>/igm, "[hl]$1[/hl]");
  str = str.replace(/<img class="icon"[^<]*\/([^<]*).gif">/igm, "[icon]$1[/icon]");
  str = str.replace(/<img[^>]*\/(\d+).jpg".*?>/igm, "[pic]$1[/pic]");
  str = str.replace(/<audio.*src="\/[^"]*\/([^"]*)".*<\/audio>/igm, "[music]$1[/music]");
  str = str.replace(/<[^<]+?>/igm, "");
  return str;
};

const save = async () => {
  document.title = "Saving...";
  window.onbeforeunload = function (e) {
    const message = "Your changes have not been saved yet!";
    if (e) {
      e.returnValue = message;
    }
    return message;
  };
  const isPrivate = document.querySelector("#blog_private").innerText === "private" ? 1 : 0;
  const datetime = utc_time(document.querySelector("#blog_time").innerText);
  const params = new URLSearchParams();
  params.append("id", document.querySelector("#blog_id").innerText);
  params.append("category", window.category_id);
  params.append("private", isPrivate);
  params.append("time", datetime);
  params.append("title", html_2_bbcode(document.querySelector("#blog_title").innerText));
  params.append("blog", html_2_bbcode(document.querySelector("#blog_content").innerHTML));
  const msg = await fetch("ajax.php?edit_blog", {
    method: "POST",
    body: params
  }).then((e) => e.text());
  if (msg === "") {
    document.title = "SolarDay";
  } else {
    alert(msg); // eslint-disable-line no-alert
  }
  window.onbeforeunload = null;
};

const findNode = (list, node) => {
  for (let i = 0; i < list.length; i += 1) {
    if (list[i] === node) {
      return i;
    }
  }
  return -1;
};

const addemoticon = (src) => {
  document.querySelector("#emoticon").style.display = "none";
  document.querySelector("#blind").style.display = "none";

  let range = null;
  let selection = null;
  selection = window.getSelection();
  if (selection.getRangeAt && selection.rangeCount) {
    range = selection.getRangeAt(0);
    range.deleteContents();
    const image = document.createElement("img");
    image.className = "icon";
    image.src = src;
    range.insertNode(image);
    const newRange = document.createRange();
    const newSelection = window.getSelection();
    newRange.setStartAfter(image);
    newRange.collapse(true);
    newSelection.removeAllRanges();
    newSelection.addRange(newRange);
  }
};

document.querySelectorAll("#emoticon .icon").forEach((icon) => {
  icon.addEventListener("click", () => {
    addemoticon(icon.style.backgroundImage.slice(5, -2));
  });
});

const filesize = (bytes) => {
  let size = parseInt(bytes, 10);
  if (bytes > 1000000) {
    size = `${parseFloat(size / 1024 / 1024).toFixed(2)}MB`;
  } else if (bytes > 1000) {
    size = `${parseFloat(size / 1024).toFixed(2)}KB`;
  } else {
    size = `${parseFloat(size).toFixed(2)}Bytes`;
  }
  return size;
};


const fileHandler = (file) => {
  blindshow(document.querySelector("#messagebox"));
  const xmlHttpRequest = new XMLHttpRequest();
  xmlHttpRequest.upload.onloadstart = function (e) {
    const percent = parseInt(parseInt(e.loaded, 10) / parseInt(e.total, 10) * 100, 10);
    document.querySelector("#messagebox").innerHTML = `Uploading......<br>${file.name}<br><br>${filesize(e.loaded)}/${filesize(e.total)}<br><div style="text-align:right;border-top:1px solid #000;width:${percent * 3}px">${percent}%</div>`;
  };
  xmlHttpRequest.upload.onprogress = function (e) {
    if (e.lengthComputable) {
      const percent = parseInt(parseInt(e.loaded, 10) / parseInt(e.total, 10) * 100, 10);
      document.querySelector("#messagebox").innerHTML = `Uploading......<br>${file.name}<br><br>${filesize(e.loaded)}/${filesize(e.total)}<br><div style="text-align:right;border-top:1px solid #000;width:${percent * 3}px">${percent}%</div>`;
    }
  };
  xmlHttpRequest.onload = function (e) {
    let ext = (/[.]/).exec(file.name) ? (/[^.]+$/).exec(file.name) : undefined;
    ext = ext.toString().toLowerCase();

    let range = null;
    let selection = null;
    selection = window.getSelection();
    if (selection.getRangeAt && selection.rangeCount) {
      range = selection.getRangeAt(0);
      range.deleteContents();

      if (ext === "jpg" || ext === "png" || ext === "gif") {
        const br = document.createElement("br");
        const image = document.createElement("img");
        image.setAttribute("onclick", "show_photo(this.src);");
        image.className = "photo";
        image.src = `/pic/thumb_big/${this.responseText}.jpg`;
        range.insertNode(br);
        range.insertNode(image);

        const newRange = document.createRange();
        const newSelection = window.getSelection();
        newRange.setStartAfter(br);
        newRange.collapse(true);
        newSelection.removeAllRanges();
        newSelection.addRange(newRange);
      } else if (ext === "mp3") {
        const name = document.createTextNode(this.responseText.slice(0, -4));
        const br = document.createElement("br");
        const audio = document.createElement("audio");
        audio.setAttribute("controls", "controls");
        audio.setAttribute("type", "audio/mp3");
        audio.src = `/music/${this.responseText}`;
        range.insertNode(audio);
        range.insertNode(br);
        range.insertNode(name);

        const newRange = document.createRange();
        const newSelection = window.getSelection();
        newRange.setStartAfter(br);
        newRange.collapse(true);
        newSelection.removeAllRanges();
        newSelection.addRange(newRange);
      }
    }
    document.querySelector("#messagebox").style.display = "none";
    document.querySelector("#blind").style.display = "none";
  };
  xmlHttpRequest.open("POST", "ajax.php?upload", true);
  const formData = new FormData();
  formData.append("file", file);
  xmlHttpRequest.send(formData);
};

document.querySelector("#blog_content").ondragover = (ev) => {
  ev.preventDefault();
  ev.dataTransfer.dropEffect = "copy";
};

document.querySelector("#blog_content").ondrop = (ev) => {
  ev.preventDefault();
  const files = ev.dataTransfer.files;
  for (let i = 0; i < files.length; i += 1) {
    fileHandler(files[i]);
  }
};

const ctrl = (key, callback, args) => {
  document.addEventListener("keydown", (e) => {
    if (e.keyCode === key.charCodeAt(0) && e.ctrlKey) {
      e.preventDefault();
      callback.apply(this, args);
      return false;
    }
  });
};

ctrl("S", async () => {
  if (document.querySelector("#blog_content").contentEditable === "true") {
    await save();
  }
});

ctrl("E", async () => {
  await edit_blog();
});

ctrl("À", async () => { // backtick
  if (document.querySelector("#blog_content").contentEditable !== "true") {
    await add_blog();
  }
});

ctrl("D", async () => {
  await delete_blog();
});

ctrl("Q", () => {
  if (document.querySelector("#blog_content").contentEditable === "true") {
    blindshow(document.querySelector("#emoticon"));
  }
});

