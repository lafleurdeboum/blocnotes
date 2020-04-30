  - login+password protection

  - focus change on loads
  
  - keymaps : Esc, ctrl-s
  
  - tags
  
  - document rename
  - document relink
  
  - on note delete, delete exclusive documents
  
  ~ improve media display : image plugin (photoswipe ?)
  
  - treat documents uri to avoid special caracters
  - upload progress - see https://developer.mozilla.org/en-US/docs/Web/API/File/Using_files_from_web_applications
  
  - use engine js call to get article display - lets player and upload keep going
  
  - delete note/document undo
  
  - distinguish between raw_comment NULL or empty string in engine
  - view initialize separated from view load (objects ?)

# Done

  + #editNote textarea size on mobile
  
  + thumbnails folder in documents
  
  + on doc upload, only copy new file if it's recorded in DB
  + on document delete, purge thumbnails

  + on note modify, update timestamp with sqlite trigger
  + on note modify or add, refresh rss feed in an xml file ?
  + rss encoding
  
  + improve media display : catch-all embed condition

  + rename note in engine
  + modifiable title in nav bar
  + icons in menu, note list in index
  
  + improve docs view : meaningful border
  + improve docs view : spacings
  + improve docs view : upload button inline
  
  + delete note/document confirm

  + reorganize engine upside down : engine.php would require($call);
    $call being e.g. "note/read.php"
  
