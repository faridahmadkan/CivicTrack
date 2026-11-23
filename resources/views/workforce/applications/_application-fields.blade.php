<div class="grid gap-5 sm:grid-cols-2">
    <label for="position_title" class="block text-sm font-semibold text-slate-800 sm:col-span-2">Position of interest
        <input id="position_title" name="position_title" value="{{ old('position_title') }}" required maxlength="120" class="mt-1.5 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal shadow-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100" placeholder="For example, service coordinator">
        @error('position_title')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
    </label>
    <label for="requested_unit_id" class="block text-sm font-semibold text-slate-800 sm:col-span-2">Organization unit
        <select id="requested_unit_id" name="requested_unit_id" required class="mt-1.5 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal shadow-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100"><option value="">Choose an active unit</option>@foreach ($organizationUnits as $unit)<option value="{{ $unit->id }}" @selected(old('requested_unit_id') == $unit->id)>{{ $unit->name }} · {{ ucfirst($unit->unit_type->value) }}</option>@endforeach</select>
        @error('requested_unit_id')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
    </label>
    <label for="experience_summary" class="block text-sm font-semibold text-slate-800">Relevant experience <span class="font-normal text-slate-500">(optional)</span>
        <textarea id="experience_summary" name="experience_summary" rows="5" maxlength="6000" class="mt-1.5 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal leading-6 shadow-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100">{{ old('experience_summary') }}</textarea>
        @error('experience_summary')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
    </label>
    <label for="qualification_summary" class="block text-sm font-semibold text-slate-800">Qualifications and skills <span class="font-normal text-slate-500">(optional)</span>
        <textarea id="qualification_summary" name="qualification_summary" rows="5" maxlength="6000" class="mt-1.5 w-full rounded-xl border border-line bg-white px-3.5 py-3 text-sm font-normal leading-6 shadow-sm focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-forest-100">{{ old('qualification_summary') }}</textarea>
        @error('qualification_summary')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
    </label>
    <label for="documents" class="block text-sm font-semibold text-slate-800 sm:col-span-2">Supporting documents <span class="font-normal text-slate-500">(optional, up to 5)</span>
        <input id="documents" name="documents[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="mt-1.5 block w-full rounded-xl border border-line bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-sand-100 file:px-4 file:py-3 file:text-xs file:font-semibold file:text-slate-700">
        <span class="mt-1.5 block text-xs leading-5 text-slate-500">PDF or image files, up to 10 MB each. Documents remain private and are available only to authorized reviewers and you.</span>
        @error('documents')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
        @error('documents.*')<span class="mt-1 block text-xs font-medium text-coral-600">{{ $message }}</span>@enderror
    </label>
</div>
