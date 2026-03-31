<x-mail::message>
# Здравствуйте!

Пожалуйста, нажмите кнопку ниже, чтобы подтвердить адрес электронной почты.

<x-mail::button :url="$actionUrl" color="primary">
Подтвердить email
</x-mail::button>

Если вы не создавали аккаунт, никаких дополнительных действий не требуется.

С уважением,<br>
{{ config('app.name') }}

<x-slot:subcopy>
Если у вас не получается нажать кнопку "Подтвердить email", скопируйте и вставьте ссылку ниже в адресную строку браузера:
<span class="break-all">[{{ $actionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
</x-mail::message>
