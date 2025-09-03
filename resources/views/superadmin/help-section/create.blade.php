@extends('layouts.main')
@section('title', __('Upload'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('help-section.index') }}">{{ __('Help') }}</a></li>
    <li class="breadcrumb-item">{{ __('Upload') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            @if (tenant('id') == null)
                @if ($databasePermission == null)
                    <div class="alert alert-warning">
                        {{ __('Please on your database permission to create auto generate DATABASE.') }}<a
                            href="{{ route('settings') }}" target="_blank">{{ __('On database permission') }}</a>
                    </div>
                @else
                    <div class="alert alert-warning">
                        {{ __('Please off your database permission to create your own DATABASE.') }}<a
                            href="{{ route('settings') }}" target="_blank">{{ __('Off database permission') }}</a>
                    </div>
                @endif
            @endif
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Upload Videos, Files or Images for help') }}</h5>
                    </div>
                    <div class="card-body">
                        <h2>
                            Videos, Files or Images will be uploaded here
                        </h2>
                    </div>
                </div>
        </section>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush
@push('javascript')
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    <script>
        $("#phone").intlTelInput({
            geoIpLookup: function(callback) {
                $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : "";
                    callback(countryCode);
                });
            },
            initialCountry: "auto",
            separateDialCode: true,
        });
        $('#phone').on('countrychange', function(e) {
            $(this).val('');
            var selectedCountry = $(this).intlTelInput('getSelectedCountryData');
            var dialCode = selectedCountry.dialCode;
            var maskNumber = intlTelInputUtils.getExampleNumber(selectedCountry.iso2, 0, 0);
            maskNumber = intlTelInputUtils.formatNumber(maskNumber, selectedCountry.iso2, 2);
            maskNumber = maskNumber.replace('+' + dialCode + ' ', '');
            mask = maskNumber.replace(/[0-9+]/ig, '0');
            $('input[name="country_code"]').val(selectedCountry.iso2);
            $('input[name="dial_code"]').val(dialCode);
            $('#phone').mask(mask, {
                placeholder: maskNumber
            });
        });
    </script>
@endpush
