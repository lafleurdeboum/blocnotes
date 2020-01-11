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
    if(form.action.search("delete.php") != -1) {
        // Confirm deletion :
        target = form.title ? form.title.value : form.filename.value;
        if(! confirm("Voulez-vous vraiment supprimer " + target + " ?")) {
            return false;
        }
    }
    const XHR = new XMLHttpRequest();
    const FD = new FormData(form);
    
    XHR.addEventListener("load", function(event) {
        var answer = null;
        try {
            answer = JSON.parse(event.target.responseText);
        } catch (err) {
            answer = Array();
            answer.message = "L'engin n'a pas répondu";
            answer.messageType = "alert-warning";
        }
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
                deleteDocForm.hidden = true;
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
                             "fade",
                             "show");
    messageDiv.role = "alert"; // TODO check this attribute
    main.appendChild(messageDiv);
    //mainWindow.prepend(messageDiv); // DEBUG fails on older browsers
    setTimeout(function() {
        main.removeChild(messageDiv);
    }, 3000);
    /*
    */
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

    //leftMostButton.innerHTML = feather.icons["file-plus"].toSvg();
    leftMostButton.innerHTML = feather.icons["plus-square"].toSvg();
    uploadInput.onchange = function(event) {
        handleFiles(uploadInput.files);
    };
    leftMostButton.onclick = function(event) {
        noteReader.hidden = true;
        uploadInput.click();
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
    populateDocumentList(note.documents);
    populateMenu(note.notes);
}

function handleFiles(files) {
    for(var i=0; i < files.length; i++) {
        var file = files[i];
        console.log("uploading " + file.name);
        uploadForm.title.value = file.name.replace(/\.\w+$/, "");
        query_engine(uploadForm, function(answer) {
            title = answer.title;
            readNote(answer);
        });
    }
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

    var uploadForm = noteEditor.querySelector("form.dropzone");
    // uploaded file will be linked to note. If the note name changes they are
    // relinked.
    uploadForm.title.value = titleInput.value;
    uploadForm.dropzone.on('complete', function(event) {
        const answer = JSON.parse(event.xhr.response);
        console.log("adding documents to doclist : ");
        populateDocumentList(answer.documents);
    });
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
    dropzoneMessage = document.querySelector(".dz-message");
    dropzoneMessage.innerHTML = feather.icons['upload'].toSvg();
    noteEditor.hidden = false;
}

document.addEventListener("DOMContentLoaded", function(event) {
    var title = $_GET['title'] || "";

    readForm.title.value = title;

    query_engine(readForm, readNote);
});

