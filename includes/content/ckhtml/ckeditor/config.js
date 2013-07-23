/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config
	
	
	config.toolbar = [
		['Source'],['Cut','Copy','Paste','PasteText','PasteFromWord'],['Find','Replace','-','SelectAll','RemoveFormat'],['Image','Table','HorizontalRule','SpecialChar'],['Undo','Redo'],'/',
		['Bold','Italic','Underline','Strike'],['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Link','Unlink','Anchor'],
		'/',['Styles','Format','Font','FontSize'],['TextColor','BGColor','Scayt']];

	config.toolbar_inline =[
	['Strike','PasteText','RemoveFormat'],['Image','Table','HorizontalRule', 'NumberedList','BulletedList','Outdent','Indent','Blockquote'], ['Link', 'Unlink','Anchor'], '/', 
	['Format','FontSize', 'TextColor','BGColor'], ['JustifyCenter','JustifyRight','JustifyBlock'], ['Pico', 'PicoSave']];

	config.removePlugins = 'resize,elementspath,wsc';
	config.height = 300;

	config.filebrowserBrowseUrl = url('includes/uploader/browse.php?');
	config.filebrowserImageBrowseUrl  = url('includes/uploader/browse.php?mode=image');
	config.filebrowserWindowWidth     = '800';
    config.filebrowserWindowHeight    = '600';
    config.fontSize_sizes = '8/8px;9/9px;10/10px;11/11px;12/12px;13/13px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;30/30px;32/32px;34/34px;36/36px;38/38px;40/40px;42/42px;44/44px;46/46px;48/48px;50/50px;52/52px;54/54px;56/56px;58/58px;60/60px;62/62px;64/64px;66/66px;68/68px;70/70px;72/72px;74/74px;76/76px;78/78px;80/80px;';

	config.extraPlugins = 'stylesheetparser,font,justify,find,selectall,panelbutton,colordialog,colorbutton,pico,showborders,menubutton,scayt';
	config.contentsCss = url('site/ckeditor.php'); 
	config.stylesSet = [];
};