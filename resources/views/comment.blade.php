@use('\Kirschbaum\Commentions\Config')

<div class="comm:flex comm:items-start comm:gap-x-4 comm:border comm:border-gray-300 comm:dark:border-gray-700 comm:p-4 comm:rounded-lg comm:shadow-sm comm:mb-2"
    id="filament-comment-{{ $comment->getId() }}">
    @if ($avatar = $comment->getAuthorAvatar())
        <img src="{{ $comment->getAuthorAvatar() }}" alt="User Avatar"
            class="comm:w-10 comm:h-10 comm:rounded-full comm:mt-0.5 comm:object-cover comm:object-center" />
    @endif

    <div class="comm:flex-1">
        <div
            class="comm:text-sm comm:font-bold comm:text-gray-900 comm:dark:text-gray-100 comm:flex comm:justify-between comm:items-center">
            <div>
                {{ $comment->getAuthorName() }}
                <span class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-300"
                    title="Commented at {{ $comment->getCreatedAt()->format('Y-m-d H:i:s') }}">{{ $comment->getCreatedAt()->diffForHumans() }}</span>

                @if ($comment->getUpdatedAt()->gt($comment->getCreatedAt()))
                    <span class="comm:text-xs comm:text-gray-300 comm:ml-1"
                        title="Edited at {{ $comment->getUpdatedAt()->format('Y-m-d H:i:s') }}">(edited)</span>
                @endif

                @if ($comment->getLabel())
                    <span
                        class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-300 comm:bg-gray-100 comm:dark:bg-gray-800 comm:px-1.5 comm:py-0.5 comm:rounded-md">
                        {{ $comment->getLabel() }}
                    </span>
                @endif
            </div>

            @if ($comment->isComment() && Config::resolveAuthenticatedUser()?->canAny(['update', 'delete'], $comment))
                <div class="comm:flex comm:gap-x-1">
                    @if (Config::resolveAuthenticatedUser()?->can('update', $comment))
                        <x-filament::icon-button icon="heroicon-s-pencil-square" wire:click="edit" size="xs"
                            color="gray" />
                    @endif

                    @if (Config::resolveAuthenticatedUser()?->can('delete', $comment))
                        <x-filament::modal id="delete-comment-modal-{{ $comment->getId() }}" width="sm">
                            <x-slot name="trigger">
                                <x-filament::icon-button icon="heroicon-s-trash" size="xs" color="gray" />
                            </x-slot>

                            <x-slot name="heading">
                                Delete Comment
                            </x-slot>

                            <div class="comm:py-4">
                                Are you sure you want to delete this comment? This action cannot be undone.
                            </div>

                            <x-slot name="footer">
                                <div class="comm:flex comm:justify-end comm:gap-x-4">
                                    <x-filament::button
                                        wire:click="$dispatch('close-modal', { id: 'delete-comment-modal-{{ $comment->getId() }}' })"
                                        color="gray">
                                        Cancel
                                    </x-filament::button>

                                    <x-filament::button wire:click="delete" color="danger">
                                        Delete
                                    </x-filament::button>
                                </div>
                            </x-slot>
                        </x-filament::modal>
                    @endif
                </div>
            @endif
        </div>

        @if ($editing)
            <div class="comm:mt-2">
                <div class="tip-tap-container comm:mb-2" wire:ignore>
                    <div x-data="editor(@js($commentBody), @js($mentionables), 'comment')">
                        <div x-ref="element"></div>
                    </div>
                </div>

                <div class="comm:flex comm:gap-x-2">
                    <x-filament::button wire:click="updateComment({{ $comment->getId() }})" size="sm">
                        Save
                    </x-filament::button>

                    <x-filament::button wire:click="cancelEditing" size="sm" color="gray">
                        Cancel
                    </x-filament::button>
                </div>
            </div>
        @else
            <div class="comm:mt-1 comm:space-y-6 comm:text-sm comm:text-gray-800 comm:dark:text-gray-200">
                {!! $comment->getParsedBody() !!}</div>

            {{-- Display attachments --}}
            @if ($comment->hasAttachments())
                <div class="comm:mt-3 comm:space-y-2">
                    @if ($comment->getAttachmentCount() > 1)
                        <div class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-400 comm:font-medium">
                            Attachments ({{ $comment->getAttachmentCount() }})
                        </div>
                    @endif
                    <div class="comm:space-y-2">
                        @foreach ($comment->getAttachments() as $attachment)
                            <div
                                class="comm:flex comm:items-center comm:gap-3 comm:p-3 comm:bg-gray-50 comm:dark:bg-gray-800 comm:rounded-md comm:border comm:border-gray-200 comm:dark:border-gray-700">
                                <div class="comm:flex comm:items-center comm:space-x-2 comm:flex-1 comm:min-w-0">
                                    @if (in_array($attachment['mime_type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp']))
                                        <svg class="comm:w-4 comm:h-4 comm:text-green-500 comm:flex-shrink-0"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    @elseif (str_starts_with($attachment['mime_type'], 'video/'))
                                        <svg class="comm:w-4 comm:h-4 comm:text-blue-500 comm:flex-shrink-0"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    @elseif (str_starts_with($attachment['mime_type'], 'audio/'))
                                        <svg class="comm:w-4 comm:h-4 comm:text-purple-500 comm:flex-shrink-0"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3">
                                            </path>
                                        </svg>
                                    @else
                                        <svg class="comm:w-4 comm:h-4 comm:text-gray-500 comm:flex-shrink-0"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    @endif
                                    <div class="comm:flex-1 comm:min-w-0">
                                        <div class="comm:text-sm comm:text-gray-700 comm:dark:text-gray-300 comm:truncate"
                                            title="{{ $attachment['name'] }}">
                                            {{ $attachment['name'] }}
                                        </div>
                                        <div class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-400">
                                            {{ number_format(($attachment['size'] ?? 0) / 1024, 1) }} KB
                                        </div>
                                    </div>
                                </div>
                                <div class="comm:flex comm:items-center comm:space-x-1 comm:flex-shrink-0">
                                    {{-- Open in new tab button --}}
                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="comm:text-gray-600 comm:dark:text-gray-400 hover:comm:text-gray-800 comm:dark:hover:text-gray-200 comm:p-1 comm:rounded comm:transition-colors"
                                        title="Open in new tab">
                                        <svg class="comm:w-4 comm:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                            </path>
                                        </svg>
                                    </a>
                                    {{-- Download button --}}
                                    <a href="{{ Storage::url($attachment['path']) }}"
                                        download="{{ $attachment['name'] }}"
                                        class="comm:text-blue-600 comm:dark:text-blue-400 hover:comm:text-blue-800 comm:dark:hover:text-blue-300 comm:p-1 comm:rounded comm:transition-colors"
                                        title="Download">
                                        <svg class="comm:w-4 comm:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($comment->isComment())
                <livewire:commentions::reactions :comment="$comment"
                    :wire:key="'reaction-manager-' . $comment->getId()" />
            @endif
        @endif
    </div>
</div>
