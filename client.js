// TODO Make readArticle a GET method, so we can start it during loading
// TODO Deal with the flickering of the editor at load

var $_GET = {},
  args = location.search.substr(1).split(/&/);
for (var i=0; i<args.length; ++i) {
  var tmp = args[i].split(/=/);
  if (tmp[0] != "") {
    $_GET[decodeURIComponent(tmp[0])] = decodeURIComponent(
        tmp.slice(1).join("").replace("+", " "));
  }
}

var title = $_GET["title"];
var comment;
var articles = new Array();

if (!title) { title = ""; }

function logResult(text, alertType) {
  let message = document.createElement("div");
  message.classList.add("alert",
                             alertType,
                             "alert-dismissible",
                             "fade",
                             "show");
  message.role = "alert"; // TODO check this attribute
  message.innerHTML = text;
  let mainWindow = document.getElementById("main");
  mainWindow.prepend(message);
  setTimeout(function() {
    mainWindow.removeChild(message);
  }, 3000);
  console.log(text);
}

function populateMenu() {
  let menu = document.getElementById("menuList");
  menu.innerHTML = "";
  articles.forEach((article) => {
    let link = document.createElement("li");
    link.innerHTML = "<a href='?title=" + article + "'>" + article + "</a>";
    link.classList.add("nav-item");
    menu.appendChild(link);
  });
}

function readArticle() {
  // Request the note entitled "title" :
  // TODO should reload the menu (DEBUG) and modify URL.
  let form = document.getElementById("postComment");
  let commentDiv = document.getElementById("commentSections");
  form.title.value = title;
  const XHR = new XMLHttpRequest();
  const FD = new FormData(form);

  XHR.addEventListener("load", function(event) {
    readMode();
    var answer = JSON.parse(event.target.responseText);
    articles = answer.articles;
    populateMenu();
    commentDiv.innerHTML = answer.comment;
    comment = answer.raw_comment;
    commentField.textContent = comment;
    if (comment == "") {
      logResult(answer.alert, answer.alertType);
    }
  });
  XHR.addEventListener("error", function(event) {
    var answer = JSON.parse(event.target.responseText);
    logResult(answer.alert, answer.alertType);
  });

  XHR.open("POST", "readComment.php");
  XHR.send(FD);
}

function submitArticle() {
  let form = document.getElementById("postComment");
  let commentDiv = document.getElementById("commentSections");
  const XHR = new XMLHttpRequest();
  const FD = new FormData(form);

  XHR.addEventListener("load", function(event) {
    readMode();
    var answer = JSON.parse(event.target.responseText);
    commentDiv.innerHTML = answer.comment;
    comment = answer.raw_comment;
    if (comment == "") {
      logResult(answer.alert, answer.alertType);
    }
  });
  XHR.addEventListener("error", function(event) {
    var answer = JSON.parse(event.target.responseText);
    logResult(answer.alert, answer.alertType);
  });

  XHR.open("POST", "postComment.php");
  XHR.send(FD);
}

function deleteNote() {
  const XHR = new XMLHttpRequest();
  XHR.addEventListener("load", function(event) {
    var answer = JSON.parse(event.target.responseText);
    logResult(answer.alert, answer.alertType);
    title = "";
    readArticle();
  });
  XHR.open("GET", "deleteNote.php?title=" + title, true);
  XHR.send();
}

function editMode() {
  var editor = document.getElementById("editor");
  var comments = document.getElementById("comments");
  var titleField = document.getElementById("titleField");
  var commentField = document.getElementById("commentField");
  comments.style.display = "none";
  titleField.style.display = "none";
  commentField.style.display = "block";
  editor.style.display = "block";

  titleField.textContent = title;
  commentField.textContent = comment;

  var saveButton = document.getElementById("leftButton");
  saveButton.textContent = "Enregistrer";
  saveButton.removeEventListener('click', newNote);
  saveButton.addEventListener('click', submitArticle);

  var modToggle = document.getElementById("leftMostButton");
  modToggle.textContent = "Annuler";
  modToggle.style.display = "inline";
  modToggle.removeEventListener('click', editMode);
  modToggle.addEventListener('click', readMode);
}

function readMode() {
  var editor = document.getElementById("editor");
  var comments = document.getElementById("comments");
  comments.style.display = "block";
  editor.style.display = "none";

  var newNoteButton = document.getElementById("leftMostButton");
  newNoteButton.textContent = "Nouvelle note";
  newNoteButton.removeEventListener('click', readMode);
  newNoteButton.addEventListener('click', newNote);
  if (title) {
    newNoteButton.style.display = "none";
  } else {
    newNoteButton.style.display = "inline";
  }

  var modToggle = document.getElementById("leftButton");
  modToggle.textContent = "Modifier";
  modToggle.removeEventListener('click', submitArticle);
  modToggle.addEventListener('click', editMode);
}

function newNote() {
  editMode();
  var titleField = document.getElementById("titleField");
  titleField.style.display = "block";
  titleField.placeholder = "Nouvelle note";
  titleField.textContent = "ok";
}

document.addEventListener("DOMContentLoaded", function(event) {

  var editor = document.getElementById("editor");
  var comments = document.getElementById("comments");
  var titleSpan = document.getElementById("titleSpan");
  var titleField = document.getElementById("titleField");
  var commentField = document.getElementById("commentField");
  var deleteButton = document.getElementById("rightButton");

  if (title != "") {
    titleSpan.innerHTML = "<h4> > " + title + "</h4>";
  }
  deleteButton.addEventListener('click', deleteNote);
  readArticle();

});

