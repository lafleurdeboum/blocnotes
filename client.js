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
    var notesMenu = document.querySelector(".notes-nav");
    //notesMenu.innerHTML = "";
    notes.forEach(function(note) {
        var link = document.createElement("li");
        link.innerHTML = "<a href='?title=" + note + "'>" + note + "</a>";
        link.classList.add("nav-item");
        notesMenu.appendChild(link);
    });
}

function populateDocumentList(documents) {
    var docDivs = document.querySelectorAll(".docs-nav");
    console.log("nombre de nav de docs : " + docDivs.length);
    //docDivs.foreach(function(docDivs[i]) {
    for(var i=0; i < docDivs.length; i++) {
        docDivs[i].innerHTML = "";
        documents.forEach(function(fileinfo) {
            var newMedia = null;
            var link = document.createElement("span");
            var mediatype = fileinfo.filetype.split('/')[0];
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
            docDivs[i].appendChild(link);
        });
    }
}

function messageUser(message, messageType) {
    if (message) {
        var mainWindow = document.getElementById("main");
        var messageDiv = document.createElement("div");

        messageDiv.innerHTML = message;
        messageDiv.classList.add("alert",
                                 messageType,
                                 "alert-dismissible",
                                 "fade",
                                 "show");
        messageDiv.role = "alert"; // TODO check this attribute
        nav.appendChild(messageDiv);
        //mainWindow.prepend(messageDiv); // DEBUG fails on older browsers
        setTimeout(function() {
            nav.removeChild(messageDiv);
        }, 3000);
        console.log(message);
    }
}

function readNote(note) {
    // TODO Move some to an initialize function to be used in general.
    var readForm = noteReader.querySelector("form.readNote");
    var contentHolder = noteReader.querySelector("div.contentHolder");
    var uploadForm = noteEditor.querySelector("form.dropzone");
    var contentField = noteEditor.querySelector("textarea");

    readForm.title.value = note.title;
    uploadForm.title.value = note.title;
    contentHolder.innerHTML = note.comment;
    contentField.textContent = note.raw_comment;

    rightButton.textContent = "Supprimer"; // Delete
    rightButton.onclick = function(event) {
        noteReader.style.display = "none";
        deleteNote(note);
    }
    leftButton.textContent = "Modifier"; // Modify
    leftButton.onclick = function(event) {
        noteReader.style.display = "none";
        editNote(note);
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
    noteReader.style.display = "initial";
}

function deleteNote(note) {
    var deleteForm = noteReader.querySelector("form.deleteNote");

    deleteForm.title.value = note.title;
    query_engine(deleteForm, function(answer) {
        readNote(answer);
    });
}

function createNote(note) {
    // There's a "form.create" but we'll just tweak a form.modify form.
    var createForm = noteEditor.querySelector("form.modifyNote");

    editNote("Nouvelle note");
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
    var editForm = noteEditor.querySelector("form.modifyNote");

    editForm.title.value = note.title;
    leftButton.textContent = "Annuler"; // Cancel
    leftButton.onclick = function(event) {
        noteEditor.style.display = "none";
        readNote(note);
    }
    rightButton.textContent = "Enregistrer"; // Save
    rightButton.onclick = function(event) {
        query_engine(editForm, function(answer) {
            noteEditor.style.display = "none";
            readNote(answer);
        });
    }
    noteEditor.style.display = "initial";
}

document.addEventListener("DOMContentLoaded", function(event) {
    title = $_GET['title'] || "";
    var readForm = noteReader.querySelector("form.readNote");
    readForm.title.value = title;
    query_engine(readForm, readNote);
});

