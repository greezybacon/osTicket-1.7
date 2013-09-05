if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

/* Generic draft support for osTicket. The plugins supports draft retrieval
 * automatically, along with draft autosave, and image uploading.
 *
 * Configuration:
 * draft_namespace: namespace for the draft retrieval
 * draft_object_id: extension to the namespace for draft retrieval
 *
 * Caveats:
 * Login (staff only currently) is required server-side for drafts and image
 * uploads. Furthermore, the id of the staff is considered for the drafts,
 * so one user will not retrieve drafts for another user.
 */
RedactorPlugins.draft = {
    init: function() {
        if (!this.opts.draft_namespace)
            return;

        this.opts.changeCallback = this.hideDraftSaved;
        var autosave_url = 'ajax.php/draft/' + this.opts.draft_namespace;
        if (this.opts.draft_object_id)
            autosave_url += '.' + this.opts.draft_object_id;
        this.opts.autosave = autosave_url;
        this.opts.autosaveInterval = 4;
        this.opts.autosaveCallback = this.setupDraftUpdate;
        this.opts.initCallback = this.recoverDraft;
        this.opts.imageGetJson = 'ajax.php/draft/images/browse';
        this.opts.syncBeforeCallback = this.captureImageSizes;
    },
    recoverDraft: function() {
        var self = this;
        $.ajax(this.opts.autosave, {
            dataType: 'json',
            statusCode: {
                200: function(json) {
                    self.draft_id = json.draft_id;
                    // Allow the operation to be undone
                    self.bufferSet();
                    // Relace the current content with the draft, sync, and make
                    // images editable
                    self.set(json.body);
                    self.observeImages();
                    self.setupDraftUpdate(json);
                    self.focus();
                },
                205: function() {
                    // Save empty draft immediately;
                    var ai = self.opts.autosaveInterval;

                    // Save immediately -- capture the created autosave
                    // interval and clear it as soon as possible. Note that
                    // autosave()ing doesn't happen immediately. It happens
                    // async after the autosaveInterval expires.
                    self.opts.autosaveInterval = 0;
                    self.autosave();
                    var interval = self.autosaveInterval;
                    setTimeout(function() {
                        clearInterval(interval);
                    }, 1);

                    // Reinstate previous autosave interval timing
                    self.opts.autosaveInterval = ai;
                }
            }
        });
    },
    setupDraftUpdate: function(data) {
        this.$box.parent().find('.draft-saved').show();

        if (typeof data != 'object')
            data = $.parseJSON(data);

        if (!data || !data.draft_id)
            return;

        $('input[name=draft_id]', this.$box.closest('form'))
            .val(data.draft_id);
        this.draft_id = data.draft_id;

        this.opts.imageUpload =
            'ajax.php/draft/'+data.draft_id+'/attach';
        this.opts.autosave = 'ajax.php/draft/'+data.draft_id;
    },

    hideDraftSaved: function() {
        this.$box.parent().find('.draft-saved').hide();
    },

    deleteDraft: function() {
        var self = this;
        $.ajax('ajax.php/draft/'+this.draft_id, {
            type: 'delete',
            success: function() {
                self.opts.autosave = '';
                self.opts.imageUpload = '';
                self.draft_id = undefined;
                clearInterval(self.autosaveInterval);
                self.hideDraftSaved();
                self.set('');
            }
        });
    },

    captureImageSizes: function(html) {
        $('img', this.$box).each(function(i, img) {
                // TODO: Rewrite the entire <img> tag. Otherwise the @width
                // and @height attributes will begin to accumulate
            src_re = $(img).attr('src').replace('?','\\?');
            html = html.replace(new RegExp(
                '<img ([^>]*)src="'+src_re+'"([^>]*)>'),
                function(i, a, b) {
                    if (!img.clientWidth) return i;
                    extra = a + ' ' + b;
                    extra = extra.replace(/(?:width|height)="[^"]+"\s*/gi, '')
                    extra = 'width="' + img.clientWidth
                        + '" height="' + img.clientHeight + '" ' + extra.trim();
                    return '<img src="' + $(img).attr('src') + '" ' + extra + '>';
                }
            );
        });
        return html;
    }
};

/* Redactor richtext init */
$(function() {
    var redact = function(el) {
        var el = $(el),
            options = {
                'air': el.hasClass('no-bar'),
                'autoresize': !el.hasClass('no-bar'),
                'plugins': ['fontcolor','fontfamily'],
            };
        if (el.hasClass('allow-images')) {
            var reset = $('input[type=reset]', el.closest('form')),
                draft_saved = $('<span>')
                .addClass("pull-right draft-saved faded")
                .css({'position':'relative','top':'-1.8em','right':'1em'})
                .hide()
                .append($('<span>')
                    .css({'position':'relative', 'top':'0.17em'})
                    .text('Draft Saved'));
            el.closest('form').append($('<input type="hidden" name="draft_id"/>'));
            if (reset)
                reset.click(function() {
                    el.redactor('set', '');
                });
            if (el.hasClass('draft-delete')) {
                draft_saved.append($('<span>')
                    .addClass('action-button')
                    .click(function() {
                        el.redactor('deleteDraft');
                        return false;
                    })
                    .append($('<i>')
                        .addClass('icon-trash')
                    )
                );
            }
            draft_saved.insertBefore(el);
            options['plugins'].push('draft');
            if (el.data('draftNamespace'))
                options['draft_namespace'] = el.data('draftNamespace');
            if (el.data('draftObjectId'))
                options['draft_object_id'] = el.data('draftObjectId');
        }
        el.redactor(options);
    }
    $('.richtext').each(function(i,el) {
        if ($(el).hasClass('ifhtml'))
            // Check if html_thread is enabled first
            getConfig().then(function(c) {
                if (c.html_thread)
                    redact(el);
            });
        else
            // Make a rich text editor immediately
            redact(el);
    });
});
