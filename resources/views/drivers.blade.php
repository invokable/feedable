<x-layouts.main :title="config('app.name')">
    <x-slot:left>
        @include('driver-index')
    </x-slot:left>

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl prose dark:prose-invert">
        <flux:heading size="xl" level="2">対応サイト</flux:heading>

        <div class="grid gap-4">
            @foreach($drivers as $driver)
                <div class="mb-3" id="{{ data_get($driver, 'id') }}">
                    <div class="flex flex-col gap-6">
                        <div class="rounded-xl border bg-white dark:bg-stone-950 dark:border-stone-800 text-stone-800 shadow-xs p-5">
                            <flux:heading size="lg">{{ data_get($driver, 'name') }}</flux:heading>
                            <flux:text>{!! Str::markdown(data_get($driver, 'description', ''), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</flux:text>

                            <flux:input value="{{ url(data_get($driver, 'example')) }}" readonly copyable/>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.main>
