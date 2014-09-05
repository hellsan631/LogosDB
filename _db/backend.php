<?php

date_default_timezone_set('America/New_York');

//@TODO change functions to use $options array instead of long constructors
//@TODO add the ability to change sorting
//@TODO getNext should be 1 object only, getNextList should handle pagination
//@TODO add new functions getPrevious, getPreviousList
//@TODO continue commenting
//@TODO modular file structure
//@TODO update should handle more robust datasets (thinks like an array of new data)

//Cool Stuff
//@TODO menu system overhaul - a more robust menu system with children and parents
//@TODO built into framework object chaining for deleting, MYSQL JOIN, and updating of objects
//@TODO creation of tables if they do not exist, automagically.
//@TODO backing up of data automagically
//@TODO page caching system
//@TODO data display should run on angular JS instead, perhaps using HTML5 storage to save selected object, then displaying it on page
//@TODO Email system for emailing datagrams and members
//@TODO re-encode line breaks in phpbb for the web application so that they display correctly on the forums
//@TODO angularJS data sorting using a dropdown

//PERMISSIONS
//@TODO Permissions System Overhaul
//@TODO Method Permissions Level
//@TODO permissions system overhaul+addition - The ability to embed permissions in code in a web page, and specify a name and a pLevel
//@TODO more robust permissions system that allows methods to have a permission set
//@TODO move permissions to the core instead of it being a regular database object
//@TODO all methods/pages should check to see if the there is a permission set, and if not, then ignore that permission
//@TODO guest permissions
//@TODO Groups and Group permissions
//@TODO negative permission levels

?>