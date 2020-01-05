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
function getLocation(href) {
    a = document.createElement("a");
    a.href = href;
    // The "a" element automatically gets a complete URL as href.
    return a.href;
}
function togglePlay(file, event) {
    button = event.target;
    //player = document.querySelector("#player");
    fileLocation = getLocation(file);
    if(fileLocation != player.src) {
        button.innerHTML = feather.icons["pause"].toSvg();
        player.src = file;
        player.style.display = "inline";
        player.play();
        console.log("playing " + file);
    } else {
        if(player.paused) {
            button.innerHTML = feather.icons["pause"].toSvg();
            player.play();
            console.log("resuming " + file);
        } else {
            button.innerHTML = feather.icons["play"].toSvg();
            player.pause();
            console.log("pausing " + file);
        }
    }
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
                    if(! docDiv.classList.contains("editable")) {
                        button = document.createElement("button");
                        fileLocation = getLocation("documents/" + fileinfo.filename);
                        if(fileLocation != player.src) {
                            button.innerHTML = feather.icons["play"].toSvg();
                        } else {
                            button.innerHTML = feather.icons["pause"].toSvg();
                        }
                        button.onclick = function(event) {
                            togglePlay('documents/' + fileinfo.filename, event);
                        };
                        button.classList.add("btn");
                        link.appendChild(button);
                    }
                    newMedia = document.createElement("span");
                    newMedia.innerHTML = fileinfo.filename;
                    link.appendChild(newMedia);
                    break;
                case "image":
                    newMedia = "<a href='documents/" + fileinfo.filename + "'><img src='documents/" + fileinfo.filename + "' width=100 height=100 /></a>";
                    link.innerHTML = newMedia;
                    break;
            }
            if(docDiv.classList.contains("editable")) {
                var deleteDocForm = document.createElement("form");
                //deleteDocForm.action = "engine/note/detachDocument.php";
                deleteDocForm.action = "engine/document/delete.php";
                deleteDocForm.method = "post";
                deleteDocForm.style.display = "none";
                deleteDocForm.classList.add("deleteDocument");
                var filenameInput = document.createElement("input");
                filenameInput.name = "filename";
                filenameInput.value = fileinfo.filename;
                filenameInput.type = "hidden";
                deleteDocForm.appendChild(filenameInput);
                var deleteButton = document.createElement("button");
                deleteButton.innerHTML = feather.icons["trash-2"].toSvg();
                deleteButton.classList.add("btn");
                deleteButton.onclick = function(event) {
                    query_engine(deleteDocForm, function(answer) {
                        populateDocumentList(answer.documents);
                    });
                };
                link.appendChild(deleteDocForm);
                link.appendChild(deleteButton);
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

    rightButton.innerHTML = feather.icons["edit"].toSvg();
    rightButton.onclick = function(event) {
        noteReader.style.display = "none";
        editNote(note);
    }
    /*
    leftButton.textContent = "Supprimer"; // Modify
    leftButton.onclick = function(event) {
        noteReader.style.display = "none";
        deleteNote(note);
    }
    */
    leftButton.style.display = "none";

    //leftMostButton.innerHTML = feather.icons["file-plus"].toSvg();
    leftMostButton.innerHTML = feather.icons["plus-square"].toSvg();
    leftMostButton.onclick = function(event) {
        noteReader.style.display = "none";
        createNote(note);
    }

    if(note.title) {
        displayTitle(": " + note.title);
        leftMostButton.style.display = "none";
    } else {
        leftMostButton.style.display = "inherit";
    }
    if (note.documents) {
        populateDocumentList(note.documents);
    }
    if (note.notes) {
        populateMenu(note.notes);
    }
    noteReader.style.display = "inherit";
    feather.replace();
}

function deleteNote(note) {
    var deleteForm = noteReader.querySelector("form.deleteNote");

    deleteForm.title.value = note.title;
    query_engine(deleteForm, function(answer) {
        populateMenu(answer.notes);
        readNote(answer);
    });
}

function createNote(note) {
    // There's a "form.create" but we'll just tweak a form.modify form.
    editNote(note);
    leftMostButton.style.display = "none";
    var createForm = noteEditor.querySelector("form.modifyNote");

    createForm.action = "engine/note/create.php";
    createForm.title.type = "input";
    createForm.title.value = "";
    createForm.title.placeholder = "nouvelle note";
    createForm.raw_comment.textContent = "";
    //rightButton.textContent = "Créer"; // Create
    rightButton.innerHTML = feather.icons["save"].toSvg();
    rightButton.onclick = function(event) {
        query_engine(createForm, function(answer) {
            createForm.title.type = "hidden";
            noteEditor.style.display = "none";
            readNote(answer);
        });
    }
    leftButton.innerHTML = feather.icons["chevron-left"].toSvg();
    leftButton.onclick = function(event) {
        createForm.title.type = "hidden";
        noteEditor.style.display = "none";
        readNote(note);
    }
    leftButton.style.display = "inherit";
}

function editNote(note) {
    var editForm = noteEditor.querySelector("form.modifyNote");

    editForm.action = "engine/note/modify.php";
    editForm.title.value = note.title;
    leftButton.innerHTML = feather.icons["chevron-left"].toSvg();
    leftButton.style.display = "inherit";
    //leftButton.textContent = "Annuler"; // Cancel
    leftButton.onclick = function(event) {
        noteEditor.style.display = "none";
        readNote(note);
    }
    //rightButton.textContent = "Enregistrer"; // Save
    rightButton.innerHTML = feather.icons["save"].toSvg();
    rightButton.onclick = function(event) {
        query_engine(editForm, function(answer) {
            noteEditor.style.display = "none";
            readNote(answer);
        });
    }
    leftMostButton.innerHTML = feather.icons["trash-2"].toSvg();
    leftMostButton.onclick = function(event) {
        noteEditor.style.display = "none";
        deleteNote(note);
    }
    leftMostButton.style.display = "inherit";
    dropzoneMessage = document.querySelector(".dz-message");
    dropzoneMessage.innerHTML = feather.icons['upload'].toSvg();
    noteEditor.style.display = "inherit";
}

document.addEventListener("DOMContentLoaded", function(event) {
    title = $_GET['title'] || "";
    var readForm = noteReader.querySelector("form.readNote");
    readForm.title.value = title;
    query_engine(readForm, readNote);
});

