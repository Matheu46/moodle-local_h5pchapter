// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * @module      local_h5pchapter/deeplink
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * AMD Module for H5P Chapter Deeplink (Double Inception Architecture)
 *
 * @module local_h5pchapter/deeplink
 */
define(['jquery'], function($) {
    return {
        // Parent window code (Layer 0)
        initParent: function(params) {
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

        // Code running inside the embed iframe (Layer 1)
        initIframe: function() {
            window.parent.postMessage({
                app: 'h5pchapter',
                action: 'ready'
            }, '*');

            window.addEventListener('message', function(event) {
                if (event.data && event.data.app === 'h5pchapter' && event.data.action === 'execute') {
                    var targetChaptersStr = event.data.target ? String(event.data.target) : '';
                    var targetChapters = targetChaptersStr.split(',').map(function(item) {
                        return parseInt(item.trim(), 10);
                    }).filter(function(item) {
                        return !isNaN(item) && item > 0;
                    });
                    var blockNavigation = event.data.block;

                    var checkDOM = function() {
                        var targetDoc = document;

                        var $innerIframe = $('iframe.h5p-iframe');
                        if ($innerIframe.length > 0) {
                            try {
                                targetDoc = $innerIframe[0].contentDocument || $innerIframe[0].contentWindow.document;
                            } catch (e) {
                                // Ignore cross-origin errors if they occur
                            }
                        }

                        var $menuContainer = $(targetDoc).find('.h5p-interactive-book-navigation');

                        if ($menuContainer.length > 0) {
                            var $chapters = $(targetDoc).find('.h5p-interactive-book-navigation-chapter');

                            if ($chapters.length > 0) {
                                // 1. Simulate native click for deep linking
                                if (targetChapters.length > 0) {
                                    var firstTargetIndex = targetChapters[0] - 1;

                                    if ($chapters.length > firstTargetIndex) {
                                        var $targetLi = $chapters.eq(firstTargetIndex);
                                        var $clickable = $targetLi.find('[role="button"], a, button').first();
                                        var domElement = $clickable.length > 0 ? $clickable[0] : $targetLi[0];

                                        var clickEvent = new MouseEvent('click', {
                                            view: targetDoc.defaultView,
                                            bubbles: true,
                                            cancelable: true
                                        });
                                        domElement.dispatchEvent(clickEvent);
                                    }
                                }

                                // 2. Visual blocking of navigation elements
                                if (blockNavigation) {
                                    var cssRule = '<style>' +
                                    '.h5p-interactive-book-status-main { display: flex !important; ' +
                                    'justify-content: space-between; }' +
                                    '.h5p-theme-button { display: none !important; }' +
                                    '.h5p-interactive-book-status-chapter { max-width: none !important; }' +
                                    '.h5p-interactive-book-cover { display: none !important; }' +
                                    '.h5p-interactive-book-status-progress-wrapper ' +
                                    '{ display: none !important; }' +
                                    '.h5p-interactive-book-status-footer { display: none !important; }' +
                                    '.h5p-interactive-book-status-arrow { display: none !important; }';

                                    if (targetChapters.length <= 1) {
                                        // Only 1 chapter allowed: hide side menu completely
                                        cssRule += '.h5p-interactive-book-navigation { display: none !important; }' +
                                                   '.h5p-interactive-book-status-menu { display: none !important; }' +
                                                   '.h5p-interactive-book-status-side { display: none !important; }' +
                                                   '.h5p-interactive-book-status-header { display: flex !important; }' +
                                                   '.h5p-interactive-book-status-main { width: 100% !important; }' +
                                                   '.h5p-interactive-book-main { width: 100% !important; }';
                                    } else {
                                        // Multiple chapters allowed: hide unlisted chapters only
                                        cssRule += '.h5p-interactive-book-navigation-chapter.h5pchapter-locked ' +
                                                   '{ display: none !important; }';
                                    }

                                    cssRule += '</style>';
                                    $(targetDoc).find('head').append(cssRule);

                                    // Mark unlisted chapters for hiding
                                    if (targetChapters.length > 1) {
                                        $chapters.each(function(index, elem) {
                                            if (targetChapters.indexOf(index + 1) === -1) {
                                                $(elem).addClass('h5pchapter-locked');
                                            }
                                        });
                                    }
                                }

                                return;
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