var $_GET = {}, args = location.search.substr(1).split(/&/);
for (var i=0; i<args.length; ++i) {
    var tmp = args[i].split(/=/);
    if (tmp[0] != "") {
        $_GET[decodeURIComponent(tmp[0])] = decodeURIComponent(
                tmp.slice(1).join("").replace("+", " "));
    }
}

function query_engine(form, callback) {
    //form.title.value = title;
    //form.action = "engine/echo.php";
    const XHR = new XMLHttpRequest();
    const FD = new FormData(form);
    
    XHR.addEventListener("load", function(event) {
        const answer = JSON.parse(event.target.responseText);
        messageUser(answer.message, answer.messageType);
        callback(answer);
    });

    XHR.open("POST", form.action);
    XHR.send(FD);
}

function populateMenu(notes) {
  let menu = nav.querySelector("ul.notes-nav");
  menu.innerHTML = "";
  notes.forEach((note) => {
    let link = document.createElement("li");
    link.innerHTML = "<a href='?title=" + note + "'>" + note + "</a>";
    link.classList.add("nav-item");
    menu.appendChild(link);
  });
}

function populateDocumentList(documents) {
  //let ul = document.getElementById("documentList");
  let ul = noteReader.querySelector("ul.docs-nav");
  ul.innerHTML = "";
  documents.forEach((fileinfo) => {
    let newMedia = null;
    let link = document.createElement("li");
    let mediatype = fileinfo.filetype.split('/')[0];
    switch(mediatype) {
      case 'audio':
      case 'video':
        newMedia = "<video controls src='documents/" + fileinfo.filename + "' width=100 height=100 />";
        link.innerHTML = newMedia;
        break;
      case 'image':
        newMedia = new Image();
        newMedia.onload = function() {
          link.appendChild(newMedia);
        }
        newMedia.width = '100';
        newMedia.height = '100';
        newMedia.src = "documents/" + fileinfo.filename;
        break;
    }
    link.classList.add("nav-item");
    ul.appendChild(link);
  });
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

function readNote(note) {
    // TODO Move some to an initialize function to be used in general.
    let readForm = noteReader.querySelector("form.read");
    let contentHolder = noteReader.querySelector("div.contentHolder");
    let uploadForm = noteEditor.querySelector("form.dropzone");
    let contentField = noteEditor.querySelector("textarea");

    readForm.title.value = note.title;
    uploadForm.title.value = note.title;
    contentHolder.innerHTML = note.comment;
    contentField.textContent = note.raw_comment;

    rightButton.textContent = "x"; // Delete
    rightButton.onclick = function(event) {
        noteReader.style.display = "none";
        deleteNote(note);
    }
    leftButton.textContent = "o"; // Modify
    leftButton.onclick = function(event) {
        noteReader.style.display = "none";
        editeNote(note);
    }
    leftMostButton.onclick = function(event) {
        noteReader.style.display = "none";
        createNote(note);
    }
    if (note.title == "") {
        leftMostButton.style.display = "initial";
    } else {
        leftMostButton.style.display = "none";
    }

    populateDocumentList(note.documents);
    populateMenu(note.notes);
    noteReader.style.display = "block";
}

function deleteNote(note) {
    let deleteForm = noteReader.querySelector("form.delete");

    deleteForm.title.value = note.title;
    query_engine(deleteForm, function(answer) {
        readNote(answer);
    });
}

function createNote(note) {
    // There's a "form.create" but we'll just tweak a form.modify form.
    let createForm = noteEditor.querySelector("form.modify");

    editeNote("Nouvelle note");
    createForm.title.type = "input";
    createForm.action = "engine/createNote.php";
    rightButton.onclick = function(event) {
        query_engine(createForm, function(answer) {
            createForm.title.type = "hidden";
            noteEditor.style.display = "none";
            readNote(answer);
        });
    }
}

function editNote(note) {
    let editForm = noteEditor.querySelector("form.modify");

    editForm.title.value = note.title;
    leftButton.textContent = "<"; // Cancel
    leftButton.onclick = function(event) {
        noteEditor.style.display = "none";
        readNote(note);
    }
    rightButton.textContent = ">"; // Save
    rightButton.onclick = function(event) {
        query_engine(editForm, function(answer) {
            noteEditor.style.display = "none";
            readNote(answer);
        });
    }
    noteEditor.style.display = "block";
}

document.addEventListener("DOMContentLoaded", function(event) {
    title = $_GET['title'] || "";
    let readForm = noteReader.querySelector("form.read");
    readForm.title.value = title;
    query_engine(readForm, readNote);
});

