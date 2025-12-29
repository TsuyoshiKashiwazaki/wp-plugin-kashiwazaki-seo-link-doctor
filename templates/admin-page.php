<div class="wrap">
    <h1>Kashiwazaki SEO Link Doctor</h1>

    <!-- タブナビゲーション -->
    <nav class="nav-tab-wrapper ksv-nav-tabs">
        <a href="#" class="nav-tab nav-tab-active" data-tab="ksv-tab-checker">リンクチェック</a>
        <a href="#" class="nav-tab" data-tab="ksv-tab-manual">説明書</a>
    </nav>

    <!-- リンクチェックタブ -->
    <div id="ksv-tab-checker" class="ksv-tab-content ksv-tab-active">
        <div class="ksv-toolbar">
            <button id="ksv-clear-cache-btn" class="button button-secondary">ステータスキャッシュをクリア</button>
            <span id="ksv-cache-status"></span>
        </div>

        <div class="ksv-container">
            <div class="ksv-content-list">
                <h2>サイト内コンテンツ一覧</h2>
                <div id="ksv-content-table-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>タイトル</th>
                                <th>URL</th>
                                <th>投稿タイプ</th>
                                <th>日付</th>
                                <th>アクション</th>
                            </tr>
                        </thead>
                        <tbody id="ksv-content-list">
                            <tr>
                                <td colspan="5">読み込み中...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 説明書タブ -->
    <div id="ksv-tab-manual" class="ksv-tab-content">
        <div class="ksv-manual">
            <div class="ksv-manual-section">
                <h2>Kashiwazaki SEO Link Doctor とは</h2>
                <p>Kashiwazaki SEO Link Doctor は、WordPressサイト内のリンク切れを効率的に検出・管理するためのプラグインです。<br>
                サイト内のすべてのページに含まれるリンクをチェックし、404エラーやサーバーエラーなどの問題を素早く発見できます。</p>
            </div>

            <div class="ksv-manual-section">
                <h2>主な機能</h2>
                <div class="ksv-feature-grid">
                    <div class="ksv-feature-card">
                        <h3>リンクステータスチェック</h3>
                        <p>HTTPステータスコードを取得し、200（正常）以外のリンクをエラーとして検出します。HEADリクエストを使用した軽量なチェックで、サーバー負荷を最小限に抑えます。</p>
                    </div>
                    <div class="ksv-feature-card">
                        <h3>リアルタイム進捗表示</h3>
                        <p>リンクチェック中は進捗状況（n/n件、%）をリアルタイムで表示。大量のリンクがあるページでも、処理状況を把握できます。</p>
                    </div>
                    <div class="ksv-feature-card">
                        <h3>プレビュー機能</h3>
                        <p>「確認」ボタンをクリックすると、該当リンクをページ内でハイライト表示。赤枠と黄色背景で視覚的に位置を確認でき、自動スクロールで該当箇所まで移動します。</p>
                    </div>
                    <div class="ksv-feature-card">
                        <h3>キャッシュ機能</h3>
                        <p>リンクのステータス結果を24時間キャッシュ。同じURLを再チェックする際の時間を短縮します。キャッシュは手動でクリアすることも可能です。</p>
                    </div>
                    <div class="ksv-feature-card">
                        <h3>個別/一括再チェック</h3>
                        <p>エラーが修正された後、個別のリンクだけを再チェックしたり、ページ全体のリンクを一括で再チェックできます。</p>
                    </div>
                    <div class="ksv-feature-card">
                        <h3>結果のコピー</h3>
                        <p>チェック結果をタブ区切りテキストでクリップボードにコピー。Excelやスプレッドシートに貼り付けて管理できます。</p>
                    </div>
                </div>
            </div>

            <div class="ksv-manual-section">
                <h2>使い方</h2>

                <h3>1. リンクチェックの実行</h3>
                <ol>
                    <li>「リンクチェック」タブでサイト内のコンテンツ一覧を確認</li>
                    <li>チェックしたいページの「リンクチェック」ボタンをクリック</li>
                    <li>進捗バーで処理状況を確認（n/n件 %表示）</li>
                    <li>チェック完了後、結果がモーダルウィンドウで表示されます</li>
                </ol>

                <h3>2. 結果の見方</h3>
                <table class="ksv-manual-table">
                    <tr>
                        <th>列</th>
                        <th>説明</th>
                    </tr>
                    <tr>
                        <td><strong>再チェック</strong></td>
                        <td>「再」ボタンで個別のリンクを再チェック（キャッシュをスキップ）</td>
                    </tr>
                    <tr>
                        <td><strong>No.</strong></td>
                        <td>リンクの通し番号</td>
                    </tr>
                    <tr>
                        <td><strong>行</strong></td>
                        <td>HTMLソース内の行番号（目安）</td>
                    </tr>
                    <tr>
                        <td><strong>リンクテキスト</strong></td>
                        <td>リンクのアンカーテキスト</td>
                    </tr>
                    <tr>
                        <td><strong>URL</strong></td>
                        <td>リンク先URL（長いURLは▶で展開可能）</td>
                    </tr>
                    <tr>
                        <td><strong>タイプ</strong></td>
                        <td>内部リンク / 外部リンク</td>
                    </tr>
                    <tr>
                        <td><strong>ステータス</strong></td>
                        <td>HTTPステータスコード（200=正常、それ以外=エラー）</td>
                    </tr>
                    <tr>
                        <td><strong>確認</strong></td>
                        <td>プレビューでリンク箇所をハイライト表示</td>
                    </tr>
                </table>

                <h3>3. エラーリンクの修正</h3>
                <ol>
                    <li>エラー行（赤背景）を確認</li>
                    <li>「確認」をクリックしてページ内の位置を特定</li>
                    <li>WordPressの投稿編集画面でリンクを修正</li>
                    <li>「再」ボタンで個別に再チェック、または「一括再チェック」で全体を再確認</li>
                </ol>

                <h3>4. キャッシュ管理</h3>
                <ul>
                    <li><strong>自動キャッシュ：</strong>リンクステータスは24時間キャッシュされます</li>
                    <li><strong>手動クリア：</strong>「ステータスキャッシュをクリア」ボタンで全キャッシュを削除</li>
                    <li><strong>個別スキップ：</strong>「再」ボタンや「一括再チェック」はキャッシュをスキップして最新状態をチェック</li>
                </ul>
            </div>

            <div class="ksv-manual-section">
                <h2>ステータスコード一覧</h2>
                <table class="ksv-manual-table">
                    <tr>
                        <th>コード</th>
                        <th>意味</th>
                        <th>対処法</th>
                    </tr>
                    <tr class="ksv-status-ok">
                        <td><strong>200</strong></td>
                        <td>正常</td>
                        <td>問題ありません</td>
                    </tr>
                    <tr class="ksv-status-warn">
                        <td><strong>301</strong></td>
                        <td>恒久的リダイレクト</td>
                        <td>リダイレクト先のURLに更新を推奨</td>
                    </tr>
                    <tr class="ksv-status-warn">
                        <td><strong>302</strong></td>
                        <td>一時的リダイレクト</td>
                        <td>通常は問題なし、必要に応じて確認</td>
                    </tr>
                    <tr class="ksv-status-warn">
                        <td><strong>403</strong></td>
                        <td>アクセス禁止</td>
                        <td>リンク先がアクセス制限中。外部サイトの場合はボット制限の可能性あり</td>
                    </tr>
                    <tr class="ksv-status-error">
                        <td><strong>404</strong></td>
                        <td>ページが存在しない</td>
                        <td>リンク切れです。URLを修正または削除してください</td>
                    </tr>
                    <tr class="ksv-status-error">
                        <td><strong>500</strong></td>
                        <td>サーバーエラー</td>
                        <td>リンク先サーバーの問題。時間をおいて再チェック</td>
                    </tr>
                    <tr class="ksv-status-error">
                        <td><strong>503</strong></td>
                        <td>サービス利用不可</td>
                        <td>リンク先がメンテナンス中の可能性。後で再チェック</td>
                    </tr>
                    <tr class="ksv-status-error">
                        <td><strong>ERR</strong></td>
                        <td>接続エラー</td>
                        <td>DNS解決失敗、タイムアウトなど。URLが正しいか確認</td>
                    </tr>
                </table>
            </div>

            <div class="ksv-manual-section">
                <h2>除外されるリンク</h2>
                <p>以下のスキームで始まるリンクはチェック対象外です：</p>
                <ul class="ksv-exclude-list">
                    <li><code>tel:</code> - 電話番号リンク</li>
                    <li><code>mailto:</code> - メールリンク</li>
                    <li><code>javascript:</code> - JavaScriptリンク</li>
                    <li><code>data:</code> - データURI</li>
                    <li><code>ftp:</code> - FTPリンク</li>
                    <li><code>file:</code> - ローカルファイル</li>
                    <li><code>sms:</code> - SMSリンク</li>
                    <li><code>skype:</code> - Skypeリンク</li>
                    <li><code>#</code> - 空のアンカーリンク</li>
                </ul>
            </div>

            <div class="ksv-manual-section">
                <h2>ヒントとベストプラクティス</h2>
                <div class="ksv-tips">
                    <div class="ksv-tip">
                        <h4>定期的なチェック</h4>
                        <p>外部サイトは予告なく閉鎖・移転することがあります。月1回程度の定期チェックを推奨します。</p>
                    </div>
                    <div class="ksv-tip">
                        <h4>403エラーについて</h4>
                        <p>一部の外部サイトはボットからのアクセスを制限しています。403が出ても、ブラウザで直接アクセスすると正常に表示される場合があります。</p>
                    </div>
                    <div class="ksv-tip">
                        <h4>大量リンクのあるページ</h4>
                        <p>リンク数が多いページは処理に時間がかかります。進捗表示を確認しながらお待ちください。</p>
                    </div>
                    <div class="ksv-tip">
                        <h4>結果の活用</h4>
                        <p>「コピー」ボタンで結果をExcelに貼り付け、チームで共有・管理することができます。</p>
                    </div>
                </div>
            </div>

            <div class="ksv-manual-section ksv-manual-footer">
                <p><strong>Kashiwazaki SEO Link Doctor</strong> - SEO対策のためのリンク切れ検出ツール</p>
            </div>
        </div>
    </div>

    <!-- プレビューモーダル -->
    <div id="ksv-preview-modal" class="ksv-modal" style="display:none;">
        <div class="ksv-preview-modal-content">
            <span class="ksv-modal-close ksv-preview-close">&times;</span>
            <div class="ksv-preview-header">
                <span class="ksv-preview-title">リンク箇所プレビュー</span>
                <span class="ksv-preview-hint">※ 赤枠・黄色背景でハイライト表示</span>
            </div>
            <iframe id="ksv-preview-iframe"></iframe>
        </div>
    </div>

    <!-- モーダルウィンドウ -->
    <div id="ksv-link-modal" class="ksv-modal" style="display:none;">
        <div class="ksv-modal-content">
            <span class="ksv-modal-close">&times;</span>
            <div class="ksv-link-results" id="ksv-link-results">
                <h2>リンクチェック結果</h2>
                <div style="margin-bottom:10px;text-align:right"><button id="ksv-copy-btn" class="ksv-button">コピー</button></div>
                <div id="ksv-page-info"></div>
                <div id="ksv-links-table-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>再チェック</th>
                                <th>No.</th>
                                <th>行</th>
                                <th>リンクテキスト</th>
                                <th>URL</th>
                                <th>タイプ</th>
                                <th>ステータス</th>
                                <th>確認</th>
                            </tr>
                        </thead>
                        <tbody id="ksv-links-list">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
