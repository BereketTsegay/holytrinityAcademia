<x-mail::message>
# Welcome to School Management System!

You have been invited to join the School Management System as a {{ $user->roles->first()->name }}.

Here are your login credentials:

**Email:** {{ $user->email }}
**Temporary Password:** {{ $tempPassword }}

<x-mail::button :url="$loginUrl">
Login to Your Account
</x-mail::button>

Please login and complete your profile setup. Remember to change your password after first login.

Thanks,<br>
School Management Team
</x-mail::message>
