@extends('layouts.app')
@section('title', ucfirst($type))
@section('content')
<div class="space-y-5">
    <h1 class="text-2xl font-bold">{{ ucfirst($type) }}</h1>
    <nav class="flex gap-4"><a href="{{ route('customers.index') }}">Customers</a><a href="{{ route('suppliers.index') }}">Suppliers</a></nav>
    @if(session('contact_status'))<p role="status" class="rounded bg-green-50 p-3 text-green-800">{{ session('contact_status') }}</p>@endif
    <section class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="mb-4 text-lg font-semibold">{{ $editing ? 'Edit' : 'Add New' }} {{ $type === 'suppliers' ? 'Supplier' : 'Customer' }}</h2>
        <form method="post" action="{{ $editing ? route($type.'.update', $editing->id) : route($type.'.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            @if($editing) @method('PATCH') @endif
            <div><label for="contact-id" class="mb-1 block text-sm font-semibold">ID</label><input id="contact-id" value="{{ $editing?->id ?? 'Auto generated on save' }}" readonly class="w-full rounded border border-slate-300 bg-slate-50 p-2"></div>
            @foreach(['name' => 'Name', 'email' => 'Gmail / Email', 'phone' => 'Phone', 'address' => 'Address', 'contact_person' => 'Contact Person'] + ($type === 'customers' ? ['class' => 'Customer Type'] : []) as $field => $label)
            <div><label for="{{ $field }}" class="mb-1 block text-sm font-semibold">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ old($field, $editing?->{$field}) }}" maxlength="{{ $field === 'phone' ? 50 : ($field === 'email' ? 254 : 255) }}" @required(in_array($field, ['name', 'class'])) class="w-full rounded border border-slate-300 p-2">@error($field)<p class="text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror</div>
            @endforeach
            <div class="flex items-center gap-4 sm:col-span-2"><button class="contact-save-button" type="submit">{{ $editing ? 'Update' : 'Save' }}</button>@if($editing)<a href="{{ route($type.'.index') }}">Cancel</a>@endif</div>
        </form>
    </section>
    <section class="overflow-x-auto rounded-xl border border-slate-200 bg-white p-4"><table class="w-full text-left text-sm"><thead><tr>@foreach(['ID', 'Name', 'Gmail / Email', 'Phone', 'Address', 'Contact Person', 'Actions'] as $heading)<th class="p-3">{{ $heading }}</th>@endforeach</tr></thead><tbody>
    @forelse($contacts as $contact)<tr class="border-t border-slate-100"><td class="p-3">{{ $contact->id }}</td><td class="p-3">{{ $contact->name }}</td><td class="p-3">{{ $contact->email ?? '—' }}</td><td class="p-3">{{ $contact->phone }}</td><td class="p-3">{{ $contact->address }}</td><td class="p-3">{{ $contact->contact_person }}</td><td class="p-3"><div class="flex gap-3"><a href="{{ route($type.'.edit', $contact->id) }}">Edit</a><form method="post" action="{{ route($type.'.destroy', $contact->id) }}" onsubmit="return confirm('Delete this contact?')">@csrf @method('DELETE')<button type="submit" class="text-rose-700">Delete</button></form></div></td></tr>
    @empty<tr><td colspan="7" class="p-5 text-center">No records found. Add your first contact above.</td></tr>@endforelse
    </tbody></table><div class="mt-4">{{ $contacts->links() }}</div></section>
</div>
@endsection
