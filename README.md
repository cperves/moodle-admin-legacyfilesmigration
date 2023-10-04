# legacy_files_migration
a tool to migration legacyfiles (obsolete course files) into usual moodle files
## Goal
This plugin is designed in order to migrate legacy course files to private files area for a choosen user


## Author
Written for Université de Strasbourg(unistra.fr) by Celine Perves cperves@unistra.fr  
Inspired from "Nicolas Can" script see https://moodle.org/mod/forum/discuss.php?d=210415  


## Installation
Install the whole folder into admin/tool folder  


## Use
Navigation
Under root navigation will appear a new admin category name legacyfiles migration  
this has 2 submenu :  
* Legacy files migration settings : for chaning folder name  
* Legacy files migration with the tool  


## Migration
Legacy files migration will show a list of courses to migrate  
foreach of these you have to choose an editingteacher (if availabe) or fill a username  
The legacy files will be migrated into user selected private repository  


## Test
For test use you can automaticaly select first user of owner lists and fill username with a username  


## Features
migrate all legacy files into user private file areas  
change module resource references to files to the migrated files in user private area  
in case of folder referenced by a module resource, all the files and subfolders entries will automatically be created as module resource (in legacy files mode these were created at first consultation  


## Licence
 http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  
 http://www.cecill.info/licences/Licence_CeCILL_V2-en.html  
 
 Feel free to complete and improve this plugin  
 do not hesitate to fork and pull request  