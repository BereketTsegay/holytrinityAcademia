<x-mail::message>
# Welcome to School Management System! 🎓

You have been invited to join the **School Management System** as a **{{ $user->roles->first()->name }}**.

## Your Account Details

**Name:** {{ $user->first_name }} {{ $user->last_name }}  
**Email:** {{ $user->email }}  
**Temporary Password:** {{ $tempPassword }}  
**Role:** {{ ucfirst($user->roles->first()->name) }}

@if($user->isStudent())
**Student ID:** {{ $user->student_id }}
@elseif($user->isTeacher())
**Employee ID:** {{ $user->employee_id }}
@endif

<x-mail::button :url="$loginUrl">
Access Your Account
</x-mail::button>

## Getting Started

1. **Click the button above** to access your account
2. **Log in** using your email and the temporary password provided
3. **Complete your profile** by updating your personal information
4. **Change your password** for security reasons
5. **Explore the system** and its features based on your role

## Security Notice

- This is a temporary password - please change it immediately after login
- Keep your login credentials secure and do not share them
- The invitation link will expire for security reasons

## Need Help?

If you have any questions or encounter issues while accessing your account, please contact the system administrator.

We're excited to have you on board! 🙂

Best regards,  
**School Management Team**

---
*This is an automated message. Please do not reply to this email.*
</x-mail::message>