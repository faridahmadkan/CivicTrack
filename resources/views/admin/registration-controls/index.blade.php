@extends('layouts.admin')

@section('title', 'Registration controls')
@section('eyebrow', 'Public access settings')

@section('content')
    <div class="max-w-3xl">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Access governance</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Registration controls</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Each public path is controlled independently. Workforce applicants receive no staff or worker permissions unless an administrator approves their application.</p>
    </div>

    <div class="mt-7 grid gap-5 xl:grid-cols-3">
        @foreach ($contexts as $context)
            @php($control = $controls->get($context->value))
            <section class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div><h2 class="text-base font-semibold text-slate-900">{{ $context->label() }} registration</h2><p class="mt-1 text-xs leading-5 text-slate-500">{{ $context === App\Enums\RoleName::Citizen ? 'Resident accounts' : ucfirst($context->value).' applicants' }}</p></div>
                    @if ($control?->isOpenAt())<span class="rounded-full bg-forest-50 px-2.5 py-1 text-[0.68rem] font-semibold text-forest-800">Open now</span>@else<span class="rounded-full bg-sand-100 px-2.5 py-1 text-[0.68rem] font-semibold text-slate-600">Closed now</span>@endif
                </div>
                @if ($control)
                    <form method="POST" action="{{ route('admin.registration-controls.update', $context->value) }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="context" value="{{ $context->value }}">
                        <div class="flex items-start gap-3 rounded-xl bg-sand-50 p-3.5">
                            <input type="hidden" name="enabled" value="0">
                            <input id="enabled-{{ $context->value }}" type="checkbox" name="enabled" value="1" @checked($control->enabled) class="mt-0.5 size-4 rounded border-slate-300 text-forest-800 focus:ring-forest-700">
                            <label for="enabled-{{ $context->value }}" class="text-sm font-semibold text-slate-800">Allow this type of application</label>
                        </div>
                        <div>
                            <label for="opens-{{ $context->value }}" class="mb-1.5 block text-xs font-semibold text-slate-700">Open at <span class="font-normal text-slate-500">(optional)</span></label>
                            <input id="opens-{{ $context->value }}" name="opens_at" type="datetime-local" value="{{ $control->opens_at?->format('Y-m-d\TH:i') }}" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
                        </div>
                        <div>
                            <label for="closes-{{ $context->value }}" class="mb-1.5 block text-xs font-semibold text-slate-700">Close at <span class="font-normal text-slate-500">(optional)</span></label>
                            <input id="closes-{{ $context->value }}" name="closes_at" type="datetime-local" value="{{ $control->closes_at?->format('Y-m-d\TH:i') }}" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-800">Save {{ strtolower($context->label()) }} settings</button>
                    </form>
                @else
                    <p class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">Settings have not been initialized. Run the database seeders before managing public access.</p>
                @endif
            </section>
        @endforeach
    </div>
    @error('context')<p class="mt-4 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
    @error('opens_at')<p class="mt-4 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
    @error('closes_at')<p class="mt-4 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
@endsection
