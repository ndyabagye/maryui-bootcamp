<?php

use App\Models\Country;
use App\Models\Language;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithFileUploads;

    // Component parameter
    public User $user;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('nullable|image|max:1024')]
    public $photo;

    #[Rule('required')]
    public array $my_languages = [];

    //optional
    #[Rule('sometimes')]
    public ?int $country_id = null;

    #[Rule('sometimes')]
    public ?string $bio = null;

    public function mount(): void
    {
        $this->fill($this->user);

        $this->my_languages = $this->user->languages->pluck('id')->all();
    }

    public function save(): void
    {
        //  validate
        $data = $this->validate();
        //  update
        $this->user->update($data);

        //  sync selection
        $this->user->languages()->sync($this->my_languages);

        // upload file and save the avatar `url` on User model
        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $this->user->update(['avatar' => "/storage/${url}"]);
        }
        //  toast and redirect
        $this->success('User updated.', redirectTo: '/users');
    }

    //    Used to fill countries and languages combobox
    public function with(): array
    {
        return [
            'countries' => Country::all(),
            'languages' => Language::all(),
        ];
    }

}; ?>

<div>
    <x-header title="Update {{ $user->name }}" separator/>


    <x-form wire:submit="save">
        {{--        Basic section--}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Basic" subtitle="Basic info from user" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">
                <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                    <img src="{{ $user->avatar ?? '/empty-user.jpg' }}" class="h-40 rounded-lg"/>
                </x-file>
                <x-input label="Name" wire:model="name"/>
                <x-input label="Email" wire:model="email"/>
                <x-select label="Country" wire:model="country_id" :options="$countries" placeholder="---"/>
            </div>
        </div>

        <hr class="my-5">

        {{--        more information--}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Details" subtitle="More about the user" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">
                {{-- multi selection--}}
                <x-choices-offline
                    label="My Languages"
                    wire:model="my_languages"
                    :options="$languages"
                    searchable
                />
                <x-editor wire:model="bio" label="Bio" hint="The great biography"/>
            </div>
        </div>


        <x-slot:actions>
            <x-button label="Cancel" link="/users"/>
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
