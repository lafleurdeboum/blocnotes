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
        if (answer.message) {
            if (answer.messageType) {
                messageUser(answer.message, answer.messageType);
            } else {
                messageUser(answer.message, "alert-success");
            }
        }
        callback(answer);
    });

    XHR.open("POST", form.action);
    XHR.send(FD);
}

function populateMenu(notes) {
    var noteMenus = document.querySelectorAll(".notes-nav");
    for(var i=0; i < noteMenus.length; i++) {
        var noteMenu = noteMenus[i];
        console.log(noteMenu);
        noteMenu.innerHTML = "";
        notes.forEach(function(note) {
            if(note != "") {
                var link = document.createElement("span");
                link.innerHTML = "<a href='?title=" + note + "'>" + note + "</a>";
                link.classList.add("nav-item");
                noteMenu.appendChild(link);
            }
        });
    }
}

function displayTitle(title) {
    var titleSpan = document.querySelector("#titleSpan");
    titleSpan.innerHTML = title;
}
function Play(file) {
    console.log(file);
    player = document.querySelector("#player");
    player.autoplay = true;
    player.src = file;
    player.style.display = "inline";
    console.log(player);
    //newMedia = "<audio controls src='" + file + "' width=100 height=100 />";
    //container.innerHTML += newMedia;
}

function populateDocumentList(documents) {
    var docDivs = document.querySelectorAll(".docs-nav");
    for(var i=0; i < docDivs.length; i++) {
        var docDiv = docDivs[i];
        docDiv.innerHTML = "";
        documents.forEach(function(fileinfo) {
            var newMedia = null;
            var link = document.createElement("span");
            var mediatype = fileinfo.filetype.split('/')[0];
            switch(mediatype) {
                case 'audio':
                case 'video':
                    //newMedia = "<video controls src='documents/" + fileinfo.filename + "' width=100 height=100 />";
                    //newMedia = "<a onclick=Play('documents/" + fileinfo.filename + "');><img src='icons/" + mediatype + ".svg' width=100 height=100 /></a>";
                    newMedia = document.createElement("span");
                    button = document.createElement("button");
                    button.innerHTML = "<img src='icons/audio.svg'></img>" + fileinfo.filename;
                    button.onclick = function() {
                        Play('documents/' + fileinfo.filename);
                    };
                    button.classList.add("btn");
                    button.classList.add("btn-secondary");
                    newMedia.appendChild(button);
                    //newMedia.innerHTML += fileinfo.filename;
                    //link.innerHTML = newMedia;
                    //newMedia.onload = () => { link.appendChild(newMedia); };
                    link.appendChild(newMedia);
                    break;
                case "image":
                    newMedia = "<a href='documents/" + fileinfo.filename + "'><img src='documents/" + fileinfo.filename + "' width=100 height=100 /></a>";
                    link.innerHTML = newMedia;
                /*
                case 'image':
                    newMedia = new Image();
                    newMedia.onload = function() {
                        link.appendChild(newMedia);
                    }
                    newMedia.width = '100';
                    newMedia.height = '100';
                    newMedia.src = "documents/" + fileinfo.filename;
                    break;
                */
            }
            if(docDiv.classList.contains("editable")) {
                var editDocListForm = document.createElement("form");
                //editDocListForm.action = "engine/note/detachDocument.php";
                editDocListForm.action = "engine/document/delete.php";
                editDocListForm.method = "post";
                editDocListForm.classList.add("deleteDocument");
                var filenameInput = document.createElement("input");
                filenameInput.name = "filename";
                filenameInput.value = fileinfo.filename;
                filenameInput.type = "hidden";
                editDocListForm.appendChild(filenameInput);
                var submitButton = document.createElement("button");
                submitButton.onclick = function(event) {
                    query_engine(editDocListForm, function(answer) {
                        populateDocumentList(answer.documents);
                    });
                };
                link.appendChild(editDocListForm);
                link.appendChild(submitButton);
            }
            link.classList.add("nav-item");
            link.classList.add('document');
            link.classList.add(mediatype);
            docDivs[i].appendChild(link);
        });
    }
}

function messageUser(message, messageType) {
    //var mainWindow = document.getElementById("main");
    var navStart = nav.querySelector("span");
    var messageDiv = document.createElement("div");

    messageDiv.innerHTML = message;
    messageDiv.classList.add("alert",
                             messageType,
                             "alert-dismissible",
                             "fade",
                             "show");
    messageDiv.role = "alert"; // TODO check this attribute
    navStart.appendChild(messageDiv);
    //mainWindow.prepend(messageDiv); // DEBUG fails on older browsers
    setTimeout(function() {
        navStart.removeChild(messageDiv);
    }, 3000);
    console.log(messageType + " : " + message);
}

function readNote(note) {
    // TODO Move some to an initialize function to be used in general.
    var readForm = noteReader.querySelector("form.readNote");
    var uploadForm = noteEditor.querySelector("form.dropzone");
    var contentField = noteEditor.querySelector("form.modifyNote textarea");
    var contentHolder = noteReader.querySelector("div.contentHolder");

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

    if(note.title) {
        displayTitle(": " + note.title);
        leftMostButton.style.display = "none";
    } else {
        leftMostButton.style.display = "initial";
    }
    if (note.documents) {
        populateDocumentList(note.documents);
    }
    if (note.notes) {
        populateMenu(note.notes);
    }
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
    editNote(note);
    var createForm = noteEditor.querySelector("form.modifyNote");

    createForm.title.type = "input";
    createForm.title.value = note.title;
    createForm.action = "engine/note/create.php";
    rightButton.textContent = "Créer"; // Create
    rightButton.onclick = function(event) {
        query_engine(createForm, function(answer) {
            createForm.title.type = "hidden";
            noteEditor.style.display = "none";
            readNote(answer);
        });
    }
    console.log(rightButton);
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

