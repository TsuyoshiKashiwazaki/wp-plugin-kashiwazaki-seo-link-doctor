jQuery(document).ready(function($) {

    // タブ切り替え
    $('.ksv-nav-tabs .nav-tab').on('click', function(e) {
        e.preventDefault();
        const tabId = $(this).data('tab');

        // タブのアクティブ状態を切り替え
        $('.ksv-nav-tabs .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // コンテンツの表示切り替え
        $('.ksv-tab-content').removeClass('ksv-tab-active');
        $('#' + tabId).addClass('ksv-tab-active');
    });

    loadContentList();

    // キャッシュクリアボタン
    $('#ksv-clear-cache-btn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).text('クリア中...');

        $.ajax({
            url: ksv_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ksv_clear_cache',
                nonce: ksv_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#ksv-cache-status').text(response.data.message).fadeIn().delay(3000).fadeOut();
                    // ローカルキャッシュもクリア
                    localStorage.removeItem('ksvLinkCache');
                    ksvLinkCache = {};
                } else {
                    $('#ksv-cache-status').text('エラーが発生しました').fadeIn().delay(3000).fadeOut();
                }
                btn.prop('disabled', false).text('ステータスキャッシュをクリア');
            },
            error: function() {
                $('#ksv-cache-status').text('通信エラー').fadeIn().delay(3000).fadeOut();
                btn.prop('disabled', false).text('ステータスキャッシュをクリア');
            }
        });
    });
    
    function loadContentList() {
        $('#ksv-content-list').html('<tr><td colspan="5" class="ksv-loading">読み込み中...</td></tr>');
        
        $.ajax({
            url: ksv_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ksv_get_content_list',
                nonce: ksv_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.lastContentList = response.data;
                    displayContentList(response.data);
                } else {
                    $('#ksv-content-list').html('<tr><td colspan="5">エラーが発生しました</td></tr>');
                }
            },
            error: function() {
                $('#ksv-content-list').html('<tr><td colspan="5">通信エラーが発生しました</td></tr>');
            }
        });
    }
    
    // localStorageキャッシュ管理
    function getCache() {
        try {
            return JSON.parse(localStorage.getItem('ksvLinkCache') || '{}');
        } catch (e) { return {}; }
    }
    function setCache(obj) {
        localStorage.setItem('ksvLinkCache', JSON.stringify(obj));
    }
    let ksvLinkCache = getCache();

    // サイト内コンテンツ一覧もソート可能に
    function makeContentTableSortable() {
        const $table = $('#ksv-content-table-container table');
        $table.find('th').css('cursor', 'pointer').off('click').on('click', function() {
            const idx = $(this).index();
            const rows = $table.find('tbody > tr').get();
            const asc = !$(this).hasClass('asc');
            $table.find('th').removeClass('asc desc');
            $(this).addClass(asc ? 'asc' : 'desc');
            rows.sort(function(a, b) {
                let A = $(a).children('td').eq(idx).text();
                let B = $(b).children('td').eq(idx).text();
                if (!isNaN(A) && !isNaN(B)) {
                    A = Number(A); B = Number(B);
                }
                if (A < B) return asc ? -1 : 1;
                if (A > B) return asc ? 1 : -1;
                return 0;
            });
            $.each(rows, function(i, row) {
                $table.children('tbody').append(row);
            });
        });
    }

    function displayContentList(contentList) {
        if (contentList.length === 0) {
            $('#ksv-content-list').html('<tr><td colspan="5">コンテンツが見つかりません</td></tr>');
            return;
        }
        let html = '';
        contentList.forEach(function(content) {
            const postId = content.id;
            html += '<tr>';
            html += '<td>' + escapeHtml(content.title) + '</td>';
            html += '<td><a href="' + content.url + '" target="_blank">' + escapeHtml(content.url) + '</a></td>';
            html += '<td>' + escapeHtml(content.post_type) + '</td>';
            html += '<td>' + content.date + '</td>';
            html += '<td>';
            html += '<div class="ksv-btn-group">';
            if (ksvLinkCache[postId]) {
                html += '<button class="ksv-button check-links-btn" data-post-id="' + postId + '" data-skip-cache="true">一括再チェック</button>';
                html += '<button class="ksv-button ksv-cache-btn" data-post-id="' + postId + '">結果を見る</button>';
            } else {
                html += '<button class="ksv-button check-links-btn" data-post-id="' + postId + '" data-skip-cache="false">リンクチェック</button>';
            }
            html += '</div>';
            html += '</td>';
            html += '</tr>';
        });
        $('#ksv-content-list').html(html);
        makeContentTableSortable();
    }
    
    // ローディングアニメーションHTML
    const loadingHtml = '<tr><td colspan="6" class="ksv-loading"><div class="ksv-loader"></div><span class="ksv-loading-label">リンクをチェック中...</span><div class="ksv-progress-bar"><div class="ksv-progress-bar-inner" id="ksv-progress-bar-inner"></div></div></td></tr>';

    // ボタンイベント
    $(document).on('click', '.check-links-btn', function() {
        const postId = $(this).data('post-id');
        const skipCache = $(this).data('skip-cache') === true || $(this).data('skip-cache') === 'true';
        checkPageLinks(postId, $(this), skipCache);
    });

    $(document).on('click', '.ksv-cache-btn', function() {
        const postId = $(this).data('post-id');
        if (ksvLinkCache[postId]) {
            displayLinkResults(ksvLinkCache[postId]);
        }
    });

    function checkPageLinks(postId, btn, skipCache) {
        btn.prop('disabled', true).html('<span class="ksv-btn-spinner"></span>抽出中...');

        // まずリンクを抽出
        $.ajax({
            url: ksv_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ksv_extract_links',
                post_id: postId,
                nonce: ksv_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    if (data.links.length === 0) {
                        btn.prop('disabled', false).text('リンクチェック');
                        alert('リンクが見つかりません');
                        return;
                    }
                    // 個別にチェック開始
                    checkLinksSequentially(postId, btn, data, skipCache);
                } else {
                    displayDebugError(response.data);
                    btn.prop('disabled', false).text('一括再チェック');
                }
            },
            error: function() {
                displayDebugError({ message: '通信エラーが発生しました' });
                btn.prop('disabled', false).text('一括再チェック');
            }
        });
    }

    function checkLinksSequentially(postId, btn, data, skipCache) {
        const links = data.links;
        const total = links.length;
        let checked = 0;
        let results = [];

        // 最初にスピナーとテキスト用のspanを設置
        btn.html('<span class="ksv-btn-spinner"></span><span class="ksv-progress-text"></span>');

        function updateProgress() {
            const pct = Math.round((checked / total) * 100);
            btn.find('.ksv-progress-text').text(checked + '/' + total + ' (' + pct + '%)');
        }

        function checkNext(index) {
            if (index >= total) {
                // 完了
                const finalData = {
                    post_id: postId,
                    post_title: data.post_title,
                    post_url: data.post_url,
                    links: results,
                    total_links: total,
                    error_links: results.filter(function(link) {
                        return link.status_code !== 200 || link.is_error;
                    })
                };
                ksvLinkCache[postId] = finalData;
                setCache(ksvLinkCache);
                btn.prop('disabled', false).text('一括再チェック');
                let $group = btn.closest('.ksv-btn-group');
                if ($group.find('.ksv-cache-btn').length === 0) {
                    $('<button class="ksv-button ksv-cache-btn" data-post-id="' + postId + '">結果を見る</button>').insertAfter(btn);
                }
                return;
            }

            const link = links[index];
            $.ajax({
                url: ksv_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ksv_check_single_link',
                    url: link.url,
                    skip_cache: skipCache ? 'true' : 'false',
                    nonce: ksv_ajax.nonce
                },
                success: function(response) {
                    checked++;
                    if (response.success) {
                        results.push(Object.assign({}, link, response.data));
                    } else {
                        results.push(Object.assign({}, link, { status_code: 0, is_error: true, error_message: 'Check failed' }));
                    }
                    updateProgress();
                    checkNext(index + 1);
                },
                error: function() {
                    checked++;
                    results.push(Object.assign({}, link, { status_code: 0, is_error: true, error_message: 'Request failed' }));
                    updateProgress();
                    checkNext(index + 1);
                }
            });
        }

        updateProgress();
        checkNext(0);
    }
    
    function animateProgressBar() {
        const bar = document.getElementById('ksv-progress-bar-inner');
        if (!bar) return;
        let width = 0;
        bar.style.width = '0%';
        let interval = setInterval(function() {
            if (width >= 100) {
                width = 10; // ループアニメ
            }
            width += 5;
            bar.style.width = width + '%';
        }, 100);
        // 結果表示時にクリア
        $(document).one('ksv-progress-stop', function() {
            clearInterval(interval);
            bar.style.width = '100%';
        });
    }
    
    function displayLinkResults(data) {
        $(document).trigger('ksv-progress-stop');
        const errorCount = Array.isArray(data.error_links) ? data.error_links.length : 0;
        $('#ksv-page-info').html(
            '<div class="ksv-page-info">' +
            '<h3>' + escapeHtml(data.post_title) + '</h3>' +
            '<p><strong>URL:</strong> <a href="' + data.post_url + '" target="_blank">' + escapeHtml(data.post_url) + '</a></p>' +
            '<p><strong>総リンク数:</strong> ' + data.total_links + ' | <strong>エラーリンク数:</strong> ' + errorCount + '</p>' +
            '</div>'
        );

        if (data.links.length === 0) {
            $('#ksv-links-list').html('<tr><td colspan="8">リンクが見つかりません</td></tr>');
        } else {
            let html = '';
            data.links.forEach(function(link, idx) {
                const statusClass = getStatusClass(link.status_code, link.is_error);
                const linkTypeClass = link.is_internal ? 'ksv-link-internal' : 'ksv-link-external';
                const linkTypeText = link.is_internal ? '内部' : '外部';
                const rowNum = idx + 1;
                const lineNo = link.line_no || '-';
                const isError = link.status_code !== 200 || link.is_error;
                let statusHtml = '';
                if (!isError) {
                    statusHtml = '<span class="ksv-status-200-code">' + link.status_code + '</span>';
                } else {
                    statusHtml = '<span class="ksv-status-error-code">' + (link.is_error ? 'ERR' : link.status_code) + '</span>';
                }
                const occurrence = link.occurrence || 1;
                const confirmLink = '<a href="#" class="ksv-highlight-link" data-post-id="' + data.post_id + '" data-href="' + escapeHtml(link.url) + '" data-occurrence="' + occurrence + '">確認</a>';
                const recheckBtn = '<button class="ksv-button ksv-single-recheck-btn" data-url="' + escapeHtml(link.url) + '" data-row-idx="' + idx + '">再</button>';
                const fullUrl = escapeHtml(link.url);
                const truncatedUrl = link.url.length > 50 ? escapeHtml(link.url.substring(0, 50)) + '...' : fullUrl;
                const needsTruncate = link.url.length > 50;
                let urlCell = '';
                if (needsTruncate) {
                    urlCell = '<span class="ksv-url-toggle" title="展開">▶</span>' +
                              '<a href="' + link.url + '" target="_blank" class="' + linkTypeClass + ' ksv-url-truncated">' + truncatedUrl + '</a>' +
                              '<a href="' + link.url + '" target="_blank" class="' + linkTypeClass + ' ksv-url-full" style="display:none;">' + fullUrl + '</a>';
                } else {
                    urlCell = '<a href="' + link.url + '" target="_blank" class="' + linkTypeClass + '">' + fullUrl + '</a>';
                }
                html += '<tr class="' + (isError ? 'ksv-row-error' : '') + '" data-row-idx="' + idx + '">';
                html += '<td>' + recheckBtn + '</td>';
                html += '<td>' + rowNum + '</td>';
                html += '<td>' + lineNo + '</td>';
                html += '<td>' + escapeHtml(link.text || '(なし)') + '</td>';
                html += '<td class="ksv-url-cell">' + urlCell + '</td>';
                html += '<td>' + linkTypeText + '</td>';
                html += '<td class="' + statusClass + '">' + statusHtml + '</td>';
                html += '<td>' + confirmLink + '</td>';
                html += '</tr>';
            });
            $('#ksv-links-list').html(html);
            makeTableSortable('#ksv-links-table-container table');
        }
        // モーダルを表示
        $('#ksv-link-modal').fadeIn(200, function() {
            $('html, body').animate({ scrollTop: $('#ksv-link-modal').offset().top - 30 }, 400);
        });
    }
    
    function displayDebugError(data) {
        let html = '<tr><td colspan="5">' + escapeHtml(data.message || 'エラーが発生しました') + '</td></tr>';
        if (data.debug) {
            html += '<tr><td colspan="5"><pre style="white-space:pre-wrap; color:#c00; background:#f9f9f9; border:1px solid #eee; padding:10px;">' + escapeHtml(data.debug) + '</pre></td></tr>';
        }
        $('#ksv-links-list').html(html);
        $('#ksv-link-modal').fadeIn(200);
    }
    
    // 結果モーダルの閉じる処理
    $(document).on('click', '#ksv-link-modal > .ksv-modal-content > .ksv-modal-close', function() {
        $('#ksv-link-modal').fadeOut(200);
    });
    $(document).on('click', '#ksv-link-modal', function(e) {
        if (e.target === this) {
            $('#ksv-link-modal').fadeOut(200);
        }
    });

    // URL展開トグル
    $(document).on('click', '.ksv-url-toggle', function() {
        const $cell = $(this).closest('.ksv-url-cell');
        const $truncated = $cell.find('.ksv-url-truncated');
        const $full = $cell.find('.ksv-url-full');
        const $toggle = $(this);

        if ($truncated.is(':visible')) {
            $truncated.hide();
            $full.show();
            $toggle.text('▼').attr('title', '折りたたむ');
        } else {
            $truncated.show();
            $full.hide();
            $toggle.text('▶').attr('title', '展開');
        }
    });

    // 個別再チェックボタン
    $(document).on('click', '.ksv-single-recheck-btn', function() {
        const btn = $(this);
        const url = btn.data('url');
        const rowIdx = btn.data('row-idx');
        const $row = btn.closest('tr');

        btn.prop('disabled', true).text('...');

        $.ajax({
            url: ksv_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ksv_check_single_link',
                url: url,
                skip_cache: 'true',
                nonce: ksv_ajax.nonce
            },
            success: function(response) {
                btn.prop('disabled', false).text('再');
                if (response.success) {
                    const result = response.data;
                    const isError = result.status_code !== 200 || result.is_error;
                    let statusHtml = '';
                    if (!isError) {
                        statusHtml = '<span class="ksv-status-200-code">' + result.status_code + '</span>';
                        $row.removeClass('ksv-row-error');
                    } else {
                        statusHtml = '<span class="ksv-status-error-code">' + (result.is_error ? 'ERR' : result.status_code) + '</span>';
                        $row.addClass('ksv-row-error');
                    }
                    // ステータス列を更新（7番目のtd）
                    $row.find('td').eq(6).html(statusHtml);
                    showToast('再チェック完了: ' + result.status_code);
                } else {
                    showToast('再チェックに失敗しました');
                }
            },
            error: function() {
                btn.prop('disabled', false).text('再');
                showToast('通信エラーが発生しました');
            }
        });
    });

    // 確認リンク：ハイライトプレビューを表示
    $(document).on('click', '.ksv-highlight-link', function(e) {
        e.preventDefault();
        const postId = $(this).data('post-id');
        const href = $(this).data('href');
        const occurrence = $(this).data('occurrence') || 1;

        showToast('プレビューを読み込み中...');

        $.ajax({
            url: ksv_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ksv_preview_highlight',
                post_id: postId,
                href: href,
                occurrence: occurrence,
                nonce: ksv_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const iframe = document.getElementById('ksv-preview-iframe');
                    iframe.srcdoc = response.data.html;
                    $('#ksv-preview-modal').fadeIn(200);
                } else {
                    showToast('プレビューの読み込みに失敗しました');
                }
            },
            error: function() {
                showToast('通信エラーが発生しました');
            }
        });
    });

    // プレビューモーダルを閉じる
    $(document).on('click', '.ksv-preview-close', function(e) {
        e.stopPropagation();
        $('#ksv-preview-modal').fadeOut(200);
        document.getElementById('ksv-preview-iframe').srcdoc = '';
    });
    $(document).on('click', '#ksv-preview-modal', function(e) {
        if (e.target === this) {
            e.stopPropagation();
            $('#ksv-preview-modal').fadeOut(200);
            document.getElementById('ksv-preview-iframe').srcdoc = '';
        }
    });

    // トースト通知
    function showToast(message) {
        let $toast = $('#ksv-toast');
        if (!$toast.length) {
            $toast = $('<div id="ksv-toast"></div>').appendTo('body');
        }
        $toast.text(message).addClass('show');
        setTimeout(function() {
            $toast.removeClass('show');
        }, 3000);
    }
    
    function getStatusClass(statusCode, isError) {
        if (isError) {
            return 'ksv-status-error';
        }
        if (statusCode === 200) {
            return 'ksv-status-200';
        }
        // 200以外はすべてエラー色
        return 'ksv-status-error';
    }
    
    function getStatusText(statusCode, isError, errorMessage) {
        if (isError) {
            return '接続エラー: ' + (errorMessage || '不明なエラー');
        }
        
        switch (statusCode) {
            case 200:
                return '正常';
            case 301:
                return 'リダイレクト (永続的)';
            case 302:
                return 'リダイレクト (一時的)';
            case 404:
                return 'ページが見つかりません';
            case 500:
                return 'サーバーエラー';
            case 503:
                return 'サービス利用不可';
            default:
                return 'HTTP ' + statusCode;
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function makeTableSortable(tableSelector) {
        const $table = $(tableSelector);
        $table.find('th').css('cursor', 'pointer').off('click').on('click', function() {
            const idx = $(this).index();
            const rows = $table.find('tbody > tr').get();
            const asc = !$(this).hasClass('asc');
            $table.find('th').removeClass('asc desc');
            $(this).addClass(asc ? 'asc' : 'desc');
            rows.sort(function(a, b) {
                let A = $(a).children('td').eq(idx).text();
                let B = $(b).children('td').eq(idx).text();
                if (!isNaN(A) && !isNaN(B)) {
                    A = Number(A); B = Number(B);
                }
                if (A < B) return asc ? -1 : 1;
                if (A > B) return asc ? 1 : -1;
                return 0;
            });
            $.each(rows, function(i, row) {
                $table.children('tbody').append(row);
            });
        });
    }

    $(document).on('click', '#ksv-copy-btn', function() {
        let tsv = '';
        // ヘッダー
        tsv += 'No.\t行\tリンクテキスト\tURL\tタイプ\tステータス\t確認URL\n';
        $('#ksv-links-list tr').each(function() {
            let row = [];
            $(this).find('td').each(function() {
                let txt = $(this).text().replace(/\s+/g, ' ').trim();
                row.push(txt);
            });
            if (row.length) tsv += row.join('\t') + '\n';
        });
        // コピー
        const temp = $('<textarea>').val(tsv).appendTo('body').select();
        document.execCommand('copy');
        temp.remove();
        $(this).text('コピーしました！');
        setTimeout(() => { $(this).text('コピー'); }, 1200);
    });
}); 