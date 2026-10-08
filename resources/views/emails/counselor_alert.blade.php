<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:0 auto;padding:24px;">
    <div style="background:#064e3b;border-radius:16px 16px 0 0;padding:20px 24px;color:#ffffff;">
        <div style="font-size:12px;font-weight:bold;letter-spacing:2px;opacity:0.8;">PSU STUDENT SUPPORT</div>
        <h2 style="margin:6px 0 0;font-size:20px;">{{ $alertTitle }}</h2>
    </div>
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 16px 16px;padding:24px;color:#334155;font-size:15px;line-height:1.6;">
        <p style="margin:0 0 16px;">{!! nl2br(e($alertBody)) !!}</p>
        @if(!empty($actionUrl))
            <a href="{{ $actionUrl }}" style="display:inline-block;background:#059669;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 24px;border-radius:10px;">{{ $actionLabel }}</a>
        @endif
        <p style="font-size:12px;color:#94a3b8;margin:20px 0 0;">This is an automated message from the PSU Mental Health Portal. Please log in to review the details.</p>
    </div>
</div>
</body>
</html>
