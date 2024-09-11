<?php

use App\Models\Country;
use App\Models\Language;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithFileUploads;

    public User $user;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required')]
    public string $email = '';

    #[Rule('required', as: "languages")]
    public array $my_languages = [];

    #[Rule('sometimes')]
    public ?int $country_id = null;

    #[Rule('sometimes')]
    public ?string $bio = null;

    #[Rule('nullable|image|max:1024')]
    public $photo;

    public string $first_password = 'supersecret';

    public function mount(): void
    {
        // Initialize the $user property with a new User instance
        $this->user = new User();
    }

    public function save(): void
    {
        //  validate
        $data = $this->validate();

        $data['avatar'] = '/empty-user.jpg';
        $data['password'] = Hash::make($this->first_password);
        $data['email_verified_at'] = now();

        $this->user = User::create($data);

        //  sync selection
        $this->user->languages()->sync($this->my_languages);
        //  upload file and save the avatar `url` on User model
        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $this->user->update(['avatar' => '/storage/$url']);
        }
        //  toast and redirect
        $this->success('User created.', redirectTo: '/users');
    }

    public function with(): array
    {
        return [
            'countries' => Country::all(),
            'languages' => Language::all(),
        ];
    }


}; ?>

<div>
    <x-header title="Create New User" separator/>

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
