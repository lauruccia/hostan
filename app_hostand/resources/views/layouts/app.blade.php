<!doctype html>
@php
    $settings = settings();

@endphp
<html lang="en">
<!-- [Head] start -->
@include('admin.head')

<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="{{ $settings['accent_color'] }}" data-pc-sidebar-theme="light"
    data-pc-sidebar-caption="{{ $settings['sidebar_caption'] }}" data-pc-direction="{{ $settings['theme_layout'] }}"
    data-pc-theme="{{ $settings['theme_mode'] }}">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ Sidebar Menu ] start -->
    @include('admin.menu')
    <!-- [ Sidebar Menu ] end -->
    <!-- [ Header Topbar ] start -->
    @include('admin.header')
    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->

            @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="page-header-title">
                                <h5 class="m-b-10"> @yield('page-title')</h5>
                            </div>
                        </div>
                        <div class="col-auto">
                            <ul class="breadcrumb">
                                @yield('breadcrumb')
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->


            <!-- [ Main Content ] start -->
            @include('admin.content')
            

            <!-- [ Main Content ] end -->
        </div>
    </div>

        {{-- expose Laravel locale to JS (needed by custom.js) --}}
    <script>
    window.appLocale = @json(app()->getLocale());
    </script>

    @include('admin.footer')
    @stack('script-page')

    <style>
        /* Custom modal open/close animation */
        #customModal.modal .modal-dialog {
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
            transform: scale(0.85);
            opacity: 0;
        }
        #customModal.modal.show .modal-dialog {
            transform: scale(1);
            opacity: 1;
        }
        #customModal.modal .modal-content {
            transition: box-shadow 0.3s ease-out;
        }
        #customModal.modal.show .modal-content {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>

    <div class="modal fade" id="customModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0 py-3">
                    <h5 class="modal-title fw-semibold text-primary"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body modal-body p-0" style="max-height: min(75vh, 800px); overflow-y: auto;">
                </div>
            </div>
        </div>
    </div>

</body>
<!-- [Body] end -->

</html>
