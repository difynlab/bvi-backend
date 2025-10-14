@include('mail.partials.header', ['subject' => $subject])

<h2 style="font-family: 'Raleway', sans-serif; font-weight: 600; margin: 0; margin-bottom: 10px;">Welcome to {{ config('app.name') }}, {{ $mail['user']->first_name }} {{ $mail['user']->last_name }}</h2>

<p style="font-size: 15px; line-height: 1.6; margin: 0; margin-bottom: 5px;">We're excited to have you onboard as a <strong>Member</strong>! On <strong>{{ config('app.name') }}</strong>.</p>

<div style="margin-top: 25px;">
    <a style="margin: 0; background-color: #353535; color: white; border-radius: 8px; padding: 15px 25px; display: inline-block; font-size: 17px; text-decoration: none;" href="{{ route('member.dashboard') }}">Dashboard</a>
</div>

@include('mail.partials.footer')