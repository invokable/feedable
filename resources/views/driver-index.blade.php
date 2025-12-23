@foreach($drivers as $driver)
    <div class="pb-3">
        <flux:link href="#{{ data_get($driver, 'id') }}" variant="ghost" class="text-neutral-300">{{ data_get($driver, 'name') }}</flux:link>
    </div>
@endforeach
