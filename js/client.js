var $_GET = {}, args = location.search.substr(1).split(/&/);
for (var i=0; i<args.length; ++i) {
    var tmp = args[i].split(/=/);
    if (tmp[0] != "") {
        $_GET[decodeURIComponent(tmp[0])] = decodeURIComponent(
                tmp.slice(1).join("").replace("+", " "));
    }
}

function query_engine(form, callback) {
    //form.action = "engine/echo.php";
    if(form.action.search("delete.php") != -1) {
        target = form.title ? form.title.value : form.filename.value;
        if(! confirm("Voulez-vous vraiment supprimer " + target + " ?")) {
            return false;
        }
    }
    var callParameter = document.createElement("input");
    callParameter.type = "hidden";
    callParameter.name = "call";
    callParameter.value = form.getAttribute("call");
    form.appendChild(callParameter);
    console.log(form.call);
    const XHR = new XMLHttpRequest();
    const FD = new FormData(form);
    
    XHR.addEventListener("load", function(event) {
        var answer = null;
        try {
            answer = JSON.parse(event.target.responseText);
            answer.messages.forEach(function(message) {
                if(message.length == 1) {
                    messageUser(message[0], "alert-info", 4000);
                } else {
                    messageUser(message[0], message[1], 4000);
                }
            });
        } catch (err) {
            messageUser("Réponse de l'engin non valide :", "alert-danger");
            messageUser(event.target.responseText, "alert-danger", timeout=0);
        }
        callback(answer);
        /*
        history.pushState({
            title: answer.title
        }, "page 2", "?title=" + answer.title + "&action=" + form.action);
        */
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
        player.hidden = false;
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

function populateDocumentList(documents, noteTitle) {
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
                    newMedia.innerHTML = "<a href='documents/" + fileinfo.filename + "'>" + fileinfo.filename + "</a>";
                    link.appendChild(newMedia);
                    break;
                case "image":
                    var thumbnail = document.createElement("img");
                    thumbnail.src = "thumbnails/" + fileinfo.filename;
                    thumbnail.setAttribute("data-photo", "documents/" + fileinfo.filename);
                    thumbnail.setAttribute("data-photo-title", fileinfo.filename.replace(/\..+$/, ""));
                    thumbnail.setAttribute("data-photo-w", 1024);
                    thumbnail.setAttribute("data-photo-h", 768);
                    thumbnail.setAttribute("data-pswp-uid", 0);

                    newMedia = document.createElement("a");
                    newMedia.href = "documents/" + fileinfo.filename;
                    newMedia.appendChild(thumbnail);
                    link.appendChild(newMedia);
                    break;
            }
            if(docDiv.classList.contains("editable")) {
                var deleteDocForm = document.createElement("form");
                //deleteDocForm.action = "engine/note/detachDocument.php";
                deleteDocForm.action = "engine/engine.php";
                deleteDocForm.setAttribute("call", "document/delete.php");
                deleteDocForm.method = "post";
                deleteDocForm.hidden = true;
                deleteDocForm.classList.add("deleteDocument");
                var noteTitleInput = document.createElement("input");
                noteTitleInput.name = "title";
                noteTitleInput.value = noteTitle;
                deleteDocForm.appendChild(noteTitleInput);
                var filenameInput = document.createElement("input");
                filenameInput.name = "filename";
                filenameInput.value = fileinfo.filename;
                deleteDocForm.appendChild(filenameInput);
                var deleteButton = document.createElement("button");
                deleteButton.innerHTML = feather.icons["trash-2"].toSvg();
                deleteButton.classList.add("btn");
                deleteButton.onclick = function(event) {
                    query_engine(deleteDocForm, function(answer) {
                        populateDocumentList(answer.documents, answer.title);
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

function messageUser(message, messageType, timeout) {
    var messageDiv = document.createElement("div");

    if (! messageType) {
        messageType = "alert-info";
    }
    messageDiv.innerHTML = message;
    messageDiv.classList.add("alert",
                             messageType,
                             "fade",
                             "show");
    messageDiv.role = "alert"; // TODO check this attribute
    if(timeout || timeout != 0) {
        if(! timeout) { timeout = 3000; }
        setTimeout(function() {
            messager.removeChild(messageDiv);
        }, timeout);
    } else {
        closeSpan = document.createElement("span");
        closeSpan.classList.add("closeButton");
        closeSpan.innerHTML = feather.icons["x-circle"].toSvg();
        closeSpan.onclick = function() {
            messager.removeChild(messageDiv);
        }
        messageDiv.appendChild(closeSpan);
    }
    messager.appendChild(messageDiv);
    console.log(messageType + " : " + message);
}

function readNote(note) {
    // TODO Move some to an initialize function to be used in general.
    var contentField = noteEditor.querySelector("#modifyForm textarea");
    var contentHolder = noteReader.querySelector("div.contentHolder");

    readForm.title.value = note.title;
    titleInput.value = note.title;
    titleInput.hidden = true;
    contentHolder.innerHTML = note.comment;
    contentField.textContent = note.raw_comment;

    rightButton.innerHTML = feather.icons["edit"].toSvg();
    rightButton.onclick = function(event) {
        noteReader.hidden = true;
        editNote(note);
    }

    leftButton.hidden = true;

    //leftMostButton.innerHTML = feather.icons["plus-square"].toSvg();
    leftMostButton.innerHTML = feather.icons["file-plus"].toSvg();
    uploadCreateInput.onchange = function(event) {
        for(var i=0; i < uploadCreateInput.files.length; i++) {
            var file = uploadCreateInput.files[i];
            messageUser("uploading <b>" + file.name + "</b> ...");
            uploadCreateForm.title.value = file.name.replace(/\.\w+$/, "");
            query_engine(uploadCreateForm, function(answer) {
                title = answer.title;
                readNote(answer);
                console.log(file.name + " uploaded");
            });
        };
    };
    leftMostButton.onclick = function(event) {
        //noteReader.hidden = true;
        uploadCreateInput.click();
    };

    var noteNav = noteReader.querySelector('.notes-nav');
    if(note.title) {
        titleSpan.innerHTML = note.title;
        titleSpan.hidden = false;
        leftMostButton.hidden = true;
        noteNav.hidden = true;
    } else {
        leftMostButton.hidden = false;
        noteNav.hidden = false;
    }

    noteReader.hidden = false;
    feather.replace();
    populateDocumentList(note.documents, note.title);
    populateMenu(note.notes);
}

function deleteNote(note) {
    deleteForm.title.value = note.title;
    query_engine(deleteForm, function(answer) {
        populateMenu(answer.notes);
        readNote(answer);
    });
}

function createNote(note) {
    titleInput.placeholder = "Nouvelle note";
    titleInput.hidden = false;
    titleSpan.hidden = true;
    createForm.raw_comment.textContent = "";

    rightButton.innerHTML = feather.icons["save"].toSvg();
    rightButton.onclick = function(event) {
        createForm.title.value = titleInput.value;
        query_engine(createForm, function(answer) {
            noteCreator.hidden = true;
            title = answer.title;
            readNote(answer);
        });
    }
    leftButton.innerHTML = feather.icons["chevron-left"].toSvg();
    leftButton.onclick = function(event) {
        //createForm.title.type = "hidden";
        noteCreator.hidden = true;
        readNote(note);
    }
    rightButton.hidden = false;
    leftButton.hidden = false;
    leftMostButton.hidden = true;
    noteCreator.hidden = false;
}

function editNote(note) {
    titleInput.value = note.title;
    titleInput.hidden = false;
    titleSpan.hidden = true;

    modifyForm.old_title.value = note.title;

    // uploaded file will be linked to note. If the note name changes they are
    // relinked.
    uploadInput.onchange = function() {
        for(var i=0; i < uploadInput.files.length; i++) {
            var file = uploadInput.files[i];
            messageUser("uploading <b>" + file.name + "</b> ...");
            uploadForm.title.value = note.title;
            query_engine(uploadForm, function(answer) {
                console.log(file.name + " uploaded");
                populateDocumentList(answer.documents, answer.title);
            });
        }
    };
    uploadButton.innerHTML = feather.icons["upload"].toSvg();
    uploadButton.onclick = function() {
        uploadInput.click();
    }
    leftButton.innerHTML = feather.icons["chevron-left"].toSvg();
    leftButton.hidden = false;
    leftButton.onclick = function(event) {
        noteEditor.hidden = true;
        // DEBUG here we need to load view, not initialize it
        readNote(note);
    }
    rightButton.innerHTML = feather.icons["save"].toSvg();
    rightButton.onclick = function(event) {
        leftMostButton.onclick = null;
        // modifyForm calls modify.php who moves the note to the new title
        // and relinks all the docs.
        modifyForm.title.value = titleInput.value;
        query_engine(modifyForm, function(answer) {
            noteEditor.hidden = true;
            readNote(answer);
        });
    }
    leftMostButton.innerHTML = feather.icons["trash-2"].toSvg();
    leftMostButton.onclick = function(event) {
        noteEditor.hidden = true;
        deleteNote(note);
    }
    leftMostButton.hidden = false;
    noteEditor.hidden = false;
}

document.addEventListener("DOMContentLoaded", function(event) {
    var title = $_GET['title'] || "";

    readForm.title.value = title;

    query_engine(readForm, readNote);
});

