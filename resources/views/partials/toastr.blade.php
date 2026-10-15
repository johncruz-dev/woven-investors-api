<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
    #toast-container > .toast {
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.16);
        opacity: 1;
        border-radius: 10px;
        padding: 14px 14px 14px 50px;
        background-image: none !important;
    }
    #toast-container > .toast-success { background-color: #0f5c56; }
    #toast-container > .toast-error { background-color: #b42318; }
    #toast-container > .toast-info { background-color: #175cd3; }
    #toast-container > .toast-warning { background-color: #b54708; }
    #toast-container > .toast:before {
        content: '';
        position: absolute;
        left: 16px;
        top: 50%;
        width: 18px;
        height: 18px;
        transform: translateY(-50%);
        background-repeat: no-repeat;
        background-position: center;
        background-size: 18px 18px;
    }
    #toast-container > .toast-success:before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23fff' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m4.5 12.75 6 6 9-13.5'/%3E%3C/svg%3E");
    }
    #toast-container > .toast-error:before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23fff' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 18 18 6M6 6l12 12'/%3E%3C/svg%3E");
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    toastr.options = {
        closeButton: true,
        newestOnTop: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        preventDuplicates: true,
        timeOut: 4000,
        extendedTimeOut: 1500,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut',
    };

    @if (session('status'))
        toastr.success(@json(session('status')));
    @endif

    @if (session('success'))
        toastr.success(@json(session('success')));
    @endif

    @if (session('error'))
        toastr.error(@json(session('error')));
    @endif

    @if (session('warning'))
        toastr.warning(@json(session('warning')));
    @endif

    @if (session('info'))
        toastr.info(@json(session('info')));
    @endif

    @if (isset($errors) && $errors->any())
        @foreach ($errors->all() as $message)
            toastr.error(@json($message));
        @endforeach
    @endif

    @foreach (($upcomingLessonToasts ?? []) as $toast)
        toastr.warning(@json($toast['body']), @json($toast['title']));
    @endforeach
</script>
