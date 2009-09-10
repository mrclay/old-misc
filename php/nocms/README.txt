NoCms : Just a file content editor

(WARNING : VERY ALPHA! DO NOT USE!)

NoCms is a just a web editor for files containing blocks of content. You build a small site and use PHP include/readfile (or equivalents from other languages) to include these files. 

FEATURES
  * no DB required
  * single user login
  * CKEditor for HTML blocks

SETUP
  * Place "nocms" in document_root somewhere.
  * Place the "ckeditor" library directory inside "nocms".
  * Place the "Zend" library directory inside "nocms/lib". (ideally v1.9.2)
  * You may need to chmod 777 your "nocms/content" directory
  * Place some text files named [title].block.html in "nocms/content"
  * Set a password in "nocms/config.php"
  * Open http://example.com/nocms/
