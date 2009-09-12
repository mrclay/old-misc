NoCms : Just a file content editor

NoCms is a just a web-based editor for files in a single directory. You decide
what you want to use them for.

BASIC USE CASE
  Developer: 
  * Wants to add user-editable content blocks to a site.
  * Puts content in files in the "nocms/content" directory.
  * Uses a server-side language to include the files into the pages.
  * Sleeps w/o worry of kitchen-sink CMS vulnerabilities
  User: 
  * Can edit blocks at http://example.com/nocms/

FEATURES
  * No DB (blocks are files in "nocms/content")
  * Rich-text editor for HTML (CKEditor 3)
  * Configurable number of backups stored for each block

SETUP
  1 svn checkout http://mrclay.googlecode.com/svn/trunk/php/nocms/ nocms
  2 Put "nocms" in document_root somewhere.
  3 Put the "ckeditor" library directory inside "nocms".
  4 Put the "Zend" library directory inside "nocms/lib". (ideally v1.9.2)
  4.5 You may need to chmod 777 your "nocms/content" directory
  5 Put content with filenames like *(.block.html|.txt) in "nocms/content"
  6 Set a password in "nocms/config.php"
  7 Open http://example.com/nocms/

TODO
  * UI for reverting to backups
  * .inline.html (rich-text editor, but no block-level elements)
  * rewrite using Zend_Controller|View
  * multiple users, each w/ own content directory (but KISS)
  * passwordSalt option, show md5(pwd+salt) to user on login
  * Notifications after redirects. No ZF equivalent of
    http://www.solarphp.com/class/Solar_Session/getFlash() ?
  * Dev could set a single-use token and e-mail a "set password" link to user
  
GOALS
  * Don't do too much
  * As little non-library code as possible
  * Easy to use/extend/rebrand
