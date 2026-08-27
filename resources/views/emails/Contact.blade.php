<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; font-size: 14px; color:#222; line-height:1.6;">

    <h2 style="margin-bottom: 4px;">New contact form submission</h2>
    <p style="color:#666; margin-top:0;">Received {{ $submittedAt->format('d M Y, h:i A') }}</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse; width:100%; max-width:520px;">
        <tr>
            <td style="width:120px; color:#666;">Name</td>
            <td><strong>{{ $data['name'] }}</strong></td>
        </tr>
        <tr>
            <td style="color:#666;">Email</td>
            <td><a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a></td>
        </tr>
        @if(!empty($data['phone']))
        <tr>
            <td style="color:#666;">Phone</td>
            <td>{{ $data['phone'] }}</td>
        </tr>
        @endif
        @if(!empty($data['topic']))
        <tr>
            <td style="color:#666;">Topic</td>
            <td>{{ $data['topic'] }}</td>
        </tr>
        @endif
    </table>

    <p style="color:#666; margin-bottom:4px; margin-top:20px;">Message</p>
    <div style="padding:12px 16px; background:#f6f6f6; border-radius:8px; white-space: pre-wrap;">{{ $data['message'] }}</div>

    <p style="color:#999; font-size:12px; margin-top:24px;">Reply directly to this email to respond to {{ $data['name'] }}.</p>

</body>
</html>