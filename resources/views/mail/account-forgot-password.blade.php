@include('mail.partials.header', ['subject' => $subject])
            
<h2 style="font-family: 'Raleway', sans-serif; font-weight: 600; margin: 0; margin-bottom: 10px;">Password Reset Request</h2>

<p style="font-size: 15px; line-height: 1.6; margin: 0; margin-bottom: 5px;">Hi {{ $mail['user']->first_name }} {{ $mail['user']->last_name }},</p>

<p style="font-size: 15px; line-height: 1.6; margin: 0;">We received a request to reset your password for your {{ config('app.name') }} account. Click the button below to reset it:</p>

<div style="margin: 25px 0;">
    <a style="margin: 0; background-color: #353535; color: white; border-radius: 8px; padding: 15px 25px; display: inline-block; font-size: 17px; text-decoration: none;" href="#">Reset Password</a>
</div>

<p style="font-size: 14px; line-height: 1.6; margin: 0;">If you did not request a password reset, you can safely ignore this email.</p>

@include('mail.partials.footer')