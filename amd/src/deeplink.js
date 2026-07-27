/**
 * AMD Module for H5P Chapter Deeplink (Double Inception Architecture)
 *
 * @module local_h5pchapter/deeplink
 */
define(['jquery'], function ($) {
    return {
        // --- CÓDIGO DA PÁGINA PAI (CAMADA 0) ---
        initParent: function (params) {

            window.addEventListener('message', function(event) {
                if (event.data && event.data.app === 'h5pchapter' && event.data.action === 'ready') {

                    event.source.postMessage({
                        app: 'h5pchapter',
                        action: 'execute',
                        target: params.chapter_target,
                        block: params.block_navigation
                    }, '*');
                }
            });
        },

// --- CÓDIGO DENTRO DO EMBED.PHP (CAMADA 1) ---
        initIframe: function () {

            window.parent.postMessage({
                app: 'h5pchapter',
                action: 'ready'
            }, '*');

            window.addEventListener('message', function(event) {
                if (event.data && event.data.app === 'h5pchapter' && event.data.action === 'execute') {

                    var targetChapterNum = parseInt(event.data.target, 10);
                    var blockNavigation = event.data.block;

                    var checkDOM = function() {
                        var targetDoc = document;

                        var $innerIframe = $('iframe.h5p-iframe');
                        if ($innerIframe.length > 0) {
                            try {
                                targetDoc = $innerIframe[0].contentDocument || $innerIframe[0].contentWindow.document;
                            } catch (e) {

                            }
                        }

                        var $menuContainer = $(targetDoc).find('.h5p-interactive-book-navigation');

                        if ($menuContainer.length > 0) {
                            var $chapters = $(targetDoc).find('.h5p-interactive-book-navigation-chapter');

                            if ($chapters.length > 0) {

                                // 1. O SALTO (DEEPLINK COM CLIQUE NATIVO)
                                if (!isNaN(targetChapterNum) && targetChapterNum > 0) {
                                    var targetIndex = targetChapterNum - 1;

                                    if ($chapters.length > targetIndex) {
                                        var $targetLi = $chapters.eq(targetIndex);
                                        var $clickable = $targetLi.find('[role="button"], a, button').first();

                                        // Pega o elemento DOM cru (sem o wrapper do jQuery)
                                        var domElement = $clickable.length > 0 ? $clickable[0] : $targetLi[0];

                                        // Cria um evento de mouse nativo simulando um clique humano real
                                        var clickEvent = new MouseEvent('click', {
                                            view: targetDoc.defaultView,
                                            bubbles: true,
                                            cancelable: true
                                        });

                                        // Dispara o evento
                                        domElement.dispatchEvent(clickEvent);
                                    }
                                }

                                // 2. A GUILHOTINA (BLOQUEIO VISUAL)
                                if (blockNavigation) {
                                    var cssRule = '<style>' +
                                    '.h5p-interactive-book-status-main { display: flex !important; ' +
                                    'width: 100% !important; justify-content: space-between; }' +
                                    '.h5p-interactive-book-status-header { display: flex !important; }' +
                                    '.h5p-interactive-book-navigation { display: none !important; }' +
                                    '.h5p-theme-button { display: none !important; }' +
                                    '.h5p-interactive-book-status-chapter { max-width: none !important; }' +
                                    '.h5p-interactive-book-status-side { display: none !important; }' +
                                    '.h5p-interactive-book-main { width: 100% !important; }' +
                                    '.h5p-interactive-book-cover { display: none !important; }' +
                                    '.h5p-interactive-book-status-menu { display: none !important; }' +
                                    '.h5p-interactive-book-status-progress-wrapper ' +
                                    '{ display: none !important; }' +
                                    '.h5p-interactive-book-status-footer { display: none !important; }' +
                                    '</style>';
                                    $(targetDoc).find('head').append(cssRule);
                                }

                                return; // Missão cumprida, encerra o loop!
                            }
                        }

                        setTimeout(checkDOM, 300);
                    };

                    checkDOM();
                }
            });
        }
    };
});