const hex = (buffer) => {
  const hexCodes = [];
  const view = new DataView(buffer);
  for (let i = 0; i < view.byteLength; i += 4) {
    const value = view.getUint32(i);
    const stringValue = value.toString(16);
    const padding = "00000000";
    const paddedValue = (padding + stringValue).slice(-padding.length);
    hexCodes.push(paddedValue);
  }
  return hexCodes.join("");
};

const sha256 = (str) => crypto.subtle.digest("SHA-256", new TextEncoder("utf-8").encode(str)).then((hash) => hex(hash));
const login = async (pwd) => {
  if (pwd !== null) {
    const digest = await sha256(`solarday_${document.querySelector("#pwd").value}`);
    await fetch("ajax.php?login", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: `password=${digest}`
    });
    location.reload();
  }
};

const center = (element) => {
  element.style.position = "absolute";
  element.style.top = `${((window.innerHeight - element.clientHeight) / 2) + window.scrollY}px`;
  element.style.left = `${((window.innerWidth - element.clientWidth) / 2) + window.scrollX}px`;
};

const blindshow = (element) => {
  document.querySelector("#blind").style.display = "block";
  element.style.display = "block";
  center(element);
  document.querySelector("#blind").addEventListener("click", () => {
    document.querySelector("#blind").style.display = "none";
    element.style.display = "none";
  });
};

const loginButton = document.createElement("a");
loginButton.textContent = "login";
loginButton.addEventListener("click", () => {
  blindshow(document.querySelector("#login"));
  document.querySelector("#pwd").focus();
});

const fmt_time = (strtime, local = false) => {
  const week = [
    "日",
    "一",
    "二",
    "三",
    "四",
    "五",
    "六"
  ];
  const [
    datestr,
    timestr
  ] = strtime.split(" ");
  const d = new Date(datestr.split("-")[0], datestr.split("-")[1] - 1, datestr.split("-")[2]);
  d.setHours(timestr.split(":")[0], timestr.split(":")[1], timestr.split(":")[2]);
  if (!local) {
    d.setHours(d.getHours() - (d.getTimezoneOffset() / 60));
  }

  const date = `${d.getFullYear()}年 ${(d.getMonth() + 1).toString().padStart(2, "0")}月 ${d.getDate().toString().padStart(2, "0")}日`;
  const day = `星期${week[d.getDay()]}`;
  const apm = d.getHours() < 12 ? "AM" : "PM";
  if (d.getHours() >= 12) {
    d.setHours(d.getHours() - 12);
  }
  const time = `${d.getHours().toString().padStart(2, "0")}:${d.getMinutes().toString().padStart(2, "0")}:${d.getSeconds().toString().padStart(2, "0")}`;
  return `${date} (${day}) ${apm} ${time}`;
};

const fmt_shorttime = (strtime) => {
  if (strtime.split(" ").length === 3) {
    return strtime;
  }

  const [
    datestr,
    timestr
  ] = strtime.split(" ");
  const d = new Date(datestr.split("-")[0], datestr.split("-")[1] - 1, datestr.split("-")[2]);
  d.setHours(timestr.split(":")[0], timestr.split(":")[1], timestr.split(":")[2]);
  d.setHours(d.getHours() - (d.getTimezoneOffset() / 60));

  const date = `${d.getFullYear()}-${(d.getMonth() + 1).toString().padStart(2, "0")}-${(d.getDate().toString().padStart(2, "0"))}`;
  const apm = d.getHours() < 12 ? "AM" : "PM";
  if (d.getHours() >= 12) {
    d.setHours(d.getHours() - 12);
  }
  const time = `${d.getHours().toString().padStart(2, "0")}:${d.getMinutes().toString().padStart(2, "0")}:${d.getSeconds().toString().padStart(2, "0")}`;
  return `${date} ${apm} ${time}`;
};

const resize_photo = () => {
  document.querySelectorAll(".photo").forEach((element) => {
    let margin = 0;
    let max_height = 0;
    if (window.innerWidth < 530) {
      margin = 40;
    } else if (window.innerWidth < 760) {
      margin = 120;
    }
    max_height = (window.innerWidth - margin) / element.dataset.width * element.dataset.height;
    if (max_height > element.dataset.height) {
      max_height = element.dataset.height;
    }
    max_height -= max_height % 25;

    element.style.maxHeight = `${max_height}px`;
  });
};

window.onload = () => {
  document.body.style.backgroundImage = "url(/image/banner.jpg)";
};
document.querySelector("#blog_foot").innerHTML = "";
document.querySelector("#blog_foot").appendChild(loginButton);
document.querySelector("#login>form").addEventListener("submit", () => {
  login(document.querySelector("#pwd").value);
  return false;
});
document.querySelectorAll(".time").forEach((node) => {
  node.textContent = fmt_time(node.dataset.time);
});
document.querySelectorAll(".shorttime").forEach((node) => {
  node.textContent = fmt_shorttime(node.textContent);
});
window.onresize = resize_photo;
resize_photo();

document.addEventListener("turbolinks:load", () => {
  document.querySelectorAll(".time").forEach((node) => {
    node.textContent = fmt_time(node.dataset.time);
  });
  document.querySelectorAll(".shorttime").forEach((node) => {
    node.textContent = fmt_shorttime(node.textContent);
  });
  resize_photo();
  document.body.style.backgroundImage = "url(/image/banner.jpg)";
});

