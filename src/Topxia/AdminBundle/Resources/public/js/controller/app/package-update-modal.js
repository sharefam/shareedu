define(function(require, exports, module) {

    var Widget = require('widget');

    exports.run = function() {
        $('#modal').on('hidden.bs.modal', function (e) {
            window.location.reload();
        })
        
        var progressBar = new ProgressBar({
            element: '#package-update-progress'
        });

        var $updateBtn = $("#begin-update");

        var urls = $updateBtn.data();
        var steps = getQueue(urls);

        $.each(steps, function(i, step) {
            $(document).queue('update_step_queue', function() {
                exec(step.title, step.url, progressBar, step.progressRange[0], step.progressRange[1]);
            });
        });

        progressBar.on('completed', function() {
            progressBar.deactive();
            progressBar.text(Translator.trans('admin.app.package.update_success'));
            $("#updating-hint").hide();
            $("#finish-update-btn").show();
        });


        $updateBtn.click(function() {

            if(!confirm(Translator.trans('admin.app.package.update_confirm_message'))) {
                return;
            }

            $updateBtn.hide();
            $("#updating-hint").show();
            progressBar.show();

            $.post(urls.checkLastErrorUrl, function(result) {
                if (result === true) {
                    if(!confirm(Translator.trans('admin.app.package.check_last_error_confirm_message'))) {
                        $("#updating-hint").hide();
                        progressBar.hide();
                        $updateBtn.show();
                        return ;
                    }
                }
                $(document).dequeue('update_step_queue');
            }, 'json');

        });

        $("#finish-update-btn").click(function() {
            $(this).button('loading').addClass('disabled');
            setTimeout(function(){
                window.location.reload();
            }, 3000);
        });
    };


    function exec(title, url, progressBar, startProgress, endProgress) {
        progressBar.setProgress(startProgress, Translator.trans('admin.app.package.update_running',{title:title}));
        $.ajax(url, {
            async: true,
            dataType: 'json',
            type: 'POST'
        }).done(function(data, textStatus, jqXHR) {
            if (data.status == 'error') {
                progressBar.error(makeErrorsText(Translator.trans('admin.app.package.update_running_error',{title:title}), data.errors));
            } else if (typeof(data.index) != "undefined") {
                if (url.indexOf('index') < 0) {
                    url = url+'&index=0';
                }
                url = url.replace(/index=\d+/g,'index='+data.index);
                endProgress = startProgress + data.progress;
                if (endProgress > 100) {
                    endProgress = 100;
                }
                progressBar.setProgress(endProgress, Translator.trans('admin.app.package.update_running_success',{title:data.message}));
                startProgress = endProgress;
                title =  data.message;
                exec(title, url, progressBar, startProgress, endProgress);
            } else if(data.isUpgrade){
               $('.text-success').text(data.toVersion);
                var urls = $("#begin-update").data();
                steps = getQueue(urls);
                $.each(steps, function(i, step) {
                    var url = step.url.replace(/\d+/g,data.packageId);
                    $(document).queue('update_step_queue', function() {
                        exec(step.title, url, progressBar, step.progressRange[0], step.progressRange[1]);
                    });
                });
                $(document).dequeue('update_step_queue');
            } else {
                progressBar.setProgress(endProgress, Translator.trans('admin.app.package.update_running_success',{title:title}));
                $(document).dequeue('update_step_queue');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            if(title != '检查系统版本') {
                progressBar.error(Translator.trans('admin.app.package.update_error',{title:title}));
                $(document).clearQueue('update_step_queue');
            } else {
                progressBar.setProgress(endProgress, title + Translator.trans('admin.app.package.update_success'));
                $(document).dequeue('update_step_queue');
            }
        });
    }

    function makeErrorsText(title, errors) {
        var html = '<p>' + title + '<p>';
        html += '<ul>';
        $.each(errors, function(index, text) {
            html += '<li>' + text + '</li>';
        });
        html += '</ul>';
        return html;
    }

    var getQueue = function (urls){
        var steps = [
            {
                title: Translator.trans('admin.app.package.update.check_environment'),
                url: urls.checkEnvironmentUrl,
                progressRange: [3, 10]
            },
            {
                title: Translator.trans('admin.app.package.update.check_depends'),
                url: urls.checkDependsUrl,
                progressRange: [13, 20]
            },
            {
                title: Translator.trans('admin.app.package.update.backup_file'),
                url: urls.backupFileUrl,
                progressRange: [23, 30]
            },
            {
                title: Translator.trans('admin.app.package.update.backup_db'),
                url: urls.backupDbUrl,
                progressRange: [33, 40]
            },
            {
                title: Translator.trans('admin.app.package.update.check_download_extract'),
                url: urls.checkDownloadExtractUrl,
                progressRange: [43, 50]
            },
            {
                title: Translator.trans('admin.app.package.update.download_extract'),
                url: urls.downloadExtractUrl,
                progressRange: [53, 60]
            }
        ];


        var type = $("input[name='package-type']").val();
        if(type == 'upgrade'){
            var list = [
                {
                    title: Translator.trans('admin.app.package.update.begin_upgrade'),
                    url: urls.beginUpgradeUrl,
                    progressRange: [62, 94]
                },
                {
                    title: Translator.trans('admin.app.package.update.check_newest'),
                    url: urls.checkNewestUrl,
                    progressRange: [97, 100]
                }
            ];

            $.merge(steps,list);
        }else{
            steps.push({
                title: Translator.trans('admin.app.package.update.begin_upgrade'),
                url: urls.beginUpgradeUrl,
                progressRange: [62, 100]
            });
        }

        return steps;
    }

    var ProgressBar = Widget.extend({

        setProgress: function(progress, text) {
            this.$('.progress-bar').css({width: progress + '%'});
            this.$('.progress-text').html(text);

            if (progress >= 100) {
                this.trigger('completed');
            }
        },

        reset: function() {
            this.$('.progress-bar').css({width: '0%'});
            this.$('.progress-text').html('');
        },

        show: function() {
            this.element.show();
        },

        hide: function() {
            this.element.hide();
        },

        active: function() {
            this.$('.progress').addClass('active');
        },

        deactive: function() {
            this.$('.progress').removeClass('active');
        },

        text: function(text) {
            this.$('.progress-text').html(text);
        },

        error: function(text) {
            this.$('.progress-bar').addClass('progress-bar-danger');
            this.$('.progress-text').addClass('text-danger').html(text);
            this.deactive();
        },

        recovery: function() {
            this.$('.progress-bar').removeClass('progress-bar-danger');
            this.$('.progress-text').removeClass('text-danger').html('');
            this.active();
        },

        hasError: function() {
            return this.$('.progress-bar').hasClass('progress-bar-danger');
        }

    });

});
