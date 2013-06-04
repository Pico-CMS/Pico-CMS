/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config
	
	config.toolbar = [
		['Source'],['Cut','Copy','Paste','PasteText','PasteFromWord','-','SpellChecker'],['Find','Replace','-','SelectAll','RemoveFormat'],['Image','Table','HorizontalRule','SpecialChar'],['Undo','Redo'],'/',
		['Bold','Italic','Underline','Strike'],['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Link','Unlink','Anchor'],
		'/',['Styles','Format','Font','FontSize'],['TextColor','BGColor']];

	config.toolbar_inline =[
	['Strike','PasteText','RemoveFormat'],['Image','Table','HorizontalRule', 'NumberedList','BulletedList','Outdent','Indent','Blockquote'], ['Link', 'Unlink','Anchor'], '/', 
	['Format','FontSize', 'TextColor','BGColor'], ['JustifyCenter','JustifyRight','JustifyBlock'], ['Pico', 'PicoSave']];

	config.removePlugins = 'resize,elementspath';
	config.height = 300;

	config.filebrowserBrowseUrl = url('includes/uploader/browse.php?');
	config.filebrowserImageBrowseUrl  = url('includes/uploader/browse.php?mode=image');
	config.filebrowserWindowWidth     = '800';
    config.filebrowserWindowHeight    = '600';

	config.extraPlugins = 'stylesheetparser,font,justify,find,selectall,panelbutton,colordialog,colorbutton,pico,showborders';
	config.contentsCss = url('site/ckeditor.php'); 
	config.stylesSet = [];
};