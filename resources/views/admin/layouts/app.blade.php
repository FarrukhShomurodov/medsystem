<!DOCTYPE html>
{{-- layout-menu-collapsed --}}
<html lang="en" class="light-style layout-menu-fixed " dir="ltr" data-theme="theme-default"
      data-assets-path="/" data-template="horizontal-menu-template">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>

    @yield('title')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/boxicons.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fontawesome.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/fonts/flag-icons.css') }}"/>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/css/rtl/core.css') }}?v={{ now()->addHours(2)->timestamp }}"
          class="template-customizer-core-css"/>
    <link rel="stylesheet" href="{{ asset('vendor/css/rtl/theme-default.css') }}?v={{ now()->addHours(2)->timestamp }}"
          class="template-customizer-theme-css"/>
    <link rel="stylesheet" href="{{ asset('css/demo.css') }}?v={{ now()->addHours(2)->timestamp }}"/>

    <!-- Vendors CSS -->
    <link rel="stylesheet"
          href="{{ asset('vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}?v={{ now()->addHours(2)->timestamp }}"/>

    <link rel="stylesheet" href="{{ asset('vendor/libs/flatpickr/flatpickr.css') }}"/>

    <link rel="stylesheet"
          href="{{ asset('vendor/libs/typeahead-js/typeahead.css') }}?v={{ now()->addHours(2)->timestamp }}"/>
    <link rel="stylesheet"
          href="{{ asset('vendor/libs/bs-stepper/bs-stepper.css') }}?v={{ now()->addHours(2)->timestamp }}"/>
    <link rel="stylesheet"
          href="{{ asset('vendor/libs/bootstrap-select/bootstrap-select.css') }}?v={{ now()->addHours(2)->timestamp }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/libs/select2/select2.css') }}?v={{ now()->addHours(2)->timestamp }}"/>

    <!-- Helpers -->
    <script src="{{ asset('vendor/js/helpers.js') }}"></script>
    {{--    <script src="{{ asset('vendor/js/template-customizer.js') }}"></script>--}}
    <script src="{{ asset('js/config.js') }}"></script>

    <!--Summernote-->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

    <style>
        body {
            overflow-x: hidden;
        }

        .alert-container .alert {
            min-width: 250px;
            max-width: 400px;
            opacity: 0.8;
            top: 50px;
            z-index: 10000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .alert-container .alert:hover {
            opacity: 1;
        }

        .alert ul {
            margin-bottom: 0;
            padding-left: 1em;
        }

        .alert .btn-close {
            font-size: 0.8rem;
        }

        .icon-border {
            border: 1px solid !important;
        }

        .input-group-text.text-danger {
            border-color: #dc3545 !important;
            color: #dc3545 !important;
        }

        .is-invalid {
            border: 1px solid #dc3545 !important;
            border-radius: .25rem !important;
            border-left: none !important;
            border-bottom-left-radius: 0px !important;
            border-top-left-radius: 0px !important;
        }

        .invalid-feedback {
            display: block;
        }

        .room-map {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 колонки */
            gap: 16px;
            margin-top: 1rem;
            border: 1px solid #ddd;
            padding: 16px;
            background: #f5f5f5;
            border-radius: 10px;
            min-height: 200px;
        }

        .bed-item {
            background-color: #ffffff;
            padding: 20px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .bed-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1);
        }

        #add-bed {
            margin-top: 20px;
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        #add-bed i {
            margin-right: 5px;
        }

        .bed-item span {
            display: block;
            margin-top: 8px;
            font-size: 0.9rem;
            color: #555;
        }

        #calendar-container {
            overflow-x: auto;
        }

        #calendar-table th, #calendar-table td {
            white-space: nowrap;
            min-width: 150px;
            vertical-align: middle;
        }

        .status-cell {
            cursor: pointer;
        }
    </style>
</head>

<body>

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar  ">
    <div class="layout-container">

        @include('admin.layouts.menu')


        <!-- Layout container -->
        <div class="layout-page">

            @include('admin.layouts.header')

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Core JS -->
<script src="{{ asset('vendor/libs/jquery/jquery.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="{{ asset('vendor/libs/popper/popper.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="{{ asset('vendor/js/bootstrap.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script
    src="{{ asset('vendor/libs/bootstrap-select/bootstrap-select.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="{{ asset('vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}?v={{ now()->addHours(2)->timestamp }}"
        defer></script>
<script src="{{ asset('vendor/libs/hammer/hammer.js') }}?v={{ now()->addHours(2)->timestamp }}" defer></script>
<script src="{{ asset('vendor/vendor/libs/i18n/i18n.js') }}?v={{ now()->addHours(2)->timestamp }}" defer></script>
<script src="{{ asset('vendor/libs/typeahead-js/typeahead.js') }}?v={{ now()->addHours(2)->timestamp }}" defer></script>
<script src="{{ asset('vendor/js/menu.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>

<!-- Vendors JS -->
<script src="{{ asset('vendor/libs/bs-stepper/bs-stepper.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="{{ asset('vendor/libs/select2/select2.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script
    src="{{ asset('vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script
    src="{{ asset('vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="{{ asset('vendor/libs/moment/moment.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="{{ asset('js/forms-selects.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script src="{{ asset('vendor/libs/flatpickr/flatpickr.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('js/main.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>

<script src="{{ asset('js/forms-pickers.js') }}"></script>

<script src="{{ asset('js/form-wizard-numbered.js') }}"></script>
<script src="{{ asset('js/form-wizard-validation.js') }}"></script>

@yield('scripts')


<script>
    $(document).ready(function () {
        $('.flatpickr-date').flatpickr({
            dateFormat: 'Y-m-d', // Формат для отправки
        })

        $('.flatpickr-date-time').flatpickr({
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            time_24hr: true
        });


        // alert
        $('.alert').each(function (index) {
            setTimeout(() => {
                $(this).fadeOut('slow', function () {
                    $(this).remove();
                });
            }, 3000 * (index + 1));
        });

        // Image
        $('#imageInput').on('change', function () {
            const files = Array.from($(this)[0].files);
            const imagePreview = $('#imagePreview');

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const imgElement = $('<img>', {
                        src: e.target.result,
                        alt: file.name,
                        class: 'uploaded-image'
                    });

                    const imgContainer = $('<div>', {class: 'image-container td__img'});
                    imgContainer.append(imgElement);

                    const deleteBtn = $('<button>', {
                        class: 'btn btn-danger btn-sm delete-image',
                        text: 'Удалить',
                        click: function () {
                            imgContainer.remove();
                            const index = files.indexOf(file);
                            if (index !== -1) {
                                files.splice(index, 1);
                                updateFileInput(files);
                            }
                        }
                    });
                    imgContainer.append(deleteBtn);

                    imagePreview.append(imgContainer);
                };
                reader.readAsDataURL(file);
            });
        });

        function updateFileInput(files) {
            const input = $('#imageInput')[0];
            const fileList = new DataTransfer();
            files.forEach(file => {
                fileList.items.add(file);
            });
            input.files = fileList.files;
        }

        $(document).on('click', '.delete-image', function () {
            const path = $(this).data('photo-path');
            if (path) {
                $.ajax({
                    url: `/api/delete/image/${path}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        console.log(res);
                        $(this).closest('.image-container').remove();
                    }.bind(this),
                    error: function (error) {
                        console.error('Error deleting photo:', error);
                    }
                });
            }
        });

        // select2
        $('.select2').select2({
            tags: true
        });
    })
</script>
</body>
</html>
