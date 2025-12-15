<x-layouts.auth.card :title="config('app.name')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl prose dark:prose-invert">
        <flux:heading size="xl" level="2">対応サイト</flux:heading>

        <div class="grid gap-4">
            @foreach($drivers as $driver)
                <div class="mb-3">
                    <flux:heading size="lg">{{ data_get($driver, 'name') }}</flux:heading>
                    <flux:text>{!! Str::markdown(data_get($driver, 'description'), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</flux:text>

                    <flux:input value="{{ data_get($driver, 'example') }}" readonly copyable/>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.auth.card>
