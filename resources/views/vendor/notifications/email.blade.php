@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@lang('You recently requested to reset your password for your account. Use the button below to reset it. ')
<strong> This password reset link will expire in 60 minutes.</strong>

{{-- Action Button --}}
@isset($actionText)
<?php
    switch ($level) {
        case 'success':
        case 'error':
            $color = $level;
            break;
        default:
            $color = 'primary';
    }
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
<p>If you did not request a password reset, please ignore this email or <a href="https://greendropship.com/contact-us/" target="_blank">Contact our support team</a> if you have any questions.</p>

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Thanks'),<br>
@lang('The Support Team')
@endif

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
@lang(
    "If you’re having trouble clicking the \"Reset Your Password\" button, copy and paste the URL below\n".
    'into your web browser: [:actionURL](:actionURL)',
    [
        'actionURL' => $actionUrl,
    ]
)
@endslot
@endisset
@endcomponent
