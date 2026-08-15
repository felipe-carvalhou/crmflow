<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-slate-900">Perfil</h1>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="app-card p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="app-card p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="app-card border-rose-200/70 p-6 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
