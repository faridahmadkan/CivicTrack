@if (session('status'))
    <div role="status" class="mb-5 rounded-xl border border-forest-100 bg-forest-50 px-4 py-3 text-sm leading-6 text-forest-900">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold">Please review the information below.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
