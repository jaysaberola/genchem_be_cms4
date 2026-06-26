/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.dtd.$removeEmpty['span'] = false;
CKEDITOR.dtd.$removeEmpty['i'] = false;
CKEDITOR.dtd.$removeEmpty['a'] = false;
CKEDITOR.editorConfig = function(config) {

    // %REMOVE_START%
    // The configuration options below are needed when running CKEditor from source files.
    // config.plugins = 'dialogui,dialog,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,notification,button,toolbar,clipboard,panelbutton,panel,floatpanel,colorbutton,colordialog,templates,menu,contextmenu,copyformatting,resize,elementspath,enterkey,entities,popup,filetools,filebrowser,find,fakeobjects,floatingspace,listblock,richcombo,font,format,horizontalrule,htmlwriter,iframe,wysiwygarea,image,indent,indentblock,indentlist,justify,menubutton,language,link,list,liststyle,magicline,maximize,pagebreak,pastetext,pastefromword,preview,print,removeformat,selectall,showblocks,showborders,sourcearea,scayt,stylescombo,tab,table,tabletools,tableselection,undo,lineutils,widgetselection,widget,notificationaggregator,uploadwidget,uploadimage,wsc,imagebrowser';
    config.removeButtons = 'Save,NewPage,Form,Templates,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,Flash,Smiley,SpecialChar,About';

    config.skin = 'moono-lisa';
    config.toolbarGroups = [
        { name: 'document', groups: ['mode', 'document', 'doctools'] },
        { name: 'clipboard', groups: ['clipboard', 'undo'] },
        { name: 'editing', groups: ['find', 'selection', 'spellchecker', 'editing'] },
        { name: 'forms', groups: ['forms'] },
        { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
        { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph'] },
        { name: 'links', groups: ['links'] },
        { name: 'insert', groups: ['insert'] },
        { name: 'styles', groups: ['styles'] },
        { name: 'colors', groups: ['colors'] },
        { name: 'tools', groups: ['tools'] },
        { name: 'others', groups: ['others'] },
        { name: 'about', groups: ['about'] }
    ];
    // %REMOVE_END%

    // For laravel file manager upload to server
    config.filebrowserUploadMethod = 'form';

    config.allowedContent = true;
    config.protectedSource.push(/<i[^>]*><\/i>/g);
    config.protectedSource.push(/<span[^>]*><\/span>/g);
    config.protectedSource.push(/<a[^>]*><\/a>/g);

    var publicBase = (function () {
        if (typeof window.CMS_PUBLIC_URL === 'string' && window.CMS_PUBLIC_URL) {
            return window.CMS_PUBLIC_URL.replace(/\/$/, '');
        }

        var parts = window.location.pathname.split('/').filter(Boolean);
        var publicIdx = parts.indexOf('public');

        if (publicIdx >= 0) {
            return window.location.origin + '/' + parts.slice(0, publicIdx + 1).join('/');
        }

        return window.location.origin;
    })();

    var themeBase = publicBase + '/theme/';
    var lfmBase = publicBase + '/laravel-filemanager';

    config.contentsCss = [themeBase + 'plugins/bootstrap/css/bootstrap.css',
        themeBase + 'plugins/fontawesome/css/all.min.css',
        themeBase + 'plugins/linearicon/linearicon.min.css',
        themeBase + 'plugins/responsive-tabs/css/responsive-tabs.css',
        themeBase + 'plugins/slick/slick.css',
        themeBase + 'plugins/slick/slick-theme.css',
        themeBase + 'css/tagsinput.min.css',
        themeBase + 'plugins/rd-navbar/rd-navbar.css',
        themeBase + 'plugins/aos/dist/aos.min.css',
        themeBase + 'css/animate.min.css',
        themeBase + 'css/style.css'];

    config.filebrowserImageBrowseUrl = lfmBase + '?type=Images';
    config.filebrowserImageUpload = lfmBase + '/upload?type=Images';
    config.filebrowserBrowseUrl = lfmBase + '?type=Files';
    config.filebrowserUploadUrl = lfmBase + '/upload?type=Files';

    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';
};