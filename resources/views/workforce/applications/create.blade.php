@extends('layouts.citizen')

@section('title', 'Apply for '.($type === App\Enums\WorkforceApplicationType::Staff ? 'staff access' : 'field worker access'))

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('workforce.applications.index') }}" class="text-sm font-semibold text-forest-800 hover:text-forest-950">← Workforce applications</a>
        <p class="mt-6 text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Application · {{ ucfirst($type->value) }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Apply for {{ $type === App\Enums\WorkforceApplicationType::Staff ? 'staff coordination' : 'field worker' }} access</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Applications are reviewed by an administrator. Approval creates a separate {{ $type->value }} profile and scoped organization membership.</p>
    </div>

    @if (! $registrationOpen)
        <div class="mt-7 max-w-3xl rounded-2xl border border-amber-200 bg-amber-50 p-5"><h2 class="text-sm font-semibold text-amber-950">Applications are currently closed</h2><p class="mt-1 text-sm leading-6 text-amber-900">This application path is controlled by the operating organization. Your information has not been submitted.</p></div>
    @elseif ($organizationUnits->isEmpty())
        <div class="mt-7 max-w-3xl rounded-2xl border border-line bg-white p-5"><h2 class="text-sm font-semibold text-slate-900">No service units are available</h2><p class="mt-1 text-sm leading-6 text-slate-600">The organization has not configured an active unit for applications yet. Please return later.</p></div>
    @else
        <form method="POST" action="{{ route('workforce.applications.store', $type->value) }}" enctype="multipart/form-data" class="mt-7 max-w-3xl rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-8">
            @csrf
            <x-form-messages />
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><label for="position_title" class="mb-2 block text-sm font-semibold text-slate-800">Position of interest</label><input id="position_title" name="position_title" value="{{ old('position_title') }}" required maxlength="120" class="w-full rounded-lg border border-line px-3.5 py-3 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100" placeholder="For example, service coordinator">@error('position_title')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2"><label for="requested_unit_id" class="mb-2 block text-sm font-semibold text-slate-800">Organization unit</label><select id="requested_unit_id" name="requested_unit_id" required class="w-full rounded-lg border border-line bg-white px-3.5 py-3 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100"><option value="">Choose an active unit</option>@foreach ($organizationUnits as $unit)<option value="{{ $unit->id }}" @selected(old('requested_unit_id') == $unit->id)>{{ $unit->name }} · {{ ucfirst($unit->unit_type->value) }}</option>@endforeach</select>@error('requested_unit_id')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="experience_summary" class="mb-2 block text-sm font-semibold text-slate-800">Relevant experience <span class="font-normal text-slate-500">(optional)</span></label><textarea id="experience_summary" name="experience_summary" rows="5" maxlength="6000" class="w-full rounded-lg border border-line px-3.5 py-3 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">{{ old('experience_summary') }}</textarea>@error('experience_summary')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="qualification_summary" class="mb-2 block text-sm font-semibold text-slate-800">Qualifications and skills <span class="font-normal text-slate-500">(optional)</span></label><textarea id="qualification_summary" name="qualification_summary" rows="5" maxlength="6000" class="w-full rounded-lg border border-line px-3.5 py-3 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">{{ old('qualification_summary') }}</textarea>@error('qualification_summary')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2"><label for="documents" class="mb-2 block text-sm font-semibold text-slate-800">Supporting documents <span class="font-normal text-slate-500">(optional, up to 5)</span></label><input id="documents" name="documents[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="block w-full rounded-lg border border-line bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-sand-100 file:px-4 file:py-3 file:text-xs file:font-semibold file:text-slate-700"> <p class="mt-1.5 text-xs text-slate-500">PDF or image files, up to 10 MB each. Documents are stored privately and only available to authorized reviewers and you.</p>@error('documents')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror @error('documents.*')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
            </div>
            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between"><a href="{{ route('workforce.applications.index') }}" class="text-center text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a><button type="submit" class="rounded-lg bg-forest-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-forest-800">Submit application</button></div>
        </form>
    @endif
@endsection
