/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.stylesSet.add( 'my_styles',
[
    // Inline styles
    { name : 'Box Color - Gray', element : 'div', attributes : { 'class' : 'box-a' } },
    { name : 'Box Color - Blue', element : 'div', attributes : { 'class' : 'box-b' } },
    { name : 'Font - Berthold Akzidenz', element : 'span', attributes : { 'class' : 'berthold_akzidenx' } },
    { name : 'Font - Pistilli', element : 'span', attributes : { 'class' : 'pistilli' } },
    { name : 'Font - Bodoni', element : 'span', attributes : { 'class' : 'bodoni' } },
]);

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	//config.contentsCss = url('site/style.php?page_id='+CURRENT_PAGE); 
	config.extraPlugins = 'stylesheetparser';
	config.contentsCss = url('site/ckeditor.php'); 
	config.stylesSet = [];
	
	config.toolbar = 'Pico';

	config.toolbar_Pico =[['Source', 'Maximize'],['Cut','Copy','Paste','PasteText','PasteFromWord','-','SpellChecker','Scayt'],['Find','Replace','-','SelectAll','RemoveFormat'],['Image','Table','HorizontalRule','SpecialChar','PageBreak'],'/',['Bold','Italic','Underline','Strike'],['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Link','Unlink','Anchor'],'/',['Styles','Format','Font','FontSize'],['TextColor','BGColor'],['Undo','Redo']];
	
	config.filebrowserBrowseUrl = url('includes/uploader/browse.php?');
	config.filebrowserImageBrowseUrl  = url('includes/uploader/browse.php?mode=image');
	
	config.removePlugins = 'resize,elementspath';
	config.height = 300;
	//config.stylesSet = 'my_styles';
};