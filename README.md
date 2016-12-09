### WonderCMS plugins development
- Approved plugins get upladed to the forum at: https://wondercms.com/forum

### List of currently approved plugins
- Trumbowyg (WYSIWYG editor) - author: Yassine Addi
- File uploader - author: Yassine Addi
- Simple hits counter - author: Yassine Addi

### Installation
1. Download and upload your chosen plugin, along with its folder into your WonderCMS plugins folder.

The plugin is then activated and will start working automatically.

### .htaccess in your plugins directory
1. To disable acccess to some files like hits.txt by random intruders, put the following code into an .htaccess document into your plugins directory.

`Deny from all`

### If any errors occur, please correct file permissions to 644 and folder permissions to 755. You can do this manually or with the script below (added by Bill Carson)
  - `find ./ -type d -exec chmod 755 {} \;`
  - `find ./ -type f -exec chmod 644 {} \;`

### WonderCMS website
- https://wondercms.com

### WonderCMS community
- https://wondercms.com/forum/

### WonderCMS plugins forum
- https://wondercms.com/forum/viewforum.php?f=30

### WonderCMS themes repository
- https://github.com/robiso/wondercms-themes

### WonderCMS themes forum
- https://wondercms.com/forum/viewforum.php?f=29
