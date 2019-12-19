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
var notes = new Array();
var message;
var messageType;

if (!title) { title = ""; }


function readNote() {
  let commentDiv = document.getElementById("commentSections");
  let titleSpan = document.getElementById("titleSpan");
  engine("readNote", function(event) {
    readView();
    populateMenu();
    commentDiv.innerHTML = comment;
    commentField.textContent = raw_comment;
    if (title != "") {
      titleSpan.innerHTML = "<h4> > " + title + "</h4>";
    } else {
      titleSpan.innerHTML = "";
    }
    messageUser(message, messageType);
  });
}

function postNote() {
  let commentDiv = document.getElementById("commentSections");
  engine("postNote", function(event) {
    readView();
    populateMenu();
    commentDiv.innerHTML = comment;
    commentField.textContent = raw_comment;
    if (comment == "") {
      messageUser(message, messageType);
    }
  });
}

function deleteNote() {
  engine("deleteNote", function(event) {
    messageUser(message, messageType);
    title = "";
    titleField.value = "";
    readNote();
  });
}

function newNote() {
  editView();
  var titleField = document.getElementById("titleField");
  titleField.style.display = "block";
  titleField.type = "input";
  titleField.placeholder = "Nouvelle note";
}


function editView() {
  var editor = document.getElementById("editor");
  var comments = document.getElementById("comments");
  var titleField = document.getElementById("titleField");
  var commentField = document.getElementById("commentField");
  comments.style.display = "none";
  titleField.style.display = "none";
  commentField.style.display = "block";
  editor.style.display = "block";

  titleField.textContent = title;
  commentField.textContent = raw_comment;

  var saveButton = document.getElementById("leftButton");
  saveButton.textContent = "Enregistrer";
  saveButton.removeEventListener('click', newNote);
  saveButton.addEventListener('click', postNote);

  var modToggle = document.getElementById("leftMostButton");
  modToggle.textContent = "Annuler";
  modToggle.style.display = "inline";
  modToggle.removeEventListener('click', editView);
  modToggle.addEventListener('click', readView);
}

function readView() {
  var editor = document.getElementById("editor");
  var comments = document.getElementById("comments");
  comments.style.display = "block";
  editor.style.display = "none";

  var newNoteButton = document.getElementById("leftMostButton");
  newNoteButton.textContent = "Nouvelle note";
  newNoteButton.removeEventListener('click', readView);
  newNoteButton.addEventListener('click', newNote);
  if (title) {
    newNoteButton.style.display = "none";
  } else {
    newNoteButton.style.display = "inline";
  }

  var modToggle = document.getElementById("leftButton");
  modToggle.textContent = "Modifier";
  modToggle.removeEventListener('click', postNote);
  modToggle.addEventListener('click', editView);
}


function engine(action, callback) {
  let form = document.getElementById("editorForm");
  form.act.value = action;
  console.log(form);
  const XHR = new XMLHttpRequest();
  const FD = new FormData(form);

  XHR.addEventListener("load", function(event) {
    var answer = JSON.parse(event.target.responseText);
    comment = answer.comment;
    raw_comment = answer.raw_comment;
    notes = answer.notes;
    message = answer.message;
    messageType = answer.messageType;
    callback(event);
  });
  XHR.addEventListener("error", function(event) {
    var answer = JSON.parse(event.target.responseText);
    messageUser(answer.message, answer.messageType);
  });

  XHR.open("POST", "engine.php");
  XHR.send(FD);
}

function messageUser(message, messageType) {
  if (message) {
    let mainWindow = document.getElementById("main");
    let messageDiv = document.createElement("div");
    messageDiv.innerHTML = message;
    messageDiv.classList.add("alert",
                               messageType,
                               "alert-dismissible",
                               "fade",
                               "show");
    messageDiv.role = "alert"; // TODO check this attribute
    mainWindow.prepend(messageDiv);
    setTimeout(function() {
      mainWindow.removeChild(messageDiv);
    }, 3000);
    console.log(message);
  }
}

function populateMenu() {
  let menu = document.getElementById("menuList");
  menu.innerHTML = "";
  notes.forEach((note) => {
    let link = document.createElement("li");
    link.innerHTML = "<a href='?title=" + note + "'>" + note + "</a>";
    link.classList.add("nav-item");
    menu.appendChild(link);
  });
}

document.addEventListener("DOMContentLoaded", function(event) {

  var titleField = document.getElementById("titleField");
  var deleteButton = document.getElementById("rightButton");

  titleField.value = title;
  deleteButton.addEventListener('click', deleteNote);
  readNote();

});

