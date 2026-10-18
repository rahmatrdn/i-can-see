// Wait for jQuery to be available (Vite modules load before CDN jQuery)
function waitForJQuery(callback) {
    if (typeof window.jQuery !== 'undefined') {
        callback(window.jQuery);
    } else {
        var checkInterval = setInterval(function () {
            if (typeof window.jQuery !== 'undefined') {
                clearInterval(checkInterval);
                callback(window.jQuery);
            }
        }, 10);
    }
}

waitForJQuery(function ($) {
$(document).ready(function () {
    console.log('SPA Script Loaded');

    // Replace initial history state so back button works properly
    if (!history.state) {
        history.replaceState({
            path: window.location.href,
            spa: true
        }, '', window.location.href);
    }

    // Re-initialize Preline plugins on initial load
    // (jQuery may load after Preline's auto-init, causing components to miss initialization)
    reinitPlugins();

    // Helper to update content from HTML response
    function handleSpaResponse(data, urlToPush, isFormSubmit = false) {
        // Track open modals before cleanup
        var openModalId = null;
        if (isFormSubmit) {
            var openModal = document.querySelector('.hs-overlay.open');
            if (openModal) {
                openModalId = openModal.id;
            }
        }

        // --- Clean up any open Preline modals/overlays before replacing content ---
        // Preline appends the backdrop to <body> (outside #main-content), so it
        // must be removed manually or it stays after the DOM swap.
        try {
            // Close all open HSOverlay instances gracefully
            if (window.HSOverlay) {
                document.querySelectorAll('[data-hs-overlay]').forEach(function (trigger) {
                    var targetId = trigger.getAttribute('data-hs-overlay');
                    var targetEl = targetId ? document.querySelector(targetId) : null;
                    if (targetEl && targetEl.classList.contains('open')) {
                        // If it's a form submit, we might want to keep it, but DOM swap usually breaks JS instances
                        // So we close it but remember to re-open it later if needed
                        var instance = window.HSOverlay.getInstance(targetEl);
                        if (instance && typeof instance.close === 'function') instance.close();
                    }
                });
                // Also close any element that has the 'open' class used by Preline overlays
                document.querySelectorAll('.hs-overlay.open').forEach(function (el) {
                    try {
                        var instance = window.HSOverlay.getInstance(el);
                        if (instance && typeof instance.close === 'function') instance.close();
                    } catch (e) {}
                });
            }
        } catch (e) {}
        // Remove leftover backdrop elements and body scroll-lock regardless
        document.querySelectorAll('.hs-overlay-backdrop').forEach(function (el) {
            el.remove();
        });
        document.body.classList.remove('overflow-hidden');
        document.body.style.removeProperty('overflow');
        // --- End modal cleanup ---

        // Use DOMParser for reliable script extraction (jQuery strips scripts)
        var parser = new DOMParser();
        var doc = parser.parseFromString(data, 'text/html');

        // Extract new content using native DOM
        var mainContentEl = doc.querySelector('#main-content');
        var sidebarEl = doc.querySelector('#hs-application-sidebar');

        var newContent = mainContentEl ? mainContentEl.innerHTML : null;
        var newSidebar = sidebarEl ? sidebarEl.innerHTML : null;

        if (newContent) {
            $('#main-content').html(newContent);
            console.log('Content updated');

            // Extract page-specific scripts from the response body
            // Exclude layout scripts (jQuery, NProgress, Vite bundles, SPA handler)
            var layoutScriptPatterns = [
                'jquery',
                'nprogress',
                'vite',
                'SPA Script Loaded', // Our SPA handler
                'preline/index' // Preline UI bundle (not helper scripts like hs-apexcharts-helpers.js)
            ];

            var allBodyScripts = doc.body.querySelectorAll('script');
            var externalScripts = [];
            var inlineScripts = [];

            allBodyScripts.forEach(function (s) {
                var src = s.src || '';
                var content = s.textContent || '';

                // Skip layout scripts
                var isLayoutScript = layoutScriptPatterns.some(function (pattern) {
                    return src.toLowerCase().includes(pattern.toLowerCase()) ||
                        content.includes(pattern);
                });

                if (isLayoutScript) return;

                if (s.src) {
                    externalScripts.push({ src: s.src, type: s.type || '' });
                } else if (s.textContent.trim()) {
                    inlineScripts.push(s.textContent);
                }
            });

            console.log('Found page scripts:', externalScripts.length, 'external,', inlineScripts.length,
                'inline');

            // Function to load external scripts sequentially
            function loadScriptsSequentially(entries, callback) {
                if (entries.length === 0) {
                    callback();
                    return;
                }
                var entry = entries.shift();
                var src = entry.src || entry;
                var type = entry.type || '';
                // Check if script is already loaded
                if (document.querySelector('script[src="' + src + '"]')) {
                    loadScriptsSequentially(entries, callback);
                    return;
                }
                var script = document.createElement('script');
                script.src = src;
                if (type) {
                    script.type = type;
                }
                script.onload = function () {
                    loadScriptsSequentially(entries, callback);
                };
                script.onerror = function () {
                    console.error('Failed to load script:', src);
                    loadScriptsSequentially(entries, callback);
                };
                document.body.appendChild(script);
            }

            loadScriptsSequentially(externalScripts.slice(), function () {
                // Execute inline scripts after external ones have loaded
                inlineScripts.forEach(function (code) {
                    try {
                        var script = document.createElement('script');
                        script.textContent = code;
                        document.body.appendChild(script);
                    } catch (e) {
                        console.error('Error executing inline script:', e);
                    }
                });

                // Re-initialize plugins after scripts have loaded
                reinitPlugins();

                // Dispatch load events for scripts waiting on them
                document.dispatchEvent(new Event('DOMContentLoaded', {
                    bubbles: true,
                    cancelable: true
                }));
                window.dispatchEvent(new Event('load'));
            });

        } else {
            console.error('#main-content not found in response');
            return false;
        }

        if (newSidebar) {
            // Only swap sidebar if the HTML actually changed. Rewriting it on every
            // navigation destroys Preline (HSOverlay/HSAccordion) instances and forces
            // a full DOM rescan, which makes mobile navigation feel laggy.
            var currentSidebarEl = document.getElementById('hs-application-sidebar');
            var currentSidebar = currentSidebarEl ? currentSidebarEl.innerHTML : '';
            if (currentSidebar !== newSidebar) {
                $('#hs-application-sidebar').html(newSidebar);
                console.log('Sidebar updated');
                // Re-init plugins only when sidebar DOM actually changed
                reinitPlugins();
            }
        } else {
            // Content changed but no sidebar in response — still ensure plugins are wired
            reinitPlugins();
        }

        // Update URL
        if (urlToPush && window.location.href !== urlToPush) {
            window.history.pushState({
                path: urlToPush,
                spa: true
            }, '', urlToPush);
        }

        return {
            success: true,
            openModalId: openModalId
        };
    }

    function reinitPlugins() {
        // Re-initialize Preline
        if (window.HSStaticMethods) {
            window.HSStaticMethods.autoInit();
        }

        // Specific re-init for common components if autoInit isn't enough
        if (window.HSAccordion && typeof window.HSAccordion.autoInit === 'function') {
            window.HSAccordion.autoInit();
        }

        if (window.HSOverlay && typeof window.HSOverlay.autoInit === 'function') {
            window.HSOverlay.autoInit();
        }

        // Re-initialize Flatpickr
        if (window.flatpickr) {
            window.flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j F Y",
                allowInput: true
            });

            initDateRangePickers();
        }
    }

    function initDateRangePickers() {
        if (!window.flatpickr) {
            return;
        }

        document.querySelectorAll('input.datepicker-range').forEach(function (rangeInput) {
            var dariId = rangeInput.getAttribute('data-dari-input');
            var sampaiId = rangeInput.getAttribute('data-sampai-input');
            var dariInput = dariId ? document.getElementById(dariId) : null;
            var sampaiInput = sampaiId ? document.getElementById(sampaiId) : null;

            if (!dariInput || !sampaiInput) {
                return;
            }

            var savedDari = dariInput.value || '';
            var savedSampai = sampaiInput.value || '';

            if (rangeInput._flatpickr) {
                rangeInput._flatpickr.destroy();
            }

            if (savedDari) {
                dariInput.value = savedDari;
            }

            if (savedSampai) {
                sampaiInput.value = savedSampai;
            }

            var defaultDates = [savedDari, savedSampai].filter(Boolean);

            function syncHiddenDates(selectedDates, instance) {
                if (selectedDates.length >= 1) {
                    dariInput.value = instance.formatDate(selectedDates[0], 'Y-m-d');
                } else {
                    dariInput.value = '';
                }

                if (selectedDates.length >= 2) {
                    sampaiInput.value = instance.formatDate(selectedDates[1], 'Y-m-d');
                } else if (selectedDates.length === 1) {
                    sampaiInput.value = instance.formatDate(selectedDates[0], 'Y-m-d');
                } else {
                    sampaiInput.value = '';
                }
            }

            window.flatpickr(rangeInput, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'j F Y',
                allowInput: false,
                defaultDate: defaultDates.length > 0 ? defaultDates : undefined,
                onChange: syncHiddenDates,
            });

            var form = rangeInput.closest('form');

            if (form && !form.dataset.dateRangeBound) {
                form.dataset.dateRangeBound = '1';
                form.addEventListener('submit', function () {
                    var fp = rangeInput._flatpickr;

                    if (fp && fp.selectedDates.length > 0) {
                        syncHiddenDates(fp.selectedDates, fp);
                    }
                });
            }
        });
    }

    // Intercept clicks on elements with 'navigate' attribute
    $('body').on('click', 'a[navigate]', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        console.log('SPA Navigation clicked:', url);

        if (!url || url.startsWith('#') || url.startsWith('javascript:')) {
            return;
        }

        var shouldDelay = false;
        // Close Sidebar on Mobile if it's open
        try {
            if (window.innerWidth < 1024) {
                var toggleBtn = document.querySelector('[data-hs-overlay="#hs-application-sidebar"]');
                if (toggleBtn && toggleBtn.getAttribute('aria-expanded') === 'true') {
                    toggleBtn.click();
                    shouldDelay = true;
                } else {
                    var backdrop = document.querySelector('.hs-overlay-backdrop');
                    if (backdrop) {
                        backdrop.click();
                        shouldDelay = true;
                    }
                }
            }
        } catch (error) {
            // Ignore errors if overlay library isn't fully loaded or element invalid
            console.log('Sidebar Close Debug:', error);
        }

        if (shouldDelay) {
            // Start AJAX in parallel with the sidebar close animation. Previously
            // we waited 300ms BEFORE firing the request which made navigation feel
            // slow on mobile. Now: fetch starts immediately, and we only delay the
            // DOM swap until the close animation finishes.
            loadPage(url, 300);
        } else {
            loadPage(url);
        }
    });

    function loadPage(url, swapDelay) {
        // Start Loading
        NProgress.start();
        var startedAt = Date.now();

        $.ajax({
            url: url,
            success: function (data) {
                var elapsed = Date.now() - startedAt;
                var wait = Math.max(0, (swapDelay || 0) - elapsed);
                var apply = function () {
                    if (!handleSpaResponse(data, url).success) {
                        window.location.href = url;
                    }
                    NProgress.done();
                };
                if (wait > 0) {
                    setTimeout(apply, wait);
                } else {
                    apply();
                }
            },
            error: function (xhr, status, error) {
                console.error('SPA Load Error:', error);
                window.location.href = url; // Fallback to normal navigation on error
            }
        });
    }

    // Track if we're in the middle of SPA navigation
    var spaNavigating = false;

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function (event) {
        // If no state or not our SPA state, let browser handle it
        if (!event.state || !event.state.spa) {
            console.log('Non-SPA state, letting browser handle');
            return;
        }

        var url = window.location.href;
        console.log('Browser back/forward to:', url);

        // Prevent any default behavior by immediately updating content
        spaNavigating = true;

        // Load the page without pushing to history (already in history)
        NProgress.start();
        $.ajax({
            url: url,
            success: function (data) {
                // Use DOMParser for reliable parsing
                var parser = new DOMParser();
                var doc = parser.parseFromString(data, 'text/html');

                var mainContentEl = doc.querySelector('#main-content');
                var sidebarEl = doc.querySelector('#hs-application-sidebar');

                if (mainContentEl) {
                    $('#main-content').html(mainContentEl.innerHTML);

                    // Re-load page scripts (same logic as handleSpaResponse)
                    var layoutScriptPatterns = ['jquery', 'nprogress', 'vite',
                        'SPA Script Loaded', 'preline/index'
                    ];
                    var allBodyScripts = doc.body.querySelectorAll('script');
                    var externalScripts = [];
                    var inlineScripts = [];

                    allBodyScripts.forEach(function (s) {
                        var src = s.src || '';
                        var content = s.textContent || '';
                        var isLayoutScript = layoutScriptPatterns.some(function (
                            pattern) {
                            return src.toLowerCase().includes(pattern
                                .toLowerCase()) || content.includes(
                                    pattern);
                        });
                        if (isLayoutScript) return;
                        if (s.src) externalScripts.push({ src: s.src, type: s.type || '' });
                        else if (s.textContent.trim()) inlineScripts.push(s
                            .textContent);
                    });

                    // Load scripts sequentially
                    (function loadNext(entries, cb) {
                        if (entries.length === 0) {
                            cb();
                            return;
                        }
                        var entry = entries.shift();
                        var u = entry.src || entry;
                        var t = entry.type || '';
                        if (document.querySelector('script[src="' + u + '"]')) {
                            loadNext(entries, cb);
                            return;
                        }
                        var script = document.createElement('script');
                        script.src = u;
                        if (t) {
                            script.type = t;
                        }
                        script.onload = script.onerror = function () {
                            loadNext(entries, cb);
                        };
                        document.body.appendChild(script);
                    })(externalScripts.slice(), function () {
                        inlineScripts.forEach(function (code) {
                            try {
                                var script = document.createElement('script');
                                script.textContent = code;
                                document.body.appendChild(script);
                            } catch (e) { }
                        });

                        // Re-initialize plugins
                        reinitPlugins();

                        document.dispatchEvent(new Event('DOMContentLoaded', {
                            bubbles: true,
                            cancelable: true
                        }));
                        window.dispatchEvent(new Event('load'));
                    });
                }

                if (sidebarEl) {
                    $('#hs-application-sidebar').html(sidebarEl.innerHTML);
                }

                // Re-initialize plugins immediately after HTML update
                reinitPlugins();

                NProgress.done();
                spaNavigating = false;
            },
            error: function () {
                spaNavigating = false;
                window.location.reload();
            }
        });
    });

    // Handle page restored from bfcache (browser back-forward cache)
    window.addEventListener('pageshow', function (event) {
        if (event.persisted && spaNavigating) {
            // Page was restored from bfcache while we were SPA navigating
            console.log('Page restored from bfcache during SPA navigation');
            event.preventDefault();
        }
    });

    // Helper to handle JSON validation errors
    function handleValidationErrors($form, errors) {
        // Clear previous errors
        $form.find('.border-red-500').removeClass('border-red-500');
        $form.find('.validation-error').remove();

        // Loop through errors
        $.each(errors, function (field, messages) {
            var $input = $form.find('[name="' + field + '"]');
            if ($input.length) {
                var message = messages[0];

                if ($input.attr('type') === 'radio') {
                    // For radio buttons, highlight the labels/containers and place error after the group
                    var $group = $input.closest('.grid').length ? $input.closest('.grid') : $input.parent();
                    
                    // Add border to the radio card labels if they exist
                    $input.each(function() {
                        var $label = $(this).closest('label');
                        if ($label.length) $label.addClass('border-red-500');
                    });

                    if (!$group.next('.validation-error').length) {
                        $group.after('<p class="text-sm text-red-600 mt-1 validation-error">' + message + '</p>');
                    }
                } else {
                    $input.addClass('border-red-500');
                    $input.after('<p class="text-sm text-red-600 mt-1 validation-error">' + message + '</p>');
                }
            }
        });
    }

    // Global function for custom toast close button
    window.tostifyCustomClose = function (el) {
        $(el).closest('.toastify').remove();
    };

    function getToastNode(message, type) {
        type = type || 'success';

        var typeConfig = {
            success: {
                icon: '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                borderClass: 'border-green-200 dark:border-green-900',
                bgClass: 'bg-white dark:bg-neutral-800',
                textClass: 'text-gray-700 dark:text-neutral-300'
            },
            error: {
                icon: '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                borderClass: 'border-red-200 dark:border-red-900',
                bgClass: 'bg-white dark:bg-neutral-800',
                textClass: 'text-gray-700 dark:text-neutral-300'
            },
            warning: {
                icon: '<svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                borderClass: 'border-yellow-200 dark:border-yellow-900',
                bgClass: 'bg-white dark:bg-neutral-800',
                textClass: 'text-gray-700 dark:text-neutral-300'
            },
            info: {
                icon: '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                borderClass: 'border-blue-200 dark:border-blue-900',
                bgClass: 'bg-white dark:bg-neutral-800',
                textClass: 'text-gray-700 dark:text-neutral-300'
            }
        };

        var config = typeConfig[type] || typeConfig.success;

        var html = `
        <div class="animate-toast-pop ${config.bgClass} border ${config.borderClass} rounded-xl shadow-xl dark:shadow-neutral-900/50" role="alert">
            <div class="flex items-start gap-3 p-4">
              <div class="flex-shrink-0 mt-0.5">
                ${config.icon}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium ${config.textClass} mb-0 pb-0 leading-relaxed">${message}</p> 
              </div>
              <button onclick="tostifyCustomClose(this)" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>
        </div>`;
        var div = document.createElement('div');
        div.innerHTML = html.trim();
        return div.firstChild;
    }

    // Intercept form submissions with 'navigate-form' attribute
    $('body').on('submit', 'form[navigate-form]', function (e) {
        e.preventDefault();
        var $form = $(this);
        console.log('SPA Form Submit:', $form.attr('action'));

        // Find submit button & Set Loading State
        var $btn = $form.find('button[type="submit"]');
        var originalHtml = $btn.html();
        if ($btn.length) {
            $btn.prop('disabled', true).html(
                '<span class="animate-spin inline-block size-4 border-[3px] border-current border-t-transparent rounded-full" role="status" aria-label="loading"></span> Loading...'
            );
        }

        NProgress.start();
        var action = $form.attr('action');
        var method = $form.attr('method') || 'POST'; // Default to POST if not specified
        var nativeXhr;
        var ajaxData;
        var ajaxProcessData;
        var ajaxContentType;

        if (method.toUpperCase() === 'GET') {
            ajaxData = $form.serialize();
            ajaxProcessData = true;
            ajaxContentType = 'application/x-www-form-urlencoded; charset=UTF-8';
        } else {
            ajaxData = new FormData(this);
            ajaxProcessData = false;
            ajaxContentType = false;
        }

        $.ajax({
            url: action,
            type: method,
            data: ajaxData,
            processData: ajaxProcessData,
            contentType: ajaxContentType,
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                // Capture the native XHR object to access responseURL later
                nativeXhr = xhr;
                return xhr;
            },
            success: function (data, textStatus, xhr) {
                // Attempt to get final URL from native XHR (handles redirects)
                var finalUrl = (nativeXhr ? nativeXhr.responseURL : null) || action;
                console.log('Form Success, Final URL:', finalUrl);

                var spaResult = handleSpaResponse(data, finalUrl, true);
                if (spaResult.success) {
                    console.log('Form SPA update success');

                    // After DOM update, check for flash messages injected by Laravel
                    var errorMessage = $('#spa-flash-error').text().trim();
                    var successMessage = $('#spa-flash-success').text().trim();

                    if (errorMessage) {
                        // Server redirected back with an error flash — show error toast
                        if ($btn.length) {
                            $btn.prop('disabled', false).html(originalHtml);
                        }

                        // Re-open modal if it was open before
                        if (spaResult.openModalId) {
                            setTimeout(function () {
                                var modalEl = document.getElementById(spaResult.openModalId);
                                if (modalEl && window.HSOverlay) {
                                    HSOverlay.open(modalEl);
                                }
                            }, 100);
                        }

                        if (window.Toastify) {
                            Toastify({
                                node: getToastNode(errorMessage, 'error'),
                                duration: 5000,
                                className: "p-0 bg-transparent shadow-none max-w-xs",
                                gravity: "top",
                                position: "right",
                                stopOnFocus: true,
                                style: {
                                    background: "transparent",
                                    boxShadow: "none"
                                }
                            }).showToast();
                        }
                    } else {
                        // Success path: use flash message or fallback
                        var toastMessage = successMessage ||
                            (method.toUpperCase() === 'GET' ? '' : "Form submitted successfully");

                        if (window.Toastify && toastMessage) {
                            Toastify({
                                node: getToastNode(toastMessage, 'success'),
                                duration: 3000,
                                className: "p-0 bg-transparent shadow-none max-w-xs",
                                gravity: "top",
                                position: "right",
                                stopOnFocus: true,
                                style: {
                                    background: "transparent",
                                    boxShadow: "none"
                                }
                            }).showToast();
                        }
                    }
                } else {
                    window.location.reload();
                }
                NProgress.done();
            },
            error: function (xhr) {
                console.error('Form Error', xhr);
                NProgress.done();

                // Restore Button State
                if ($btn.length) {
                    $btn.prop('disabled', false).html(originalHtml);
                }

                if (xhr.status === 422 || xhr.status === 400) {
                    var response = xhr.responseJSON;
                    if (response && response.errors) {
                        handleValidationErrors($form, response.errors);
                    } else {
                        alert('Validation failed but no errors returned.');
                    }
                } else {
                    var errorMessage = 'An error occurred: ' + xhr.status;
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.statusText) {
                        errorMessage += ' ' + xhr.statusText;
                    }

                    // Show error toast
                    if (window.Toastify) {
                        Toastify({
                            node: getToastNode(errorMessage, 'error'),
                            duration: 5000,
                            className: "p-0 bg-transparent shadow-none max-w-xs",
                            gravity: "top",
                            position: "right",
                            stopOnFocus: true,
                            style: {
                                background: "transparent",
                                boxShadow: "none",
                            }
                        }).showToast();
                    }

                    // Try to render response if it is HTML (e.g. 500 error page)
                    // Warning: replacing body with error page might break SPA context but provides feedback.
                    var $temp = $('<div>').html(xhr.responseText);
                    if ($temp.find('#main-content').length) {
                        handleSpaResponse(xhr.responseText, action);
                    } else {
                        // Full replacement if critical error
                        if (xhr.responseText) {
                            document.open();
                            document.write(xhr.responseText);
                            document.close();
                        }
                    }
                }
            }
        });
    });

});
}); // end waitForJQuery
