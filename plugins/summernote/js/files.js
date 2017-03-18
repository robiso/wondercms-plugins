// SummerNote plugin for WonderCMS, JavaScript
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('jquery'));
    } else {
        factory(window.jQuery);
    }
}(function ($) {

    var filetype = 'docs';

    $.extend($.summernote.plugins, {

        'files': function(context) {
            var self = this;

            self.filetype = '';
            self.file = '';
            self.range = '';

            var ui = $.summernote.ui;
            var $editor = context.layoutInfo.editor;
            var options = context.options;

            context.memo('button.files', function() {
                var button = ui.button({
                    contents: '<i class="glyphicon glyphicon-folder-open"/>',
                    tooltip: 'files',
                    click: context.createInvokeHandler('files.showDialog', 'docs')
                });
                var $files = button.render();
                return $files;
            });

            self.initialize = function() {
                var $container = options.dialogsInBody ? $(document.body) : $editor;

                var body =  '<div class="form-group row-fluid" id="filesDialog">'+
                '<div id="filesList" style="padding-left: 10px;"></div>'+
                '<form class="form-inline" id="fileUpload" enctype="multipart/form-data">'+
                '<div class="form-group" style="width: 100%">'+
                    '<label for="file">File of image or document to upload</label><input type="file" class="form-control file" name="file" id="file" style="width: 100%" />'+
                '</div>'+
                '<div id="fileUrlDiv" class="form-group" style="width: 100%; padding-top: 10px">'+
                    '<label for="fileUrl">URL of image or document</label><input type="text" class="form-control" name="fileUrl" id="fileUrl" style="width: 100%" />'+
                '</div>'+
                '</form>'+
                '</div>';
                var footer = '<button href="#" class="btn btn-primary ext-files-btn">OK</button>';

                self.$dialog = ui.dialog({
                    title: 'Files manager',
                    fade: options.dialogsFade,
                    body: body,
                    footer: footer
                }).render().appendTo($container);
            };

            this.destroy = function() {
                this.$dialog.remove();
                this.$dialog = null;
                this.$panel.remove();
                this.$panel = null;
            };

            self.showDialog = function(t) {
                context.invoke('editor.saveRange');

                self.$dialog.find('#file').val('');
                self.$dialog.find('#fileUrlDiv').val('');

                self
                .openDialog(t)
                .then(function(dialogData) {
                    ui.hideDialog(self.$dialog);
                    context.invoke('editor.restoreRange');
                })
                .fail(function() {
                    context.invoke('editor.restoreRange');
                });
            };

            self.openDialog = function(t) {

                self.filetype = t;
                self.file = '';
                self.range = context.invoke('editor.createRange');

                return $.Deferred(function(deferred) {
                    var $dialogBtn = self.$dialog.find('.ext-files-btn');

                    ui.onDialogShown(self.$dialog, function() {
                        context.triggerEvent('dialog.shown');

                        self.$dialog.find('.modal-title').text('Files manager: '+self.filetype);
                        self.fileList(context, self.filetype);

                        $dialogBtn
                        .click(function(event) {
                            event.preventDefault();

                            self.fileLocal = true;
                            if (self.file == '') {
                                if (self.$dialog.find('#fileUrl').val()!='') {
                                    self.file = self.$dialog.find('#fileUrl').val();
                                    self.fileLocal = false;
                                } else if (self.$dialog.find('#file').val()) {
                                    self.fileUpload(self.$dialog.find('#file'), self.filetype)
                                }
                            }
                            if (self.file != '') {
                                if (self.fileLocal) {
                                    var fileUrl = 'files/'+self.filetype+'/'+self.file;
                                } else {
                                    var fileUrl = self.file;
                                }
                                if (self.filetype=='images') {
                                    context.invoke('editor.restoreRange');
                                    context.invoke('editor.insertImage', fileUrl);
                                } else {
                                    context.invoke('editor.restoreRange');
                                    var node = document.createElement('a');
                                    $(node).attr('href', fileUrl).attr('target', '_blank').html(self.range.toString());
                                    context.invoke('editor.insertNode', node);
                                }
                                self.filetype = '';
                                self.file = '';
                                self.$dialog.find('#file').val('');
                                self.$dialog.find('#fileUrl').val('');
                            }
                            deferred.resolve({ action: 'Files dialog OK clicked...' });
                        });
                    });

                    ui.onDialogHidden(self.$dialog, function() {
                        $dialogBtn.off('click');
                        if (deferred.state() === 'pending') {
                            deferred.reject();
                        }
                    });

                    ui.showDialog(self.$dialog);
                });
            };

            self.fileList = function(context, type) {
                data = new FormData();
                data.append("do", 'ls');
                data.append("type", type);
                $.ajax({
                    type: "POST",
                    url: "plugins/summernote/file.php?do=ls&type="+type,
                    data: data,
                    cache: false,
                    contentType: false,
                    dataType: 'json',
                    processData: false,
                    success: function(l) {
                        if (type=='images') {
                            var html = '<div style="overflow-y: scroll; min-height: 140px;">';
                            jQuery.each(l, function(i, f) {
                                html = html + '<div class="fileItem text-center" file="'+f.replace(/ /g,"%20")+'" data-toggle="tooltip" title="Click to select image"><div class="thumb"><span><img class="pop" style="" src="files/images/'+f.replace(/ /g,"%20")+'" /></span></div>'+f+'</div>';
                            });
                            html = html + '</div>';
                        } else {
                            var html = '<div style="overflow-y: scroll; min-height: 140px;">';
                            jQuery.each(l, function(i, f) {
                                var icon = '';
                                if(f.indexOf('.')<0) {
                                    icon = '<i class="fa fa-file-o fa-5x"></i>';
                                } else {
                                    var ext = f.split('.');
                                    switch(ext[ext.length-1]) {
                                        case "txt": case "text":
                                            icon = '<i class="fa fa-file-text-o fa-5x"></i>'; break;
                                        case "doc": case "docx":
                                            icon = '<i class="fa fa-file-word-o fa-5x"></i>'; break;
                                        case "xls": case "xlsx":
                                            icon = '<i class="fa fa-file-excel-o fa-5x"></i>'; break;
                                        case "ppt": case "pptx":
                                            icon = '<i class="fa fa-file-archive-o fa-5x"></i>'; break;
                                        case "zip": case "rar": case "7z":
                                            icon = '<i class="fa fa-file-powerpoint-o fa-5x"></i>'; break;
                                        case "pdf":
                                            icon = '<i class="fa fa-file-pdf-o fa-5x"></i>'; break;
                                        default:
                                            icon = '<i class="fa fa-file-o fa-5x"></i>';
                                    }
                                }
                                html = html + '<div class="fileItem text-center" file="'+f.replace(/ /g,"%20")+'" data-toggle="tooltip" title="Click to select image"><div class="thumb"><span>'+icon+'</span></div>'+f+'</div>';
                            });
                            html = html + '</div>';
                        }
                        self.$dialog.find('#filesList').html(html);
                        self.$dialog.find('.fileItem').click(function() {
                            self.file = $(this).attr('file');
                            self.$dialog.find('.thumbselect').removeClass('thumbselect');
                            $(this).find('.thumb').addClass('thumbselect');
                        });
                    },
                    error: function(message) {
                        alert('Files listing error: '+message);
                    }
                });
            };

            self.fileUpload = function(files, type) {
                var f = $(files).parent().parent('form').get(0);
                data = new FormData(f);
                data.append("do", 'ul');
                data.append("type", type);
                $.ajax({
                    url: "plugins/summernote/file.php?do=ul&type="+type,
                    type: "POST",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(url) {
                        if (url.search('Ooops!')==-1) {
                            url = url.replace(/ /g,"%20");
                            if (type == 'images') {
                                context.invoke('editor.restoreRange');
                                context.invoke('editor.insertImage', url);
                            } else {
                                context.invoke('editor.restoreRange');
                                var node = document.createElement('a');
                                $(node).attr('href', url).attr('target', '_blank').html(self.range.toString());
                                context.invoke('editor.insertNode', node);
                            }
                        } else {
                            alert('File upload error: '+url);
                        }
                    },
                    error: function(message) {
                        alert('File upload error: '+message);
                    }
                });
            };
        },

        'doc': function(context) {
            var ui = $.summernote.ui;
            context.memo('button.doc', function() {
                var button = ui.button({
                    contents: '<i class="glyphicon glyphicon-file"/>',
                    tooltip: 'Document',
                    click: context.createInvokeHandler('files.showDialog', 'docs')
                });
                var $doc = button.render();
                return $doc;
            });
        },

        'image': function(context) {
            var ui = $.summernote.ui;
            context.memo('button.image', function() {
                var button = ui.button({
                    contents: '<i class="glyphicon glyphicon-picture"/>',
                    tooltip: 'Image',
                    click: context.createInvokeHandler('files.showDialog', 'images')
                });
                var $image = button.render();
                return $image;
            });
        }
    });
}));
