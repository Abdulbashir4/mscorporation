@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<section class="settings-page">
    <div class="purchase-heading"><h1>Settings</h1><p>Manage your company information and preferences.</p></div>
    <article class="card company-profile-summary">
        <div class="company-profile-summary-heading"><div class="company-profile-summary-logo">@if($companyProfile['logo'])<img src="{{ $companyProfile['logo'] }}" alt="Company logo">@else<x-icon name="bank"/>@endif</div><div><h2>{{ $companyProfile['name'] }}</h2><p>{{ $companyProfile['business'] }}</p></div><button type="button" class="primary-button" id="open-company-editor" aria-haspopup="dialog"><x-icon name="edit"/>Edit Company Information</button></div>
        <dl class="company-info-fields">@foreach(['name'=>'Company Name','business'=>'Business Type','phone'=>'Phone','email'=>'Email','address'=>'Address','license'=>'Trade License No.','vat'=>'BIN / VAT No.','tagline'=>'Tagline'] as $field=>$label)<div><dt>{{ $label }}</dt><dd>{{ $companyProfile[$field] ?: 'Not provided' }}</dd></div>@endforeach</dl>
        @if(session('company_saved'))<p class="company-editor-status" role="status">{{ session('company_saved') }}</p>@endif
    </article>
</section>
<template id="company-editor-template">
<form class="company-editor-form" id="company-editor-form" method="post" action="{{ route('settings.company.update') }}" enctype="multipart/form-data">
    @csrf
    <div class="company-editor-wide company-logo-field"><span>Company Logo</span><div class="company-logo-preview" id="company-logo-preview" role="img" aria-label="Company logo preview">@if($companyProfile['logo'])<img src="{{ $companyProfile['logo'] }}" alt="Current company logo">@else<span>No logo uploaded</span>@endif</div><input type="file" id="company-logo-input" accept="image/png,image/jpeg,image/webp" aria-label="Company logo"><small>PNG, JPG or WebP · Maximum 2 MB</small><button type="button" id="company-logo-remove">Remove Logo</button></div>
    <label>Company Name <span>*</span><input name="name" value="{{ $companyProfile['name'] }}" required maxlength="150" autocomplete="organization"></label>
    <label>Business Type<input name="business" value="{{ $companyProfile['business'] }}" maxlength="200"></label>
    <label>Phone<input name="phone" value="{{ $companyProfile['phone'] }}" type="tel" maxlength="50" autocomplete="tel"></label>
    <label>Email<input name="email" value="{{ $companyProfile['email'] }}" type="email" maxlength="150" autocomplete="email"></label>
    <label class="company-editor-wide">Address<textarea name="address" rows="3" maxlength="500" autocomplete="street-address">{{ $companyProfile['address'] }}</textarea></label>
    <label>Trade License No.<input name="license" value="{{ $companyProfile['license'] }}" maxlength="100"></label>
    <label>BIN / VAT No.<input name="vat" value="{{ $companyProfile['vat'] }}" maxlength="100"></label>
    <label class="company-editor-wide">Tagline<input name="tagline" value="{{ $companyProfile['tagline'] }}" maxlength="200"></label>
    <section class="company-info-card company-editor-wide" aria-labelledby="company-preview-title">
        <div class="company-info-content">
            <h4 id="company-preview-title">Company Information Preview</h4>
            <dl class="company-info-fields">
                @foreach(['name'=>'Company Name','business'=>'Business Type','phone'=>'Phone','email'=>'Email','address'=>'Address','license'=>'Trade License No.','vat'=>'BIN / VAT No.','tagline'=>'Tagline'] as $field=>$label)
                    <div @class(['company-info-wide' => $field === 'address'])>
                        <dt>{{ $label }}</dt>
                        <dd data-company-preview="{{ $field }}">{{ $companyProfile[$field] ?? 'Not provided' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>
    <p class="company-editor-note">Company information and logo are saved to the database and displayed across the site.</p>
    <p id="company-editor-status" role="status" class="company-editor-status"></p>
    <div class="company-editor-actions"><button type="button" class="company-editor-cancel">Cancel</button><button type="submit" class="primary-button">Save Changes</button></div>
</form>
</template>
@endsection




