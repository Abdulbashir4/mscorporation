<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierController extends Controller
{
    private function type(Request $request): string
    {
        return str_starts_with($request->route()->getName(), 'suppliers.') ? 'suppliers' : 'customers';
    }

    public function index(Request $request): View
    {
        $type = $this->type($request);

        return view('contact-management', ['type' => $type, 'contacts' => DB::table($type)->orderBy('name')->paginate(25), 'editing' => null]);
    }

    public function edit(Request $request, string $contact): View
    {
        $type = $this->type($request);

        return view('contact-management', ['type' => $type, 'contacts' => DB::table($type)->orderBy('name')->paginate(25), 'editing' => DB::table($type)->where('id', $contact)->firstOrFail()]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:254'], 'phone' => ['nullable', 'string', 'max:50'], 'address' => ['nullable', 'string', 'max:255'], 'contact_person' => ['nullable', 'string', 'max:255']]);
        if ($this->type($request) === 'customers') {
            $data += $request->validate(['class' => ['required', 'string', 'max:255']]);
        }

        return $data;
    }

    public function store(Request $request): RedirectResponse
    {
        $type = $this->type($request);
        DB::table($type)->insert($this->validated($request) + ['created_at' => now()]);

        return redirect()->route($type.'.index')->with('contact_status', 'Contact added.');
    }

    public function update(Request $request, string $contact): RedirectResponse
    {
        $type = $this->type($request);
        DB::table($type)->where('id', $contact)->firstOrFail();
        DB::table($type)->where('id', $contact)->update($this->validated($request));

        return redirect()->route($type.'.index')->with('contact_status', 'Contact updated.');
    }

    public function destroy(Request $request, string $contact): RedirectResponse
    {
        $type = $this->type($request);
        DB::table($type)->where('id', $contact)->firstOrFail();
        DB::table($type)->where('id', $contact)->delete();

        return redirect()->route($type.'.index')->with('contact_status', 'Contact deleted.');
    }
}
