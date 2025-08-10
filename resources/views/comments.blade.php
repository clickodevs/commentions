@use('\Kirschbaum\Commentions\Config')

<div class="comm:space-y-2" x-data="{ wasFocused: false }">
  @if (Config::resolveAuthenticatedUser()?->can('create', Config::getCommentModel()))
  <form wire:submit.prevent="save" x-cloak>
    {{-- tiptap editor --}}
    <div class="comm:relative tip-tap-container comm:mb-2" x-on:click="wasFocused = true" wire:ignore>
      <div
        x-data="editor(@js($commentBody), @js($this->mentions), 'comments')">
        <div x-ref="element"></div>
      </div>
    </div>

    {{-- File upload section --}}
    <template x-if="wasFocused">
      <div class="comm:mb-4">
        <div class="comm:flex comm:items-center comm:gap-2 comm:mb-2">
          <input
            type="file"
            wire:model="attachments"
            multiple
            class="comm:hidden"
            id="comment-attachments"
            max="5">
          <label
            for="comment-attachments"
            class="comm:cursor-pointer comm:inline-flex comm:items-center comm:px-3 comm:py-2 comm:text-xs comm:font-medium comm:text-gray-700 comm:bg-white comm:border comm:border-gray-300 comm:rounded-md comm:shadow-sm hover:comm:bg-gray-50 focus:comm:outline-none focus:comm:ring-2 focus:comm:ring-offset-2 focus:comm:ring-indigo-500">
            <svg class="comm:w-4 comm:h-4 comm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
            </svg>
            Attach Files
          </label>
          <span class="comm:text-xs comm:text-gray-500">Max 5 files, 20MB each</span>
        </div>

        @error('attachments')
        <div class="comm:text-red-600 comm:text-sm comm:mb-2">{{ $message }}</div>
        @enderror

        @error('attachments.*')
        <div class="comm:text-red-600 comm:text-sm comm:mb-2">{{ $message }}</div>
        @enderror

        {{-- Show uploaded files --}}
        @if($attachments)
        <div class="comm:space-y-2">
          @foreach($attachments as $index => $attachment)
          <div class="comm:flex comm:items-center comm:gap-3 comm:p-3 comm:bg-gray-50 comm:rounded-md comm:border comm:border-gray-200">
            <div class="comm:flex comm:items-center comm:space-x-2 comm:flex-1 comm:min-w-0">
              <svg class="comm:w-4 comm:h-4 comm:text-gray-500 comm:flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <div class="comm:flex-1 comm:min-w-0">
                <div class="comm:text-sm comm:text-gray-700 comm:truncate" title="{{ $attachment->getClientOriginalName() }}">
                  {{ $attachment->getClientOriginalName() }}
                </div>
                <div class="comm:text-xs comm:text-gray-500">
                  {{ number_format($attachment->getSize() / 1024, 1) }} KB
                </div>
              </div>
            </div>
            <button
              type="button"
              wire:click="removeAttachment({{ $index }})"
              class="comm:text-red-600 hover:comm:text-red-800 comm:p-1 comm:rounded comm:transition-colors comm:flex-shrink-0"
              title="Remove file">
              <svg class="comm:w-4 comm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          @endforeach
        </div>
        @endif

        <div wire:loading wire:target="attachments" class="comm:text-sm comm:text-gray-500">
          Uploading files...
        </div>
      </div>
    </template>

    <template x-if="wasFocused">
      <div>
        <x-filament::button
          wire:click="save"
          size="sm">Comment</x-filament::button>

        <x-filament::button
          x-on:click="wasFocused = false"
          wire:click="clear"
          size="sm"
          color="gray">Cancel</x-filament::button>
      </div>
    </template>
  </form>
  @endif

  <livewire:commentions::comment-list
    :record="$record"
    :mentionables="$this->mentions"
    :polling-interval="$pollingInterval"
    :paginate="$paginate ?? true"
    :per-page="$perPage ?? 5"
    :load-more-label="$loadMoreLabel ?? 'Show more'"
    :per-page-increment="$perPageIncrement ?? null" />
</div>
