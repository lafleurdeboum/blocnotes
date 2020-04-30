# blocnotes

Just another sort of pastebin for the moment, nothing fancy. Supports Markdown
though.

## Requirements

You will need a server that does html with php+sqlite support ; all the comments
will be stored in an sqlite database (namely, `admin/notes.db`). Your users will
need a web browser. Javascript support should be optional (though recommended
for decent user experience).

## Install

`git clone` it somewhere with http access. make shure the http server has write
access to `admin` and `admin/notes.db`, and read access to `engine/engine.php`,
`index.html` and `client.js`. Then access through `index.html` online. In case
something goes wrong with the DB, you can access `admin` to get a sql web
interface. Be shure to set the password in `admin/phpsqliteadmin.config.php`.

