<!-- resources/views/emails/contact.blade.php -->
@component('mail::message')
# Nouveau message de contact

**Nom:** {{ $contact->name }}  
**Email:** {{ $contact->email }}  
**Sujet:** {{ $contact->subject }}  

**Message:**  
{{ $contact->message }}

@component('mail::button', ['url' => 'mailto:'.$contact->email])
Répondre à {{ $contact->name }}
@endcomponent

Merci,  
{{ config('app.name') }}
@endcomponent