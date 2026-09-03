(function($) {
    'use strict';

    // Store generated blog data globally
    var currentBlogData = null;

    // ========================================
    // TAB SWITCHING
    // ========================================
    $(document).on('click', '.gbbg-tab-btn', function() {
        var tab = $(this).data('tab');
        $('.gbbg-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.gbbg-tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });

    // ========================================
    // RANGE SLIDER VALUE DISPLAY
    // ========================================
    $(document).on('input', '.gbbg-range', function() {
        $(this).siblings('.gbbg-range-value').text($(this).val());
    });

    // ========================================
    // TOGGLE API KEY VISIBILITY
    // ========================================
    $(document).on('click', '.gbbg-toggle-visibility', function() {
        var target = $('#' + $(this).data('target'));
        var type = target.attr('type') === 'password' ? 'text' : 'password';
        target.attr('type', type);
        $(this).find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
    });

    // ========================================
    // TEST API CONNECTION
    // ========================================
    $(document).on('click', '#gbbg-test-api-btn', function() {
        var btn = $(this);
        var resultDiv = $('#gbbg-api-test-result');
        btn.addClass('loading');
        resultDiv.hide();

        $.post(gbbg_ajax.ajax_url, {
            action: 'gbbg_test_api',
            nonce: gbbg_ajax.nonce
        }, function(response) {
            btn.removeClass('loading');
            resultDiv.show();
            if (response.success) {
                resultDiv.removeClass('error').addClass('success').html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message);
            } else {
                resultDiv.removeClass('success').addClass('error').html('<span class="dashicons dashicons-warning"></span> ' + response.data.message);
            }
        }).fail(function() {
            btn.removeClass('loading');
            resultDiv.show().removeClass('success').addClass('error').html('<span class="dashicons dashicons-warning"></span> Network error. Please check your connection.');
        });
    });

    // ========================================
    // AI TOPIC SUGGESTIONS
    // ========================================
    $(document).on('click', '#gbbg-suggest-btn', function() {
        var btn = $(this);
        var container = $('#gbbg-topic-suggestions');
        btn.addClass('loading');
        container.hide().empty();

        $.post(gbbg_ajax.ajax_url, {
            action: 'gbbg_suggest_topics',
            nonce: gbbg_ajax.nonce
        }, function(response) {
            btn.removeClass('loading');
            if (response.success && response.data.topics) {
                var html = '<h3 style="margin-top:0;font-size:15px;color:#333;">&#128161; AI-Suggested Topics</h3>';
                response.data.topics.forEach(function(t) {
                    html += '<div class="gbbg-suggestion-item" data-topic="' + escHtml(t.topic) + '" data-keyword="' + escHtml(t.focus_keyword || '') + '">';
                    html += '<div class="gbbg-suggestion-topic">' + escHtml(t.topic) + '</div>';
                    html += '<div class="gbbg-suggestion-meta">';
                    if (t.focus_keyword) html += '<span>&#127919; ' + escHtml(t.focus_keyword) + '</span>';
                    if (t.target_audience) html += '<span>&#128101; ' + escHtml(t.target_audience) + '</span>';
                    if (t.search_intent) html += '<span>&#128269; ' + escHtml(t.search_intent) + '</span>';
                    html += '</div></div>';
                });
                container.html(html).slideDown(300);
            } else {
                container.html('<div class="gbbg-alert gbbg-alert-warning"><p>' + (response.data ? response.data.message : 'Failed to get suggestions.') + '</p></div>').slideDown(300);
            }
        }).fail(function() {
            btn.removeClass('loading');
            container.html('<div class="gbbg-alert gbbg-alert-warning"><p>Network error.</p></div>').slideDown(300);
        });
    });

    // Click suggestion to fill fields
    $(document).on('click', '.gbbg-suggestion-item', function() {
        $('#gbbg-topic').val($(this).data('topic'));
        $('#gbbg-focus-keyword').val($(this).data('keyword'));
        $(this).css({background: '#e8f5e9', borderColor: '#a5d6a7'});
    });

    // ========================================
    // GENERATE BLOG POST
    // ========================================
    $(document).on('click', '#gbbg-generate-btn', function() {
        var topic = $('#gbbg-topic').val().trim();
        if (!topic) {
            alert('Please enter a blog topic.');
            return;
        }

        var btn = $(this);
        btn.addClass('loading');

        // Show progress
        $('#gbbg-progress-container').slideDown(300);
        $('#gbbg-seo-card, #gbbg-preview-card').hide();
        animateProgress(0);

        var progressInterval = setInterval(function() {
            var current = parseFloat($('#gbbg-progress-fill').css('width')) / $('#gbbg-progress-fill').parent().width() * 100;
            if (current < 85) {
                animateProgress(current + Math.random() * 8);
            }
        }, 800);

        $.post(gbbg_ajax.ajax_url, {
            action: 'gbbg_generate_blog',
            nonce: gbbg_ajax.nonce,
            topic: topic,
            focus_keyword: $('#gbbg-focus-keyword').val().trim(),
            custom_instructions: $('#gbbg-custom-instructions').val().trim()
        }, function(response) {
            clearInterval(progressInterval);
            btn.removeClass('loading');

            if (response.success) {
                animateProgress(100);
                setTimeout(function() {
                    $('#gbbg-progress-container').slideUp(200);
                    displayResults(response.data);
                }, 500);
            } else {
                animateProgress(0);
                $('#gbbg-progress-container').slideUp(200);
                alert('Error: ' + (response.data ? response.data.message : 'Unknown error'));
            }
        }).fail(function() {
            clearInterval(progressInterval);
            btn.removeClass('loading');
            animateProgress(0);
            $('#gbbg-progress-container').slideUp(200);
            alert('Network error. Please try again.');
        });
    });

    function animateProgress(pct) {
        $('#gbbg-progress-fill').css('width', Math.min(100, pct) + '%');
    }

    // ========================================
    // DISPLAY RESULTS
    // ========================================
    function displayResults(data) {
        currentBlogData = data.blog_data;
        var seo = data.seo_result;

        // SEO Score
        var score = seo.score;
        var color = score >= 90 ? '#00c853' : (score >= 70 ? '#ffab00' : '#ff1744');
        var degrees = (score / 100) * 360;
        $('.gbbg-score-circle').css('background', 'conic-gradient(' + color + ' ' + degrees + 'deg, #e0e4ea ' + degrees + 'deg)');

        // Animate score number
        $({val: 0}).animate({val: score}, {
            duration: 1200,
            easing: 'swing',
            step: function() { $('#gbbg-score-number').text(Math.round(this.val)); },
            complete: function() { $('#gbbg-score-number').text(score); }
        });

        // SEO Checks
        var checksHtml = '';
        seo.checks.forEach(function(c) {
            var cls = 'gbbg-check-' + c.status;
            var icon = c.status === 'pass' ? '&#10004;' : (c.status === 'warn' ? '&#9888;' : '&#10008;');
            checksHtml += '<div class="gbbg-check-item ' + cls + '">';
            checksHtml += '<span class="gbbg-check-icon">' + icon + '</span>';
            checksHtml += '<span>' + escHtml(c.rule) + ': ' + escHtml(c.detail) + '</span>';
            checksHtml += '<span class="gbbg-check-score">' + c.score + '</span>';
            checksHtml += '</div>';
        });
        $('#gbbg-seo-checks').html(checksHtml);

        // Preview
        $('#gbbg-preview-title').text(currentBlogData.title || '');
        $('#gbbg-preview-meta-desc').text(currentBlogData.meta_description || '');
        $('#gbbg-preview-keyword').text(currentBlogData.focus_keyword || '');
        $('#gbbg-preview-wordcount').text(seo.word_count + ' words');
        $('#gbbg-preview-content').html(currentBlogData.full_content || currentBlogData.content_html || '');

        // Show cards with animation
        $('#gbbg-seo-card').slideDown(400);
        setTimeout(function() { $('#gbbg-preview-card').slideDown(400); }, 200);
    }

    // ========================================
    // PUBLISH / DRAFT
    // ========================================
    $(document).on('click', '#gbbg-publish-btn, #gbbg-draft-btn', function() {
        if (!currentBlogData) {
            alert('No blog data. Generate a post first.');
            return;
        }

        var btn = $(this);
        var status = (btn.attr('id') === 'gbbg-publish-btn') ? 'publish' : 'draft';
        btn.addClass('loading');

        $.post(gbbg_ajax.ajax_url, {
            action: 'gbbg_publish_post',
            nonce: gbbg_ajax.nonce,
            post_status: status,
            blog_data: currentBlogData
        }, function(response) {
            btn.removeClass('loading');
            if (response.success) {
                var notice = '<div class="gbbg-result-notice success">';
                notice += '<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message;
                if (response.data.edit_url) notice += ' <a href="' + response.data.edit_url + '">Edit Post</a>';
                if (response.data.view_url) notice += ' | <a href="' + response.data.view_url + '" target="_blank">View Post</a>';
                notice += '</div>';
                $('.gbbg-publish-bar').after(notice);
            } else {
                alert('Error: ' + (response.data ? response.data.message : 'Unknown error'));
            }
        }).fail(function() {
            btn.removeClass('loading');
            alert('Network error.');
        });
    });

    // ========================================
    // UTILITY
    // ========================================
    function escHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

})(jQuery);