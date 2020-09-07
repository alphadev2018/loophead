@extends('common::framework')

@section('angular-styles')
    {{--angular styles begin--}}
		<link rel="stylesheet" href="client/styles.0d8d2a172fc87ef82ca9.css">
	{{--angular styles end--}}
@endsection

@section('angular-scripts')
    {{--angular scripts begin--}}
		<script>setTimeout(function() {
        var spinner = document.querySelector('.global-spinner');
        if (spinner) spinner.style.display = 'flex';
    }, 100);</script>
		<script src="client/runtime-es2015.c68673a5923e2195c688.js" type="module"></script>
		<script src="client/runtime-es5.c68673a5923e2195c688.js" nomodule defer></script>
		<script src="client/polyfills-es5.17c10e62de51b5b9d337.js" nomodule defer></script>
		<script src="client/polyfills-es2015.84f0e61e42a8dc9f39a4.js" type="module"></script>
		<script src="client/main-es2015.0d99f9c3adc5f386af5c.js" type="module"></script>
		<script src="client/main-es5.0d99f9c3adc5f386af5c.js" nomodule defer></script>
	{{--angular scripts end--}}
@endsection
