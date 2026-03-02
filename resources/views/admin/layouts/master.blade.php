<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>{{ $pageTitle }}</title>

    {{-- FONT AWESOME LINK --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css"
        integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>


    {{-- VITE --}}
    @vite('resources/css/app.css')

    {{-- LIVEWIRE --}}
    @livewireStyles()

    <style>
        .add-event-form select:required:invalid {
            color: #afafaf;
        }

        .add-event-form option {
            color: #000;
        }

        .swal-button--confirm {
            background-color: #034889;
            color: #fff;
        }

        .swal-button--cancel {
            background-color: #dd3333;
            color: #fff;
        }


        .swal-button--confirm:hover {
            background-color: #033e75 !important;
        }

        .swal-button--cancel:hover {
            background-color: #cb2e2e !important;
        }
    </style>
</head>

<body class="bg-white">
    <div class="bg-registrationPrimaryColor">
        <div class="container mx-auto py-3 px-5">
            <div class="flex justify-between">
                <div>
                    <img src="{{ asset('assets/images/logo2.jpg') }}" class="max-h-16" alt="logo">
                </div>
                <div class="text-white font-semibold flex items-center gap-10">
                    {{-- <a href="{{ route('admin.dashboard.view') }}"
                        class="{{ request()->is('admin/dashboard*') ? 'text-dashboardNavItemHoverColor' : 'hover:underline' }}">Dashboard</a> --}}
                    <a href="{{ route('admin.event.view') }}"
                        class="{{ request()->is('admin/event*') ? 'text-dashboardNavItemHoverColor' : 'hover:underline' }}">Events</a>
                    <a href="{{ route('admin.member.view') }}"
                        class="{{ request()->is('admin/member*') ? 'text-dashboardNavItemHoverColor' : 'hover:underline' }}">Members</a>
                    {{-- <a href="{{ route('admin.delegate.view') }}"
                        class="{{ request()->is('admin/delegate*') ? 'text-dashboardNavItemHoverColor' : 'hover:underline' }}">Delegates</a> --}}
                    <a href="{{ route('admin.logout') }}" class="hover:underline">Logout</a>
                    <br><br>
                </div>
            </div>
        </div>
    </div>

    @if (request()->is('admin/event/*/*/dashboard*') ||
            request()->is('admin/event/*/*/detail*') ||
            request()->is('admin/event/*/*/registration-type*') ||
            request()->is('admin/event/*/*/delegate-fees*') ||
            request()->is('admin/event/*/*/promo-code*') ||
            request()->is('admin/event/*/*/registrant*') ||
            request()->is('admin/event/*/*/delegate*') ||
            request()->is('admin/event/*/*/printed-badge*') ||
            request()->is('admin/event/*/*/scanned-delegate*')) 
        @include('admin.layouts.event_navigations')
    @endif

    @yield('content')

    <div>
        @include('helpers.registration_loading_screen')
    </div>

    @livewireScripts()

    @if (request()->is('admin/event/add'))
        <script src="{{ asset('js/manageEvents/toggleEarlyBirdFieldsAdd.js') }}"></script>
        <script src="{{ asset('js/manageEvents/loadingButtonAdd.js') }}"></script>
    @elseif(request()->is('admin/event/*/*/edit'))
        <script src="{{ asset('js/manageEvents/toggleEarlyBirdFieldsEdit.js') }}"></script>
        <script src="{{ asset('js/manageEvents/loadingButtonEdit.js') }}"></script>
    {{-- @elseif (request()->is('admin/event/*/*/dashboard'))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
    @else
    @endif

    <script src="{{ asset('js/manageEvents/imagePreview.js') }}"></script>
    <script src="{{ asset('js/allswal.js') }}"></script>


    
</body>

</html>
