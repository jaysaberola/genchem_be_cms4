Dear {{ $adminInfo->name ?? 'Admin' }},

{{ $clientInfo['name'] }} has sent an inquiry for your action.
Please see details of the inquiry below.

Name: {{ $clientInfo['name'] }}
@if(!empty($clientInfo['company']))
Company: {{ $clientInfo['company'] }}
@endif
Email: {{ $clientInfo['email'] }}
Contact Number: {{ $clientInfo['contact'] }}
Message: {{ $clientInfo['message'] }}


Regards,
{{ $setting->company_name }}



{{ $setting->company_name }}
{{ $setting->company_address }}
{{ $setting->tel_no }} | {{ $setting->mobile_no }}

{{ url('/') }}
