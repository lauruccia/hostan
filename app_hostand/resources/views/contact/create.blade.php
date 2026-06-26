{{-- contact-modal.blade.php --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $isAdmin = \Auth::check() && \Auth::user()->type === 'owner';
    $hostandName = 'Hostand';
    $hostandEmail = 'servizi.atman@gmail.com';
    $hostandContact = '+39 3509750228';
@endphp

{{ Form::open(['url' => 'contact', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        {{-- Owner Search Field --}}
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            @if($isAdmin)
                {{ Form::text('name', $hostandName, ['class' => 'form-control', 'readonly' => true]) }}
            @else
                <select id="owner_search" name="name" class="form-control" style="width: 100%;"></select>
            @endif
        </div>

        {{-- Email Input --}}
        <div class="form-group col-md-12">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::text('email', $isAdmin ? $hostandEmail : null, ['class' => 'form-control', 'placeholder' => __('Enter contact email'), 'readonly' => $isAdmin]) }}
        </div>

        {{-- Contact Number --}}
        <div class="form-group col-md-12">
            {{ Form::label('contact_number', __('Contact Number'), ['class' => 'form-label']) }}
            {{ Form::text('contact_number', $isAdmin ? $hostandContact : null, ['class' => 'form-control', 'placeholder' => __('Enter contact number'), 'readonly' => $isAdmin]) }}
        </div>

        {{-- Subject --}}
        <div class="form-group col-md-12">
            {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
            {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => __('Enter contact subject')]) }}
        </div>

        {{-- Message --}}
        <div class="form-group col-md-12">
            {{ Form::label('message', __('Message'), ['class' => 'form-label']) }}
            {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 5, 'placeholder' => __('Enter your message')]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}

{{-- If user is admin we don't need Select2; otherwise ensure it exists and init --}}
@if(!$isAdmin)

<!-- small loader: will add jQuery/Select2 only if missing, then init -->
<script>
(function() {
    // CDN URLs
    var JQ = 'https://code.jquery.com/jquery-3.6.0.min.js';
    var S2_CSS = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
    var S2_JS  = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';

    function addCssOnce(href) {
        if (!document.querySelector('link[href="'+href+'"]')) {
            var l = document.createElement('link');
            l.rel = 'stylesheet'; l.href = href;
            document.head.appendChild(l);
            console.log('[Loader] Added CSS:', href);
        }
    }
    function addScriptOnce(src, cb) {
        if (document.querySelector('script[src="'+src+'"]')) {
            console.log('[Loader] Script already present:', src);
            if (cb) cb();
            return;
        }
        var s = document.createElement('script');
        s.src = src;
        s.async = false;
        s.onload = function() { console.log('[Loader] Script loaded:', src); if (cb) cb(); };
        s.onerror = function() { console.error('[Loader] Failed loading script:', src); if (cb) cb && cb(new Error('load failed')); };
        document.head.appendChild(s);
    }

    // Ensure jQuery exists, else load it
    function ensurejQuery(next) {
        if (window.jQuery) {
            console.log('[Loader] jQuery detected, version:', window.jQuery.fn && window.jQuery.fn.jquery);
            return next();
        }
        addScriptOnce(JQ, function(err) {
            if (err) { console.error('[Loader] jQuery failed to load'); return next(err); }
            console.log('[Loader] jQuery ready, version:', window.jQuery.fn && window.jQuery.fn.jquery);
            next();
        });
    }

    // Ensure Select2 (css + js)
    function ensureSelect2(next) {
        addCssOnce(S2_CSS);
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            console.log('[Loader] select2 already available');
            return next();
        }
        // load after jQuery
        addScriptOnce(S2_JS, function(err) {
            if (err) { console.error('[Loader] select2 failed to load'); return next(err); }
            // small delay to let select2 attach to jQuery
            setTimeout(function() {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                    console.log('[Loader] select2 attached to jQuery');
                    next();
                } else {
                    console.error('[Loader] select2 not attached after load');
                    next(new Error('select2 not attached'));
                }
            }, 50);
        });
    }

    // initialize your select2 with logging
    function initSelect2() {
        (function($){
            console.log('[Init] Document ready and jQuery select2 check:',$.fn && $.fn.select2 ? true : false);
            if (!$.fn.select2) {
                console.error('[Init] Select2 missing - aborting init.');
                return;
            }

            var $owner = $('#owner_search');
            if (!$owner.length) {
                console.warn('[Init] #owner_search not found in DOM.');
                return;
            }

            // avoid reinit
            if ($owner.data('select2')) {
                console.log('[Init] #owner_search already initialized.');
                return;
            }

            $owner.select2({
                placeholder: '{{ __("Search owner by name") }}',
                allowClear: true,
                dropdownParent: $owner.closest('.modal').length ? $owner.closest('.modal') : $(document.body),
                ajax: {
                    url: '{{ route('users.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        console.log('[AJAX] sending q=', params.term);
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        console.log('[AJAX] got response', data);
                        return { results: data.results || data || [] };
                    },
                    cache: true,
                    error: function(xhr, status, err) {
                        console.error('[AJAX] error', status, err, xhr && xhr.responseText);
                    }
                },
                templateResult: function(item) {
                    if (!item || item.loading) return item.text || 'Loading...';
                    var txt = item.text || item.name || item.email || item.id;
                    if (item.email) txt += ' (' + item.email + ')';
                    return txt;
                },
                templateSelection: function(item) {
                    if (!item) return '';
                    return item.text || item.name || item.id;
                }
            });

            $owner.on('select2:select', function(e){
                var d = e.params && e.params.data ? e.params.data : null;
                console.log('[Event] selected', d);
                $('input[name="email"]').val(d && d.email ? d.email : '');
                $('input[name="contact_number"]').val(d && (d.contact || d.phone || d.contact_number) ? (d.contact || d.phone || d.contact_number) : '');
            });

            $owner.on('select2:clear', function(){
                console.log('[Event] cleared');
                $('input[name="email"]').val('');
                $('input[name="contact_number"]').val('');
            });

            console.log('[Init] Select2 initialization finished.');
        })(jQuery);
    }

    // run the loader chain
    ensurejQuery(function(err) {
        if (err) { console.error('[Loader] jQuery load chain failed.'); return; }
        ensureSelect2(function(err2) {
            if (err2) { console.error('[Loader] Select2 load failed.'); return; }
            // ensure DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSelect2);
            } else {
                initSelect2();
            }
        });
    });

})();
</script>
@endif
